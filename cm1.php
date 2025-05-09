<?php
session_start();
if (!isset($_SESSION['counselee_id'])) {
    header("Location: clogin.php");
    exit();
}
require 'db.php';
$counselee_id = $_SESSION['counselee_id'];

// Handle sending messages
if (isset($_POST['send'])) {
    $recipient_id = $_POST['recipient_id'];
    $recipient_type = $_POST['recipient_type'];
    $message = trim($_POST['message']);

    $stmt = $conn->prepare("INSERT INTO messages (sender_id, sender_type, recipient_id, recipient_type, message) VALUES (?, 'counselee', ?, ?, ?)");
    $stmt->bind_param("iiss", $counselee_id, $recipient_id, $recipient_type, $message);
    $stmt->execute();
}

// Handle deleting messages
if (isset($_POST['delete'])) {
    $msg_id = $_POST['message_id'];
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ? AND sender_id = ?");
    $stmt->bind_param("ii", $msg_id, $counselee_id);
    $stmt->execute();
    $conn->query("DELETE FROM replies WHERE message_id = $msg_id");
}

// Handle marking replies as read
if (isset($_POST['mark_read'])) {
    $reply_id = $_POST['reply_id'];
    $stmt = $conn->prepare("UPDATE replies SET is_read = 1 WHERE id = ?");
    $stmt->bind_param("i", $reply_id);
    $stmt->execute();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Counselee Messaging</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #eef2f7;
            padding: 30px;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .form-box, .message-box {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 25px;
        }

        .form-box h3 {
            margin-top: 0;
        }

        label {
            font-weight: bold;
        }

        select, input[type="number"], textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        button {
            padding: 10px 18px;
            background: #0066cc;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background: #004c99;
        }

        .message-box b {
            color: #444;
        }

        .reply {
            margin-top: 15px;
            padding-left: 20px;
            border-left: 3px solid #007bff;
            background: #f0f8ff;
            padding: 10px;
            border-radius: 5px;
        }

        .reply.unread {
            background: #e0f7e9;
        }

        small i {
            color: #888;
        }
    </style>
</head>
<body>

<h2>Counselee Message Center</h2>

<!-- Message Form -->
<div class="form-box">
    <h3>Send a New Message</h3>
    <form method="POST">
        <label>Send To:</label>
        <select name="recipient_type" required>
            <option value="counselor">Counselor</option>
            <option value="admin">Admin</option>
        </select>

        <label>Recipient ID:</label>
        <input type="number" name="recipient_id" required>

        <label>Your Message:</label>
        <textarea name="message" required></textarea>

        <button type="submit" name="send">Send Message</button>
    </form>
</div>

<!-- Display Sent Messages -->
<h3>Sent Messages</h3>

<?php
$stmt = $conn->prepare("SELECT * FROM messages WHERE sender_id = ? AND sender_type = 'counselee' ORDER BY timestamp DESC");
$stmt->bind_param("i", $counselee_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()):
?>
<div class="message-box">
    <b>To:</b> <?= htmlspecialchars($row['recipient_type']) ?> (ID <?= $row['recipient_id'] ?>)<br>
    <b>Message:</b><br><?= nl2br(htmlspecialchars($row['message'])) ?><br>
    <small><i>Sent: <?= $row['timestamp'] ?></i></small>

    <?php
    $msg_id = $row['id'];
    $replies = $conn->query("SELECT * FROM replies WHERE message_id = $msg_id ORDER BY timestamp ASC");
    while ($rep = $replies->fetch_assoc()):
    ?>
        <div class="reply <?= $rep['is_read'] ? '' : 'unread' ?>">
            <b>Reply from <?= htmlspecialchars($rep['sender_type']) ?>:</b><br>
            <?= nl2br(htmlspecialchars($rep['reply_text'])) ?><br>
            <small><i><?= $rep['timestamp'] ?></i></small><br>

            <?php if (!$rep['is_read']): ?>
                <form method="POST" style="margin-top: 5px;">
                    <input type="hidden" name="reply_id" value="<?= $rep['id'] ?>">
                    <button type="submit" name="mark_read" style="background: #28a745;">Mark as Read</button>
                </form>
            <?php else: ?>
                <small style="color: green;">✔ Read</small>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>

    <form method="POST" style="margin-top: 15px;">
        <input type="hidden" name="message_id" value="<?= $row['id'] ?>">
        <button type="submit" name="delete" style="background: #dc3545;">Delete</button>
    </form>
</div>
<?php endwhile; ?>

</body>
</html>
