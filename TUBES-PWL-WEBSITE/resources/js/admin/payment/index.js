/* ============================================
   PAYMENT DASHBOARD - JAVASCRIPT
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {
    initializePaymentFilters();
    initializeTableInteractions();
});

/**
 * Initialize payment filter interactions
 */
function initializePaymentFilters() {
    const filterTabs = document.querySelectorAll('.filter-tab');
    
    filterTabs.forEach(tab => {
        if (tab.tagName === 'A') {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                const href = this.getAttribute('href');
                const dashboard = document.querySelector('.payment-dashboard');
                if (dashboard) {
                    dashboard.style.transition = 'opacity 0.15s ease-out';
                    dashboard.style.opacity = '0.5';
                }
                setTimeout(() => {
                    window.location.href = href;
                }, 100);
            });
        }
    });
}

/**
 * Initialize table row interactions
 */
function initializeTableInteractions() {
    const tableRows = document.querySelectorAll('.table-row');
    
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.cursor = 'pointer';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.cursor = 'default';
        });
    });
}

/**
 * Format currency display
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
}

/**
 * Format date display
 */
function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

/**
 * Handle filter status change
 */
function changePaymentFilter(status) {
    const form = document.createElement('form');
    form.method = 'GET';
    form.action = window.location.pathname;
    
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'status';
    input.value = status;
    
    form.appendChild(input);
    document.body.appendChild(form);
    
    // Add fade-out effect
    const dashboard = document.querySelector('.payment-dashboard');
    if (dashboard) {
        dashboard.style.transition = 'opacity 0.3s ease-out';
        dashboard.style.opacity = '0.8';
    }
    
    form.submit();
}

// Export functions for inline use if needed
window.paymentDashboard = {
    formatCurrency,
    formatDate,
    changePaymentFilter
};
