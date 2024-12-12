<?php

$host = 'localhost';       
$username = 'root';        
$password = '';            
$database = 'opms'; 

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optionally, you can set the character set to UTF-8 for better handling of special characters
$conn->set_charset("utf8");
?>
