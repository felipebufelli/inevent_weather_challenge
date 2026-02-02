<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Services\JwtService;
use PHPUnit\Framework\TestCase;

final class JwtServiceTest extends TestCase
{
    private JwtService $service;

    protected function setUp(): void
    {
        $_ENV['JWT_SECRET'] = 'test_secret_key';
        $_ENV['JWT_EXPIRATION'] = 3600;
        $this->service = new JwtService();
    }

    public function testGenerateTokenReturnsValidJwtString(): void
    {
        $userData = [
            'id' => 1,
            'email' => 'user@test.com',
            'name' => 'Test User',
            'city' => 'São Paulo',
        ];

        $token = $this->service->generateToken($userData);

        $this->assertNotEmpty($token);
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+$/', $token);
    }

    public function testValidateTokenReturnsPayloadForValidToken(): void
    {
        $userData = [
            'id' => 1,
            'email' => 'user@test.com',
            'name' => 'Test User',
            'city' => 'SP',
        ];
        $token = $this->service->generateToken($userData);

        $decoded = $this->service->validateToken($token);

        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('iat', $decoded);
        $this->assertArrayHasKey('exp', $decoded);
        $this->assertArrayHasKey('user', $decoded);
        $this->assertEquals($userData['id'], $decoded['user']->id ?? $decoded['user']['id']);
        $this->assertEquals($userData['email'], $decoded['user']->email ?? $decoded['user']['email']);
    }

    public function testValidateTokenReturnsNullForInvalidToken(): void
    {
        $result = $this->service->validateToken('invalid.token.here');

        $this->assertNull($result);
    }

    public function testValidateTokenReturnsNullForExpiredToken(): void
    {
        $_ENV['JWT_EXPIRATION'] = -3600; // expired 1 hour ago
        $service = new JwtService();
        $token = $service->generateToken([
            'id' => 1,
            'email' => 'u@t.com',
            'name' => 'U',
            'city' => 'SP',
        ]);

        $result = $service->validateToken($token);

        $this->assertNull($result);
    }

    public function testGetUserFromTokenReturnsUserArray(): void
    {
        $userData = [
            'id' => 2,
            'email' => 'other@test.com',
            'name' => 'Other',
            'city' => 'RJ',
        ];
        $token = $this->service->generateToken($userData);

        $user = $this->service->getUserFromToken($token);

        $this->assertIsArray($user);
        $this->assertEquals(2, $user['id']);
        $this->assertEquals('other@test.com', $user['email']);
        $this->assertEquals('Other', $user['name']);
        $this->assertEquals('RJ', $user['city']);
    }

    public function testGetUserFromTokenReturnsNullForInvalidToken(): void
    {
        $result = $this->service->getUserFromToken('invalid');

        $this->assertNull($result);
    }

    public function testGetTokenFromHeaderReturnsTokenWhenBearerPresent(): void
    {
        $expectedToken = 'my-jwt-token-123';
        $headers = ['Authorization' => 'Bearer ' . $expectedToken];

        $token = $this->service->getTokenFromHeader($headers);

        $this->assertSame($expectedToken, $token);
    }

    public function testGetTokenFromHeaderReturnsNullWhenNoAuthorization(): void
    {
        $token = $this->service->getTokenFromHeader([]);

        $this->assertNull($token);
    }

    public function testGetTokenFromHeaderIsCaseInsensitive(): void
    {
        $expectedToken = 'token-abc';
        $headers = ['authorization' => 'Bearer ' . $expectedToken];

        $token = $this->service->getTokenFromHeader($headers);

        $this->assertSame($expectedToken, $token);
    }

    public function testGetTokenFromHeaderReturnsNullWhenHeaderNull(): void
    {
        $token = $this->service->getTokenFromHeader(['Content-Type' => 'application/json']);

        $this->assertNull($token);
    }
}
