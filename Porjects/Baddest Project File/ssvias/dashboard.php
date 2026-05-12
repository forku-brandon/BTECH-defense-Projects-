<?php
$pageTitle = 'Dashboard';
require_once 'includes/db.php';
require_once 'includes/auth_check.php';
require_login();
$userId   = (int)$_SESSION['user_id'];
$userName = explode(' ', $_SESSION['user']['name'])[0];
$role     = current_role();

// Stats
$stmtV = $pdo->prepare("SELECT COUNT(*) FROM vehicles WHERE owner_id=?"); $stmtV->execute([$userId]); $myVehicles = (int)$stmtV->fetchColumn();
$stmtS = $pdo->prepare("SELECT COUNT(*) FROM vehicles WHERE owner_id=? AND status='stolen'"); $stmtS->execute([$userId]); $myStolen = (int)$stmtS->fetchColumn();
$stmtR = $pdo->prepare("SELECT COUNT(*) FROM vehicles WHERE owner_id=? AND status='recovered'"); $stmtR->execute([$userId]); $myRecovered = (int)$stmtR->fetchColumn();
$stmtSg = $pdo->prepare("SELECT COUNT(*) FROM sightings WHERE reporter_id=?"); $stmtSg->execute([$userId]); $mySightings = (int)$stmtSg->fetchColumn();
$stmtN = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0"); $stmtN->execute([$userId]); $unread = (int)$stmtN->fetchColumn();

// Recent vehicles
$recentVehicles = $pdo->prepare("SELECT * FROM vehicles WHERE owner_id=? ORDER BY created_at DESC LIMIT 4"); $recentVehicles->execute([$userId]); $recentVehicles = $recentVehicles->fetchAll();

// Recent notifications
$recentNotifs = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 5"); $recentNotifs->execute([$userId]); $recentNotifs = $recentNotifs->fetchAll();

// Recent stolen reports for my vehicles
$myReports = $pdo->prepare("
    SELECT sr.*, v.plate_number, v.make, v.model FROM stolen_reports sr
    JOIN vehicles v ON v.id = sr.vehicle_id
    WHERE v.owner_id=? ORDER BY sr.reported_at DESC LIMIT 5
"); $myReports->execute([$userId]); $myReports = $myReports->fetchAll();

require_once 'includes/header.php';
?>
<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Main</div>
      <a href="/ssvias/dashboard.php" class="sidebar-link active"><span class="s-icon">📊</span> Dashboard</a>
      <a href="/ssvias/vehicles.php" class="sidebar-link"><span class="s-icon">🚗</span> My Vehicles</a>
      <a href="/ssvias/add-vehicle.php" class="sidebar-link"><span class="s-icon">➕</span> Add Vehicle</a>
    </div>
    <div class="sidebar-section">
      <div class="sidebar-label">Community</div>
      <a href="/ssvias/sightings.php" class="sidebar-link"><span class="s-icon">👁</span> Report Sighting</a>
      <a href="/ssvias/verify.php" class="sidebar-link"><span class="s-icon">🔍</span> Verify Vehicle</a>
    </div>
    <div class="sidebar-section">
      <div class="sidebar-label">Account</div>
      <a href="/ssvias/notifications.php" class="sidebar-link"><span class="s-icon">🔔</span> Notifications <?php if($unread>0): ?><span style="margin-left:auto;background:var(--danger);color:#fff;border-radius:50px;padding:.05rem .4rem;font-size:.7rem;"><?=$unread?></span><?php endif; ?></a>
      <a href="/ssvias/profile.php" class="sidebar-link"><span class="s-icon">👤</span> Profile</a>
      <?php if(can('officer')): ?><a href="/ssvias/admin/" class="sidebar-link"><span class="s-icon">⚙</span> Admin Panel</a><?php endif; ?>
    </div>
  </aside>

  <main class="main-content">
    <!-- Welcome -->
    <div style="margin-bottom:1.75rem;">
      <h1 style="font-size:1.5rem;font-weight:700;">👋 Welcome back, <?= e($userName) ?>!</h1>
      <p style="color:var(--text2);font-size:.9rem;">Here's an overview of your vehicles and activity.</p>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-4" style="margin-bottom:1.75rem;">
      <div class="stat-card">
        <div class="stat-icon blue">🚗</div>
        <div class="stat-info"><div class="stat-val" data-counter="<?=$myVehicles?>">0</div><div class="stat-lbl">My Vehicles</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red">🚨</div>
        <div class="stat-info"><div class="stat-val" data-counter="<?=$myStolen?>" style="color:var(--danger);">0</div><div class="stat-lbl">Stolen</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green">✅</div>
        <div class="stat-info"><div class="stat-val" data-counter="<?=$myRecovered?>" style="color:var(--success);">0</div><div class="stat-lbl">Recovered</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon yellow">👁</div>
        <div class="stat-info"><div class="stat-val" data-counter="<?=$mySightings?>">0</div><div class="stat-lbl">Sightings Filed</div></div>
      </div>
    </div>

    <!-- Alerts for stolen vehicles -->
    <?php if($myStolen > 0): ?>
    <div class="alert alert-danger" style="margin-bottom:1.5rem;font-size:.9rem;">
      🚨 You have <strong><?=$myStolen?></strong> stolen vehicle<?=$myStolen>1?'s':''?> currently reported. <a href="/ssvias/vehicles.php" style="color:var(--danger);font-weight:600;">View details →</a>
    </div>
    <?php endif; ?>

    <div class="grid grid-2" style="align-items:start;">
      <!-- My Vehicles -->
      <div class="card">
        <div class="card-header">
          <span class="card-title"><span class="icon">🚗</span> My Vehicles</span>
          <a href="/ssvias/vehicles.php" class="btn btn-outline btn-sm">View All</a>
        </div>
        <?php if(empty($recentVehicles)): ?>
        <div class="empty-state" style="padding:1.5rem;">
          <div class="empty-icon">🚗</div>
          <p>No vehicles yet.</p>
          <a href="/ssvias/add-vehicle.php" class="btn btn-primary btn-sm">+ Add Vehicle</a>
        </div>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:.65rem;">
          <?php foreach($recentVehicles as $v): ?>
          <div style="display:flex;align-items:center;gap:.85rem;padding:.7rem;background:var(--bg3);border-radius:8px;border:1px solid var(--border);">
            <div style="font-size:1.6rem;"><?= $v['type']==='motorcycle'?'🏍':($v['type']==='truck'?'🚚':($v['type']==='bus'?'🚌':'🚗')) ?></div>
            <div style="flex:1;min-width:0;">
              <div style="font-family:'JetBrains Mono',monospace;font-weight:700;font-size:.9rem;"><?=e($v['plate_number'])?></div>
              <div style="font-size:.78rem;color:var(--text2);"><?=e($v['year'].' '.$v['make'].' '.$v['model'])?></div>
            </div>
            <?= status_badge($v['status']) ?>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Recent Notifications -->
      <div class="card">
        <div class="card-header">
          <span class="card-title"><span class="icon">🔔</span> Notifications</span>
          <a href="/ssvias/notifications.php" class="btn btn-outline btn-sm">View All</a>
        </div>
        <?php if(empty($recentNotifs)): ?>
        <div class="empty-state" style="padding:1.5rem;"><div class="empty-icon">🔔</div><p>No notifications yet.</p></div>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;">
          <?php $typeIcons=['alert'=>'🚨','info'=>'ℹ','success'=>'✅','warning'=>'⚠']; foreach($recentNotifs as $n): ?>
          <div style="display:flex;gap:.75rem;padding:.7rem 0;border-bottom:1px solid var(--border);">
            <span style="font-size:1.1rem;"><?= $typeIcons[$n['type']] ?? 'ℹ' ?></span>
            <div>
              <div style="font-size:.83rem;font-weight:<?=$n['is_read']?'400':'600'?>;color:<?=$n['is_read']?'var(--text2)':'var(--text)'?>;"><?=e($n['title'])?></div>
              <div style="font-size:.75rem;color:var(--text2);"><?=time_ago($n['created_at'])?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="card" style="margin-top:1.25rem;">
      <div class="card-header"><span class="card-title"><span class="icon">⚡</span> Quick Actions</span></div>
      <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
        <a href="/ssvias/add-vehicle.php" class="btn btn-primary">🚗 Register Vehicle</a>
        <a href="/ssvias/verify.php" class="btn btn-outline">🔍 Verify a Plate</a>
        <a href="/ssvias/sightings.php" class="btn btn-outline">👁 Report Sighting</a>
        <a href="/ssvias/notifications.php" class="btn btn-outline">🔔 View Notifications</a>
      </div>
    </div>
  </main>
</div>
<?php require_once 'includes/footer.php'; ?>
