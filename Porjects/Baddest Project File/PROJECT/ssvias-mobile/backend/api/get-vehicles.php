<?php
// backend/api/get-vehicles.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$mockVehicles = [
    ['id' => 1, 'plate_number' => 'AB123CD', 'vin' => 'VIN123456', 'make' => 'Toyota', 'model' => 'Corolla', 'year' => 2020, 'color' => 'Silver', 'status' => 'stolen', 'owner_name' => 'John Doe'],
    ['id' => 2, 'plate_number' => 'XY789ZZ', 'vin' => 'VIN789012', 'make' => 'Honda', 'model' => 'Civic', 'year' => 2021, 'color' => 'Black', 'status' => 'safe', 'owner_name' => 'Jane Smith'],
    ['id' => 3, 'plate_number' => 'NW456GH', 'vin' => 'VIN456789', 'make' => 'Ford', 'model' => 'Ranger', 'year' => 2019, 'color' => 'White', 'status' => 'stolen', 'owner_name' => 'Peter Jones']
];

echo json_encode(['success' => true, 'data' => $mockVehicles]);
?>