<?php
require_once 'config.php';

// Get the highest rated recipe
$query = "SELECT 
    r.id, 
    r.name, 
    r.image_url, 
    r.description as info,
    r.average_rating,
    r.rating_count
FROM recipes r 
WHERE r.average_rating > 0 
ORDER BY r.average_rating DESC, r.rating_count DESC 
LIMIT 1";

$result = $conn->query($query);

if ($result && $recipe = $result->fetch_assoc()) {
    $rotw = [
        'title' => 'Recipe of the Week',
        'name' => $recipe['name'],
        'image_url' => $recipe['image_url'],
        'info' => $recipe['info'],
        'link' => 'http://' . $_SERVER['HTTP_HOST'] . '/FrontestEnd/recipe-detail.php?id=' . $recipe['id']
    ];
    
    // Save to rotw.json
    file_put_contents(__DIR__ . '/rotw.json', json_encode($rotw, JSON_PRETTY_PRINT));
    echo "Updated Recipe of the Week to: " . $recipe['name'] . " (Rating: " . $recipe['average_rating'] . ")";
} else {
    echo "No recipes with ratings found.";
}
?>
