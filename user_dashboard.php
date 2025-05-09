<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['counsellor_id'])) {
    header("Location: user_login.php");
    exit();
}

require 'db.php'; // Database connection
$counsellor_id = $_SESSION['counsellor_id']; 
function getUnreadNotificationCount($counsellor_id) {
    global $conn;

    // Count unread messages from counselees to this counselor
    
    $stmt1 = $conn->prepare("
    SELECT COUNT(*) 
    FROM messages 
    WHERE recipient_id = ? 
      AND recipient_type = 'counselor' 
      AND is_read = 0
");
$stmt1->bind_param("i", $counsellor_id);
$stmt1->execute();
$stmt1->bind_result($count1);
$stmt1->fetch();
$stmt1->close();

    
        // Get all message IDs received by the counselor
        $stmt2 = $conn->prepare("
            SELECT id 
            FROM messages 
            WHERE sender_id = ?
        ");
        $stmt2->bind_param("i", $counsellor_id);
        $stmt2->execute();
        $result = $stmt2->get_result();
    
        $messageIds = [];
        while ($row = $result->fetch_assoc()) {
            $messageIds[] = $row['id'];
        }
        $stmt2->close();
    
        // Prepare IN clause manually for the message_ids
        $count2 = 0;
        if (!empty($messageIds)) {
            $in = implode(',', array_fill(0, count($messageIds), '?'));
            $types = str_repeat('i', count($messageIds));
            $query = "
                SELECT COUNT(*) 
                FROM replies 
                WHERE sender_type = 'admin' 
                  AND is_read = 0 
                  AND message_id IN ($in)
            ";
            $stmt3 = $conn->prepare($query);
            $stmt3->bind_param($types, ...$messageIds);
            $stmt3->execute();
            $stmt3->bind_result($count2);
            $stmt3->fetch();
            $stmt3->close();
        }
    
        return $count1 + $count2;
    }


// Fetch the unread notification count for the current counselor
$unread_count = getUnreadNotificationCount($counsellor_id);
// Fetch total counselees
$counselees_result = $conn->query("SELECT COUNT(*) AS total FROM counselees");
$counselees = $counselees_result->fetch_assoc()["total"];

// Fetch recent sign-ins (last 7 days)
$recent_signins_result = $conn->query("SELECT COUNT(*) AS recent FROM counselees WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
$recent_signins = $recent_signins_result->fetch_assoc()["recent"];

// Fetch resolved cases (successful entries in family table - deleted cases)
$resolved_cases_result = $conn->query("SELECT COUNT(*) AS resolved FROM completed");
$resolved_cases = $resolved_cases_result->fetch_assoc()["resolved"];

// Fetch upcoming appointments (next 7 days)
$appointments_result = $conn->query("SELECT a.id, c.name AS counselee_name, a.requested_date 
FROM appointments a 
JOIN counselees c ON a.counselee_id = c.id
WHERE a.status = 'pending'
ORDER BY a.requested_date ASC 
LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Counselor Dashboard</title>
    <style>
      body {
    font-family: Arial, sans-serif;
    line-height: 1.6;
    background: url('images/b2.png') no-repeat center center fixed;
    background-size: cover;
    position: relative;
    height: 100vh;
    display: flex;
    flex-direction: column;
}

body::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.6); /* White overlay with 60% opacity */
    z-index: 0;
}

.container, header {
    position: relative;
    z-index: 1; /* Ensures content is above the overlay */
}

        header {
            display: flex;
            align-items: center;
            background-color: #2c3e50;
            color: #fff;
            padding: 10px 20px;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        header img {
            width: 100px;
            height: auto;
            margin-right: 20px;
        }
        nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    flex-wrap: wrap;
    gap: 20px;
}

        nav a, nav .dropdown {
            text-decoration: none;
            color: #fff;
            padding: 10px 15px;
            border-radius: 5px;
            background-color: #16a085;
            position: relative;
            transition: background-color 0.3s;
        }
        nav a:hover, nav .dropdown:hover {
            background-color: #138d75;
        }
        .dropdown {
            cursor: pointer;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color:#1abc9c;
            color: #333;
            min-width: 200px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            z-index: 1000;
        }
        .dropdown-content a {
            display: block;
            padding: 10px;
            color: #333;
            text-decoration: none;
        }
        .dropdown-content a:hover {
            background-color: #16a085;
        }
        .dropdown:hover .dropdown-content {
            display: block;
        }
       /* Dropdown Styling */
.dropdown {
    position: relative;
    display: inline-block;
}

.dropbtn {
    background-color: #16a085;
    color: white;
    padding: 10px;
    border: none;
    cursor: pointer;
    font-size: 14px;
    margin-top: 15px;
}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: #16a085; /* 🍃 Clean green background */
    min-width: 200px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); /* More realistic shadow */
    border-radius: 5px;
    z-index: 1;
}

.dropdown-content a {
    display: block;
    padding: 10px;
    color: #333;
    text-decoration: none;
}

.dropdown-content a:hover {
    background-color: #16a085;
}

/* Show dropdown on hover */
.dropdown:hover .dropdown-content {
    display: block;
}

        .container {
            padding: 20px;
        }
        .dashboard-grid {
    display: flex; /* Use flexbox instead of grid */
    justify-content: space-between; /* Space between the columns */
    gap: 20px;
}

.box {
    flex: 1; /* Ensure each box takes equal width */
    margin: 10px;
}

.overview-box {
    max-width: 300px; /* Make the overview column narrower */
}

.appointment-box {
    flex: 0; /* Make the appointment box take more space */
}

.quick-actions-box {
    max-width: 300px; /* Make the quick actions column narrower */
}

.quick-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    align-items: center;
}

.quick-actions button {
    width: 100%; /* Make buttons take full width */
    max-width: 250px; /* Limit maximum width */
    padding: 10px;
    background: #1abc9c;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s ease;
    text-align: center;
}

.quick-actions button:hover {
    background: #16a085;
}
        ul {
            list-style: none;
            padding: 0;
        }
        ul li {
            margin: 5px 0;
            padding: 10px;
            font-size: 1.5em; /* Increase font size for list items */
            color: #2c3e50;
            background: #ecf0f1;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-decoration: green;
        }
        .schedule-button {
            padding: 5px 10px;
            background: #2980b9;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .schedule-button:hover {
            background: #1f6690;
        }
        .unread-count {
            color: white;
            background-color: red;
            border-radius: 50%;
            padding: 5px 10px;
            font-size: 14px;
            position: absolute;
            top: 5px;
            right: 10px;
        }
    </style>
</head>
<body>
    <header>
        <img src="images/logo.png" alt="Logo">
        <nav>
            <a href="#" onclick="showSection('dashboard')">Dashboard</a>
            <div class="dropdown">
            <a href="#" class="dropbtn">Manage Counselee</a>
            <div class="dropdown-content">
                <a href="newlyadmitted.php">Newly Admitted Counselees</a>
                <a href="onsession.php">On-Session Counselees</a>
                <a href="completed.php">Completed Counselees</a>
            </div>
        </div>
    <a href="uppload.php" style="text-decoration: none; color: inherit;">Upload Data</a>
            <a href="cm2.php" onclick="showSection('feedback')">Feedback
            <a href="cohelp.php" class="help-button">Help</a>
            <a href="cfaq.php" class="faq-button">FAQ</a>
            <a href="cm2.php" style="position: relative; display: inline-block;">
    🔔 Notifications
    <?php if ($unread_count > 0): ?>
        <span style="
                position: absolute;
            top: -5px;
            right: -10px;
            background: red;
            color: white;
            padding: 2px 6px;
            border-radius: 50%;
            font-size: 12px
            "><?= $unread_count ?></span>
    <?php endif; ?>
</a>


            <a href="logout.php">Logout</a>
        </nav>
    </header>
    
    <div class="container">
        <div id="dashboard" class="section active box">
            <div class="dashboard-grid">
                <div class="box">
                    <h3>Overview</h3>
                    <p><strong>Total Counselees:</strong> <?php echo $counselees; ?></p>
                    <p><strong>Recent Sign-ins:</strong> <?php echo $recent_signins; ?></p>
                    <p><strong>Resolved Cases:</strong> <?php echo $resolved_cases; ?></p>
                </div>

                <div class="box">
                    <h3>Appointment Requests</h3>
                    <ul>
                        <?php while ($appointment = $appointments_result->fetch_assoc()): ?>
                            <li>
                                <?php echo $appointment['counselee_name'] . " - " . $appointment['requested_date']; ?>
                                <a href="schedule_appointment.php?id=<?php echo $appointment['id']; ?>" class="schedule-button">Schedule</a>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>

                <div class="box">
    <h3>Quick Actions</h3>
    <div class="quick-actions">
        <button onclick="location.href='approvals.php'">Approve Counselee</button>
        <button onclick="location.href='search.php'">Search Counselee</button>

        <!-- Generate Report Button with Dropdown -->
        <div class="dropdown">
            <button class="dropbtn">Generate Report</button>
            <div class="dropdown-content">
                <a href="scheduledreport.php">Scheduled Appointments</a>
                <a href="counseleeoverview.php">All Counselees</a>
                <a href="pendingappointment.php">Pending Appointments</a>
            </div>
        </div>
    </div>
</div>


    <script>
        function showSection(sectionId) {
            const sections = document.querySelectorAll('.section');
            sections.forEach(section => section.classList.remove('active'));

            const selectedSection = document.getElementById(sectionId);
            if (selectedSection) {
                selectedSection.classList.add('active');
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>