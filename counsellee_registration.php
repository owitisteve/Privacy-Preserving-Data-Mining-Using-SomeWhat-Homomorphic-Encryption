<?php
session_start();

// Debugging: Check session values
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: user_login.php');
    exit();
}

include 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$valid_schools = [
    "School of Agriculture and Food Science",
    "School of Business and Economics",
    "School of Computing and Informatics",
    "School of Education",
    "School of Engineering and Architecture",
    "School of Health Sciences",
    "School of Nursing",
    "School of Pure and Applied Sciences",
    "TVET"
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $registration_number = trim($_POST['registration_number']);
    $designation = $_POST['designation'];
    $school = $_POST['school'];
    $department = trim($_POST['department']);

    $errors = [];

    // Validations
    if (empty($name)) $errors[] = "Name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    if (empty($registration_number)) $errors[] = "Registration number is required.";
    if (!in_array($designation, ['staff', 'student'])) $errors[] = "Invalid designation.";
    if (!in_array($school, $valid_schools)) $errors[] = "Invalid school selection.";
    if (empty($department)) $errors[] = "Department is required.";

    if (empty($errors)) {
        // Check if registration number already exists
        $stmt = $conn->prepare("SELECT * FROM counselees WHERE registration_number = ?");
        $stmt->bind_param("s", $registration_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('Counselee already exists!'); window.location.href = 'register_counselee.php';</script>";
        } else {
            // Insert new counselee
            $stmt = $conn->prepare("INSERT INTO counselees (name, email, registration_number, designation, school, department) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $name, $email, $registration_number, $designation, $school, $department);
            if ($stmt->execute()) {
                echo "<script>alert('Registration successful!'); window.location.href = 'user_dashboard.php';</script>";
                exit();
            } else {
                echo "Error: " . $stmt->error;
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
    <title>Register Counselee</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            width: 400px;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }
        select, input[type="text"], input[type="email"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .submit-btn {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 15px;
        }
        .submit-btn:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Register Counselee</h2>
        <form action="" method="post">
            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="registration_number">Registration Number:</label>
            <input type="text" id="registration_number" name="registration_number" required>

            <label for="designation">Designation:</label>
            <select id="designation" name="designation" required>
                <option value="">Select Designation</option>
                <option value="staff">Staff</option>
                <option value="student">Student</option>
            </select>

            <label for="school">School:</label>
            <select id="school" name="school" required>
                <option value="">Select School</option>
                <?php foreach ($valid_schools as $school): ?>
                    <option value="<?php echo htmlspecialchars($school); ?>"> <?php echo htmlspecialchars($school); ?> </option>
                <?php endforeach; ?>
            </select>

            <label for="department">Department:</label>
            <input type="text" id="department" name="department" required>

            <button type="submit" class="submit-btn">Register</button>
        </form>
    </div>
</body>
</html>
