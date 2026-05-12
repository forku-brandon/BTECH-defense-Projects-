<?php
/**
 * SSVIAS — Vehicles API
 * Actions: add, delete, mark_stolen, mark_recovered, list
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_login('/ssvias/login.php');

$action = post('action', get('action', 'list'));
$userId = (int)$_SESSION['user_id'];

// ─── ADD VEHICLE ──────────────────────────────────────────
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $plate  = strtoupper(trim(post('plate_number')));
    $vin    = strtoupper(trim(post('vin')));
    $make   = post('make');
    $model  = post('model');
    $color  = post('color');
    $year   = (int)post('year');
    $type   = post('type', 'car');
    $desc   = post('description');

    if (empty($plate) || empty($make) || empty($model) || empty($color) || $year < 1900) {
        header('Location: /ssvias/add-vehicle.php?err=' . urlencode('Please fill in all required fields.'));
        exit;
    }

    // Check duplicate plate
    $stmt = $pdo->prepare("SELECT id FROM vehicles WHERE plate_number = ? LIMIT 1");
    $stmt->execute([$plate]);
    if ($stmt->fetch()) {
        header('Location: /ssvias/add-vehicle.php?err=' . urlencode("Plate number $plate is already registered."));
        exit;
    }

    $imagePath = null;
    if (!empty($_FILES['vehicle_image']['name'])) {
        $imagePath = upload_image($_FILES['vehicle_image'], 'vehicles');
        if (!$imagePath) {
            header('Location: /ssvias/add-vehicle.php?err=' . urlencode('Invalid image. Use JPG, PNG or WebP under 5MB.'));
            exit;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO vehicles (owner_id, plate_number, vin, make, model, color, year, type, description, image_path) VALUES (?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute([$userId, $plate, $vin, $make, $model, $color, $year, $type, $desc, $imagePath]);

    header('Location: /ssvias/vehicles.php?msg=' . urlencode("Vehicle $plate registered successfully!"));
    exit;
}

// ─── MARK STOLEN ──────────────────────────────────────────
if ($action === 'mark_stolen' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehicleId = (int)post('vehicle_id');
    $location  = post('last_seen_location');
    $desc      = post('description');

    // Verify ownership (or admin)
    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ? AND (owner_id = ? OR ? IN (SELECT id FROM users WHERE role IN ('admin','officer') AND id = ?))");
    $stmt->execute([$vehicleId, $userId, $userId, $userId]);
    $vehicle = $stmt->fetch();

    if (!$vehicle) {
        header('Location: /ssvias/vehicles.php?err=' . urlencode('Vehicle not found or access denied.'));
        exit;
    }

    $pdo->prepare("UPDATE vehicles SET status = 'stolen' WHERE id = ?")->execute([$vehicleId]);

    $pdo->prepare("INSERT INTO stolen_reports (vehicle_id, reporter_id, last_seen_location, description) VALUES (?,?,?,?)")
        ->execute([$vehicleId, $userId, $location, $desc]);

    // Notify owner
    create_notification($pdo, $vehicle['owner_id'], 'Vehicle Reported Stolen',
        "Your vehicle ({$vehicle['plate_number']}) has been marked as stolen. Authorities have been alerted.", 'alert');

    header('Location: /ssvias/vehicles.php?msg=' . urlencode('Stolen report submitted. Authorities have been notified.'));
    exit;
}

// ─── MARK RECOVERED ───────────────────────────────────────
if ($action === 'mark_recovered' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehicleId = (int)post('vehicle_id');
    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ? AND owner_id = ?");
    $stmt->execute([$vehicleId, $userId]);
    $vehicle = $stmt->fetch();
    if (!$vehicle) { header('Location: /ssvias/vehicles.php?err=' . urlencode('Access denied.')); exit; }

    $pdo->prepare("UPDATE vehicles SET status='recovered' WHERE id=?")->execute([$vehicleId]);
    $pdo->prepare("UPDATE stolen_reports SET status='closed' WHERE vehicle_id=? AND status!='closed'")->execute([$vehicleId]);

    create_notification($pdo, $userId, 'Vehicle Recovered', "Great news! Your vehicle ({$vehicle['plate_number']}) has been marked as recovered.", 'success');

    header('Location: /ssvias/vehicles.php?msg=' . urlencode('Vehicle marked as recovered!'));
    exit;
}

// ─── DELETE ───────────────────────────────────────────────
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehicleId = (int)post('vehicle_id');
    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id=? AND owner_id=?");
    $stmt->execute([$vehicleId, $userId]);
    if (!$stmt->fetch()) { header('Location: /ssvias/vehicles.php?err=' . urlencode('Access denied.')); exit; }

    $pdo->prepare("DELETE FROM vehicles WHERE id=?")->execute([$vehicleId]);
    header('Location: /ssvias/vehicles.php?msg=' . urlencode('Vehicle removed from your account.'));
    exit;
}

header('Location: /ssvias/vehicles.php');
