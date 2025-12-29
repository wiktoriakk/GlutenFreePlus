// ==================== AUTH.JS ====================

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
    
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegister);
    }
    
    // Password toggle functionality
    const toggleButtons = document.querySelectorAll('.password-toggle');
    toggleButtons.forEach(button => {
        button.addEventListener('click', togglePasswordVisibility);
    });
    
    // Disable HTML5 validation tooltips
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.setAttribute('novalidate', 'novalidate');
    });
});

// ==================== LOGIN ====================
async function handleLogin(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submit-btn');
    const btnText = document.getElementById('btn-text');
    const btnSpinner = document.getElementById('btn-spinner');
    
    clearErrors();
    
    const formData = new FormData(e.target);
    const email = formData.get('email');
    const password = formData.get('password');
    
    if (!email || !password) {
        showAlert('Please fill in all fields', 'error');
        return;
    }
    
    if (!isValidEmail(email)) {
        showAlert('Please enter a valid email address', 'error');
        return;
    }
    
    submitBtn.disabled = true;
    btnText.style.display = 'none';
    btnSpinner.style.display = 'inline-block';
    
    try {
        const response = await fetch('/login', {
            method: 'POST',
            body: formData
        });
        
        const text = await response.text();
        let data;
        
        try {
            data = JSON.parse(text);
        } catch {
            console.error('Invalid JSON:', text);
            throw new Error('Server error');
        }
        
        if (data.success) {
            window.location.href = data.redirect || '/dashboard';
        } else {
            showAlert(data.error || 'Invalid email or password', 'error');
        }
        
    } catch (error) {
        console.error('Login error:', error);
        showAlert('Connection error. Please try again.', 'error');
    } finally {
        submitBtn.disabled = false;
        btnText.style.display = 'inline';
        btnSpinner.style.display = 'none';
    }
}

// ==================== REGISTER ====================
async function handleRegister(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submit-btn');
    const btnText = document.getElementById('btn-text');
    const btnSpinner = document.getElementById('btn-spinner');
    
    clearErrors();
    
    const formData = new FormData(e.target);
    const name = formData.get('name');
    const email = formData.get('email');
    const password = formData.get('password');
    const confirmPassword = formData.get('confirm_password');
    
    let hasError = false;
    
    if (!name || name.trim().length < 2) {
        showFieldError('name', 'Name must be at least 2 characters');
        hasError = true;
    }
    
    if (!email || !isValidEmail(email)) {
        showFieldError('email', 'Please enter a valid email address');
        hasError = true;
    }
    
    if (!password || password.length < 8) {
        showFieldError('password', 'Password must be at least 8 characters');
        hasError = true;
    } else if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(password)) {
        showFieldError('password', 'Password must contain uppercase, lowercase, and number');
        hasError = true;
    }
    
    if (password !== confirmPassword) {
        showFieldError('confirm_password', 'Passwords do not match');
        hasError = true;
    }
    
    if (hasError) return;
    
    submitBtn.disabled = true;
    btnText.style.display = 'none';
    btnSpinner.style.display = 'inline-block';
    
    try {
        const response = await fetch('/register', {
            method: 'POST',
            body: formData
        });
        
        const text = await response.text();
        let data;
        
        try {
            data = JSON.parse(text);
        } catch {
            console.error('Invalid JSON:', text);
            throw new Error('Server error');
        }
        
        if (data.success) {
            showAlert('Registration successful! Redirecting...', 'success');
            setTimeout(() => {
                window.location.href = data.redirect || '/login';
            }, 1500);
        } else {
            if (data.errors && typeof data.errors === 'object') {
                Object.keys(data.errors).forEach(field => {
                    showFieldError(field, data.errors[field]);
                });
            } else {
                showAlert(data.error || 'Registration failed', 'error');
            }
        }
        
    } catch (error) {
        console.error('Register error:', error);
        showAlert('Connection error. Please try again.', 'error');
    } finally {
        submitBtn.disabled = false;
        btnText.style.display = 'inline';
        btnSpinner.style.display = 'none';
    }
}

// ==================== HELPERS ====================

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function showAlert(message, type) {
    const alertContainer = document.getElementById('alert-container');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    alertContainer.innerHTML = '';
    alertContainer.appendChild(alert);
    
    if (type !== 'success') {
        setTimeout(() => alert.remove(), 5000);
    }
}

function showFieldError(fieldName, message) {
    const errorSpan = document.getElementById(`${fieldName}-error`);
    const inputField = document.getElementById(fieldName);
    
    if (errorSpan) {
        errorSpan.textContent = message;
        errorSpan.style.display = 'block';
    }
    
    if (inputField) {
        inputField.classList.add('input-error');
    }
}

function clearErrors() {
    const alertContainer = document.getElementById('alert-container');
    if (alertContainer) alertContainer.innerHTML = '';
    
    document.querySelectorAll('.form-error').forEach(span => {
        span.textContent = '';
        span.style.display = 'none';
    });
    
    document.querySelectorAll('.input-error').forEach(input => {
        input.classList.remove('input-error');
    });
}

function togglePasswordVisibility(e) {
    const button = e.currentTarget;
    const input = button.previousElementSibling;
    const eyeIcon = button.querySelector('#eye-icon');
    const eyeOffIcon = button.querySelector('#eye-off-icon');
    
    if (input.type === 'password') {
        input.type = 'text';
        eyeIcon.style.display = 'none';
        eyeOffIcon.style.display = 'block';
    } else {
        input.type = 'password';
        eyeIcon.style.display = 'block';
        eyeOffIcon.style.display = 'none';
    }
}