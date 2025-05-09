<?php
session_start();
require 'vendor/fpdf/fpdf/src/Fpdf/Fpdf.php';
use Fpdf\Fpdf;

if (!isset($_SESSION['user_logged_in'])) {
    header('Location: user_login.php');
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "D_vine@245", "ppdm");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch pending appointments with counselee details
$query = "
    SELECT a.id AS appointment_id, c.name AS counselee_name, c.registration_number, a.requested_date
    FROM appointments a
    JOIN counselees c ON a.counselee_id = c.id
    WHERE a.status = 'pending'";

$result = $conn->query($query);

if ($result->num_rows == 0) {
    // No pending appointments found, show the modal
    echo "
        <script>
            // Display the modal
            alert('No pending appointments found.');
            // Redirect to user dashboard after closing the alert
            window.location.href = 'user_dashboard.php';
        </script>
    ";
    exit(); // Stop the rest of the code from running
}

// Create PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(190, 10, 'Pending Appointments Report', 0, 1, 'C');
$pdf->Ln(5);

// Table Headers
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(20, 10, 'ID', 1);
$pdf->Cell(70, 10, 'Counselee Name', 1);
$pdf->Cell(50, 10, 'Registration Number', 1);
$pdf->Cell(50, 10, 'Requested Date', 1);
$pdf->Ln();

// Table Data
$pdf->SetFont('Arial', '', 12);
while ($row = $result->fetch_assoc()) {
    $pdf->Cell(20, 10, $row['appointment_id'], 1);
    $pdf->Cell(70, 10, $row['counselee_name'], 1);
    $pdf->Cell(50, 10, $row['registration_number'], 1);
    $pdf->Cell(50, 10, $row['requested_date'], 1);
    $pdf->Ln();
}

// Output PDF (View in Browser)
$pdf->Output('I', 'Pending_Appointments_Report.pdf'); // 'I' shows in browser
?>
