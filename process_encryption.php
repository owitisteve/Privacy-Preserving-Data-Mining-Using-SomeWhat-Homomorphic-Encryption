<?php
// Execute the Python script and capture the output
$output = shell_exec('python3 encrypt.py 2>&1');

if (strpos($output, 'Data encryption and upload successful.') !== false) {
    echo "<script>alert('Data uploaded successfully!');</script>";
    header("Refresh: 2; URL=user_dashboard.php");
    exit;
} else {
    echo "<script>alert('Data upload failed: " . addslashes($output) . "');</script>";
    header("Refresh: 2; URL=user_dashboard.php");
    exit;
}
?>
