<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: user_login.php');
    exit();
}

include 'db.php';

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

// Initialize counselee as null
$counselee = null;

// Step 1: Search for counselee by Registration Number
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search_registration_number'])) {
    $registration_number = trim($_POST['search_registration_number']);
    
    // Fetch the counselee's data from the database
    $stmt = $conn->prepare("SELECT * FROM counselees WHERE registration_number = ?");
    $stmt->bind_param("s", $registration_number);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if counselee data is found
    if ($result->num_rows === 0) {
        echo "<script>alert('Counselee not found!');</script>";
    } else {
        $counselee = $result->fetch_assoc(); // Assign the counselee data to $counselee
    }
}

// Step 2: Update counselee details
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_counselee']) && $counselee !== null) {
    // Get the updated form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $designation = $_POST['designation'];
    $school = $_POST['school'];
    $department = trim($_POST['department']);
    $registration_number = $counselee['registration_number']; // Keep the original registration number

    // Validation of form fields
    $errors = [];
    if (empty($name)) $errors[] = "Name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    if (!in_array($designation, ['staff', 'student'])) $errors[] = "Invalid designation.";
    if (!in_array($school, $valid_schools)) $errors[] = "Invalid school selection.";
    if (empty($department)) $errors[] = "Department is required.";

    if (empty($errors)) {
        // Prepare the update query with dynamic fields
        $stmt = $conn->prepare("UPDATE counselees SET name = ?, email = ?, designation = ?, school = ?, department = ? WHERE registration_number = ?");
        $stmt->bind_param("ssssss", $name, $email, $designation, $school, $department, $registration_number);

        // Execute the update query
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo "<script>alert('Counselee updated successfully!'); window.location.href = 'user_dashboard.php';</script>";
            } else {
                echo "<script>alert('No changes detected or data was not modified.');</script>";
            }
        } else {
            echo "Error executing query: " . $stmt->error;
        }
    } else {
        // Show validation errors
        foreach ($errors as $error) {
            echo "<script>alert('$error');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Counselee</title>
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
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Counselee</h2>
        <!-- Step 1: Search Form to find Counselee by Registration Number -->
        <form method="post">
            <label for="search_registration_number">Enter Registration Number:</label>
            <input type="text" id="search_registration_number" name="search_registration_number" required>
            <button type="submit" class="submit-btn">Search</button>
        </form>

        <?php if ($counselee !== null): ?>
        <!-- Step 2: Edit Form to update Counselee Details -->
        <form action="" method="post">
            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($counselee['name']); ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($counselee['email']); ?>" required>

            <label for="registration_number">Registration Number:</label>
            <input type="text" id="registration_number" name="registration_number" value="<?php echo htmlspecialchars($counselee['registration_number']); ?>" disabled>

            <label for="designation">Designation:</label>
            <select id="designation" name="designation" required>
                <option value="staff" <?php echo $counselee['designation'] == 'staff' ? 'selected' : ''; ?>>Staff</option>
                <option value="student" <?php echo $counselee['designation'] == 'student' ? 'selected' : ''; ?>>Student</option>
            </select>

            <label for="school">School:</label>
            <select id="school" name="school" required>
                <option value="">Select School</option>
                <?php foreach ($valid_schools as $school): ?>
                    <option value="<?php echo htmlspecialchars($school); ?>" <?php echo $counselee['school'] == $school ? 'selected' : ''; ?>><?php echo htmlspecialchars($school); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="department">Department:</label>
            <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($counselee['department']); ?>" required>

            <button type="submit" class="submit-btn" name="update_counselee">Update</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
