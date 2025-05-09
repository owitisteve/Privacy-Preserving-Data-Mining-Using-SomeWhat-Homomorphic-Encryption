<?php
session_start();
include 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Generate a new login code for each page load (unless it's a form submission)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['login_code'] = strtoupper(substr(md5(rand()), 0, 5));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $registration_number = trim($_POST['registration_number']);
    $password = $_POST['password'];
    $input_code = strtoupper(trim($_POST['code'] ?? ''));

    if ($input_code !== $_SESSION['login_code']) {
        $error = "Verification code is incorrect.";
        $_SESSION['login_code'] = strtoupper(substr(md5(rand()), 0, 5)); // refresh code
    } elseif (empty($registration_number) || empty($password)) {
        $error = "All fields are required.";
    } else {
        // Check in pending approvals first
        $stmt = $conn->prepare("SELECT * FROM pending_approvals WHERE registration_number = ?");
        $stmt->bind_param("s", $registration_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password_hash'])) {
                $_SESSION['counselee_id'] = $row['id'];
                $_SESSION['counselee_name'] = $row['name'];
                $_SESSION['approved'] = false;
                unset($_SESSION['login_code']); // clear code after successful login
                header("Location: counselee_dashboard.php");
                exit();
            } else {
                $error = "Invalid password.";
                $_SESSION['login_code'] = strtoupper(substr(md5(rand()), 0, 5));
            }
        } else {
            // Check in the approved counselees table
            $stmt = $conn->prepare("SELECT * FROM counselees WHERE registration_number = ?");
            $stmt->bind_param("s", $registration_number);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                if (password_verify($password, $row['password_hash'])) {
                    $_SESSION['counselee_id'] = $row['id'];
                    $_SESSION['counselee_name'] = $row['name'];
                    $_SESSION['approved'] = true;
                    unset($_SESSION['login_code']);
                    header("Location: counselee_dashboard.php");
                    exit();
                } else {
                    $error = "Invalid password.";
                    $_SESSION['login_code'] = strtoupper(substr(md5(rand()), 0, 5));
                }
            } else {
                $error = "No account found. Please register.";
                $_SESSION['login_code'] = strtoupper(substr(md5(rand()), 0, 5));
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Counselee Login</title>
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
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 350px;
            text-align: center;
        }
        h2 {
            margin-bottom: 10px;
        }
        label {
            font-weight: bold;
            display: block;
            text-align: left;
            margin-top: 10px;
        }
        input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .submit-btn {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 15px;
        }
        .submit-btn:hover {
            background-color: #0056b3;
        }
        .register-link {
            display: block;
            margin-top: 10px;
            font-size: 14px;
        }
        .register-link a {
            color: #007bff;
            text-decoration: none;
        }
        .register-link a:hover {
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
    <div class="container">
        <h2>Counselee Login</h2>
        <?php if (!empty($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form action="" method="post">
            <label for="registration_number">Registration Number:</label>
            <input type="text" id="registration_number" name="registration_number" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="code">Enter Verification Code:</label>
            <div class="verification-code"><?php echo $_SESSION['login_code']; ?></div>
            <input type="text" id="code" name="code" required>

            <button type="submit" class="submit-btn">Login</button>
        </form>
        <p class="register-link">Not registered? <a href="cregister.php">Register here</a></p>
    </div>
</body>
</html>
