<?php
include 'db_connection.php';
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data and sanitize
    $registered_id = isset($_POST['registered-id']) ? trim($_POST['registered-id']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Prevent SQL injection
    $registered_id = $conn->real_escape_string($registered_id);
    $password = $conn->real_escape_string($password);

    if (!empty($registered_id)) {
        // Admin login
        $sql = "SELECT * FROM admin WHERE registered_id='$registered_id' LIMIT 1";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['passwords'])) {
                // Start session for admin
                $_SESSION['registered_id'] = $registered_id;
                $_SESSION['adminname'] = $row['adminname'];

                header("Location: admin_dashboard.php");
                exit();
            } else {
                echo "Invalid Registered ID or password.";
            }
        } else {
            echo "Invalid Registered ID or password.";
        }
    } else {
        echo "Please provide your Registered ID.";
    }
    $conn->close();
} else {
    // If the request method is not POST, show an error
    echo "Invalid request method.";
}
?>
