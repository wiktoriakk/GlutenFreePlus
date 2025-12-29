// ==================== MODERATOR PANEL JS ====================

let allPosts = [];
let allComments = [];

document.addEventListener('DOMContentLoaded', () => {
    loadPosts();
    loadComments();
    
    // Tab switching
    const tabs = document.querySelectorAll('.tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => switchTab(tab.dataset.tab));
    });
    
    // Search functionality
    const searchPosts = document.getElementById('search-posts');
    if (searchPosts) {
        searchPosts.addEventListener('input', (e) => {
            filterPosts(e.target.value);
        });
    }
    
    const searchComments = document.getElementById('search-comments');
    if (searchComments) {
        searchComments.addEventListener('input', (e) => {
            filterComments(e.target.value);
        });
    }
});

// ==================== TAB SWITCHING ====================

function switchTab(tabName) {
    // Update tab buttons
    document.querySelectorAll('.tab').forEach(tab => {
        tab.classList.toggle('active', tab.dataset.tab === tabName);
    });
    
    // Update tab content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.toggle('active', content.id === `${tabName}-content`);
    });
}

// ==================== LOAD POSTS ====================

async function loadPosts() {
    try {
        const response = await fetch('/moderator/posts/list');
        const data = await response.json();
        
        if (data.success) {
            allPosts = data.posts;
            renderPosts(allPosts);
            updateStats();
        } else {
            showAlert('Failed to load posts', 'error');
        }
    } catch (error) {
        console.error('Load posts error:', error);
        showAlert('Connection error. Please try again.', 'error');
    }
}

// ==================== RENDER POSTS ====================

function renderPosts(posts) {
    const container = document.getElementById('posts-list');
    
    if (posts.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
                <h3>No posts found</h3>
                <p>There are no posts to moderate at the moment.</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = posts.map(post => `
        <div class="content-item" data-post-id="${post.id}">
            <div class="item-header">
                <div class="item-info">
                    <h3 class="item-title">${escapeHtml(post.title)}</h3>
                    <div class="item-meta">
                        <span class="post-type-badge type-${post.post_type}">
                            ${getPostTypeIcon(post.post_type)} ${post.post_type}
                        </span>
                        <span class="item-meta-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            ${escapeHtml(post.author)}
                        </span>
                        <span class="item-meta-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            ${formatDate(post.created_at)}
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="item-content">
                ${escapeHtml(post.content)}
            </div>
            
            <div class="item-footer">
                <div class="item-stats">
                    <span class="item-stat">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                        ${post.likes_count} likes
                    </span>
                    <span class="item-stat">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                        ${post.comments_count} comments
                    </span>
                </div>
                
                <div class="item-actions">
                    <button class="btn-action btn-view" onclick="viewPost(${post.id})">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        View
                    </button>
                    <button class="btn-action btn-hide" onclick="hidePost(${post.id}, '${escapeHtml(post.title)}')">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                        Hide
                    </button>
                    <button class="btn-action btn-delete" onclick="showDeletePostModal(${post.id}, '${escapeHtml(post.title)}')">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                        Delete
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

// ==================== LOAD COMMENTS ====================

async function loadComments() {
    try {
        const response = await fetch('/moderator/comments/list');
        const data = await response.json();
        
        if (data.success) {
            allComments = data.comments;
            renderComments(allComments);
            updateStats();
        } else {
            showAlert('Failed to load comments', 'error');
        }
    } catch (error) {
        console.error('Load comments error:', error);
        showAlert('Connection error. Please try again.', 'error');
    }
}

// ==================== RENDER COMMENTS ====================

function renderComments(comments) {
    const container = document.getElementById('comments-list');
    
    if (comments.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <h3>No comments found</h3>
                <p>There are no comments to moderate at the moment.</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = comments.map(comment => `
        <div class="comment-item" data-comment-id="${comment.id}">
            <div class="comment-header">
                <span class="comment-author">${escapeHtml(comment.author)}</span>
                <span style="font-size: 0.875rem; color: var(--color-text-muted);">${formatDate(comment.created_at)}</span>
            </div>
            
            <div class="comment-post">
                On post: <span class="comment-post-title">"${escapeHtml(comment.post_title)}"</span>
            </div>
            
            <div class="comment-content">
                ${escapeHtml(comment.content)}
            </div>
            
            <div class="comment-footer">
                <span>${escapeHtml(comment.author_email)}</span>
                <div class="item-actions">
                    <button class="btn-action btn-delete" onclick="showDeleteCommentModal(${comment.id})">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                        Delete
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

// ==================== UPDATE STATS ====================

function updateStats() {
    document.getElementById('total-posts').textContent = allPosts.length;
    document.getElementById('total-comments').textContent = allComments.length;
}

// ==================== FILTER FUNCTIONS ====================

function filterPosts(searchTerm) {
    const filtered = allPosts.filter(post => 
        post.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
        post.content.toLowerCase().includes(searchTerm.toLowerCase()) ||
        post.author.toLowerCase().includes(searchTerm.toLowerCase())
    );
    
    renderPosts(filtered);
}

function filterComments(searchTerm) {
    const filtered = allComments.filter(comment => 
        comment.content.toLowerCase().includes(searchTerm.toLowerCase()) ||
        comment.author.toLowerCase().includes(searchTerm.toLowerCase())
    );
    
    renderComments(filtered);
}

// ==================== POST ACTIONS ====================

function viewPost(postId) {
    // Redirect to community post view (to be implemented)
    window.location.href = `/community/post/${postId}`;
}

async function hidePost(postId, postTitle) {
    if (!confirm(`Hide post "${postTitle}"? It will no longer be visible to users.`)) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('post_id', postId);
        
        const response = await fetch('/moderator/posts/hide', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.message, 'success');
            // Remove from list
            allPosts = allPosts.filter(p => p.id !== postId);
            renderPosts(allPosts);
            updateStats();
        } else {
            showAlert(data.error, 'error');
        }
    } catch (error) {
        console.error('Hide post error:', error);
        showAlert('Connection error. Please try again.', 'error');
    }
}

function showDeletePostModal(postId, postTitle) {
    const modal = document.createElement('div');
    modal.className = 'modal active';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>Delete Post</h3>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to permanently delete this post?</p>
                <p style="font-weight: 600; margin-top: 12px;">"${escapeHtml(postTitle)}"</p>
                <p style="color: #C62828; margin-top: 12px;">⚠️ This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button class="btn btn-danger" style="background: #C62828;" onclick="deletePost(${postId})">Delete Post</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });
}

async function deletePost(postId) {
    try {
        const formData = new FormData();
        formData.append('post_id', postId);
        
        const response = await fetch('/moderator/posts/delete', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.message, 'success');
            closeModal();
            
            // Remove from list
            allPosts = allPosts.filter(p => p.id !== postId);
            renderPosts(allPosts);
            updateStats();
        } else {
            showAlert(data.error, 'error');
        }
    } catch (error) {
        console.error('Delete post error:', error);
        showAlert('Connection error. Please try again.', 'error');
    }
}

// ==================== COMMENT ACTIONS ====================

function showDeleteCommentModal(commentId) {
    const modal = document.createElement('div');
    modal.className = 'modal active';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>Delete Comment</h3>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this comment?</p>
                <p style="color: #C62828; margin-top: 12px;">⚠️ This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button class="btn btn-danger" style="background: #C62828;" onclick="deleteComment(${commentId})">Delete Comment</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });
}

async function deleteComment(commentId) {
    try {
        const formData = new FormData();
        formData.append('comment_id', commentId);
        
        const response = await fetch('/moderator/comments/delete', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.message, 'success');
            closeModal();
            
            // Remove from list
            allComments = allComments.filter(c => c.id !== commentId);
            renderComments(allComments);
            updateStats();
        } else {
            showAlert(data.error, 'error');
        }
    } catch (error) {
        console.error('Delete comment error:', error);
        showAlert('Connection error. Please try again.', 'error');
    }
}

// ==================== HELPERS ====================

function closeModal() {
    const modal = document.querySelector('.modal');
    if (modal) modal.remove();
}

function showAlert(message, type) {
    const container = document.getElementById('alert-container');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            ${type === 'success' ? 
                '<polyline points="20 6 9 17 4 12"></polyline>' : 
                '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line>'
            }
        </svg>
        ${message}
    `;
    
    container.innerHTML = '';
    container.appendChild(alert);
    
    setTimeout(() => alert.remove(), 5000);
}

function getPostTypeIcon(type) {
    const icons = {
        tip: '💡',
        question: '❓',
        story: '📖',
        general: '📝'
    };
    return icons[type] || '📝';
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);
    
    if (minutes < 60) return `${minutes}m ago`;
    if (hours < 24) return `${hours}h ago`;
    if (days < 7) return `${days}d ago`;
    
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}