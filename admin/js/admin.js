/**
 * Admin JavaScript Utilities
 * 
 * Common functions used across admin pages
 */

// Format price for display
function formatPrice(price) {
    return 'Rs. ' + parseFloat(price).toFixed(2);
}

// Format date/time for display
function formatDateTime(datetime) {
    const date = new Date(datetime);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Show notification
function showNotification(message, type = 'info') {
    // Simple alert for now - can be enhanced with toast notifications
    alert(message);
}

// Confirm action
function confirmAction(message) {
    return confirm(message);
}

// Handle API errors
function handleApiError(error) {
    console.error('API Error:', error);
    showNotification('An error occurred. Please try again.', 'error');
}

// Check if user is authenticated
function checkAuth() {
    // This will be handled by PHP session checks
    // JavaScript can add additional client-side checks if needed
}

// Auto-logout after inactivity (optional)
let inactivityTimer;
function resetInactivityTimer() {
    clearTimeout(inactivityTimer);
    // Set to 30 minutes of inactivity
    inactivityTimer = setTimeout(() => {
        alert('Session expired due to inactivity');
        window.location.href = 'logout.php';
    }, 30 * 60 * 1000);
}

// Track user activity
document.addEventListener('mousemove', resetInactivityTimer);
document.addEventListener('keypress', resetInactivityTimer);
resetInactivityTimer();
