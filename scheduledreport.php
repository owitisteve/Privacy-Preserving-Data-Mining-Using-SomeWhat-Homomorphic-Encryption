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

// Fetch only appointments scheduled today or in the future
$result = $conn->query("SELECT a.id, c.name AS counselee_name, a.scheduled_date, a.scheduled_time 
                        FROM appointments a 
                        JOIN counselees c ON a.counselee_id = c.id
                        WHERE a.status = 'scheduled' AND a.scheduled_date >= CURDATE()
                        ORDER BY a.scheduled_date ASC");

if ($result->num_rows == 0) {
    // Display modal if no upcoming appointments
    echo "
    <div id='noAppointmentsModal' class='modal'>
        <div class='modal-content'>
            <p>No upcoming scheduled appointments found.</p>
            <a href='user_dashboard.php'>
                <button>OK</button>
            </a>
        </div>
    </div>
    <style>
        /* Modal Style */
        .modal {
            display: block;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            width: 300px;
            margin: auto;
        }
        .modal button {
            padding: 10px 20px;
            margin-top: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .modal button:hover {
            background-color: #45a049;
        }
    </style>
    <script>
        // Close the modal when clicked outside of the content
        window.onclick = function(event) {
            var modal = document.getElementById('noAppointmentsModal');
            if (event.target == modal) {
                window.location.href = 'user_dashboard.php';
            }
        }
    </script>
    ";
    exit();
}

// Create PDF if appointments exist
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(190, 10, 'Upcoming Scheduled Appointments Report', 0, 1, 'C');
$pdf->Ln(5);

// Table Headers
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(20, 10, 'ID', 1);
$pdf->Cell(70, 10, 'Counselee Name', 1);
$pdf->Cell(50, 10, 'Scheduled Date', 1);
$pdf->Cell(40, 10, 'Scheduled Time', 1);
$pdf->Ln();

// Table Data
$pdf->SetFont('Arial', '', 12);
while ($row = $result->fetch_assoc()) {
    $pdf->Cell(20, 10, $row['id'], 1);
    $pdf->Cell(70, 10, $row['counselee_name'], 1);
    $pdf->Cell(50, 10, $row['scheduled_date'], 1);
    $pdf->Cell(40, 10, $row['scheduled_time'], 1);
    $pdf->Ln();
}

// Output PDF
$pdf->Output('I', 'Scheduled_Appointments.pdf');
?>
