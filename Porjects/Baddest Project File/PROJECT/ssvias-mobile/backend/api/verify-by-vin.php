<?php
// backend/api/verify-by-vin.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$data = json_decode(file_get_contents("php://input"));

$mockVehiclesByVIN = [
    'VIN123456' => ['plate_number' => 'AB123CD', 'make' => 'Toyota', 'model' => 'Corolla', 'year' => 2020, 'status' => 'stolen'],
    'VIN789012' => ['plate_number' => 'XY789ZZ', 'make' => 'Honda', 'model' => 'Civic', 'year' => 2021, 'status' => 'safe']
];

if(isset($data->vin) && isset($mockVehiclesByVIN[$data->vin])) {
    echo json_encode([
        'success' => true,
        'vehicle' => $mockVehiclesByVIN[$data->vin]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Vehicle not found'
    ]);
}
?>