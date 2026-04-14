<?php
session_start();
require_once 'config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ Only admins can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ✅ Handle adding a recipe
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_recipe'])) {
    $name = trim($_POST['name']);
    $info = trim($_POST['info']);
    $video_url = trim($_POST['video_url']);
    $origin = trim($_POST['origin']);
    $inventor = trim($_POST['inventor']);
    $description = trim($_POST['description']);
    $ingredients = trim($_POST['ingredients']);
    $steps = trim($_POST['steps']);
    $calories = trim($_POST['calories']);
    $protein = trim($_POST['protein']);
    $fat = trim($_POST['fat']);
    $carbs = trim($_POST['carbs']);
    $sodium = trim($_POST['sodium']);
    $cholesterol = trim($_POST['cholesterol']);
    $dietary_properties = trim($_POST['dietary_properties']);

    // ✅ Handle image upload
    $image_url = "";
    if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image_url']['tmp_name'];
        $fileName = $_FILES['image_url']['name'];
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $newFileName = uniqid('img_', true) . '.' . $fileExtension;

        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $destPath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $image_url = $destPath;
        } else {
            die("❌ Failed to upload image.");
        }
    }

    // ✅ Insert into database
    $stmt = $conn->prepare("
        INSERT INTO recipes 
        (name, image_url, info, video_url, origin, inventor, description, ingredients, steps, calories, protein, fat, carbs, sodium, cholesterol, dietary_properties, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->bind_param(
        "ssssssssssssssss",
        $name,
        $image_url,
        $info,
        $video_url,
        $origin,
        $inventor,
        $description,
        $ingredients,
        $steps,
        $calories,
        $protein,
        $fat,
        $carbs,
        $sodium,
        $cholesterol,
        $dietary_properties
    );

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: dashboard.php?msg=✅ Recipe added successfully!");
        exit();
    } else {
        die("❌ Error adding recipe: " . $stmt->error);
    }
}

// ✅ Handle delete recipe
if (isset($_GET['delete_recipe'])) {
    $id = intval($_GET['delete_recipe']);
    $conn->query("DELETE FROM recipes WHERE id = $id");
    header("Location: dashboard.php?msg=Recipe deleted");
    exit();
}

// ✅ Fetch all users
$users = $conn->query("SELECT id, username, role FROM users ORDER BY id ASC");

// ✅ Fetch all recipes
$recipes = $conn->query("SELECT id, name, image_url, info, video_url FROM recipes ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Eggcipe Admin Dashboard</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <header class="header">
        <div class="logo">🥚 Eggcipe Admin</div>
        <div class="user-section">
            <span>Welcome, <?= htmlspecialchars($_SESSION['username']); ?> (Admin)</span>
            <a href="index.php" class="btn">Home</a>
            <a href="admin_suggestions.php" class="btn">Suggestions</a>
            <a href="logout.php" class="btn logout">Logout</a>
        </div>
    </header>

    <main>
        <h2>Admin Dashboard</h2>
        <?php if (isset($_GET['msg'])): ?>
            <p class="success"><?= htmlspecialchars($_GET['msg']); ?></p>
        <?php endif; ?>

        <!-- 👥 User Management -->
        <section class="panel">
            <h3>👥 User Management</h3>
            <table>
                <tr><th>ID</th><th>Username</th><th>Role</th><th>Actions</th></tr>
                <?php while ($user = $users->fetch_assoc()): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td>
                        <?php if ($user['role'] !== 'admin'): ?>
                            <a href="promote_user.php?id=<?= $user['id'] ?>">Promote</a> |
                        <?php endif; ?>
                        <a href="delete_user.php?id=<?= $user['id'] ?>" onclick="return confirm('Delete this user?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </section>

        <!-- 🍳 Recipe Management -->
        <section class="panel">
            <h3>🍳 Recipe Management</h3>

            <form method="POST" class="recipe-form" enctype="multipart/form-data">
                <h4>Add New Recipe</h4>

                <label>Recipe Name:</label>
                <input type="text" name="name" required>

                <label>Image:</label>
                <input type="file" name="image_url" accept="image/*" required>

                <label>Info:</label>
                <textarea name="info" rows="2" placeholder="Short description"></textarea>

                <label>Video URL:</label>
                <input type="text" name="video_url" placeholder="e.g., https://youtu.be/...">

                <label>Origin:</label>
                <input type="text" name="origin">

                <label>Inventor:</label>
                <input type="text" name="inventor">

                <label>Description:</label>
                <textarea name="description" rows="3"></textarea>

                <label>Ingredients:</label>
                <textarea name="ingredients" rows="3"></textarea>

                <label>Steps:</label>
                <textarea name="steps" rows="3"></textarea>

                <label>Calories:</label>
                <input type="text" name="calories">

                <label>Protein:</label>
                <input type="text" name="protein">

                <label>Fat:</label>
                <input type="text" name="fat">

                <label>Carbs:</label>
                <input type="text" name="carbs">

                <label>Sodium:</label>
                <input type="text" name="sodium">

                <label>Cholesterol:</label>
                <input type="text" name="cholesterol">

                <label>Dietary Properties:</label>
                <input type="text" name="dietary_properties">

                <button type="submit" name="add_recipe">✅ Add Recipe</button>
            </form>

            <h4>Current Recipes</h4>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Image</th>
                    <th>Info</th>
                    <th>Video</th>
                    <th>Actions</th>
                </tr>
                <?php while ($r = $recipes->fetch_assoc()): ?>
                <tr>
                    <td><?= $r['id'] ?></td>
                    <td><?= htmlspecialchars($r['name']) ?></td>
                    <td><img src="<?= htmlspecialchars($r['image_url']) ?>" width="80"></td>
                    <td><?= htmlspecialchars($r['info']) ?></td>
                    <td>
                        <?php if (!empty($r['video_url'])): ?>
                            <a href="<?= htmlspecialchars($r['video_url']) ?>" target="_blank">View</a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit_recipe.php?id=<?= $r['id'] ?>">Edit</a> |
                        <a href="dashboard.php?delete_recipe=<?= $r['id'] ?>" onclick="return confirm('Delete this recipe?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </section>
    </main>

    <!-- ✅ Auto YouTube embed converter -->
    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const form = document.querySelector(".recipe-form");
        const videoInput = form.querySelector("input[name='video_url']");

        form.addEventListener("submit", (e) => {
            let url = videoInput.value.trim();
            if (!url) return;

            // Convert youtu.be link → embed
            if (url.includes("youtu.be/")) {
                const videoId = url.split("youtu.be/")[1].split("?")[0];
                videoInput.value = `https://www.youtube.com/embed/${videoId}`;
            }
            // Convert normal YouTube link → embed
            else if (url.includes("watch?v=")) {
                const videoId = new URL(url).searchParams.get("v");
                videoInput.value = `https://www.youtube.com/embed/${videoId}`;
            }
        });
    });
    </script>
</body>
</html>
