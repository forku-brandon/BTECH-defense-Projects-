<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';

// Authentication Check
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access");
}

// Set Headers for CSV Download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=WataReport_Export_' . date('Y-m-d_H-i') . '.csv');

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Output the column headings
fputcsv($output, [
    'Tracking ID', 
    'Issue Type', 
    'Description', 
    'Latitude', 
    'Longitude', 
    'Address', 
    'Reporter Name', 
    'Upvote Count', 
    'Status', 
    'Reported At', 
    'Acknowledged At', 
    'Resolved At', 
    'Resolution Time (Hours)', 
    'Assigned Team', 
    'Internal Notes'
]);

// Build Query based on filters
$whereClause = "WHERE 1=1";
$params = [];

if (!empty($_GET['status']) && $_GET['status'] !== 'all') {
    $whereClause .= " AND status = ?";
    $params[] = $_GET['status'];
}

if (!empty($_GET['issue_type']) && $_GET['issue_type'] !== 'all') {
    $whereClause .= " AND issue_type = ?";
    $params[] = $_GET['issue_type'];
}

if (!empty($_GET['start_date'])) {
    $whereClause .= " AND DATE(created_at) >= ?";
    $params[] = $_GET['start_date'];
}

if (!empty($_GET['end_date'])) {
    $whereClause .= " AND DATE(created_at) <= ?";
    $params[] = $_GET['end_date'];
}

try {
    $sql = "SELECT tracking_id, issue_type, description, latitude, longitude, address, reporter_name, upvote_count, status, created_at, acknowledged_at, resolved_at, resolution_time_hours, assigned_team, internal_notes FROM reports $whereClause ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Fetch the data and output row by row
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Format some columns if necessary
        $row['issue_type'] = getIssueTypeName($row['issue_type']);
        $row['status'] = ucfirst($row['status']);
        
        fputcsv($output, $row);
    }
} catch (PDOException $e) {
    // If error, write error to CSV
    fputcsv($output, ['Error generating export: ' . $e->getMessage()]);
}

fclose($output);
exit;
