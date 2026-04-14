<?php
session_start();
require_once 'config.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$id = intval($_GET['id']);
$conn->query("UPDATE users SET role='admin' WHERE id=$id");
header("Location: dashboard.php?msg=User promoted to admin");
exit();
