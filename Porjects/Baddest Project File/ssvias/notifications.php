<?php
$pageTitle = 'Notifications';
require_once 'includes/db.php';
require_once 'includes/auth_check.php';
require_login();
$userId = (int)$_SESSION['user_id'];

$notifs = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC");
$notifs->execute([$userId]);
$notifs = $notifs->fetchAll();
$unread = count(array_filter($notifs, fn($n) => !$n['is_read']));

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
      <a href="/ssvias/notifications.php" class="sidebar-link active"><span class="s-icon">🔔</span> Notifications</a>
      <a href="/ssvias/profile.php" class="sidebar-link"><span class="s-icon">👤</span> Profile</a>
    </div>
  </aside>

  <main class="main-content">
    <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
      <div>
        <h1>🔔 Notifications <?php if($unread>0): ?><span style="font-size:1rem;background:var(--danger);color:#fff;border-radius:50px;padding:.1rem .55rem;font-weight:600;"><?=$unread?></span><?php endif; ?></h1>
        <p>Alerts and updates for your vehicles and reports.</p>
      </div>
      <?php if($unread>0): ?>
      <form action="/ssvias/api/notifications.php" method="POST">
        <input type="hidden" name="action" value="mark_all_read">
        <button class="btn btn-outline btn-sm">✓ Mark All Read</button>
      </form>
      <?php endif; ?>
    </div>

    <div class="card" style="padding:0;overflow:hidden;">
      <?php if(empty($notifs)): ?>
      <div class="empty-state"><div class="empty-icon">🔔</div><h3>No Notifications</h3><p>You're all caught up!</p></div>
      <?php else: ?>
      <?php
      $typeIcons = ['alert'=>'🚨','info'=>'ℹ','success'=>'✅','warning'=>'⚠'];
      $typeBg    = ['alert'=>'rgba(248,81,73,0.12)','info'=>'rgba(47,129,247,0.12)','success'=>'rgba(63,185,80,0.12)','warning'=>'rgba(210,153,34,0.12)'];
      foreach($notifs as $n): ?>
      <div class="notif-item <?= !$n['is_read'] ? 'unread' : '' ?>">
        <div class="notif-icon" style="background:<?= $typeBg[$n['type']] ?? 'var(--bg3)' ?>;">
          <?= $typeIcons[$n['type']] ?? 'ℹ' ?>
        </div>
        <div class="notif-body" style="flex:1;">
          <div class="notif-title"><?= e($n['title']) ?></div>
          <div class="notif-msg"><?= e($n['message']) ?></div>
          <div class="notif-time">🕐 <?= time_ago($n['created_at']) ?></div>
        </div>
        <div style="display:flex;flex-direction:column;gap:.35rem;flex-shrink:0;">
          <?php if(!$n['is_read']): ?>
          <form action="/ssvias/api/notifications.php" method="POST">
            <input type="hidden" name="action" value="mark_read">
            <input type="hidden" name="id" value="<?= $n['id'] ?>">
            <button class="btn btn-outline btn-sm" title="Mark read">✓</button>
          </form>
          <?php endif; ?>
          <form action="/ssvias/api/notifications.php" method="POST" onsubmit="return confirm('Delete this notification?')">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $n['id'] ?>">
            <button class="btn btn-outline btn-sm" title="Delete" style="color:var(--danger);">🗑</button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </main>
</div>
<?php require_once 'includes/footer.php'; ?>
