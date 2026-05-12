<?php
$adminTitle = 'Manage Vehicles';
require_once __DIR__ . '/admin_header.php';

$vehicles = $pdo->query(
    "SELECT v.*, u.name AS owner_name, u.email AS owner_email FROM vehicles v JOIN users u ON u.id = v.owner_id ORDER BY v.created_at DESC"
)->fetchAll();
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1rem;">
  <div>
    <h1 style="font-size:1.6rem;font-weight:700;">🚗 All Vehicles</h1>
    <p style="color:var(--text2);">View and manage vehicle records across the system.</p>
  </div>
  <a href="/ssvias/admin/index.php" class="btn btn-outline btn-sm">← Dashboard</a>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>Plate</th><th>Owner</th><th>Vehicle</th><th>Status</th><th>Registered</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if (empty($vehicles)): ?>
          <tr><td colspan="6" style="text-align:center;color:var(--text2);padding:2rem;">No vehicles found.</td></tr>
        <?php else: ?>
          <?php foreach ($vehicles as $v): ?>
          <tr>
            <td><?= e($v['plate_number']) ?></td>
            <td><?= e($v['owner_name']) ?><br><small><?= e($v['owner_email']) ?></small></td>
            <td><?= e($v['year'].' '.$v['make'].' '.$v['model']) ?></td>
            <td><?= status_badge($v['status']) ?></td>
            <td><?= e(date('M d, Y', strtotime($v['created_at']))) ?></td>
            <td style="display:flex;gap:.35rem;flex-wrap:wrap;">
              <form action="/ssvias/api/admin.php" method="POST" style="display:inline-block;">
                <input type="hidden" name="action" value="update_vehicle_status">
                <input type="hidden" name="vehicle_id" value="<?= $v['id'] ?>">
                <select name="status" style="width:100px;border-radius:8px;padding:.4rem .6rem;background:var(--bg3);border:1px solid var(--border);color:var(--text);">
                  <?php foreach (['active'=>'Active','stolen'=>'Stolen','recovered'=>'Recovered'] as $key => $label): ?>
                    <option value="<?= $key ?>" <?= $v['status'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-outline btn-sm" style="margin-top:.35rem;">Update</button>
              </form>
              <form action="/ssvias/api/admin.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this vehicle?')">
                <input type="hidden" name="action" value="delete_vehicle">
                <input type="hidden" name="vehicle_id" value="<?= $v['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>