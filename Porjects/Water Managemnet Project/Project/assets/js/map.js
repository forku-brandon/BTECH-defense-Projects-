document.addEventListener('DOMContentLoaded', () => {
    // Initialize map
    const map = L.map('map', {
        zoomControl: false // Custom position
    }).setView([MAP_CENTER_LAT, MAP_CENTER_LNG], 13);
    
    // Add zoom control to top right
    L.control.zoom({ position: 'topright' }).addTo(map);

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(map);

    // Marker cluster group
    const markers = L.markerClusterGroup({
        maxClusterRadius: 40,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false,
        zoomToBoundsOnClick: true
    });

    let allReports = [];

    // Custom marker icons
    const createIcon = (color) => {
        return L.divIcon({
            className: 'custom-div-icon',
            html: `<div style="background-color:${color}; width: 24px; height: 24px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>`,
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });
    };

    const icons = {
        pending: createIcon('#EF4444'),    // Red
        acknowledged: createIcon('#F59E0B'),// Yellow
        resolved: createIcon('#10B981')    // Green
    };

    // Format date string
    const formatDate = (dateString) => {
        const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
        return new Date(dateString).toLocaleDateString(undefined, options);
    };

    // Load reports from API
    const loadReports = async () => {
        try {
            const response = await fetch('api/get_reports.php');
            const data = await response.json();
            
            if (data.status === 'success') {
                allReports = data.data;
                renderMarkers(allReports);
            } else {
                console.error("Failed to load reports:", data.message);
            }
        } catch (error) {
            console.error("Error fetching reports:", error);
        }
    };

    // Render markers on map
    const renderMarkers = (reports) => {
        markers.clearLayers();
        
        reports.forEach(report => {
            const icon = icons[report.status] || icons.pending;
            
            const marker = L.marker([report.latitude, report.longitude], { icon: icon });
            
            // Format type name
            const typeNames = {
                'burst_pipe': 'Burst Pipe',
                'no_water_unexplained': 'No Water Flow (Unexplained)',
                'water_suspension_bill': 'Water Suspension (Bill Related)',
                'other': 'Other'
            };
            const typeName = typeNames[report.issue_type] || report.issue_type;
            
            // Format status name
            const statusNames = {
                'pending': 'Pending Review',
                'acknowledged': 'Acknowledged',
                'resolved': 'Resolved'
            };
            const statusName = statusNames[report.status] || report.status;
            
            // Popup content
            const popupContent = `
                <div style="font-family: 'Inter', sans-serif; min-width: 200px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 8px; margin-bottom: 8px;">
                        <h4 style="margin: 0; color: #1F2937;">${typeName}</h4>
                        <span style="background: ${icon.options.html.match(/background-color:([^;]+)/)[1]}; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: bold;">
                            ${statusName}
                        </span>
                    </div>
                    <p style="margin: 0 0 8px 0; color: #6B7280; font-size: 0.85rem; line-height: 1.4;">
                        ${report.description ? report.description.substring(0, 100) + (report.description.length > 100 ? '...' : '') : 'No description provided.'}
                    </p>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; color: #9CA3AF; margin-bottom: 10px;">
                        <span><i class="ph ph-clock"></i> ${formatDate(report.created_at)}</span>
                        <span><i class="ph ph-thumbs-up"></i> ${report.upvote_count}</span>
                    </div>
                    <a href="status.php?id=${report.tracking_id}" style="display: block; text-align: center; background: #0A58CA; color: white; text-decoration: none; padding: 6px; border-radius: 4px; font-weight: 500; font-size: 0.9rem;">
                        View Details
                    </a>
                </div>
            `;
            
            marker.bindPopup(popupContent);
            markers.addLayer(marker);
        });
        
        map.addLayer(markers);
    };

    // Filter handling
    const typeFilter = document.getElementById('typeFilter');
    if (typeFilter) {
        typeFilter.addEventListener('change', (e) => {
            const selectedType = e.target.value;
            if (selectedType === 'all') {
                renderMarkers(allReports);
            } else {
                const filtered = allReports.filter(r => r.issue_type === selectedType);
                renderMarkers(filtered);
            }
        });
    }

    // Initial load
    loadReports();
});
