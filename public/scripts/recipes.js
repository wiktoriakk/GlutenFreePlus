// ==================== RECIPES PAGE ====================

let currentTab = 'recipes';

document.addEventListener('DOMContentLoaded', function() {
    initTabs();
    loadRecipes();
    initSearch();
    initAddButton();
});

// ==================== TABS ====================

function initTabs() {
    const tabBtns = document.querySelectorAll('.tab-btn');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            currentTab = this.dataset.tab;
            loadRecipes();
        });
    });
}

// ==================== LOAD RECIPES ====================

async function loadRecipes(search = '') {
    const grid = document.getElementById('recipes-grid');
    grid.innerHTML = '<div class="loading" style="grid-column: 1/-1; padding: 2rem; text-align: center;"><div class="spinner" style="margin: 0 auto;"></div></div>';

    try {
        let url = `/recipes/get?type=${currentTab}`;
        if (search) url += `&search=${encodeURIComponent(search)}`;

        const response = await fetch(url);
        const data = await response.json();

        if (data.success && data.recipes) {
            displayRecipes(data.recipes);
        } else {
            showEmpty();
        }
    } catch (error) {
        console.error('Error loading recipes:', error);
        grid.innerHTML = '<p style="grid-column: 1/-1; text-align: center; color: var(--color-error);">Failed to load recipes</p>';
    }
}

// ==================== DISPLAY RECIPES ====================

function displayRecipes(recipes) {
    const grid = document.getElementById('recipes-grid');

    if (recipes.length === 0) {
        showEmpty();
        return;
    }

    grid.innerHTML = recipes.map(recipe => createRecipeCard(recipe)).join('');
}

function createRecipeCard(recipe) {
    return `
        <div class="recipe-card" onclick="viewRecipe(${recipe.id})">
            <div class="recipe-image">
                ${recipe.image_url ? 
                    `<img src="${recipe.image_url}" alt="${escapeHtml(recipe.title)}" onerror="this.parentElement.innerHTML='<div style=\\'display:flex;align-items:center;justify-content:center;height:100%;font-size:3rem;\\'>🍳</div>'">` :
                    '<div style="display:flex;align-items:center;justify-content:center;height:100%;font-size:3rem;">🍳</div>'
                }
            </div>
            <div class="recipe-info">
                <h3 class="recipe-title">${escapeHtml(recipe.title)}</h3>
                <p class="recipe-author">by ${escapeHtml(recipe.author_name)}</p>
                <div class="recipe-stats">
                    <div class="stat likes">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                        </svg>
                        <span>${recipe.likes}</span>
                    </div>
                    <div class="stat comments">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                        <span>${recipe.comments}</span>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// ==================== SEARCH ====================

function initSearch() {
    const searchInput = document.getElementById('recipe-search');
    let searchTimeout;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            searchTimeout = setTimeout(() => {
                loadRecipes(query);
            }, 300);
        });
    }
}

// ==================== ADD RECIPE BUTTON ====================

function initAddButton() {
    const addBtn = document.getElementById('add-recipe-btn');
    
    if (addBtn) {
        addBtn.addEventListener('click', function() {
            window.location.href = '/recipes/create';
        });
    }
}

// ==================== VIEW RECIPE ====================

function viewRecipe(recipeId) {
    console.log('View recipe:', recipeId);
    window.location.href = `/recipes/show?id=${recipeId}`;
}

// ==================== EMPTY STATE ====================

function showEmpty() {
    const grid = document.getElementById('recipes-grid');
    
    let message = 'No recipes found';
    let icon = '🍳';
    
    if (currentTab === 'favourites') {
        message = 'No favourite recipes yet';
        icon = '❤️';
    } else if (currentTab === 'tips') {
        message = 'No tips available';
        icon = '💡';
    }
    
    grid.innerHTML = `
        <div class="recipes-empty" style="grid-column: 1/-1;">
            <div class="recipes-empty-icon">${icon}</div>
            <p class="recipes-empty-text">${message}</p>
        </div>
    `;
}

// ==================== HELPERS ====================

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}