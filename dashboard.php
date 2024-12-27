<?php
include_once 'db_connection.php';
session_start();
// Check if session variables are set
if (!isset($_SESSION['admission_number'])) {
    header("location:login.php");
    exit;
}
$user_id = $_SESSION['admission_number'];
$email = $_SESSION['email']; 

// Check if the session variables for username and admission number are set
if (isset($_SESSION['username']) && isset($_SESSION['admission_number'])) {
    $name = $_SESSION['username'];
    $admission_number = $_SESSION['admission_number'];
} else {
    echo "Error: User data is not available.";
    exit;
}
// Fetch active exams for student
$sql = "SELECT * FROM exams WHERE exam_date > NOW()"; // Only active exams
$exams_result = $conn->query($sql);
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
        .top-container {
            background-color: #fff;
            padding: 10px 20px;
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
        .clear {
            clear: both;
        }
        .tab-content {
            display: none;
            background:white;
            height: calc(100vh - 150px); /* Full screen height minus the top container and navbar height */
            overflow-y: auto; /* Allow scrolling if content is longer than the screen */
            padding: 20px;
        }

        /* Make the Home tab content visible by default */
        #home {
            display: block;
        }

        /* Navbar below top container */
        .navbar {
            background-color: #333;
            overflow: hidden;
        }
        .navbar a {
            float: left;
            display: block;
            color: white;
            padding: 14px 20px;
            text-align: center;
            text-decoration: none;
            width: 10%;
        }
        .navbar a:hover {
            background-color: #575757;
        }
        .navbar .active {
            background-color: #8a4bff;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Top Container (Welcome and Logout Button) -->
    <div class="top-container">
        <h1>Welcome, <?php echo htmlspecialchars($name); ?>!</h1>
        <a href="logout.php" class="logout-btn">Logout</a>
        <div class="clear"></div>
    </div>

    <!-- Navbar just below the Welcome container -->
    <div class="navbar">
        <a href="javascript:void(0)" class="tab-link active" data-tab="home">Home</a>
        <a href="javascript:void(0)" class="tab-link" data-tab="active">Active</a>
        <a href="javascript:void(0)" class="tab-link" data-tab="previous">Previous</a>
        <a href="javascript:void(0)" class="tab-link" data-tab="rules">Rules</a>
        <a href="javascript:void(0)" class="tab-link" data-tab="about">About Us</a>
    </div>

    <!-- Home Tab Content -->
    <div id="home" class="tab-content">
        <div class="dashboard">
            <p><strong>Username:</strong> <?php echo htmlspecialchars($name); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
            <p><strong>Admission Number:</strong> <?php echo htmlspecialchars($admission_number); ?></p>
        </div>
    </div>

    <!-- Active Tab Content -->
    <div id="active" class="tab-content">
        <h2>Active Section</h2>
        <p>Details and content related to the active section will go here.</p>
        <?php if ($exams_result->num_rows > 0): ?>
    <ul>
        <?php while ($row = $exams_result->fetch_assoc()): ?>
            <li><a href="start_exam.php?exam_id=<?php echo $row['id']; ?>">Start <?php echo htmlspecialchars($row['exam_name']); ?></a></li>
        <?php endwhile; ?>
    </ul>
<?php else: ?>
    <p>No active exams at the moment.</p>
<?php endif; ?>
    </div>

    <!-- Previous Tab Content -->
    <div id="previous" class="tab-content">
        <h2>Previous Section</h2>
        <p>Details and content related to the previous section will go here.</p>
    </div>

    <!-- Rules Tab Content -->
    <div id="rules" class="tab-content">
        <h2>Rules Section</h2>
        <p>Details and content related to the rules section will go here.</p>
    </div>

    <!-- About Tab Content -->
    <div id="about" class="tab-content">
        <h2>About Section</h2>
        <p>Details and content related to the about section will go here.</p>
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
