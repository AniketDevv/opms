<?php
include 'db_connection.php';
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data and sanitize
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $registered_id = trim($_POST['registered-id']);

    // Prevent SQL injection
    $email = $conn->real_escape_string($email);
    $password = $conn->real_escape_string($password);
    $registered_id = $conn->real_escape_string($registered_id);

    // Determine if the login is for a student or an admin
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
    } else if (!empty($registered_id)) {
        // Admin login
        $sql = "SELECT * FROM admin WHERE registered_id='$registered_id' LIMIT 1";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                // Start session for admin
                $_SESSION['registered_id'] = $registered_id;
                $_SESSION['username'] = $row['username'];

                header("Location: admin_dashboard.php");
                exit();
            } else {
                echo "Invalid Registered ID or password.";
            }
        } else {
            echo "Invalid Registered ID or passwords.";
        }
    } else {
        echo "Please provide either an email or Registered ID.";
    }
    $conn->close();
}else {
    // If the request method is not POST, show an error
    echo "Invalid request method.";
}



?>
