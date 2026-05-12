<?php
/**
 * SSVIAS — Verify API (JSON)
 * GET /api/verify.php?plate=NW-1234-A  or  ?vin=XYZ
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$plate = strtoupper(trim(get('plate')));
$vin   = strtoupper(trim(get('vin')));

if (empty($plate) && empty($vin)) {
    echo json_encode(['success' => false, 'message' => 'Provide a plate number or VIN.']);
    exit;
}

$where = $plate ? "plate_number = ?" : "vin = ?";
$val   = $plate ?: $vin;

$stmt = $pdo->prepare("
    SELECT v.*, u.name AS owner_name, u.phone AS owner_phone
    FROM vehicles v
    JOIN users u ON u.id = v.owner_id
    WHERE v.$where LIMIT 1
");
$stmt->execute([$val]);
$vehicle = $stmt->fetch();

if (!$vehicle) {
    echo json_encode(['success' => false, 'message' => 'No vehicle found with that plate/VIN in the registry.']);
    exit;
}

// Get latest stolen report if stolen
$report = null;
if ($vehicle['status'] === 'stolen') {
    $rs = $pdo->prepare("SELECT * FROM stolen_reports WHERE vehicle_id = ? ORDER BY reported_at DESC LIMIT 1");
    $rs->execute([$vehicle['id']]);
    $report = $rs->fetch();
}

echo json_encode([
    'success' => true,
    'data' => [
        'plate'       => $vehicle['plate_number'],
        'vin'         => $vehicle['vin'],
        'make'        => $vehicle['make'],
        'model'       => $vehicle['model'],
        'color'       => $vehicle['color'],
        'year'        => $vehicle['year'],
        'type'        => $vehicle['type'],
        'status'      => $vehicle['status'],
        'image'       => $vehicle['image_path'] ? '/ssvias/' . $vehicle['image_path'] : null,
        'report'      => $report ? [
            'location'    => $report['last_seen_location'],
            'reported_at' => $report['reported_at'],
            'description' => $report['description'],
        ] : null,
    ]
]);
