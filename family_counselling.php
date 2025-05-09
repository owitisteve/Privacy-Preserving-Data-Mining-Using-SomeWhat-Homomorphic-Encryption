<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Security check: Redirect if the user is not logged in
if (!isset($_SESSION['user_logged_in'])) {
    header('Location: user_login.php');
    exit();
}

$conn = new mysqli('localhost', 'root', 'D_vine@245', 'ppdm');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Initialize variables
$counselee_found = false;
$counselee = null;
$error_message = "";
$success_message = "";

// Handle the search for counselee by registration number
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search_registration_number'])) {
    $registration_number = trim($_POST['registration_number']);
    
    // Search in the 'counselees' table
    $stmt = $conn->prepare("SELECT * FROM counselees WHERE registration_number = ?");
    $stmt->bind_param("s", $registration_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $counselee = $result->fetch_assoc();
        $_SESSION['counselee_id'] = $counselee['id'];  // Store counselee ID in session
        $counselee_found = true;
    } else {
        $error_message = "Counselee not found!";
    }
}

// Handle the family data submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_family_data'])) {
    // Retrieve form data
    $county = $conn->real_escape_string($_POST['county']);
    $religion = $conn->real_escape_string($_POST['religion']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $family_members = (int)$_POST['family_members'];
    $dependents = (int)$_POST['dependents'];
    $chronic_conditions = (int)$_POST['chronic_conditions'];
    $income_range = $conn->real_escape_string($_POST['income_range']);
    $conflicts = (int)$_POST['conflicts'];
    $parents = (int)$_POST['parents'];
    $counselor_remarks = $conn->real_escape_string($_POST['counselor_remarks']);

    // Ensure a counselee ID exists in session
    $counselee_id = $_SESSION['counselee_id'] ?? null;  

    if ($counselee_id) {
        // Check if family data already exists for this counselee
        $stmt = $conn->prepare("SELECT * FROM family WHERE counselee_id = ?");
        $stmt->bind_param("i", $counselee_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Data already exists, display an error message
            $error_message = "Family data has already been uploaded for this counselee. If you want to re-upload, please delete the previous data first.";
        } else {
            // Insert new data into the family table
            $sql = "INSERT INTO family (county, religion, gender, family_members, dependents, chronic_conditions, income_range, conflicts, parents, counselor_remarks, counselee_id) 
                    VALUES ('$county', '$religion', '$gender', '$family_members', '$dependents', '$chronic_conditions', '$income_range', '$conflicts', '$parents', '$counselor_remarks', '$counselee_id')";
            
            if ($conn->query($sql) === TRUE) {
                $_SESSION['success_message'] = "Family counseling data uploaded successfully!";
                $counselee_found = false;  // Reset the form display after successful submission
            } else {
                $_SESSION['error_message'] = "Error: " . $conn->error;
            }
        }
    } else {
        $_SESSION['error_message'] = "No counselee found for this session.";
    }
}

// Handle the deletion of existing family data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_family_data'])) {
    $counselee_id = $_SESSION['counselee_id'] ?? null;

    if ($counselee_id) {
        // Delete the existing family data
        $stmt = $conn->prepare("DELETE FROM family WHERE counselee_id = ?");
        $stmt->bind_param("i", $counselee_id);
        if ($stmt->execute()) {
            $success_message = "Family data deleted successfully. You can now re-upload the data.";
        } else {
            $error_message = "Error deleting family data: " . $conn->error;
        }
    } else {
        $error_message = "No counselee found for this session.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Counseling</title>
    <style>
        /* General styling for the body */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7fa;
        }
        
        .container {
            width: 80%;
            max-width: 900px;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Styling the form */
        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        label {
            font-size: 16px;
            margin-bottom: 5px;
        }

        input, textarea, select {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #28a745;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        .error, .success {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            font-size: 16px;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        /* Modal styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow: auto;
            padding-top: 100px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
            text-align: center;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        /* Responsive design for smaller screens */
        @media screen and (max-width: 600px) {
            .container {
                width: 95%;
            }

            form input, form textarea {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Search for counselee form -->
        <form method="POST">
            <label>Enter Registration Number:</label>
            <input type="text" name="registration_number" required>
            <button type="submit" name="search_registration_number">Find Counselee</button>
        </form>

        <!-- Display error message if counselee not found -->
        <?php if ($error_message): ?>
            <div class="error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Family data form (displayed if a counselee is found) -->
        <?php if ($counselee_found): ?>
            <form method="POST">
                <h3>Counselee Found: <?php echo $counselee['registration_number']; ?> - <?php echo $counselee['name']; ?></h3>
                <label for="county">County:</label>
                <input type="text" name="county" required>
                <label for="religion">Religion:</label>
                <input type="text" name="religion" required>
                <label for="gender">Gender:</label>
                <select name="gender" required>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
                <label for="family_members">Number of Family Members:</label>
                <input type="number" name="family_members" required>
                <label for="dependents">Number of Dependents:</label>
                <input type="number" name="dependents" required>
                <label for="chronic_conditions">Number of Chronic Conditions:</label>
                <input type="number" name="chronic_conditions" required>
                <label for="income_range">Income Range:</label>
                <input type="text" name="income_range" required>
                <label for="conflicts">Family Conflicts (1-10):</label>
                <input type="number" name="conflicts" required>
                <label for="parents">Status of Parents (1-10):</label>
                <input type="number" name="parents" required>
                <label for="counselor_remarks">Counselor's Remarks:</label>
                <textarea name="counselor_remarks" rows="4" required></textarea>
                <button type="submit" name="submit_family_data">Upload Family Data</button>
            </form>

            <!-- Option to delete existing family data -->
            <form method="POST">
                <button type="submit" name="delete_family_data">Delete Existing Data</button>
            </form>

        <?php endif; ?>

        <!-- Display success message -->
        <?php if ($success_message): ?>
            <div class="modal" id="successModal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <p><?php echo $success_message; ?></p>
                    <button onclick="window.location.href='user_login.php';">OK</button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function closeModal() {
            document.getElementById('successModal').style.display = "none";
        }

        // Show modal if success message is set
        <?php if ($success_message): ?>
            document.getElementById('successModal').style.display = "block";
        <?php endif; ?>
    </script>
</body>
</html>
