// ==================== RECIPE DETAIL JS ====================

document.addEventListener('DOMContentLoaded', () => {
    initFavoriteButton();
});

function initFavoriteButton() {
    const favoriteBtn = document.getElementById('favorite-btn');
    
    if (favoriteBtn) {
        favoriteBtn.addEventListener('click', toggleFavorite);
    }
}

async function toggleFavorite() {
    const btn = document.getElementById('favorite-btn');
    const recipeId = btn.dataset.recipeId;
    const likesCount = document.getElementById('likes-count');
    
    if (!recipeId) return;
    
    try {
        const formData = new FormData();
        formData.append('recipe_id', recipeId);
        
        const response = await fetch('/recipes/toggle-favorite', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Toggle liked state
            btn.classList.toggle('liked');
            
            // Update likes count
            if (likesCount) {
                const currentCount = parseInt(likesCount.textContent);
                likesCount.textContent = data.liked ? currentCount + 1 : currentCount - 1;
            }
            
            // Show feedback
            showToast(data.message || 'Added to favorites!');
        } else {
            showToast(data.error || 'Failed to toggle favorite', 'error');
        }
    } catch (error) {
        console.error('Toggle favorite error:', error);
        showToast('Connection error', 'error');
    }
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        bottom: 100px;
        right: 20px;
        padding: 12px 20px;
        background: ${type === 'success' ? '#2E7D32' : '#C62828'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1000;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 2000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);