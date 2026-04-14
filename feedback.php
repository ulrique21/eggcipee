<?php
session_start();
require 'config.php'; // Assuming 'config.php' holds your database connection ($pdo)

$message = ''; // Message to display success or failure
$isLoggedIn = isset($_SESSION['user_id']); 
$username = $_SESSION['username'] ?? 'Guest';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $suggestion_type = 'Dish/Feedback';
    $feedback_text = filter_input(INPUT_POST, 'feedback_text', FILTER_SANITIZE_SPECIAL_CHARS);
    
    if (empty($feedback_text)) {
        $message = '<p class="error">Feedback field cannot be empty.</p>';
    } else {
        $sql = "INSERT INTO user_feedback (username, suggestion_type, feedback_text) VALUES (?, ?, ?)";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bind_param("sss", $username, $suggestion_type, $feedback_text); 
            
            if ($stmt->execute()) {
                $message = '<p class="success">✅ Thank you for your feedback! We will review your suggestion shortly.</p>';
            } else {
                $message = '<p class="error">Database error on execute: ' . $stmt->error . '</p>';
            }
            $stmt->close();
        } catch (Exception $e) {
            $message = '<p class="error">An error occurred while submitting: ' . $e->getMessage() . '</p>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eggcipe | Feedback</title>
    <link rel="stylesheet" href="index.css">
    <style>
        body {
            background-image: url('EggBG.jpg');
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            background-attachment: fixed;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Match navbar styling */
        .main-header {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(90deg, #fff5cc, #fff9e6);
            padding: 18px 60px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            border-bottom: 1px solid #ffe79a;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .logo {
            font-size: 1.9rem;
            font-weight: bold;
            color: #ffb703;
            text-decoration: none;
            letter-spacing: 0.5px;
        }

        .user-section a {
            text-decoration: none;
            color: #3b2f14;
            font-weight: 500;
            border: 1px solid #ffe79a;
            padding: 7px 14px;
            border-radius: 10px;
            transition: all 0.3s ease;
            background: #fffdf7;
            margin-left: 8px;
        }

        .user-section a:hover {
            background: #ffd76a;
            color: #3b2f14;
            border-color: #ffd76a;
        }

        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 50px 20px;
        }

        .form-container {
            max-width: 600px;
            width: 100%;
            background: rgba(255, 255, 255, 0.9);
            padding: 30px 40px;
            border-radius: 16px;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.25);
            text-align: left;
        }

        h2 {
            color: #333;
            font-weight: 600;
            text-align: center;
            margin-bottom: 20px;
        }

        textarea {
            width: 100%;
            min-height: 150px;
            padding: 10px;
            margin-bottom: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        textarea:focus {
            border-color: #ffb703;
            box-shadow: 0 0 5px #ffd76a;
            outline: none;
        }

        .submit-btn {
            background-color: #ffb703;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            width: 100%;
            transition: background 0.3s, transform 0.2s;
        }

        .submit-btn:hover {
            background-color: #f77f00;
            transform: translateY(-2px);
        }

        .error { color: #b71c1c; }
        .success { color: #2e7d32; }

        /* Footer */
        .main-footer {
            text-align: center;
            padding: 25px 0;
            background: linear-gradient(90deg, #fff9e6, #fff5cc);
            border-top: 1px solid #ffe79a;
            box-shadow: 0 -2px 6px rgba(255, 183, 3, 0.1);
            width: 100%;
            margin-top: auto;
        }

        .footer-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .footer-btn {
            text-decoration: none;
            background: #ffb703;
            color: #fff;
            padding: 10px 18px;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .footer-btn:hover {
            background: #f77f00;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<header class="main-header">
    <a href="index.php" class="logo">🥚 Eggcipe</a>
    <div class="user-section">
        <?php if ($isLoggedIn): ?>
            <span>Welcome, <?= htmlspecialchars($username); ?>!</span>
            <a href="account.php">Account Settings</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </div>
</header>

<main class="main-content">
    <div class="form-container">
        <h2>Suggestion & Feedback</h2>
        <?= $message; ?>
        <form method="POST" action="">
            <p>We appreciate your suggestions for new dishes or any other feedback!</p>
            <label for="feedback_text">Your Feedback:</label>
            <textarea id="feedback_text" name="feedback_text" required></textarea>
            <p>Submitted by: <?= htmlspecialchars($username); ?></p>
            <button type="submit" class="submit-btn">Submit Suggestion</button>
        </form>
        <p style="text-align:center; margin-top:15px;">
            <a href="index.php">← Back to Dishes</a>
        </p>
    </div>
</main>

<footer class="main-footer">
    <div class="footer-buttons">
        <a href="feedback.php" class="footer-btn">Suggestions & Feedback</a>
        <a href="report.php" class="footer-btn">Report a Problem</a>
        <a href="contact.php" class="footer-btn">Contact Us</a>
    </div>
</footer>

</body>
</html>
