<?php
session_start();
if (!isset($_SESSION['user_logged_in'])) {
    header('Location: user_login.php');
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "D_vine@245", "ppdm");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if ID is provided
if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$appointment_id = intval($_GET['id']);

// Fetch appointment details
$appointment_query = $conn->query("SELECT * FROM appointments WHERE id = $appointment_id");
$appointment = $appointment_query->fetch_assoc();

if (!$appointment) {
    die("Appointment not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $scheduled_date = $_POST['scheduled_date'];
    $scheduled_time = $_POST['scheduled_time'];

    // Update appointment status and schedule
    $stmt = $conn->prepare("UPDATE appointments SET status = 'scheduled', scheduled_date = ?, scheduled_time = ? WHERE id = ?");
    $stmt->bind_param("ssi", $scheduled_date, $scheduled_time, $appointment_id);

    if ($stmt->execute()) {
        echo "<script>alert('Appointment scheduled successfully!'); window.location='user_dashboard.php';</script>";
    } else {
        echo "Error scheduling appointment.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Appointment</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body {
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: white;
            width: 90%;
            max-width: 400px;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h2 {
            margin-bottom: 10px;
            color: #333;
        }
        p {
            font-size: 16px;
            margin-bottom: 15px;
            color: #555;
        }
        label {
            display: block;
            text-align: left;
            margin-bottom: 5px;
            font-weight: bold;
            color: #444;
        }
        input {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            font-size: 18px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Schedule Appointment</h2>
        <p><strong>Counselee ID:</strong> <?php echo htmlspecialchars($appointment['counselee_id']); ?></p>

        <form method="POST">
            <label for="date">Select Date:</label>
            <input type="date" name="scheduled_date" id="date" required>

            <label for="time">Select Time:</label>
            <input type="time" name="scheduled_time" id="time" required>

            <button type="submit">Schedule</button>
        </form>
    </div>
    <script>
    const dateInput = document.getElementById("date");
    const timeInput = document.getElementById("time");

    // Set today's date as the minimum date
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const todayStr = `${yyyy}-${mm}-${dd}`;
    dateInput.min = todayStr;

    // Disable past times if today is selected
    dateInput.addEventListener('change', function () {
        const selectedDate = new Date(this.value);
        const now = new Date();

        if (this.value === todayStr) {
            const hh = String(now.getHours()).padStart(2, '0');
            const min = String(now.getMinutes()).padStart(2, '0');
            timeInput.min = `${hh}:${min}`;
        } else {
            timeInput.removeAttribute('min');
        }
    });
</script>


</body>
</html>
