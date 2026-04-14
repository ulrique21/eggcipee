<?php
include 'config.php';
session_start();

// ✅ Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ✅ Get recipe ID
$id = $_GET['id'] ?? null;
if (!$id) {
    die("Recipe not found.");
}

// ✅ Fetch recipe data
$query = "SELECT * FROM recipes WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$recipe = $result->fetch_assoc();

if (!$recipe) {
    die("Recipe not found in database.");
}

// ✅ Function to normalize YouTube URL
function normalizeYouTubeURL($url) {
    if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([\w-]{11})/', $url, $matches)) {
        return "https://www.youtube.com/embed/" . $matches[1];
    }
    return ''; // not a valid YouTube URL
}

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $ingredients = $_POST['ingredients'] ?? '';
    $steps = $_POST['steps'] ?? '';
    $video_url = $_POST['video_url'] ?? '';
    $calories = $_POST['calories'] ?? '';
    $protein = $_POST['protein'] ?? '';
    $fat = $_POST['fat'] ?? '';
    $carbs = $_POST['carbs'] ?? '';
    $sodium = $_POST['sodium'] ?? '';
    $cholesterol = $_POST['cholesterol'] ?? '';
    $dietary_properties = $_POST['dietary_properties'] ?? '';

    // ✅ Normalize YouTube URL
    $video_url = normalizeYouTubeURL($video_url);

    // ✅ Handle image upload
    $image_url = $recipe['image_url'];
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $targetFile = $targetDir . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $image_url = $targetFile;
        }
    }

    // ✅ Update database
    $update = "UPDATE recipes 
               SET name=?, ingredients=?, steps=?, image_url=?, video_url=?, 
                   calories=?, protein=?, fat=?, carbs=?, sodium=?, cholesterol=?, dietary_properties=? 
               WHERE id=?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param(
        "ssssssssssssi",
        $name, $ingredients, $steps, $image_url, $video_url,
        $calories, $protein, $fat, $carbs, $sodium, $cholesterol, $dietary_properties, $id
    );
    $stmt->execute();

    header("Location: edit_recipe.php?id=$id&msg=success");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Recipe | Eggcipe</title>
    <link rel="stylesheet" href="edit_recipe.css">
</head>
<body>

<header class="header">
    <div class="logo">🥚 Eggcipe Admin</div>
    <nav class="nav-right">
        <a href="dashboard.php" class="nav-btn">🏠 Dashboard</a>
        <a href="logout.php" class="nav-btn logout">🚪 Logout</a>
    </nav>
</header>

<main>
    <h2>Edit Recipe</h2>

    <form method="POST" enctype="multipart/form-data" class="edit-form">

        <label for="name">Recipe Name</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($recipe['name']) ?>" required>

        <label for="ingredients">Ingredients</label>
        <textarea id="ingredients" name="ingredients" required><?= htmlspecialchars($recipe['ingredients']) ?></textarea>

        <label for="steps">Steps / Instructions</label>
        <textarea id="steps" name="steps" required><?= htmlspecialchars($recipe['steps']) ?></textarea>

        <label for="video_url">YouTube Video Link (optional)</label>
        <input type="url" id="video_url" name="video_url"
               placeholder="https://www.youtube.com/watch?v=..."
               value="<?= htmlspecialchars($recipe['video_url'] ?? '') ?>"
               oninput="updateVideoPreview(this.value)">

        <div class="video-preview" id="videoPreview">
            <?php
            if (!empty($recipe['video_url'])) {
                if (preg_match('/(?:youtube\.com\/(?:embed\/|watch\?v=)|youtu\.be\/)([\w-]{11})/', $recipe['video_url'], $matches)) {
                    $videoId = $matches[1];
                    echo "<iframe width='100%' height='315' 
                                src='https://www.youtube.com/embed/$videoId' 
                                allowfullscreen></iframe>";
                }
            }
            ?>
        </div>

        <label for="image">Recipe Image</label>
        <input type="file" id="image" name="image">
        <?php if (!empty($recipe['image_url'])): ?>
            <div class="preview">
                <img src="<?= htmlspecialchars($recipe['image_url']) ?>" alt="Recipe Image">
            </div>
        <?php endif; ?>

        <hr>

        <h3>Complete Nutrition (Per Serving)</h3>
        <div class="nutrition-grid">
            <div><label>Calories</label><input type="text" name="calories" value="<?= htmlspecialchars($recipe['calories']) ?>"></div>
            <div><label>Protein</label><input type="text" name="protein" value="<?= htmlspecialchars($recipe['protein']) ?>"></div>
            <div><label>Fat</label><input type="text" name="fat" value="<?= htmlspecialchars($recipe['fat']) ?>"></div>
            <div><label>Carbs</label><input type="text" name="carbs" value="<?= htmlspecialchars($recipe['carbs']) ?>"></div>
            <div><label>Sodium</label><input type="text" name="sodium" value="<?= htmlspecialchars($recipe['sodium']) ?>"></div>
            <div><label>Cholesterol</label><input type="text" name="cholesterol" value="<?= htmlspecialchars($recipe['cholesterol']) ?>"></div>
        </div>

        <label for="dietary_properties">Dietary Properties</label>
        <textarea id="dietary_properties" name="dietary_properties" placeholder="e.g., Gluten-Free, High Protein, Low Sugar"><?= htmlspecialchars($recipe['dietary_properties']) ?></textarea>

        <button type="submit" class="submit-btn">💾 Save Changes</button>
    </form>
</main>

<div id="toast" class="toast">✅ Recipe updated successfully!</div>

<script>
function updateVideoPreview(url) {
    const preview = document.getElementById('videoPreview');
    preview.innerHTML = '';

    const match = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([\w-]{11})/);
    if (match && match[1]) {
        const videoId = match[1];
        preview.innerHTML = `
            <iframe width="100%" height="315"
                    src="https://www.youtube.com/embed/${videoId}"
                    allowfullscreen></iframe>`;
    } else if (url.trim() !== '') {
        preview.innerHTML = `<p style="color:red;">⚠️ Invalid YouTube URL</p>`;
    }
}

// ✅ Success Toast
const params = new URLSearchParams(window.location.search);
if (params.get('msg') === 'success') {
    const toast = document.getElementById('toast');
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}
</script>

</body>
</html>
