<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Database connection
include 'modal.php';
$conn = new mysqli("localhost", "root", "D_vine@245", "ppdm");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Promote logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['promote_id'])) {
    $id = $_POST['promote_id'];

    // Fetch the counselee's data
    $fetch = $conn->query("SELECT * FROM counselees WHERE id = $id");
    if ($fetch && $fetch->num_rows > 0) {
        $data = $fetch->fetch_assoc();

        // Check if email already exists in completed table
$email = $data['email'];
$checkQuery = $conn->prepare("SELECT id FROM completed WHERE email = ?");
$checkQuery->bind_param("s", $email);
$checkQuery->execute();
$checkResult = $checkQuery->get_result();

if ($checkResult->num_rows == 0) {
    // Insert into completed table if email does not exist
    $stmt = $conn->prepare("INSERT INTO completed (name, email, registration_number, designation, school, department, year_of_study, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", 
        $data['name'], 
        $data['email'], 
        $data['registration_number'], 
        $data['designation'], 
        $data['school'], 
        $data['department'], 
        $data['year_of_study'], 
        $data['created_at']
    );
    $stmt->execute();
} else {
    // Update the record in completed table if email exists
    $updateStmt = $conn->prepare("UPDATE completed SET name = ?, registration_number = ?, designation = ?, school = ?, department = ?, year_of_study = ?, created_at = ? WHERE email = ?");
    $updateStmt->bind_param("ssssssss", 
        $data['name'], 
        $data['registration_number'], 
        $data['designation'], 
        $data['school'], 
        $data['department'], 
        $data['year_of_study'], 
        $data['created_at'],
        $data['email']
    );
    $updateStmt->execute();
}
        // Delete from counselees
        $conn->query("DELETE FROM counselees WHERE id = $id");

         showModal("Counselee promoted successfully.");
    }
}

// Fetch counselees registered before today
$query = "SELECT * FROM counselees WHERE DATE(created_at) < CURDATE()";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Counselee Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .container {
            padding: 20px;
        }

        h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #16a085;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        button {
            padding: 8px 16px;
            background-color: #16a085;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #1abc9c;
        }

        .success-message {
            color: green;
            text-align: center;
            font-size: 1.2em;
            margin-bottom: 20px;
        }

        .back-button {
            margin: 20px 0;
            display: inline-block;
            text-decoration: none;
        }

        .download-button {
            margin-bottom: 10px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="user_dashboard.php" class="back-button">
            <button>Back to Dashboard</button>
        </a>

        <form action="download_on_session.php" method="post" class="download-button">
            <button type="submit">Download List</button>
        </form>

        <h2>Counselees on Session</h2>

        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Reg No</th>
                <th>Admission Date</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['name'] ?></td>
                <td><?= $row['email'] ?></td>
                <td><?= $row['registration_number'] ?></td>
                <td><?= $row['created_at'] ?></td>
                <td>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to promote this counselee?');">
                        <input type="hidden" name="promote_id" value="<?= $row['id'] ?>">
                        <button type="submit">Promote</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>

<?php $conn->close(); ?>
