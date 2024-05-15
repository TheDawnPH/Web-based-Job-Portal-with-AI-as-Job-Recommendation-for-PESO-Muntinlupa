<?php
session_start();

include 'config.php';

/* Function to record WPM into user's profile [wip]
function recordWPM($conn, $wpm)
{
    $user_id = $_SESSION["user_id"];
    $sql = "UPDATE users SET wpm = ? WHERE user_id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $param_wpm, $param_user_id);
        $param_wpm = $wpm;
        $param_user_id = $user_id;
        if (mysqli_stmt_execute($stmt)) {
            return true;
        }
    }
    return false;
} */
?>

<html>

<head>
    <title>PESO Muntinlupa - Job Portal</title>
    <link rel="stylesheet" href="css/index.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="icon" type="image/png" href="/img/peso_muntinlupa.png">
    <link rel="manifest" href="/site.webmanifest">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
    <style>
        #typing-area {
            user-select: none;
            /* Disable text selection */
            -moz-user-select: none;
            -webkit-user-select: none;
            -ms-user-select: none;
        }
    </style>
</head>

<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Typing Test</h1>
                <p>Test your typing speed and accuracy. Type the following below:</p>
                <div id="typing-area"></div>
                <input type="text" id="user-input" class="form-control mt-3" placeholder="Type here">
                <p id="result"></p>
                <button id="start-btn" class="btn btn-primary">Start Test</button>
            </div>
        </div>
        <script>
            const typingArea = document.getElementById('typing-area');
            const userInput = document.getElementById('user-input');
            const resultDisplay = document.getElementById('result');
            const startBtn = document.getElementById('start-btn');

            let textToType = '';
            let startTime, endTime;

            // Generate random text for typing
            function generateText() {
                const words = ['The quick brown fox jumps over the lazy dog.', 
                'How much wood would a woodchuck chuck if a woodchuck could chuck wood?', 
                'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 
                'Were not they supposed to put in a left-hand turn lane three years ago? he thought as he waited through three lights at the intersection with Exchange Street.', 
                'He had a routine. He would stick to it. It would get him through the day.', 
                'She never blamed him, as he feared she would. She picked up and went on. He was again amazed by her lightness, her resiliency.'];
                textToType = words[Math.floor(Math.random() * words.length)];
                typingArea.textContent = textToType;
            }

            // Function to start the test
            function startTest() {
                generateText();
                userInput.value = '';
                userInput.focus();
                resultDisplay.textContent = 'Type the text above as fast and accurately as you can.';
                startBtn.style.display = 'none';
                startTime = new Date();
            }

            // Function to end the test
            function endTest() {
                endTime = new Date();
                const elapsedTime = (endTime - startTime) / 1000; // Time in seconds
                const typedText = userInput.value.trim();
                const accuracy = calculateAccuracy(typedText, textToType);
                const wordsTyped = typedText.split(' ').length;
                const wpm = Math.round((wordsTyped / elapsedTime) * 60);
                resultDisplay.textContent = `Your typing speed: ${wpm} WPM`;
                startBtn.style.display = 'block';
                startBtn.textContent = 'Restart Test';

                // If user is logged in, record WPM into user's profile
                <?php
                /* wip
                if (isset($_SESSION["user_id"])) {
                    echo "recordWPM($conn, $wpm);"; // Fixed passing of parameters
                } */
                ?>
            }

            // Function to calculate accuracy
            function calculateAccuracy(typedText, originalText) {
                const typedWords = typedText.split(' ');
                const originalWords = originalText.split(' ');
                let correctCount = 0;
                for (let i = 0; i < Math.min(typedWords.length, originalWords.length); i++) {
                    if (typedWords[i] === originalWords[i]) {
                        correctCount++;
                    }
                }
                return Math.round((correctCount / originalWords.length) * 100);
            }

            // Event listener for start button click
            startBtn.addEventListener('click', startTest);

            // Event listener for typing in the input field
            userInput.addEventListener('input', function(event) {
                const typedText = event.target.value.trim();
                if (typedText === textToType) {
                    endTest();
                }
            });

            // Start the test initially
            startTest();
        </script>
</body>

</html>
