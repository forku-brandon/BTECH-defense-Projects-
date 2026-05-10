<?php
// backend/api/register.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$data = json_decode(file_get_contents("php://input"));

if(isset($data->name) && isset($data->email) && isset($data->password)) {
    // Mock registration - in production, save to database
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful',
        'user_id' => rand(1000, 9999)
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'All fields required'
    ]);
}
?>