// js/utils.js
// Additional utility functions for the application

// Generate unique ID
function generateId() {
    return Date.now() + '-' + Math.random().toString(36).substr(2, 9);
}

// Validate Cameroon phone number
function validateCameroonPhone(phone) {
    const phoneRegex = /^(6|2)[0-9]{8}$/;
    return phoneRegex.test(phone);
}

// Get user's location with address
async function getLocationAddress(lat, lng) {
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`);
        const data = await response.json();
        return data.display_name || `${lat}, ${lng}`;
    } catch (error) {
        return `${lat}, ${lng}`;
    }
}

// Share vehicle alert
function shareAlert(plateNumber, status) {
    const shareData = {
        title: 'SSVIAS Vehicle Alert',
        text: `Vehicle ${plateNumber} is reported as ${status.toUpperCase()}! Please be cautious.`,
        url: window.location.href
    };
    
    if (navigator.share) {
        navigator.share(shareData);
    } else {
        navigator.clipboard.writeText(shareData.text);
        showAlert('Alert copied to clipboard', 'success');
    }
}

// Offline data sync
class OfflineSync {
    static async saveOffline(key, data) {
        const offlineData = JSON.parse(localStorage.getItem('offlineData') || '{}');
        offlineData[key] = {
            data: data,
            timestamp: new Date().toISOString(),
            synced: false
        };
        localStorage.setItem('offlineData', JSON.stringify(offlineData));
    }
    
    static async syncOfflineData() {
        const offlineData = JSON.parse(localStorage.getItem('offlineData') || '{}');
        for (const [key, value] of Object.entries(offlineData)) {
            if (!value.synced && navigator.onLine) {
                // Attempt to sync
                const result = await API.request('sync-data.php', 'POST', value.data);
                if (result.success) {
                    value.synced = true;
                    offlineData[key] = value;
                }
            }
        }
        localStorage.setItem('offlineData', JSON.stringify(offlineData));
    }
}

// Police emergency contacts
const emergencyContacts = {
    police: '117',
    gendarmerie: '1500',
    ambulance: '119',
    fire: '118'
};

// Display emergency contacts
function showEmergencyContacts() {
    const contacts = Object.entries(emergencyContacts)
        .map(([service, number]) => `${service.toUpperCase()}: ${number}`)
        .join('\n');
    alert('Emergency Contacts:\n' + contacts);
}

// Initialize service worker for PWA support
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js').then(reg => {
        console.log('Service Worker registered');
    });
}

// Check internet connection
window.addEventListener('online', () => {
    showAlert('Back online! Syncing data...', 'success');
    OfflineSync.syncOfflineData();
});

window.addEventListener('offline', () => {
    showAlert('You are offline. Data will be saved locally.', 'warning');
});