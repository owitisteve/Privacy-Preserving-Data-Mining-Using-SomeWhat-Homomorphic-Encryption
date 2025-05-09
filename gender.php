<?php
session_start();
if (!isset($_SESSION['user_logged_in'])) {
    header('Location: admin_login.php');
    exit();
}

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the FPDF library
require 'vendor/fpdf/fpdf/src/Fpdf/Fpdf.php';
use Fpdf\Fpdf;

// Database connection
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

// Fetch the gender and total counts of counseling entries (family table data)
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

// Generate the PDF report first, without any HTML content
$pdf = new Fpdf();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(190, 10, 'Anomaly Detection in Counseling Success Based on Gender', 0, 1, 'C');
$pdf->Ln(5);

// Summary Information
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

// Save the PDF to a file first before outputting to browser
$pdfOutputPath = __DIR__ . '/reports/anomaly_report.pdf';
$pdf->Output('F', $pdfOutputPath); // Save PDF to file

// Now proceed with HTML rendering for the chart and sending back the image
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anomaly Report with Chart</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div style="width: 80%; margin: auto;">
        <h3>Gender Distribution of Counseling Sessions</h3>
        <canvas id="myChart"></canvas>
        <script>
            var ctx = document.getElementById('myChart').getContext('2d');
            var chart = new Chart(ctx, {
                type: 'bar', // Type of chart (bar, line, etc.)
                data: {
                    labels: ['Male', 'Female'],
                    datasets: [{
                        label: 'Sessions',
                        data: [<?php echo $genderCounts['male']; ?>, <?php echo $genderCounts['female']; ?>],
                        backgroundColor: ['blue', 'red'],
                        borderColor: ['blue', 'red'],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        </script>
    </div>

    <!-- Trigger to capture the chart as an image and insert into PDF -->
    <script>
        window.onload = function() {
            // Capture the chart as an image (Base64 string)
            var chartImage = document.getElementById('myChart').toDataURL('image/png');
            
            // Function to send the image to the server for saving and embedding in the PDF
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "save_chart_image.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    // Image is saved, ready to be added to PDF
                    console.log("Chart image saved successfully.");
                }
            };
            xhr.send("imageData=" + encodeURIComponent(chartImage));
        };
    </script>
</body>
</html>
<?php
// Output PDF after HTML content has been rendered
$pdf->Output('I', 'anomaly_report.pdf'); // This will output the PDF inline in the browser
?>
