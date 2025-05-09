<?php
$host = 'localhost';
$user = 'root';
$password = 'D_vine@245';
$database = 'ppddm';
$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
?>
