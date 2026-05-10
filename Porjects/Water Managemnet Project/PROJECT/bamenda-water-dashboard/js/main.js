// ============================================
// Main JavaScript - Helper Functions
// ============================================

// Show loading spinner
function showLoading() {
    let spinner = document.getElementById('loading-spinner');
    if (!spinner) {
        spinner = document.createElement('div');
        spinner.id = 'loading-spinner';
        spinner.className = 'spinner-overlay';
        spinner.innerHTML = '<div class="spinner"></div>';
        document.body.appendChild(spinner);
    }
    spinner.style.display = 'flex';
}

function hideLoading() {
    const spinner = document.getElementById('loading-spinner');
    if (spinner) {
        spinner.style.display = 'none';
    }
}

// Show alert message
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = `
        <span>${type === 'success' ? '✅' : type === 'danger' ? '❌' : type === 'warning' ? '⚠️' : 'ℹ️'}</span>
        <span>${message}</span>
        <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
    `;
    
    // Add to container or body
    const container = document.querySelector('.container');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
    } else {
        document.body.insertBefore(alertDiv, document.body.firstChild);
    }
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentElement) alertDiv.remove();
    }, 5000);
}

// Format date
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

// Format datetime
function formatDateTime(dateString) {
    if (!dateString) return 'N/A';
    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

// Get status color
function getStatusColor(status) {
    switch(status) {
        case 'safe': return '#4caf50';
        case 'caution': return '#ffc107';
        case 'unsafe': return '#f44336';
        case 'no-data': return '#9e9e9e';
        default: return '#9e9e9e';
    }
}

// Get status text
function getStatusText(status) {
    switch(status) {
        case 'safe': return 'Safe';
        case 'caution': return 'Caution';
        case 'unsafe': return 'Unsafe';
        case 'no-data': return 'No Data';
        default: return 'Unknown';
    }
}

// Get observation type text
function getObservationTypeText(type) {
    const types = {
        'clear': 'Clear Water',
        'cloudy': 'Cloudy Water',
        'bad_smell': 'Bad Smell',
        'bad_taste': 'Bad Taste',
        'dumping': 'Waste Dumping',
        'other': 'Other Issue'
    };
    return types[type] || type;
}

// Escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Capitalize first letter
function capitalizeFirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// Truncate text
function truncateText(text, maxLength = 100) {
    if (!text) return '';
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
}

// Make global
window.showLoading = showLoading;
window.hideLoading = hideLoading;
window.showAlert = showAlert;
window.formatDate = formatDate;
window.formatDateTime = formatDateTime;
window.getStatusColor = getStatusColor;
window.getStatusText = getStatusText;
window.getObservationTypeText = getObservationTypeText;
window.escapeHtml = escapeHtml;
window.capitalizeFirst = capitalizeFirst;
window.truncateText = truncateText;