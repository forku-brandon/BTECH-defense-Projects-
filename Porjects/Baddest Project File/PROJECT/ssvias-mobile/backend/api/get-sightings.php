<?php
// backend/api/get-sightings.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$mockSightings = [
    ['id' => 1, 'plate_number' => 'AB123CD', 'location' => 'Bamenda Main Market', 'reporter_name' => 'Anonymous', 'sighting_date' => '2024-01-25 09:15:00', 'details' => 'Vehicle seen heading north'],
    ['id' => 2, 'plate_number' => 'NW456GH', 'location' => 'Yaoundé City Center', 'reporter_name' => 'Paul', 'sighting_date' => '2024-01-26 16:30:00', 'details' => 'Parked suspiciously']
];

echo json_encode(['success' => true, 'data' => $mockSightings]);
?>