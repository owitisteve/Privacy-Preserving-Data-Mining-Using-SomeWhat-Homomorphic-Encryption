<!DOCTYPE html>
<html>
<head>
    <title>Encrypted Data Submission</title>
</head>
<body>
    <h2>Submit Encrypted Data</h2>
    <form action="process.php" method="post">
        <label>Name:</label>
        <input type="text" name="name" required><br><br>

        <label>Age:</label>
        <input type="number" name="age" required><br><br>

        <label>Year of Study:</label>
        <input type="number" name="year_of_study" required><br><br>

        <label>Gender:</label>
        <select name="gender" required>
            <option value="M">Male</option>
            <option value="F">Female</option>
        </select><br><br>

        <input type="submit" value="Submit">
    </form>
</body>
</html>
