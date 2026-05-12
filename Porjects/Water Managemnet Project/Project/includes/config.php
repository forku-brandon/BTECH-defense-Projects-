<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'watareport_db');

// Application configuration
define('APP_NAME', 'WataReport');
define('BASE_URL', 'http://localhost/Water%20Managemnet%20Project/Project/'); // Adjust as needed
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB

// Map defaults (Bamenda, Cameroon)
define('MAP_CENTER_LAT', 5.9339);
define('MAP_CENTER_LNG', 10.1568);

// Turn on error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);
