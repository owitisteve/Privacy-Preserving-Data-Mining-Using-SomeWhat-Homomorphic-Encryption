<?php
$host = 'localhost';
$user = 'root';
$password = 'D_vine@245';
$database = 'ppdm';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
?>
