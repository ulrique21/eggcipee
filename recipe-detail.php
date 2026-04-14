<?php
session_start();
require_once 'config.php';

// ✅ Get recipe ID from URL (e.g., recipe-detail.php?id=1)
$recipe_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// ✅ Fetch recipe details from database (protect against prepare failure)
$stmt = $conn->prepare("SELECT * FROM recipes WHERE id = ?");
if ($stmt === false) {
    error_log("DB prepare failed (recipes SELECT): " . $conn->error);
    // show a friendly page instead of fatal
    http_response_code(500);
    echo "<p>Database error. Please try again later.</p>";
    exit;
}
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$result = $stmt->get_result();
$recipe = $result ? $result->fetch_assoc() : null;
$stmt->close();

// ✅ If recipe not found, redirect to homepage
if (!$recipe) {
    header("Location: index.php");
    exit();
}

// ✅ Check login status
$isLoggedIn = isset($_SESSION['user_id']);
$username = $_SESSION['username'] ?? 'Guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($recipe['name']); ?> Recipe | Eggcipe</title>
    <link rel="stylesheet" href="recipe-detail.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .rating-container { margin: 20px 0; }
        .rating-stars { color: #ffd700; font-size: 24px; cursor: pointer; }
        .rating-stars .star { display: inline-block; margin-right: 5px; cursor: pointer; }
        .rating-stars .star:hover, .rating-stars .star.hovered { transform: scale(1.2); transition: transform 0.2s; }
        .rating-text { margin-top: 5px; font-size: 14px; color: #666; }
        .rating-average { font-weight: bold; color: #333; }
        .rating-count { color: #666; }
    </style>
</head>
<body>

    <!-- 🔹 HEADER -->
    <header class="main-header">
        <a href="index.php" class="logo">🥚 Eggcipe</a>
        <div class="nav-links">
            <?php if ($isLoggedIn): ?>
                <span>Welcome, <?= htmlspecialchars($username); ?>!</span>
                <a href="index.php" class="btn btn-home">Home</a>
                <a href="logout.php" class="btn btn-logout">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn">Login</a>
                <a href="register.php" class="btn">Register</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- 🔹 MAIN CONTENT -->
    <main class="recipe-detail">

        <div class="recipe-header">
            <img src="<?= htmlspecialchars($recipe['image_url']); ?>" alt="<?= htmlspecialchars($recipe['name']); ?>" class="recipe-image">
            <main class="recipe-container">
                <h1><?= htmlspecialchars($recipe['name']); ?></h1>
                
                <div class="recipe-meta">
                    <span class="cooking-time">⏱️ <?= htmlspecialchars($recipe['cooking_time'] ?? 'N/A'); ?> mins</span>
                    <span class="difficulty">🧑‍🍳 <?= htmlspecialchars(ucfirst($recipe['difficulty'] ?? 'Not specified')); ?></span>
                </div>

                <!-- Rating Section -->
                <div class="rating-container">
                    <div class="rating-stars" data-recipe-id="<?= (int)$recipe['id'] ?>">
                        <?php
                        $user_rating = 0;
                        if ($isLoggedIn) {
                            $rstmt = $conn->prepare("SELECT rating FROM ratings WHERE recipe_id = ? AND user_id = ?");
                            if ($rstmt !== false) {
                                $rstmt->bind_param('ii', $recipe['id'], $_SESSION['user_id']);
                                $rstmt->execute();
                                $ur = $rstmt->get_result()->fetch_assoc();
                                $user_rating = $ur['rating'] ?? 0;
                                $rstmt->close();
                            } else {
                                error_log("DB prepare failed (ratings SELECT): " . $conn->error);
                            }
                        }
                        
                        $avg_rating = number_format($recipe['average_rating'] ?? 0, 1);
                        $rating_count = $recipe['rating_count'] ?? 0;
                        
                        for ($i = 1; $i <= 5; $i++) {
                            $active = $i <= $user_rating ? 'active' : '';
                            echo "<span class='star $active' data-rating='$i'>&#9733;</span>";
                        }
                        ?>
                    </div>
                    <div class="rating-text">
                        <span class="rating-average"><?= $avg_rating ?></span> out of 5
                        <span class="rating-count">(<?= (int)$rating_count ?> ratings)</span>
                        <?php if (!$isLoggedIn): ?>
                            <div><small><a href="login.php">Log in</a> to rate this recipe</small></div>
                        <?php endif; ?>
                    </div>
                </div>
                <p class="recipe-info"><strong>Origin:</strong> <?= htmlspecialchars($recipe['origin']); ?></p>
                <p class="recipe-info"><strong>Inventor:</strong> <?= htmlspecialchars($recipe['inventor']); ?></p>
                <p class="recipe-info"><strong>Description:</strong> <?= nl2br(htmlspecialchars($recipe['description'])); ?></p>
            </div>
        </div>

        <?php if ($isLoggedIn): ?>
        <section class="recipe-ingredients">
            <h2>Ingredients</h2>
            <ul>
                <?php 
                $ingredients = explode("\n", $recipe['ingredients'] ?? '');
                foreach ($ingredients as $ingredient): 
                    if (trim($ingredient) !== ''):
                ?>
                    <li>• <?= htmlspecialchars($ingredient); ?></li>
                <?php endif; endforeach; ?>
            </ul>
        </section>

        <section class="recipe-steps">
            <h2>Step-by-Step Tutorial</h2>
            <ol>
                <?php 
                $steps = explode("\n", $recipe['steps'] ?? '');
                foreach ($steps as $step): 
                    if (trim($step) !== ''):
                ?>
                    <li><?= htmlspecialchars($step); ?></li>
                <?php endif; endforeach; ?>
            </ol>
        </section>

        <section class="recipe-nutrition">
            <h2>Complete Nutrition (Per Serving)</h2>
            <div class="nutrition-grid">
                <div><strong>Calories:</strong> <?= htmlspecialchars($recipe['calories']); ?></div>
                <div><strong>Protein:</strong> <?= htmlspecialchars($recipe['protein']); ?></div>
                <div><strong>Fat:</strong> <?= htmlspecialchars($recipe['fat']); ?></div>
                <div><strong>Carbs:</strong> <?= htmlspecialchars($recipe['carbs']); ?></div>
                <div><strong>Sodium:</strong> <?= htmlspecialchars($recipe['sodium']); ?></div>
                <div><strong>Cholesterol:</strong> <?= htmlspecialchars($recipe['cholesterol']); ?></div>
            </div>
        </section>

        <?php if (!empty($recipe['dietary_properties'])): ?>
        <section class="dietary-info">
            <h2>Dietary Properties</h2>
            <ul>
                <?php 
                $props = explode("\n", $recipe['dietary_properties']);
                foreach ($props as $prop): 
                    if (trim($prop) !== ''):
                ?>
                    <li>• <?= htmlspecialchars($prop); ?></li>
                <?php endif; endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>

        <section class="recipe-video">
            <h2>Video Tutorial</h2>
            <div class="video-wrapper">
                <?php if (!empty($recipe['video_url'])): ?>
                    <iframe src="<?= htmlspecialchars($recipe['video_url']); ?>" frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen></iframe>
                <?php else: ?>
                    <p>No video available for this recipe yet.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Comments Section -->
        <?php
        // Fetch comments ordered by likes desc then newest (protect against prepare failure)
        $commentsAll = [];
        $commentsRes = $conn->prepare("SELECT id, user_id, username, content, likes, created_at FROM comments WHERE recipe_id = ? ORDER BY likes DESC, created_at DESC");
        if ($commentsRes === false) {
            error_log("DB prepare failed (comments SELECT): " . $conn->error);
        } else {
            $commentsRes->bind_param('i', $recipe_id);
            $commentsRes->execute();
            $g = $commentsRes->get_result();
            $commentsAll = $g ? $g->fetch_all(MYSQLI_ASSOC) : [];
            $commentsRes->close();
        }
        $topThree = array_slice($commentsAll, 0, 3);
        $moreComments = array_slice($commentsAll, 3);
        ?>
        <section class="comments">
            <h2>Comments</h2>

            <?php if ($isLoggedIn): ?>
            <form id="commentForm" class="comment-form">
                <textarea id="commentContent" name="content" rows="3" maxlength="1000" placeholder="Share your thoughts..." required></textarea>
                <input type="hidden" id="commentRecipeId" name="recipe_id" value="<?= (int)$recipe_id ?>">
                <button type="submit" class="view-btn">Post Comment</button>
            </form>
            <?php else: ?>
            <p class="comment-hint"><a href="login.php">Log in</a> to post a comment.</p>
            <?php endif; ?>

            <div class="comments-list" id="topComments">
                <?php foreach ($topThree as $c): ?>
                <div class="comment" data-comment-id="<?= (int)$c['id'] ?>">
                    <div class="comment-head">
                        <strong><?= htmlspecialchars($c['username']) ?></strong>
                        <span class="date"><?= htmlspecialchars($c['created_at']) ?></span>
                    </div>
                    <div class="comment-body"><?= nl2br(htmlspecialchars($c['content'])) ?></div>
                    <div class="comment-actions">
                        <button class="like-btn" data-id="<?= (int)$c['id'] ?>" <?= $isLoggedIn ? '' : 'disabled' ?>>👍 <span class="likes-count"><?= (int)$c['likes'] ?></span></button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if (count($moreComments) > 0): ?>
            <button id="showMoreComments" class="view-btn" style="margin:12px 0;">Click to scroll for more comments</button>
            <div class="comments-list" id="moreComments" style="display:none;">
                <?php foreach ($moreComments as $c): ?>
                <div class="comment" data-comment-id="<?= (int)$c['id'] ?>">
                    <div class="comment-head">
                        <strong><?= htmlspecialchars($c['username']) ?></strong>
                        <span class="date"><?= htmlspecialchars($c['created_at']) ?></span>
                    </div>
                    <div class="comment-body"><?= nl2br(htmlspecialchars($c['content'])) ?></div>
                    <div class="comment-actions">
                        <button class="like-btn" data-id="<?= (int)$c['id'] ?>" <?= $isLoggedIn ? '' : 'disabled' ?>>👍 <span class="likes-count"><?= (int)$c['likes'] ?></span></button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </section>
        <?php else: ?>
        <section class="guest-gate" style="text-align:center; margin: 30px 0;">
            <p><strong>Log in to view full ingredients, steps, nutrition, and video.</strong></p>
            <div style="display:flex; gap:10px; justify-content:center;">
                <a href="login.php" class="btn">Login</a>
                <a href="register.php" class="btn">Register</a>
            </div>
        </section>
        <?php endif; ?>

    </main>

    <!-- 🔹 FOOTER -->
    <footer class="main-footer">
        <div class="footer-buttons">
             <a href="feedback.php" class="footer-btn">Suggestions & Feedback</a>
            <a href="report.php" class="footer-btn">Report a Problem</a>
            <a href="contact.php" class="footer-btn">Contact Us</a>
        </div>
        <p>&copy; <?= date('Y'); ?> Eggcipe. All rights reserved.</p>
    </footer>

    <script src="recipe-script.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const starsContainer = document.querySelector('.rating-stars');
        if (!starsContainer) return;

        const stars = starsContainer.querySelectorAll('.star');
        const recipeId = starsContainer.dataset.recipeId;
        const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;

        if (!isLoggedIn) return;

        // Highlight stars on hover
        stars.forEach(star => {
            star.addEventListener('mouseover', function() {
                const rating = parseInt(this.dataset.rating);
                highlightStars(rating);
            });

            star.addEventListener('mouseout', function() {
                const activeRating = document.querySelector('.star.active')?.dataset.rating || 0;
                highlightStars(activeRating);
            });

            star.addEventListener('click', function() {
                const rating = this.dataset.rating;
                rateRecipe(recipeId, rating);
            });
        });

        function highlightStars(rating) {
            stars.forEach(star => {
                if (parseInt(star.dataset.rating) <= rating) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
        }

        async function rateRecipe(recipeId, rating) {
            try {
                const response = await fetch('rate_recipe.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ recipe_id: recipeId, rating: parseInt(rating) })
                });

                const result = await response.json();
                
                if (result.success) {
                    const ratingText = document.querySelector('.rating-text');
                    if (ratingText) {
                        const averageSpan = ratingText.querySelector('.rating-average');
                        const countSpan = ratingText.querySelector('.rating-count');
                        
                        if (averageSpan) averageSpan.textContent = result.average_rating;
                        if (countSpan) {
                            const count = parseInt(countSpan.textContent.match(/\d+/)[0] || '0');
                            countSpan.textContent = `(${count + (result.is_new ? 1 : 0)} ratings)`;
                        }
                    }
                    fetch('update_recipe_of_the_week.php');
                } else {
                    alert(result.message || 'Failed to save rating');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while saving your rating');
            }
        }
    });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('commentForm');
        const contentEl = document.getElementById('commentContent');
        const recipeId = document.getElementById('commentRecipeId')?.value;
        const topList = document.getElementById('topComments');
        const moreList = document.getElementById('moreComments');
        const showMoreBtn = document.getElementById('showMoreComments');

        if (showMoreBtn && moreList) {
            showMoreBtn.addEventListener('click', () => {
                moreList.style.display = '';
                moreList.scrollIntoView({ behavior: 'smooth' });
                showMoreBtn.style.display = 'none';
            });
        }

        if (form && contentEl && recipeId) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const content = contentEl.value.trim();
                if (!content) return;
                const body = new FormData();
                body.append('recipe_id', recipeId);
                body.append('content', content);
                const res = await fetch('comments.php', { method: 'POST', body });
                const json = await res.json();
                if (json.success) {
                    const html = `
                    <div class="comment" data-comment-id="${json.comment.id}">
                        <div class="comment-head"><strong>${json.comment.username}</strong>
                        <span class="date">${json.comment.created_at}</span></div>
                        <div class="comment-body">${json.comment.content}</div>
                        <div class="comment-actions"><button class="like-btn" data-id="${json.comment.id}">👍 <span class="likes-count">0</span></button></div>
                    </div>`;
                    if (moreList) moreList.insertAdjacentHTML('afterbegin', html); else topList.insertAdjacentHTML('beforeend', html);
                    contentEl.value = '';
                    wireLikeButtons(document);
                } else {
                    alert(json.message || 'Failed to post comment');
                }
            });
        }

        function wireLikeButtons(root) {
            root.querySelectorAll('.like-btn').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const id = btn.getAttribute('data-id');
                    const body = new FormData();
                    body.append('comment_id', id);
                    const res = await fetch('like_comment.php', { method: 'POST', body });
                    const json = await res.json();
                    if (json.success) {
                        const span = btn.querySelector('.likes-count');
                        if (span && typeof json.likes === 'number') span.textContent = json.likes;
                    } else if (json.message) {
                        alert(json.message);
                    }
                });
            });
        }

        wireLikeButtons(document);
    });
    </script>
</body>
</html>