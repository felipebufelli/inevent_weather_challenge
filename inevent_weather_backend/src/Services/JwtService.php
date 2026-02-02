<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JwtService
{
    private string $secretKey;
    private int $expiration;

    public function __construct()
    {
        $this->secretKey = $_ENV['JWT_SECRET'] ?? 'default_secret_key';
        $this->expiration = (int) ($_ENV['JWT_EXPIRATION'] ?? 3600);
    }

    public function generateToken(array $userData): string
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + $this->expiration;

        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'user' => [
                'id' => $userData['id'],
                'email' => $userData['email'],
                'name' => $userData['name'],
                'city' => $userData['city']
            ]
        ];

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    public function validateToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));
            return (array) $decoded;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getUserFromToken(string $token): ?array
    {
        $decoded = $this->validateToken($token);
        
        if ($decoded && isset($decoded['user'])) {
            return (array) $decoded['user'];
        }

        return null;
    }

    /**
     * @param array<string, string>|null $headers Optional headers (for testing). If null, uses getallheaders().
     */
    public function getTokenFromHeader(?array $headers = null): ?string
    {
        if ($headers === null) {
            $raw = function_exists('getallheaders') ? getallheaders() : [];
            $headers = is_array($raw) ? $raw : [];
        }
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
