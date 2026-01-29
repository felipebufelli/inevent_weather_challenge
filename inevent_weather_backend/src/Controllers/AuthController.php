<?php

namespace App\Controllers;

require_once __DIR__ . '/../Services/UserService.php';
require_once __DIR__ . '/../Services/JwtService.php';
require_once __DIR__ . '/../Config/Database.php';

use App\Services\UserService;
use App\Services\JwtService;

class AuthController
{
    private UserService $userService;
    private JwtService $jwtService;

    public function __construct()
    {
        $this->userService = new UserService();
        $this->jwtService = new JwtService();
    }

    public function handleRequest(string $action): void
    {
        try {
            switch ($action) {
                case 'login':
                    $this->login();
                    break;
                case 'register':
                    $this->register();
                    break;
                case 'me':
                    $this->me();
                    break;
                default:
                    $this->sendResponse(404, ['error' => true, 'message' => 'Ação não encontrada']);
            }
        } catch (\Exception $e) {
            $this->sendResponse(500, ['error' => true, 'message' => $e->getMessage()]);
        }
    }

    private function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendResponse(405, ['error' => true, 'message' => 'Método não permitido']);
            return;
        }

        $data = $this->getJsonBody();
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        // Validate required fields
        if (!$email || !$password) {
            $this->sendResponse(400, ['error' => true, 'message' => 'Email e senha são obrigatórios']);
            return;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->sendResponse(400, ['error' => true, 'message' => 'Formato de e-mail inválido']);
            return;
        }

        // Verify credentials
        $user = $this->userService->verifyPassword($email, $password);

        if (!$user) {
            $this->sendResponse(401, ['error' => true, 'message' => 'E-mail ou senha incorretos']);
            return;
        }

        // Generate JWT token
        $token = $this->jwtService->generateToken($user);

        $this->sendResponse(200, [
            'success' => true,
            'message' => 'Login realizado com sucesso',
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'city' => $user['city']
            ],
            'token' => $token
        ]);
    }

    private function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendResponse(405, ['error' => true, 'message' => 'Método não permitido']);
            return;
        }

        $data = $this->getJsonBody();

        // Validate required fields
        $errors = $this->validateRegistrationData($data);
        if (!empty($errors)) {
            $this->sendResponse(400, ['error' => true, 'message' => implode(', ', $errors)]);
            return;
        }

        try {
            $user = $this->userService->create($data);

            // Generate JWT token for auto-login after registration
            $token = $this->jwtService->generateToken($user);

            $this->sendResponse(201, [
                'success' => true,
                'message' => 'Usuário cadastrado com sucesso',
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'city' => $user['city']
                ],
                'token' => $token
            ]);
        } catch (\Exception $e) {
            $this->sendResponse(400, ['error' => true, 'message' => $e->getMessage()]);
        }
    }

    private function me(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendResponse(405, ['error' => true, 'message' => 'Método não permitido']);
            return;
        }

        $token = $this->jwtService->getTokenFromHeader();

        if (!$token) {
            $this->sendResponse(401, ['error' => true, 'message' => 'Token não fornecido']);
            return;
        }

        $user = $this->jwtService->getUserFromToken($token);

        if (!$user) {
            $this->sendResponse(401, ['error' => true, 'message' => 'Token inválido ou expirado']);
            return;
        }

        // Get fresh user data from database
        $freshUser = $this->userService->getById($user['id']);

        if (!$freshUser) {
            $this->sendResponse(404, ['error' => true, 'message' => 'Usuário não encontrado']);
            return;
        }

        $this->sendResponse(200, [
            'success' => true,
            'user' => $freshUser
        ]);
    }

    private function validateRegistrationData(array $data): array
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = 'Nome é obrigatório';
        } elseif (strlen($data['name']) < 2) {
            $errors[] = 'Nome deve ter no mínimo 2 caracteres';
        }

        if (empty($data['email'])) {
            $errors[] = 'E-mail é obrigatório';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Formato de e-mail inválido';
        }

        if (empty($data['password'])) {
            $errors[] = 'Senha é obrigatória';
        } elseif (strlen($data['password']) < 6) {
            $errors[] = 'Senha deve ter no mínimo 6 caracteres';
        }

        if (empty($data['city'])) {
            $errors[] = 'Cidade é obrigatória';
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

// Parse URI to get action
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$action = 'login'; // default

if (preg_match('/^\/api\/auth\/(\w+)$/', $uri, $matches)) {
    $action = $matches[1];
}

$controller = new AuthController();
$controller->handleRequest($action);
