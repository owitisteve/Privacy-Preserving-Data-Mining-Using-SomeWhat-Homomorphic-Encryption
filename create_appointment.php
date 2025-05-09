<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['user_logged_in'])) {
    header('Location: user_login.php');
    exit();
}
// Database connection
$servername = "localhost"; 
$username = "root"; 
$password = "D_vine@245"; 
$dbname = "ppdm";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$admin_created = false; // Flag to track if the admin was created

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_username = $_POST['username'];
    $admin_password = $_POST['password'];

    // Hash the password before storing it in the database
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

    // Debugging: Check the hashed password
    echo "<pre>";
    echo "Hashed Password: " . $hashed_password;
    echo "</pre>";

    // Insert the admin data into the database using a prepared statement
    $sql = "INSERT INTO admin (username, password) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param('ss', $admin_username, $hashed_password);
        
        if ($stmt->execute()) {
            $admin_created = true; // Admin was successfully created
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error: " . $conn->error;
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin</title>
    <link rel="stylesheet" href="style.css">
    <script>
        // Function to redirect after the modal is closed
        function redirectToLogin() {
            window.location.href = 'admin_login.php';
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Create Admin</h2>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <label for="username">Admin Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Create Admin</button>
        </form>
    </div>

    <!-- Modal -->
    <?php if ($admin_created): ?>
    <div id="successModal" style="display: block;">
        <div class="modal-content">
            <h2>Success!</h2>
            <p>The admin account has been created successfully.</p>
            <button onclick="redirectToLogin()">OK</button>
        </div>
    </div>
    <style>
        #successModal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: #fff;
            padding: 30px;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .modal-content h2 {
            color: #2c3e50;
        }
        .modal-content button {
            padding: 10px 20px;
            background-color: #1abc9c;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .modal-content button:hover {
            background-color: #16a085;
        }
    </style>
    <?php endif; ?>
</body>
</html>
