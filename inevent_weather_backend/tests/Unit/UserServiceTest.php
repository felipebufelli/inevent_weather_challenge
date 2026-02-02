<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Services\UserService;
use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;

final class UserServiceTest extends TestCase
{
    public function testGetByEmailReturnsNullWhenUserNotFound(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetch')->willReturn(false);
        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $service = new UserService($pdo);
        $result = $service->getByEmail('nonexistent@test.com');

        $this->assertNull($result);
    }

    public function testGetByEmailReturnsUserWhenFound(): void
    {
        $user = [
            'id' => 1,
            'name' => 'Test',
            'city' => 'SP',
            'email' => 'test@test.com',
            'password' => password_hash('secret', PASSWORD_DEFAULT),
        ];
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetch')->willReturn($user);
        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $service = new UserService($pdo);
        $result = $service->getByEmail('test@test.com');

        $this->assertIsArray($result);
        $this->assertSame(1, $result['id']);
        $this->assertSame('test@test.com', $result['email']);
    }

    public function testVerifyPasswordReturnsUserWhenPasswordCorrect(): void
    {
        $hashed = password_hash('correct', PASSWORD_DEFAULT);
        $user = [
            'id' => 1,
            'name' => 'U',
            'city' => 'SP',
            'email' => 'u@t.com',
            'password' => $hashed,
        ];
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetch')->willReturn($user);
        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $service = new UserService($pdo);
        $result = $service->verifyPassword('u@t.com', 'correct');

        $this->assertIsArray($result);
        $this->assertArrayNotHasKey('password', $result);
        $this->assertSame(1, $result['id']);
    }

    public function testVerifyPasswordReturnsNullWhenPasswordWrong(): void
    {
        $user = [
            'id' => 1,
            'name' => 'U',
            'city' => 'SP',
            'email' => 'u@t.com',
            'password' => password_hash('correct', PASSWORD_DEFAULT),
        ];
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetch')->willReturn($user);
        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $service = new UserService($pdo);
        $result = $service->verifyPassword('u@t.com', 'wrong');

        $this->assertNull($result);
    }

    public function testVerifyPasswordReturnsNullWhenUserNotFound(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetch')->willReturn(false);
        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $service = new UserService($pdo);
        $result = $service->verifyPassword('nobody@test.com', 'any');

        $this->assertNull($result);
    }

    public function testCreateThrowsWhenEmailAlreadyExists(): void
    {
        $existingUser = ['id' => 1, 'email' => 'existing@test.com', 'name' => 'X', 'city' => 'SP', 'password' => 'hash'];
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetch')->willReturn($existingUser);
        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $service = new UserService($pdo);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('E-mail já cadastrado');
        $service->create([
            'name' => 'New',
            'city' => 'SP',
            'email' => 'existing@test.com',
            'password' => 'secret',
        ]);
    }

    public function testDeleteReturnsTrueWhenRowDeleted(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('rowCount')->willReturn(1);
        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $service = new UserService($pdo);
        $result = $service->delete(1);

        $this->assertTrue($result);
    }

    public function testDeleteReturnsFalseWhenNoRowDeleted(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('rowCount')->willReturn(0);
        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $service = new UserService($pdo);
        $result = $service->delete(999);

        $this->assertFalse($result);
    }

    public function testGetByIdReturnsNullWhenNotFound(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetch')->willReturn(false);
        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $service = new UserService($pdo);
        $result = $service->getById(999);

        $this->assertNull($result);
    }

    public function testGetByIdReturnsUserWhenFound(): void
    {
        $user = ['id' => 1, 'name' => 'U', 'city' => 'SP', 'email' => 'u@t.com', 'created_at' => null, 'updated_at' => null];
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetch')->willReturn($user);
        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $service = new UserService($pdo);
        $result = $service->getById(1);

        $this->assertIsArray($result);
        $this->assertSame(1, $result['id']);
    }
}
