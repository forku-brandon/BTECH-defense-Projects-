<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

try {
    // Only get reports from the last 30 days for the public map
    $stmt = $pdo->prepare("
        SELECT tracking_id, issue_type, description, latitude, longitude, 
               status, created_at, upvote_count
        FROM reports 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    
    $reports = $stmt->fetchAll();
    
    echo json_encode([
        'status' => 'success',
        'data' => $reports
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error'
    ]);
}
