<!DOCTYPE html>
<html>
<head>
    <title>Counselee Help</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fa;
            padding: 30px;
        }
        .faq-container {
            max-width: 800px;
            margin: auto;
        }
        .question {
            background: #ffffff;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .answer {
            display: none;
            background: #f0f4f8;
            border-left: 4px solid #007BFF;
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
    <h2>Counselee Help Center</h2>

    <div class="question" onclick="toggleAnswer(0)">
        1. How do I register as a new counselee?
        <div class="answer">
            <ul>
                <li>Go to the student (counselee) portal login page.</li>
                <li>Click on the "Register" or "Sign up" link.</li>
                <li>Fill in all the required personal details.</li>
                <li>Submit the form and wait for admin approval.</li>
                <li>Check back later for approval notification.</li>
            </ul>
        </div>
    </div>

    <div class="question" onclick="toggleAnswer(1)">
        2. How do I request a counseling appointment?
        <div class="answer">
            <ul>
                <li>Login to your counselee account.</li>
                <li>Navigate to the "Request Appointment" section.</li>
                <li>Select the preferred appointment date.</li>
                <li>Submit the request.</li>
                <li>Wait for a response from the counselor.</li>
            </ul>
        </div>
    </div>
    <div class="question" onclick="toggleAnswer(2)">
        3. How do I contact the admin or counselor incase of a problem?
        <div class="answer">
            <ul>
                <li>Go to the counselee dashboard.</li>
                <li>Click on the feedback tab.</li>
                <li>Select the recipient of the message either admin or counselor.</li>
                <li>Enter your message in the chat box.</li>
                <li>Click send.</li>
            </ul>
        </div>
    </div>

    <div class="question" onclick="toggleAnswer(3)">
        4. How do I view my feedback or messages?
        <div class="answer">
            <ul>
                <li>Login to the portal.</li>
                <li>Click on the Feedback tab.</li>
                <li>Unread messages are highlighted with a notification.</li>
                <li>Click on a message to read it.</li>
                <li>Reply if a response is needed.</li>
            </ul>
        </div>
    </div>

    <div class="question" onclick="toggleAnswer(4)">
        5. How do I check my registration status?
        <div class="answer">
            <ul>
                <li>Login to your counselee portal.</li>
                <li>Go to the "appointment status" section.</li>
                <li>Click on the tab.</li>
                <li>Your appointment status will be displayed in form of a modal</li>
                
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
