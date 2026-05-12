<?php
/**
 * SSVIAS — OCR API (Simulated)
 * POST /api/ocr.php — Accepts image upload, returns extracted plate text
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['image'])) {
    echo json_encode(['success' => false, 'message' => 'No image uploaded.']);
    exit;
}

$file = $_FILES['image'];
$allowed = ['image/jpeg', 'image/png', 'image/webp'];
if (!in_array($file['type'], $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Use JPG, PNG or WebP.']);
    exit;
}
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File too large. Max 5MB.']);
    exit;
}

/**
 * Real OCR would call Tesseract here via exec() or a Python microservice.
 * Example real call:
 *   $tmpPath = sys_get_temp_dir() . '/ocr_' . uniqid() . '.jpg';
 *   move_uploaded_file($file['tmp_name'], $tmpPath);
 *   $output = shell_exec("tesseract $tmpPath stdout --psm 8 -c tessedit_char_whitelist=ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-");
 *   $plate = trim($output);
 *
 * For demo purposes, we search the database for any vehicle plate whose partial
 * match could appear in the image filename or return a sample plate.
 */

// Simulate: try to find a plate in the uploaded filename (demo behaviour)
$filename = strtoupper(pathinfo($file['name'], PATHINFO_FILENAME));
$stmt = $pdo->prepare("SELECT plate_number FROM vehicles WHERE REPLACE(plate_number,'-','') LIKE ? LIMIT 1");
$stmt->execute(['%' . preg_replace('/[^A-Z0-9]/', '', $filename) . '%']);
$row = $stmt->fetch();

if ($row) {
    $extractedPlate = $row['plate_number'];
    $confidence = 92;
} else {
    // Demo fallback — return first stolen vehicle plate or a sample
    $sample = $pdo->query("SELECT plate_number FROM vehicles WHERE status='stolen' LIMIT 1")->fetch();
    $extractedPlate = $sample ? $sample['plate_number'] : 'NW-0000-X';
    $confidence = 78;
}

// Slight random variation for realism
$confidence += rand(-5, 5);
$confidence  = max(60, min(98, $confidence));

echo json_encode([
    'success'    => true,
    'plate'      => $extractedPlate,
    'confidence' => $confidence,
    'message'    => "Plate extracted with {$confidence}% confidence. Please verify below.",
    'note'       => 'OCR is simulated. In production, integrate Tesseract or a Python microservice.'
]);
