<?php
// backend/api/create-user.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$data = json_decode(file_get_contents("php://input"));

if(isset($data->name) && isset($data->email) && isset($data->password)) {
    echo json_encode([
        'success' => true,
        'message' => 'User created successfully',
        'user_id' => rand(1000, 9999)
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
}
?>