<?php
session_start();
if (!isset($_SESSION['user_logged_in'])) {
    header('Location: user_login.php');
    exit();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'paillier.php';

// Initialize Paillier Cryptosystem
$paillier = new Paillier();

// Connect to MySQL
$conn = new mysqli("localhost", "root", "D_vine@245", "ppdm");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve encrypted data from the database
$result = $conn->query("SELECT name, age, height, department FROM test");

$total_age_encrypted = null;
$total_height_encrypted = null;

// Loop through the rows to perform encrypted addition
while ($row = $result->fetch_assoc()) {
    // Decode the base64-encoded encrypted values
    $age_encrypted = gmp_init(base64_decode($row['age']));
    $height_encrypted = gmp_init(base64_decode($row['height']));

    // Add encrypted age and height to the total
    if ($total_age_encrypted === null) {
        $total_age_encrypted = $age_encrypted;
        $total_height_encrypted = $height_encrypted;
    } else {
        $total_age_encrypted = $paillier->addEncrypted($total_age_encrypted, $age_encrypted);
        $total_height_encrypted = $paillier->addEncrypted($total_height_encrypted, $height_encrypted);
    }
}

// Decrypt the total age and total height
$total_age_decrypted = $paillier->decrypt($total_age_encrypted);
$total_height_decrypted = $paillier->decrypt($total_height_encrypted);

// Convert the decrypted GMP values to integers
$total_age = gmp_intval($total_age_decrypted);
$total_height = gmp_intval($total_height_decrypted);

// Display the results
echo "Total Age (Decrypted): " . $total_age . "<br>";
echo "Total Height (Decrypted): " . $total_height . "<br>";

$conn->close();
?>
