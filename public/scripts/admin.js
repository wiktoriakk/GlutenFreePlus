// ==================== ADMIN PANEL JS ====================

let allUsers = [];

document.addEventListener('DOMContentLoaded', () => {
    loadUsers();
    
    // Search functionality
    const searchInput = document.getElementById('search-users');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            filterUsers(e.target.value);
        });
    }
});

// ==================== LOAD USERS ====================

async function loadUsers() {
    try {
        const response = await fetch('/admin/users/list');
        const data = await response.json();
        
        if (data.success) {
            allUsers = data.users;
            renderUsers(allUsers);
            updateStats(allUsers);
        } else {
            showAlert('Failed to load users', 'error');
        }
    } catch (error) {
        console.error('Load users error:', error);
        showAlert('Connection error. Please try again.', 'error');
    }
}

// ==================== RENDER USERS ====================

function renderUsers(users) {
    const tbody = document.getElementById('users-table-body');
    
    if (users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="loading">No users found</td></tr>';
        return;
    }
    
    tbody.innerHTML = users.map(user => `
        <tr data-user-id="${user.id}">
            <td>#${user.id}</td>
            <td>
                <strong>${escapeHtml(user.name)}</strong>
            </td>
            <td>${escapeHtml(user.email)}</td>
            <td>
                <span class="role-badge role-${user.role}">
                    ${getRoleIcon(user.role)} ${user.role}
                </span>
            </td>
            <td>${user.user_type || '-'}</td>
            <td>
                <span class="status-badge ${user.is_active ? 'status-active' : 'status-inactive'}">
                    <span class="status-dot"></span>
                    ${user.is_active ? 'Active' : 'Blocked'}
                </span>
            </td>
            <td>${formatDate(user.created_at)}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-action btn-role" onclick="showChangeRoleModal(${user.id}, '${user.role}', '${escapeHtml(user.name)}')">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                        Role
                    </button>
                    <button class="btn-action ${user.is_active ? 'btn-block' : 'btn-activate'}" onclick="toggleUserStatus(${user.id}, ${user.is_active})">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            ${user.is_active ? 
                                '<circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line>' :
                                '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline>'
                            }
                        </svg>
                        ${user.is_active ? 'Block' : 'Activate'}
                    </button>
                    <button class="btn-action btn-delete" onclick="showDeleteModal(${user.id}, '${escapeHtml(user.name)}')">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                        Delete
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// ==================== UPDATE STATS ====================

function updateStats(users) {
    const total = users.length;
    const active = users.filter(u => u.is_active).length;
    const admins = users.filter(u => u.role === 'admin').length;
    const moderators = users.filter(u => u.role === 'moderator').length;
    
    document.getElementById('total-users').textContent = total;
    document.getElementById('active-users').textContent = active;
    document.getElementById('admin-count').textContent = admins;
    document.getElementById('moderator-count').textContent = moderators;
}

// ==================== FILTER USERS ====================

function filterUsers(searchTerm) {
    const filtered = allUsers.filter(user => 
        user.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        user.email.toLowerCase().includes(searchTerm.toLowerCase()) ||
        user.role.toLowerCase().includes(searchTerm.toLowerCase())
    );
    
    renderUsers(filtered);
}

// ==================== TOGGLE USER STATUS ====================

async function toggleUserStatus(userId, currentStatus) {
    if (!confirm(`Are you sure you want to ${currentStatus ? 'block' : 'activate'} this user?`)) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('user_id', userId);
        
        const response = await fetch('/admin/users/toggle-status', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.message, 'success');
            
            // Update user in array
            const userIndex = allUsers.findIndex(u => u.id === userId);
            if (userIndex !== -1) {
                allUsers[userIndex].is_active = data.is_active;
                renderUsers(allUsers);
                updateStats(allUsers);
            }
        } else {
            showAlert(data.error, 'error');
        }
    } catch (error) {
        console.error('Toggle status error:', error);
        showAlert('Connection error. Please try again.', 'error');
    }
}

// ==================== CHANGE ROLE ====================

function showChangeRoleModal(userId, currentRole, userName) {
    const modal = document.createElement('div');
    modal.className = 'modal active';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>Change User Role</h3>
            </div>
            <div class="modal-body">
                <p style="margin-bottom: 16px;">Select new role for <strong>${escapeHtml(userName)}</strong>:</p>
                <select id="new-role" class="form-select" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem;">
                    <option value="user" ${currentRole === 'user' ? 'selected' : ''}>User</option>
                    <option value="moderator" ${currentRole === 'moderator' ? 'selected' : ''}>Moderator</option>
                    <option value="admin" ${currentRole === 'admin' ? 'selected' : ''}>Admin</option>
                </select>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button class="btn btn-primary" onclick="changeUserRole(${userId})">Change Role</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Close on backdrop click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });
}

async function changeUserRole(userId) {
    const newRole = document.getElementById('new-role').value;
    
    try {
        const formData = new FormData();
        formData.append('user_id', userId);
        formData.append('role', newRole);
        
        const response = await fetch('/admin/users/change-role', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.message, 'success');
            closeModal();
            
            // Update user in array
            const userIndex = allUsers.findIndex(u => u.id === userId);
            if (userIndex !== -1) {
                allUsers[userIndex].role = data.role;
                renderUsers(allUsers);
                updateStats(allUsers);
            }
        } else {
            showAlert(data.error, 'error');
        }
    } catch (error) {
        console.error('Change role error:', error);
        showAlert('Connection error. Please try again.', 'error');
    }
}

// ==================== DELETE USER ====================

function showDeleteModal(userId, userName) {
    const modal = document.createElement('div');
    modal.className = 'modal active';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>Delete User</h3>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong>${escapeHtml(userName)}</strong>?</p>
                <p style="color: #C62828; margin-top: 12px;">⚠️ This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button class="btn btn-danger" style="background: #C62828;" onclick="deleteUser(${userId})">Delete User</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });
}

async function deleteUser(userId) {
    try {
        const formData = new FormData();
        formData.append('user_id', userId);
        
        const response = await fetch('/admin/users/delete', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.message, 'success');
            closeModal();
            
            // Remove user from array
            allUsers = allUsers.filter(u => u.id !== userId);
            renderUsers(allUsers);
            updateStats(allUsers);
        } else {
            showAlert(data.error, 'error');
        }
    } catch (error) {
        console.error('Delete user error:', error);
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

function getRoleIcon(role) {
    const icons = {
        admin: '👑',
        moderator: '🛡️',
        user: '👤'
    };
    return icons[role] || '👤';
}

function formatDate(dateString) {
    const date = new Date(dateString);
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