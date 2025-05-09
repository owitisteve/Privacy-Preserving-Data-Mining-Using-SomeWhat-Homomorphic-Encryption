<?php
// DB Connection
$conn = new mysqli("localhost", "root", "D_vine@245", "ppdm");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$search_result = null;
$from_table = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['search_term'])) {
    $term = $conn->real_escape_string(trim($_POST['search_term']));

    // Search in active counselees
    $query1 = "SELECT * FROM counselees WHERE email = '$term' OR registration_number = '$term'";
    $result1 = $conn->query($query1);

    if ($result1->num_rows > 0) {
        $search_result = $result1->fetch_assoc();
        $from_table = "counselees";
    } else {
        // Search in completed
        $query2 = "SELECT * FROM completed WHERE email = '$term' OR registration_number = '$term'";
        $result2 = $conn->query($query2);

        if ($result2->num_rows > 0) {
            $search_result = $result2->fetch_assoc();
            $from_table = "completed";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Counselee</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f7fc;
            padding: 40px;
            color: #333;
        }
        h2 {
            text-align: center;
            color: #2e5cb8;
        }
        form {
            text-align: center;
            margin-bottom: 30px;
        }
        input[type="text"] {
            padding: 10px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
        }
        button {
            padding: 10px 20px;
            background-color: #2e5cb8;
            color: white;
            border: none;
            border-radius: 5px;
            margin-left: 10px;
            cursor: pointer;
        }
        button:hover {
            background-color: #1c3d8d;
        }
        table {
            border-collapse: collapse;
            width: 60%;
            margin: 0 auto 30px auto;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        th, td {
            border: 1px solid #dcdcdc;
            padding: 12px 15px;
            text-align: left;
        }
        th {
            background-color: #f1f5fb;
            color: #333;
        }
        .back-btn {
            display: flex;
            justify-content: center;
        }
        .message {
            text-align: center;
            font-size: 18px;
            margin-top: 20px;
        }
        .status-label {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .status-label.completed {
            background-color: #6c757d;
        }
    </style>
</head>
<body>

<h2>Search Counselee</h2>

<form method="POST">
    <input type="text" name="search_term" placeholder="Enter Email or Reg No" required>
    <button type="submit">Search</button>
</form>

<?php if ($search_result): ?>
    <div class="message">
        <span class="status-label <?= $from_table == 'completed' ? 'completed' : '' ?>">
            <?= $from_table == 'counselees' ? 'In Session' : 'Completed' ?>
        </span>
    </div>
    <table>
        <tr><th>Name</th><td><?= $search_result['name'] ?></td></tr>
        <tr><th>Email</th><td><?= $search_result['email'] ?></td></tr>
        <tr><th>Reg Number</th><td><?= $search_result['registration_number'] ?></td></tr>
        <?php if ($from_table == 'counselees'): ?>
            <tr><th>Designation</th><td><?= $search_result['designation'] ?></td></tr>
            <tr><th>School</th><td><?= $search_result['school'] ?></td></tr>
            <tr><th>Department</th><td><?= $search_result['department'] ?></td></tr>
            <tr><th>Year of Study</th><td><?= $search_result['year_of_study'] ?></td></tr>
        <?php endif; ?>
        <tr><th>Admission/Completion Date</th><td><?= $search_result['created_at'] ?></td></tr>
    </table>
<?php elseif ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
    <p class="message" style="color: red;">No counselee found with that email or registration number.</p>
<?php endif; ?>

<div class="back-btn">
    <a href="user_dashboard.php">
        <button>Back to Dashboard</button>
    </a>
</div>

</body>
</html>

<?php $conn->close(); ?>
