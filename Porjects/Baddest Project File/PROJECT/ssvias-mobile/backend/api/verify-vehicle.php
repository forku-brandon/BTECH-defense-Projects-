<?php
// backend/api/verify-vehicle.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if(isset($data->plate)) {
    $plate = strtoupper($data->plate);
    
    $query = "SELECT v.*, u.name as owner_name 
              FROM vehicles v 
              LEFT JOIN users u ON v.owner_id = u.id 
              WHERE v.plate_number = :plate";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':plate', $plate);
    $stmt->execute();
    
    if($stmt->rowCount() > 0) {
        $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode([
            'success' => true,
            'vehicle' => $vehicle
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Vehicle not found'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Plate number required'
    ]);
}
?>