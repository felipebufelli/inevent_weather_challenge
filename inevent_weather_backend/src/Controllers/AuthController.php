<?php

namespace App\Controllers;

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON body
$json = file_get_contents('php://input');
$data = json_decode($json, true);

$email = $data['email'] ?? null;
$password = $data['password'] ?? null;

// Validate required fields
if (!$email || !$password) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => 'Email e senha são obrigatórios'
    ]);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => 'Formato de e-mail inválido'
    ]);
    exit;
}

// Validate password (minimum 6 characters)
if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => 'Senha deve ter no mínimo 6 caracteres'
    ]);
    exit;
}


$token = base64_encode(json_encode([
    'email' => $email,
    'exp' => time() + 3600
]));

echo json_encode([
    'success' => true,
    'message' => 'Login realizado com sucesso',
    'user' => [
        'email' => $email,
        'name' => explode('@', $email)[0] 
    ],
    'token' => $token
]);
