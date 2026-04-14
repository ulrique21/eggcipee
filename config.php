<?php
$servername = getenv('mysql.railway.internal');
$username   = getenv('root');
$password   = getenv('tFtgMquFNgZifOPLasFsSRxfFAHtCfyS');
$dbname     = getenv('railway');

if (!$servername || !$username || !$password || !$dbname) {
    die("Missing environment variables");
}

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>