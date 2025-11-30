// ==================== COMMUNITY PAGE ====================

document.addEventListener('DOMContentLoaded', function() {
    loadMembers();
    initFilters();
    initSearch();
});

let currentFilter = 'all';

// ==================== LOAD MEMBERS ====================

async function loadMembers(search = '', userType = '') {
    const loading = document.getElementById('loading');
    const membersGrid = document.getElementById('members-grid');

    loading.style.display = 'block';
    membersGrid.innerHTML = '';

    try {
        let url = '/community/members?';
        if (search) url += `search=${encodeURIComponent(search)}&`;
        if (userType && userType !== 'all') url += `user_type=${encodeURIComponent(userType)}&`;

        const response = await fetch(url);
        const data = await response.json();

        if (data.success && data.users) {
            if (data.users.length === 0) {
                membersGrid.innerHTML = '<p class="text-center" style="grid-column: 1/-1; padding: 2rem;">No members found</p>';
            } else {
                data.users.forEach(user => {
                    membersGrid.appendChild(createMemberCard(user));
                });
            }
        } else {
            membersGrid.innerHTML = '<p class="text-center" style="grid-column: 1/-1; color: var(--color-error);">Failed to load members</p>';
        }
    } catch (error) {
        console.error('Error loading members:', error);
        membersGrid.innerHTML = '<p class="text-center" style="grid-column: 1/-1; color: var(--color-error);">An error occurred</p>';
    } finally {
        loading.style.display = 'none';
    }
}

// ==================== CREATE MEMBER CARD ====================

function createMemberCard(user) {
    const card = document.createElement('div');
    card.className = 'member-card';

    const avatarBg = getAvatarColor(user.name);

    card.innerHTML = `
        <div class="member-avatar" style="background: ${avatarBg}">
            ${user.avatar ? 
                `<img src="${user.avatar}" alt="${user.name}" onerror="this.style.display='none'">` : 
                `<span style="font-size: 2rem; font-weight: 600; color: white;">${getInitials(user.name)}</span>`
            }
        </div>
        <h3 class="member-name">${escapeHtml(user.name)}</h3>
        ${user.user_type ? `<span class="member-type">${escapeHtml(user.user_type)}</span>` : ''}
        <div class="member-actions">
            <button class="btn-view-profile" onclick="viewProfile(${user.id})">View profile</button>
        </div>
    `;

    return card;
}

// ==================== FILTERS ====================

function initFilters() {
    const filterBtns = document.querySelectorAll('.filter-btn');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            currentFilter = this.dataset.filter;
            const userType = currentFilter === 'all' ? '' : currentFilter;
            loadMembers('', userType);
        });
    });
}

// ==================== SEARCH ====================

function initSearch() {
    const searchInput = document.getElementById('search-input');
    let searchTimeout;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const searchTerm = this.value.trim();
            if (searchTerm) {
                loadMembers(searchTerm, '');
                // Reset filter to "All" when searching
                document.querySelectorAll('.filter-btn').forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.filter === 'all');
                });
                currentFilter = 'all';
            } else {
                loadMembers('', currentFilter === 'all' ? '' : currentFilter);
            }
        }, 300);
    });
}

// ==================== HELPER FUNCTIONS ====================

function viewProfile(userId) {
    window.location.href = `/profile/${userId}`;
}

function getInitials(name) {
    return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
}

function getAvatarColor(name) {
    const colors = [
        'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
        'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
        'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
        'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
        'linear-gradient(135deg, #30cfd0 0%, #330867 100%)',
        'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)',
        'linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%)',
    ];

    const index = name.charCodeAt(0) % colors.length;
    return colors[index];
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