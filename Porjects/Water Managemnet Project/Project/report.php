<?php
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Disruption - <?php echo APP_NAME; ?></title>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <!-- SweetAlert2 for nice alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                <a href="index.php" class="nav-link">Home Map</a>
                <a href="status.php" class="nav-link">Check Status</a>
            </div>
        </div>
    </nav>

    <div class="container page-container">
        <div class="card" style="max-width: 800px; margin: 0 auto;">
            <div class="card-header">
                <h2>Report Water Disruption</h2>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.5rem;">
                    Please provide details about the water issue you are experiencing.
                </p>
            </div>
            
            <div class="card-body">
                <!-- Informational Disclaimer -->
                <div class="alert alert-info">
                    <i class="ph-fill ph-info" style="font-size: 1.5rem;"></i>
                    <div>
                        <strong>Note:</strong> This is a community reporting tool. Submitting a report helps the water company track issues, but does not guarantee immediate response.
                    </div>
                </div>

                <form id="reportForm" enctype="multipart/form-data">
                    
                    <!-- Disruption Type -->
                    <div class="form-group">
                        <label class="form-label">1. What type of issue are you reporting? <span style="color: red;">*</span></label>
                        
                        <div class="disruption-types">
                            <label class="type-card">
                                <input type="radio" name="issue_type" value="burst_pipe" required>
                                <i class="ph-fill ph-pipe icon" style="color: #0A58CA;"></i>
                                <span class="title">Burst Pipe</span>
                            </label>
                            
                            <label class="type-card">
                                <input type="radio" name="issue_type" value="no_water_unexplained">
                                <i class="ph-fill ph-drop-slash icon" style="color: #EF4444;"></i>
                                <span class="title">No Water Flow<br><small>(Unexplained)</small></span>
                            </label>
                            
                            <label class="type-card">
                                <input type="radio" name="issue_type" value="water_suspension_bill">
                                <i class="ph-fill ph-money icon" style="color: #F59E0B;"></i>
                                <span class="title">Water Suspension<br><small>(Bill Related)</small></span>
                            </label>
                            
                            <label class="type-card">
                                <input type="radio" name="issue_type" value="other">
                                <i class="ph-fill ph-question icon" style="color: #6B7280;"></i>
                                <span class="title">Other Issue</span>
                            </label>
                        </div>
                    </div>

                    <!-- Bill Suspension Warning -->
                    <div id="billWarning" class="alert alert-warning" style="display: none;">
                        <i class="ph-fill ph-warning-circle" style="font-size: 1.5rem;"></i>
                        <div>
                            <strong>Possible Unpaid Bills</strong><br>
                            If your water was cut due to unpaid bills, technical repair teams cannot resolve this. Please contact your water company's billing department. You may still submit this report for tracking.
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label class="form-label" for="description">2. Description</label>
                        <textarea class="form-control" id="description" name="description" maxlength="500" placeholder="Please provide any extra details (e.g., 'Water is flooding the road')..."></textarea>
                        <div style="text-align: right; font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">
                            <span id="charCount">0</span>/500 characters
                        </div>
                    </div>

                    <!-- Location Capture -->
                    <div class="form-group">
                        <label class="form-label">3. Location Details <span style="color: red;">*</span></label>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem;">We need your location to direct the repair team. Please allow location access.</p>
                        
                        <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                            <button type="button" id="getLocationBtn" class="btn btn-outline" style="width: 100%;">
                                <i class="ph-bold ph-crosshair"></i> Get My Location
                            </button>
                        </div>
                        
                        <input type="hidden" name="latitude" id="latitude" required>
                        <input type="hidden" name="longitude" id="longitude" required>
                        
                        <div id="mapPreviewWrapper" style="display: none;">
                            <div class="map-preview-container">
                                <div id="mapPreview" class="map-preview"></div>
                                <div id="locationAccuracy" class="location-accuracy"></div>
                            </div>
                            <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.5rem;">
                                <i class="ph-fill ph-info"></i> You can drag the pin to adjust the exact location if needed.
                            </p>
                        </div>
                    </div>

                    <!-- Photo Upload -->
                    <div class="form-group">
                        <label class="form-label">4. Photo Evidence (Optional)</label>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem;">Upload up to 3 photos (Max 5MB each). This helps teams assess the issue.</p>
                        
                        <label class="file-upload-wrapper" for="photos">
                            <i class="ph ph-camera upload-icon"></i>
                            <h4>Click to Upload Photos</h4>
                            <p style="color: var(--text-muted); font-size: 0.85rem;">or drag and drop them here</p>
                            <input type="file" id="photos" name="photos[]" accept="image/jpeg, image/png" multiple>
                        </label>
                        
                        <div id="imagePreviewContainer" class="image-preview-container"></div>
                    </div>

                    <!-- Reporter Info -->
                    <div class="form-group">
                        <label class="form-label" for="reporter_name">5. Your Name (Optional)</label>
                        <input type="text" class="form-control" id="reporter_name" name="reporter_name" maxlength="100" placeholder="Enter your name (leave blank to report anonymously)">
                    </div>

                    <!-- Submit -->
                    <div style="margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem; text-align: right;">
                        <button type="submit" id="submitBtn" class="btn btn-primary" style="width: 100%; font-size: 1.1rem; padding: 1rem;">
                            <i class="ph-bold ph-paper-plane-tilt"></i> Submit Report
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const MAP_CENTER_LAT = <?php echo MAP_CENTER_LAT; ?>;
        const MAP_CENTER_LNG = <?php echo MAP_CENTER_LNG; ?>;
    </script>
    <script src="assets/js/report.js"></script>
</body>
</html>
