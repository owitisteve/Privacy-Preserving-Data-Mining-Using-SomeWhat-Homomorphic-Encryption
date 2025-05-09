<?php
require 'vendor/fpdf/fpdf/src/Fpdf/Fpdf.php';
use Fpdf\Fpdf;

// DB Connection
$conn = new mysqli("localhost", "root", "D_vine@245", "ppdm");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get counselees registered before today
$query = "SELECT * FROM counselees WHERE DATE(created_at) < CURDATE()";
$result = $conn->query($query);

// Generate PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(200, 10, 'Counselees On Session', 0, 1, 'C');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(10, 10, 'ID', 1);
$pdf->Cell(45, 10, 'Name', 1);
$pdf->Cell(45, 10, 'Email', 1);
$pdf->Cell(45, 10, 'Reg Number', 1);
$pdf->Cell(45, 10, 'Admission Date', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);
while ($row = $result->fetch_assoc()) {
    $pdf->Cell(10, 10, $row['id'], 1);
    $pdf->Cell(45, 10, $row['name'], 1);
    $pdf->Cell(45, 10, $row['email'], 1);
    $pdf->Cell(45, 10, $row['registration_number'], 1);
    $pdf->Cell(45, 10, $row['created_at'], 1);
    $pdf->Ln();
}

$pdf->Output('D', 'counselees_on_session.pdf');
$conn->close();
?>
