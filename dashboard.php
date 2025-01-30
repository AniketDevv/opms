<?php
include_once 'db_connection.php';
session_start();
date_default_timezone_set('Asia/Kolkata');
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
$sql = "SELECT * FROM exams WHERE exam_date >= NOW()"; // Only active exams
$exams_result = $conn->query($sql);

$sql = "SELECT * FROM exams WHERE exam_date < NOW()"; // Only active exams
$exams_result_1 = $conn->query($sql);

// Fetch attempted exams from the exam_results table
$sql_attempted = "SELECT exams.exam_name, exams.exam_date, exam_results.status,exam_results.exam_id 
                      FROM exam_results 
                      JOIN exams ON exam_results.exam_id = exams.id 
                      WHERE exam_results.admission_number = ? AND exam_results.status = 'attempted'";
$stmt = $conn->prepare($sql_attempted);
$stmt->bind_param("i", $_SESSION['admission_number']);
$stmt->execute();
$result = $stmt->get_result();

// Store the IDs of attempted exams
$attempted_exams = [];
while ($row = $result->fetch_assoc()) {
    $attempted_exams[] = $row['exam_id'];
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
        .top-container {
            background: linear-gradient(45deg,rgb(3, 3, 3),rgb(139, 139, 139));
            padding: 10px 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
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
        .clear {
            clear: both;
        }
        .tab-content {
            display: none;
            background-color:rgb(243, 243, 243);
            height: calc(100vh - 150px); /* Full screen height minus the top container and navbar height */
            overflow-y: auto; /* Allow scrolling if content is longer than the screen */
            padding: 20px;
        }

        /* Make the Home tab content visible by default */
        #home {
            display: block;
        }
        .home-dashboard{
            display:flex;
            padding: 50px;
            margin-left:80px;
        }
        .dashboard{
            width:700px;
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
        }

        .img {
            margin-top:50px;
        max-width: 100%; /* Optional: ensures the image doesn’t overflow the container */
        }
        /* Navbar below top container */
        .navbar {
            background-color: #333;
            overflow: hidden;
            box-shadow: 0 10px 8px 0 rgba(0, 0, 0, 1);
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
        /* Profile Dropdown Styles */
        .profile-dropdown {
            position: relative;
            display: inline-block;
            float: right;
        }

        .profile-btn {
            background-color: #8a4bff;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
        }

        .profile-btn:hover {
            background-color: #6a3cff;
        }
        .dropdown {
            display: none; /* Initially hidden */
            position: absolute;
            top: 100%;  /* Make the dropdown appear below the button */
            right: 0;
            background-color:rgb(34, 33, 33);
            min-width: 160px;
            border: 1px solid #ccc;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            z-index: 1;
        }

        .dropdown a {
            padding: 12px 16px;
        }

        .dropdown a {
            color: white;
            text-decoration: none;
            display: block;
        }

        .dropdown a:hover {
            background-color: #575757;
        }
        .dropdown.show {
            display: block; /* Shows the dropdown */
        }

        /* Style for Exam Link */
        #Exam_link {
            background-color:rgb(242, 233, 243); 
            color: white;    
            width: 100px;        
            padding: 10px 20px;        
            border-radius: 5px;        /* Rounded corners */     /* Remove underline */
            /*display: inline-block;  */   /* Make it an inline block */
            font-size: 1.1rem;         /* Slightly larger font */
            margin: 10px 0;            /* Space between links */
            transition: background-color 0.3s ease, transform 0.2s ease; /* Smooth transition on hover */
        }
        #Exam_link a{
            color: black; 
            text-decoration: none;
        /* Hover effect */
        }
        #Exam_link a:hover {
            color:rgb(86, 0, 245); 
        /* Hover effect */
        }
        #Exam_link:hover {
            background-color:rgb(243, 235, 245); /* Slightly darker green */
            transform: scale(1.05);     /* Slight zoom effect */
        }

        /* Active state */
        #Exam_link:active {
            background-color:rgb(36, 37, 36); /* Even darker green */
            transform: scale(0.98);     /* Slight shrink effect on click */
        }
        .rule_container {
        padding: 0px 20px;
        background-color:rgb(243, 243, 243);
        border-radius: 5px;
        height: 100%;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        ol li{
            margin: 50px;
        }
        ol li::before {
        width: 30px; /* Set width of the number */
        height: 30px; /* Set height of the number */
        display: 50% 50%;
        align-items:center;
        justify-items:center;
        padding: 18px;
        background-color:rgb(204, 171, 221); /* Optional: background color for clarity */
        font-size: 18px; /* Adjust font size to fit inside the circle */
        }

        .icon_container{
            display:flex;
            align-items: center; 
        }
        .check-icon {
        font-size: 24px;
        color: black;
        padding: 10px;
        border:2px solid black;
        margin-bottom: 0px;
        }
        .check-icon-1{
            padding-left:15px;
            align-items: center; 
            
        }
        .check-icon-1 h2 {
        font-size: 20px;
        margin-top: 35px;
        
        }

        .check-icon-1 p {
        font-size: 16px;
        line-height: 0;
        margin-bottom: 50px;
        }

        .importance-instructions {
        background-color:rgb(223, 222, 223);
        border:2px solid rgb(190, 120, 190);
        padding: 20px;
        border-radius: 5px;
        margin-bottom: 30px;
        }

        .importance-instructions h3 {
        font-size: 18px;
        margin-bottom: 10px;
        }

        ol {
        counter-reset: my-counter;
        }

        ol li {
        list-style-type: none;
        counter-increment: my-counter;
        padding-left: 20px;
        margin-bottom: 10px;
        }

        ol li::before {
        content: counter(my-counter) ". ";
        margin-left: -20px;
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
        <div class="profile-dropdown">
        <button id="profile-btn" class="profile-btn">Profile Details</button>
        <div class="dropdown">
            <a><p><strong>Username:</strong> <?php echo htmlspecialchars($name); ?></p></a>
            <a><p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p></a>
            <a> <p><strong>Admission Number:</strong> <?php echo htmlspecialchars($admission_number); ?></p></a>  
           
</div>
    </div>
    </div>

    <!-- Home Tab Content -->
    <div id="home" class="tab-content">
        <div class="home-dashboard">
        <div class="dashboard">
            <h1>Master Your Skills with Our Online Exam System</h1>
            <p>Welcome to our online exam platform, where coding challenges help you sharpen your 
                skills and track your progress. Whether you're preparing for exams or improving your
                 coding abilities, we're here to support your learning journey.</p>
        </div>
        <div class="img1">
             <img src="img.png" alt="Illustration" class="img">
        </div>
        </div>
    </div>

    <!-- Active Tab Content -->
    <div id="active" class="tab-content">
    <h2>Active Section</h2>
    <?php if ($exams_result->num_rows > 0): ?>
    <ul>
        <?php while ($row = $exams_result->fetch_assoc()): ?>
            <?php 
                $exam_date = strtotime($row['exam_date']); // Convert exam date-time to Unix timestamp
                $current_time = time(); // Get current date and time as Unix timestamp  
            ?>
            <!-- If the exam date-time is in the future, show a message -->
            <?php if ($exam_date > $current_time): ?>
                <li id="Exam_link"><span><?php echo htmlspecialchars($row['exam_name']); ?> (Starts later)</span></li>
            <?php endif; ?>
        <?php endwhile; ?>
    </ul>
    <?php endif; ?>
    <?php if ($exams_result_1->num_rows > 0): ?>
    <ul>
        <?php while ($row = $exams_result_1->fetch_assoc()): ?>
            <?php 
                $exam_date = strtotime($row['exam_date']); // Convert exam date-time to Unix timestamp
                $current_time = time(); // Get current date and time as Unix timestamp  
            ?>
            <?php if ($exam_date <= $current_time && !in_array($row['id'], $attempted_exams)): ?>
                <!-- If the current time is after the exam's scheduled time, show Start link -->
                <li id="Exam_link"><a href="start_exam.php?exam_id=<?php echo $row['id']; ?>">Start <?php echo htmlspecialchars($row['exam_name']); ?></a></li>
            <?php endif; ?>
        <?php endwhile; ?>
    </ul>
    <?php else: ?>
        <p>No active exams at the moment.</p>
    <?php endif; ?>
</div>

    <!-- Previous Tab Content -->
    <div id="previous" class="tab-content">
        <h2>Previous Exams</h2>
        <?php
    // Fetch exams that have been attempted by the student
    $sql_previous = "SELECT exams.exam_name, exams.exam_date, exam_results.status, exam_results.exam_id 
                     FROM exam_results 
                     JOIN exams ON exam_results.exam_id = exams.id 
                     WHERE exam_results.admission_number = ? AND exam_results.status = 'attempted'";
    $stmt_previous = $conn->prepare($sql_previous);
    $stmt_previous->bind_param("i", $_SESSION['admission_number']);
    $stmt_previous->execute();
    $previous_result = $stmt_previous->get_result();
    
    // Check if there are any attempted exams
    if ($previous_result->num_rows > 0): ?>
    <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr>
                <th>Exam Name</th>
                <th>Exam Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $previous_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['exam_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['exam_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>No exams attempted yet.</p>
    <?php endif; ?>
    </div>

    <!-- Rules Tab Content -->
    <div id="rules" class="tab-content">
        <div class="rule_container">
           <div class="icon_container">
                <div class="check-icon">✓</div>
                <div class="check-icon-1">
                   <h2>Step-by-Step Guide</h2>
                   <p>Follow these instructions to navigate the exam platform smoothly.</p>
                </div>
            </div>
            <div class="importance-instructions">
                <h3>IMPORTANCE INSTRUCTIONS</h3>
                <p>Clear instructions help students navigate the exam platform with ease, reducing confusion and stress. Read the instructions carefully before starting the exam.</p>
            </div>

        <ol>
           <li>-------------------------------------------------------------------------------------</li>
           <li>-------------------------------------------------------------------------------------</li>
           <li>-------------------------------------------------------------------------------------</li>
        </ol>
        </div>


    </div>

    <!-- About Tab Content -->
    <div id="about" class="tab-content">
        <h2>About Section</h2>
        <p>Details and content related to the about section will go here.</p>
    </div>
</div>

<script>
   // Toggle Profile Dropdown
   // Toggle Profile Dropdown
   document.getElementById('profile-btn').addEventListener('click', function(event) {
      event.stopPropagation(); // Prevent the click from propagating to other elements
      var dropdown = document.querySelector('.dropdown');
      dropdown.classList.toggle('show');  // Toggle the dropdown visibility
   });

   // Close the dropdown if clicking outside
   document.addEventListener('click', function(event) {
      var dropdown = document.querySelector('.dropdown');
      var profileBtn = document.getElementById('profile-btn');
      if (!profileBtn.contains(event.target) && !dropdown.contains(event.target)) {
          dropdown.classList.remove('show');
      }
   });
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
