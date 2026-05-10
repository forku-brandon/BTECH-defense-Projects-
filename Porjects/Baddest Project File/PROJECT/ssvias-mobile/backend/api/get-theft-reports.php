<?php
// backend/api/get-theft-reports.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$mockReports = [
    ['id' => 1, 'vehicle_id' => 1, 'plate_number' => 'AB123CD', 'make' => 'Toyota', 'model' => 'Corolla', 'status' => 'pending', 'last_location' => 'Bamenda City Center', 'report_date' => '2024-01-15 10:30:00'],
    ['id' => 2, 'vehicle_id' => 3, 'plate_number' => 'NW456GH', 'make' => 'Ford', 'model' => 'Ranger', 'status' => 'verified', 'last_location' => 'Douala Airport', 'report_date' => '2024-01-20 14:15:00']
];

echo json_encode(['success' => true, 'data' => $mockReports]);
?>