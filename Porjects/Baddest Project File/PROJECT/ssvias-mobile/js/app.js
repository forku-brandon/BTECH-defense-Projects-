// Add to js/app.js

// Offline Detection and Handling
window.addEventListener('load', () => {
    if (!navigator.onLine) {
        showAlert('You are offline. Some features may be limited.', 'warning');
    }
});

// Auto-save form data
function autoSaveForm(formId, storageKey) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const inputs = form.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('change', () => {
            const formData = {};
            inputs.forEach(inp => {
                if (inp.id) {
                    formData[inp.id] = inp.value;
                }
            });
            localStorage.setItem(storageKey, JSON.stringify(formData));
        });
    });
    
    // Load saved data
    const saved = localStorage.getItem(storageKey);
    if (saved) {
        const formData = JSON.parse(saved);
        Object.keys(formData).forEach(key => {
            const input = document.getElementById(key);
            if (input) input.value = formData[key];
        });
    }
}

// Clear auto-saved data
function clearAutoSave(storageKey) {
    localStorage.removeItem(storageKey);
}

// Share functionality
async function shareContent(title, text, url) {
    if (navigator.share) {
        try {
            await navigator.share({ title, text, url });
            return true;
        } catch (error) {
            return false;
        }
    } else {
        // Fallback
        await navigator.clipboard.writeText(`${title}\n${text}\n${url}`);
        showAlert('Content copied to clipboard!', 'success');
        return false;
    }
}

// Rate limiting for API calls
class RateLimiter {
    constructor(limit = 10, interval = 60000) {
        this.limit = limit;
        this.interval = interval;
        this.calls = [];
    }
    
    canMakeCall() {
        const now = Date.now();
        this.calls = this.calls.filter(time => now - time < this.interval);
        
        if (this.calls.length < this.limit) {
            this.calls.push(now);
            return true;
        }
        return false;
    }
}

const apiRateLimiter = new RateLimiter(20, 60000);

// Wrap API calls with rate limiting
const originalRequest = API.request;
API.request = async function(endpoint, method, data) {
    if (!apiRateLimiter.canMakeCall()) {
        showAlert('Too many requests. Please wait a moment.', 'warning');
        return { success: false, message: 'Rate limit exceeded' };
    }
    return originalRequest(endpoint, method, data);
};

// Session timeout
let sessionTimeout;
const SESSION_DURATION = 30 * 60 * 1000; // 30 minutes

function resetSessionTimeout() {
    if (sessionTimeout) clearTimeout(sessionTimeout);
    sessionTimeout = setTimeout(() => {
        if (localStorage.getItem('user')) {
            showAlert('Session expired. Please login again.', 'warning');
            logout();
        }
    }, SESSION_DURATION);
}

// Track user activity
['click', 'keypress', 'scroll', 'touchstart'].forEach(event => {
    document.addEventListener(event, resetSessionTimeout);
});

// Initialize session tracking
if (localStorage.getItem('user')) {
    resetSessionTimeout();
}

// Debug mode (disable in production)
const DEBUG = true;
function debugLog(...args) {
    if (DEBUG) {
        console.log('[SSVIAS Debug]:', ...args);
    }
}

// Performance monitoring
function measurePerformance(name, callback) {
    const start = performance.now();
    const result = callback();
    const end = performance.now();
    debugLog(`${name} took ${(end - start).toFixed(2)}ms`);
    return result;
}