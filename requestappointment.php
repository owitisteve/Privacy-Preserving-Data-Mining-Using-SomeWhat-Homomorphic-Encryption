<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require 'db.php';
// Ensure the user is logged in
if (!isset($_SESSION['counselee_id'])) {
    header("Location: clogin.php");
    exit();
}

$message = "";
$showMessage = false; // Track if the message should be shown
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $counselee_id = $_SESSION['counselee_id'];
    $counselor_id = 1; // Assuming one counselor for now
    $requested_date = $_POST['requested_date'];

    // Fetch counselee name from database
    $nameQuery = "SELECT name FROM counselees WHERE id = ?";
    $nameStmt = $conn->prepare($nameQuery);
    $nameStmt->bind_param("i", $counselee_id);
    $nameStmt->execute();
    $nameStmt->bind_result($counselee_name);
    $nameStmt->fetch();
    $nameStmt->close();

    // Insert appointment request into the database with counselee name
    $sql = "INSERT INTO appointments (counselee_id, counselor_id, requested_date, decline_reason, status, counselee_name) 
            VALUES (?, ?, ?, '', 'pending', ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiss", $counselee_id, $counselor_id, $requested_date, $counselee_name);
    if ($stmt->execute()) {
        $message = "Appointment request sent successfully!";
        $showMessage = true; // Show message only after success
    } else {
        $message = "Error: " . $stmt->error;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request an Appointment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: white;
            padding: 20px;
            width: 350px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h2 {
            margin-bottom: 15px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        input, button {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #007bff;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message-box {
            display: <?php echo $showMessage ? 'block' : 'none'; ?>;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            text-align: center;
            margin-top: 20px;
        }
        .ok-button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            margin-top: 10px;
            cursor: pointer;
            border-radius: 5px;
        }
        .ok-button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Request an Appointment</h2>

    <form method="POST">
        <label for="requested_date">Select Date:</label>
        <input type="date" id="requested_date" name="requested_date" required min="">
        <button type="submit">Request Appointment</button>
    </form>
</div>

<!-- Success Message (Hidden initially) -->
<?php if ($showMessage): ?>
    <div class="message-box">
        <p><?php echo $message; ?></p>
        <button class="ok-button" onclick="window.location.href='counselee_dashboard.php'">OK</button>
    </div>
<?php endif; ?>
<script>
    // Set minimum date to today to disable past dates
    const today = new Date().toISOString().split('T')[0];
    document.getElementById("requested_date").setAttribute('min', today);
</script>


</body>
</html>
