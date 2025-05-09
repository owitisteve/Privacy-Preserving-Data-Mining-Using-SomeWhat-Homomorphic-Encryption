<?php
session_start();
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
    $year_of_study = trim($_POST['year_of_study']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    // Validations
    if (empty($name)) $errors[] = "Name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";

    // Check if email already exists
    $stmt = $conn->prepare("SELECT * FROM counselees WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $email_result = $stmt->get_result();
    if ($email_result->num_rows > 0) $errors[] = "Email already exists.";

    // Check if registration number already exists
    $stmt = $conn->prepare("SELECT * FROM counselees WHERE registration_number = ?");
    $stmt->bind_param("s", $registration_number);
    $stmt->execute();
    $reg_result = $stmt->get_result();
    if ($reg_result->num_rows > 0) $errors[] = "Registration number already exists.";

    if (empty($registration_number)) $errors[] = "Registration number is required.";
    if (!in_array($designation, ['staff', 'student'])) $errors[] = "Invalid designation.";
    if (!in_array($school, $valid_schools)) $errors[] = "Invalid school selection.";
    if (empty($department)) $errors[] = "Department is required.";
    if (empty($year_of_study)) $errors[] = "Year of study is required.";
    if (empty($password) || empty($confirm_password)) $errors[] = "Password and confirmation are required.";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match.";
    if (strlen($password) < 8 || 
        !preg_match('/[A-Z]/', $password) || 
        !preg_match('/[a-z]/', $password) || 
        !preg_match('/[0-9]/', $password) || 
        !preg_match('/[\W]/', $password)) {
        $errors[] = "Password must be at least 8 characters long and include an uppercase letter, a lowercase letter, a digit, and a special character.";
    }

    if (!empty($errors)) {
        $error_message = implode("<br>", $errors);
        $_SESSION['error_message'] = $error_message;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert into pending approvals
    $decline_reason = "";
    $stmt = $conn->prepare("INSERT INTO pending_approvals (name, email, registration_number, designation, school, department, year_of_study, password_hash, decline_reason) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $name, $email, $registration_number, $designation, $school, $department, $year_of_study, $password_hash, $decline_reason);

    if ($stmt->execute()) {
        echo "<script>alert('Registration submitted for approval. You will be notified once approved.'); window.location.href = 'clogin.php';</script>";
        exit();
    } else {
        echo "Error: " . $stmt->error;
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
            height: 120vh;
            margin: 0;
        }

        .container {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
        }

        form {
            display: grid;
            gap: 8px;
        }

        label {
            font-weight: bold;
            font-size: 14px;
        }

        input, select {
            width: 100%;
            padding: 6px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        #errorModal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4); /* Black background with transparency */
}

/* Modal Content */
.modal-content {
    background-color: #fff;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 400px;
    border-radius: 8px;
    text-align: center;
    animation: fadeIn 0.3s;
}

/* Close Button */
.close-btn {
    background-color: #28a745;
    color: white;
    padding: 8px 16px;
    margin-top: 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.close-btn:hover {
    background-color: #218838;
}

/* Animation for Modal */
@keyframes fadeIn {
    from {opacity: 0;}
    to {opacity: 1;}
}

        .submit-btn {
            width: 100%;
            padding: 8px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 8px;
            font-size: 14px;
        }

        .submit-btn:hover {
            background-color: #218838;
        }
        .password-hint {
            color: red;
            font-size: 12px;}
    </style>
</head>
<body>
    <div class="container">
        <h2>Counselee Registration</h2>
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
            <select id="school" name="school" required onchange="updateDepartments()">
                <option value="">Select School</option>
                <?php foreach ($valid_schools as $school): ?>
                    <option value="<?php echo htmlspecialchars($school); ?>"> <?php echo htmlspecialchars($school); ?> </option>
                <?php endforeach; ?>
            </select>

            <label for="department">Department:</label>
            <select id="department" name="department" required>
                <option value="">Select Department</option>
            </select>

            <label for="year_of_study">Year of Study:</label>
            <select id="year_of_study" name="year_of_study" required>
                <option value="">Select Year</option>
                <?php for ($i = 1; $i <= 4; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>

            <label for="password">Password:</label>
<input type="password" id="password" name="password" required oninput="validatePassword()">
<div id="password-hint" class="password-hint"></div>


            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit" class="submit-btn">Register</button>
        </form>
    </div>
<!-- Error Modal -->
<div id="errorModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5);">
    <div style="background: white; padding: 20px; margin: 100px auto; border-radius: 8px; width: 90%; max-width: 400px; text-align: center;">
        <h3 style="color: red;">Error</h3>
        <p id="modalMessage"></p>
        <button onclick="closeModal()" style="background-color: #28a745; color: white; padding: 8px 16px; border: none; border-radius: 4px; margin-top: 10px;">Close</button>
    </div>
</div>

<script>
function showModal(message) {
    const modal = document.getElementById("errorModal");
    const messageContainer = document.getElementById("modalMessage");
    messageContainer.innerHTML = message;
    modal.style.display = "block";
}

function closeModal() {
    const modal = document.getElementById("errorModal");
    modal.style.display = "none";
}
</script>

    <script>
        function updateDepartments() {
            const school = document.getElementById("school").value;
            const department = document.getElementById("department");
            const departments = {
                "School of Computing and Informatics": ["Computer Science", "Computer Technology", "Information Technology"],
                "School of Agriculture and Food Science": ["Agricultural Economics", "Food Science", "Animal Science"],
                "School of Business and Economics": ["Accounting", "Business Administration", "Economics"],
                "School of Engineering and Architecture": ["Civil Engineering", "Mechanical Engineering", "Architecture"],
                "School of Education": ["Arts", "sciences"],
                "School of Health Sciences" : ["Public Health","Community Health","Clinical Medicine"],
                "School of Nursing" : ["General Nursing","Midwifery"],
                "School of Pure and Applied Sciences" : ["Mathematics","Physics","Chemistry"],
                "TVET" : ["Automotive Engineering","Building Technology","Electrical Engineering","Plumbing and Pipefitting","Welding and Fabrication","Carpentry and Joinery"]
            };
            department.innerHTML = '<option value="">Select Department</option>';
            if (departments[school]) {
                departments[school].forEach(dept => {
                    const option = document.createElement("option");
                    option.value = dept;
                    option.textContent = dept;
                    department.appendChild(option);
                });
            }
        }
    </script>
 <script>
    function validatePassword() {
        const password = document.getElementById("password").value;
        const hint = document.getElementById("password-hint");
        const requirements = [
            password.length >= 8,
            /[A-Z]/.test(password),
            /[a-z]/.test(password),
            /[0-9]/.test(password),
            /[\W]/.test(password)
        ];
        const messages = [
            "At least 8 characters",
            "An uppercase letter",
            "A lowercase letter",
            "A number",
            "A special character"
        ];
        const feedback = messages
            .filter((_, index) => !requirements[index])
            .join(', ');

        hint.textContent = feedback ? "Password must contain: " + feedback : "Password is strong.";
    }
</script>
<script>
    window.onload = function() {
        <?php if (isset($_SESSION['error_message'])): ?>
            showModal("<?php echo $_SESSION['error_message']; ?>");
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
    };
</script>


</body>
</html>
