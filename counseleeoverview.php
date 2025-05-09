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

// Fetch counselee data
$query = "SELECT id, name, email, registration_number, designation, school, department FROM counselees"; 
$stmt = $pdo->prepare($query);
$stmt->execute();
$counseleeData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create PDF object
$pdf = new Fpdf('L', 'mm', 'A4');  // 'L' for landscape, 'mm' for millimeters, 'A4' for page size
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(190, 10, 'Counselee Overview', 0, 1, 'C');
$pdf->Ln(5);

// Table Headers
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(200, 200, 200);
$pdf->Cell(10, 10, 'ID', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Name', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Email', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Reg No.', 1, 0, 'C', true);
$pdf->Cell(20, 10, 'Role', 1, 0, 'C', true);
$pdf->Cell(65, 10, 'School', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Department', 1, 1, 'C', true);

// Table Content
$pdf->SetFont('Arial', '', 9);
foreach ($counseleeData as $row) {
    $pdf->Cell(10, 10, $row['id'], 1);
    $pdf->Cell(35, 10, $row['name'], 1);
    $pdf->Cell(35, 10, $row['email'], 1);
    $pdf->Cell(30, 10, $row['registration_number'], 1);
    $pdf->Cell(20, 10, ucfirst($row['designation']), 1);
    $pdf->Cell(65, 10, $row['school'], 1);
    $pdf->Cell(30, 10, $row['department'], 1);
    $pdf->Ln();
}

// Output PDF (View in browser)
$pdf->Output();

// Optionally, force download:
// $pdf->Output('D', 'counselee_overview.pdf');
?>
