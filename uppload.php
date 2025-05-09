
<!DOCTYPE html>
<html>
<head>
    <title>Counseling Data Entry</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        form {
            background-color: whitesmoke;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: auto;
        }

        label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
            color: #555;
        }

        input[type="text"], select, textarea {
            width: 100%;
            padding: 8px;
            margin: 5px 0 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        input[type="text"]:readonly {
            background-color: #f0f0f0;
        }

        input[type="submit"], button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            margin: 5px 0;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }

        button:hover, input[type="submit"]:hover {
            background-color: #45a049;
        }

        .counseling-section {
            margin: 15px 0;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .error-message {
            color: red;
            font-weight: bold;
            margin-top: 10px;
            text-align: center;
        }
    </style>
     <script>
        function showFields() {
            const counseling_type = ['family', 'mental', 'career', 'health'];
            const selectedCounseling_type = document.getElementById('counseling_type').value;
            counseling_type.forEach(counseling_type => {
                document.getElementById(counseling_type).style.display = (counseling_type === selectedCounseling_type) ? 'block' : 'none';
            });
        }

        async function fetchData() {
            const regNumber = document.getElementById('registration_number').value;
            const errorMessage = document.getElementById('error-message');
            if (!regNumber) return;

            try {
                const response = await fetch('fetch.php?registration_number=' + regNumber);
                const data = await response.json();

                if (data) {
                    document.getElementById('name').value = data.name;
                    document.getElementById('school').value = data.school;
                    document.getElementById('department').value = data.department;
                    document.getElementById('year_of_study').value = data.year_of_study;
                    errorMessage.style.display = 'none'; // Hide error message if counselee is found
                } else {
                    errorMessage.style.display = 'block'; // Show error message if counselee is not found
                    document.getElementById('name').value = '';
                    document.getElementById('school').value = '';
                    document.getElementById('department').value = '';
                    document.getElementById('year_of_study').value = '';
                }
            } catch (error) {
                console.error('Error fetching data:', error);
                errorMessage.style.display = 'block'; // Show error message if there is an error fetching data
            }
        }
    </script>
</head>
<body>
    <h2>Enter Counseling Data</h2>
    <form action="submit.php" method="post">
        <label>Registration Number:</label><br>
        <input type="text" id="registration_number" name="registration_number" required>
        <button type="button" onclick="fetchData()">Search</button><br><br>

        <label>Name:</label><br>
        <input type="text" id="name" name="name" readonly><br><br>

        <label>School:</label><br>
        <input type="text" id="school" name="school" readonly><br><br>

        <label>Department:</label><br>
        <input type="text" id="department" name="department" readonly><br><br>

        <label>Year of Study:</label><br>
        <input type="text" id="year_of_study" name="year_of_study" readonly><br><br>

        <label>Counseling Category:</label>
        <select name="counseling_type" id="counseling_type" onchange="showFields()" required>
            <option value="">Select...</option>
            <option value="family">Family</option>
            <option value="mental">Mental Health</option>
            <option value="career">Career Guidance</option>
            <option value="health">Health Issues</option>
        </select><br><br>

        <div id="family" class="counseling-section" style="display:none;">
            Family Structure:
            <select name="family_structure">
                <option value="">Please select...</option>
                <option value="Nuclear">Nuclear</option>
                <option value="Extended">Extended</option>
                <option value="Single Parent">Single Parent</option>
                <option value="Blended">Blended</option>
            </select><br>
            Emotional and Social Issues: <select name="emotional_issues">
        <option value="">Please select...</option>
            <option>Stress</option>
            <option>Support</option>
            <option>Isolation</option>
            <option>Stigma</option>
            <option>Rejection</option>
        </select><br>
        Parenting Style: <select name="parenting_style">
        <option value="">Please select...</option>
            <option>Authoritative</option>
            <option>Permissive</option>
            <option>Neglectful</option>
            <option>Authoritarian</option>
        </select><br>
        Conflict Type: <select name="conflict_type">
        <option value="">Please select...</option>
            <option>Financial</option>
            <option>Parental</option>
            <option>Sibling Rivalry</option>
            <option>In-Law Issues</option>
        </select><br>
        Crisis and Trauma:<select name="crisis">
        <option value="">Please select...</option>
            <option>Violence</option>
            <option>Abuse</option>
            <option>Trauma</option>
            <option>Grief</option>
            <option>Loss</option>
        </select><br>
        </div>
        <!-- Mental Health Counseling -->
 <div id="mental" class="counseling-section" style="display:none;">
        Mental Condition: <select name="mental_condition">
            <option value="">Please select...</option>
            <option>Anxiety</option>
            <option>Depression</option>
            <option>Stress</option>
            <option>Hopelessness</option>
            <option>Fear</option>
            <option>Shame</option>
            <option>Anger</option>
            <option>Grief</option>
        </select><br>
        Stress Level: <select name="stress_level">
        <option value="">Please select...</option>
            <option>Low</option>
            <option>Moderate</option>
            <option>High</option>
        </select><br>
        Coping Mechanism: <select name="coping_mechanism">
        <option value="">Please select...</option>
            <option>Healthy</option>
            <option>Unhealthy</option>
            <option>Mixed</option>
        </select><br>
    </div>
    <!-- Career Guidance Counseling -->
    <div id="career" class="counseling-section" style="display:none;">
        Academic Challenges: <select name="academic">
            <option value="">Please select...</option>
            <option>Failure</option>
            <option>Underperformance</option>
            <option>Suspension</option>
            <option>Expulsion</option>
            <option>Procastination</option>
            <option>Career Misplacement</option>
            <option>Student Lecturer Grudge</option>
        </select><br>
        Finacial Strain: <select name="finance">
        <option value="">Please select...</option>
            <option>Fee Arrears</option>
            <option>Upkeep</option>
            <option>Debt</option>
            <option>Loans</option>
        </select><br>
        Skills and Competence Issues: <select name="skills">
        <option value="">Please select...</option>
            <option>Incompetent</option>
            <option>Unpreparedness</option>
            <option>Imbalance</option>
            <option>Difficulty</option>
        </select><br>
    </div>
    <!-- Health Issues Counseling -->
<div id="health" class="counseling-section" style="display:none;">
        Physical Health Challenges: <select name="physical">
            <option value="">Please select...</option>
            <option>Disability</option>
            <option>Accident</option>
            <option>Fatigue</option>
        </select><br>
        Sexual and Reproductive Health: <select name="sexual">
        <option value="">Please select...</option>
            <option>STI</option>
            <option>Pregnancy</option>
            <option>Abortion</option>
            <option>Contraception</option>
        </select><br>
        Substance Abuse and Addiction: <select name="drug_abuse">
        <option value="">Please select...</option>
            <option>Alcoholism</option>
            <option>Smoking</option>
            <option>Withdrawal</option>
            <option>Rehabilitation</option>
        </select><br>
    </div>
        <label>Counselor Comments:</label><br>
        <textarea name="counselor_comments" rows="4" cols="50"></textarea><br><br>

        <input type="submit" value="Submit">
    </form>
</body>
</html>
