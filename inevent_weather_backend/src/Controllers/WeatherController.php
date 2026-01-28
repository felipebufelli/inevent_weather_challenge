<?php

namespace App\Controllers;

use App\Services\OpenWeatherService;

$city = $_GET['city'] ?? null;

if (!$city) {
    http_response_code(400);
    echo json_encode([
        'error' => 'City parameter is required'
    ]);
    return;
}

$service = new OpenWeatherService();
$result = $service->getWeatherByCity($city);

if (isset($result['error'])) {
    if (isset($result['error'])) {
        http_response_code(500);
    }
    
}

echo json_encode($result);
