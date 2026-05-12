<?php
$pageTitle = 'Profile';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth_check.php';
require_login();
$userId = (int)$_SESSION['user_id'];

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim(post('name'));
    $phone = trim(post('phone'));
    $email = trim(post('email'));
    $newPw = post('new_password');
    $oldPw = post('current_password');
    $err = $msg = '';

    if (empty($name) || empty($email)) { $err = 'Name and email are required.'; }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $err = 'Invalid email address.'; }
    else {
        // Check email unique (not own)
        $chk = $pdo->prepare("SELECT id FROM users WHERE email=? AND id!=?");
        $chk->execute([$email, $userId]);
        if ($chk->fetch()) { $err = 'Email already used by another account.'; }
        else {
            if (!empty($newPw)) {
                $current = $pdo->prepare("SELECT password FROM users WHERE id=?");
                $current->execute([$userId]);
                $hash = $current->fetchColumn();
                if (!password_verify($oldPw, $hash)) { $err = 'Current password is incorrect.'; }
                elseif (strlen($newPw) < 6) { $err = 'New password must be at least 6 characters.'; }
                else {
                    $pdo->prepare("UPDATE users SET name=?,phone=?,email=?,password=? WHERE id=?")
                        ->execute([$name, $phone, $email, password_hash($newPw, PASSWORD_BCRYPT), $userId]);
                    $msg = 'Profile and password updated!';
                }
            } else {
                $pdo->prepare("UPDATE users SET name=?,phone=?,email=? WHERE id=?")->execute([$name,$phone,$email,$userId]);
                $msg = 'Profile updated successfully!';
            }
            if (empty($err)) {
                $_SESSION['user']['name']  = $name;
                $_SESSION['user']['email'] = $email;
                $_SESSION['user']['phone'] = $phone;
            }
        }
    }
}

$user = $pdo->prepare("SELECT * FROM users WHERE id=?");
$user->execute([$userId]);
$user = $user->fetch();
require_once 'includes/header.php';
?>
<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Navigation</div>
      <a href="/ssvias/dashboard.php" class="sidebar-link"><span class="s-icon">📊</span> Dashboard</a>
      <a href="/ssvias/vehicles.php" class="sidebar-link"><span class="s-icon">🚗</span> My Vehicles</a>
      <a href="/ssvias/sightings.php" class="sidebar-link"><span class="s-icon">👁</span> Report Sighting</a>
      <a href="/ssvias/verify.php" class="sidebar-link"><span class="s-icon">🔍</span> Verify Vehicle</a>
      <a href="/ssvias/notifications.php" class="sidebar-link"><span class="s-icon">🔔</span> Notifications</a>
      <a href="/ssvias/profile.php" class="sidebar-link active"><span class="s-icon">👤</span> Profile</a>
    </div>
  </aside>

  <main class="main-content">
    <div class="page-header">
      <h1>👤 My Profile</h1>
      <p>Manage your account information and security settings.</p>
    </div>

    <?php if (!empty($err)): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>
    <?php if (!empty($msg)): ?><div class="alert alert-success">✅ <?= e($msg) ?></div><?php endif; ?>

    <div class="grid grid-2" style="align-items:start;">
      <!-- Profile Info -->
      <div class="card">
        <div class="card-header"><span class="card-title"><span class="icon">📋</span> Account Details</span></div>

        <!-- Avatar -->
        <div style="text-align:center;margin-bottom:1.5rem;">
          <div style="width:80px;height:80px;background:linear-gradient(135deg,var(--accent),#7c3aed);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:800;color:#fff;margin:0 auto .75rem;">
            <?= strtoupper(substr($user['name'], 0, 1)) ?>
          </div>
          <div style="font-weight:700;"><?= e($user['name']) ?></div>
          <div style="color:var(--text2);font-size:.82rem;"><?= e($user['email']) ?></div>
          <span class="badge badge-blue" style="margin-top:.4rem;">
            <?= ['admin'=>'⚙ Admin','officer'=>'👮 Officer','owner'=>'🚗 Owner','public'=>'👤 Public'][$user['role']] ?? $user['role'] ?>
          </span>
        </div>

        <form action="/ssvias/profile.php" method="POST">
          <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" value="<?= e($user['name']) ?>" required>
          </div>
          <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" value="<?= e($user['email']) ?>" required>
          </div>
          <div class="form-group">
            <label>Phone Number</label>
            <input type="tel" name="phone" value="<?= e($user['phone'] ?? '') ?>" placeholder="+237600000000">
          </div>
          <div style="background:var(--bg3);border-radius:8px;padding:1rem;margin-bottom:1rem;">
            <div style="font-size:.82rem;font-weight:600;color:var(--text2);margin-bottom:.75rem;">🔒 Change Password (leave blank to keep current)</div>
            <div class="form-group">
              <label>Current Password</label>
              <input type="password" name="current_password" placeholder="Enter current password">
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label>New Password</label>
              <input type="password" name="new_password" placeholder="Min 6 characters">
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-block">💾 Save Changes</button>
        </form>
      </div>

      <!-- Account Stats -->
      <div>
        <div class="card" style="margin-bottom:1.25rem;">
          <div class="card-header"><span class="card-title"><span class="icon">📊</span> Account Stats</span></div>
          <?php
          $stV=$pdo->prepare("SELECT COUNT(*) FROM vehicles WHERE owner_id=?"); $stV->execute([$userId]);
          $stS=$pdo->prepare("SELECT COUNT(*) FROM sightings WHERE reporter_id=?"); $stS->execute([$userId]);
          $stN=$pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=?"); $stN->execute([$userId]);
          ?>
          <div style="display:flex;flex-direction:column;gap:.65rem;">
            <div style="display:flex;justify-content:space-between;padding:.6rem .85rem;background:var(--bg3);border-radius:8px;">
              <span style="color:var(--text2);font-size:.875rem;">🚗 Vehicles Registered</span>
              <strong><?= $stV->fetchColumn() ?></strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding:.6rem .85rem;background:var(--bg3);border-radius:8px;">
              <span style="color:var(--text2);font-size:.875rem;">👁 Sightings Reported</span>
              <strong><?= $stS->fetchColumn() ?></strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding:.6rem .85rem;background:var(--bg3);border-radius:8px;">
              <span style="color:var(--text2);font-size:.875rem;">🔔 Notifications Received</span>
              <strong><?= $stN->fetchColumn() ?></strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding:.6rem .85rem;background:var(--bg3);border-radius:8px;">
              <span style="color:var(--text2);font-size:.875rem;">📅 Member Since</span>
              <strong><?= date('M Y', strtotime($user['created_at'])) ?></strong>
            </div>
          </div>
        </div>

        <div class="card" style="border-color:rgba(248,81,73,0.25);">
          <div class="card-header"><span class="card-title" style="color:var(--danger);"><span class="icon">⚠</span> Danger Zone</span></div>
          <p style="font-size:.83rem;color:var(--text2);margin-bottom:1rem;">These actions are permanent and cannot be undone.</p>
          <a href="/ssvias/api/auth.php?action=logout" class="btn btn-danger btn-block btn-sm">⬅ Logout of Account</a>
        </div>
      </div>
    </div>
  </main>
</div>
<?php require_once 'includes/footer.php'; ?>
