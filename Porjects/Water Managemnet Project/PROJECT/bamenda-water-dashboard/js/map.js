// ============================================
// Bamenda Water & Health Dashboard
// Map Functions using Leaflet.js
// ============================================

// Use a different variable name to avoid conflict
let mainMap = null;
let waterMarkers = [];
let healthMarkers = [];
let currentWaterSources = [];

// Mankon, Bamenda coordinates
const MANKON_CENTER = [5.9667, 10.1500];
const DEFAULT_ZOOM = 14;

// Initialize Map
function initMap() {
    // Check if Leaflet is loaded
    if (typeof L === 'undefined') {
        console.error('Leaflet library not loaded');
        return;
    }
    
    // Check if map already exists
    if (mainMap !== null) {
        console.log('Map already initialized');
        return;
    }
    
    // Create map instance
    mainMap = L.map('map').setView(MANKON_CENTER, DEFAULT_ZOOM);
    
    // Add OpenStreetMap tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(mainMap);
    
    // Load water sources
    loadWaterSources();
    
    // Load health facilities
    loadHealthFacilities();
    
    // Add scale bar
    L.control.scale().addTo(mainMap);
    
    console.log('Map initialized successfully');
}

// Load Water Sources
async function loadWaterSources() {
    try {
        showLoading();
        const result = await API.get('/sources');
        
        if (result.success && result.data) {
            currentWaterSources = result.data;
            addWaterMarkers(currentWaterSources);
            console.log('Water sources loaded:', currentWaterSources.length);
        } else {
            console.error('Failed to load water sources:', result);
            showAlert('Error loading water sources. Please refresh the page.', 'danger');
        }
        hideLoading();
    } catch (error) {
        console.error('Error loading water sources:', error);
        hideLoading();
        showAlert('Error loading water sources. Please try again.', 'danger');
    }
}

// Add Water Markers to Map
function addWaterMarkers(sources) {
    if (!mainMap) return;
    
    // Clear existing markers
    waterMarkers.forEach(marker => mainMap.removeLayer(marker));
    waterMarkers = [];
    
    sources.forEach(source => {
        const statusColor = getStatusColor(source.safety_status);
        
        // Create custom icon
        const icon = L.divIcon({
            className: 'custom-marker',
            html: `<div style="background-color: ${statusColor}; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
            iconSize: [20, 20],
            popupAnchor: [0, -10]
        });
        
        // Create marker
        const marker = L.marker([source.latitude, source.longitude], { icon })
            .addTo(mainMap)
            .bindPopup(createWaterPopup(source));
        
        marker.on('click', () => {
            console.log('Marker clicked:', source.name);
        });
        
        waterMarkers.push(marker);
    });
    
    console.log('Added', waterMarkers.length, 'markers');
}

// Create Water Source Popup Content
function createWaterPopup(source) {
    const statusText = getStatusText(source.safety_status);
    const statusClass = `status-${source.safety_status}`;
    
    return `
        <div class="water-popup" style="min-width: 200px;">
            <h3 style="margin-bottom: 0.5rem; color: #2e7d32;">${escapeHtml(source.name)}</h3>
            <p><strong>Type:</strong> ${source.source_type}</p>
            <p><strong>Location:</strong> ${escapeHtml(source.location_desc)}</p>
            <div class="status-badge ${statusClass}" style="margin: 0.5rem 0;">
                <strong>Status:</strong> ${statusText}
            </div>
            <p><small>Last tested: ${formatDate(source.last_test_date)}</small></p>
            <a href="water-source.html?id=${source.id}" class="btn btn-sm btn-primary" style="display: inline-block; margin-top: 0.5rem;">View Details</a>
        </div>
    `;
}

// Load Health Facilities
async function loadHealthFacilities() {
    try {
        const result = await API.get('/health-facilities');
        
        if (result.success && result.data) {
            window.healthFacilitiesData = result.data;
            console.log('Health facilities loaded:', result.data.length);
        } else {
            console.error('Failed to load health facilities:', result);
        }
    } catch (error) {
        console.error('Error loading health facilities:', error);
    }
}

// Toggle Health Facilities Layer
function toggleHealthLayer() {
    if (!mainMap) return;
    
    const btn = document.getElementById('toggle-health-btn');
    
    if (healthMarkers.length === 0 && window.healthFacilitiesData) {
        addHealthMarkers();
        if (btn) btn.style.backgroundColor = '#4caf50';
        showAlert('Health facilities layer enabled', 'info');
    } else {
        removeHealthMarkers();
        if (btn) btn.style.backgroundColor = '';
        showAlert('Health facilities layer disabled', 'info');
    }
}

function addHealthMarkers() {
    if (!mainMap || !window.healthFacilitiesData) return;
    
    // Clear existing health markers
    healthMarkers.forEach(marker => mainMap.removeLayer(marker));
    healthMarkers = [];
    
    window.healthFacilitiesData.forEach(facility => {
        // Custom icon for health facilities
        const icon = L.divIcon({
            className: 'health-marker',
            html: `<div style="background-color: #2196f3; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;">
                        <span style="color: white; font-size: 10px;">🏥</span>
                    </div>`,
            iconSize: [20, 20],
            popupAnchor: [0, -10]
        });
        
        const marker = L.marker([facility.latitude, facility.longitude], { icon })
            .addTo(mainMap)
            .bindPopup(`
                <div style="min-width: 200px;">
                    <h4>${escapeHtml(facility.name)}</h4>
                    <p><em>${escapeHtml(facility.demo_data ? JSON.stringify(facility.demo_data) : 'Demo data')}</em></p>
                    <small>⚠️ This is simulated data for demonstration purposes</small>
                </div>
            `);
        
        healthMarkers.push(marker);
    });
}

function removeHealthMarkers() {
    if (!mainMap) return;
    healthMarkers.forEach(marker => mainMap.removeLayer(marker));
    healthMarkers = [];
}

// Filter Markers by Search Term
function filterMapMarkers(searchTerm) {
    if (!mainMap) return;
    
    if (!searchTerm) {
        // Show all markers
        waterMarkers.forEach(marker => mainMap.addLayer(marker));
        return;
    }
    
    const filteredSources = currentWaterSources.filter(source => 
        source.name.toLowerCase().includes(searchTerm) ||
        source.location_desc.toLowerCase().includes(searchTerm)
    );
    
    // Hide all markers first
    waterMarkers.forEach(marker => mainMap.removeLayer(marker));
    
    // Show only filtered markers
    filteredSources.forEach(source => {
        const marker = waterMarkers.find(m => {
            const latLng = m.getLatLng();
            return latLng.lat === source.latitude && latLng.lng === source.longitude;
        });
        if (marker) mainMap.addLayer(marker);
    });
    
    // If there are results, fit bounds to show them
    if (filteredSources.length > 0) {
        const bounds = L.latLngBounds(filteredSources.map(s => [s.latitude, s.longitude]));
        mainMap.fitBounds(bounds, { padding: [50, 50] });
    }
}

// Center Map on User Location
function centerOnUser() {
    if (!mainMap) return;
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const userLat = position.coords.latitude;
                const userLng = position.coords.longitude;
                mainMap.setView([userLat, userLng], 15);
                showAlert('Map centered on your location', 'success');
            },
            (error) => {
                console.error('Geolocation error:', error);
                showAlert('Unable to get your location. Please check your browser permissions.', 'warning');
            }
        );
    } else {
        showAlert('Geolocation is not supported by your browser', 'warning');
    }
}

// Reset Map View
function resetMapView() {
    if (!mainMap) return;
    mainMap.setView(MANKON_CENTER, DEFAULT_ZOOM);
    showAlert('Map reset to default view', 'info');
}

// Helper function to escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Make functions global
window.initMap = initMap;
window.centerOnUser = centerOnUser;
window.resetMapView = resetMapView;
window.toggleHealthLayer = toggleHealthLayer;
window.filterMapMarkers = filterMapMarkers;

// Initialize map when page loads - but only if map element exists
document.addEventListener('DOMContentLoaded', () => {
    // Check if we're on a page with map
    if (document.getElementById('map')) {
        // Load Leaflet CSS if not already loaded
        if (!document.querySelector('link[href*="leaflet"]')) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
            document.head.appendChild(link);
        }
        
        // Load Leaflet JS if not already loaded
        if (typeof L === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            script.onload = () => {
                // Small delay to ensure everything is loaded
                setTimeout(() => initMap(), 100);
            };
            document.head.appendChild(script);
        } else {
            initMap();
        }
    }
});