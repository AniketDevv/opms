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

    $sql_insert = "INSERT INTO exam_results (admission_number, exam_id, hard_question, normal_question, hard_answer, normal_answer)
                   VALUES (?, ?, ?, ?, ?, ?)";
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
</head>
<body>

<h2>Start Exam: <?php echo htmlspecialchars($exam_name); ?></h2>

<form action="start_exam.php?exam_id=<?php echo $exam_id; ?>" method="POST">
    <h3>Hard Question:</h3>
    <p><?php echo htmlspecialchars($hard_question['question_text']); ?></p>
    <textarea name="hard_answer" rows="4" cols="50" required></textarea><br><br>

    <h3>Normal Question:</h3>
    <p><?php echo htmlspecialchars($normal_question['question_text']); ?></p>
    <textarea name="normal_answer" rows="4" cols="50" required></textarea><br><br>

    <button type="submit">Submit Exam</button>
</form>

</body>
</html>

<?php
$conn->close();
?>

