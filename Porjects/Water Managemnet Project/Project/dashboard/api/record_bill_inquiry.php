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
$billStatus = sanitizeInput($_POST['bill_status'] ?? '');
$notes = sanitizeInput($_POST['notes'] ?? '');

$validStatuses = ['paid_pending_reconnect', 'outstanding_balance', 'unknown_referred'];
if (!in_array($billStatus, $validStatuses)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid bill status']);
    exit;
}

try {
    // Verify report is actually bill related
    $stmtCheck = $pdo->prepare("SELECT issue_type FROM reports WHERE id = ?");
    $stmtCheck->execute([$reportId]);
    $report = $stmtCheck->fetch();

    if (!$report || $report['issue_type'] !== 'water_suspension_bill') {
        throw new Exception("Invalid report type for bill inquiry.");
    }

    $stmtInsert = $pdo->prepare("
        INSERT INTO bill_inquiries (report_id, staff_user_id, bill_status, notes) 
        VALUES (?, ?, ?, ?)
    ");
    $stmtInsert->execute([$reportId, $_SESSION['user_id'], $billStatus, empty($notes) ? null : $notes]);

    echo json_encode(['status' => 'success', 'message' => 'Bill inquiry recorded']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
