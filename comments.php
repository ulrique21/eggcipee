<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Ensure tables exist
$conn->query("CREATE TABLE IF NOT EXISTS comments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  recipe_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  username VARCHAR(120) NOT NULL,
  content TEXT NOT NULL,
  likes INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX(recipe_id), INDEX(likes), INDEX(created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->query("CREATE TABLE IF NOT EXISTS comment_likes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  comment_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_like (comment_id, user_id),
  INDEX(comment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Invalid method']);
  exit;
}

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'Login required']);
  exit;
}

$recipeId = intval($_POST['recipe_id'] ?? 0);
$content = trim($_POST['content'] ?? '');
if ($recipeId <= 0 || $content === '') {
  echo json_encode(['success' => false, 'message' => 'Missing fields']);
  exit;
}

$userId = intval($_SESSION['user_id']);
$username = $_SESSION['username'] ?? 'User';

$stmt = $conn->prepare("INSERT INTO comments (recipe_id, user_id, username, content) VALUES (?, ?, ?, ?)");
$stmt->bind_param('iiss', $recipeId, $userId, $username, $content);
if ($stmt->execute()) {
  echo json_encode([
    'success' => true,
    'comment' => [
      'id' => $stmt->insert_id,
      'recipe_id' => $recipeId,
      'user_id' => $userId,
      'username' => $username,
      'content' => htmlspecialchars($content, ENT_QUOTES, 'UTF-8'),
      'likes' => 0,
      'created_at' => date('Y-m-d H:i:s')
    ]
  ]);
} else {
  echo json_encode(['success' => false, 'message' => 'Failed to add comment']);
}
$stmt->close();
?>



