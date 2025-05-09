<?php
session_start();
include 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// List of valid genders
$valid_genders = ['male', 'female', 'other'];

$success_message = null; // Initialize success message
$errors = []; // Initialize errors array

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $gender = $_POST['gender'];
    $password = trim($_POST['password']);
    $confirm_password = $_POST['confirm_password'];

    // Validate name
    if (empty($name)) {
        $errors[] = "Name is required.";
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format. Example: steve@gmail.com.";
    }

    // Validate gender
    if (!in_array($gender, $valid_genders)) {
        $errors[] = "Invalid gender selection.";
    }

    // Validate password
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,}$/', $password)) {
        $errors[] = "Password must be at least 8 characters long, include one uppercase letter, one lowercase letter, one number, and one special character.";
    }

    // Validate password confirmation
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert data into the counsellor table
        $stmt = $conn->prepare("INSERT INTO counsellor (name, email, gender, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $gender, $hashed_password);

        if ($stmt->execute()) {
            $success_message = "Registration successful!";
        } else {
            $errors[] = "Error during registration. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Counsellor Registration</title>
    <script>
        // Show the success modal and redirect to login
        function showSuccessModal() {
            const successModal = document.getElementById('successModal');
            successModal.style.display = 'block';
        }

        function showErrorModal() {
            const errorModal = document.getElementById('errorModal');
            errorModal.style.display = 'block';
        }

        function closeModal() {
            document.getElementById('errorModal').style.display = 'none';
        }

        function redirectToLogin() {
            window.location.href = 'user_login.php';
        }
    </script>
</head>
<body>
    <h2>Counsellor Registration</h2>

    <!-- Form -->
    <form method="POST">
        <label>Name:</label><br>
        <input type="text" name="name" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Gender:</label><br>
        <select name="gender" required>
            <option value="">--Select Gender--</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
        </select><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <label>Confirm Password:</label><br>
        <input type="password" name="confirm_password" required><br><br>

        <button type="submit">Register</button>
    </form>

    <!-- Error Modal -->
    <div id="errorModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); text-align: center;">
        <div style="background: white; padding: 20px; margin: 20% auto; width: 50%; border-radius: 10px;">
            <h3>Error</h3>
            <ul id="errorList" style="color: red; list-style-type: none;">
                <?php
                if (!empty($errors)) {
                    foreach ($errors as $error) {
                        echo "<li>$error</li>";
                    }
                }
                ?>
            </ul>
            <button onclick="closeModal()">Close</button>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); text-align: center;">
        <div style="background: white; padding: 20px; margin: 20% auto; width: 50%; border-radius: 10px;">
            <h3><?php echo isset($success_message) ? $success_message : ''; ?></h3>
            <button onclick="redirectToLogin()">OK</button>
        </div>
    </div>

    <!-- Show appropriate modal -->
    <?php if (!empty($errors)) { ?>
        <script>showErrorModal();</script>
    <?php } elseif (isset($success_message)) { ?>
        <script>showSuccessModal();</script>
    <?php } ?>
</body>
</html>
