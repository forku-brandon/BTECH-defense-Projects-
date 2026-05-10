// js/api.js
const API_BASE_URL = 'http://localhost/ssvias-mobile/backend/api/';

class API {
    static async request(endpoint, method = 'GET', data = null) {
        const url = API_BASE_URL + endpoint;
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            }
        };
        
        if (data) {
            options.body = JSON.stringify(data);
        }
        
        try {
            const response = await fetch(url);
            return await response.json();
        } catch (error) {
            console.error('API Error:', error);
            // Return mock data for demo
            return this.getMockData(endpoint, data);
        }
    }
    
    static getMockData(endpoint, data) {
        const mockResponses = {
            'verify-vehicle.php': (data) => {
                const mockVehicles = {
                    'AB123CD': { plate: 'AB123CD', vin: 'VIN123456', make: 'Toyota', model: 'Corolla', year: 2020, status: 'stolen', owner: 'John Doe', reported_date: '2024-01-15' },
                    'XY789ZZ': { plate: 'XY789ZZ', vin: 'VIN789012', make: 'Honda', model: 'Civic', year: 2021, status: 'safe', owner: 'Jane Smith' }
                };
                const vehicle = mockVehicles[data.plate] || { status: 'safe', message: 'Vehicle not found in database' };
                return { success: true, vehicle: vehicle };
            },
            'login.php': () => ({ success: true, message: 'Login successful', user: { id: 1, name: 'Test User', role: 'user' } }),
            'register.php': () => ({ success: true, message: 'Registration successful' }),
            'report-stolen.php': () => ({ success: true, message: 'Vehicle reported as stolen' }),
            'report-sighting.php': () => ({ success: true, message: 'Sighting reported successfully' })
        };
        
        return mockResponses[endpoint] ? mockResponses[endpoint](data) : { success: true, data: [] };
    }
    
    static async login(email, password) {
        return await this.request('login.php', 'POST', { email, password });
    }
    
    static async register(userData) {
        return await this.request('register.php', 'POST', userData);
    }
    
    static async verifyVehicle(plateNumber) {
        return await this.request('verify-vehicle.php', 'POST', { plate: plateNumber });
    }
    
    static async reportStolen(vehicleData) {
        return await this.request('report-stolen.php', 'POST', vehicleData);
    }
    
    static async reportSighting(sightingData) {
        return await this.request('report-sighting.php', 'POST', sightingData);
    }
    
    static async getStolenVehicles() {
        return await this.request('get-vehicles.php?status=stolen', 'GET');
    }
}

// js/app.js
let currentUser = null;

// Check authentication
function checkAuth() {
    const user = localStorage.getItem('user');
    if (user) {
        currentUser = JSON.parse(user);
        return true;
    }
    return false;
}

// Show alert message
function showAlert(message, type = 'success') {
    const alertDiv = document.getElementById('alert');
    if (alertDiv) {
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;
        alertDiv.style.display = 'block';
        setTimeout(() => {
            alertDiv.style.display = 'none';
        }, 3000);
    }
}

// Format date
function formatDate(date) {
    return new Date(date).toLocaleDateString('en-CM');
}

// Get current location
function getCurrentLocation() {
    return new Promise((resolve, reject) => {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    resolve({
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    });
                },
                (error) => {
                    reject(error);
                }
            );
        } else {
            reject(new Error('Geolocation not supported'));
        }
    });
}

// Load user dashboard
async function loadDashboard() {
    if (!checkAuth()) {
        window.location.href = '/ssvias-mobile/pages/login.html';
        return;
    }
    
    // Update user info
    document.getElementById('user-name').textContent = currentUser.name || 'User';
    
    // Load recent stolen vehicles
    const result = await API.getStolenVehicles();
    if (result.success && result.data) {
        const recentList = document.getElementById('recent-stolen');
        if (recentList) {
            recentList.innerHTML = result.data.slice(0,5).map(vehicle => `
                <div class="vehicle-item">
                    <strong>${vehicle.plate}</strong> - ${vehicle.make} ${vehicle.model}
                    <span class="badge badge-stolen">Stolen</span>
                </div>
            `).join('');
        }
    }
}

// Logout function
function logout() {
    localStorage.removeItem('user');
    window.location.href = '/ssvias-mobile/index.html';
}