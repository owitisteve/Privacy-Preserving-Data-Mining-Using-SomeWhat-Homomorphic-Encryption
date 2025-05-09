<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['user_logged_in'])) {
    header('Location: admin_login.php');
    exit();
}

include 'db.php';

// Assuming admin ID is stored in session
$admin_id = $_SESSION['admin_id']; // Set this during login

function getAdminNotificationCount($conn, $admin_id) {
    $count = 0;
    $query = "
        SELECT COUNT(*) as unread_count
        FROM messages 
        WHERE recipient_id = ? 
          AND recipient_type = 'admin' 
          AND is_read = 0
    ";
    
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $admin_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result(); // safer and avoids bind_result issue
            if ($row = $result->fetch_assoc()) {
                $count = $row['unread_count'];
            }
        }
        $stmt->close();
    }
    
    return $count;
}

$unread_count = getAdminNotificationCount($conn, $admin_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #0073e6;
            padding: 15px 20px;
            color: white;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }
        .logo {
            height: 50px;
        }
        .logout {
            text-decoration: none;
            color: white;
            font-weight: bold;
            background: #d9534f;
            padding: 8px 15px;
            border-radius: 5px;
            transition: 0.3s;
        }
        .logout:hover {
            background: #c9302c;
        }
        .sidebar {
            width: 250px;
            background: #333;
            color: white;
            position: fixed;
            top: 130px;
            left: 0;
            height: calc(100% - 60px);
            padding-top: 20px;
        }
        .sidebar a {
            display: block;
            color: white;
            padding: 15px;
            text-decoration: none;
            border-bottom: 1px solid #444;
            transition: 0.3s;
        }
        .sidebar a:hover {
            background: #555;
        }
        .container {
            margin-left: 270px;
            padding: 80px 20px 20px;
        }
        /* Hide dropdown initially */
        .dropdown-container {
            display: none;
            margin-top: 20px;
        }
        .dropdown-container select {
            width: 250px;
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            background-color: #fff;
            border: 1px solid #ccc;
            margin-bottom: 20px;
        }
        /* Active state for Query Execution tab */
        .active {
            background-color: #444;
        }
    </style>
    <script>
        function toggleDropdown() {
            var dropdown = document.getElementById('report-dropdown');
            dropdown.style.display = (dropdown.style.display === 'none' || dropdown.style.display === '') ? 'block' : 'none';
        }

        document.addEventListener("DOMContentLoaded", function () {
            var querySelect = document.getElementById("report-select");
            querySelect.addEventListener("change", function () {
                var selectedValue = this.value;

                if (selectedValue === "counselee_overview") {
                    window.location.href = "counseleeoverview.php";
                } else if (selectedValue === "anomaly_report") {
                    window.location.href = "anomalyreport.php";
                } else if (selectedValue === "frequent_counseling_type") {
                    window.location.href = "frequent.php";
                } else if (selectedValue === "frequency_by_school") {
                    window.location.href = "fschool.php";
                } else if (selectedValue === "frequency_by_year_of_study") {
                    window.location.href = "year.php";
                }
            });
        });
    </script>
    <script>
        // Function to fetch the unread message count from the adminnotification.php file
        function updateNotificationCount() {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "adminnotifications.php", true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    // Update the notification count in the sidebar
                    var unreadCount = xhr.responseText;
                    document.getElementById("notification-count").innerText = unreadCount;
                }
            };
            xhr.send();
        }

        // Update the notification count when the page loads
        window.onload = function () {
            updateNotificationCount();
        };

        // Optionally, you can update the count every few seconds
        setInterval(updateNotificationCount, 30000); // Update every 30 seconds
    </script>
</head>
<body>
    <header>
        <img src="images/logo.png" alt="Logo" class="logo">
        <a href="admin_logout.php" class="logout">Logout</a>
    </header>

    <div class="sidebar">
        <a href="#">Dashboard</a>
        <a href="mining.php">Mining Operations</a>
        <a href="javascript:void(0);" onclick="toggleDropdown()" class="active">Reports</a>
        <a href="adminhelp.php">Help</a>
        <a href="cm3.php" style="position: relative;">
        🔔 Notifications
        <?php if ($unread_count > 0): ?>
            <span style="
                position: absolute;
                top: 10px;
                right: 15px;
                background: red;
                color: white;
                padding: 2px 6px;
                border-radius: 50%;
                font-size: 12px;
            "><?= $unread_count ?></span>
        <?php endif; ?>
    </a>
    </div>

    <div class="container">
        <h2>Welcome to the Analytics Dashboard</h2>
        <div id="report-dropdown" class="dropdown-container">
            <label for="report-select">Select Report:</label>
            <select id="report-select" name="report">
                <option value="">-- Select Report --</option>
                <option value="counselee_overview">Counselee Overview</option>
                <option value="frequent_counseling_type">Frequent Counseling Type Report</option>
                <option value="frequency_by_school">Frequency by School Report</option>
                <option value="frequency_by_year_of_study">Frequency by Year of Study Report</option>
            </select>
        </div>
    </div>
</body>
</html>