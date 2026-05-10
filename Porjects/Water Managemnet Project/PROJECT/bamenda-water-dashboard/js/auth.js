// ============================================
// Authentication & Role Management
// ============================================

// User roles
const ROLES = {
    ANONYMOUS: 'anonymous',
    REGISTERED: 'registered',
    HEALTH_WORKER: 'health_worker',
    DATA_ENTRY: 'data_entry',
    ADMIN: 'admin'
};

// Role display names
const ROLE_NAMES = {
    [ROLES.HEALTH_WORKER]: 'Health Worker',
    [ROLES.DATA_ENTRY]: 'Water Quality Officer',
    [ROLES.ADMIN]: 'Administrator',
    [ROLES.REGISTERED]: 'Community Member'
};

// Dashboard URLs for each role (with folder structure)
const DASHBOARD_URLS = {
    [ROLES.ADMIN]: 'admin/dashboard.html',
    [ROLES.DATA_ENTRY]: 'water-officer/dashboard.html',
    [ROLES.HEALTH_WORKER]: 'health-worker/dashboard.html',
    [ROLES.REGISTERED]: 'dashboard.html'
};

// Permission matrix
const PERMISSIONS = {
    [ROLES.ANONYMOUS]: [
        'view_map', 'view_sources', 'view_source_details', 'submit_report'
    ],
    [ROLES.REGISTERED]: [
        'view_map', 'view_sources', 'view_source_details', 'submit_report',
        'view_own_reports', 'view_dashboard'
    ],
    [ROLES.HEALTH_WORKER]: [
        'view_map', 'view_sources', 'view_source_details', 'submit_report',
        'view_own_reports', 'view_dashboard', 'view_health_dashboard',
        'submit_disease_data', 'view_disease_trends', 'generate_health_reports'
    ],
    [ROLES.DATA_ENTRY]: [
        'view_map', 'view_sources', 'view_source_details', 'submit_report',
        'view_own_reports', 'view_dashboard', 'add_test_results',
        'edit_sources', 'view_all_reports', 'manage_water_sources',
        'generate_quality_reports', 'view_test_history'
    ],
    [ROLES.ADMIN]: [
        'view_map', 'view_sources', 'view_source_details', 'submit_report',
        'view_own_reports', 'view_dashboard', 'add_test_results',
        'edit_sources', 'view_all_reports', 'moderate_reports',
        'manage_users', 'system_settings', 'view_analytics',
        'backup_data', 'view_logs'
    ]
};

// Current user state
let currentUser = null;
let currentRole = ROLES.ANONYMOUS;

// Check authentication
function checkAuth() {
    const token = localStorage.getItem('auth_token');
    const userData = localStorage.getItem('current_user');
    
    if (token && userData) {
        try {
            currentUser = JSON.parse(userData);
            currentRole = currentUser.role;
            updateUIForRole();
            return true;
        } catch (e) {
            console.error('Error parsing user data:', e);
            logout();
            return false;
        }
    }
    currentUser = null;
    currentRole = ROLES.ANONYMOUS;
    updateUIForRole();
    return false;
}

// Login function with role-based redirect
async function login(email, password) {
    try {
        const result = await API.post('/auth/login', { email, password });
        
        if (result.success) {
            const token = 'demo_token_' + Date.now();
            localStorage.setItem('auth_token', token);
            localStorage.setItem('current_user', JSON.stringify(result.user));
            currentUser = result.user;
            currentRole = result.user.role;
            updateUIForRole();
            
            // Get the redirect URL based on role
            const redirectUrl = DASHBOARD_URLS[result.user.role];
            
            return { 
                success: true, 
                user: result.user, 
                redirectUrl: redirectUrl 
            };
        } else {
            return { success: false, message: result.message || 'Invalid credentials' };
        }
    } catch (error) {
        console.error('Login error:', error);
        return { success: false, message: 'Network error. Please try again.' };
    }
}

// Logout function
function logout() {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('current_user');
    currentUser = null;
    currentRole = ROLES.ANONYMOUS;
    updateUIForRole();
    
    // Redirect to home if on protected page
    const protectedPaths = ['/admin/', '/water-officer/', '/health-worker/'];
    const isProtected = protectedPaths.some(path => window.location.pathname.includes(path));
    if (isProtected && !window.location.pathname.includes('login.html')) {
        window.location.href = '../index.html';
    }
}

// Check permission
function hasPermission(permission) {
    const userPermissions = PERMISSIONS[currentRole] || [];
    return userPermissions.includes(permission);
}

// Require permission (with redirect)
function requirePermission(permission, redirectUrl = '/index.html') {
    if (!hasPermission(permission)) {
        if (redirectUrl) {
            window.location.href = redirectUrl;
        }
        return false;
    }
    return true;
}

// Get role display name
function getRoleDisplayName(role) {
    return ROLE_NAMES[role] || role;
}

// Update UI based on role
function updateUIForRole() {
    const authButtons = document.getElementById('auth-buttons');
    const userMenuContainer = document.getElementById('user-menu-container');
    
    if (!authButtons || !userMenuContainer) return;
    
    if (currentUser) {
        authButtons.style.display = 'none';
        userMenuContainer.style.display = 'block';
        
        // Get correct dashboard path based on role
        let dashboardPath = DASHBOARD_URLS[currentUser.role];
        
        userMenuContainer.innerHTML = `
            <div class="user-menu">
                <div class="user-avatar">${currentUser.name.charAt(0)}</div>
                <div>
                    <div class="user-name">${escapeHtml(currentUser.name)}</div>
                    <div class="role-badge role-${currentUser.role}" style="font-size: 0.7rem;">${getRoleDisplayName(currentUser.role)}</div>
                </div>
                <div class="dropdown-menu">
                    <a href="#" onclick="showUserProfile()">👤 Profile</a>
                    <a href="${dashboardPath}">📊 My Dashboard</a>
                    ${currentUser.role === 'admin' ? '<a href="../admin/dashboard.html">⚙️ Admin Panel</a>' : ''}
                    ${currentUser.role === 'data_entry' ? '<a href="../water-officer/dashboard.html">💧 Water Management</a>' : ''}
                    ${currentUser.role === 'health_worker' ? '<a href="../health-worker/dashboard.html">🏥 Health Management</a>' : ''}
                    <a href="#" onclick="logout()">🚪 Logout</a>
                </div>
            </div>
        `;
    } else {
        authButtons.style.display = 'flex';
        userMenuContainer.style.display = 'none';
    }
}

// Show user profile modal
function showUserProfile() {
    const modal = document.getElementById('profile-modal');
    if (modal && currentUser) {
        document.getElementById('profile-name').textContent = currentUser.name;
        document.getElementById('profile-email').textContent = currentUser.email;
        document.getElementById('profile-role').textContent = getRoleDisplayName(currentUser.role);
        document.getElementById('profile-joined').textContent = currentUser.created_at || '2024-01-01';
        modal.classList.add('active');
    }
}

// Close modal
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.remove('active');
}

// Make functions global
window.checkAuth = checkAuth;
window.login = login;
window.logout = logout;
window.hasPermission = hasPermission;
window.requirePermission = requirePermission;
window.showUserProfile = showUserProfile;
window.closeModal = closeModal;
window.getRoleDisplayName = getRoleDisplayName;
window.DASHBOARD_URLS = DASHBOARD_URLS;

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    checkAuth();
    
    // Add profile modal if not exists
    if (!document.getElementById('profile-modal')) {
        const modalHtml = `
            <div id="profile-modal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>User Profile</h3>
                        <button class="close-btn" onclick="closeModal('profile-modal')">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Name:</strong> <span id="profile-name"></span></p>
                        <p><strong>Email:</strong> <span id="profile-email"></span></p>
                        <p><strong>Role:</strong> <span id="profile-role"></span></p>
                        <p><strong>Joined:</strong> <span id="profile-joined"></span></p>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }
});