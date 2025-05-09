<?php
session_start();
include 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if admin is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: user_login.php');
    exit();
}

// Handle approval or rejection
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['approve'])) {
        $id = $_POST['id'];
        
        // Get user details from pending approvals
        $stmt = $conn->prepare("SELECT * FROM pending_approvals WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            // Insert into approved counselees table
            $stmt = $conn->prepare("INSERT INTO counselees (registration_number, name, email, password_hash, school, department, year_of_study) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $user['registration_number'], $user['name'], $user['email'], $user['password_hash'], $user['school'], $user['department'], $user['year_of_study']);
            
            $stmt->execute();

            // Remove from pending approvals
            $stmt = $conn->prepare("DELETE FROM pending_approvals WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            $message = "User approved successfully.";
        }
    } elseif (isset($_POST['decline'])) {
        $id = $_POST['id'];

        // Remove user from pending approvals
        $stmt = $conn->prepare("DELETE FROM pending_approvals WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $message = "User declined successfully.";
    }
}

// Get all pending approvals
$result = $conn->query("SELECT * FROM pending_approvals");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Approvals</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
            text-align: center;
        }
        h2 {
            margin-bottom: 10px;
        }
        .message {
            color: green;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        .btn {
            padding: 5px 10px;
            border: none;
            cursor: pointer;
            color: white;
            border-radius: 4px;
        }
        .approve-btn {
            background-color: green;
        }
        .decline-btn {
            background-color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Pending Approvals</h2>

        <?php if (!empty($message)): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>

        <table>
            <tr>
                <th>Reg No</th>
                <th>Name</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['registration_number']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="approve" class="btn approve-btn">Approve</button>
                        </form>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="decline" class="btn decline-btn">Decline</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
