<?php
// backend/api/login.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once '../config/database.php';

$data = json_decode(file_get_contents("php://input"));

if(isset($data->email) && isset($data->password)) {
    // Mock response for demo
    $mockUsers = [
        'user@example.com' => ['id' => 1, 'name' => 'John Doe', 'role' => 'citizen', 'password' => 'password123'],
        'police@ssvias.cm' => ['id' => 2, 'name' => 'Officer Smith', 'role' => 'police', 'password' => 'police123'],
        'admin@ssvias.cm' => ['id' => 3, 'name' => 'Admin User', 'role' => 'admin', 'password' => 'admin123']
    ];
    
    if(isset($mockUsers[$data->email]) && $mockUsers[$data->email]['password'] === $data->password) {
        $user = $mockUsers[$data->email];
        unset($user['password']);
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'user' => $user
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid credentials'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Email and password required'
    ]);
}
?>