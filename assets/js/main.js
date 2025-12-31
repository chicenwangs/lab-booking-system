/**
 * ============================================================================
 * SQUAD 1: MAIN JAVASCRIPT
 * ============================================================================
 * UI interactions, mobile menu, alerts, and general functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ========================================================================
    // MOBILE MENU TOGGLE
    // ========================================================================
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navLinks = document.querySelector('.nav-links');
    
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            
            // Change icon
            if (navLinks.classList.contains('active')) {
                this.textContent = '✕';
            } else {
                this.textContent = '☰';
            }
        });
        
        // Close menu when clicking a link
        const navItems = navLinks.querySelectorAll('a');
        navItems.forEach(item => {
            item.addEventListener('click', function() {
                navLinks.classList.remove('active');
                mobileMenuBtn.textContent = '☰';
            });
        });
    }
    
    // ========================================================================
    // AUTO-HIDE ALERTS
    // ========================================================================
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        // Add slide-in animation
        alert.style.animation = 'slideInDown 0.3s ease';
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            alert.style.transition = 'opacity 0.3s ease';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
        
        // Allow manual close by clicking
        alert.style.cursor = 'pointer';
        alert.addEventListener('click', function() {
            this.style.opacity = '0';
            setTimeout(() => this.remove(), 300);
        });
    });
    
    // ========================================================================
    // CONFIRM DELETE ACTIONS
    // ========================================================================
    const deleteButtons = document.querySelectorAll('[data-confirm]');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm');
            if (!confirm(message || 'Are you sure you want to delete this?')) {
                e.preventDefault();
            }
        });
    });
    
    // ========================================================================
    // TIME SLOT SELECTION (For booking pages)
    // ========================================================================
    const timeSlots = document.querySelectorAll('.time-slot');
    timeSlots.forEach(slot => {
        slot.addEventListener('click', function() {
            if (!this.classList.contains('disabled')) {
                // Remove selected from all slots
                timeSlots.forEach(s => s.classList.remove('selected'));
                
                // Add selected to clicked slot
                this.classList.add('selected');
                
                // Update hidden inputs
                const startTime = this.getAttribute('data-start');
                const endTime = this.getAttribute('data-end');
                
                const startInput = document.getElementById('start_time');
                const endInput = document.getElementById('end_time');
                
                if (startInput) startInput.value = startTime;
                if (endInput) endInput.value = endTime;
            }
        });
    });
    
    // ========================================================================
    // CALCULATE BOOKING COST
    // ========================================================================
    const labSelect = document.getElementById('lab_id');
    const dateInput = document.getElementById('booking_date');
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    
    if (labSelect && startTimeInput && endTimeInput) {
        const calculateCost = () => {
            const selectedOption = labSelect.options[labSelect.selectedIndex];
            const rate = parseFloat(selectedOption?.getAttribute('data-rate') || 0);
            const start = startTimeInput.value;
            const end = endTimeInput.value;
            
            if (start && end && rate) {
                const startMinutes = timeToMinutes(start);
                const endMinutes = timeToMinutes(end);
                
                if (endMinutes > startMinutes) {
                    const hours = (endMinutes - startMinutes) / 60;
                    const cost = (hours * rate).toFixed(2);
                    
                    const costDisplay = document.getElementById('cost-display');
                    if (costDisplay) {
                        costDisplay.textContent = `$${cost}`;
                        costDisplay.style.color = 'var(--success-color)';
                        costDisplay.style.fontWeight = 'bold';
                    }
                }
            }
        };
        
        labSelect.addEventListener('change', calculateCost);
        startTimeInput.addEventListener('change', calculateCost);
        endTimeInput.addEventListener('change', calculateCost);
    }
    
    // ========================================================================
    // FORM VALIDATION FEEDBACK
    // ========================================================================
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = 'var(--danger-color)';
                } else {
                    field.style.borderColor = 'var(--border)';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showNotification('Please fill in all required fields', 'error');
            }
        });
    });
    
    // ========================================================================
    // SMOOTH SCROLL TO ANCHOR LINKS
    // ========================================================================
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href !== '') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
    
    // ========================================================================
    // TABLE ROW ACTIONS
    // ========================================================================
    const tableRows = document.querySelectorAll('table tbody tr');
    tableRows.forEach(row => {
        // Add click-to-highlight functionality
        row.addEventListener('click', function(e) {
            // Don't highlight if clicking a button or link
            if (!e.target.closest('button, a')) {
                this.classList.toggle('highlighted');
            }
        });
    });
    
});

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Convert time string to minutes
 * @param {string} time - Time in HH:MM format
 * @returns {number} - Total minutes
 */
function timeToMinutes(time) {
    const parts = time.split(':');
    return parseInt(parts[0]) * 60 + parseInt(parts[1]);
}

/**
 * Show notification popup
 * @param {string} message - Message to display
 * @param {string} type - Type: success, error, warning, info
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.textContent = message;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    notification.style.maxWidth = '500px';
    notification.style.cursor = 'pointer';
    notification.style.animation = 'slideInDown 0.3s ease';
    
    document.body.appendChild(notification);
    
    // Auto-remove after 4 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 4000);
    
    // Remove on click
    notification.addEventListener('click', function() {
        this.style.opacity = '0';
        setTimeout(() => this.remove(), 300);
    });
}

/**
 * Add item to cart with animation (for booking system)
 * @param {number} labId - Lab ID
 * @param {string} labName - Lab name
 */
function addToCart(labId, labName) {
    const cartCount = document.querySelector('.cart-count');
    if (cartCount) {
        const current = parseInt(cartCount.textContent) || 0;
        cartCount.textContent = current + 1;
        cartCount.style.animation = 'bounce 0.5s';
        setTimeout(() => {
            cartCount.style.animation = '';
        }, 500);
    }
    showNotification(`${labName} added to cart!`, 'success');
}

/**
 * Format currency for display
 * @param {number} amount - Amount to format
 * @returns {string} - Formatted currency string
 */
function formatCurrency(amount) {
    return '$' + parseFloat(amount).toFixed(2);
}

/**
 * Debounce function for performance
 * @param {Function} func - Function to debounce
 * @param {number} wait - Wait time in milliseconds
 * @returns {Function} - Debounced function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// ============================================================================
// EXPORT FOR OTHER SCRIPTS
// ============================================================================
window.labBookingUtils = {
    showNotification,
    addToCart,
    formatCurrency,
    timeToMinutes,
    debounce
};