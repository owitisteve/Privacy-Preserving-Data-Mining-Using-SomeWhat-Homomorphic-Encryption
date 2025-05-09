<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $age = $_POST["age"];
    $year_of_study = $_POST["year_of_study"];
    $gender = $_POST["gender"];

    // Prepare JSON data to send to Python
    $data = json_encode([
        "name" => $name,
        "age" => (int)$age,
        "year_of_study" => (int)$year_of_study,
        "gender" => $gender
    ]);

    // Path to Python script & virtual environment
    $pythonScript = "/home/vostive/Desktop/project/ncrypt.py";
    $pythonBin = "/home/vostive/Desktop/project/tenseal-env/bin/python3";

    // Run Python script and capture output
    $command = "echo " . escapeshellarg($data) . " | $pythonBin $pythonScript";
    $output = shell_exec($command);

    // Decode JSON output from Python
    $encrypted_data = json_decode($output, true);

    if ($encrypted_data) {
        // MySQL database connection
        $conn = new mysqli("localhost", "root", "D_vine@245", "ppdm");

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Create table if not exists
        $conn->query("CREATE TABLE IF NOT EXISTS testss (
            id INT AUTO_INCREMENT PRIMARY KEY,
            encrypted_name LONGBLOB,
            encrypted_age LONGBLOB,
            encrypted_year_of_study LONGBLOB,
            encrypted_gender LONGBLOB
        )");

        // Prepare statement
        $stmt = $conn->prepare("INSERT INTO testss (encrypted_name, encrypted_age, encrypted_year_of_study, encrypted_gender) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $encrypted_data['encrypted_name'], $encrypted_data['encrypted_age'], $encrypted_data['encrypted_year_of_study'], $encrypted_data['encrypted_gender']);

        // Execute query
        if ($stmt->execute()) {
            echo "Data encrypted and inserted successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close connections
        $stmt->close();
        $conn->close();
    } else {
        echo "Error: Unable to encrypt data.";
    }
} else {
    echo "Invalid request.";
}
?>
