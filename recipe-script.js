let currentRating = 0;

function rateDifficulty(rating) {
    currentRating = rating;
    const stars = document.querySelectorAll('.star');
    
    stars.forEach((star, index) => {
        if (index < rating) {
            star.style.color = '#ffc107';
        } else {
            star.style.color = '#e0e0e0';
        }
    });
}

function addComment() {
    const textarea = document.querySelector('.add-comment textarea');
    const commentText = textarea.value.trim();
    
    if (commentText) {
        const commentsList = document.querySelector('.comments-list');
        const newComment = document.createElement('div');
        newComment.className = 'comment';
        newComment.innerHTML = `
            <div class="comment-author">You</div>
            <div class="comment-text">${commentText}</div>
        `;
        
        commentsList.appendChild(newComment);
        textarea.value = '';
        
        // Scroll to the new comment
        newComment.scrollIntoView({ behavior: 'smooth' });
    }
}

function playVideo() {
    alert('Video player would open here! In a real implementation, this would embed a video player or open a modal with the video.');
}

// Allow Enter key to submit comments
document.addEventListener('DOMContentLoaded', () => {
    const textarea = document.querySelector('.add-comment textarea');
    if (textarea) {
        textarea.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && e.ctrlKey) {
                addComment();
            }
        });
    }
});

