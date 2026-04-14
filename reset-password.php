<?php
require_once 'config.php';
require_once 'db.php';

$message = '';
$messageType = '';
$valid_token = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    try {
        // Check if token is valid and not expired
        $sql = "SELECT id, username FROM users WHERE reset_token = ? AND reset_expires > NOW()";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$token]);
        
        if ($stmt->rowCount() > 0) {
            $valid_token = true;
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Handle password reset
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $password = $_POST['password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                
                if (empty($password) || empty($confirm_password)) {
                    $message = "All fields are required!";
                    $messageType = "error";
                } elseif (strlen($password) < 6) {
                    $message = "Password must be at least 6 characters long!";
                    $messageType = "error";
                } elseif ($password !== $confirm_password) {
                    $message = "Passwords do not match!";
                    $messageType = "error";
                } else {
                    // Update password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $updateSql = "UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?";
                    $updateStmt = $conn->prepare($updateSql);
                    $updateStmt->execute([$hashed_password, $token]);
                    
                    $message = "Password reset successfully! You can now login with your new password.";
                    $messageType = "success";
                    $valid_token = false; // Hide form after successful reset
                }
            }
        } else {
            $message = "Invalid or expired reset token.";
            $messageType = "error";
        }
    } catch (PDOException $e) {
        $message = "Password reset failed: " . $e->getMessage();
        $messageType = "error";
    }
} else {
    $message = "No reset token provided.";
    $messageType = "error";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Recipe App</title>
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
            <h2>Reset Password</h2>
            
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($valid_token): ?>
                <form method="post" action="" class="reset-form">
                    <div class="form-group">
                        <label for="password">New Password:</label>
                        <input type="password" id="password" name="password" required minlength="6">
                        <small>Password must be at least 6 characters long</small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                    </div>

                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </form>
            <?php endif; ?>
            
            <div class="form-footer">
                <p><a href="login.php">Back to Login</a></p>
                <p><a href="index.php">← Back to Home</a></p>
            </div>
        </div>
    </div>
</body>
</html>
