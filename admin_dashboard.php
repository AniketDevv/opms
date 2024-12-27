<?php
include_once 'db_connection.php';
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['registered_id'])) {
    header("location:login.php");
    exit;
}

// Fetch the list of logged-in students
$sql = "SELECT username, email, admission_number FROM users"; 
$result = $conn->query($sql);
$loggedInUsers = [];  // Array to store logged-in users

// Check the session for active users
foreach ($_SESSION as $key => $value) {
    if (strpos($key, 'user_') === 0) {  // If session variable is a logged-in user
        // Add the user info to the logged-in users list
        $loggedInUsers[] = $value;
    }
}

// Handle user deletion
if (isset($_GET['delete_user_id'])) {
    $user_id = $_GET['delete_user_id'];

    // Prepare the SQL query to delete the user
    $delete_sql = "DELETE FROM users WHERE admission_number = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    // Redirect to the same page to refresh the user list
    header("location:admin_dashboard.php");
    exit;
}

// Fetch all exam results
$sql_results = "SELECT * FROM exam_results";
$results = $conn->query($sql_results);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        /* Reset margin and padding */
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
        }
        .top-container {
            background-color: #fff;
            padding: 10px 20px;
            height: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .top-container h1 {
            font-size: 2rem;
            float: left;
            margin: 0;
        }

        .top-container .logout-btn {
            background-color: #8a4bff;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1rem;
            float: right;
        }

        .top-container .logout-btn:hover {
            background-color: #6a3cff;
        }

        /* Navbar styles */
        .navbar {
            background-color: #333;
            overflow: hidden;
            padding: 0;
            margin: 0;
            clear: both;
        }

        .navbar a {
            float: left;
            display: block;
            color: white;
            padding: 14px 20px;
            text-align: center;
            text-decoration: none;
            width: 10%;  /* Equal width for each link */
        }

        .navbar a:hover {
            background-color: #575757;
        }

        .navbar .active {
            background-color: #8a4bff;
        }

        /* Tab content styles */
        .tab-content {
            display: none;
            padding: 20px;
        }

        /* Make the Home tab content visible by default */
        #home {
            display: block;
        }

        /* Table styles for user list */
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
        .delete-btn {
            background-color: red;
            color: white;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 5px;
        }

        .delete-btn:hover {
            background-color: darkred;
        }

    </style>
</head>
<body>

<div class="container">
    <!-- Top Container (Welcome and Logout Button) -->
    <div class="top-container">
        <h1>Welcome, Admin <?php echo htmlspecialchars($_SESSION['adminname']); ?>!</h1>
        <a href="logout.php" class="logout-btn">Logout</a>
        <div class="clear"></div>
    </div>

    <!-- Navbar just below the Welcome container -->
    <div class="navbar">
        <a href="javascript:void(0)" class="tab-link active" data-tab="home">Home</a>
        <a href="javascript:void(0)" class="tab-link" data-tab="users">Users</a>
        <a href="javascript:void(0)" class="tab-link" data-tab="add_exam">Add Exam</a>
        <a href="javascript:void(0)" class="tab-link" data-tab="results">Results</a>
    </div>

    <!-- Home Tab Content -->
    <div id="home" class="tab-content">
        <h2>Admin Dashboard</h2>
        <p>Welcome to the admin panel. Here you can manage users and exams.</p>
    </div>

    <!-- Users Tab Content -->
    <div id="users" class="tab-content">
        <h2>Logged-in Students</h2>
        <div class="student-list">
            <?php if ($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Admission Number</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['admission_number']); ?></td>
                                <td>
                                    <a href="?delete_user_id=<?php echo $row['admission_number']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No students are currently logged in.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Exam Tab Content -->
    <div id="add_exam" class="tab-content">
        <h2>Add New Exam</h2>
        <form action="add_exam.php" method="post">
    <label for="exam_name">Exam Name:</label><br>
    <input type="text" id="exam_name" name="exam_name" required><br><br>

    <label for="exam_date">Exam Date:</label><br>
    <input type="date" id="exam_date" name="exam_date" required><br><br>

    <h3>Hard-Level Questions:</h3>
    <div id="hard-questions-container">
        <div class="question-container">
            <label>Question 1:</label><br>
            <textarea name="hard_questions[]" rows="4" cols="50"></textarea><br><br>
        </div>
    </div>
    <button type="button" onclick="addQuestion('hard')">Add Another Hard Question</button><br><br>

    <h3>Normal-Level Questions:</h3>
    <div id="normal-questions-container">
        <div class="question-container">
            <label>Question 1:</label><br>
            <textarea name="normal_questions[]" rows="4" cols="50"></textarea><br><br>
        </div>
    </div>
    <button type="button" onclick="addQuestion('normal')">Add Another Normal Question</button><br><br>

    <input type="submit" value="Submit Exam">
    <?php
   
    ?>
</form>

<script>
    let hardQuestionCount = 1;  // To track the number of hard-level questions added
    let normalQuestionCount = 1;  // To track the number of normal-level questions added

    // Function to dynamically add questions
    function addQuestion(level) {
        let container, count;
        if (level === 'hard') {
            container = document.getElementById('hard-questions-container');
            count = ++hardQuestionCount;  // Increment the count for hard questions
        } else {
            container = document.getElementById('normal-questions-container');
            count = ++normalQuestionCount;  // Increment the count for normal questions
        }

        // Create a new question container
        const newQuestionContainer = document.createElement('div');
        newQuestionContainer.classList.add('question-container');

        // Create the new label
        const newLabel = document.createElement('label');
        newLabel.textContent = 'Question ' + count + ':';
        newQuestionContainer.appendChild(newLabel);
        newQuestionContainer.appendChild(document.createElement('br'));

        // Create a new textarea
        const newTextarea = document.createElement('textarea');
        newTextarea.name = level + "_questions[]";  // Name the input for array submission
        newTextarea.rows = 4;
        newTextarea.cols = 50;
        newQuestionContainer.appendChild(newTextarea);

        // Add a line break for better spacing
        const lineBreak = document.createElement('br');
        newQuestionContainer.appendChild(lineBreak);
        
        // Append the new question container to the appropriate container
        container.appendChild(newQuestionContainer);
    }
</script>


    </div>
    <div id="results" class="tab-content">
        <h2>Logged-in Students</h2>
        <?php if ($results->num_rows > 0): ?>
    <table border="1">
        <thead>
            <tr>
                <th>Admission Number</th>
                <th>Exam Name</th>
                <th>Hard Question</th>
                <th>Hard Question Answer</th>
                <th>Normal Question</th>
                <th>Normal Question Answer</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $results->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['admission_number']); ?></td>
                    <td><?php
                        $exam_query = "SELECT exam_name FROM exams WHERE id = ?";
                        $stmt = $conn->prepare($exam_query);
                        $stmt->bind_param("i", $row['exam_id']);
                        $stmt->execute();
                        $exam_result = $stmt->get_result();
                        $exam = $exam_result->fetch_assoc();
                        echo htmlspecialchars($exam['exam_name']);
                    ?></td>
                    <td><?php echo htmlspecialchars($row['hard_question']); ?></td>
                    <td><?php echo htmlspecialchars($row['hard_answer']); ?></td>
                    <td><?php echo htmlspecialchars($row['normal_question']); ?></td>
                    <td><?php echo htmlspecialchars($row['normal_answer']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No exam results available.</p>
<?php endif; ?>
    </div>
</div>

<script>
    // Get all tab links
    const tabLinks = document.querySelectorAll('.tab-link');
    
    // Get all tab content
    const tabContents = document.querySelectorAll('.tab-content');
    
    // Add click event to each tab link
    tabLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Remove the active class from all tabs
            tabLinks.forEach(link => link.classList.remove('active'));
            
            // Hide all tab contents
            tabContents.forEach(content => content.style.display = 'none');
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Show the content corresponding to the clicked tab
            const activeTab = this.getAttribute('data-tab');
            document.getElementById(activeTab).style.display = 'block';
        });
    });
</script>

</body>
</html>

<?php
$conn->close();
?>
