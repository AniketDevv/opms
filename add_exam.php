<?php
include_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exam_name = $_POST['exam_name'];
    $exam_date = $_POST['exam_date'];

    // Insert exam into the database
    $sql = "INSERT INTO exams (exam_name, exam_date) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $exam_name, $exam_date);
    if ($stmt->execute()) {
        // Get the exam_id of the newly inserted exam
        $exam_id = $stmt->insert_id;  // Get the last inserted ID

        // Insert hard-level questions
        if (!empty($_POST['hard_questions'])) {
            foreach ($_POST['hard_questions'] as $question_text) {
                $stmt = $conn->prepare("INSERT INTO questions (question_text, difficulty, exam_id) VALUES (?, 'hard', ?)");
                $stmt->bind_param("si", $question_text, $exam_id);
                $stmt->execute();
            }
        }

        // Insert normal-level questions
        if (!empty($_POST['normal_questions'])) {
            foreach ($_POST['normal_questions'] as $question_text) {
                $stmt = $conn->prepare("INSERT INTO questions (question_text, difficulty, exam_id) VALUES (?, 'normal', ?)");
                $stmt->bind_param("si", $question_text, $exam_id);
                $stmt->execute();
            }
        }

        // Redirect to the admin dashboard after adding the exam and questions
        header("location:admin_dashboard.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;  // If the exam insertion fails
    }
}
?>
