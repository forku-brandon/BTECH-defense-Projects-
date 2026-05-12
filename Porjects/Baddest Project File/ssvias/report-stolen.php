<?php
$pageTitle = 'Report Stolen Vehicle';
require_once 'includes/db.php';
require_once 'includes/auth_check.php';
require_once 'includes/functions.php';
require_login();

$userId = (int)$_SESSION['user_id'];
$availableVehicles = $pdo->prepare("SELECT id, plate_number, make, model, status FROM vehicles WHERE owner_id = ? AND status != 'stolen' ORDER BY created_at DESC");
$availableVehicles->execute([$userId]);
$availableVehicles = $availableVehicles->fetchAll();

require_once 'includes/header.php';
?>
<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Navigation</div>
      <a href="/ssvias/dashboard.php" class="sidebar-link"><span class="s-icon">📊</span> Dashboard</a>
      <a href="/ssvias/vehicles.php" class="sidebar-link"><span class="s-icon">🚗</span> My Vehicles</a>
      <a href="/ssvias/add-vehicle.php" class="sidebar-link"><span class="s-icon">➕</span> Add Vehicle</a>
      <a href="/ssvias/report-stolen.php" class="sidebar-link active"><span class="s-icon">🚨</span> Report Stolen</a>
      <a href="/ssvias/sightings.php" class="sidebar-link"><span class="s-icon">👁</span> Report Sighting</a>
      <a href="/ssvias/verify.php" class="sidebar-link"><span class="s-icon">🔍</span> Verify Vehicle</a>
      <a href="/ssvias/notifications.php" class="sidebar-link"><span class="s-icon">🔔</span> Notifications</a>
      <a href="/ssvias/profile.php" class="sidebar-link"><span class="s-icon">👤</span> Profile</a>
    </div>
  </aside>

  <main class="main-content">
    <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
      <div>
        <h1>🚨 Report Stolen Vehicle</h1>
        <p>Submit a theft report for one of your registered vehicles.</p>
      </div>
    </div>

    <div class="card" style="max-width:760px;">
      <?php if (empty($availableVehicles)): ?>
      <div class="empty-state">
        <div class="empty-icon">🚗</div>
        <h3>No registered vehicles found</h3>
        <p>Register a vehicle first, then use this page to report it stolen.</p>
        <a href="/ssvias/add-vehicle.php" class="btn btn-primary">Add Vehicle</a>
      </div>
      <?php else: ?>
      <form action="/ssvias/api/reports.php" method="POST">
        <input type="hidden" name="action" value="report">
        <div class="form-group">
          <label for="vehicle_id">Select Vehicle</label>
          <select id="vehicle_id" name="vehicle_id" required>
            <option value="">Choose a vehicle</option>
            <?php foreach ($availableVehicles as $vehicle): ?>
              <option value="<?= $vehicle['id'] ?>">
                <?= e($vehicle['plate_number'] . ' — ' . $vehicle['make'] . ' ' . $vehicle['model'] . ' (' . ucfirst($vehicle['status']) . ')') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="last_seen_location">Last Known Location</label>
          <input type="text" id="last_seen_location" name="last_seen_location" placeholder="e.g. Commercial Avenue, Bamenda" required>
        </div>
        <div class="form-group">
          <label for="description">Description</label>
          <textarea id="description" name="description" placeholder="Any details about when and where the vehicle was last seen..."></textarea>
        </div>
        <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
          <button type="submit" class="btn btn-danger">Submit Report</button>
          <a href="/ssvias/vehicles.php" class="btn btn-outline">Back to My Vehicles</a>
        </div>
      </form>
      <?php endif; ?>
    </div>
  </main>
</div>
<?php require_once 'includes/footer.php'; ?>