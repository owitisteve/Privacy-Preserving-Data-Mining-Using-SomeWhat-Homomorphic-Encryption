<?php
session_start();

// Ensure counselee is logged in
if (!isset($_SESSION['counselee_id'])) {
    header("Location: clogin.php");
    exit();
}

include 'db.php';

$counselee_id = $_SESSION['counselee_id']; // Get counselee ID from session
$feedback_sent = false;

// ✅ Mark notification as read if triggered via GET
if (isset($_GET['mark_as_read'])) {
    $notification_id = (int)$_GET['mark_as_read'];
    $check_query = mysqli_query($conn, "SELECT * FROM notifications WHERE id = $notification_id AND user_id = $counselee_id");

    if (mysqli_num_rows($check_query) > 0) {
        mysqli_query($conn, "UPDATE notifications SET is_read = 1 WHERE id = $notification_id");
    }

    header('Location: counseleehelp.php');
    exit();
}

// Handle new help message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    mysqli_query($conn, "INSERT INTO feedback (sender_id, sender_role, message) 
                         VALUES ($counselee_id, 'counselee', '$message')");
    $feedback_sent = true;
}

// Handle message deletion
if (isset($_GET['delete_message_id'])) {
    $message_id = (int)$_GET['delete_message_id'];
    mysqli_query($conn, "DELETE FROM feedback WHERE id = $message_id AND sender_id = $counselee_id");
    header('Location: counseleehelp.php');
    exit();
}

// Fetch feedback history
$results = mysqli_query($conn, "SELECT * FROM feedback 
                                WHERE sender_id = $counselee_id AND sender_role = 'counselee' 
                                ORDER BY created_at DESC");

if (!$results) {
    die('Error in query: ' . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Counselee Help & Feedback</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            background: #f4f4f4;
        }
        h2 {
            color: #0073e6;
        }
        form {
            background: white;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            resize: vertical;
            margin-bottom: 10px;
        }
        button {
            background-color: #0073e6;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .feedback-entry {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #0073e6;
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        }
        .admin-reply {
            margin-top: 10px;
            background: #e7f7e7;
            padding: 10px;
            border-left: 4px solid green;
        }
        .delete-button {
            background-color: #d9534f;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
            display: inline-block;
            text-decoration: none;
        }
        .delete-button:hover {
            background-color: #c9302c;
        }
        .notification-info {
            padding: 10px;
            background: #ffffcc;
            margin-bottom: 20px;
            border-left: 4px solid #ffcc00;
        }
        .mark-read-btn {
            background-color: #28a745;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            margin-top: 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h2>Send a Message to Admin</h2>

    <?php if ($feedback_sent): ?>
        <p style="color: green;">Your message was sent successfully.</p>
    <?php endif; ?>

    <form method="post">
        <label for="message">Your Message:</label><br>
        <textarea name="message" rows="4" placeholder="Describe your issue or request..." required></textarea><br>
        <button type="submit">Send Message</button>
    </form>

    <h2>Your Previous Messages & Admin Replies</h2>

    <?php while ($row = mysqli_fetch_assoc($results)): ?>
        <div class="feedback-entry">
            <strong>Sent:</strong> <?= $row['created_at'] ?><br>
            <p><?= nl2br(htmlspecialchars($row['message'])) ?></p>

            <?php if (!empty($row['admin_reply'])): ?>
                <div class="admin-reply">
                    <strong>Admin replied:</strong><br>
                    <?= nl2br(htmlspecialchars($row['admin_reply'])) ?>
                </div>
            <?php endif; ?>

            <a href="counseleehelp.php?delete_message_id=<?= $row['id'] ?>" class="delete-button" onclick="return confirm('Are you sure you want to delete this message?')">Delete Message</a>
        </div>
    <?php endwhile; ?>
</body>
</html>
