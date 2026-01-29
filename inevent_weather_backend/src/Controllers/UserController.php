<?php

namespace App\Controllers;

require_once __DIR__ . '/../Services/UserService.php';
require_once __DIR__ . '/../Services/JwtService.php';
require_once __DIR__ . '/../Config/Database.php';

use App\Services\UserService;
use App\Services\JwtService;

class UserController
{
    private UserService $userService;
    private JwtService $jwtService;

    public function __construct()
    {
        $this->userService = new UserService();
        $this->jwtService = new JwtService();
    }

    public function handleRequest(string $method, ?int $id = null): void
    {
        try {
            // Verify authentication for all requests
            $currentUser = $this->authenticateRequest();
            
            if (!$currentUser) {
                $this->sendResponse(401, ['error' => true, 'message' => 'Não autorizado']);
                return;
            }

            switch ($method) {
                case 'GET':
                    $this->getProfile($currentUser);
                    break;
                case 'PUT':
                    $this->updateProfile($currentUser);
                    break;
                case 'DELETE':
                    $this->deleteAccount($currentUser);
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

    private function getProfile(array $currentUser): void
    {
        $user = $this->userService->getById($currentUser['id']);
        
        if (!$user) {
            $this->sendResponse(404, ['error' => true, 'message' => 'Usuário não encontrado']);
            return;
        }

        $this->sendResponse(200, [
            'success' => true,
            'data' => $user
        ]);
    }

    private function updateProfile(array $currentUser): void
    {
        $data = $this->getJsonBody();

        // Validate fields
        $errors = $this->validateUpdateData($data);
        if (!empty($errors)) {
            $this->sendResponse(400, ['error' => true, 'message' => implode(', ', $errors)]);
            return;
        }

        try {
            $user = $this->userService->update($currentUser['id'], $data);
            
            if (!$user) {
                $this->sendResponse(404, ['error' => true, 'message' => 'Usuário não encontrado']);
                return;
            }

            $this->sendResponse(200, [
                'success' => true,
                'message' => 'Perfil atualizado com sucesso',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            $this->sendResponse(400, ['error' => true, 'message' => $e->getMessage()]);
        }
    }

    private function deleteAccount(array $currentUser): void
    {
        $deleted = $this->userService->delete($currentUser['id']);

        if (!$deleted) {
            $this->sendResponse(404, ['error' => true, 'message' => 'Usuário não encontrado']);
            return;
        }

        $this->sendResponse(200, [
            'success' => true,
            'message' => 'Conta excluída com sucesso'
        ]);
    }

    private function validateUpdateData(array $data): array
    {
        $errors = [];

        if (isset($data['name']) && strlen($data['name']) < 2) {
            $errors[] = 'Nome deve ter no mínimo 2 caracteres';
        }

        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Formato de e-mail inválido';
        }

        if (isset($data['password']) && !empty($data['password']) && strlen($data['password']) < 6) {
            $errors[] = 'Senha deve ter no mínimo 6 caracteres';
        }

        return $errors;
    }

    private function getJsonBody(): array
    {
        $json = file_get_contents('php://input');
        return json_decode($json, true) ?? [];
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

$controller = new UserController();
$controller->handleRequest($_SERVER['REQUEST_METHOD']);
