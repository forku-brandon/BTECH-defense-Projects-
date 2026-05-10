<?php
// backend/api/register-vehicle.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$data = json_decode(file_get_contents("php://input"));

if(isset($data->plate_number) && isset($data->make) && isset($data->model)) {
    echo json_encode([
        'success' => true,
        'message' => 'Vehicle registered successfully',
        'vehicle_id' => rand(1000, 9999)
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required vehicle information'
    ]);
}
?>