// ==================== SCANNER PAGE ====================

document.addEventListener('DOMContentLoaded', function() {
    initSearch();
    initScanButton();
});

// ==================== SEARCH ====================

function initSearch() {
    const searchInput = document.getElementById('product-search');
    let searchTimeout;

    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const query = this.value.trim();
            if (query) {
                searchProduct(query);
            }
        }
    });

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length >= 3) {
            searchTimeout = setTimeout(() => {
                searchProduct(query);
            }, 500);
        }
    });
}

async function searchProduct(query) {
    try {
        const response = await fetch(`/scanner/search?q=${encodeURIComponent(query)}`);
        const data = await response.json();

        if (data.success && data.product) {
            displayProduct(data.product);
            if (data.alternatives) {
                displayAlternatives(data.alternatives);
            }
        } else {
            showNoResults();
        }
    } catch (error) {
        console.error('Search error:', error);
        showError('Failed to search product');
    }
}

// ==================== SCAN BUTTON ====================

function initScanButton() {
    const scanBtn = document.getElementById('scan-barcode-btn');
    
    scanBtn.addEventListener('click', function() {
        // Mock barcode scan - w produkcji użyłby QuaggaJS lub html5-qrcode
        const mockBarcode = '5901234123457';
        document.getElementById('product-search').value = 'Organic Wholewheat Bread';
        searchProduct('Organic Wholewheat Bread');
    });
}

// ==================== DISPLAY PRODUCT ====================

function displayProduct(product) {
    const resultDiv = document.getElementById('product-result');
    
    const safetyClass = product.is_gluten_free ? 'safe' : 'unsafe';
    const safetyText = product.is_gluten_free ? 
        'Safe to consume - This product is verified gluten-free' :
        'Contains gluten - Not safe for celiac diet';
    
    resultDiv.innerHTML = `
        <div class="product-header">
            <div class="product-image">
                ${product.image_url ? 
                    `<img src="${product.image_url}" alt="${escapeHtml(product.name)}" onerror="this.parentElement.innerHTML='<span style=\\'font-size:3rem\\'>🍞</span>'">` :
                    '<span style="font-size:3rem">🍞</span>'
                }
            </div>
            <div class="product-info">
                ${product.certified ? `
                <div class="product-badge certified">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>Certified Gluten-Free</span>
                </div>
                ` : ''}
                <h2 class="product-name">${escapeHtml(product.name)}</h2>
                <p class="product-brand">${escapeHtml(product.brand)}</p>
                <button class="favorite-btn" onclick="toggleFavorite()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <div class="safety-status ${safetyClass}">
            <strong>${product.is_gluten_free ? '✅' : '❌'} ${safetyText.split(' - ')[0]}</strong>
            <p>${safetyText.split(' - ')[1]}</p>
        </div>
        
        ${product.ingredients ? `
        <div class="product-details">
            <h3 style="font-weight: 600; margin-bottom: 0.5rem;">Ingredients:</h3>
            <p style="color: var(--color-text-light);">${escapeHtml(product.ingredients)}</p>
        </div>
        ` : ''}
    `;
    
    resultDiv.style.display = 'block';
}

// ==================== DISPLAY ALTERNATIVES ====================

function displayAlternatives(alternatives) {
    const section = document.getElementById('alternatives-section');
    const grid = document.getElementById('alternatives-grid');
    
    if (!alternatives || alternatives.length === 0) {
        section.style.display = 'none';
        return;
    }
    
    grid.innerHTML = alternatives.map(alt => `
        <div class="alternative-card" onclick="searchProduct('${escapeHtml(alt.name)}')">
            <div class="alternative-image">
                ${alt.image_url ? 
                    `<img src="${alt.image_url}" alt="${escapeHtml(alt.name)}" onerror="this.parentElement.innerHTML='<span style=\\'font-size:2rem\\'>🍞</span>'">` :
                    '<span style="font-size:2rem">🍞</span>'
                }
            </div>
            <div class="alternative-info">
                <div class="alternative-name">${escapeHtml(alt.name)}</div>
                <div class="alternative-brand">${escapeHtml(alt.brand)}</div>
            </div>
        </div>
    `).join('');
    
    section.style.display = 'block';
}

// ==================== HELPERS ====================

function showNoResults() {
    const resultDiv = document.getElementById('product-result');
    resultDiv.innerHTML = `
        <div style="text-align: center; padding: 2rem;">
            <p style="font-size: 1.125rem; color: var(--color-text-light);">
                No product found. Try a different search term.
            </p>
        </div>
    `;
    resultDiv.style.display = 'block';
    
    document.getElementById('alternatives-section').style.display = 'none';
}

function showError(message) {
    const resultDiv = document.getElementById('product-result');
    resultDiv.innerHTML = `
        <div style="text-align: center; padding: 2rem;">
            <p style="color: var(--color-error);">${escapeHtml(message)}</p>
        </div>
    `;
    resultDiv.style.display = 'block';
}

function toggleFavorite() {
    alert('Favorite feature coming soon!');
}

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