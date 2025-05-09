<?php
// Database Connection (replace with your actual credentials)
$host = 'localhost';
$dbname = 'ppdm';
$username = 'root';
$password = 'D_vine@245';

try {
    // Set up PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set PDO to throw exceptions on errors
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle connection error
    echo "Connection failed: " . $e->getMessage();
    exit;
}

// Function to get unread notification count
function getUnreadNotificationCount($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

// Function to mark notification as read after user clicks it
function markNotificationAsRead($notification_id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
    $stmt->execute([$notification_id]);
}

// Function to fetch all notifications for a user
function getNotifications($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// Example: Get unread notifications count for a specific user (user with ID 1)
$user_id = 1; // Replace with the actual user ID
$unread_count = getUnreadNotificationCount($user_id);
$notifications = getNotifications($user_id);

// If a notification is clicked, mark it as read
if (isset($_GET['mark_as_read'])) {
    $notification_id = $_GET['mark_as_read'];
    markNotificationAsRead($notification_id);
    header("Location: help.php"); // Reload the page after marking as read
    exit();  // Ensure that the script stops here and does not continue processing
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification System</title>
    <style>
        /* Style for the notification bell */
        .notification-bell {
            position: relative;
            font-size: 30px;
            cursor: pointer;
        }

        /* Style for the notification count */
        .notification-count {
            position: absolute;
            top: -5px;
            right: -10px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 5px;
            font-size: 12px;
        }

        /* Style for the notification list */
        .notification-list {
            list-style: none;
            padding: 0;
            max-width: 300px;
            margin-top: 20px;
            border: 1px solid #ddd;
        }

        .notification-item {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            cursor: pointer;
        }

        .notification-item.read {
            background-color: #f0f0f0;
        }

        .notification-item:hover {
            background-color: #e2e2e2;
        }
    </style>
</head>
<body>
    <!-- Notification Bell -->
    <div class="notification-bell">
        <i class="fas fa-bell"></i>
        <?php if ($unread_count > 0): ?>
            <div class="notification-count"><?php echo $unread_count; ?></div>
        <?php endif; ?>
    </div>

    <!-- Notifications Dropdown (or List) -->
    <ul class="notification-list">
    <?php foreach ($notifications as $notification): ?>
        <?php if (!$notification['is_read']): ?>
            <li class="notification-item">
                <form method="GET" action="help.php" style="margin: 0;">
                    <input type="hidden" name="mark_as_read" value="<?php echo $notification['id']; ?>">
                    <button type="submit" style="background: none; border: none; color: inherit; text-align: left; padding: 0; width: 100%; cursor: pointer;">
                        You have a new message, click to open
                    </button>
                </form>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php if (empty(array_filter($notifications, fn($n) => !$n['is_read']))): ?>
        <li class="notification-item read" style="text-align:center; color: gray;">No new messages</li>
    <?php endif; ?>
</ul>



    <!-- Font Awesome CDN for Bell Icon -->
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html>
