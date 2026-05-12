<?php
/**
 * SSVIAS — Admin API
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_role('officer');

$action  = post('action');
$adminId = (int)$_SESSION['user_id'];

function logAction(PDO $pdo, int $adminId, string $action, string $type, int $targetId): void {
    $pdo->prepare("INSERT INTO admin_logs (admin_id, action, target_type, target_id) VALUES (?,?,?,?)")
        ->execute([$adminId, $action, $type, $targetId]);
}

// ─── VEHICLE STATUS ───────────────────────────────────────
if ($action === 'update_vehicle_status') {
    $vehicleId = (int)post('vehicle_id');
    $status    = post('status');
    if (!in_array($status, ['active','stolen','recovered'])) {
        header('Location: /ssvias/admin/vehicles.php?err=' . urlencode('Invalid status.')); exit;
    }
    $pdo->prepare("UPDATE vehicles SET status=? WHERE id=?")->execute([$status, $vehicleId]);
    logAction($pdo, $adminId, "Set vehicle status to $status", 'vehicle', $vehicleId);
    header('Location: /ssvias/admin/vehicles.php?msg=' . urlencode('Vehicle status updated.'));
    exit;
}

// ─── DELETE VEHICLE ───────────────────────────────────────
if ($action === 'delete_vehicle') {
    $vehicleId = (int)post('vehicle_id');
    $pdo->prepare("DELETE FROM vehicles WHERE id=?")->execute([$vehicleId]);
    logAction($pdo, $adminId, 'Deleted vehicle', 'vehicle', $vehicleId);
    header('Location: /ssvias/admin/vehicles.php?msg=' . urlencode('Vehicle deleted.'));
    exit;
}

// ─── VERIFY REPORT ────────────────────────────────────────
if ($action === 'verify_report') {
    $reportId  = (int)post('report_id');
    $vehicleId = (int)post('vehicle_id');
    $pdo->prepare("UPDATE stolen_reports SET status='verified' WHERE id=?")->execute([$reportId]);
    $pdo->prepare("UPDATE vehicles SET status='stolen' WHERE id=?")->execute([$vehicleId]);
    // Notify owner
    $owner = $pdo->prepare("SELECT owner_id, plate_number FROM vehicles WHERE id=?");
    $owner->execute([$vehicleId]);
    $ov = $owner->fetch();
    if ($ov) create_notification($pdo, $ov['owner_id'], 'Report Verified', "Your stolen vehicle report for {$ov['plate_number']} has been verified by authorities.", 'success');
    logAction($pdo, $adminId, 'Verified stolen report', 'report', $reportId);
    header('Location: /ssvias/admin/reports.php?msg=' . urlencode('Report verified.'));
    exit;
}

// ─── CLOSE REPORT ─────────────────────────────────────────
if ($action === 'close_report') {
    $reportId  = (int)post('report_id');
    $vehicleId = (int)post('vehicle_id');
    $pdo->prepare("UPDATE stolen_reports SET status='closed' WHERE id=?")->execute([$reportId]);
    $pdo->prepare("UPDATE vehicles SET status='recovered' WHERE id=?")->execute([$vehicleId]);
    logAction($pdo, $adminId, 'Closed stolen report', 'report', $reportId);
    header('Location: /ssvias/admin/reports.php?msg=' . urlencode('Report closed and vehicle marked recovered.'));
    exit;
}

// ─── USER MANAGEMENT ─────────────────────────────────────
if ($action === 'update_user_role') {
    require_role('admin');
    $targetUser = (int)post('user_id');
    $role       = post('role');
    if (!in_array($role, ['admin','officer','owner','public'])) {
        header('Location: /ssvias/admin/users.php?err=' . urlencode('Invalid role.')); exit;
    }
    $pdo->prepare("UPDATE users SET role=? WHERE id=?")->execute([$role, $targetUser]);
    logAction($pdo, $adminId, "Changed user role to $role", 'user', $targetUser);
    header('Location: /ssvias/admin/users.php?msg=' . urlencode('User role updated.'));
    exit;
}

if ($action === 'toggle_user') {
    require_role('admin');
    $targetUser = (int)post('user_id');
    $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id=?")->execute([$targetUser]);
    logAction($pdo, $adminId, 'Toggled user active status', 'user', $targetUser);
    header('Location: /ssvias/admin/users.php?msg=' . urlencode('User status updated.'));
    exit;
}

// ─── VERIFY SIGHTING ──────────────────────────────────────
if ($action === 'verify_sighting') {
    $sightingId = (int)post('sighting_id');
    $pdo->prepare("UPDATE sightings SET verified=1 WHERE id=?")->execute([$sightingId]);
    logAction($pdo, $adminId, 'Verified sighting', 'sighting', $sightingId);
    header('Location: /ssvias/admin/sightings.php?msg=' . urlencode('Sighting verified.'));
    exit;
}

header('Location: /ssvias/admin/');
