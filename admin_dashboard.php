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
    <!-- Include PrismJS CSS for styling -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.25.0/themes/prism.min.css" rel="stylesheet" />
    <!-- Include PrismJS JS library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.25.0/prism.min.js"></script>
    <!-- Include Java language support for PrismJS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.25.0/components/prism-java.min.js"></script>
    <!-- Include a date-time picker library -->
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <!-- Include Flatpickr JavaScript for date-time picker -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <title>Admin Dashboard</title>
    <style>
        /* Reset margin and padding */
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
        }
        .top-container {
            background: linear-gradient(45deg,rgb(3, 3, 3),rgb(139, 139, 139));
            padding: 10px 20px;
            height: 40px;
            margin-bottom:1px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.6);
        }

        .top-container h1 {
            font-size: 2rem;
            color:rgb(204, 218, 195);
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
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.5);
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
        .home-dashboard{
            display:flex;
            padding: 30px 100px;
            margin-left:90px;
        }
        .dashboard{
            width:800px;
            margin-top:70px;
            text-align:center;
        }
        
        .dashboard h1{
            font-family: 'Dancing Script', cursive;
            font-weight: 700;
            font-size: 25px;
            text-transform: uppercase;
            color: #2D2D2D;
            margin-bottom: 15px;

        }
        .dashboard p {
            font-family: 'Pacifico', cursive;
            font-weight: normal;
            font-size: 17px;
            color:rgb(75, 73, 73);
        }
        .img1{
          width: 1000px; /* Example width of container */
          height: 400px; /* Example height of container */
          margin-left:100px;
        }

        .img {
            margin-top:50px;
            max-width: 100%; /* Optional: ensures the image doesn’t overflow the container */
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
        /* Styling for Exam Name and Exam Date (floating) */
        .exam-header {
            display: flex;
            justify-content: space-between;
        }

        .exam-name, .exam-date {
            width: 48%; /* Ensures that they are side by side with some space between */
        }

        .exam-date {
            text-align: left;
        }

        .exam-name label, .exam-date label {
            font-weight: bold;
        }

        .exam-name input, .exam-date input {
            width: 100%;
            padding: 8px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        /* Section for Questions (Normal and Hard Questions) */
        .question-section {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .normal-questions, .hard-questions {
            width: 48%;
        }

        .normal-questions h3, .hard-questions h3 {
            font-size: 1.2rem;
        }

        .question-container {
            margin-bottom: 20px;
        }

        .normal-questions textarea, .hard-questions textarea {
            width: 100%;
            padding: 8px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .normal-questions button, .hard-questions button {
            background-color: #8a4bff;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }

        .normal-questions button:hover, .hard-questions button:hover {
            background-color: #6a3cff;
        }

        /* Submit Button Styling */
        .form-submit {
            text-align: right;
            margin-top: 20px;
        }

        .form-submit input[type="submit"] {
            background-color: #8a4bff;
            color: white;
            padding: 12px 24px;
            font-size: 1.2rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .form-submit input[type="submit"]:hover {
            background-color: #6a3cff;
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
    <div class="home-dashboard">
        <div class="dashboard">
            <h1>Ensuring Fairness, Integrity, and Excellence in Every Exam</h1>
            <p>As an Exam Administrator, we ensure a secure, fair, and efficient testing environment,
                 giving every student an equal opportunity to succeed while upholding integrity in the process</p>
        </div>
        <div class="img1">
             <img src="img1.png" alt="Illustration" class="img">
        </div>
        </div>
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
        <!-- Exam Name and Exam Date Floating -->
        <div class="exam-header">
            <!-- Exam Name (float right) -->
            <div class="exam-name">
                <label for="exam_name">Exam Name:</label><br>
                <input type="text" id="exam_name" name="exam_name" required><br><br>
            </div>

            <!-- Exam Date (float left) -->
            <div class="exam-date">
                <label for="exam_date">Exam Date:</label><br>
                <input type="date" id="exam_date" name="exam_date" required><br><br>
            </div>
        </div>

        <!-- Normal and Hard-Level Questions Layout -->
        <div class="question-section">
            <div class="normal-questions">
                <h3>Normal-Level Questions:</h3>
                <div id="normal-questions-container">
                    <div class="question-container">
                        <label>Question 1:</label><br>
                        <textarea name="normal_questions[]" rows="4" cols="50"></textarea><br><br>
                    </div>
                </div>
                <button type="button" onclick="addQuestion('normal')">Add Another Normal Question</button><br><br>
            </div>

            <div class="hard-questions">
                <h3>Hard-Level Questions:</h3>
                <div id="hard-questions-container">
                    <div class="question-container">
                        <label>Question 1:</label><br>
                        <textarea name="hard_questions[]" rows="4" cols="50"></textarea><br><br>
                    </div>
                </div>
                <button type="button" onclick="addQuestion('hard')">Add Another Hard Question</button><br><br>
            </div>
        </div>

        <!-- Submit Button (aligned to the right) -->
        <div class="form-submit">
            <input type="submit" value="Submit Exam">
        </div>
    </form>
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
    <h2>Exam Results</h2>

    <?php
    // SQL query to get exam results along with exam name and date
    $sql_exam_results = "SELECT er.*, e.exam_name, e.exam_date 
                         FROM exam_results er
                         INNER JOIN exams e ON er.exam_id = e.id
                         ORDER BY e.exam_date DESC, e.exam_name ASC"; // Order by exam date and name
    $result_exam_results = $conn->query($sql_exam_results);

    // Group results by exam name and date
    $examResults = [];
    if ($result_exam_results->num_rows > 0) {
        while ($row = $result_exam_results->fetch_assoc()) {
            // Group by exam name and date
            $examResults[$row['exam_name']][$row['exam_date']][] = $row;
        }
    }

    // Loop through the grouped results and display them
    if (count($examResults) > 0) {
        foreach ($examResults as $exam_name => $dates) {
            echo "<h3>Exam: " . htmlspecialchars($exam_name) . "</h3>";
            foreach ($dates as $exam_date => $results) {
                echo "<h4>Exam Date: " . htmlspecialchars($exam_date) . "</h4>";
                echo "<table border='1' class='exam-results-table'>
                        <thead>
                            <tr>
                                <th>Admission Number</th>
                                <th>Hard Question</th>
                                <th>Hard Question Answer</th>
                                <th>Normal Question</th>
                                <th>Normal Question Answer</th>
                                <th>Attempted/Not-Attempted</th>
                            </tr>
                            <tr>
                                <th colspan='6' class='toggle-header'>Show results</th>
                            </tr>
                        </thead>
                        <tbody class='toggle-body' style='display: none;'>"; // Hide rows by default

                // Display the results for this particular exam and date
                foreach ($results as $row) {
                    echo "<tr>
                            <td>" . htmlspecialchars($row['admission_number']) . "</td>
                            <td>" . htmlspecialchars($row['hard_question']) . "</td>
                            <td><pre><code class='language-java'>" . htmlspecialchars($row['hard_answer']) . "</code></pre></td>
                            <td>" . htmlspecialchars($row['normal_question']) . "</td>
                            <td><pre><code class='language-java'>" . htmlspecialchars($row['normal_answer']) . "</code></pre></td>
                            <td>" . htmlspecialchars($row['status']) . "</td>
                          </tr>";
                }
                echo "</tbody>
                    </table>";
            }
        }
    } else {
        echo "<p>No exam results available.</p>";
    }
    ?>
</div>

<script>
    // JavaScript to toggle table rows visibility when the header is clicked
    document.querySelectorAll('.toggle-header').forEach(header => {
        header.addEventListener('click', function() {
            // Find the corresponding tbody and toggle its visibility
            const tbody = this.closest('table').querySelector('.toggle-body');
            const isVisible = tbody.style.display === 'table-row-group';

            // Toggle the display property of the tbody
            tbody.style.display = isVisible ? 'none' : 'table-row-group';
        });
    });
</script>


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
<script>
        // Initialize the flatpickr date-time picker
        flatpickr("#exam_date", {
            enableTime: true,
            dateFormat: "Y-m-d H:i:s", // Format: Year-Month-Day Hour:Minute
            minDate: "today" // Ensure that the exam date is today or in the future
        });
    </script>

</body>
</html>

<?php
$conn->close();
?>
