<!DOCTYPE html>
<html>
<head>
    <title>Counselor Help</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef3f7;
            padding: 30px;
        }
        .faq-container {
            max-width: 800px;
            margin: auto;
        }
        .question {
            background: #ffffff;
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .answer {
            display: none;
            background: #e8f0fe;
            border-left: 4px solid #28a745;
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
    <h2>Counselor Help Center</h2>

    <div class="question" onclick="toggleAnswer(0)">
        1. How do I approve a counselee?
        <div class="answer">
            <ul>
                <li>Login to the counsellor portal.</li>
                <li>In the counsellor dashbord click the "approve counselee" tab.</li>
                <li>Enter the counselee registration number to verify eligibilty.</li>
                <li>Approve if eligible else decline</li>
                <li>If ineligible give decline reason before clicking.</li>
            </ul>
        </div>
    </div>

    <div class="question" onclick="toggleAnswer(1)">
        2. How do I schedule appointment requests?
        <div class="answer">
            <ul>
                <li>Login to your portal.</li>
                <li>Navigate to the dashboard section.</li>
                <li>Under the appointment requests column click at the schedule appointment button.</li>
                <li>Select the desired date and time.</li>
                <li>Click schedule</li>
            </ul>
        </div>
    </div>

    <div class="question" onclick="toggleAnswer(2)">
        3. How do I respond to messages from counselees?
        <div class="answer">
            <ul>
                <li>Go to the "Feedback" tab after login.</li>
                <li>Unread messages are shown with notification icons.</li>
                <li>Click on a message to view it.</li>
                <li>Click "Reply" to send your response.</li>
                <li>All replies are recorded in the counselor message system.</li>
            </ul>
        </div>
    </div>

    <div class="question" onclick="toggleAnswer(3)">
        4. How do I promote a counselee?
        <div class="answer">
            <ul>
                <li>Go to the "Manage counselee" tab.</li>
                <li>From the drop down list select "on-session".</li>
                <li>Click the "promote" button next to the name of the counselee you want to promote.</li>
                <li>A success message will appear showing the success of the promotion</li>
                
            </ul>
        </div>
    </div>

    <div class="question" onclick="toggleAnswer(4)">
        5. How do I upload counseling information?
        <div class="answer">
            <ul>
                <li>Login to counsellor portal.</li>
                <li>Click the "upload data" tab</li>
                <li>Enter the counselling details in the form that appears.</li>
                <li>Click Submit.</li>
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
