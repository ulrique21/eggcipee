<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Invalid method']);
  exit;
}

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'Login required']);
  exit;
}

$commentId = intval($_POST['comment_id'] ?? 0);
if ($commentId <= 0) {
  echo json_encode(['success' => false, 'message' => 'Invalid comment']);
  exit;
}

// Ensure likes table exists (if comments.php not hit yet)
$conn->query("CREATE TABLE IF NOT EXISTS comment_likes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  comment_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_like (comment_id, user_id),
  INDEX(comment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$userId = intval($_SESSION['user_id']);

// Insert like; if duplicate, ignore
$stmt = $conn->prepare("INSERT INTO comment_likes (comment_id, user_id) VALUES (?, ?)");
$stmt->bind_param('ii', $commentId, $userId);
if ($stmt->execute()) {
  // increment likes counter
  $conn->query("UPDATE comments SET likes = likes + 1 WHERE id = " . $commentId);
  // fetch new count
  $res = $conn->query("SELECT likes FROM comments WHERE id = " . $commentId);
  $likes = ($row = $res->fetch_assoc()) ? intval($row['likes']) : null;
  echo json_encode(['success' => true, 'likes' => $likes]);
} else {
  // Likely duplicate
  echo json_encode(['success' => false, 'message' => 'Already liked']);
}
$stmt->close();
?>



