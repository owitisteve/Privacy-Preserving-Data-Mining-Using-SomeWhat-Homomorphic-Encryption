<?php
session_start();
if (!isset($_SESSION['user_logged_in'])) {
    header('Location: admin_login.php');
    exit();
}

include 'ddb.php'; // Ensure database connection

// Fetch frequency of cases per year of study
$query = "SELECT year_of_study, COUNT(*) as frequency FROM counseling GROUP BY year_of_study ORDER BY frequency DESC";
$result = mysqli_query($conn, $query);

$years = [];
$frequencies = [];
$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $years[] = $row['year_of_study'];
    $frequencies[] = $row['frequency'];
    $data[] = [$row['year_of_study'], $row['frequency']]; // Store for CSV
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frequent Counseling Cases by Year of Study</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background-color: #f4f4f4;
            text-align: center;
        }
        h2 {
            text-align: center;
        }
        table {
            width: 60%;
            margin: 20px auto;
            border-collapse: collapse;
            background: white;
        }
        table, th, td {
            border: 1px solid black;
            text-align: center;
            padding: 10px;
        }
        canvas {
            display: block;
            margin: auto;
            background: white;
        }
        .button-container {
            margin-top: 20px;
        }
        .btn {
            background-color: #1565c0;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin: 10px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background-color: #0d47a1;
        }
    </style>
</head>
<body>

<h2>Frequent Counseling Cases by Year of Study</h2>

<!-- Display Data in Table -->
<table id="dataTable">
    <tr>
        <th>Year of Study</th>
        <th>Frequency</th>
    </tr>
    <?php foreach ($years as $index => $year): ?>
        <tr>
            <td><?php echo htmlspecialchars($year); ?></td>
            <td><?php echo $frequencies[$index]; ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<!-- Display Data in Bar Chart -->
<canvas id="yearChart" width="400" height="200"></canvas>

<!-- Buttons -->
<div class="button-container">
    <a href="admin_dashboard.php" class="btn">Back</a>
    <button class="btn" onclick="downloadPDF()">Download PDF</button>
</div>

<script>
    var ctx = document.getElementById('yearChart').getContext('2d');
    var yearChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($years); ?>,
            datasets: [{
                label: 'Number of Cases',
                data: <?php echo json_encode($frequencies); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Function to download PDF
    async function downloadPDF() {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF();

        // Add title
        pdf.setFontSize(18);
        pdf.text("Frequent Counseling Cases by Year of Study", 20, 20);

        // Capture chart as image
        const canvas = document.getElementById('yearChart');
        const chartImage = canvas.toDataURL('image/png');

        // Capture table using html2canvas
        const table = document.getElementById('dataTable');
        const tableCanvas = await html2canvas(table);
        const tableImage = tableCanvas.toDataURL('image/png');

        // Add chart to PDF
        pdf.addImage(chartImage, 'PNG', 20, 30, 160, 80);

        // Add table to PDF
        pdf.addImage(tableImage, 'PNG', 20, 120, 160, 60);

        // Save the PDF
        pdf.save('frequent_counseling_years.pdf');
    }
</script>

</body>
</html>
