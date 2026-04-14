<?php
require_once 'config.php';
require_once 'db.php';
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    
    if (empty($email)) {
        $message = "Email address is required!";
        $messageType = "error";
    } else {
        try {
            // Check if email exists
            $sql = "SELECT id, username FROM users WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Generate reset token
                $reset_token = bin2hex(random_bytes(32));
                $reset_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Update user with reset token
                $updateSql = "UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->execute([$reset_token, $reset_expires, $email]);
                
                // Send reset email using PHPMailer
                $mail = new PHPMailer(true);
                
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = SMTP_HOST;
                    $mail->SMTPAuth = true;
                    $mail->Username = SMTP_USERNAME;
                    $mail->Password = SMTP_PASSWORD;
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = SMTP_PORT;

                    // Recipients
                    $mail->setFrom(FROM_EMAIL, FROM_NAME);
                    $mail->addAddress($email, $user['username']);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Password Reset - Recipe App';
                    $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset-password.php?token=" . $reset_token;
                    
                    $mail->Body = "
                        <h2>Password Reset Request</h2>
                        <p>Hi {$user['username']},</p>
                        <p>We received a request to reset your password. Click the link below to reset your password:</p>
                        <p><a href='$reset_link' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Reset Password</a></p>
                        <p>Or copy and paste this link: $reset_link</p>
                        <p><strong>This link will expire in 1 hour.</strong></p>
                        <p>If you didn't request this reset, please ignore this email.</p>
                        <p>Best regards,<br>Recipe App Team</p>
                    ";

                    $mail->send();
                    $message = "Password reset link sent to your email address!";
                    $messageType = "success";
                    
                } catch (Exception $e) {
                    $message = "Failed to send reset email. Error: " . $mail->ErrorInfo;
                    $messageType = "error";
                }
            } else {
                $message = "Email address not found in our system.";
                $messageType = "error";
            }
        } catch (PDOException $e) {
            $message = "Password reset failed: " . $e->getMessage();
            $messageType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Recipe App</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-brand">Recipe App</a>
            <div class="nav-menu">
                <a href="index.php">Home</a>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="form-container">
            <h2>Forgot Password</h2>
            <p>Enter your email address and we'll send you a link to reset your password.</p>
            
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="" class="forgot-form">
                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <button type="submit" class="btn btn-primary">Send Reset Link</button>
            </form>
            
            <div class="form-footer">
                <p>Remember your password? <a href="login.php">Login here</a></p>
                <p><a href="index.php">← Back to Home</a></p>
            </div>
        </div>
    </div>
</body>
</html>
