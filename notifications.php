<?php
session_start();
if (!isset($_SESSION['user_logged_in'])) {
    header('Location: admin_login.php');
    exit();
}

include 'db.php';

// Handle reply and mark as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['feedback_id'], $_POST['admin_reply'])) {
    $id = intval($_POST['feedback_id']);
    $reply = mysqli_real_escape_string($conn, $_POST['admin_reply']);

    mysqli_query($conn, "UPDATE feedback SET is_read = 1, admin_reply = '$reply' WHERE id = $id");
    header("Location: notifications.php");
    exit();
}

// Fetch feedback
$result = mysqli_query($conn, "SELECT * FROM feedback ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Notifications</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            background: #f4f4f4;
        }
        h2 {
            color: #0073e6;
        }
        .feedback {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 5px solid #0073e6;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .unread {
            border-left-color: red;
        }
        .meta {
            font-size: 14px;
            color: #555;
            margin-bottom: 10px;
        }
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border: 1px solid #ccc;
            resize: vertical;
        }
        button {
            background-color: green;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            margin-top: 10px;
            cursor: pointer;
        }
        .admin-reply {
            margin-top: 15px;
            background: #e7f7e7;
            padding: 10px;
            border-left: 4px solid green;
        }
    </style>
</head>
<body>
    <h2>Admin Notifications</h2>

    <?php while($row = mysqli_fetch_assoc($result)): ?>
        <div class="feedback <?= $row['is_read'] ? '' : 'unread' ?>">
            <div class="meta">
                From: <strong><?= htmlspecialchars($row['sender_role']) ?> (ID: <?= $row['sender_id'] ?>)</strong><br>
                Sent: <?= $row['created_at'] ?>
            </div>
            <p><?= nl2br(htmlspecialchars($row['message'])) ?></p>

            <?php if (!$row['is_read']): ?>
                <form method="post" action="">
                    <input type="hidden" name="feedback_id" value="<?= $row['id'] ?>">
                    <label for="admin_reply">Reply:</label>
                    <textarea name="admin_reply" rows="3" placeholder="Type your reply here..." required></textarea>
                    <button type="submit">Send Reply & Mark as Read</button>
                </form>
            <?php elseif (!empty($row['admin_reply'])): ?>
                <div class="admin-reply">
                    <strong>Admin replied:</strong><br>
                    <?= nl2br(htmlspecialchars($row['admin_reply'])) ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
</body>
</html>
