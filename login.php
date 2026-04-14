<?php
session_start();
require_once 'config.php';
require_once 'otp_helpers.php';

// Handle login form submission
$message = '';
$show_otp_form = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify OTP submission
    if (isset($_POST['verify_otp'])) {
        $otp = trim($_POST['otp'] ?? '');
        if (!isset($_SESSION['pending_user']) || !isset($_SESSION['otp_code']) || !isset($_SESSION['otp_expires'])) {
            $message = 'Session expired. Please log in again.';
        } else if (time() > $_SESSION['otp_expires']) {
            $message = 'OTP expired. Please resend and try again.';
            $show_otp_form = true;
        } else if ($otp !== $_SESSION['otp_code']) {
            $message = 'Invalid OTP. Please try again.';
            $show_otp_form = true;
        } else {
            // OTP success → complete login
            $u = $_SESSION['pending_user'];
            $_SESSION['user_id'] = $u['id'];
            $_SESSION['username'] = $u['username'];
            $_SESSION['role'] = $u['role'];

            // If user opted to trust this device, set a 3-day cookie
            if (!empty($_POST['trust_device'])) {
                $token = hash('sha256', $u['id'] . '|' . ($u['password_hash'] ?? '') . '|' . ($_SERVER['HTTP_USER_AGENT'] ?? '')); 
                $cookieName = 'trusted_login_' . $u['id'];
                setcookie($cookieName, $token, [
                    'expires' => time() + 3 * 24 * 60 * 60,
                    'path' => '/',
                    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            }
            unset($_SESSION['pending_user'], $_SESSION['otp_code'], $_SESSION['otp_expires']);
            header('Location: index.php');
            exit;
        }
    }
    // Resend OTP
    else if (isset($_POST['resend_otp'])) {
        if (!isset($_SESSION['pending_user'])) {
            $message = 'Session expired. Please log in again.';
        } else {
            $otp = generateOTP();
            $_SESSION['otp_code'] = $otp;
            $_SESSION['otp_expires'] = time() + 5 * 60;
            $err = null;
            if (sendOTP($_SESSION['pending_user']['email'], $otp, $err)) {
                $message = 'A new OTP has been sent to your email.';
            } else {
                $message = 'Failed to send OTP email. ' . ($err ? ('Details: ' . htmlspecialchars($err)) : 'Please try again later.');
            }
            $show_otp_form = true;
        }
    }
    // Username/password login → start OTP flow
    else {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $stmt = $conn->prepare("SELECT id, username, password, role, email FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                // Check if this device is trusted for this user
                $cookieName = 'trusted_login_' . (int)$row['id'];
                if (!empty($_COOKIE[$cookieName])) {
                    $expected = hash('sha256', $row['id'] . '|' . $row['password'] . '|' . ($_SERVER['HTTP_USER_AGENT'] ?? ''));
                    if (hash_equals($expected, $_COOKIE[$cookieName])) {
                        // Bypass OTP and log in directly
                        $_SESSION['user_id'] = (int)$row['id'];
                        $_SESSION['username'] = $row['username'];
                        $_SESSION['role'] = $row['role'];
                        header('Location: index.php');
                        exit;
                    }
                }
                // Prepare OTP
                $otp = generateOTP();
                $_SESSION['pending_user'] = [
                    'id' => (int)$row['id'],
                    'username' => $row['username'],
                    'role' => $row['role'],
                    'email' => $row['email'],
                    'password_hash' => $row['password']
                ];
                $_SESSION['otp_code'] = $otp;
                $_SESSION['otp_expires'] = time() + 5 * 60; // 5 minutes
                $err = null;
                if (sendOTP($row['email'], $otp, $err)) {
                    $message = 'We sent a 6-digit OTP to your email.';
                } else {
                    $message = 'Failed to send OTP email. ' . ($err ? ('Details: ' . htmlspecialchars($err)) : 'Please try again later.');
                }
                $show_otp_form = true;
            } else {
                $message = "❌ Incorrect password.";
            }
        } else {
            $message = "❌ User not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Eggcipe | <?php echo $show_otp_form ? 'Verify OTP' : 'Login'; ?></title>
    <link rel="stylesheet" href="loginn.css?v=2">
    <style>
        .otp-container {
            display: <?php echo $show_otp_form ? 'block' : 'none'; ?>;
            max-width: 400px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .otp-input {
            letter-spacing: 1rem;
            font-size: 2rem;
            text-align: center;
            width: 100%;
            padding: 0.5rem;
            margin: 1rem 0;
            border: 2px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<!-- 🥚 Navbar -->
<header class="main-header">
    <a href="index.php" class="logo">🥚 Eggcipe</a>
    <div class="nav-links">
        <a href="index.php" class="btn">Home</a>
        <a href="register.php" class="btn">Register</a>
    </div>
</header>

<!-- OTP Verification Form -->
<div class="otp-container">
    <h2>Verify Your Identity</h2>
    <p>We've sent a 6-digit OTP to your registered email. Please enter it below.</p>
    <?php if (!empty($message)): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>
    <form method="POST" action="login.php">
        <input type="text" name="otp" class="otp-input" placeholder="000000" maxlength="6" pattern="\d{6}" required>
        <div style="display:flex; align-items:center; gap:8px; justify-content:flex-start; margin: 8px 0 10px 0;">
            <label style="display:flex; align-items:center; gap:8px; font-size: 0.95rem; color:#3b2f14;">
                <input type="checkbox" name="trust_device" value="1"> Trust this device for 3 days
            </label>
        </div>
        <button type="submit" name="verify_otp" class="btn">Verify OTP</button>
    </form>
    <form method="POST" action="login.php" style="margin-top:8px;">
        <button type="submit" name="resend_otp" class="btn">Resend OTP</button>
    </form>
</div>

<!-- 🍳 Login Section -->
<main class="login-main" style="<?php echo $show_otp_form ? 'display:none;' : ''; ?>">
    <div class="login-container">
        <h2>Login</h2>
        <?php if ($message): ?>
            <div class="error-box"><?= htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>

            <button type="submit" class="btn-login">Login</button>

            <div class="register-link">
                Don't have an account? <a href="register.php">Register here.</a>
            </div>
        </form>
    </div>
</main>

<!-- Footer -->
<footer class="main-footer">
    <div class="footer-buttons">
        <a href="feedback.php" class="footer-btn">Suggestions & Feedback</a>
        <a href="report.php" class="footer-btn">Report a Problem</a>
        <a href="contact.php" class="footer-btn">Contact Info</a>
    </div>
</footer>

</body>
</html>
