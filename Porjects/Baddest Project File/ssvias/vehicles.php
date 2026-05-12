<?php
$pageTitle = 'My Vehicles';
require_once 'includes/db.php';
require_once 'includes/auth_check.php';
require_login();
$userId = (int)$_SESSION['user_id'];

$vehicles = $pdo->prepare("SELECT * FROM vehicles WHERE owner_id = ? ORDER BY created_at DESC");
$vehicles->execute([$userId]);
$vehicles = $vehicles->fetchAll();

require_once 'includes/header.php';
?>
<div class="app-layout">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Navigation</div>
      <a href="/ssvias/dashboard.php" class="sidebar-link"><span class="s-icon">📊</span> Dashboard</a>
      <a href="/ssvias/vehicles.php" class="sidebar-link active"><span class="s-icon">🚗</span> My Vehicles</a>
      <a href="/ssvias/sightings.php" class="sidebar-link"><span class="s-icon">👁</span> Report Sighting</a>
      <a href="/ssvias/verify.php" class="sidebar-link"><span class="s-icon">🔍</span> Verify Vehicle</a>
      <a href="/ssvias/notifications.php" class="sidebar-link"><span class="s-icon">🔔</span> Notifications</a>
      <a href="/ssvias/profile.php" class="sidebar-link"><span class="s-icon">👤</span> Profile</a>
    </div>
  </aside>

  <main class="main-content">
    <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
      <div>
        <h1>🚗 My Vehicles</h1>
        <p>Manage your registered vehicles and report theft.</p>
      </div>
      <a href="/ssvias/add-vehicle.php" class="btn btn-primary">+ Add Vehicle</a>
    </div>

    <?php if (empty($vehicles)): ?>
    <div class="empty-state">
      <div class="empty-icon">🚗</div>
      <h3>No Vehicles Registered</h3>
      <p>Register your vehicles to enable theft reporting and tracking.</p>
      <a href="/ssvias/add-vehicle.php" class="btn btn-primary">+ Register Your First Vehicle</a>
    </div>
    <?php else: ?>
    <div class="grid grid-3">
      <?php foreach ($vehicles as $v): ?>
      <div class="vehicle-card <?= $v['status'] === 'stolen' ? 'stolen' : '' ?>">
        <div class="vehicle-img">
          <?php if ($v['image_path']): ?>
            <img src="/ssvias/<?= e($v['image_path']) ?>" alt="<?= e($v['plate_number']) ?>">
          <?php else: ?>
            🚗
          <?php endif; ?>
        </div>
        <div class="vehicle-info">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;">
            <span class="vehicle-plate"><?= e($v['plate_number']) ?></span>
            <?= status_badge($v['status']) ?>
          </div>
          <div class="vehicle-meta">
            <span>🚙 <?= e($v['year'].' '.$v['make'].' '.$v['model']) ?></span>
            <span>🎨 <?= e($v['color']) ?></span>
            <?php if ($v['vin']): ?><span>🔑 VIN: <?= e($v['vin']) ?></span><?php endif; ?>
            <span>📅 Added <?= time_ago($v['created_at']) ?></span>
          </div>
        </div>
        <div class="vehicle-actions">
          <?php if ($v['status'] === 'active'): ?>
            <button onclick="openReportModal(<?= $v['id'] ?>, '<?= e($v['plate_number']) ?>')" class="btn btn-danger btn-sm" style="flex:1;">⚠ Report Stolen</button>
          <?php elseif ($v['status'] === 'stolen'): ?>
            <form action="/ssvias/api/vehicles.php" method="POST" style="flex:1;" onsubmit="return confirm('Mark as recovered?')">
              <input type="hidden" name="action" value="mark_recovered">
              <input type="hidden" name="vehicle_id" value="<?= $v['id'] ?>">
              <button class="btn btn-success btn-sm btn-block">↩ Mark Recovered</button>
            </form>
          <?php else: ?>
            <span class="badge badge-success" style="flex:1;justify-content:center;">✓ Recovered</span>
          <?php endif; ?>
          <form action="/ssvias/api/vehicles.php" method="POST" onsubmit="return confirm('Delete this vehicle?')">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="vehicle_id" value="<?= $v['id'] ?>">
            <button class="btn btn-outline btn-sm">🗑</button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </main>
</div>

<!-- Report Stolen Modal -->
<div class="modal-overlay" id="reportModal">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">⚠ Report Vehicle as Stolen</span>
      <button class="modal-close" onclick="closeModal('reportModal')">✕</button>
    </div>
    <form action="/ssvias/api/vehicles.php" method="POST">
      <input type="hidden" name="action" value="mark_stolen">
      <input type="hidden" name="vehicle_id" id="reportVehicleId">
      <p style="color:var(--text2);font-size:.875rem;margin-bottom:1.25rem;">Reporting: <strong id="reportPlate" style="color:var(--danger);"></strong></p>
      <div class="form-group">
        <label>Last Known Location</label>
        <input type="text" name="last_seen_location" placeholder="e.g. Commercial Avenue, Bamenda" required>
      </div>
      <div class="form-group">
        <label>Additional Details</label>
        <textarea name="description" placeholder="Any relevant details about the theft..."></textarea>
      </div>
      <div style="display:flex;gap:.75rem;">
        <button type="button" onclick="closeModal('reportModal')" class="btn btn-outline" style="flex:1;">Cancel</button>
        <button type="submit" class="btn btn-danger" style="flex:1;">⚠ Submit Report</button>
      </div>
    </form>
  </div>
</div>

<script>
function openReportModal(id, plate) {
  document.getElementById('reportVehicleId').value = id;
  document.getElementById('reportPlate').textContent = plate;
  openModal('reportModal');
}
</script>
<?php require_once 'includes/footer.php'; ?>
