<?php
$pythonScriptPath = '/home/vostive/Desktop/project/condition.py';
$output = shell_exec('/home/vostive/Desktop/project/tenseal-env/bin/python ' . $pythonScriptPath);

// Check if the output is empty or if there are errors
if ($output === null || empty($output)) {
    // In case of failure, you can display a message or log the error, or just redirect.
    // For example, you can redirect to an error page or back to the current page
    header("Location: error.php");  // Redirect to an error page (optional)
    exit();
} else {
    // Redirect to mining.php after successful script execution
    header("Location: mining.php");  // Redirect to mining.php
    exit();
}
?>
