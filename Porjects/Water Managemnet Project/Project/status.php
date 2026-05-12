<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

$trackingId = isset($_GET['id']) ? sanitizeInput($_GET['id']) : '';
$report = null;
$history = [];
$photos = [];
$error = '';

if ($trackingId) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM reports WHERE tracking_id = ?");
        $stmt->execute([$trackingId]);
        $report = $stmt->fetch();

        if ($report) {
            // Get History
            $stmtHist = $pdo->prepare("
                SELECT sh.*, u.username 
                FROM status_history sh 
                LEFT JOIN users u ON sh.user_id = u.id 
                WHERE sh.report_id = ? 
                ORDER BY sh.created_at ASC
            ");
            $stmtHist->execute([$report['id']]);
            $history = $stmtHist->fetchAll();

            // Get Photos
            $stmtPhotos = $pdo->prepare("SELECT * FROM photos WHERE report_id = ?");
            $stmtPhotos->execute([$report['id']]);
            $photos = $stmtPhotos->fetchAll();
        } else {
            $error = "No report found with tracking ID: " . htmlspecialchars($trackingId);
        }
    } catch (PDOException $e) {
        $error = "An error occurred while fetching the report.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Report Status - <?php echo APP_NAME; ?></title>
    <!-- Leaflet CSS for map preview -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .search-hero {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-teal));
            padding: 4rem 2rem;
            text-align: center;
            color: white;
            border-radius: var(--radius-lg);
            margin-bottom: 2rem;
        }
        .search-form {
            display: flex;
            max-width: 600px;
            margin: 2rem auto 0;
            background: white;
            padding: 0.5rem;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
        }
        .search-form input {
            flex-grow: 1;
            border: none;
            padding: 1rem;
            font-size: 1.1rem;
            border-radius: var(--radius-sm);
            outline: none;
        }
        .search-form button {
            border-radius: var(--radius-sm);
            padding: 0 2rem;
        }
    </style>
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
                <a href="status.php" class="nav-link active">Check Status</a>
            </div>
        </div>
    </nav>

    <div class="container page-container">
        
        <?php if (!$report): ?>
        <div class="search-hero">
            <h1>Track Your Report</h1>
            <p style="opacity: 0.9; margin-top: 1rem;">Enter your Tracking ID to view the status of your reported water issue.</p>
            
            <form action="status.php" method="GET" class="search-form">
                <input type="text" name="id" placeholder="e.g. WB-20260515-0001" required value="<?php echo htmlspecialchars($trackingId); ?>">
                <button type="submit" class="btn btn-primary">Track</button>
            </form>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger" style="max-width: 600px; margin: 0 auto;">
                <i class="ph-fill ph-warning-circle" style="font-size: 1.5rem;"></i>
                <div><?php echo $error; ?></div>
            </div>
        <?php endif; ?>

        <?php else: ?>
        <!-- Report Details View -->
        
        <div style="margin-bottom: 1rem;">
            <a href="status.php" class="btn btn-outline btn-sm"><i class="ph ph-arrow-left"></i> Track Another</a>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 350px; gap: 2rem;">
            <!-- Left Column: Details -->
            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3 style="margin-bottom: 0.25rem;">Report <?php echo htmlspecialchars($report['tracking_id']); ?></h3>
                        <span class="badge <?php echo getStatusBadgeClass($report['status']); ?>">
                            <?php echo ucfirst($report['status']); ?>
                        </span>
                    </div>
                    
                    <button id="upvoteBtn" class="btn btn-outline btn-sm" data-id="<?php echo $report['id']; ?>">
                        <i class="ph-bold ph-thumbs-up"></i> <span id="upvoteCount"><?php echo $report['upvote_count']; ?></span> Upvotes
                    </button>
                </div>
                <div class="card-body">
                    
                    <?php if ($report['issue_type'] === 'water_suspension_bill' && $report['status'] === 'pending'): ?>
                        <div class="alert alert-warning">
                            <i class="ph-fill ph-info"></i>
                            <div>
                                <strong>Note on Bill Suspensions:</strong> If your issue is bill-related, technical teams cannot resolve it. Please ensure you have contacted the billing department.
                            </div>
                        </div>
                    <?php endif; ?>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                        <div>
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">Issue Type</p>
                            <p style="font-weight: 600;"><?php echo getIssueTypeName($report['issue_type']); ?></p>
                        </div>
                        <div>
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">Reported On</p>
                            <p style="font-weight: 600;"><?php echo date('M d, Y - H:i', strtotime($report['created_at'])); ?></p>
                        </div>
                        <?php if ($report['assigned_team']): ?>
                        <div style="grid-column: span 2;">
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">Assigned Repair Team</p>
                            <p style="font-weight: 600; color: var(--primary-blue);"><i class="ph-fill ph-users"></i> <?php echo htmlspecialchars($report['assigned_team']); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.5rem;">Description</p>
                        <div style="background: var(--bg-body); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                            <?php echo nl2br(htmlspecialchars($report['description'] ?: 'No description provided.')); ?>
                        </div>
                    </div>

                    <?php if (count($photos) > 0): ?>
                    <div style="margin-top: 2rem;">
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.5rem;">Photo Evidence</p>
                        <div style="display: flex; gap: 1rem; overflow-x: auto; padding-bottom: 1rem;">
                            <?php foreach ($photos as $photo): ?>
                                <a href="<?php echo htmlspecialchars($photo['file_path']); ?>" target="_blank">
                                    <img src="<?php echo htmlspecialchars($photo['file_path']); ?>" alt="Report Photo" style="width: 120px; height: 120px; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>

            <!-- Right Column: Timeline & Map -->
            <div>
                <div class="card" style="margin-bottom: 1.5rem;">
                    <div class="card-header">
                        <h4>Status Timeline</h4>
                    </div>
                    <div class="card-body" style="padding-top: 0; padding-bottom: 1rem;">
                        <div class="timeline">
                            <?php foreach ($history as $idx => $event): ?>
                                <?php 
                                    $isLast = ($idx === count($history) - 1);
                                    $activeClass = $isLast ? 'active' : '';
                                    if ($event['new_status'] === 'resolved') $activeClass = 'resolved';
                                ?>
                                <div class="timeline-item <?php echo $activeClass; ?>">
                                    <div class="timeline-date"><?php echo date('M d, g:i A', strtotime($event['created_at'])); ?></div>
                                    <div class="timeline-content">
                                        <strong><?php echo ucfirst($event['new_status']); ?></strong>
                                        <?php if ($event['new_status'] === 'pending'): ?>
                                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Report submitted and waiting for review.</p>
                                        <?php elseif ($event['new_status'] === 'acknowledged'): ?>
                                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Water company is investigating the issue.</p>
                                        <?php elseif ($event['new_status'] === 'resolved'): ?>
                                            <p style="font-size: 0.85rem; color: var(--status-resolved); margin-top: 0.25rem;">Issue has been resolved!</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body" style="padding: 0;">
                        <div id="staticMap" style="height: 200px; width: 100%; border-radius: var(--radius-lg);"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scripts for Upvote and Static Map -->
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Initialize Map
                const lat = <?php echo $report['latitude']; ?>;
                const lng = <?php echo $report['longitude']; ?>;
                
                const map = L.map('staticMap', {
                    zoomControl: false,
                    dragging: false,
                    scrollWheelZoom: false
                }).setView([lat, lng], 15);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap'
                }).addTo(map);
                
                // Color marker based on status
                const statusColors = {
                    'pending': '#EF4444',
                    'acknowledged': '#F59E0B',
                    'resolved': '#10B981'
                };
                const color = statusColors['<?php echo $report['status']; ?>'];
                
                const customIcon = L.divIcon({
                    className: 'custom-div-icon',
                    html: `<div style="background-color:${color}; width: 24px; height: 24px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>`,
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                });

                L.marker([lat, lng], {icon: customIcon}).addTo(map);

                // Upvote functionality
                const upvoteBtn = document.getElementById('upvoteBtn');
                if(upvoteBtn) {
                    upvoteBtn.addEventListener('click', async () => {
                        const reportId = upvoteBtn.dataset.id;
                        upvoteBtn.disabled = true;
                        
                        try {
                            const response = await fetch('api/upvote.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: `report_id=${reportId}`
                            });
                            
                            const data = await response.json();
                            
                            if(data.status === 'success') {
                                document.getElementById('upvoteCount').textContent = data.new_count;
                                upvoteBtn.classList.replace('btn-outline', 'btn-primary');
                                upvoteBtn.innerHTML = '<i class="ph-bold ph-check"></i> Upvoted';
                            } else {
                                alert(data.message);
                                upvoteBtn.disabled = false;
                            }
                        } catch (error) {
                            alert('An error occurred.');
                            upvoteBtn.disabled = false;
                        }
                    });
                }
            });
        </script>
        <?php endif; ?>
    </div>
</body>
</html>
