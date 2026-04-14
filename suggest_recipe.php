<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $recipeName = trim($_POST['recipe_name'] ?? '');
    $details = trim($_POST['details'] ?? '');

    // Basic validation
    if ($name === '' || $email === '' || $recipeName === '') {
        echo "<script>alert('Please complete all required fields.'); window.history.back();</script>";
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Please enter a valid email address.'); window.history.back();</script>";
        exit;
    }

    // Determine logged-in user if any
    session_start();
    $userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

    // Ensure suggestions table exists
    $createSql = "CREATE TABLE IF NOT EXISTS suggestions (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NULL,
        name VARCHAR(120) NOT NULL,
        email VARCHAR(190) NOT NULL,
        recipe_name VARCHAR(190) NOT NULL,
        details TEXT NULL,
        status ENUM('new','reviewing','added','rejected') NOT NULL DEFAULT 'new',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX (status),
        INDEX (created_at),
        INDEX (recipe_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    if (!$conn || !($conn instanceof mysqli)) {
        echo "<script>alert('Database connection not available.'); window.history.back();</script>";
        exit;
    }

    if (!$conn->query($createSql)) {
        $err = addslashes($conn->error);
        echo "<script>alert('Failed to ensure table exists: {$err}'); window.history.back();</script>";
        exit;
    }

    // Insert suggestion
    $stmt = $conn->prepare("INSERT INTO suggestions (user_id, name, email, recipe_name, details, status) VALUES (?, ?, ?, ?, ?, 'new')");
    if (!$stmt) {
        $err = addslashes($conn->error);
        echo "<script>alert('Failed to prepare insert: {$err}'); window.history.back();</script>";
        exit;
    }
    $stmt->bind_param('issss', $userId, $name, $email, $recipeName, $details);
    if (!$stmt->execute()) {
        $err = addslashes($stmt->error);
        $stmt->close();
        echo "<script>alert('Failed to save suggestion: {$err}'); window.history.back();</script>";
        exit;
    }
    $stmt->close();

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'muenriquez@tip.edu.ph';
        $mail->Password   = 'tsacmfxzhmzvnhxv';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('muenriquez@tip.edu.ph', 'Eggcipe Suggestions');
        $mail->addReplyTo($email, $name);
        $mail->addAddress('muenriquez@tip.edu.ph', 'Website Admin');

        $mail->isHTML(true);
        $mail->Subject = 'Recipe Suggestion: ' . $recipeName;
        $body = "<strong>From:</strong> " . htmlspecialchars($name) . " (" . htmlspecialchars($email) . ")<br>" .
                "<strong>Suggested Recipe:</strong> " . htmlspecialchars($recipeName) . "<br>" .
                "<strong>Details:</strong><br>" . nl2br(htmlspecialchars($details));
        $mail->Body = $body;

        $mail->send();
        echo "<script>alert('✅ Thanks! Your recipe suggestion was saved and sent.'); window.location.href='index.php';</script>";
    } catch (Exception $e) {
        echo "<script>alert('❌ Suggestion saved but email failed: {$mail->ErrorInfo}'); window.location.href='index.php';</script>";
    }
} else {
    header('Location: index.php');
}
?>


