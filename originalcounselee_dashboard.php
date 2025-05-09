<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['counselee_id'])) {
    header("Location: clogin.php");
    exit();
}

require 'db.php'; // Ensure this file connects to the database

$counselee_id = $_SESSION['counselee_id'];
$can_book_appointment = $_SESSION['approved'];

// Fetch email
$query = "SELECT email FROM counselees WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $counselee_id);
$stmt->execute();
$result = $stmt->get_result();
$email = ($result->num_rows > 0) ? $result->fetch_assoc()['email'] : "Email not found";

// Fetch unread notifications
$notif_query = "SELECT scheduled_date, scheduled_time FROM appointments 
                WHERE counselee_id = ? AND status = 'scheduled' AND notification_status = 'unread'";
$notif_stmt = $conn->prepare($notif_query);
$notif_stmt->bind_param("i", $counselee_id);
$notif_stmt->execute();
$notif_result = $notif_stmt->get_result();

$notifications = [];
while ($row = $notif_result->fetch_assoc()) {
    $notifications[] = $row;
}
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
        .container { background: white; width: 90vw; height: 90vh; display: flex; flex-direction: column; align-items: center; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .tabs { display: flex; justify-content: space-around; width: 100%; background-color: #007bff; padding: 15px; border-radius: 5px; }
        .tabs button { background: none; border: none; color: white; font-size: 16px; cursor: pointer; padding: 10px; transition: background 0.3s; }
        .tabs button:hover, .tabs button.active { background-color: #0056b3; border-radius: 5px; }
        .tab-content { display: none; width: 100%; height: 20vh; justify-content: center; align-items: center; text-align: center; flex-direction: column; }
        .active { display: flex; }
        .logout { margin-top: 50px; display: inline-block; background-color: blue; color: white; padding: 10px; text-decoration: none; border-radius: 5px; }
        .logout:hover { background-color: darkred; }
        .notif-icon { position: relative; cursor: pointer; }
        .notif-bell { font-size: 24px; color: white; }
        .notif-badge { background: red; color: white; font-size: 12px; border-radius: 50%; padding: 4px 8px; position: absolute; top: -5px; right: -5px; display: <?= count($notifications) ? 'inline' : 'none'; ?>; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Welcome, <?= $_SESSION['counselee_name']; ?></h2>

        <div class="tabs">
            <button onclick="showTab('home')" class="active">Home</button>
            <button onclick="showTab('appointments')">Appointments</button>
            <button onclick="showTab('profile')">Profile</button>
            <div class="notif-icon" onclick="showTab('notifications')" id="notif-btn">
                🔔<span class="notif-badge" id="notif-count"><?= count($notifications); ?></span>
                <span>Notifications</span>
            </div>
        </div>

        <div id="home" class="tab-content active">
            <h3>Dashboard Overview</h3>
            <p>Welcome to your dashboard!</p>
        </div>

        <div id="appointments" class="tab-content">
            <h3>Appointments</h3>
            <?php if ($can_book_appointment): ?>
                <a href="requestappointment.php">Request an Appointment</a>
            <?php else: ?>
                <p>Your registration is pending approval. You cannot book an appointment yet.</p>
            <?php endif; ?>
        </div>

        <div id="profile" class="tab-content">
            <h3>Your Profile</h3>
            <p><strong>Name:</strong> <?= $_SESSION['counselee_name']; ?></p>
            <p><strong>Email:</strong> <?= $email; ?></p>
        </div>

        <div id="notifications" class="tab-content">
            <h3>Notifications</h3>
            <ul id="notif-list">
                <?php if (count($notifications) > 0): ?>
                    <?php foreach ($notifications as $notif): ?>
                        <li>📅 Your appointment is scheduled on <b><?= $notif['scheduled_date']; ?></b> at <b><?= $notif['scheduled_time']; ?></b></li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>No new notifications.</li>
                <?php endif; ?>
            </ul>
        </div>

        <a href="clogout.php" class="logout">Logout</a>
    </div>

    <script>
        function showTab(tabId) {
            var tabs = document.getElementsByClassName('tab-content');
            var buttons = document.querySelectorAll('.tabs button, .notif-icon');

            for (var i = 0; i < tabs.length; i++) {
                tabs[i].classList.remove('active');
            }

            for (var j = 0; j < buttons.length; j++) {
                buttons[j].classList.remove('active');
            }

            document.getElementById(tabId).classList.add('active');
            if (tabId === 'notifications') {
                fetch('mark_notifications.php')
                    .then(response => response.text())
                    .then(() => {
                        document.getElementById('notif-count').style.display = 'none';
                    });
            }
        }

        function fetchNotifications() {
            fetch('fetch_notifications.php')
                .then(response => response.json())
                .then(data => {
                    let notifCount = data.length;
                    let notifBadge = document.getElementById('notif-count');
                    let notifList = document.getElementById('notif-list');

                    if (notifCount > 0) {
                        notifBadge.style.display = 'inline';
                        notifBadge.innerText = notifCount;
                    } else {
                        notifBadge.style.display = 'none';
                    }

                    notifList.innerHTML = data.length > 0 ? data.map(notif => `<li>📅 Your appointment is scheduled on <b>${notif.scheduled_date}</b> at <b>${notif.scheduled_time}</b></li>`).join('') : '<li>No new notifications.</li>';
                });
        }

        setInterval(fetchNotifications, 5000);
    </script>
</body>
</html>
