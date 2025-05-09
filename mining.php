<?php
session_start();
if (!isset($_SESSION['user_logged_in'])) {
    header('Location: admin_login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mining Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #e3f2fd;
            color: #333;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        header {
            background-color: #1565c0;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 24px;
        }

        .navbar {
    background-color: #1e88e5;
    display: flex;
    justify-content: center;
    padding: 10px;
    position: relative;
    align-items: center; /* Ensures text alignment */
}

.navbar a, .dropdown .dropbtn {
    color: white;
    padding: 12px 20px;
    text-decoration: none;
    font-size: 18px;
    transition: background-color 0.3s ease;
    display: flex;
    align-items: center; /* Aligns text vertically */
    justify-content: center;
    height: 100%; /* Ensures consistent height */
}

.dropdown .dropbtn {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%; /* Fix vertical misalignment */
    white-space: nowrap; /* Prevents line break */
}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: #1e88e5;
    min-width: 200px;
    box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
    z-index: 1;
}

.dropdown-content a {
    color: white;
    padding: 12px;
    text-decoration: none;
    display: block;
    text-align: left;
}

.dropdown-content a:hover {
    background-color: #0d47a1;
}

.dropdown:hover .dropdown-content {
    display: block;
}


        .navbar a.logout {
            background-color: #d32f2f;
            margin-left: auto;
        }

        .container {
            display: flex;
            flex: 1;
        }

        .content {
            flex: 1;
            padding: 20px;
            text-align: center;
        }

        .footer {
            background-color: #1565c0;
            color: white;
            text-align: center;
            padding: 10px;
            margin-top: auto;
        }
    </style>
</head>
<body>

<header>
    <h1>Mining Hub</h1>
</header>

<div class="navbar">
<a href="anomalies.php" class="tab-link" id="tab-anomaly" onclick="setActiveTab(this)">Anomaly Detection</a>
    <div class="dropdown">
        <a href="#" class="dropbtn">Frequent Pattern Mining</a>
        <div class="dropdown-content">
            <a href="frequent.php"> Frequency by Counseling Type </a>
            <a href="fschool.php">Frequency by School</a>
            <a href="year.php">Frequency by Year of Study</a>
        </div>
    </div>
    <a href="analysis.php" class="tab-link" id="tab-analyis" onclick="setActiveTab(this)">Counselor Sentiment Analysis</a>
    <a href="admin_logout.php" class="logout">Logout</a>
</div>

<div class="container">
    <div class="content" id="welcome-text">
        <p>Welcome to the Mining Hub. Please select a tab to begin.</p>
    </div>
</div>

<footer class="footer">
    <p>&copy; 2025 Mining Hub - Privacy-Preserving Data Mining</p>
</footer>
<script>
    // Function to set the clicked tab as active
    function setActiveTab(tab) {
        // Remove 'active' class from all tabs
        let tabs = document.querySelectorAll('.tab-link');
        tabs.forEach(function(tab) {
            tab.classList.remove('active');
        });

        // Add 'active' class to the clicked tab
        tab.classList.add('active');
    }

    // Optionally, set the active tab when the page is loaded
    document.addEventListener("DOMContentLoaded", function() {
        var currentTab = document.getElementById('tab-anomaly');
        if (currentTab) {
            setActiveTab(currentTab); // Set the default active tab
        }
    });
</script>
</body>
</html>
