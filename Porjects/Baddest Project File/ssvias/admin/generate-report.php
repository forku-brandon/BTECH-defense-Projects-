<?php
$adminTitle = 'Generate Report';
require_once __DIR__ . '/admin_header.php';

$totalVehicles = (int)$pdo->query("SELECT COUNT(*) FROM vehicles")->fetchColumn();
$stolenCount = (int)$pdo->query("SELECT COUNT(*) FROM vehicles WHERE status='stolen'")->fetchColumn();
$recoveredCount = (int)$pdo->query("SELECT COUNT(*) FROM vehicles WHERE status='recovered'")->fetchColumn();
$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$pendingReports = (int)$pdo->query("SELECT COUNT(*) FROM stolen_reports WHERE status='pending'")->fetchColumn();
$totalSightings = (int)$pdo->query("SELECT COUNT(*) FROM sightings")->fetchColumn();

$latestActivities = $pdo->query(
    "SELECT 'Vehicle' AS type, plate_number AS reference, created_at AS date FROM vehicles UNION ALL
     SELECT 'Report', CONCAT('Plate ', v.plate_number), reported_at FROM stolen_reports sr JOIN vehicles v ON v.id = sr.vehicle_id
     UNION ALL SELECT 'Sighting', CONCAT('Plate ', v.plate_number), sighted_at FROM sightings s JOIN vehicles v ON v.id = s.vehicle_id
     ORDER BY date DESC LIMIT 8"
)->fetchAll();
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1rem;">
  <div>
    <h1 style="font-size:1.6rem;font-weight:700;">📄 Generate Report</h1>
    <p style="color:var(--text2);">Export system summary data or print the latest activity report.</p>
  </div>
  <button onclick="window.print()" class="btn btn-primary btn-sm">Print Report</button>
</div>

<div class="grid grid-3" style="margin-bottom:1.5rem;">
  <div class="card"><div class="card-title">Total Vehicles</div><div style="font-size:2.3rem;font-weight:800;margin-top:1rem;"><?= $totalVehicles ?></div></div>
  <div class="card"><div class="card-title">Stolen Vehicles</div><div style="font-size:2.3rem;font-weight:800;margin-top:1rem;color:var(--danger);"><?= $stolenCount ?></div></div>
  <div class="card"><div class="card-title">Recovered Vehicles</div><div style="font-size:2.3rem;font-weight:800;margin-top:1rem;color:var(--success);"><?= $recoveredCount ?></div></div>
  <div class="card"><div class="card-title">Total Users</div><div style="font-size:2.3rem;font-weight:800;margin-top:1rem;"><?= $totalUsers ?></div></div>
  <div class="card"><div class="card-title">Pending Reports</div><div style="font-size:2.3rem;font-weight:800;margin-top:1rem;color:var(--warning);"><?= $pendingReports ?></div></div>
  <div class="card"><div class="card-title">Total Sightings</div><div style="font-size:2.3rem;font-weight:800;margin-top:1rem;color:var(--accent2);"><?= $totalSightings ?></div></div>
</div>

<div class="card">
  <div class="card-header"><span class="card-title">Latest Activity</span></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Type</th><th>Reference</th><th>Date</th></tr></thead>
      <tbody>
        <?php if (empty($latestActivities)): ?>
          <tr><td colspan="3" style="text-align:center;color:var(--text2);padding:2rem;">No recent activity to show.</td></tr>
        <?php else: ?>
          <?php foreach ($latestActivities as $item): ?>
          <tr>
            <td><?= e($item['type']) ?></td>
            <td><?= e($item['reference']) ?></td>
            <td><?= e(date('M d, Y H:i', strtotime($item['date']))) ?></td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>