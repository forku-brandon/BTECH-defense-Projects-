<?php
/**
 * Generate a unique tracking ID
 * Format: WB-YYYYMMDD-XXXX
 */
function generateTrackingId($pdo) {
    $datePart = date('Ymd');
    
    // Find the highest sequence number for today
    $stmt = $pdo->prepare("SELECT tracking_id FROM reports WHERE tracking_id LIKE ? ORDER BY tracking_id DESC LIMIT 1");
    $stmt->execute(["WB-{$datePart}-%"]);
    $lastId = $stmt->fetchColumn();
    
    if ($lastId) {
        $parts = explode('-', $lastId);
        $sequence = intval($parts[2]) + 1;
    } else {
        $sequence = 1;
    }
    
    return sprintf("WB-%s-%04d", $datePart, $sequence);
}

/**
 * Sanitize user input
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Calculate priority score for a report
 */
function calculatePriorityScore($issueType, $upvoteCount, $hoursElapsed) {
    $severityWeights = [
        'burst_pipe' => 50,
        'no_water_unexplained' => 40,
        'water_suspension_bill' => 20,
        'other' => 10
    ];
    
    $severityWeight = isset($severityWeights[$issueType]) ? $severityWeights[$issueType] : 10;
    
    return ($upvoteCount * 10) + ($hoursElapsed * 2) + $severityWeight;
}

/**
 * Get human-readable issue type
 */
function getIssueTypeName($type) {
    $types = [
        'burst_pipe' => 'Burst Pipe',
        'no_water_unexplained' => 'No Water Flow (Unexplained)',
        'water_suspension_bill' => 'Water Suspension (Bill Related)',
        'other' => 'Other'
    ];
    return isset($types[$type]) ? $types[$type] : 'Unknown';
}

/**
 * Get human-readable status badge class
 */
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'pending': return 'badge-danger';
        case 'acknowledged': return 'badge-warning';
        case 'resolved': return 'badge-success';
        default: return 'badge-secondary';
    }
}
