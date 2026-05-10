<?php
// backend/api/get-users.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$mockUsers = [
    ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'phone' => '677123456', 'role' => 'citizen', 'region' => 'North West'],
    ['id' => 2, 'name' => 'Officer Smith', 'email' => 'police@ssvias.cm', 'phone' => '677123457', 'role' => 'police', 'region' => 'Littoral'],
    ['id' => 3, 'name' => 'Admin User', 'email' => 'admin@ssvias.cm', 'phone' => '677123458', 'role' => 'admin', 'region' => 'Centre']
];

echo json_encode(['success' => true, 'data' => $mockUsers]);
?>