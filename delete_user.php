<?php
session_start();
require_once 'config.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$id = intval($_GET['id']);
$conn->query("DELETE FROM users WHERE id=$id");
header("Location: dashboard.php?msg=User deleted");
exit();
