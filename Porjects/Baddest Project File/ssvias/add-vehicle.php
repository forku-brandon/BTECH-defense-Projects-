<?php
$pageTitle = 'Register Vehicle';
require_once 'includes/db.php';
require_once 'includes/auth_check.php';
require_login();
require_once 'includes/header.php';
$vehicleTypes = ['car' => 'Car', 'motorcycle' => 'Motorcycle', 'truck' => 'Truck', 'bus' => 'Bus', 'other' => 'Other'];
?>
<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Navigation</div>
      <a href="/ssvias/dashboard.php" class="sidebar-link"><span class="s-icon">📊</span> Dashboard</a>
      <a href="/ssvias/vehicles.php" class="sidebar-link"><span class="s-icon">🚗</span> My Vehicles</a>
      <a href="/ssvias/add-vehicle.php" class="sidebar-link active"><span class="s-icon">➕</span> Add Vehicle</a>
      <a href="/ssvias/sightings.php" class="sidebar-link"><span class="s-icon">👁</span> Report Sighting</a>
      <a href="/ssvias/verify.php" class="sidebar-link"><span class="s-icon">🔍</span> Verify Vehicle</a>
      <a href="/ssvias/notifications.php" class="sidebar-link"><span class="s-icon">🔔</span> Notifications</a>
      <a href="/ssvias/profile.php" class="sidebar-link"><span class="s-icon">👤</span> Profile</a>
    </div>
  </aside>

  <main class="main-content">
    <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
      <div>
        <h1>➕ Register a Vehicle</h1>
        <p>Add your vehicle information and upload a photo for fast identification.</p>
      </div>
    </div>

    <div class="card" style="max-width:840px;margin-top:1rem;">
      <form action="/ssvias/api/vehicles.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        <div class="grid grid-2">
          <div class="form-group">
            <label for="plate_number">Plate Number</label>
            <input type="text" id="plate_number" name="plate_number" placeholder="NW-1234-A" required style="text-transform:uppercase;font-family:'JetBrains Mono',monospace;letter-spacing:.08em;">
          </div>
          <div class="form-group">
            <label for="vin">VIN</label>
            <input type="text" id="vin" name="vin" placeholder="17-character VIN">
          </div>
        </div>

        <div class="grid grid-2">
          <div class="form-group">
            <label for="make">Make</label>
            <input type="text" id="make" name="make" placeholder="Toyota" required>
          </div>
          <div class="form-group">
            <label for="model">Model</label>
            <input type="text" id="model" name="model" placeholder="Camry" required>
          </div>
        </div>

        <div class="grid grid-3">
          <div class="form-group">
            <label for="color">Color</label>
            <input type="text" id="color" name="color" placeholder="Silver" required>
          </div>
          <div class="form-group">
            <label for="year">Year</label>
            <input type="number" id="year" name="year" min="1900" max="2100" placeholder="2023" required>
          </div>
          <div class="form-group">
            <label for="type">Vehicle Type</label>
            <select id="type" name="type">
              <?php foreach ($vehicleTypes as $key => $label): ?>
                <option value="<?= e($key) ?>"><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label for="description">Notes</label>
          <textarea id="description" name="description" placeholder="Any additional details about this vehicle..."></textarea>
        </div>

        <div class="form-group">
          <label for="vehicle_image">Vehicle Image</label>
          <div class="file-drop">
            <input type="file" id="vehicle_image" name="vehicle_image" accept="image/*">
            <div class="drop-icon">📷</div>
            <p>Upload a photo of the vehicle or plate.</p>
            <p style="font-size:.78rem;">JPG, PNG, WebP — Max 5MB</p>
          </div>
          <div class="file-preview" style="display:none;"></div>
        </div>

        <div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:center;">
          <button type="submit" class="btn btn-primary">✅ Save Vehicle</button>
          <a href="/ssvias/vehicles.php" class="btn btn-outline">⟵ Back to Vehicles</a>
        </div>
      </form>
    </div>
  </main>
</div>
<?php require_once 'includes/footer.php'; ?>