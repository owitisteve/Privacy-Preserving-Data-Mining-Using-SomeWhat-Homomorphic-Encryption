<!DOCTYPE html>
<html>
<head>
    <title>Admin Help</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            padding: 30px;
        }
        .faq-container {
            max-width: 800px;
            margin: auto;
        }
        .question {
            background: #ffffff;
            border: 1px solid #bbb;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }
        .answer {
            display: none;
            background: #eaf3ff;
            border-left: 4px solid #ff9800;
            padding: 15px;
            margin-top: 5px;
            border-radius: 4px;
        }
        .answer ul {
            margin: 0;
            padding-left: 20px;
        }
    </style>
</head>
<body>

<div class="faq-container">
    <h2>Admin Help Center</h2>

    <div class="question" onclick="toggleAnswer(0)">
        1. How do I access the admin dashboard?
        <div class="answer">
            <ul>
                <li>Go to the admin portal login page.</li>
                <li>Enter your admin username and password.</li>
                <li>Click "Login".</li>
                <li>You’ll be redirected to the admin dashboard on successful login.</li>
                <li>If login fails, verify credentials or contact the system developer.</li>
            </ul>
        </div>
    </div>

    <div class="question" onclick="toggleAnswer(1)">
        2. How do I do data mining?
        <div class="answer">
            <ul>
                <li>Go to the "Data Mining" section in the dashboard.</li>
                <li>In the mining hub, choose the type of mining you want.</li>
                <li>Click on the subcategory of the mining from the list that appears.</li>
                <li>Insights will be presented to you both in text form and graphical format.</li>
                <li>You can proceed to download the insights drawn in pdf format </li>
            </ul>
        </div>
    </div>

    <div class="question" onclick="toggleAnswer(2)">
        3. How do I check and reply to system messages?
        <div class="answer">
            <ul>
                <li>Navigate to the "Messages" section on the dashboard.</li>
                <li>View messages from counselors or counselees.</li>
                <li>Click on a message to read it in full.</li>
                <li>Use the "Reply" button to respond directly.</li>
                <li>Replies are saved and notifications are sent to recipients.</li>
            </ul>
        </div>
    </div>

    <div class="question" onclick="toggleAnswer(3)">
        4. How do I generate data mining reports?
        <div class="answer">
            <ul>
                <li>Go to the "Reports" section.</li>
                <li>Select a specific report from the drop down list.</li>
                <li>Click "Generate Report".</li>
                <li>View the report summary and download as PDF/Excel.</li>
                <li>Use the reports for presentation or decision-making.</li>
            </ul>
        </div>
    </div>

</div>

<script>
    function toggleAnswer(index) {
        const answers = document.querySelectorAll('.answer');
        answers[index].style.display = answers[index].style.display === 'block' ? 'none' : 'block';
    }
</script>

</body>
</html>
