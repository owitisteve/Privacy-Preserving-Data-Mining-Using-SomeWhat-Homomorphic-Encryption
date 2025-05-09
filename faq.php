<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FAQs - Counselee Portal</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            color: #333;
        }

        h2 {
            text-align: center;
            font-size: 2.5rem;
            margin: 30px 0;
            color: #004e92;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .faq {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background-color: #fafafa;
        }

        .faq h3 {
            color: #333;
            cursor: pointer;
            font-size: 1.25rem;
            margin: 0;
            padding: 10px;
            background-color: #f0f0f0;
            border-radius: 6px;
            transition: background-color 0.3s ease;
        }

        .faq h3:hover {
            background-color: #cce5ff;
        }

        .faq p {
            display: none;
            padding: 10px;
            background-color: #e9ecef;
            border-left: 3px solid #007bff;
            margin-top: 10px;
            border-radius: 6px;
            font-size: 1rem;
        }

        .faq p.show {
            display: block;
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

    </style>
    <script>
        function toggleAnswer(index) {
            const answer = document.getElementById('answer' + index);
            answer.classList.toggle('show');
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Frequently Asked Questions (FAQs)</h2>

        <?php
        $faqs = [
            "How do I request an appointment with a counselor?" => "Navigate to the 'Appointments' section and submit your request.",
            "Is my information confidential?" => "Yes, your information is confidential and protected by our privacy policies.",
            "How will I know when my appointment is scheduled?" => "You'll receive a dashboard notification once it's scheduled.",
            "Can I choose my counselor?" => "Counselors are assigned based on availability and your issue.",
            "What should I do if I don’t get a response?" => "Follow up via the 'Messages' section if no response in 48 hours.",
            "What types of issues can I talk to a counselor about?" => "Mental health, academic stress, family issues, career confusion, and emotional support.",
            "How do I give feedback after a session?" => "Go to the 'Feedback' tab and share your thoughts."
        ];

        $i = 1;
        foreach ($faqs as $question => $answer) {
            echo "<div class='faq'>
                    <h3 onclick='toggleAnswer($i)'>$question</h3>
                    <p id='answer$i'>$answer</p>
                  </div>";
            $i++;
        }
        ?>
    </div>
</body>
</html>
