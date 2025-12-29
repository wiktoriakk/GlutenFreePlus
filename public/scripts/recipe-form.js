// ==================== RECIPE FORM JS ====================

// Image preview
document.addEventListener('DOMContentLoaded', () => {
    const imageInput = document.getElementById('image');
    if (imageInput) {
        imageInput.addEventListener('change', handleImagePreview);
    }
});

function handleImagePreview(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    // Validate file size (5MB)
    if (file.size > 5 * 1024 * 1024) {
        showAlert('Image too large. Maximum size is 5MB.', 'error');
        e.target.value = '';
        return;
    }
    
    // Show preview
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('preview-img').src = e.target.result;
        document.getElementById('image-preview').style.display = 'block';
    };
    reader.readAsDataURL(file);
}

function removeImage() {
    document.getElementById('image').value = '';
    document.getElementById('image-preview').style.display = 'none';
}

async function handleSubmit(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submit-btn');
    const btnText = document.getElementById('btn-text');
    const btnSpinner = document.getElementById('btn-spinner');
    
    clearErrors();
    
    const formData = new FormData(e.target);
    
    // Client-side validation
    const title = formData.get('title');
    const ingredients = formData.get('ingredients');
    const instructions = formData.get('instructions');
    const servings = formData.get('servings');
    
    let hasError = false;
    
    if (!title || title.trim().length < 3) {
        showFieldError('title', 'Title must be at least 3 characters');
        hasError = true;
    }
    
    if (!ingredients || ingredients.trim().length < 10) {
        showFieldError('ingredients', 'Please provide ingredients');
        hasError = true;
    }
    
    if (!instructions || instructions.trim().length < 20) {
        showFieldError('instructions', 'Please provide detailed instructions');
        hasError = true;
    }
    
    if (!servings || servings < 1) {
        showFieldError('servings', 'Servings must be at least 1');
        hasError = true;
    }
    
    if (hasError) return;
    
    // Disable button
    submitBtn.disabled = true;
    btnText.style.display = 'none';
    btnSpinner.style.display = 'inline-block';
    
    try {
        const response = await fetch('/recipes/store', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('Recipe created successfully!', 'success');
            
            setTimeout(() => {
                window.location.href = '/recipes';
            }, 1500);
        } else {
            if (data.errors && typeof data.errors === 'object') {
                Object.keys(data.errors).forEach(field => {
                    showFieldError(field, data.errors[field]);
                });
            } else {
                showAlert(data.error || 'Failed to create recipe', 'error');
            }
        }
    } catch (error) {
        console.error('Submit error:', error);
        showAlert('Connection error. Please try again.', 'error');
    } finally {
        submitBtn.disabled = false;
        btnText.style.display = 'inline';
        btnSpinner.style.display = 'none';
    }
}

function showAlert(message, type) {
    const alertContainer = document.getElementById('alert-container');
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
    
    alertContainer.innerHTML = '';
    alertContainer.appendChild(alert);
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
    
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