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

// Fetch unique schools from counselees table
$schoolsQuery = "SELECT DISTINCT school FROM counselees";
$schoolsStmt = $pdo->prepare($schoolsQuery);
$schoolsStmt->execute();
$schools = $schoolsStmt->fetchAll(PDO::FETCH_COLUMN);

// Initialize variables
$totalCounselees = 0;
$schoolCounts = [];
foreach ($schools as $school) {
    // Fetch counts of counseling sessions for each school from the family table
    $query = "
        SELECT COUNT(f.id) AS total_sessions
        FROM family f
        JOIN counselees c ON f.counselee_id = c.id
        WHERE c.school = :school
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['school' => $school]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $schoolCounts[$school] = $result['total_sessions'] ?? 0;
    $totalCounselees += $schoolCounts[$school];
}

// Calculate anomaly based on percentage differences
$averageSessions = $totalCounselees / (count($schoolCounts) ?: 1);
// Apply scaling factor to reduce the scale of averageSessions (e.g., divide by 2)
$averageSessions /= 2;

$anomalyMessage = "No significant anomaly detected across schools.";

$anomalies = [];
foreach ($schoolCounts as $school => $count) {
    // Calculate the percentage difference (scaled down to reduce impact)
    $percentageDiff = round((abs($count - $averageSessions) / max($averageSessions, 1)) * 50); // Scale by 50%
    
    // Apply scaling for schools with few sessions (e.g., reduce deviation for schools with 1 session)
    if ($count <= 1) {
        $percentageDiff /= 2; // Reduce the effect of a single session (or adjust as needed)
    }

    // Apply a maximum deviation cap (e.g., 30% maximum deviation)
    if ($percentageDiff > 30) {
        $percentageDiff = 30; // Cap the deviation at 30%
    }

    // Consider anomalies only if the deviation is greater than 50%
    if ($percentageDiff > 50) {
        $anomalies[] = "$school has a significant anomaly with $percentageDiff% deviation from the average.";
    }
}

// Update the anomaly message based on detected anomalies
if (!empty($anomalies)) {
    $anomalyMessage = implode("\n", $anomalies);
}

// Generate PDF Report
$pdf = new Fpdf();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(190, 10, 'Anomaly Detection in Counseling Success Based on School', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(190, 10, "Total Counselees: $totalCounselees", 0, 1);
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(190, 10, 'School-wise Counseling Sessions:', 0, 1);
$pdf->SetFont('Arial', '', 10);

foreach ($schoolCounts as $school => $count) {
    $pdf->Cell(190, 10, "$school: $count sessions", 0, 1);
}

$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(190, 10, 'Anomaly Analysis:', 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(190, 10, $anomalyMessage);
$pdf->Ln(5);

$pdf->Output('I', 'school_anomaly_report.pdf'); // 'I' for inline view in browser
?>
