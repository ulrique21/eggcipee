<?php
require_once 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Test email configuration
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'krenzerteodoro15@gmail.com';
    $mail->Password   = 'fdgx wyjb gsug ikqu';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Recipients
    $mail->setFrom('krenzerteodoro15@gmail.com', 'Recipe App Test');
    $mail->addAddress('krenzerteodoro15@gmail.com'); // Send to yourself

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email from Recipe App';
    $mail->Body    = 'This is a test email to verify SMTP configuration is working.';

    $mail->send();
    echo 'SUCCESS: Test email sent successfully!';
} catch (Exception $e) {
    echo 'ERROR: ' . $mail->ErrorInfo;
}
?>
