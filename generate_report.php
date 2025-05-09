<?php
require 'vendor/fpdf/fpdf/src/Fpdf/Fpdf.php';
use Fpdf\Fpdf;

session_start();
if (!isset($_SESSION['user_logged_in'])) {
    header('Location: admin_login.php');
    exit();
}

// Fetch the gender and total counts of counseling entries (family table data)
$host = 'localhost';
$dbname = 'ppdm';
$username = 'root';
$password = 'D_vine@245';
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database Connection Failed: ' . $e->getMessage());
}

$query = "
    SELECT f.gender, COUNT(f.id) AS total_sessions
    FROM family f
    GROUP BY f.gender
";
$stmt = $pdo->prepare($query);
$stmt->execute();
$genderData = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalCounselees = 0;
$genderCounts = ['male' => 0, 'female' => 0];
foreach ($genderData as $row) {
    $totalCounselees += $row['total_sessions'];
    $genderCounts[strtolower($row['gender'])] = $row['total_sessions'];
}

$difference = abs($genderCounts['male'] - $genderCounts['female']);
$percentageDiff = ($difference / $totalCounselees) * 100;
$anomalyMessage = "No significant anomaly detected. The gender distribution of counseling sessions is balanced.";

if ($percentageDiff > 20) {
    $higherGender = ($genderCounts['male'] > $genderCounts['female']) ? 'Male' : 'Female';
    $anomalyMessage = "Anomaly detected: $higherGender gender has a significant increase in counseling sessions, accounting for $percentageDiff% more cases than the opposite gender.";
}

// Generate PDF Report for anomalies
$pdf = new Fpdf();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(190, 10, 'Anomaly Detection in Counseling Success Based on Gender', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(190, 10, "Total Counselees: $totalCounselees", 0, 1);
$pdf->Cell(190, 10, "Male Sessions: {$genderCounts['male']}", 0, 1);
$pdf->Cell(190, 10, "Female Sessions: {$genderCounts['female']}", 0, 1);
$pdf->Cell(190, 10, "Difference: $difference", 0, 1);
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(190, 10, 'Anomaly Analysis:', 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(190, 10, $anomalyMessage);
$pdf->Ln(5);

// Add chart to the PDF
$chartImage = 'chart.png';  // Ensure the chart is saved as 'chart.png'

if (file_exists($chartImage)) {
    $pdf->Image($chartImage, 10, $pdf->GetY(), 180);  // Adjust size and position if needed
    $pdf->Ln(5);  // Add space after the chart
} else {
    $pdf->Cell(190, 10, 'No chart image found.', 0, 1);
}

$pdf->Output('I', 'anomaly_report.pdf'); // 'I' for inline view in browser
?>
