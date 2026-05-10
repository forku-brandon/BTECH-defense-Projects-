<?php
// backend/api/get-user-vehicles.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$data = json_decode(file_get_contents("php://input"));

$mockUserVehicles = [
    1 => [  // user_id 1
        ['id' => 1, 'plate_number' => 'AB123CD', 'make' => 'Toyota', 'model' => 'Corolla', 'year' => 2020, 'color' => 'Silver', 'status' => 'stolen'],
        ['id' => 2, 'plate_number' => 'XY789ZZ', 'make' => 'Honda', 'model' => 'Civic', 'year' => 2021, 'color' => 'Black', 'status' => 'safe']
    ]
];

$userId = $data->user_id ?? 0;

if(isset($mockUserVehicles[$userId])) {
    echo json_encode([
        'success' => true,
        'data' => $mockUserVehicles[$userId]
    ]);
} else {
    echo json_encode([
        'success' => true,
        'data' => []
    ]);
}
?>