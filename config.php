<?php
$servername = $_ENV['mysql.railway.internal'];
$username   = $_ENV['root'];
$password   = $_ENV['tFtgMquFNgZifOPLasFsSRxfFAHtCfyS'];
$dbname     = $_ENV['railway'];

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>