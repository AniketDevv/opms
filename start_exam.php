<?php
include_once 'db_connection.php';
session_start();

// Check if the student is logged in
if (!isset($_SESSION['admission_number'])) {
    header("location:login.php");
    exit;
}
// Get the exam ID from the URL
if (isset($_GET['exam_id'])) {
    $exam_id = $_GET['exam_id'];

    // Fetch exam details including exam name based on exam_id
    $sql_exam = "SELECT * FROM exams WHERE id = ?";
    $stmt_exam = $conn->prepare($sql_exam);
    $stmt_exam->bind_param("i", $exam_id);
    $stmt_exam->execute();
    $exam_result = $stmt_exam->get_result();
    
    // Check if exam exists
    if ($exam_result->num_rows > 0) {
        $exam = $exam_result->fetch_assoc();
        $exam_name = $exam['exam_name'];  // Get the exam name
    } else {
        echo "No exam found!";
        exit;
    }

    // Fetch one random hard question
    $sql_hard = "SELECT * FROM questions WHERE exam_id = ? AND difficulty = 'hard' ORDER BY RAND() LIMIT 1";
    $stmt_hard = $conn->prepare($sql_hard);
    $stmt_hard->bind_param("i", $exam_id);
    $stmt_hard->execute();
    $hard_result = $stmt_hard->get_result();
    $hard_question = $hard_result->fetch_assoc();

    // Fetch one random normal question
    $sql_normal = "SELECT * FROM questions WHERE exam_id = ? AND difficulty = 'normal' ORDER BY RAND() LIMIT 1";
    $stmt_normal = $conn->prepare($sql_normal);
    $stmt_normal->bind_param("i", $exam_id);
    $stmt_normal->execute();
    $normal_result = $stmt_normal->get_result();
    $normal_question = $normal_result->fetch_assoc();
    } else {
        echo "No exam found!";
        exit;
    }

// If form is submitted, store the answers in the database
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hard_answer = $_POST['hard_answer'];
    $normal_answer = $_POST['normal_answer'];

    // Sanitize newlines for display later
    $hard_answer = nl2br($hard_answer); // Convert newlines to <br> tags for display
    $normal_answer = nl2br($normal_answer); 

    // After exam submission, insert the attempt status into exam_results
    $sql_insert = "INSERT INTO exam_results (admission_number, exam_id, hard_question, normal_question, hard_answer, normal_answer, status)
    VALUES (?, ?, ?, ?, ?, ?, 'attempted')";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iissss", $_SESSION['admission_number'], $exam_id, $hard_question['question_text'], $normal_question['question_text'], $hard_answer, $normal_answer);
    $stmt_insert->execute();


    header("location:dashboard.php"); // Redirect to student dashboard after submitting
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Start Exam</title>
    <style>
        /* Basic reset */
        body, html {
            margin: 0 1%;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        /* Main container */
        .container {
            display: flex;
            justify-content: space-between;
            padding: 20px 10px;
            align-items: flex-start;
            height: 10vh;
            box-sizing: border-box;
        }

        /* Left section for the exam title */
        .left {
            width: 60%;
        }

        /* Right section for the timer */
        .right {
            width: 35%;
            display: flex;
            justify-content: flex-end;
            align-items: flex-start;
        }

        /* Timer style */
        #timer {
            font-size: 1.5em;
            font-weight: bold;
            padding: 10px 0;
            background-color: #ffcc00;
            border-radius: 5px;
        }

        /* Content area for questions and buttons */
        .content {
            display: flex;
            padding:0 20px;
            flex-direction: column;
            justify-content: space-between;
            height: 80%;
        }

        /* Question text style */
        .question {
            margin-bottom: 15px;
            font-size: 1.3em;
        }
        #question {
            font-size: 1.2em;
        }

        /* Textarea for answers */
        textarea {
            padding: 10px;
            margin-bottom: 20px;
            width: 100%;
            height: 50vh;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 1.2em;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-family: Consolas, "Courier New", monospace;
        }

        /* Buttons styling */
        button {
            padding: 10px 20px;
            margin: 10px 0;
            font-size: 1em;
            cursor: pointer;
            border: none;
            border-radius: 5px;
        }
        button#next_button{
            float:right;
        }
        button#back_button{
            float:left;
        }
    
        button#next_button, button#back_button {
            background-color: #4CAF50;
            color: white;
        }

        button#submit_button {
            float:right;
            background-color: #f44336;
            color: white;
        }

        /* Hide the hard question and back button initially */
        #hard_question, #hard_answer, #submit_button, #back_button {
            display: none;
        }
    </style>
    
    <script>
        // Disable back navigation and history manipulation
        window.history.forward();
        function noBack() { window.history.forward(); }
        setTimeout(noBack, 0);

        // 60-minute timer implementation
        let timeRemaining = 60 * 60; // 60 minutes
        let countdown = setInterval(function() {
            let minutes = Math.floor(timeRemaining / 60);
            let seconds = timeRemaining % 60;
            document.getElementById("timer").innerHTML = minutes + ":" + (seconds < 10 ? "0" : "") + seconds;
            timeRemaining--;

            if (timeRemaining < 0) {
                clearInterval(countdown);
                alert("Time's up! Submitting your exam automatically.");
                document.getElementById("examForm").submit(); // Auto-submit the form when time's up
            }
        }, 1000);

        // Disable right-click and text selection (for additional exam security)
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });
        document.addEventListener('selectstart', function(e) {
            e.preventDefault();
        });

        // Handle the browser close or tab switch attempt (using beforeunload)
        window.addEventListener('beforeunload', function (e) {
            e.preventDefault();
            e.returnValue = 'Are you sure you want to leave? Your exam will be automatically submitted if you leave without submitting.';
        });

        // Auto-submit on page close or navigation attempt
        function autoSubmitOnClose() {
            alert("You attempted to leave! Submitting your exam automatically.");
            document.getElementById("examForm").submit();
        }

        // Show the hard question after the normal question is answered
        function showHardQuestion() {
            // Hide the normal question and show the hard question
            document.getElementById('normal_question').style.display = 'none';
            document.getElementById('normal_answer').style.display = 'none';
            document.getElementById('next_button').style.display = 'none';

            document.getElementById('hard_question').style.display = 'block';
            document.getElementById('hard_answer').style.display = 'block';
            document.getElementById('submit_button').style.display = 'block';
            document.getElementById('back_button').style.display = 'block'; // Show the Back button
        }

        // Show the normal question when Back button is clicked
        function goBackToNormalQuestion() {
            // Hide the hard question and show the normal question
            document.getElementById('hard_question').style.display = 'none';
            document.getElementById('hard_answer').style.display = 'none';
            document.getElementById('submit_button').style.display = 'none';
            document.getElementById('back_button').style.display = 'none'; // Hide the Back button

            document.getElementById('normal_question').style.display = 'block';
            document.getElementById('normal_answer').style.display = 'block';
            document.getElementById('next_button').style.display = 'block'; // Show the Next button
        }
    </script>
</head>
<body>

<div class="container">
    <!-- Left Section (Exam Name) -->
    <div class="left">
        <h2>Exam Name: <?php echo htmlspecialchars($exam_name); ?></h2>
    </div>

    <!-- Right Section (Timer) -->
    <div class="right">
        <p>Time Remaining: <span id="timer">60:00</span></p>
    </div>
</div>

<!-- Content Section (Questions and Answer Section) -->
<div class="content">
    <form action="start_exam.php?exam_id=<?php echo $exam_id; ?>" method="POST" id="examForm">
        <!-- Normal Question -->
        <div id="normal_question">
            <h3 class="question">Normal Question:</h3>
            <p id="question"><?php echo htmlspecialchars($normal_question['question_text']); ?></p>
            <textarea name="normal_answer" required id="normal_answer"></textarea><br><br>

            <button type="button" id="next_button" onclick="showHardQuestion()">Next</button>
        </div>

        <!-- Hard Question (Initially hidden) -->
        <div id="hard_question" style="display:none;">
            <h3 class="question">Hard Question:</h3>
            <p id="question"><?php echo htmlspecialchars($hard_question['question_text']); ?></p>
            <textarea name="hard_answer" required id="hard_answer"></textarea><br><br>

            <!-- Back button to go back to normal question -->
            <button type="button" id="back_button" onclick="goBackToNormalQuestion()">Back</button>
            
            <!-- Submit button to submit the exam -->
            <button type="submit" id="submit_button" style="display:none;">Submit Exam</button>
        </div>
    </form>
</div>


</body>
</html>
<script>
document.addEventListener('keydown', function(event) {
    if (event.key === 'F5'  || (event.key === 'ArrowLeft' && event.altKey)) {
        event.preventDefault();
    }
});

</script>
<?php
$conn->close();
?>

