<?php
$servername = "localhost"; 
$username = "root";        
$password = "";           
$dbname = "eggcipe";

// ✅ Create only one connection (the one login.php uses)
$conn = new mysqli($servername, $username, $password, $dbname);

// ✅ Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
