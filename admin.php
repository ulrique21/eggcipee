<?php
session_start();
include 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$result = $conn->query("SELECT * FROM recipes ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Panel - Eggcipe</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>

<h1>Admin Panel</h1>

<h2>Add New Recipe</h2>
<form action="save_recipe.php" method="post">
    <input type="text" name="name" placeholder="Recipe Name" required><br>
    <input type="text" name="image_url" placeholder="Image URL" required><br>
    <textarea name="info" placeholder="Details (one per line)" required></textarea><br>
    <input type="text" name="link" placeholder="Recipe link" required><br>
    <button type="submit">Add Recipe</button>
</form>

<h2>Existing Recipes</h2>
<table border="1" cellpadding="10">
<tr><th>Name</th><th>Image</th><th>Action</th></tr>
<?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td><?php echo htmlspecialchars($row['name']); ?></td>
    <td><img src="<?php echo htmlspecialchars($row['image_url']); ?>" width="100"></td>
    <td>
        <form action="delete_recipe.php" method="post" style="display:inline;">
            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
            <button type="submit" onclick="return confirm('Delete this recipe?')">Delete</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>
</table>

</body>
</html>
