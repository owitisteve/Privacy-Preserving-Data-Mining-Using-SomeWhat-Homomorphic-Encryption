<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'db.php';

// Generate a new verification code if not submitting the form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['counsellor_login_code'] = strtoupper(substr(md5(rand()), 0, 5));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $input_code = strtoupper(trim($_POST['code'] ?? ''));

    if ($input_code !== $_SESSION['counsellor_login_code']) {
        $error = "Verification code is incorrect.";
        $_SESSION['counsellor_login_code'] = strtoupper(substr(md5(rand()), 0, 5)); // Refresh code
    } elseif (!empty($email) && !empty($password)) {
        $query = "SELECT * FROM counsellor WHERE email = ?";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $counsellor = $result->fetch_assoc();
                if (password_verify($password, $counsellor['password'])) {
                    $_SESSION['counsellor_id'] = $counsellor['id'];
                    $_SESSION['counsellor_email'] = $counsellor['email'];
                    $_SESSION['user_logged_in'] = true;
                    unset($_SESSION['counsellor_login_code']); // Clear code
                    header('Location: user_dashboard.php');
                    exit;
                } else {
                    $error = "Invalid password.";
                    $_SESSION['counsellor_login_code'] = strtoupper(substr(md5(rand()), 0, 5));
                }
            } else {
                $error = "No account found with this email.";
                $_SESSION['counsellor_login_code'] = strtoupper(substr(md5(rand()), 0, 5));
            }
        } else {
            $error = "Database error: " . $conn->error;
        }
    } else {
        $error = "Please fill in all fields.";
        $_SESSION['counsellor_login_code'] = strtoupper(substr(md5(rand()), 0, 5));
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Counsellor Login</title>
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
        .register-link {
            display: block;
            margin-top: 15px;
            text-decoration: none;
            color: #007BFF;
        }
        .register-link:hover {
            text-decoration: underline;
        }
        .verification-code {
            font-size: 18px;
            font-weight: bold;
            background-color: #eaeaea;
            padding: 6px;
            margin-top: 5px;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Counsellor Login</h2>
        <?php if (!empty($error)): ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="email" id="email" name="email" placeholder="Email" required>
            <input type="password" id="password" name="password" placeholder="Password" required>

            <label for="code">Enter Verification Code:</label>
            <div class="verification-code"><?php echo $_SESSION['counsellor_login_code']; ?></div>
            <input type="text" id="code" name="code" placeholder="Enter the code above" required>

            <button type="submit">Login</button>
        </form>
        <a href="register_counsellor.php" class="register-link">Don't have an account? Register here</a>
    </div>
</body>
</html>
