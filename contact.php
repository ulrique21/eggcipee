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
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $contact_message = $_POST['message'] ?? '';
    
    if (empty($name) || empty($email) || empty($subject) || empty($contact_message)) {
        $message = "All fields are required!";
        $messageType = "error";
    } else {
        try {
            // Save to database
            $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$name, $email, $subject, $contact_message]);
            
            // Send email using PHPMailer
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = SMTP_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = SMTP_USERNAME;
                $mail->Password = SMTP_PASSWORD;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = SMTP_PORT;

                $mail->setFrom(FROM_EMAIL, FROM_NAME);
                $mail->addAddress(FROM_EMAIL);
                $mail->addReplyTo($email, $name);

                $mail->isHTML(true);
                $mail->Subject = 'Contact Form: ' . $subject;
                $mail->Body = "
                    <h2>New Contact Form Submission</h2>
                    <p><strong>Name:</strong> $name</p>
                    <p><strong>Email:</strong> $email</p>
                    <p><strong>Subject:</strong> $subject</p>
                    <p><strong>Message:</strong></p>
                    <p>" . nl2br(htmlspecialchars($contact_message)) . "</p>
                    <hr>
                    <p><small>Sent from Eggcipe contact form</small></p>
                ";
                $mail->send();

                // Confirmation to user
                $confirmationMail = new PHPMailer(true);
                $confirmationMail->isSMTP();
                $confirmationMail->Host = SMTP_HOST;
                $confirmationMail->SMTPAuth = true;
                $confirmationMail->Username = SMTP_USERNAME;
                $confirmationMail->Password = SMTP_PASSWORD;
                $confirmationMail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $confirmationMail->Port = SMTP_PORT;

                $confirmationMail->setFrom(FROM_EMAIL, FROM_NAME);
                $confirmationMail->addAddress($email, $name);

                $confirmationMail->isHTML(true);
                $confirmationMail->Subject = 'Thank you for contacting Eggcipe';
                $confirmationMail->Body = "
                    <h2>Thank you for your message!</h2>
                    <p>Hi $name,</p>
                    <p>We have received your message regarding: <strong>$subject</strong></p>
                    <p>We will get back to you as soon as possible.</p>
                    <p>Best regards,<br>Eggcipe Team</p>
                ";

                $confirmationMail->send();

                $message = "Thank you for your message! We'll get back to you soon.";
                $messageType = "success";
                
            } catch (Exception $e) {
                $message = "Message saved successfully, but email could not be sent.";
                $messageType = "warning";
            }
            
        } catch (PDOException $e) {
            $message = "Failed to send message: " . $e->getMessage();
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
    <title>Contact Us - Eggcipe</title>
    <link rel="stylesheet" href="contact.css?v=<?php echo time(); ?>">
</head>
<body>

    <!-- HEADER -->
    <header class="header">
        <a href="index.php" class="logo">🥚 Eggcipe</a>
        <nav class="nav">
            <a href="index.php">Home</a>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        </nav>
    </header>

    <!-- MAIN CONTENT -->
    <main class="contact-main">
        <h1>Contact Us</h1>
        <p>Have questions, feedback, or just want to say hi? We'd love to hear from you!</p>

        <?php if ($message): ?>
            <div class="alert <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="post" class="contact-form">
            <label for="name">Your Name:</label>
            <input type="text" id="name" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">

            <label for="email">Your Email:</label>
            <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">

            <label for="subject">Subject:</label>
            <input type="text" id="subject" name="subject" required value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>">

            <label for="message">Message:</label>
            <textarea id="message" name="message" rows="6" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>

            <button type="submit" class="btn-submit">Send Message</button>
        </form>
    </main>

    <!-- FOOTER -->
    <footer class="footer">
        <p>&copy; <?= date('Y'); ?> Eggcipe. All rights reserved.</p>
    </footer>

</body>
</html>
