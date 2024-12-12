<?php
// Include your database connection (replace with actual credentials)
include 'db_connection.php';

// Check if the form is submitted via POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = $_POST['username'];
    $email = $_POST['email'];
    $admission_number = $_POST['admission_number'];
    $password = $_POST['password']; // Plain password (we'll hash it)

    // Validate if all fields are filled
    if (empty($username) || empty($email) || empty($admission_number) || empty($password)) {
        echo "All fields are required. Please fill in all fields.";
        exit;
    }
    // Validate if the email ends with '@gmail.com'
    if (!preg_match('/^[a-zA-Z0-9._%+-]+@gmail\.com$/', $email)) {
        echo "Please provide a valid Gmail address (e.g., username@gmail.com).";
        exit;
    }

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if the email already exists in the database
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo "Email already exists. Please use a different email.";
        exit;
    }

    // Prepare the SQL statement to insert the new user
    $sql = "INSERT INTO users (username, email, admission_number, password) VALUES (?, ?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters and execute the statement
        $stmt->bind_param("ssss", $username, $email, $admission_number, $hashed_password);
        
        if ($stmt->execute()) {
            // If registration is successful, redirect to login page
            header("Location: login.html"); // Redirect to login page
            exit();
        } else {
            echo "Error saving to the database.";
        }
        $stmt->close();
    } else {
        echo "Error preparing the SQL statement.";
    }

    // Close the database connection
    $conn->close();
} else {
    // If the request method is not POST, show an error
    echo "Invalid request method.";
}
?>
