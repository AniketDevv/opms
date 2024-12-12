<?php
include_once 'db_connection.php';
session_start();

// Check if session variables are set
if (!isset($_SESSION['email'])) {
    header("location:login.php");
    exit;
}

$email = $_SESSION['email']; 

// Check if the session variables for username and admission number are set
if (isset($_SESSION['username']) && isset($_SESSION['admission_number'])) {
    $name = $_SESSION['username'];
    $admission_number = $_SESSION['admission_number'];
} else {
    echo "Error: User data is not available.";
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: auto;
            padding: 50px 0;
        }
        .dashboard {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .dashboard h1 {
            font-size: 2rem;
            margin-bottom: 20px;
        }
        .dashboard p {
            font-size: 1.2rem;
            margin-bottom: 20px;
        }
        .logout-btn {
            background-color: #8a4bff;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1rem;
        }
        .logout-btn:hover {
            background-color: #6a3cff;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="dashboard">
        <h1>Welcome, <?php echo htmlspecialchars($name); ?>!</h1>
        <p><strong>Username:</strong> <?php echo htmlspecialchars($name); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
        <p><strong>Admission Number:</strong> <?php echo htmlspecialchars($admission_number); ?></p>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</div>

</body>
</html>
