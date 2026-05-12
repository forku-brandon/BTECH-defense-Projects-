<?php
/**
 * SSVIAS — Stolen Reports API
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_login();

$action = post('action', get('action', ''));
$userId = (int)$_SESSION['user_id'];
$role   = current_role();

if ($action === 'report' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehicleId = (int)post('vehicle_id');
    $location  = post('last_seen_location');
    $description = post('description');

    if ($vehicleId <= 0 || empty($location)) {
        header('Location: /ssvias/report-stolen.php?err=' . urlencode('Please select a vehicle and provide the last known location.'));
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ? LIMIT 1");
    $stmt->execute([$vehicleId]);
    $vehicle = $stmt->fetch();

    if (!$vehicle) {
        header('Location: /ssvias/report-stolen.php?err=' . urlencode('Vehicle not found.'));
        exit;
    }

    if ($vehicle['owner_id'] !== $userId && !in_array($role, ['admin', 'officer'], true)) {
        header('Location: /ssvias/report-stolen.php?err=' . urlencode('You do not have permission to report this vehicle.'));
        exit;
    }

    $pdo->prepare("UPDATE vehicles SET status='stolen' WHERE id=?")->execute([$vehicleId]);
    $pdo->prepare("INSERT INTO stolen_reports (vehicle_id, reporter_id, last_seen_location, description) VALUES (?,?,?,?)")
        ->execute([$vehicleId, $userId, $location, $description]);

    if ($vehicle['owner_id'] !== $userId) {
        create_notification($pdo, $vehicle['owner_id'], 'Vehicle Reported Stolen',
            "Your vehicle ({$vehicle['plate_number']}) was reported stolen by an authorized officer.", 'alert');
    }

    header('Location: /ssvias/report-stolen.php?msg=' . urlencode('Stolen report submitted successfully.'));
    exit;
}

header('Location: /ssvias/report-stolen.php');
