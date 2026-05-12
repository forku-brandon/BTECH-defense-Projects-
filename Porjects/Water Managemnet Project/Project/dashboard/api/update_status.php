<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$reportId = isset($_POST['report_id']) ? intval($_POST['report_id']) : 0;
$newStatus = sanitizeInput($_POST['status'] ?? '');
$assignedTeam = sanitizeInput($_POST['assigned_team'] ?? '');
$internalNotes = sanitizeInput($_POST['internal_notes'] ?? '');

$validStatuses = ['pending', 'acknowledged', 'resolved'];
if (!in_array($newStatus, $validStatuses)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid status']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Get current status to check if it changed
    $stmtCheck = $pdo->prepare("SELECT status, created_at FROM reports WHERE id = ?");
    $stmtCheck->execute([$reportId]);
    $currentReport = $stmtCheck->fetch();

    if (!$currentReport) {
        throw new Exception("Report not found");
    }

    $oldStatus = $currentReport['status'];
    
    // Base update query
    $updateQuery = "UPDATE reports SET status = ?, assigned_team = ?, internal_notes = ?";
    $params = [$newStatus, empty($assignedTeam) ? null : $assignedTeam, empty($internalNotes) ? null : $internalNotes];
    
    // Timestamp logic based on status change
    if ($oldStatus !== $newStatus) {
        if ($newStatus === 'acknowledged') {
            $updateQuery .= ", acknowledged_at = CURRENT_TIMESTAMP";
        } elseif ($newStatus === 'resolved') {
            // Calculate resolution time in hours
            $hours = (time() - strtotime($currentReport['created_at'])) / 3600;
            $updateQuery .= ", resolved_at = CURRENT_TIMESTAMP, resolution_time_hours = ?";
            $params[] = $hours;
        }

        // Log to status_history
        $stmtHistory = $pdo->prepare("INSERT INTO status_history (report_id, user_id, old_status, new_status) VALUES (?, ?, ?, ?)");
        $stmtHistory->execute([$reportId, $_SESSION['user_id'], $oldStatus, $newStatus]);
    }
    
    $updateQuery .= " WHERE id = ?";
    $params[] = $reportId;

    $stmtUpdate = $pdo->prepare($updateQuery);
    $stmtUpdate->execute($params);

    $pdo->commit();

    echo json_encode(['status' => 'success', 'message' => 'Report updated']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
