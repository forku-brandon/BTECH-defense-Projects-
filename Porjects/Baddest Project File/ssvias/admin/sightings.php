<?php
$adminTitle = 'Sightings';
require_once __DIR__ . '/admin_header.php';

$sightings = $pdo->query(
    "SELECT s.*, v.plate_number, v.make, v.model, u.name AS reporter_name
     FROM sightings s
     JOIN vehicles v ON v.id = s.vehicle_id
     LEFT JOIN users u ON u.id = s.reporter_id
     ORDER BY s.sighted_at DESC"
)->fetchAll();
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1rem;">
  <div>
    <h1 style="font-size:1.6rem;font-weight:700;">👁 Sightings</h1>
    <p style="color:var(--text2);">Review reported sightings and verify those ready for action.</p>
  </div>
  <a href="/ssvias/admin/index.php" class="btn btn-outline btn-sm">← Dashboard</a>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>Plate</th><th>Reporter</th><th>Vehicle</th><th>Location</th><th>Submitted</th><th>Verified</th><th>Action</th></tr></thead>
      <tbody>
        <?php if (empty($sightings)): ?>
          <tr><td colspan="7" style="text-align:center;color:var(--text2);padding:2rem;">No sighting reports found.</td></tr>
        <?php else: ?>
          <?php foreach ($sightings as $s): ?>
          <tr>
            <td><?= e($s['plate_number']) ?></td>
            <td><?= e($s['reporter_name'] ?: 'Guest') ?></td>
            <td><?= e($s['make'].' '.$s['model']) ?></td>
            <td><?= e($s['location']) ?></td>
            <td><?= e(date('M d, Y', strtotime($s['sighted_at']))) ?></td>
            <td><?= $s['verified'] ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-warning">No</span>' ?></td>
            <td>
              <?php if (!$s['verified']): ?>
              <form action="/ssvias/api/admin.php" method="POST" onsubmit="return confirm('Verify this sighting?')">
                <input type="hidden" name="action" value="verify_sighting">
                <input type="hidden" name="sighting_id" value="<?= $s['id'] ?>">
                <button type="submit" class="btn btn-success btn-sm">Verify</button>
              </form>
              <?php else: ?>
              <span class="badge badge-success">Verified</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>