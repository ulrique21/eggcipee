<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

function generateOTP(int $length = 6): string {
    $length = max(4, min(8, $length));
    $min = (int) pow(10, $length - 1);
    $max = (int) pow(10, $length) - 1;
    return (string) random_int($min, $max);
}

function sendOTP(string $toEmail, string $otp, ?string &$error = null): bool {
    $config = require __DIR__ . '/email-config.php';
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = (int) $config['port'];

        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = 'Your Eggcipe Login OTP';
        $mail->Body = '<p>Your one-time password is:</p>' .
                      '<p style="font-size:22px;font-weight:bold;letter-spacing:3px;">' . htmlspecialchars($otp) . '</p>' .
                      '<p>This code will expire in 5 minutes.</p>';
        $mail->AltBody = "Your OTP is: $otp (expires in 5 minutes)";
        $mail->send();
        return true;
    } catch (Exception $e) {
        $error = $mail->ErrorInfo ?: $e->getMessage();
        return false;
    }
}


