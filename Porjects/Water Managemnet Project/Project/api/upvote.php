<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

// Start session to track upvotes
session_start();
$sessionId = session_id();
$reportId = isset($_POST['report_id']) ? intval($_POST['report_id']) : 0;

if ($reportId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid report ID.']);
    exit;
}

try {
    // Check if this session already upvoted this report
    $stmtCheck = $pdo->prepare("SELECT id FROM upvotes WHERE report_id = ? AND session_id = ?");
    $stmtCheck->execute([$reportId, $sessionId]);
    
    if ($stmtCheck->rowCount() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'You have already upvoted this report.']);
        exit;
    }

    $pdo->beginTransaction();

    // Insert upvote record
    $stmtInsert = $pdo->prepare("INSERT INTO upvotes (report_id, session_id) VALUES (?, ?)");
    $stmtInsert->execute([$reportId, $sessionId]);

    // Update report count
    $stmtUpdate = $pdo->prepare("UPDATE reports SET upvote_count = upvote_count + 1 WHERE id = ?");
    $stmtUpdate->execute([$reportId]);

    // Get new count
    $stmtCount = $pdo->prepare("SELECT upvote_count FROM reports WHERE id = ?");
    $stmtCount->execute([$reportId]);
    $newCount = $stmtCount->fetchColumn();

    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'new_count' => $newCount
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Database error occurred.']);
}
