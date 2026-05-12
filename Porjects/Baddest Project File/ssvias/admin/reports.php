<?php
$adminTitle = 'Stolen Reports';
require_once __DIR__ . '/admin_header.php';

$reports = $pdo->query(
    "SELECT sr.*, v.plate_number, v.make, v.model, v.color, u.name AS owner_name
     FROM stolen_reports sr
     JOIN vehicles v ON v.id = sr.vehicle_id
     JOIN users u ON u.id = v.owner_id
     ORDER BY sr.reported_at DESC"
)->fetchAll();
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1rem;">
  <div>
    <h1 style="font-size:1.6rem;font-weight:700;">🚨 Stolen Reports</h1>
    <p style="color:var(--text2);">Review and verify theft reports submitted by vehicle owners.</p>
  </div>
  <a href="/ssvias/admin/index.php" class="btn btn-outline btn-sm">← Dashboard</a>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>Plate</th><th>Owner</th><th>Vehicle</th><th>Status</th><th>Last Seen</th><th>Reported</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if (empty($reports)): ?>
          <tr><td colspan="7" style="text-align:center;color:var(--text2);padding:2rem;">No stolen reports found.</td></tr>
        <?php else: ?>
          <?php foreach ($reports as $r): ?>
          <tr>
            <td><?= e($r['plate_number']) ?></td>
            <td><?= e($r['owner_name']) ?></td>
            <td><?= e($r['make'].' '.$r['model'].' '.$r['color']) ?></td>
            <td><?= status_badge($r['status']) ?></td>
            <td><?= e($r['last_seen_location'] ?: 'Unknown') ?></td>
            <td><?= e(date('M d, Y', strtotime($r['reported_at']))) ?></td>
            <td style="display:flex;gap:.35rem;flex-wrap:wrap;">
              <?php if ($r['status'] !== 'verified'): ?>
              <form action="/ssvias/api/admin.php" method="POST">
                <input type="hidden" name="action" value="verify_report">
                <input type="hidden" name="report_id" value="<?= $r['id'] ?>">
                <input type="hidden" name="vehicle_id" value="<?= $r['vehicle_id'] ?>">
                <button type="submit" class="btn btn-success btn-sm">Verify</button>
              </form>
              <?php endif; ?>
              <?php if ($r['status'] !== 'closed'): ?>
              <form action="/ssvias/api/admin.php" method="POST" onsubmit="return confirm('Close this report and mark vehicle recovered?')">
                <input type="hidden" name="action" value="close_report">
                <input type="hidden" name="report_id" value="<?= $r['id'] ?>">
                <input type="hidden" name="vehicle_id" value="<?= $r['vehicle_id'] ?>">
                <button type="submit" class="btn btn-outline btn-sm">Close</button>
              </form>
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