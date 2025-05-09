<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['counselee_id'])) {
    header("Location: clogin.php");
    exit();
}

require 'db.php'; // Database connection
$counselee_id = $_SESSION['counselee_id'];
$can_book_appointment = $_SESSION['approved'];
// Fetch the approval status from the database
// Function to get unread notification count for the counselee
function getUnreadNotificationCount($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE id = ? AND is_read = 0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    return $count;
}

// Fetch the unread notification count for the current counselee
$unread_count = getUnreadNotificationCount($counselee_id);
$can_book_appointment = $_SESSION['approved'];

// Check in counselees table first for the email
$query = "SELECT email FROM counselees WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $counselee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $email = $result->fetch_assoc()['email'];
} else {
    $email = "Email not found"; // Fallback if not found in counselee table
}

// Fetch upcoming appointments
$upcoming_query = "SELECT scheduled_date, scheduled_time 
                   FROM appointments 
                   WHERE counselee_id = ? 
                   AND status = 'scheduled' 
                   AND CONCAT(scheduled_date, ' ', scheduled_time) >= NOW()
                   ORDER BY scheduled_date ASC, scheduled_time ASC";
$upcoming_stmt = $conn->prepare($upcoming_query);
$upcoming_stmt->bind_param("i", $counselee_id);
$upcoming_stmt->execute();
$upcoming_result = $upcoming_stmt->get_result();

// Fetch notifications (appointments and approvals)
$notif_query = "SELECT scheduled_date, scheduled_time, decline_reason, status 
                FROM appointments 
                WHERE counselee_id = ? 
                AND status IN ('scheduled', 'cancelled') 
                AND notification_status = 'unread'";
$notif_stmt = $conn->prepare($notif_query);
$notif_stmt->bind_param("i", $counselee_id);
$notif_stmt->execute();
$notif_result = $notif_stmt->get_result();

$notifications = [];
while ($row = $notif_result->fetch_assoc()) {
    $notifications[] = [
        'type' => 'appointment',
        'scheduled_date' => $row['scheduled_date'],
        'scheduled_time' => $row['scheduled_time'],
        'decline_reason' => $row['decline_reason'],
        'status' => $row['status']
    ];
}

// Fetch approval notifications
$approval_query = "SELECT decline_reason FROM pending_approvals WHERE email = ? AND notification_status = 'unread'";
$approval_stmt = $conn->prepare($approval_query);
$approval_stmt->bind_param("s", $email);
$approval_stmt->execute();
$approval_result = $approval_stmt->get_result();

while ($row = $approval_result->fetch_assoc()) {
    $notifications[] = [
        'type' => 'approval',
        'decline_reason' => $row['decline_reason']
    ];
}

// Mark notifications as read after fetching
$update_query = "UPDATE appointments SET notification_status = 'read' WHERE counselee_id = ? AND notification_status = 'unread'";
$update_stmt = $conn->prepare($update_query);
$update_stmt->bind_param("i", $counselee_id);
$update_stmt->execute();

$update_approval_query = "UPDATE pending_approvals SET notification_status = 'read' WHERE email = ? AND notification_status = 'unread'";
$update_approval_stmt = $conn->prepare($update_approval_query);
$update_approval_stmt->bind_param("s", $email);
$update_approval_stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Counselee Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .container { background: white; width: 90vw; height: 90vh; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .tabs { 
            display: flex; 
            justify-content: flex-start; /* Align items to the left */
            align-items: center; /* Vertically center the buttons */
            background-color: #007bff; 
            padding: 15px; 
            border-radius: 5px;
        }
        .tabs button, .tabs a { 
            background: none; 
            border: none; 
            color: white; 
            font-size: 16px; 
            cursor: pointer; 
            padding: 10px 15px; /* Added some horizontal padding */
            text-decoration: none; 
            transition: background 0.3s;
            margin-right: 70px; /* Increased space between buttons */
            display: flex;
            align-items: center;
        }
        .tabs button:hover, .tabs a:hover, .tabs button.active {
            background-color: #0056b3;
            border-radius: 5px;
        }
        .tab-content { display: none; padding: 20px; }
        .active { display: block; }
        h4 { margin-top: 20px; font-size: 20px; }
        ul { margin: 10px 0; list-style: none; }
        li { background-color: #e0f7fa; margin: 5px 0; padding: 8px; border-radius: 5px; }
        .logout { margin-top: 0; background-color: blue; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; }
        .logout:hover { background-color: darkred; }
        .bell-container { 
    position: relative; 
    display: flex; 
    align-items: center;
}

.notification-button {
    position: relative;
    background: none;
    border: none;
    color: white;
    font-size: 16px;
    cursor: pointer;
    padding: 10px 15px;
    display: flex;
    align-items: center;
}

.notification-count {
    position: absolute;
    top: -8px;  /* Position the count above the label */
    right: -8px;  /* Position it to the right of the button */
    background: red;
    color: white;
    padding: 4px 8px;
    border-radius: 50%;
    font-size: 12px;
    font-weight: bold;
}

    </style>
</head>
<body>
    <div class="container">
        <h2>Welcome, <?= $_SESSION['counselee_name']; ?></h2>
        <div class="tabs">
            <button onclick="showTab('home')" class="active">Home</button>
            <button onclick="window.location.href='status.php'">Application Status</button>
            <button onclick="showTab('appointments')">Appointments</button>
            <button onclick="window.location.href='counseleehelp.php'" class="help-button">Help</button>
            <div class="bell-container">
                <a href="trials.php">
                    <button class="notification-button">
                        🔔 Notifications
                        <?php if ($unread_count > 0): ?>
                            <span class="notification-count"><?= $unread_count ?></span>
                        <?php endif; ?>
                    </button>
                </a>
            </div>
            <button onclick="showTab('profile')">Profile</button>
            <a href="clogout.php" class="logout">Logout</a>
        </div>

        <div id="home" class="tab-content active">
            <h3>Dashboard Overview</h3>
            <p>Welcome to your dashboard!</p>

            <h4>Upcoming Appointments</h4>
            <ul>
                <?php if ($upcoming_result->num_rows > 0): ?>
                    <?php while ($row = $upcoming_result->fetch_assoc()): ?>
                        <li>📅 Scheduled on <b><?= $row['scheduled_date']; ?></b> at <b><?= $row['scheduled_time']; ?></b></li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li>No upcoming appointments.</li>
                <?php endif; ?>
            </ul>
        </div>

        <div id="appointments" class="tab-content">
            <h3>Appointments</h3>
            <?php if ($_SESSION['approved']): ?>
    <?php
    $check_query = "SELECT status FROM appointments WHERE counselee_id = ? AND status IN ('pending', 'scheduled') AND (CONCAT(scheduled_date, ' ', scheduled_time) >= NOW() OR status = 'pending')";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $counselee_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo "<p>You cannot make another appointment request until your current one is completed or approved.</p>";
    } else {
        echo '<a href="requestappointment.php">Request an Appointment</a>';
    }
    ?>
<?php else: ?>
    <p>Your registration has not been approved yet. You cannot book an appointment.</p>
<?php endif; ?>

        </div>

        <div id="profile" class="tab-content">
            <h3>Your Profile</h3>
            <p><strong>Name:</strong> <?= $_SESSION['counselee_name']; ?></p>
            <p><strong>Email:</strong> <?= $email; ?></p>
        </div>
    </div>

    <script>
        function showTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
        }
    </script>
</body>
</html>
