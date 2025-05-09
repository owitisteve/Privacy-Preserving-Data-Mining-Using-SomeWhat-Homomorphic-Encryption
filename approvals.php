<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require 'db.php'; // Ensure this file connects to the database

// Check if the counselor is logged in
if (!isset($_SESSION['user_logged_in'])) {
    header('Location: user_login.php');
    exit();
}

// Handle duplicate check request
$duplicateMessage = '';
if (isset($_POST['check_duplicate'])) {
    $email = $_POST['email'];
    $reg_number = $_POST['registration_number'];

    $query = "SELECT id FROM counselees WHERE email = ? OR registration_number = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $email, $reg_number);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $duplicateMessage = "Duplicate entry found! Email already exists.";
    } else {
        $duplicateMessage = "No duplicates found. You can proceed with approval.";
    }
}

// Handle approval
if (isset($_POST['approve'])) {
    $id = $_POST['id'];

    $query = "SELECT * FROM pending_approvals WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Insert into `counselees`
        $insertQuery = "INSERT INTO counselees (name, email, registration_number, school, department, year_of_study, password_hash) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("sssssss", $user['name'], $user['email'], $user['registration_number'], $user['school'], 
                                $user['department'], $user['year_of_study'], $user['password_hash']);
        $insertStmt->execute();

        // Remove from `pending_approvals`
        $deleteQuery = "DELETE FROM pending_approvals WHERE id = ?";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bind_param("i", $id);
        $deleteStmt->execute();
    }
}

// Handle decline
if (isset($_POST['decline'])) {
    $id = $_POST['id'];
    $reason = $_POST['reason'];

    // Update status in `appointments` table
    $stmt = $conn->prepare("UPDATE pending_approvals SET decline_reason = ?, status = 'cancelled' WHERE id = ?");
    $stmt->bind_param("si", $reason, $id);
    $stmt->execute();
}

// Fetch pending approvals
$pendingQuery = "SELECT * FROM pending_approvals WHERE status = 'pending'";
$pendingResult = $conn->query($pendingQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Approvals</title>
    <style>
        * { font-family: Arial, sans-serif; }
        body { background-color: #f4f4f4; text-align: center; padding: 20px; }
        .container { width: 80%; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #007bff; color: white; }
        button { padding: 10px; margin: 5px; cursor: pointer; border: none; }
        .approve { background: green; color: white; }
        .decline { background: red; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Pending Approvals</h2>

        <h3>Check for Duplicate Registration</h3>
        <form method="POST">
            <input type="email" name="email" placeholder="Enter Email" required>
            <input type="text" name="registration_number" placeholder="Enter Registration Number" required>
            <button type="submit" name="check_duplicate">Check</button>
        </form>
        <?php if ($duplicateMessage): ?>
            <p style="color: <?= strpos($duplicateMessage, 'Duplicate') !== false ? 'red' : 'green'; ?>;">
                <?= $duplicateMessage; ?>
            </p>
        <?php endif; ?>

        <h3>Pending Approvals List</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Reg Number</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $pendingResult->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id']; ?></td>
                    <td><?= $row['name']; ?></td>
                    <td><?= $row['email']; ?></td>
                    <td><?= $row['registration_number']; ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $row['id']; ?>">
                            <button type="submit" name="approve" class="approve">Approve</button>
                        </form>
                        <form method="POST" style="display:inline; display: flex; align-items: center;">
    <input type="hidden" name="id" value="<?= $row['id']; ?>">
    <button type="submit" name="decline" class="decline">Decline</button>
    <select name="reason" required style="margin-left: 10px;">
        <option value="" disabled selected>Please select decline reason</option>
        <option value="Duplicate registration">Duplicate registration</option>
        <option value="Suspension not period yet over">Suspension not yet over</option>
    </select>
</form>


                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
