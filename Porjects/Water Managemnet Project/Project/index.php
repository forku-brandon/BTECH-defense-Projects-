<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Community Water Service Disruption Reporting</title>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Icons (Phosphor Icons) -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">
                <i class="ph-fill ph-drop logo-icon"></i>
                WataReport
            </a>
            <div class="nav-links">
                <a href="index.php" class="nav-link active">Home Map</a>
                <a href="status.php" class="nav-link">Check Status</a>
                <a href="dashboard/login.php" class="btn btn-outline btn-sm">Staff Login</a>
            </div>
        </div>
    </nav>

    <!-- Map Hero Section -->
    <main class="map-hero">
        <div id="map"></div>
        
        <div class="map-overlay">
            <div class="map-card">
                <h3>Live Water Reports</h3>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1rem;">
                    Bamenda, Cameroon. Showing reports from the last 30 days.
                </p>
                
                <div class="filters" style="margin-bottom: 1rem;">
                    <select id="typeFilter" class="form-control" style="padding: 0.5rem; font-size: 0.875rem;">
                        <option value="all">All Disruption Types</option>
                        <option value="burst_pipe">Burst Pipe</option>
                        <option value="no_water_unexplained">No Water Flow (Unexplained)</option>
                        <option value="water_suspension_bill">Water Suspension (Bill Related)</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="legend">
                    <div class="legend-item">
                        <div class="legend-color bg-pending"></div>
                        <span>Pending Review</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color bg-ack"></div>
                        <span>Acknowledged (Investigating)</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color bg-resolved"></div>
                        <span>Resolved</span>
                    </div>
                </div>
            </div>
        </div>

        <a href="report.php" class="floating-action fab" title="Report a Disruption">
            <i class="ph ph-plus"></i>
        </a>
    </main>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
    
    <!-- Map Configuration -->
    <script>
        const MAP_CENTER_LAT = <?php echo MAP_CENTER_LAT; ?>;
        const MAP_CENTER_LNG = <?php echo MAP_CENTER_LNG; ?>;
    </script>
    
    <!-- App JS -->
    <script src="assets/js/map.js"></script>
</body>
</html>
