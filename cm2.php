<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
$conn = new mysqli("localhost", "root", "D_vine@245", "ppdm");

// Simulate login
$_SESSION['counselor_id'] = 2;
$_SESSION['user_type'] = 'counselor';
$counselor_id = $_SESSION['counselor_id'];

if (isset($_POST['send'])) {
    $recipient_id = $_POST['recipient_id'];
    $message = trim($_POST['message']);
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, sender_type, recipient_id, recipient_type, message) VALUES (?, 'counselor', ?, 'admin', ?)");
    $stmt->bind_param("iis", $counselor_id, $recipient_id, $message);
    $stmt->execute();
}

if (isset($_POST['reply'])) {
    $msg_id = $_POST['message_id'];
    $reply_text = trim($_POST['reply_text']);
    // Mark admin replies to this message as read
    $conn->query("UPDATE replies SET is_read = 1 WHERE message_id = $msg_id AND sender_type = 'admin'");
    // Insert counselor reply
    $stmt = $conn->prepare("INSERT INTO replies (message_id, sender_id, sender_type, reply_text) VALUES (?, ?, 'counselor', ?)");
    $stmt->bind_param("iis", $msg_id, $counselor_id, $reply_text);
    $stmt->execute();
    $conn->query("UPDATE messages SET is_read = 1 WHERE id = $msg_id");
}

if (isset($_POST['delete'])) {
    $msg_id = $_POST['message_id'];
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ? AND sender_id = ?");
    $stmt->bind_param("ii", $msg_id, $counselor_id);
    $stmt->execute();
    $conn->query("DELETE FROM replies WHERE message_id = $msg_id");
}

if (isset($_POST['mark_reply_read'])) {
    $reply_id = $_POST['reply_id'];
    $conn->query("UPDATE replies SET is_read = 1 WHERE id = $reply_id");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Counselor Messaging</title>
    <style>
        body {
            font-family: "Segoe UI", sans-serif;
            background: #f0f2f5;
            padding: 30px;
            color: #333;
        }
        h2, h3, h4 {
            color: #2c3e50;
        }
        .container {
            max-width: 900px;
            margin: auto;
        }
        .form-box, .box {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 25px;
        }
        input[type="number"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }
        button {
            background-color: #3498db;
            border: none;
            padding: 10px 20px;
            color: white;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
        }
        button:hover {
            background-color: #2980b9;
        }
        .reply {
            margin-left: 25px;
            border-left: 3px solid #3498db;
            padding-left: 10px;
            margin-top: 10px;
            font-size: 14px;
            color: #34495e;
        }
        small {
            color: #777;
        }
        .delete-btn {
            background-color: #e74c3c;
            margin-top: 10px;
        }
        .delete-btn:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Counselor Message Center</h2>

    <!-- Message to Admin -->
    <div class="form-box">
        <h4>Send Message to Admin</h4>
        <form method="POST">
            <label>Admin ID:</label>
            <input type="number" name="recipient_id" required>
            <label>Your Message:</label>
            <textarea name="message" required placeholder="Type your message here..."></textarea>
            <button type="submit" name="send">Send to Admin</button>
        </form>
    </div>

    <!-- Messages from Counselees -->
    <h3>Messages from Counselees</h3>
    <?php
    $stmt = $conn->prepare("SELECT * FROM messages WHERE recipient_id = ? AND recipient_type = 'counselor' ORDER BY timestamp DESC");
    $stmt->bind_param("i", $counselor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()):
    ?>
        <div class="box">
            <b>From Counselee (ID <?= $row['sender_id'] ?>)</b><br>
            <?= nl2br(htmlspecialchars($row['message'])) ?><br>
            <small><i>Sent: <?= $row['timestamp'] ?></i></small>

            <?php
            $msg_id = $row['id'];
            $replies = $conn->query("SELECT * FROM replies WHERE message_id = $msg_id ORDER BY timestamp ASC");
            while ($rep = $replies->fetch_assoc()):
            ?>
                <div class="reply">
                    <b>Reply from <?= ucfirst($rep['sender_type']) ?>:</b><br>
                    <?= nl2br(htmlspecialchars($rep['reply_text'])) ?><br>
                    <small><i><?= $rep['timestamp'] ?></i></small>
                </div>
            <?php endwhile; ?>

            <form method="POST">
                <input type="hidden" name="message_id" value="<?= $msg_id ?>">
                <textarea name="reply_text" placeholder="Reply here..." required></textarea>
                <button type="submit" name="reply">Reply</button>
            </form>
        </div>
    <?php endwhile; ?>

    <!-- Sent Messages to Admin -->
    <h3>Your Sent Messages to Admin</h3>
    <?php
    $stmt = $conn->prepare("SELECT * FROM messages WHERE sender_id = ? AND sender_type = 'counselor' AND recipient_type = 'admin' ORDER BY timestamp DESC");
    $stmt->bind_param("i", $counselor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()):
    ?>
        <div class="box">
            <b>To Admin (ID <?= $row['recipient_id'] ?>)</b><br>
            <?= nl2br(htmlspecialchars($row['message'])) ?><br>
            <small><i>Sent: <?= $row['timestamp'] ?></i></small>

            <?php
            $msg_id = $row['id'];
            $replies = $conn->query("SELECT * FROM replies WHERE message_id = $msg_id ORDER BY timestamp ASC");
            while ($rep = $replies->fetch_assoc()):
                $is_admin_reply = $rep['sender_type'] === 'admin';
            ?>
                <div class="reply" style="<?= $is_admin_reply && $rep['is_read'] == 0 ? 'border-left: 3px solid #f39c12;' : '' ?>">
                    <b>Reply from <?= ucfirst($rep['sender_type']) ?>:</b><br>
                    <?= nl2br(htmlspecialchars($rep['reply_text'])) ?><br>
                    <small><i><?= $rep['timestamp'] ?></i></small>

                    <?php if ($is_admin_reply && $rep['is_read'] == 0): ?>
                        <form method="POST" style="margin-top: 5px;">
                            <input type="hidden" name="reply_id" value="<?= $rep['id'] ?>">
                            <button type="submit" name="mark_reply_read" style="background-color: #27ae60;">Mark as Read</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>

            <form method="POST">
                <input type="hidden" name="message_id" value="<?= $msg_id ?>">
                <button class="delete-btn" type="submit" name="delete">Delete</button>
            </form>
        </div>
    <?php endwhile; ?>
</div>
</body>
</html>
