<!DOCTYPE html>
<html>
<head>
    <title>Anomaly Detection</title>
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            padding-top: 80px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fff;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
        }

        ul {
            padding-left: 20px;
        }

        li {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<?php
// Database connection
$host = "localhost";
$user = "root";
$password = "D_vine@245";
$database = "ppddm";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define the time period for anomaly detection (last 7 days)
$time_period = '7 DAY';

// Fetch recent data for the last 7 days
$recent_query = "
    SELECT counseling_type, school, year_of_study, COUNT(*) AS recent_cases
    FROM counseling
    WHERE created_at >= NOW() - INTERVAL $time_period
    GROUP BY counseling_type, school, year_of_study
";
$recent_result = $conn->query($recent_query);

$anomalies = [];

// Check for anomalies: entries >= 20 within the last 7 days
while ($row = $recent_result->fetch_assoc()) {
    if ($row['recent_cases'] >= 20) {
        $anomalies[] = [
            "counseling_type" => $row['counseling_type'],
            "school" => $row['school'],
            "year_of_study" => $row['year_of_study'],
            "cases" => $row['recent_cases']
        ];
    }
}

// Display anomalies in a modal if any are detected
if (!empty($anomalies)) {
    echo "
        <!-- Modal -->
        <div id='anomalyModal' class='modal'>
            <div class='modal-content'>
                <span class='close'>&times;</span>
                <h2>Anomalies Detected (Past 7 Days):</h2>
                <ul>";

    foreach ($anomalies as $anomaly) {
        echo "<li><strong>" . htmlspecialchars($anomaly['counseling_type']) . "</strong> cases in " . 
             htmlspecialchars($anomaly['school']) . " (Year " . 
             htmlspecialchars($anomaly['year_of_study']) . 
             ") reached <strong>" . $anomaly['cases'] . "</strong> entries.</li>";
    }

    echo "
                </ul>
            </div>
        </div>

        <script>
            var modal = document.getElementById('anomalyModal');
            var span = document.getElementsByClassName('close')[0];
            modal.style.display = 'block';
            span.onclick = function() {
                window.location.href = 'mining.php';
            }
            window.onclick = function(event) {
                if (event.target == modal) {
                    window.location.href = 'mining.php';
                }
            }
        </script>";
} else {
    echo "<h2 style='text-align:center;'>No anomalies detected.</h2>";
}

$conn->close();
?>

</body>
</html>
