// ==================== AUTH SCRIPTS ====================

document.addEventListener('DOMContentLoaded', function() {
    initPasswordToggle();
    initLoginForm();
    initRegisterForm();
});

// ==================== PASSWORD TOGGLE ====================

function initPasswordToggle() {
    const toggleBtn = document.getElementById('toggle-password');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eye-icon');
    const eyeOffIcon = document.getElementById('eye-off-icon');

    if (toggleBtn && passwordInput) {
        toggleBtn.addEventListener('click', function() {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            
            if (eyeIcon && eyeOffIcon) {
                eyeIcon.style.display = isPassword ? 'none' : 'block';
                eyeOffIcon.style.display = isPassword ? 'block' : 'none';
            }
        });
    }
}

// ==================== LOGIN FORM ====================

function initLoginForm() {
    const form = document.getElementById('login-form');
    if (!form) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        clearErrors();

        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;

        // Client-side validation
        let hasErrors = false;

        if (!email) {
            showFieldError('email', 'Email is required');
            hasErrors = true;
        } else if (!isValidEmail(email)) {
            showFieldError('email', 'Invalid email format');
            hasErrors = true;
        }

        if (!password) {
            showFieldError('password', 'Password is required');
            hasErrors = true;
        }

        if (hasErrors) return;

        // Submit form
        setLoading(true);

        try {
            const response = await fetch('/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email, password })
            });

            const data = await response.json();

            if (data.success) {
                showAlert('Login successful! Redirecting...', 'success');
                setTimeout(() => {
                    window.location.href = data.redirect || '/dashboard';
                }, 500);
            } else {
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        showFieldError(field, data.errors[field]);
                    });
                } else {
                    showAlert('Login failed. Please try again.', 'error');
                }
            }
        } catch (error) {
            console.error('Login error:', error);
            showAlert('An error occurred. Please try again.', 'error');
        } finally {
            setLoading(false);
        }
    });
}

// ==================== REGISTER FORM ====================

function initRegisterForm() {
    const form = document.getElementById('register-form');
    if (!form) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        clearErrors();

        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const userType = document.getElementById('user_type').value;
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        // Client-side validation
        let hasErrors = false;

        if (!name) {
            showFieldError('name', 'Name is required');
            hasErrors = true;
        } else if (name.length < 2) {
            showFieldError('name', 'Name must be at least 2 characters');
            hasErrors = true;
        }

        if (!email) {
            showFieldError('email', 'Email is required');
            hasErrors = true;
        } else if (!isValidEmail(email)) {
            showFieldError('email', 'Invalid email format');
            hasErrors = true;
        }

        if (!password) {
            showFieldError('password', 'Password is required');
            hasErrors = true;
        } else if (password.length < 6) {
            showFieldError('password', 'Password must be at least 6 characters');
            hasErrors = true;
        }

        if (password !== confirmPassword) {
            showFieldError('confirm_password', 'Passwords do not match');
            hasErrors = true;
        }

        if (hasErrors) return;

        // Submit form
        setLoading(true);

        try {
            const response = await fetch('/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    name, 
                    email, 
                    password, 
                    confirm_password: confirmPassword,
                    user_type: userType || null
                })
            });

            const data = await response.json();

            if (data.success) {
                showAlert('Account created! Redirecting...', 'success');
                setTimeout(() => {
                    window.location.href = data.redirect || '/dashboard';
                }, 500);
            } else {
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        showFieldError(field, data.errors[field]);
                    });
                } else {
                    showAlert('Registration failed. Please try again.', 'error');
                }
            }
        } catch (error) {
            console.error('Register error:', error);
            showAlert('An error occurred. Please try again.', 'error');
        } finally {
            setLoading(false);
        }
    });
}

// ==================== HELPER FUNCTIONS ====================

function isValidEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

function showFieldError(field, message) {
    const errorElement = document.getElementById(`${field}-error`);
    const inputElement = document.getElementById(field);
    
    if (errorElement) {
        errorElement.textContent = message;
    }
    if (inputElement) {
        inputElement.classList.add('error');
    }
}

function clearErrors() {
    document.querySelectorAll('.form-error').forEach(el => el.textContent = '');
    document.querySelectorAll('.form-input.error').forEach(el => el.classList.remove('error'));
    
    const alertContainer = document.getElementById('alert-container');
    if (alertContainer) {
        alertContainer.innerHTML = '';
    }
}

function showAlert(message, type = 'error') {
    const alertContainer = document.getElementById('alert-container');
    if (alertContainer) {
        alertContainer.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
    }
}

function setLoading(isLoading) {
    const submitBtn = document.getElementById('submit-btn');
    const btnText = document.getElementById('btn-text');
    const btnSpinner = document.getElementById('btn-spinner');

    if (submitBtn) {
        submitBtn.disabled = isLoading;
    }
    if (btnText) {
        btnText.style.display = isLoading ? 'none' : 'inline';
    }
    if (btnSpinner) {
        btnSpinner.style.display = isLoading ? 'inline-block' : 'none';
    }
}