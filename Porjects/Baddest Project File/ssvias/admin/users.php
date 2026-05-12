<?php
$adminTitle = 'User Management';
require_once __DIR__ . '/admin_header.php';

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1rem;">
  <div>
    <h1 style="font-size:1.6rem;font-weight:700;">👥 Users</h1>
    <p style="color:var(--text2);">Manage system users, roles, and account activation.</p>
  </div>
  <a href="/ssvias/admin/index.php" class="btn btn-outline btn-sm">← Dashboard</a>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if (empty($users)): ?>
          <tr><td colspan="6" style="text-align:center;color:var(--text2);padding:2rem;">No users registered yet.</td></tr>
        <?php else: ?>
          <?php foreach ($users as $user): ?>
          <tr>
            <td><?= e($user['name']) ?></td>
            <td><?= e($user['email']) ?></td>
            <td>
              <form action="/ssvias/api/admin.php" method="POST" style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
                <input type="hidden" name="action" value="update_user_role">
                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                <select name="role" style="border-radius:8px;padding:.35rem .65rem;background:var(--bg3);border:1px solid var(--border);color:var(--text);">
                  <?php foreach (['admin'=>'Admin','officer'=>'Officer','owner'=>'Owner','public'=>'Public'] as $roleKey => $roleLabel): ?>
                    <option value="<?= $roleKey ?>" <?= $user['role'] === $roleKey ? 'selected' : '' ?>><?= $roleLabel ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-outline btn-sm">Save</button>
              </form>
            </td>
            <td><?= $user['is_active'] ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-gray">Disabled</span>' ?></td>
            <td><?= e(date('M d, Y', strtotime($user['created_at']))) ?></td>
            <td>
              <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                <form action="/ssvias/api/admin.php" method="POST" onsubmit="return confirm('Toggle this user account status?')">
                  <input type="hidden" name="action" value="toggle_user">
                  <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                  <button type="submit" class="btn btn-outline btn-sm"><?= $user['is_active'] ? 'Disable' : 'Enable' ?></button>
                </form>
              <?php else: ?>
                <span style="color:var(--text2);font-size:.8rem;">Current user</span>
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