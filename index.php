<?php
session_start();
require_once 'config.php';

// ✅ Fetch all recipes
$result = $conn->query("SELECT id, name, image_url, info FROM recipes ORDER BY id DESC");

// ✅ User session info
$isLoggedIn = isset($_SESSION['user_id']);
$username = $_SESSION['username'] ?? 'Guest';
$rotw = [
    'title' => 'Top Recipe',
    'name' => '',
    'image_url' => '',
    'info' => '',
    'link' => ''
];
// Load Recipe of the Week from JSON file if present
$rotwPath = __DIR__ . DIRECTORY_SEPARATOR . 'rotw.json';
if (file_exists($rotwPath)) {
    $json = file_get_contents($rotwPath);
    $data = json_decode($json, true);
    if (is_array($data)) {
        $rotw = array_merge($rotw, $data);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Eggcipe | Home</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>

<header class="header">
    <a href="index.php" class="logo">🥚 Eggcipe</a>
    <div class="user-section">
        <?php if ($isLoggedIn): ?>
            <span>Welcome, <?= htmlspecialchars($username); ?>!</span>
            <a href="logout.php" class="login-btn">Logout</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="dashboard.php" class="account-btn">Admin Dashboard</a>
            <?php endif; ?>
        <?php else: ?>
            <a href="login.php" class="login-btn">Login</a>
            <a href="register.php" class="account-btn">Register</a>
        <?php endif; ?>
    </div>
</header>

<main class="main-content">
    <h2 class="section-title">🍳 Explore Egg Recipes</h2>
    <div class="search-container">
        <input type="text" id="recipeSearch" class="search-input" placeholder="Search recipes by name or description...">
        <div class="suggest-inline">
            <button class="suggest-link" id="openSuggestStatic" type="button">Suggest a recipe</button>
        </div>
    </div>

    <div id="noResults" class="no-results" style="display:none;">
        <p>Can't find the recipe you're looking for?</p>
        <button class="suggest-btn" id="openSuggest">Suggest a recipe</button>
    </div>

    <div class="carousel-container">
        <button class="nav-arrow left" onclick="scrollCarousel(-1)">&#10094;</button>

        <div class="carousel" id="carousel">
            <?php while ($recipe = $result->fetch_assoc()): ?>
                <div class="recipe-card">
                    <img src="<?= htmlspecialchars($recipe['image_url']); ?>" alt="<?= htmlspecialchars($recipe['name']); ?>">
                    <h3><?= htmlspecialchars($recipe['name']); ?></h3>
                    <p><?= htmlspecialchars($recipe['info']); ?></p>
                    <a href="recipe-detail.php?id=<?= $recipe['id']; ?>" class="view-btn">View Recipe</a>
                </div>
            <?php endwhile; ?>
        </div>

        <button class="nav-arrow right" onclick="scrollCarousel(1)">&#10095;</button>
    </div>

    <div class="dots" id="dots"></div>
</main>

<!-- Recipe of the Week Sidebar -->
<aside class="rotw-sidebar">
    <div class="rotw-card">
        <h3>🥇 Top Recipe</h3>
        <?php if (!empty($rotw['image_url'])): ?>
            <img src="<?= htmlspecialchars($rotw['image_url']); ?>" alt="<?= htmlspecialchars($rotw['name'] ?: 'Recipe of the Week'); ?>">
        <?php endif; ?>
        <?php if (!empty($rotw['name'])): ?>
            <h4><?= htmlspecialchars($rotw['name']); ?></h4>
        <?php endif; ?>
        <?php if (!empty($rotw['info'])): ?>
            <p><?= htmlspecialchars($rotw['info']); ?></p>
        <?php endif; ?>
        <?php if (!empty($rotw['link'])): ?>
            <a class="view-btn" href="<?= htmlspecialchars($rotw['link']); ?>">View Recipe</a>
        <?php endif; ?>
        <?php if ($isLoggedIn && isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a class="account-btn edit-rotw-btn" href="admin_featured.php">Edit</a>
        <?php endif; ?>
    </div>
    <div class="rotw-shadow"></div>
 </aside>

<footer class="footer">
    <div class="footer-buttons">
        <a href="feedback.php" class="footer-btn">Suggestions & Feedback</a>
        <a href="report.php" class="footer-btn">Report a Problem</a>
        <a href="contact.php" class="footer-btn">Contact Us</a>
    </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const carousel = document.getElementById('carousel');
    const dotsContainer = document.getElementById('dots');
    const allCards = Array.from(document.querySelectorAll('.recipe-card'));
    let currentIndex = 0;
    const cardWidth = 320; // must match CSS card width + gap
    const visibleCardsPerView = 3; // how many cards show per view

    function getVisibleCards() {
        return allCards.filter(card => card.style.display !== 'none');
    }

    function scrollCarousel(direction) {
        const visible = getVisibleCards();
        const maxIndex = Math.max(0, Math.ceil(visible.length / visibleCardsPerView) - 1);
        currentIndex += direction;
        if (currentIndex < 0) currentIndex = 0;
        if (currentIndex > maxIndex) currentIndex = maxIndex;
        carousel.scrollTo({
            left: currentIndex * (cardWidth * visibleCardsPerView),
            behavior: 'smooth'
        });
        updateDots();
    }
    window.scrollCarousel = scrollCarousel; // keep arrows working

    function updateDots() {
        dotsContainer.innerHTML = '';
        const total = Math.max(1, Math.ceil(getVisibleCards().length / visibleCardsPerView));
        for (let i = 0; i < total; i++) {
            const dot = document.createElement('span');
            dot.className = 'dot' + (i === currentIndex ? ' active' : '');
            dot.addEventListener('click', () => {
                currentIndex = i;
                scrollCarousel(0);
            });
            dotsContainer.appendChild(dot);
        }
    }

    updateDots();

    // Search filter
    const searchInput = document.getElementById('recipeSearch');
    const noResults = document.getElementById('noResults');
    const openSuggestBtn = document.getElementById('openSuggest');
    const openSuggestStatic = document.getElementById('openSuggestStatic');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            allCards.forEach(card => {
                const name = card.querySelector('h3')?.textContent?.toLowerCase() || '';
                const info = card.querySelector('p')?.textContent?.toLowerCase() || '';
                const matches = !term || name.includes(term) || info.includes(term);
                card.style.display = matches ? '' : 'none';
            });
            const visibleCount = getVisibleCards().length;
            currentIndex = 0;
            carousel.scrollLeft = 0;
            updateDots();
            if (noResults) noResults.style.display = (visibleCount === 0 ? '' : 'none');
        });
    }

    // Suggest modal controls
    const suggestModal = document.getElementById('suggestModal');
    const closeSuggest = document.getElementById('closeSuggest');
    const openModal = () => { if (suggestModal) suggestModal.style.display = 'flex'; };
    const closeModal = () => { if (suggestModal) suggestModal.style.display = 'none'; };

    if (openSuggestBtn) openSuggestBtn.addEventListener('click', openModal);
    if (openSuggestStatic) openSuggestStatic.addEventListener('click', openModal);
    if (closeSuggest) closeSuggest.addEventListener('click', closeModal);
    window.addEventListener('click', (e) => { if (e.target === suggestModal) closeModal(); });
});
</script>

<!-- Suggest Recipe Modal -->
<div id="suggestModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Suggest a Recipe</h3>
            <button id="closeSuggest" class="modal-close">×</button>
        </div>
        <form method="POST" action="suggest_recipe.php" class="modal-form">
            <div class="form-row">
                <label for="sugg_name">Your Name</label>
                <input type="text" id="sugg_name" name="name" required>
            </div>
            <div class="form-row">
                <label for="sugg_email">Your Email</label>
                <input type="email" id="sugg_email" name="email" required>
            </div>
            <div class="form-row">
                <label for="sugg_recipe">Recipe Name</label>
                <input type="text" id="sugg_recipe" name="recipe_name" required>
            </div>
            <div class="form-row">
                <label for="sugg_details">Details (optional)</label>
                <textarea id="sugg_details" name="details" rows="4" placeholder="Tell us about the recipe, ingredients, or a link."></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="view-btn">Send Suggestion</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
