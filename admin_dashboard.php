<?php
include_once 'db_connection.php';
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['registered_id'])) {
    header("location:login.php");
    exit;
}

// Fetch the list of logged-in students
$sql = "SELECT username, email, admission_number FROM users"; // assuming there's a column 'session_active' that tracks if a user is logged in
$result = $conn->query($sql);
$loggedInUsers = [];  // Array to store logged-in users

// Check the session for active users
foreach ($_SESSION as $key => $value) {
    if (strpos($key, 'user_') === 0) {  // If session variable is a logged-in user
        // Add the user info to the logged-in users list
        $loggedInUsers[] = $value;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
        .student-list {
            margin-top: 20px;
        }
        .student-list table {
            width: 100%;
            border-collapse: collapse;
        }
        .student-list table, th, td {
            border: 1px solid #ddd;
        }
        .student-list th, td {
            padding: 10px;
            text-align: left;
        }
        .student-list th {
            background-color: #f2f2f2;
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
        <h1>Welcome, Admin <?php echo htmlspecialchars($_SESSION['adminname']); ?>!</h1>

        <h2>Logged-in Students</h2>
        <div class="student-list">
            <?php if ($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Admission Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['admission_number']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No students are currently logged in.</p>
            <?php endif; ?>
        </div>

        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</div>

</body>
</html>

<?php
$conn->close();
?>
