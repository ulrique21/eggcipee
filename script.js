function selectCard(card) {
    // Remove selected class from all cards
    document.querySelectorAll('.dish-card').forEach(c => c.classList.remove('selected'));
    // Add selected class to clicked card
    card.classList.add('selected');
}

function scrollCarousel(direction) {
    const carousel = document.getElementById('carousel');
    const cardWidth = 300; // 280px + 20px gap
    const scrollAmount = cardWidth * direction;
    
    carousel.scrollBy({
        left: scrollAmount,
        behavior: 'smooth'
    });
}

// Auto-scroll functionality (optional)
let autoScrollInterval;

function startAutoScroll() {
    autoScrollInterval = setInterval(() => {
        scrollCarousel(1);
    }, 5000);
}

function stopAutoScroll() {
    clearInterval(autoScrollInterval);
}

// Start auto-scroll on page load
document.addEventListener('DOMContentLoaded', () => {
    startAutoScroll();
    
    // Stop auto-scroll when user interacts
    document.querySelectorAll('.nav-arrow, .dish-card').forEach(element => {
        element.addEventListener('mouseenter', stopAutoScroll);
        element.addEventListener('mouseleave', startAutoScroll);
    });
});

