<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Sanitize & Validate Inputs
    $issueType = sanitizeInput($_POST['issue_type'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $latitude = floatval($_POST['latitude'] ?? 0);
    $longitude = floatval($_POST['longitude'] ?? 0);
    $reporterName = sanitizeInput($_POST['reporter_name'] ?? '');
    
    // Reverse geocoding placeholder (can be implemented later via Nominatim API)
    // For now, we leave address blank or could put "Bamenda"
    $address = 'Bamenda, Cameroon'; 

    $validTypes = ['burst_pipe', 'no_water_unexplained', 'water_suspension_bill', 'other'];
    if (!in_array($issueType, $validTypes)) {
        throw new Exception("Invalid issue type selected.");
    }

    if ($latitude == 0 || $longitude == 0) {
        throw new Exception("Valid location coordinates are required.");
    }

    // 2. Generate Tracking ID
    $trackingId = generateTrackingId($pdo);

    // 3. Insert Report
    $stmt = $pdo->prepare("
        INSERT INTO reports (tracking_id, issue_type, description, latitude, longitude, address, reporter_name) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$trackingId, $issueType, $description, $latitude, $longitude, $address, empty($reporterName) ? null : $reporterName]);
    $reportId = $pdo->lastInsertId();

    // 4. Record Initial Status History
    $stmtHistory = $pdo->prepare("
        INSERT INTO status_history (report_id, old_status, new_status) 
        VALUES (?, 'none', 'pending')
    ");
    $stmtHistory->execute([$reportId]);

    // 5. Handle Photo Uploads
    if (isset($_FILES['photos']) && !empty($_FILES['photos']['name'][0])) {
        // Ensure upload directory exists
        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0755, true);
        }

        $files = $_FILES['photos'];
        $fileCount = min(count($files['name']), 3); // Max 3 files

        for ($i = 0; $i < $fileCount; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                // Validation (Size & Type)
                if ($files['size'][$i] > MAX_UPLOAD_SIZE) continue;
                
                $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!in_array($files['type'][$i], $allowedTypes)) continue;

                $ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                $newName = uniqid("WB_IMG_") . "_" . time() . "." . strtolower($ext);
                $destination = UPLOAD_DIR . $newName;

                // Move file (Basic client side compression was requested, but we should handle raw moves safely here)
                if (move_uploaded_file($files['tmp_name'][$i], $destination)) {
                    $dbPath = 'uploads/' . $newName; // Relative path for web
                    $stmtPhoto = $pdo->prepare("INSERT INTO photos (report_id, file_path) VALUES (?, ?)");
                    $stmtPhoto->execute([$reportId, $dbPath]);
                }
            }
        }
    }

    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Report submitted successfully.',
        'tracking_id' => $trackingId
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
