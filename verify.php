<?php
require_once 'config.php';
require_once 'db.php';

$message = '';
$messageType = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    try {
        // Find user with this verification token
        $sql = "SELECT id, username FROM users WHERE verification_token = ? AND email_verified = 0";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$token]);
        
        if ($stmt->rowCount() > 0) {
            // Update user as verified
            $updateSql = "UPDATE users SET email_verified = 1, verification_token = NULL WHERE verification_token = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->execute([$token]);
            
            $message = "Email verified successfully! You can now login.";
            $messageType = "success";
        } else {
            $message = "Invalid or expired verification token.";
            $messageType = "error";
        }
    } catch (PDOException $e) {
        $message = "Verification failed: " . $e->getMessage();
        $messageType = "error";
    }
} else {
    $message = "No verification token provided.";
    $messageType = "error";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Recipe App</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>Email Verification</h2>
            
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            
            <div class="form-footer">
                <?php if ($messageType === 'success'): ?>
                    <p><a href="login.php" class="btn btn-primary">Login Now</a></p>
                <?php endif; ?>
                <p><a href="index.php">← Back to Home</a></p>
            </div>
        </div>
    </div>
</body>
</html>
