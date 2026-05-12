<?php
$adminTitle = 'Admin Dashboard';
require_once __DIR__ . '/admin_header.php';

$totalVehicles = (int)$pdo->query("SELECT COUNT(*) FROM vehicles")->fetchColumn();
$stolenCount = (int)$pdo->query("SELECT COUNT(*) FROM vehicles WHERE status='stolen'")->fetchColumn();
$usersCount = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$sightingsCount = (int)$pdo->query("SELECT COUNT(*) FROM sightings")->fetchColumn();
$pendingReports = (int)$pdo->query("SELECT COUNT(*) FROM stolen_reports WHERE status='pending'")->fetchColumn();
$unverifiedSightings = (int)$pdo->query("SELECT COUNT(*) FROM sightings WHERE verified=0")->fetchColumn();

$recentReports = $pdo->query(
    "SELECT sr.id, sr.last_seen_location, sr.status, sr.reported_at, v.plate_number, v.make, v.model, u.name AS reporter_name
     FROM stolen_reports sr
     JOIN vehicles v ON sr.vehicle_id = v.id
     JOIN users u ON sr.reporter_id = u.id
     ORDER BY sr.reported_at DESC LIMIT 6"
)->fetchAll();
?>

<div class="admin-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1rem;">
  <div class="card">
    <div class="card-title">Total Vehicles</div>
    <div style="font-size:2.5rem;font-weight:800;margin-top:1rem;"><?= $totalVehicles ?></div>
  </div>
  <div class="card">
    <div class="card-title">Stolen Vehicles</div>
    <div style="font-size:2.5rem;font-weight:800;margin-top:1rem;color:var(--danger);"><?= $stolenCount ?></div>
  </div>
  <div class="card">
    <div class="card-title">Total Users</div>
    <div style="font-size:2.5rem;font-weight:800;margin-top:1rem;"><?= $usersCount ?></div>
  </div>
  <div class="card">
    <div class="card-title">Sightings</div>
    <div style="font-size:2.5rem;font-weight:800;margin-top:1rem;color:var(--accent2);"><?= $sightingsCount ?></div>
  </div>
  <div class="card">
    <div class="card-title">Pending Reports</div>
    <div style="font-size:2.5rem;font-weight:800;margin-top:1rem;color:var(--warning);"><?= $pendingReports ?></div>
  </div>
  <div class="card">
    <div class="card-title">Unverified Sightings</div>
    <div style="font-size:2.5rem;font-weight:800;margin-top:1rem;color:var(--danger);"><?= $unverifiedSightings ?></div>
  </div>
</div>

<div class="card" style="margin-top:1.5rem;">
  <div class="card-header"><span class="card-title">Recent Stolen Reports</span></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Plate</th><th>Vehicle</th><th>Location</th><th>Reporter</th><th>Status</th><th>Reported</th></tr></thead>
      <tbody>
        <?php if (empty($recentReports)): ?>
          <tr><td colspan="6" style="text-align:center;color:var(--text2);padding:2rem;">No recent reports available.</td></tr>
        <?php else: ?>
          <?php foreach ($recentReports as $report): ?>
            <tr>
              <td><?= e($report['plate_number']) ?></td>
              <td><?= e($report['make'].' '.$report['model']) ?></td>
              <td><?= e($report['last_seen_location'] ?: 'Unknown') ?></td>
              <td><?= e($report['reporter_name']) ?></td>
              <td><?= status_badge($report['status']) ?></td>
              <td><?= e(date('M d, Y', strtotime($report['reported_at']))) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>