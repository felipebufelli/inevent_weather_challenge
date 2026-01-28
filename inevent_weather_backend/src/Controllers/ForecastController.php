<?php

namespace App\Controllers;

use App\Services\OpenWeatherService;

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$city = $_GET['city'] ?? null;

if (!$city) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => 'City parameter is required'
    ]);
    exit;
}

$service = new OpenWeatherService();
$result = $service->getForecast($city);

if (isset($result['error'])) {
    http_response_code(500);
}

echo json_encode($result);
