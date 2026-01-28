<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

header('Content-Type: application/json; charset=utf-8');

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Roteamento
switch ($uri) {
    case '/api/weather':
        require __DIR__ . '/../src/Controllers/WeatherController.php';
        break;
    
    case '/api/forecast':
        require __DIR__ . '/../src/Controllers/ForecastController.php';
        break;
    
    case '/api/air-quality':
        require __DIR__ . '/../src/Controllers/AirQualityController.php';
        break;
    
    case '/api/auth/login':
        require __DIR__ . '/../src/Controllers/AuthController.php';
        break;
    
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
}
