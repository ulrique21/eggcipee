<?php
session_start();
require_once 'config.php';
require_once 'otp_functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['temp_user_id']) || !isset($_POST['user_id']) || $_SESSION['temp_user_id'] != $_POST['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$user_id = $_SESSION['temp_user_id'];

// Get user email
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $user = $result->fetch_assoc()) {
    // Generate new OTP
    $otp = generateOTP();
    $otp_expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));
    
    // Update OTP in database
    $update = $conn->prepare("UPDATE users SET otp = ?, otp_expiry = ?, otp_attempts = 0 WHERE id = ?");
    $update->bind_param('ssi', $otp, $otp_expiry, $user_id);
    
    if ($update->execute()) {
        // Send new OTP via email
        if (sendOTP($user['email'], $otp)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send OTP']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update OTP']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'User not found']);
}
?>
