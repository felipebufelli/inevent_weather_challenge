<?php

namespace App\Controllers;

require_once __DIR__ . '/../Services/OpenWeatherService.php';

use App\Services\OpenWeatherService;
use App\Services\JwtService;

class AirQualityController
{
    private OpenWeatherService $weatherService;
    private JwtService $jwtService;

    public function __construct()
    {
        $this->weatherService = new OpenWeatherService();
        $this->jwtService = new JwtService();
    }

    public function handleRequest(string $method): void
    {
        try {
            $currentUser = $this->authenticateRequest();
            
            if (!$currentUser) {
                $this->sendResponse(401, ['error' => true, 'message' => 'Não autorizado']);
                return;
            }
            
            switch ($method) {
                case 'GET':
                    $this->getAirQuality();
                    break;
                default:
                    $this->sendResponse(405, ['error' => true, 'message' => 'Método não permitido']);
            }
        } catch (\Exception $e) {
            $this->sendResponse(500, ['error' => true, 'message' => $e->getMessage()]);
        }
    }

    private function authenticateRequest(): ?array
    {
        $token = $this->jwtService->getTokenFromHeader();
        
        if (!$token) {
            return null;
        }

        return $this->jwtService->getUserFromToken($token);
    }

    private function getAirQuality(): void
    {
        $lat = $_GET['lat'] ?? null;
        $lon = $_GET['lon'] ?? null;

        if (!$lat || !$lon) {
            $this->sendResponse(400, ['error' => true, 'message' => 'Parâmetros lat e lon são obrigatórios']);
            return;
        }

        $result = $this->weatherService->getAirQuality((float) $lat, (float) $lon);

        if (isset($result['error'])) {
            $this->sendResponse(500, $result);
            return;
        }

        $this->sendResponse(200, $result);
    }

    private function sendResponse(int $statusCode, array $data): void
    {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}

// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$controller = new AirQualityController();
$controller->handleRequest($_SERVER['REQUEST_METHOD']);
