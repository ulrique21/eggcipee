<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Update status action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['suggestion_id'], $_POST['status'])) {
    $id = (int)$_POST['suggestion_id'];
    $status = $_POST['status'];
    $allowed = ['new','reviewing','added','rejected'];
    if (in_array($status, $allowed, true)) {
        $stmt = $conn->prepare("UPDATE suggestions SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $status, $id);
        $stmt->execute();
        $stmt->close();
        header('Location: admin_suggestions.php?msg=Status updated');
        exit();
    }
}

$res = $conn->query("SELECT id, user_id, name, email, recipe_name, details, status, created_at FROM suggestions ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Eggcipe Admin - Suggestions</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <header class="header">
        <div class="logo">🥚 Eggcipe Admin</div>
        <div class="user-section">
            <a href="dashboard.php" class="btn">Dashboard</a>
            <a href="index.php" class="btn">Home</a>
            <a href="logout.php" class="btn logout">Logout</a>
        </div>
    </header>

    <main>
        <h2>Recipe Suggestions</h2>
        <?php if (isset($_GET['msg'])): ?>
            <p class="success"><?= htmlspecialchars($_GET['msg']); ?></p>
        <?php endif; ?>

        <section class="panel">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>From</th>
                    <th>Email</th>
                    <th>Recipe</th>
                    <th>Details</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = $res->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                    <td><?= htmlspecialchars($row['name']) ?><?php if (!empty($row['user_id'])): ?> (UID <?= (int)$row['user_id'] ?>)<?php endif; ?></td>
                    <td><a href="mailto:<?= htmlspecialchars($row['email']) ?>"><?= htmlspecialchars($row['email']) ?></a></td>
                    <td><?= htmlspecialchars($row['recipe_name']) ?></td>
                    <td><?= nl2br(htmlspecialchars($row['details'])) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td>
                        <form method="POST" style="display:inline-flex; gap:6px; align-items:center;">
                            <input type="hidden" name="suggestion_id" value="<?= $row['id'] ?>">
                            <select name="status">
                                <option value="new" <?= $row['status']==='new'?'selected':'' ?>>new</option>
                                <option value="reviewing" <?= $row['status']==='reviewing'?'selected':'' ?>>reviewing</option>
                                <option value="added" <?= $row['status']==='added'?'selected':'' ?>>added</option>
                                <option value="rejected" <?= $row['status']==='rejected'?'selected':'' ?>>rejected</option>
                            </select>
                            <button type="submit" class="btn">Update</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </section>
    </main>
</body>
</html>



