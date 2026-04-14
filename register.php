<?php
session_start();
require 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

// Redirect if already logged in
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: index.php");
    exit;
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $email    = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("❌ SQL prepare failed: " . $conn->error);
    }

    $stmt->bind_param("sss", $username, $email, $hashed_password);

    if ($stmt->execute()) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'muenriquez@tip.edu.ph';
            $mail->Password   = 'tsac mfxz hmzv nhxv'; // App password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('ulrique12@gmail.com', 'Eggcipe');
            $mail->addAddress($email, $username);

            $mail->isHTML(true);
            $mail->Subject = 'Welcome to Eggcipe!';
            $mail->Body    = "
                <h2>Hello, $username!</h2>
                <p>Thank you for registering at <strong>Eggcipe</strong> 🍳</p>
                <p>You can now <a href='http://yourwebsite.com/login.php'>log in here</a>.</p>
            ";

            $mail->send();
            $message = "<div class='success-box'>✅ Registration successful! Check your email for confirmation. <a href='login.php'>Login here</a></div>";
        } catch (Exception $e) {
            $message = "<div class='error-box'>✅ Registered but failed to send email. Error: {$mail->ErrorInfo}</div>";
        }
    } else {
        $message = "<div class='error-box'>❌ Registration failed: " . $stmt->error . "</div>";
    }

    $stmt->close();
    $conn->close();
}

$isLoggedIn = isset($_SESSION['user_id']);
$username = $_SESSION['username'] ?? 'Guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | Eggcipe</title>
    <link rel="stylesheet" href="registerr.css">
</head>
<body>

<!-- ✅ HEADER (same as index.php) -->
<header class="main-header">
    <a href="index.php" class="logo">🥚 Eggcipe</a>
    <div class="nav-links">
        <?php if ($isLoggedIn): ?>
            <span>Welcome, <?= htmlspecialchars($username); ?>!</span>
            <a href="logout.php" class="btn">Logout</a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="dashboard.php" class="btn">Admin Dashboard</a>
            <?php endif; ?>
        <?php else: ?>
            <a href="login.php" class="btn">Login</a>
            <a href="register.php" class="btn">Register</a>
        <?php endif; ?>
    </div>
</header>

<!-- ✅ REGISTER FORM -->
<main class="register-main">
    <div class="register-container">
        <h2>Create an Account</h2>
        <?php if (!empty($message)) echo $message; ?>
        <form method="post" action="">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" class="btn-register">Register</button>
            <p class="login-link">Already have an account? <a href="login.php">Login</a></p>
        </form>
    </div>
</main>

<!-- ✅ FOOTER (same as index.php) -->
<footer class="main-footer">
    <div class="footer-buttons">
        <a href="feedback.php" class="footer-btn">Suggestions & Feedback</a>
        <a href="report.php" class="footer-btn">Report a Problem</a>
        <a href="contact.php" class="footer-btn">Contact Info</a>
    </div>
</footer>

</body>
</html>
