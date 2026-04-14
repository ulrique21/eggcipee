<?php
include 'config.php';

$name = $_POST['name'];
$image_url = $_POST['image_url'];
$info = $_POST['info'];
$link = $_POST['link'];

$stmt = $conn->prepare("INSERT INTO recipes (name, image_url, info, link) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $image_url, $info, $link);
$stmt->execute();

header("Location: admin.php");
exit;
?>
