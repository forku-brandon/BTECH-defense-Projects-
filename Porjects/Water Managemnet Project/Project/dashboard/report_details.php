<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$reportId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($reportId <= 0) die("Invalid Report ID");

// Fetch Report
$stmt = $pdo->prepare("SELECT * FROM reports WHERE id = ?");
$stmt->execute([$reportId]);
$report = $stmt->fetch();
if (!$report) die("Report not found");

// Fetch Photos
$stmtPhotos = $pdo->prepare("SELECT * FROM photos WHERE report_id = ?");
$stmtPhotos->execute([$reportId]);
$photos = $stmtPhotos->fetchAll();

// Fetch Bill Inquiry if exists
$billInquiry = null;
if ($report['issue_type'] === 'water_suspension_bill') {
    $stmtBill = $pdo->prepare("SELECT bi.*, u.username FROM bill_inquiries bi JOIN users u ON bi.staff_user_id = u.id WHERE report_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmtBill->execute([$reportId]);
    $billInquiry = $stmtBill->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report <?php echo htmlspecialchars($report['tracking_id']); ?> - Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .dashboard-container { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: white; border-right: 1px solid var(--border-color); padding: 1.5rem; display: flex; flex-direction: column; position: fixed; height: 100vh; }
        .main-content { flex: 1; margin-left: 260px; padding: 2rem; background: var(--bg-body); }
        .sidebar-menu { margin-top: 2rem; flex: 1; }
        .menu-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: var(--text-muted); border-radius: var(--radius-sm); margin-bottom: 0.5rem; font-weight: 500; }
        .menu-item:hover, .menu-item.active { background: var(--primary-light); color: var(--primary-blue); }
        .grid-2 { display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; }
        
        .update-form label { display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.9rem; }
        .update-form select, .update-form input, .update-form textarea { width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--radius-sm); margin-bottom: 1rem; font-family: inherit; }
    </style>
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <a href="index.php" class="logo" style="margin-bottom: 0.5rem;">
            <i class="ph-fill ph-drop logo-icon"></i> WataReport
        </a>
        <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 2rem; padding-left: 0.5rem;">Staff Portal</div>
        <div class="sidebar-menu">
            <a href="index.php" class="menu-item"><i class="ph ph-squares-four"></i> Dashboard</a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="../admin/users.php" class="menu-item"><i class="ph ph-users"></i> Manage Users</a>
            <?php endif; ?>
        </div>
    </aside>

    <main class="main-content">
        <div style="margin-bottom: 1.5rem;">
            <a href="index.php" class="btn btn-outline btn-sm"><i class="ph ph-arrow-left"></i> Back to Dashboard</a>
        </div>

        <div class="grid-2">
            <!-- Left Col: Details -->
            <div>
                <div class="card" style="margin-bottom: 2rem;">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="margin: 0;">Report Details: <?php echo htmlspecialchars($report['tracking_id']); ?></h3>
                        <span class="badge <?php echo getStatusBadgeClass($report['status']); ?>">
                            <?php echo ucfirst($report['status']); ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                            <div>
                                <p style="color: var(--text-muted); font-size: 0.85rem;">Issue Type</p>
                                <p style="font-weight: 600;"><?php echo getIssueTypeName($report['issue_type']); ?></p>
                            </div>
                            <div>
                                <p style="color: var(--text-muted); font-size: 0.85rem;">Reported On</p>
                                <p style="font-weight: 600;"><?php echo date('M d, Y - H:i', strtotime($report['created_at'])); ?></p>
                            </div>
                            <div>
                                <p style="color: var(--text-muted); font-size: 0.85rem;">Reporter Name</p>
                                <p style="font-weight: 600;"><?php echo htmlspecialchars($report['reporter_name'] ?: 'Anonymous'); ?></p>
                            </div>
                            <div>
                                <p style="color: var(--text-muted); font-size: 0.85rem;">Upvotes</p>
                                <p style="font-weight: 600; color: var(--primary-blue);"><i class="ph-fill ph-thumbs-up"></i> <?php echo $report['upvote_count']; ?></p>
                            </div>
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.5rem;">User Description</p>
                            <div style="background: var(--bg-body); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                                <?php echo nl2br(htmlspecialchars($report['description'] ?: 'No description provided.')); ?>
                            </div>
                        </div>

                        <?php if (count($photos) > 0): ?>
                        <div>
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.5rem;">Photo Evidence</p>
                            <div style="display: flex; gap: 1rem;">
                                <?php foreach ($photos as $photo): ?>
                                    <a href="../<?php echo htmlspecialchars($photo['file_path']); ?>" target="_blank">
                                        <img src="../<?php echo htmlspecialchars($photo['file_path']); ?>" style="width: 150px; height: 150px; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Bill Status Inquiry Section (Only for Bill Related Reports) -->
                <?php if ($report['issue_type'] === 'water_suspension_bill'): ?>
                <div class="card" style="border-left: 4px solid #F59E0B; margin-bottom: 2rem;">
                    <div class="card-header" style="background: #FEF3C7;">
                        <h4 style="margin: 0; color: #B45309;"><i class="ph-fill ph-money"></i> Bill Status Inquiry</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($billInquiry): ?>
                            <div style="margin-bottom: 1rem; padding: 1rem; background: var(--bg-body); border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">Last Inquiry by <?php echo htmlspecialchars($billInquiry['username']); ?> on <?php echo date('M d, Y H:i', strtotime($billInquiry['created_at'])); ?></p>
                                <p style="font-weight: 600; margin-bottom: 0.5rem;">Status: 
                                    <?php 
                                        $statuses = [
                                            'paid_pending_reconnect' => 'Paid - Pending Reconnection',
                                            'outstanding_balance' => 'Outstanding Balance - Contact Customer',
                                            'unknown_referred' => 'Unknown - Refer to Billing Dept'
                                        ];
                                        echo isset($statuses[$billInquiry['bill_status']]) ? $statuses[$billInquiry['bill_status']] : 'Unknown';
                                    ?>
                                </p>
                                <?php if($billInquiry['notes']): ?>
                                    <p style="font-size: 0.9rem;"><em>Notes:</em> <?php echo nl2br(htmlspecialchars($billInquiry['notes'])); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <form id="billForm" class="update-form">
                            <input type="hidden" name="report_id" value="<?php echo $reportId; ?>">
                            <label>Update Bill Status</label>
                            <select name="bill_status" required>
                                <option value="">Select Status...</option>
                                <option value="paid_pending_reconnect">Paid - Pending Reconnection</option>
                                <option value="outstanding_balance">Outstanding Balance - Contact Customer</option>
                                <option value="unknown_referred">Unknown - Refer to Billing Dept</option>
                            </select>
                            
                            <label>Inquiry Notes</label>
                            <textarea name="notes" rows="2" placeholder="e.g. Called customer, they paid via MoMo."></textarea>
                            
                            <button type="button" id="saveBillBtn" class="btn btn-outline btn-sm" style="width: 100%;">Save Bill Inquiry</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

            </div>

            <!-- Right Col: Map & Update Status -->
            <div>
                <div class="card" style="margin-bottom: 2rem;">
                    <div class="card-body" style="padding: 0;">
                        <div id="map" style="height: 250px; width: 100%; border-radius: var(--radius-lg) var(--radius-lg) 0 0;"></div>
                    </div>
                    <div style="padding: 1rem;">
                        <p style="font-size: 0.85rem; color: var(--text-muted);"><i class="ph-fill ph-map-pin"></i> Coordinates: <?php echo $report['latitude']; ?>, <?php echo $report['longitude']; ?></p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4 style="margin: 0;">Update Report Status</h4>
                    </div>
                    <div class="card-body">
                        <form id="updateForm" class="update-form">
                            <input type="hidden" name="report_id" value="<?php echo $reportId; ?>">
                            
                            <label>Status</label>
                            <select name="status" id="statusSelect">
                                <option value="pending" <?php if($report['status'] === 'pending') echo 'selected'; ?>>Pending Review</option>
                                <option value="acknowledged" <?php if($report['status'] === 'acknowledged') echo 'selected'; ?>>Acknowledged (In Progress)</option>
                                <option value="resolved" <?php if($report['status'] === 'resolved') echo 'selected'; ?>>Resolved</option>
                            </select>

                            <label>Assign Repair Team (Optional)</label>
                            <input type="text" name="assigned_team" value="<?php echo htmlspecialchars($report['assigned_team'] ?? ''); ?>" placeholder="e.g. Team Alpha - Nkwen">

                            <label>Internal Notes</label>
                            <textarea name="internal_notes" rows="4" placeholder="Private notes for staff only..."><?php echo htmlspecialchars($report['internal_notes'] ?? ''); ?></textarea>

                            <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="ph-bold ph-floppy-disk"></i> Save Updates</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Initialize Map
    const lat = <?php echo $report['latitude']; ?>;
    const lng = <?php echo $report['longitude']; ?>;
    const map = L.map('map').setView([lat, lng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
    L.marker([lat, lng]).addTo(map);

    // Form Submissions
    document.getElementById('updateForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const response = await fetch('api/update_status.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.status === 'success') {
                Swal.fire('Success', 'Report updated successfully.', 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Network error.', 'error');
        }
    });

    const saveBillBtn = document.getElementById('saveBillBtn');
    if(saveBillBtn) {
        saveBillBtn.addEventListener('click', async () => {
            const form = document.getElementById('billForm');
            if(!form.reportValidity()) return;
            
            const formData = new FormData(form);
            try {
                const response = await fetch('api/record_bill_inquiry.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.status === 'success') {
                    Swal.fire('Success', 'Bill inquiry recorded.', 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'Network error.', 'error');
            }
        });
    }
</script>
</body>
</html>
