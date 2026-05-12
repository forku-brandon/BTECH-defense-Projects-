<?php
$pageTitle = 'Report a Sighting';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth_check.php';
require_login();
$userId = (int)$_SESSION['user_id'];
$prefillPlate = strtoupper(trim(get('vehicle_plate')));

// My recent sightings
$mySightings = $pdo->prepare("
    SELECT s.*, v.plate_number, v.make, v.model, v.color
    FROM sightings s JOIN vehicles v ON v.id = s.vehicle_id
    WHERE s.reporter_id = ?
    ORDER BY s.sighted_at DESC LIMIT 10
");
$mySightings->execute([$userId]);
$mySightings = $mySightings->fetchAll();

require_once 'includes/header.php';
?>
<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Navigation</div>
      <a href="/ssvias/dashboard.php" class="sidebar-link"><span class="s-icon">📊</span> Dashboard</a>
      <a href="/ssvias/vehicles.php" class="sidebar-link"><span class="s-icon">🚗</span> My Vehicles</a>
      <a href="/ssvias/sightings.php" class="sidebar-link active"><span class="s-icon">👁</span> Report Sighting</a>
      <a href="/ssvias/verify.php" class="sidebar-link"><span class="s-icon">🔍</span> Verify Vehicle</a>
      <a href="/ssvias/notifications.php" class="sidebar-link"><span class="s-icon">🔔</span> Notifications</a>
      <a href="/ssvias/profile.php" class="sidebar-link"><span class="s-icon">👤</span> Profile</a>
    </div>
  </aside>

  <main class="main-content">
    <div class="page-header">
      <h1>👁 Report a Sighting</h1>
      <p>Spotted a stolen vehicle? Report it here and help its owner.</p>
    </div>

    <div class="grid grid-2" style="align-items:start;">
      <!-- Form -->
      <div class="card">
        <div class="card-header">
          <span class="card-title"><span class="icon">📋</span> Sighting Details</span>
        </div>
        <form action="/ssvias/api/sightings.php" method="POST" enctype="multipart/form-data" id="sightingForm">
          <input type="hidden" name="action" value="report">
          <div class="form-group">
            <label>Vehicle Plate Number *</label>
            <input type="text" name="vehicle_plate" placeholder="e.g. NW-1234-A"
              value="<?= e($prefillPlate) ?>"
              style="text-transform:uppercase;font-family:'JetBrains Mono',monospace;letter-spacing:.08em;" required>
            <span class="form-hint">Must be a plate currently reported as stolen.</span>
          </div>
          <div class="form-group">
            <label>Location Where Spotted *</label>
            <input type="text" name="location" placeholder="e.g. Up Station, Bamenda" required>
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea name="description" placeholder="Direction of travel, driver description, any other details..."></textarea>
          </div>
          <div class="form-group">
            <label>Attach Photo (Optional)</label>
            <div class="file-drop">
              <input type="file" name="sighting_image" id="sightingImg" accept="image/*">
              <div class="drop-icon">📸</div>
              <p>Photo of the vehicle or plate</p>
            </div>
            <div class="file-preview" id="sightPreview" style="display:none;margin-top:.75rem;border-radius:8px;overflow:hidden;max-height:180px;"></div>
          </div>
          <div class="alert alert-warning">
            ⚠ Only report genuine sightings. False reports can mislead law enforcement.
          </div>
          <button type="submit" class="btn btn-primary btn-block" id="sightBtn">👁 Submit Sighting Report</button>
        </form>
      </div>

      <!-- My Sightings History -->
      <div>
        <div class="card">
          <div class="card-header">
            <span class="card-title"><span class="icon">📜</span> My Reports</span>
          </div>
          <?php if (empty($mySightings)): ?>
          <div class="empty-state" style="padding:2rem;">
            <div class="empty-icon">👁</div>
            <p>You haven't reported any sightings yet.</p>
          </div>
          <?php else: ?>
          <div style="display:flex;flex-direction:column;gap:.75rem;">
            <?php foreach ($mySightings as $s): ?>
            <div style="background:var(--bg3);border-radius:8px;padding:.85rem 1rem;border:1px solid var(--border);">
              <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.3rem;">
                <span class="vehicle-plate" style="font-size:.82rem;"><?= e($s['plate_number']) ?></span>
                <?= $s['verified'] ? '<span class="badge badge-success">✓ Verified</span>' : '<span class="badge badge-warning">Pending</span>' ?>
              </div>
              <div style="font-size:.83rem;color:var(--text2);">
                📍 <?= e($s['location']) ?><br>
                🕐 <?= time_ago($s['sighted_at']) ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>
</div>

<script>
document.getElementById('sightingForm').addEventListener('submit', function() {
  setLoading(document.getElementById('sightBtn'), true);
});
document.querySelector('[name=vehicle_plate]').addEventListener('input', function() {
  this.value = this.value.toUpperCase();
});
document.getElementById('sightingImg').addEventListener('change', function() {
  const file = this.files[0]; if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    const p = document.getElementById('sightPreview');
    p.innerHTML = `<img src="${e.target.result}" style="width:100%;object-fit:cover;">`;
    p.style.display = 'block';
  };
  reader.readAsDataURL(file);
});
</script>
<?php require_once 'includes/footer.php'; ?>
