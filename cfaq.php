<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FAQs - Counselor Portal</title>
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
            "How do I view appointments requested by counselees?" => "Navigate to the 'Appointments' section to view and manage the requests made by counselees.",
            "Can I change the appointment time once it's scheduled?" => "Yes, you can reschedule the appointment from the 'Appointments' section, but make sure to notify the counselee.",
            "What should I do if I have multiple counselees requesting the same time?" => "You can prioritize based on urgency or availability and communicate with counselees to reschedule accordingly.",
            "How do I reply to messages from counselees?" => "Go to the 'Messages' section, where you can view and respond to messages from counselees.",
            "How do I provide feedback on a counselee's progress?" => "You can leave feedback in the 'Feedback' tab after each session. Be sure to keep it confidential.",
            "How do I assign counselees to specific counseling categories?" => "Counselees are typically assigned based on their needs, but you can update or change categories under the 'Counselee Management' section.",
            "What should I do if a counselee doesn't respond to messages?" => "You can follow up via the 'Messages' section or notify the admin if the situation persists."
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
