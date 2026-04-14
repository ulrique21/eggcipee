<?php
require_once 'config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to rate recipes']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$recipe_id = intval($data['recipe_id'] ?? 0);
$rating = intval($data['rating'] ?? 0);

if ($recipe_id <= 0 || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid rating data']);
    exit;
}

$user_id = $_SESSION['user_id'];
$conn->begin_transaction();

try {
    // Check if user already rated this recipe
    $stmt = $conn->prepare("SELECT id FROM ratings WHERE recipe_id = ? AND user_id = ?");
    $stmt->bind_param('ii', $recipe_id, $user_id);
    $stmt->execute();
    $existing_rating = $stmt->get_result()->fetch_assoc();

    if ($existing_rating) {
        // Update existing rating
        $stmt = $conn->prepare("UPDATE ratings SET rating = ? WHERE id = ?");
        $stmt->bind_param('ii', $rating, $existing_rating['id']);
        $is_new = false;
    } else {
        // Insert new rating
        $stmt = $conn->prepare("INSERT INTO ratings (recipe_id, user_id, rating) VALUES (?, ?, ?)");
        $stmt->bind_param('iii', $recipe_id, $user_id, $rating);
        $is_new = true;
    }
    $stmt->execute();

    // Update recipe's average rating and count
    $update = $conn->prepare("
        UPDATE recipes r
        SET 
            average_rating = (
                SELECT ROUND(AVG(rating), 1)
                FROM ratings 
                WHERE recipe_id = ?
            ),
            rating_count = (
                SELECT COUNT(*) 
                FROM ratings 
                WHERE recipe_id = ?
            )
        WHERE r.id = ?
    ");
    $update->bind_param('iii', $recipe_id, $recipe_id, $recipe_id);
    $update->execute();

    // Get updated rating info
    $result = $conn->query("SELECT average_rating, rating_count FROM recipes WHERE id = $recipe_id")->fetch_assoc();
    
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'average_rating' => number_format($result['average_rating'] ?? 0, 1),
        'rating_count' => $result['rating_count'] ?? 0,
        'is_new' => $is_new
    ]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while saving your rating']);
}
?>
