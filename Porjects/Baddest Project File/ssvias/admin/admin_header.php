<?php
/**
 * SSVIAS — Shared Admin Header
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_role('officer');

$_adminPage = basename($_SERVER['PHP_SELF'], '.php');
$_adminRole = current_role();

// Global stats for sidebar badges
$pendingReports = (int)$pdo->query("SELECT COUNT(*) FROM stolen_reports WHERE status='pending'")->fetchColumn();
$unverSightings = (int)$pdo->query("SELECT COUNT(*) FROM sightings WHERE verified=0")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= e($adminTitle ?? 'Admin') ?> — SSVIAS Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
  <style>
    .admin-topbar{background:rgba(13,17,23,0.97);border-bottom:1px solid rgba(248,81,73,0.2);height:60px;display:flex;align-items:center;justify-content:space-between;padding:0 1.5rem;position:sticky;top:0;z-index:100;backdrop-filter:blur(12px);}
    .admin-brand{display:flex;align-items:center;gap:.6rem;font-size:1.05rem;font-weight:700;color:var(--text);}
    .admin-brand .ab-icon{width:32px;height:32px;background:linear-gradient(135deg,#f85149,#7c3aed);border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:.95rem;}
    .admin-badge{background:rgba(248,81,73,0.15);color:var(--danger);border:1px solid rgba(248,81,73,0.3);border-radius:50px;font-size:.7rem;font-weight:700;padding:.1rem .45rem;}
    .admin-layout{display:flex;min-height:calc(100vh - 60px);}
    .admin-side{width:230px;background:var(--bg2);border-right:1px solid var(--border);padding:1.25rem .75rem;position:sticky;top:60px;height:calc(100vh - 60px);overflow-y:auto;flex-shrink:0;}
    .admin-main{flex:1;padding:1.75rem;min-width:0;}
    .a-link{display:flex;align-items:center;gap:.6rem;padding:.5rem .75rem;border-radius:8px;color:var(--text2);font-size:.875rem;transition:all .2s;text-decoration:none;margin-bottom:.15rem;}
    .a-link:hover{background:var(--bg3);color:var(--text);}
    .a-link.active{background:rgba(248,81,73,0.1);color:var(--danger);}
    .a-link .a-badge{margin-left:auto;background:var(--danger);color:#fff;border-radius:50px;font-size:.65rem;font-weight:700;padding:.05rem .4rem;}
    .a-sep{font-size:.68rem;font-weight:600;color:var(--text2);text-transform:uppercase;letter-spacing:.08em;padding:.25rem .75rem;margin:.5rem 0 .2rem;}
  </style>
</head>
<body>
<div class="toast-container" id="toastContainer"></div>

<!-- Admin Topbar -->
<header class="admin-topbar">
  <div class="admin-brand">
    <div class="ab-icon">⚙</div>
    <span>SSVIAS Admin</span>
    <span class="admin-badge">SECURE</span>
  </div>
  <div style="display:flex;align-items:center;gap:.75rem;">
    <a href="/ssvias/index.php" class="btn btn-outline btn-sm">← Public Site</a>
    <span style="font-size:.82rem;color:var(--text2);">
      <?= e($_SESSION['user']['name']) ?>
      <span class="badge badge-blue" style="margin-left:.3rem;font-size:.65rem;"><?= ucfirst($_adminRole) ?></span>
    </span>
    <a href="/ssvias/api/auth.php?action=logout" class="btn btn-danger btn-sm">Logout</a>
  </div>
</header>

<div class="admin-layout">
<!-- Admin Sidebar -->
<aside class="admin-side">
  <div class="a-sep">Overview</div>
  <a href="/ssvias/admin/index.php" class="a-link <?= $_adminPage==='index'?'active':'' ?>">📊 Dashboard</a>

  <div class="a-sep">Manage</div>
  <a href="/ssvias/admin/vehicles.php" class="a-link <?= $_adminPage==='vehicles'?'active':'' ?>">🚗 All Vehicles</a>
  <a href="/ssvias/admin/reports.php" class="a-link <?= $_adminPage==='reports'?'active':'' ?>">
    🚨 Stolen Reports
    <?php if($pendingReports>0): ?><span class="a-badge"><?=$pendingReports?></span><?php endif; ?>
  </a>
  <a href="/ssvias/admin/sightings.php" class="a-link <?= $_adminPage==='sightings'?'active':'' ?>">
    👁 Sightings
    <?php if($unverSightings>0): ?><span class="a-badge"><?=$unverSightings?></span><?php endif; ?>
  </a>
  <?php if(can('admin')): ?>
  <a href="/ssvias/admin/users.php" class="a-link <?= $_adminPage==='users'?'active':'' ?>">👥 Users</a>
  <?php endif; ?>

  <div class="a-sep">Reports</div>
  <a href="/ssvias/admin/generate-report.php" class="a-link <?= $_adminPage==='generate-report'?'active':'' ?>">📄 Generate Report</a>
</aside>

<main class="admin-main">
