<?php
$conn = new mysqli(
  $_ENV['mysql.railway.internal'],
  $_ENV['root'],
  $_ENV['tFtgMquFNgZifOPLasFsSRxfFAHtCfyS'],
  $_ENV['railway']
);


// ✅ Create only one connection (the one login.php uses)
$conn = new mysqli($servername, $username, $password, $dbname);

// ✅ Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
