<?php
/**
 * SSVIAS — Sightings API
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_login();

$action = post('action');
$userId = (int)$_SESSION['user_id'];

if ($action === 'report' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehiclePlate = strtoupper(trim(post('vehicle_plate')));
    $location     = post('location');
    $description  = post('description');

    if (empty($vehiclePlate) || empty($location)) {
        header('Location: /ssvias/sightings.php?err=' . urlencode('Plate number and location are required.'));
        exit;
    }

    // Look up vehicle
    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE plate_number = ? LIMIT 1");
    $stmt->execute([$vehiclePlate]);
    $vehicle = $stmt->fetch();

    if (!$vehicle) {
        header('Location: /ssvias/sightings.php?err=' . urlencode("No vehicle found with plate $vehiclePlate."));
        exit;
    }
    if ($vehicle['status'] !== 'stolen') {
        header('Location: /ssvias/sightings.php?err=' . urlencode("Vehicle $vehiclePlate is not currently reported as stolen."));
        exit;
    }

    $imagePath = null;
    if (!empty($_FILES['sighting_image']['name'])) {
        $imagePath = upload_image($_FILES['sighting_image'], 'sightings');
    }

    $pdo->prepare("INSERT INTO sightings (vehicle_id, reporter_id, location, description, image_path) VALUES (?,?,?,?,?)")
        ->execute([$vehicle['id'], $userId, $location, $description, $imagePath]);

    // Notify vehicle owner
    create_notification($pdo, $vehicle['owner_id'],
        'Sighting Alert: ' . $vehicle['plate_number'],
        "Your stolen vehicle ({$vehicle['plate_number']}) was spotted at: $location",
        'alert');

    header('Location: /ssvias/sightings.php?msg=' . urlencode('Sighting reported successfully. The vehicle owner has been notified.'));
    exit;
}

header('Location: /ssvias/sightings.php');
