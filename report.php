<?php
session_start();
require 'config.php';

$message = ''; 
$isLoggedIn = isset($_SESSION['user_id']); 
$username = $_SESSION['username'] ?? 'Guest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $report_type = 'Problem/Misinformation';
    $report_details = filter_input(INPUT_POST, 'report_details', FILTER_SANITIZE_SPECIAL_CHARS);
    $page_url = filter_input(INPUT_POST, 'page_url', FILTER_SANITIZE_URL);
    
    if (empty($report_details)) {
        $message = '<p class="error">Report details field cannot be empty.</p>';
    } else {
        $sql = "INSERT INTO misinformation_reports (username, report_type, report_details, page_url) VALUES (?, ?, ?, ?)";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bind_param("ssss", $username, $report_type, $report_details, $page_url);
            
            if ($stmt->execute()) {
                $message = '<p class="success">✅ Thank you for reporting! We’ll look into this issue.</p>';
            } else {
                $message = '<p class="error">Database error: ' . $stmt->error . '</p>';
            }
            $stmt->close();
        } catch (Exception $e) {
            $message = '<p class="error">Error: ' . $e->getMessage() . '</p>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eggcipe | Report a Problem</title>
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

        /* NAVBAR (same as feedback.php) */
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

        /* FORM CONTAINER */
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

        label {
            font-weight: 500;
            color: #333;
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus, textarea:focus {
            border-color: #f77f00;
            box-shadow: 0 0 5px #ffd76a;
            outline: none;
        }

        textarea {
            min-height: 150px;
            resize: vertical;
        }

        .submit-btn {
            background-color: #e63946;
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
            background-color: #b71c1c;
            transform: translateY(-2px);
        }

        .error { color: #b71c1c; text-align: center; }
        .success { color: #2e7d32; text-align: center; }

        /* FOOTER (same as feedback.php) */
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
        <h2>Report a Problem or Misinformation</h2>
        <?= $message; ?>
        <form method="POST" action="">
            <p>Please describe the issue you found on our website:</p>

            <label for="page_url">Page URL:</label>
            <input type="text" id="page_url" name="page_url" placeholder="e.g., recipe-detail.php?dish_id=3" required>

            <label for="report_details">Details:</label>
            <textarea id="report_details" name="report_details" placeholder="Describe the problem or misinformation..." required></textarea>

            <p>Submitted by: <?= htmlspecialchars($username); ?></p>

            <button type="submit" class="submit-btn">Submit Report</button>
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
