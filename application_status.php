<?php
session_start();

// Ensure counselee is logged in
if (!isset($_SESSION['counselee_id'])) {
    header("Location: clogin.php");
    exit();
}

include 'db.php';

$counselee_id = $_SESSION['counselee_id']; // Get counselee ID from session

// Fetch the application status for the logged-in counselee
$query = "
    SELECT * FROM pending_approvals 
    WHERE registration_number = (SELECT registration_number FROM counselees WHERE id = $counselee_id LIMIT 1)
";
$result = mysqli_query($conn, $query);
$application_status = null;

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $status = $row['status'];
    $decline_reason = $row['decline_reason'];
    
    if ($status === 'pending') {
        $application_status = 'Pending';
    } elseif ($status === 'cancelled') {
        $application_status = 'Application Declined: ' . htmlspecialchars($decline_reason);
    } else {
        $application_status = 'Congratulations, your registration was approved!';
    }
} else {
    $application_status = 'No application found.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Status</title>
    <style>
        /* Style the application status message */
        .status-message {
            background: #f9f9f9;
            border: 1px solid #ccc;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .status-message.pending {
            background-color: #ffeb3b;
        }
        .status-message.approved {
            background-color: #4caf50;
            color: white;
        }
        .status-message.declined {
            background-color: #f44336;
            color: white;
        }
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            background: #f4f4f4;
        }
        h2 {
            color: #0073e6;
        }
        .back-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #0073e6;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .back-button:hover {
            background-color: #005bb5;
        }
    </style>
</head>
<body>

    <h2>Your Application Status</h2>
    
    <div class="status-message <?php 
        if ($application_status === 'Pending') {
            echo 'pending';
        } elseif (strpos($application_status, 'Declined') !== false) {
            echo 'declined';
        } elseif ($application_status === 'Congratulations, your registration was approved!') {
            echo 'approved';
        }
    ?>">
        <p><?php echo $application_status; ?></p>
    </div>

    <!-- Back Button to Dashboard -->
    <a href="counselee_dashboard.php" class="back-button">Back to Dashboard</a>

</body>
</html>
