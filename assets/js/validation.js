/**
 * ============================================================================
 * SQUAD 1: FORM VALIDATION JAVASCRIPT
 * ============================================================================
 * Client-side form validation to improve user experience
 * Note: Always validate on server-side as well for security
 */

// ============================================================================
// VALIDATION FUNCTIONS
// ============================================================================

/**
 * Validate email format
 * @param {string} email - Email to validate
 * @returns {boolean} - True if valid
 */
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Validate password strength
 * @param {string} password - Password to validate
 * @returns {Array} - Array of error messages (empty if valid)
 */
function validatePassword(password) {
    const errors = [];
    
    if (password.length < 8) {
        errors.push('Password must be at least 8 characters long');
    }
    if (!/[A-Z]/.test(password)) {
        errors.push('Password must contain at least one uppercase letter');
    }
    if (!/[a-z]/.test(password)) {
        errors.push('Password must contain at least one lowercase letter');
    }
    if (!/[0-9]/.test(password)) {
        errors.push('Password must contain at least one number');
    }
    
    return errors;
}

/**
 * Get password strength score
 * @param {string} password - Password to check
 * @returns {number} - Strength score (0-4)
 */
function getPasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    
    return strength;
}

// ============================================================================
// REAL-TIME VALIDATION
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    
    // ========================================================================
    // PASSWORD STRENGTH INDICATOR
    // ========================================================================
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const passwordStrength = document.getElementById('password-strength');
    
    if (passwordInput && passwordStrength) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            
            if (password.length === 0) {
                passwordStrength.textContent = '';
                return;
            }
            
            const errors = validatePassword(password);
            const strength = getPasswordStrength(password);
            
            // Display strength
            if (errors.length === 0) {
                passwordStrength.textContent = '✓ Strong password';
                passwordStrength.style.color = '#10b981';
                this.style.borderColor = '#10b981';
            } else if (strength >= 2) {
                passwordStrength.textContent = '⚠ Medium strength';
                passwordStrength.style.color = '#f59e0b';
                this.style.borderColor = '#f59e0b';
            } else {
                passwordStrength.textContent = '✗ Weak password';
                passwordStrength.style.color = '#ef4444';
                this.style.borderColor = '#ef4444';
            }
        });
    }
    
    // ========================================================================
    // CONFIRM PASSWORD MATCHING
    // ========================================================================
    if (confirmPasswordInput && passwordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            const password = passwordInput.value;
            const confirmPassword = this.value;
            
            if (confirmPassword.length === 0) {
                this.style.borderColor = 'var(--border)';
                this.setCustomValidity('');
                return;
            }
            
            if (password !== confirmPassword) {
                this.style.borderColor = '#ef4444';
                this.setCustomValidity('Passwords do not match');
            } else {
                this.style.borderColor = '#10b981';
                this.setCustomValidity('');
            }
        });
    }
    
    // ========================================================================
    // EMAIL VALIDATION
    // ========================================================================
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const email = this.value.trim();
            
            if (email.length === 0) {
                this.style.borderColor = 'var(--border)';
                return;
            }
            
            if (validateEmail(email)) {
                this.style.borderColor = '#10b981';
            } else {
                this.style.borderColor = '#ef4444';
                showFieldError(this, 'Please enter a valid email address');
            }
        });
        
        // Remove error styling on focus
        input.addEventListener('focus', function() {
            this.style.borderColor = 'var(--primary-color)';
            removeFieldError(this);
        });
    });
    
    // ========================================================================
    // REGISTRATION FORM VALIDATION
    // ========================================================================
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const fullName = document.getElementById('full_name')?.value.trim();
            const email = document.getElementById('email')?.value.trim();
            const password = document.getElementById('password')?.value;
            const confirmPassword = document.getElementById('confirm_password')?.value;
            
            let isValid = true;
            let errorMessage = '';
            
            // Validate full name
            if (!fullName || fullName.length < 3) {
                isValid = false;
                errorMessage += '• Full name must be at least 3 characters\n';
            }
            
            // Validate email
            if (!validateEmail(email)) {
                isValid = false;
                errorMessage += '• Please enter a valid email address\n';
            }
            
            // Validate password
            const passwordErrors = validatePassword(password);
            if (passwordErrors.length > 0) {
                isValid = false;
                errorMessage += '• ' + passwordErrors.join('\n• ') + '\n';
            }
            
            // Validate password match
            if (password !== confirmPassword) {
                isValid = false;
                errorMessage += '• Passwords do not match\n';
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fix the following errors:\n\n' + errorMessage);
            }
        });
    }
    
    // ========================================================================
    // LOGIN FORM VALIDATION
    // ========================================================================
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const email = document.getElementById('email')?.value.trim();
            const password = document.getElementById('password')?.value;
            
            let isValid = true;
            let errorMessage = '';
            
            if (!validateEmail(email)) {
                isValid = false;
                errorMessage += '• Please enter a valid email address\n';
            }
            
            if (password.length < 6) {
                isValid = false;
                errorMessage += '• Password is too short\n';
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fix the following errors:\n\n' + errorMessage);
            }
        });
    }
    
    // ========================================================================
    // BOOKING FORM VALIDATION
    // ========================================================================
    const bookingForm = document.getElementById('booking-form');
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
            const labId = document.getElementById('lab_id')?.value;
            const bookingDate = document.getElementById('booking_date')?.value;
            const startTime = document.getElementById('start_time')?.value;
            const endTime = document.getElementById('end_time')?.value;
            
            let isValid = true;
            let errorMessage = '';
            
            // Validate lab selection
            if (!labId) {
                isValid = false;
                errorMessage += '• Please select a laboratory\n';
            }
            
            // Validate date
            if (!bookingDate) {
                isValid = false;
                errorMessage += '• Please select a booking date\n';
            } else {
                const selectedDate = new Date(bookingDate);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                if (selectedDate < today) {
                    isValid = false;
                    errorMessage += '• Cannot book for past dates\n';
                }
            }
            
            // Validate times
            if (!startTime || !endTime) {
                isValid = false;
                errorMessage += '• Please select both start and end times\n';
            } else {
                const start = new Date(`2000-01-01 ${startTime}`);
                const end = new Date(`2000-01-01 ${endTime}`);
                
                if (end <= start) {
                    isValid = false;
                    errorMessage += '• End time must be after start time\n';
                }
                
                // Check minimum duration (e.g., 1 hour)
                const diffHours = (end - start) / (1000 * 60 * 60);
                if (diffHours < 1) {
                    isValid = false;
                    errorMessage += '• Minimum booking duration is 1 hour\n';
                }
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fix the following errors:\n\n' + errorMessage);
            }
        });
    }
    
    // ========================================================================
    // DATE INPUT VALIDATION
    // ========================================================================
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        input.setAttribute('min', today);
        
        input.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const todayDate = new Date(today);
            
            if (selectedDate < todayDate) {
                this.style.borderColor = '#ef4444';
                alert('Cannot select past dates');
                this.value = '';
            } else {
                this.style.borderColor = '#10b981';
            }
        });
    });
    
    // ========================================================================
    // REQUIRED FIELD INDICATORS
    // ========================================================================
    const requiredFields = document.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        // Add visual indicator for required fields
        const label = document.querySelector(`label[for="${field.id}"]`);
        if (label && !label.textContent.includes('*')) {
            // Already has asterisk in HTML
        }
        
        // Validate on blur
        field.addEventListener('blur', function() {
            if (this.value.trim() === '') {
                this.style.borderColor = '#ef4444';
            }
        });
        
        // Remove error on input
        field.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                this.style.borderColor = 'var(--border)';
            }
        });
    });
    
});

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Show error message under field
 * @param {HTMLElement} field - Input field
 * @param {string} message - Error message
 */
function showFieldError(field, message) {
    removeFieldError(field);
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'form-error';
    errorDiv.textContent = message;
    errorDiv.style.color = '#ef4444';
    errorDiv.style.fontSize = '0.875rem';
    errorDiv.style.marginTop = '0.25rem';
    
    field.parentNode.appendChild(errorDiv);
}

/**
 * Remove error message from field
 * @param {HTMLElement} field - Input field
 */
function removeFieldError(field) {
    const existingError = field.parentNode.querySelector('.form-error');
    if (existingError) {
        existingError.remove();
    }
}

/**
 * Validate all form fields
 * @param {HTMLFormElement} form - Form to validate
 * @returns {boolean} - True if all valid
 */
function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.style.borderColor = '#ef4444';
        }
    });
    
    return isValid;
}

// ============================================================================
// EXPORT FOR OTHER SCRIPTS
// ============================================================================
window.formValidation = {
    validateEmail,
    validatePassword,
    getPasswordStrength,
    validateForm,
    showFieldError,
    removeFieldError
};