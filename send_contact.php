<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $message = $_POST['message'] ?? '';

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'muenriquez@tip.edu.ph';
        $mail->Password   = 'tsacmfxzhmzvnhxv'; // ✅ no spaces
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // FROM: your email | REPLY-TO: sender
        $mail->setFrom('muenriquez@tip.edu.ph', 'Website Contact Form');
        $mail->addReplyTo($email, $name);

        // TO: your receiving inbox
        $mail->addAddress('muenriquez@tip.edu.ph', 'Website Admin');

        $mail->isHTML(true);
        $mail->Subject = "New Contact Message from $name";
        $mail->Body    = nl2br("Name: $name\nEmail: $email\n\nMessage:\n$message");

        $mail->send();
        echo "<script>alert('✅ Message sent successfully!'); window.location.href='index.php';</script>";
    } catch (Exception $e) {
        echo "<script>alert('❌ Message could not be sent. Error: {$mail->ErrorInfo}'); window.location.href='index.php';</script>";
    }
}
?>
