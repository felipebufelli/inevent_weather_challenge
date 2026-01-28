<?php

namespace App\Controllers;

use App\Services\OpenWeatherService;

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$lat = $_GET['lat'] ?? null;
$lon = $_GET['lon'] ?? null;

if (!$lat || !$lon) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => 'Latitude and longitude parameters are required'
    ]);
    exit;
}

$service = new OpenWeatherService();
$result = $service->getAirQuality((float) $lat, (float) $lon);

if (isset($result['error'])) {
    http_response_code(500);
}

echo json_encode($result);
