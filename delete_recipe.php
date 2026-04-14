<?php
include 'config.php';

$id = $_POST['id'];
$stmt = $conn->prepare("DELETE FROM recipes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: admin.php");
exit;
?>
