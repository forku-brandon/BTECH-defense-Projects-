<?php
/**
 * SSVIAS — Shared Header / Navigation
 * Requires $pageTitle to be set before including.
 */
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$_notifCount = 0;
if (is_logged_in()) {
    $_notifCount = unread_notifications($pdo, (int)$_SESSION['user_id']);
}

$_currentPage = basename($_SERVER['PHP_SELF'], '.php');
$_role = current_role();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="SSVIAS — Stolen Vehicle Identification and Alert System for Bamenda, Cameroon. Register, report, and track stolen vehicles.">
  <title><?= e($pageTitle ?? 'SSVIAS') ?> — SSVIAS Bamenda</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<!-- Mobile Nav -->
<nav class="mobile-nav" id="mobileNav">
  <button class="nav-close" onclick="closeMobileNav()">✕</button>
  <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.5rem;">
    <div class="brand-icon" style="width:36px;height:36px;background:linear-gradient(135deg,#2f81f7,#7c3aed);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;">🚔</div>
    <span style="font-weight:800;font-size:1.1rem;">SSVIAS</span>
  </div>
  <div class="m-links">
    <a href="/ssvias/index.php">🏠 Home</a>
    <a href="/ssvias/verify.php">🔍 Verify Vehicle</a>
    <?php if (is_logged_in()): ?>
    <a href="/ssvias/dashboard.php">📊 Dashboard</a>
    <a href="/ssvias/vehicles.php">🚗 My Vehicles</a>
    <a href="/ssvias/sightings.php">👁 Report Sighting</a>
    <a href="/ssvias/notifications.php">🔔 Notifications<?= $_notifCount > 0 ? " ($_notifCount)" : '' ?></a>
    <a href="/ssvias/profile.php">👤 Profile</a>
    <?php if (can('officer')): ?>
    <a href="/ssvias/admin/">⚙ Admin Panel</a>
    <?php endif; ?>
    <a href="/ssvias/api/auth.php?action=logout" style="color:#f85149;">⬅ Logout</a>
    <?php else: ?>
    <a href="/ssvias/login.php">🔐 Login</a>
    <a href="/ssvias/register.php">📝 Register</a>
    <?php endif; ?>
  </div>
</nav>

<!-- Top Navbar -->
<header class="navbar">
  <a href="/ssvias/index.php" class="nav-brand">
    <div class="brand-icon">🚔</div>
    <span>SSVIAS</span>
  </a>

  <nav class="nav-links">
    <a href="/ssvias/index.php" class="<?= $_currentPage === 'index' ? 'active' : '' ?>">Home</a>
    <a href="/ssvias/verify.php" class="<?= $_currentPage === 'verify' ? 'active' : '' ?>">Verify Vehicle</a>
    <?php if (is_logged_in()): ?>
    <a href="/ssvias/dashboard.php" class="<?= $_currentPage === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
    <a href="/ssvias/vehicles.php" class="<?= $_currentPage === 'vehicles' ? 'active' : '' ?>">My Vehicles</a>
    <a href="/ssvias/sightings.php" class="<?= $_currentPage === 'sightings' ? 'active' : '' ?>">Report Sighting</a>
    <?php endif; ?>
  </nav>

  <div class="nav-actions">
    <?php if (is_logged_in()): ?>
      <a href="/ssvias/notifications.php" class="btn btn-outline btn-sm" style="position:relative;" title="Notifications">
        🔔
        <?php if ($_notifCount > 0): ?>
        <span style="position:absolute;top:-4px;right:-4px;background:var(--danger);color:#fff;border-radius:50%;width:18px;height:18px;font-size:.65rem;display:flex;align-items:center;justify-content:center;font-weight:700;"><?= $_notifCount > 9 ? '9+' : $_notifCount ?></span>
        <?php endif; ?>
      </a>
      <?php if (can('officer')): ?>
      <a href="/ssvias/admin/" class="btn btn-outline btn-sm">⚙ Admin</a>
      <?php endif; ?>
      <div style="position:relative;" id="userMenuWrap">
        <button onclick="toggleUserMenu()" class="btn btn-outline btn-sm" style="display:flex;align-items:center;gap:.4rem;">
          <span style="width:24px;height:24px;background:linear-gradient(135deg,var(--accent),#7c3aed);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;color:#fff;"><?= strtoupper(substr($_SESSION['user']['name'] ?? 'U', 0, 1)) ?></span>
          <?= e(explode(' ', $_SESSION['user']['name'] ?? 'User')[0]) ?>
          ▾
        </button>
        <div id="userMenu" style="display:none;position:absolute;right:0;top:calc(100% + 8px);background:var(--bg2);border:1px solid var(--border);border-radius:10px;padding:.5rem;min-width:180px;box-shadow:var(--shadow);z-index:50;">
          <a href="/ssvias/profile.php" style="display:flex;align-items:center;gap:.5rem;padding:.55rem .75rem;border-radius:6px;color:var(--text2);font-size:.875rem;transition:all .2s;">👤 Profile</a>
          <div style="height:1px;background:var(--border);margin:.35rem 0;"></div>
          <a href="/ssvias/api/auth.php?action=logout" style="display:flex;align-items:center;gap:.5rem;padding:.55rem .75rem;border-radius:6px;color:var(--danger);font-size:.875rem;transition:all .2s;">⬅ Logout</a>
        </div>
      </div>
    <?php else: ?>
      <a href="/ssvias/login.php" class="btn btn-outline btn-sm">Login</a>
      <a href="/ssvias/register.php" class="btn btn-primary btn-sm">Register</a>
    <?php endif; ?>
    <button class="mobile-menu-btn" onclick="openMobileNav()">☰</button>
  </div>
</header>

<script>
function toggleUserMenu(){const m=document.getElementById('userMenu');m.style.display=m.style.display==='none'?'block':'none';}
document.addEventListener('click',function(e){const w=document.getElementById('userMenuWrap');if(w&&!w.contains(e.target)){const m=document.getElementById('userMenu');if(m)m.style.display='none';}});
function openMobileNav(){document.getElementById('mobileNav').classList.add('open');}
function closeMobileNav(){document.getElementById('mobileNav').classList.remove('open');}
</script>
