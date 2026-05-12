<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Authentication Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch Statistics
$stats = [
    'total' => 0,
    'pending' => 0,
    'acknowledged' => 0,
    'resolved' => 0,
    'avg_resolution' => 0
];

try {
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM reports GROUP BY status");
    while ($row = $stmt->fetch()) {
        $stats[$row['status']] = $row['count'];
        $stats['total'] += $row['count'];
    }

    $stmtAvg = $pdo->query("SELECT AVG(resolution_time_hours) as avg_time FROM reports WHERE status = 'resolved' AND resolution_time_hours IS NOT NULL");
    $avgResult = $stmtAvg->fetch();
    $stats['avg_resolution'] = $avgResult['avg_time'] ? round($avgResult['avg_time'], 1) : 0;
    
    // Fetch Reports and Calculate Priority
    $stmtReports = $pdo->query("SELECT * FROM reports ORDER BY created_at DESC");
    $reports = $stmtReports->fetchAll();
    
    // Calculate and sort by priority score
    foreach ($reports as &$report) {
        if ($report['status'] === 'resolved') {
            $report['priority_score'] = -1; // Move resolved to bottom
        } else {
            $hoursElapsed = (time() - strtotime($report['created_at'])) / 3600;
            $report['priority_score'] = calculatePriorityScore($report['issue_type'], $report['upvote_count'], $hoursElapsed);
        }
    }
    
    // Sort descending by priority score
    usort($reports, function($a, $b) {
        return $b['priority_score'] <=> $a['priority_score'];
    });

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .dashboard-container { display: flex; min-height: 100vh; }
        .sidebar {
            width: 260px;
            background: white;
            border-right: 1px solid var(--border-color);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
        }
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 2rem;
            background: var(--bg-body);
        }
        .stat-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .bg-blue-light { background: #E0F2FE; color: #0284C7; }
        .bg-red-light { background: #FEE2E2; color: #DC2626; }
        .bg-yellow-light { background: #FEF3C7; color: #D97706; }
        .bg-green-light { background: #D1FAE5; color: #059669; }
        
        .table-wrapper {
            background: white;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 1rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        th {
            background: var(--primary-light);
            font-weight: 600;
            color: var(--text-muted);
            font-size: 0.85rem;
            text-transform: uppercase;
        }
        tr:hover { background: var(--bg-body); }
        .sidebar-menu { margin-top: 2rem; flex: 1; }
        .menu-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--text-muted);
            border-radius: var(--radius-sm);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .menu-item:hover, .menu-item.active {
            background: var(--primary-light);
            color: var(--primary-blue);
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <a href="index.php" class="logo" style="margin-bottom: 0.5rem;">
            <i class="ph-fill ph-drop logo-icon"></i> WataReport
        </a>
        <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 2rem; padding-left: 0.5rem;">
            Staff Portal
        </div>
        
        <div class="sidebar-menu">
            <a href="index.php" class="menu-item active">
                <i class="ph ph-squares-four" style="font-size: 1.25rem;"></i> Dashboard
            </a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="../admin/users.php" class="menu-item">
                <i class="ph ph-users" style="font-size: 1.25rem;"></i> Manage Users
            </a>
            <?php endif; ?>
        </div>
        
        <div style="border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary-blue); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </div>
                <div>
                    <div style="font-weight: 600; font-size: 0.9rem;"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($_SESSION['company_name']); ?></div>
                </div>
            </div>
            <a href="logout.php" class="menu-item" style="color: #DC2626;">
                <i class="ph ph-sign-out" style="font-size: 1.25rem;"></i> Logout
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2>Overview</h2>
            <div style="display: flex; gap: 1rem;">
                <button class="btn btn-primary btn-sm" onclick="document.getElementById('exportModal').style.display='flex'">
                    <i class="ph-bold ph-download-simple"></i> Export Data
                </button>
                <button class="btn btn-outline btn-sm" onclick="window.print()">
                    <i class="ph ph-printer"></i> Print Report
                </button>
            </div>
        </div>
        
        <!-- Stat Cards -->
        <div class="stat-cards">
            <div class="stat-card">
                <div class="stat-icon bg-blue-light"><i class="ph-fill ph-files"></i></div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?php echo $stats['total']; ?></div>
                    <div style="color: var(--text-muted); font-size: 0.85rem;">Total Reports</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-red-light"><i class="ph-fill ph-warning-circle"></i></div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?php echo $stats['pending']; ?></div>
                    <div style="color: var(--text-muted); font-size: 0.85rem;">Pending Review</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-yellow-light"><i class="ph-fill ph-wrench"></i></div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?php echo $stats['acknowledged']; ?></div>
                    <div style="color: var(--text-muted); font-size: 0.85rem;">In Progress</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-green-light"><i class="ph-fill ph-check-circle"></i></div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?php echo $stats['avg_resolution']; ?>h</div>
                    <div style="color: var(--text-muted); font-size: 0.85rem;">Avg Resolution Time</div>
                </div>
            </div>
        </div>

        <!-- Reports Table -->
        <div class="table-wrapper">
            <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0;">Prioritized Reports</h3>
            </div>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Tracking ID</th>
                            <th>Issue Type</th>
                            <th>Date Reported</th>
                            <th>Score</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($reports) > 0): ?>
                            <?php foreach ($reports as $r): ?>
                                <tr style="<?php echo $r['issue_type'] === 'water_suspension_bill' ? 'background-color: #FEF3C7; border-left: 4px solid #F59E0B;' : ''; ?>">
                                    <td style="font-family: monospace; font-weight: 600;">
                                        <?php echo htmlspecialchars($r['tracking_id']); ?>
                                        <?php if ($r['issue_type'] === 'water_suspension_bill'): ?>
                                            <i class="ph-fill ph-money" style="color: #F59E0B; margin-left: 5px;" title="Bill Related"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo getIssueTypeName($r['issue_type']); ?></td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($r['created_at'])); ?><br>
                                        <small style="color: var(--text-muted);"><?php echo date('H:i', strtotime($r['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <?php if($r['status'] !== 'resolved'): ?>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span style="font-weight: bold; color: var(--primary-blue);"><?php echo round($r['priority_score']); ?></span>
                                            <small style="color: var(--text-muted);">(<i class="ph-fill ph-thumbs-up" style="font-size:0.7rem;"></i> <?php echo $r['upvote_count']; ?>)</small>
                                        </div>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo getStatusBadgeClass($r['status']); ?>">
                                            <?php echo ucfirst($r['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="report_details.php?id=<?php echo $r['id']; ?>" class="btn btn-outline btn-sm">View Details</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 2rem; color: var(--text-muted);">No reports found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<!-- Export Modal -->
<div id="exportModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 400px; padding: 2rem;">
        <h3 style="margin-bottom: 1.5rem;">Export Report Data</h3>
        <form action="api/export_csv.php" method="GET">
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 600;">Issue Type</label>
                <select name="issue_type" class="form-control" style="width: 100%; padding: 0.5rem;">
                    <option value="all">All Types</option>
                    <option value="burst_pipe">Burst Pipe</option>
                    <option value="no_water_unexplained">No Water Flow (Unexplained)</option>
                    <option value="water_suspension_bill">Water Suspension (Bill Related)</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 600;">Status</label>
                <select name="status" class="form-control" style="width: 100%; padding: 0.5rem;">
                    <option value="all">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="acknowledged">Acknowledged</option>
                    <option value="resolved">Resolved</option>
                </select>
            </div>
            <div style="margin-bottom: 1rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 600;">Start Date</label>
                    <input type="date" name="start_date" class="form-control" style="width: 100%; padding: 0.5rem;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 600;">End Date</label>
                    <input type="date" name="end_date" class="form-control" style="width: 100%; padding: 0.5rem;">
                </div>
            </div>
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="button" class="btn btn-outline" style="flex: 1;" onclick="document.getElementById('exportModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex: 1;" onclick="setTimeout(()=>document.getElementById('exportModal').style.display='none', 500)">Export CSV</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
