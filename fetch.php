<?php
if (isset($_GET['registration_number'])) {
    $registration_number = $_GET['registration_number'];

    // Connect to the database
    $conn = new mysqli('localhost', 'root', 'D_vine@245', 'ppdm');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Query to fetch the student's details
    $sql = "SELECT name, school, department, year_of_study FROM counselees WHERE registration_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $registration_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode($row);
    } else {
        echo json_encode(null);
    }

    $stmt->close();
    $conn->close();
}
?>
