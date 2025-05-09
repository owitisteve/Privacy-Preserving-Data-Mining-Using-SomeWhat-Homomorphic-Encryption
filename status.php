<?php
session_start();
include 'db.php'; // Your DB connection script

$counselee_id = $_SESSION['counselee_id'];
$message = ""; // To store the message content

// Step 1: Check the pending_approvals table
$sql = "SELECT status, decline_reason FROM pending_approvals WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $counselee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $status = $row['status'];
    $decline_reason = $row['decline_reason'];

    if ($status === 'pending') {
        $message = "<p style='color: orange;'>Your registration is still pending.</p>";
    } elseif ($status === 'cancelled') {
        $message = "<p style='color: red;'>Your registration was cancelled.</p>";
        $message .= "<p><strong>Reason:</strong> " . htmlspecialchars($decline_reason) . "</p>";
    } else {
        // Step 2: If status is neither pending nor cancelled, check the counselees table for approval
        $sql2 = "SELECT * FROM counselees WHERE id = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("i", $counselee_id);
        $stmt2->execute();
        $result2 = $stmt2->get_result();

        if ($result2->num_rows > 0) {
            $message = "<p style='color: green;'>🎉 Congratulations! Your registration was approved.</p>";
        } else {
            $message = "<p style='color: gray;'>🎉 Congratulations! Your registration was approved</p>";
        }

        $stmt2->close();
    }
} else {
    $message = "<p style='color: green;'>🎉 Congratulations! Your registration was approved</p>";
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Status Message</title>
    <style>
        /* Modal styles */
        .modal {
            display: block;
            position: fixed;
            z-index: 999;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.6);
        }

        .modal-content {
            background: #fff;
            padding: 30px;
            margin: 15% auto;
            border-radius: 10px;
            width: 400px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .modal-content button {
            margin-top: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        .modal-content button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<!-- Modal -->
<div class="modal">
    <div class="modal-content">
        <?php echo $message; ?>
        <button onclick="redirectToDashboard()">OK</button>
    </div>
</div>

<script>
    function redirectToDashboard() {
        window.location.href = "counselee_dashboard.php";
    }
</script>

</body>
</html>
