<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'db.php';

// Generate a new verification code if not a POST submission
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['admin_login_code'] = strtoupper(substr(md5(rand()), 0, 5));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username   = trim($_POST['username']);
    $password   = trim($_POST['password']);
    $input_code = strtoupper(trim($_POST['code'] ?? ''));

    // Check verification code first
    if ($input_code !== ($_SESSION['admin_login_code'] ?? '')) {
        $error = "Verification code is incorrect.";
        // Refresh code for next try
        $_SESSION['admin_login_code'] = strtoupper(substr(md5(rand()), 0, 5));
    }
    // Then check credentials
    elseif (!empty($username) && !empty($password)) {
        $query = "SELECT * FROM admin WHERE username = ?";
        $stmt  = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();
                if (password_verify($password, $admin['password'])) {
                    // Successful login
                    $_SESSION['admin_id']        = $admin['id'];
                    $_SESSION['username']        = $admin['username'];
                    $_SESSION['user_logged_in']  = true;
                    unset($_SESSION['admin_login_code']);
                    header('Location: admin_dashboard.php');
                    exit();
                } else {
                    $error = 'Invalid password.';
                    $_SESSION['admin_login_code'] = strtoupper(substr(md5(rand()), 0, 5));
                }
            } else {
                $error = 'Invalid username.';
                $_SESSION['admin_login_code'] = strtoupper(substr(md5(rand()), 0, 5));
            }
        } else {
            $error = 'Database error: ' . $conn->error;
            $_SESSION['admin_login_code'] = strtoupper(substr(md5(rand()), 0, 5));
        }
    } else {
        $error = "Please fill in all fields.";
        $_SESSION['admin_login_code'] = strtoupper(substr(md5(rand()), 0, 5));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Login</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .login-container {
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      width: 300px;
      text-align: center;
    }
    .login-container h2 {
      margin-bottom: 20px;
      color: #333;
    }
    .login-container input {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    .login-container button {
      width: 100%;
      padding: 10px;
      background: #007BFF;
      border: none;
      color: white;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
    }
    .login-container button:hover {
      background: #0056b3;
    }
    .error-message {
      color: red;
      margin-bottom: 10px;
    }
    .verification-code {
      font-size: 18px;
      font-weight: bold;
      background-color: #eaeaea;
      padding: 6px;
      margin: 10px 0;
      letter-spacing: 2px;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <h2>Admin Login</h2>
    <?php if (!empty($error)): ?>
      <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="POST" action="">
      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>

      <label for="code">Enter Verification Code:</label>
      <div class="verification-code">
        <?php echo htmlspecialchars($_SESSION['admin_login_code'] ?? ''); ?>
      </div>
      <input type="text" id="code" name="code" placeholder="Enter the code above" required>

      <button type="submit">Login</button>
    </form>
  </div>
</body>
</html>
