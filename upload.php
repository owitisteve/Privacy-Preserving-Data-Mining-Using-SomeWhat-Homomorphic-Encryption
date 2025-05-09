<?php
require_once 'paillier.php';

// Initialize Paillier Cryptosystem
$paillier = new Paillier();

// Connect to MySQL
$conn = new mysqli("localhost", "root", "D_vine@245", "ppdm");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Convert name and department to numbers
    $name_numeric = $paillier->convertStringToNumber($_POST['name']);
    $department_numeric = $paillier->convertStringToNumber($_POST['department']);

    // Encrypt the numeric values
    $name = base64_encode(gmp_strval($paillier->encrypt($name_numeric)));
    $age = base64_encode(gmp_strval($paillier->encrypt($_POST['age'])));
    $height = base64_encode(gmp_strval($paillier->encrypt($_POST['height'])));
    $department = base64_encode(gmp_strval($paillier->encrypt($department_numeric)));

    // Insert Encrypted Data into Database
    $stmt = $conn->prepare("INSERT INTO test (name, age, height, department) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $age, $height, $department);

    if ($stmt->execute()) {
        echo "Data inserted successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paillier Encrypted Upload</title>
</head>
<body>
    <h2>Upload Encrypted Data</h2>
    <form method="POST">
        <label>Name:</label>
        <input type="text" name="name" required><br>

        <label>Age:</label>
        <input type="number" name="age" required><br>

        <label>Height:</label>
        <input type="number" step="0.01" name="height" required><br>

        <label>Department:</label>
        <input type="text" name="department" required><br>

        <button type="submit">Submit</button>
    </form>
</body>
</html>
