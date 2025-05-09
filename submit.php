<?php
// Get POST data from form
$data = array(
    "family_structure" => !empty($_POST["family_structure"]) ? $_POST["family_structure"] : NULL,
    "emotional_issues" => !empty($_POST["emotional_issues"]) ? $_POST["emotional_issues"] : NULL,
    "parenting_style" => !empty($_POST["parenting_style"]) ? $_POST["parenting_style"] : NULL,
    "conflict_type" => !empty($_POST["conflict_type"]) ? $_POST["conflict_type"] : NULL,
    "crisis" => !empty($_POST["crisis"]) ? $_POST["crisis"] : NULL,
    "mental_condition" => !empty($_POST["mental_condition"]) ? $_POST["mental_condition"] : NULL,
    "stress_level" => !empty($_POST["stress_level"]) ? $_POST["stress_level"] : NULL,
    "coping_mechanism" => !empty($_POST["coping_mechanism"]) ? $_POST["coping_mechanism"] : NULL,
    "academic" => !empty($_POST["academic"]) ? $_POST["academic"] : NULL,
    "finance" => !empty($_POST["finance"]) ? $_POST["finance"] : NULL,
    "skills" => !empty($_POST["skills"]) ? $_POST["skills"] : NULL,
    "physical" => !empty($_POST["physical"]) ? $_POST["physical"] : NULL,
    "sexual" => !empty($_POST["sexual"]) ? $_POST["sexual"] : NULL,
    "drug_abuse" => !empty($_POST["drug_abuse"]) ? $_POST["drug_abuse"] : NULL,
    "counselor_comments" => !empty($_POST["counselor_comments"]) ? $_POST["counselor_comments"] : NULL,
    "school" => !empty($_POST["school"]) ? $_POST["school"] : NULL,
    "department" => !empty($_POST["department"]) ? $_POST["department"] : NULL,
    "year_of_study" => !empty($_POST["year_of_study"]) ? $_POST["year_of_study"] : NULL,
    "counseling_type" => !empty($_POST["counseling_type"]) ? $_POST["counseling_type"] : NULL
);

// Convert data to JSON
$json_data = json_encode($data);

// Execute Python script and pass JSON data
$command = "echo " . escapeshellarg($json_data) . " | /home/vostive/Desktop/project/tenseal-env/bin/python /home/vostive/Desktop/project/encrypt.py 2>&1";
$output = shell_exec($command);

// Display the output
echo "Result: " . htmlspecialchars($output);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Include the script to execute the encryption and handle redirection
    include 'process_encryption.php';
}
?>
