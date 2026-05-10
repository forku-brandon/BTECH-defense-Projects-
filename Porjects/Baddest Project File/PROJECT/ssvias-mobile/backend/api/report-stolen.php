<?php
// backend/api/report-stolen.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$data = json_decode(file_get_contents("php://input"));

if(isset($data->plate_number)) {
    echo json_encode([
        'success' => true,
        'message' => 'Vehicle reported as stolen',
        'report_id' => rand(10000, 99999)
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Vehicle information required'
    ]);
}
?>