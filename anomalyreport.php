<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Include FPDF library
require 'vendor/fpdf/fpdf/src/Fpdf/Fpdf.php';
use Fpdf\Fpdf;

// Database connection
$host = "localhost";
$user = "root";
$password = "D_vine@245";
$database = "ppddm";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define the time period for anomaly detection (e.g., last 7 days)
$time_period = '7 DAY';

// Fetch historical data (e.g., all entries for each school and year)
$query = "
    SELECT counseling_type, school, year_of_study, COUNT(*) AS total_cases
    FROM counseling
    WHERE created_at < NOW() - INTERVAL $time_period
    GROUP BY counseling_type, school, year_of_study
";
$result = $conn->query($query);

$historical_data = [];
while ($row = $result->fetch_assoc()) {
    $key = $row['school'] . '-' . $row['year_of_study'] . '-' . $row['counseling_type'];
    $historical_data[$key] = $row['total_cases'];
}

// Fetch recent data for the last 7 days (or any short period)
$recent_query = "
    SELECT counseling_type, school, year_of_study, COUNT(*) AS recent_cases
    FROM counseling
    WHERE created_at >= NOW() - INTERVAL $time_period
    GROUP BY counseling_type, school, year_of_study
";
$recent_result = $conn->query($recent_query);

$anomalies = [];
$percentage_threshold = 20; // 20% change for anomaly flag
$scaling_factor = 0.2; // Reduce the impact of changes

// Loop through recent data and compare against historical data
while ($row = $recent_result->fetch_assoc()) {
    $key = $row['school'] . '-' . $row['year_of_study'] . '-' . $row['counseling_type'];
    $recent_count = $row['recent_cases'];

    if (isset($historical_data[$key])) {
        $previous_count = $historical_data[$key];

        // Calculate percentage deviation from historical data with scaling factor
        $percentage_deviation = (($recent_count - $previous_count) / $previous_count) * 100 * $scaling_factor;

        // Cap percentage to 100%
        $percentage_deviation = min(abs($percentage_deviation), 100);

        // Check if the deviation exceeds the threshold
        if (abs($percentage_deviation) >= $percentage_threshold) {
            $anomalies[] = [
                "counseling_type" => $row['counseling_type'],
                "school" => $row['school'],
                "year_of_study" => $row['year_of_study'],
                "change" => round($percentage_deviation, 2) . "%" // Deviation percentage
            ];
        }
    }
}

// Generate PDF if anomalies exist
if (!empty($anomalies)) {
    // Create a new PDF instance
    $pdf = new FPDF();
    $pdf->AddPage();
    
    // Set title
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Anomaly Detection Report', 0, 1, 'C');
    $pdf->Ln(10);
    
    // Add table header
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(35, 10, 'Counseling Type', 1, 0, 'C');
    $pdf->Cell(90, 10, 'School', 1, 0, 'C');
    $pdf->Cell(35, 10, 'Year of Study', 1, 0, 'C');
    $pdf->Cell(25, 10, 'Change (%)', 1, 1, 'C');

    // Add data rows
    $pdf->SetFont('Arial', '', 12);
    foreach ($anomalies as $anomaly) {
        $pdf->Cell(35, 10, $anomaly['counseling_type'], 1, 0, 'C');
        $pdf->Cell(90, 10, $anomaly['school'], 1, 0, 'C');
        $pdf->Cell(35, 10, $anomaly['year_of_study'], 1, 0, 'C');
        $pdf->Cell(25, 10, $anomaly['change'], 1, 1, 'C');
    }

    // Save the generated PDF to the server (temporary location)
    $pdf_filename = 'anomaly_report_' . time() . '.pdf';
    $pdf_output_path = 'temp_reports/' . $pdf_filename;  // Save in the temp_reports folder
    $pdf->Output('F', $pdf_output_path);  // F for file (save to server)
    
    // Show the report link to the user
    echo "<h2>PDF Report Generated. Click the link below to download:</h2>";
    echo "<a href='$pdf_output_path' target='_blank'>Download Anomaly Report</a>";
} else {
    echo "<h2>No anomalies detected.</h2>";
}

$conn->close();
?>
