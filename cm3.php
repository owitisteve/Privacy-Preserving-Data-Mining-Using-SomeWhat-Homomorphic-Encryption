<?php
session_start();
$conn = new mysqli("localhost", "root", "D_vine@245", "ppdm");

if (!isset($_SESSION['admin_id'])) {
    $_SESSION['admin_id'] = 1;
    $_SESSION['user_type'] = 'admin';
}

$admin_id = $_SESSION['admin_id'];

// Handle reply
if (isset($_POST['reply'])) {
    $msg_id = $_POST['message_id'];
    $reply_text = trim($_POST['reply_text']);

    $stmt = $conn->prepare("INSERT INTO replies (message_id, sender_id, sender_type, reply_text) VALUES (?, ?, 'admin', ?)");
    $stmt->bind_param("iis", $msg_id, $admin_id, $reply_text);
    $stmt->execute();

    $conn->query("UPDATE messages SET is_read = 1 WHERE id = $msg_id");
}

// Delete message
if (isset($_POST['delete'])) {
    $msg_id = $_POST['message_id'];
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ? AND sender_id = ?");
    $stmt->bind_param("ii", $msg_id, $admin_id);
    $stmt->execute();
    $conn->query("DELETE FROM replies WHERE message_id = $msg_id");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Messaging</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #eef2f3;
            margin: 0;
            padding: 20px;
        }

        h2, h3 {
            color: #333;
        }

        .box {
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        textarea {
            width: 100%;
            height: 80px;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-family: inherit;
            resize: vertical;
        }

        button {
            background-color: #007bff;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .reply {
            margin-left: 25px;
            margin-top: 10px;
            padding-left: 10px;
            border-left: 2px solid #007bff;
            color: #333;
        }

        small i {
            color: #888;
        }

        .message-meta {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<h2>Admin Message Center</h2>

<h3>Incoming Messages</h3>

<?php
$stmt = $conn->prepare("SELECT * FROM messages WHERE recipient_id = ? AND recipient_type = 'admin' ORDER BY timestamp DESC");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$messages = $stmt->get_result();

while ($row = $messages->fetch_assoc()):
?>
    <div class="box">
        <div class="message-meta">
            <b>From <?= ucfirst($row['sender_type']) ?> (ID <?= $row['sender_id'] ?>)</b><br>
            <?= nl2br(htmlspecialchars($row['message'])) ?><br>
            <small><i>Sent: <?= $row['timestamp'] ?></i></small>
        </div>

        <?php
        $msg_id = $row['id'];
        $replies = $conn->query("SELECT * FROM replies WHERE message_id = $msg_id ORDER BY timestamp ASC");
        while ($rep = $replies->fetch_assoc()):
        ?>
            <div class="reply">
                <b><?= ucfirst($rep['sender_type']) ?>'s Reply:</b><br>
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

<h3>Your Sent Messages</h3>

<?php
$stmt = $conn->prepare("SELECT * FROM messages WHERE sender_id = ? AND sender_type = 'admin' ORDER BY timestamp DESC");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$sent = $stmt->get_result();

while ($row = $sent->fetch_assoc()):
?>
    <div class="box">
        <div class="message-meta">
            <b>To <?= ucfirst($row['recipient_type']) ?> (ID <?= $row['recipient_id'] ?>)</b><br>
            <?= nl2br(htmlspecialchars($row['message'])) ?><br>
            <small><i>Sent: <?= $row['timestamp'] ?></i></small>
        </div>

        <?php
        $msg_id = $row['id'];
        $replies = $conn->query("SELECT * FROM replies WHERE message_id = $msg_id ORDER BY timestamp ASC");
        while ($rep = $replies->fetch_assoc()):
        ?>
            <div class="reply">
                <b><?= ucfirst($rep['sender_type']) ?>'s Reply:</b><br>
                <?= nl2br(htmlspecialchars($rep['reply_text'])) ?><br>
                <small><i><?= $rep['timestamp'] ?></i></small>
            </div>
        <?php endwhile; ?>

        <form method="POST">
            <input type="hidden" name="message_id" value="<?= $row['id'] ?>">
            <button type="submit" name="delete">Delete</button>
        </form>
    </div>
<?php endwhile; ?>

</body>
</html>
