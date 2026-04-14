<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo 'Access denied';
    exit;
}

$message = '';
$rotwPath = __DIR__ . DIRECTORY_SEPARATOR . 'rotw.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? 'Recipe of the Week');
    $recipeId = isset($_POST['recipe_id']) ? (int)$_POST['recipe_id'] : 0;
    $name = trim($_POST['name'] ?? '');
    $imageUrl = trim($_POST['image_url'] ?? '');
    $info = trim($_POST['info'] ?? '');
    $link = trim($_POST['link'] ?? '');

    // If a recipe is selected, try to load its data
    if ($recipeId > 0) {
        $stmt = $conn->prepare('SELECT id, name, image_url, info FROM recipes WHERE id = ?');
        $stmt->bind_param('i', $recipeId);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $name = $name ?: $row['name'];
                $imageUrl = $imageUrl ?: $row['image_url'];
                $info = $info ?: $row['info'];
                $link = $link ?: ('recipe-detail.php?id=' . $row['id']);
            }
        }
        $stmt->close();
    }

    $payload = [
        'title' => $title ?: 'Recipe of the Week',
        'name' => $name,
        'image_url' => $imageUrl,
        'info' => $info,
        'link' => $link
    ];

    file_put_contents($rotwPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    $message = 'Saved successfully.';
}

// Load current values
$current = [
    'title' => 'Recipe of the Week',
    'name' => '',
    'image_url' => '',
    'info' => '',
    'link' => ''
];
if (file_exists($rotwPath)) {
    $data = json_decode(file_get_contents($rotwPath), true);
    if (is_array($data)) {
        $current = array_merge($current, $data);
    }
}

// Fetch recipes for dropdown
$recipes = [];
$recRes = $conn->query('SELECT id, name FROM recipes ORDER BY id DESC LIMIT 100');
if ($recRes) {
    while ($r = $recRes->fetch_assoc()) { $recipes[] = $r; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Recipe of the Week</title>
    <link rel="stylesheet" href="index.css">
    <style>
        .admin-container { max-width: 760px; margin: 40px auto; background: #fffefb; border: 1px solid #ffe79a; border-radius: 12px; padding: 20px; }
        .admin-container h2 { color: #ffb703; margin-bottom: 14px; }
        .form-row { display: flex; gap: 12px; margin-bottom: 12px; }
        .form-row label { width: 160px; padding-top: 8px; color: #3b2f14; font-weight: 600; }
        .form-row input[type="text"], .form-row textarea, .form-row select { flex: 1; padding: 10px; border: 1px solid #f1d9a9; border-radius: 8px; }
        textarea { min-height: 100px; resize: vertical; }
        .actions { margin-top: 16px; }
        .success { color: #0a8f3a; margin-bottom: 10px; }
    </style>
    </head>
<body>
    <div class="admin-container">
        <h2>Recipe of the Week</h2>
        <?php if ($message): ?><div class="success"><?= htmlspecialchars($message); ?></div><?php endif; ?>
        <form method="post">
            <div class="form-row">
                <label for="recipe_id">Pick existing recipe</label>
                <select id="recipe_id" name="recipe_id">
                    <option value="">-- Optional --</option>
                    <?php foreach ($recipes as $r): ?>
                        <option value="<?= $r['id']; ?>"><?= htmlspecialchars($r['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($current['title']); ?>">
            </div>
            <div class="form-row">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($current['name']); ?>">
            </div>
            <div class="form-row">
                <label for="image_url">Image URL</label>
                <input type="text" id="image_url" name="image_url" value="<?= htmlspecialchars($current['image_url']); ?>" placeholder="uploads/yourimage.jpg">
            </div>
            <div class="form-row">
                <label for="info">Info</label>
                <textarea id="info" name="info"><?= htmlspecialchars($current['info']); ?></textarea>
            </div>
            <div class="form-row">
                <label for="link">Link</label>
                <input type="text" id="link" name="link" value="<?= htmlspecialchars($current['link']); ?>" placeholder="recipe-detail.php?id=123">
            </div>
            <div class="actions">
                <button type="submit" class="view-btn">Save</button>
                <a href="index.php" class="account-btn" style="margin-left:10px;">Back</a>
            </div>
        </form>
    </div>
</body>
</html>



