<?php
include 'db_connection.php';
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data and sanitize
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Prevent SQL injection
    $email = $conn->real_escape_string($email);
    $password = $conn->real_escape_string($password);

    if (!empty($email)) {
        // Student login
        $sql = "SELECT * FROM users WHERE email='$email' LIMIT 1";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                // Start session for student
                $_SESSION['email'] = $email;
                $_SESSION['username'] = $row['username'];
                $_SESSION['admission_number'] = $row['admission_number'];

                header("Location: dashboard.php");
                exit();
            } else {
                echo "Invalid email or password.";
            }
        } else {
            echo "Invalid email or password.";
        }
    } else {
        echo "Please provide your email.";
    }
    $conn->close();
} else {
    // If the request method is not POST, show an error
    echo "Invalid request method.";
}
?>
