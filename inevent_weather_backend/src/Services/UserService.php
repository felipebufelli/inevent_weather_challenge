<?php

namespace App\Services;

use App\Config\Database;
use PDO;
use Exception;

class UserService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query('SELECT id, name, city, email, created_at, updated_at FROM users ORDER BY id');
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, name, city, email, created_at, updated_at FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        
        return $user ?: null;
    }

    public function getByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        
        return $user ?: null;
    }

    public function create(array $data): array
    {
        // Check if email already exists
        if ($this->getByEmail($data['email'])) {
            throw new Exception('E-mail já cadastrado');
        }

        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt = $this->db->prepare('
            INSERT INTO users (name, city, email, password) 
            VALUES (:name, :city, :email, :password) 
            RETURNING id, name, city, email, created_at, updated_at
        ');

        $stmt->execute([
            'name' => $data['name'],
            'city' => $data['city'],
            'email' => $data['email'],
            'password' => $hashedPassword
        ]);

        return $stmt->fetch();
    }

    public function update(int $id, array $data): ?array
    {
        // Check if user exists
        $existingUser = $this->getById($id);
        if (!$existingUser) {
            return null;
        }

        // Check if email is being changed and if it already exists
        if (isset($data['email']) && $data['email'] !== $existingUser['email']) {
            if ($this->getByEmail($data['email'])) {
                throw new Exception('E-mail já cadastrado');
            }
        }

        // Build update query dynamically
        $fields = [];
        $params = ['id' => $id];

        if (isset($data['name'])) {
            $fields[] = 'name = :name';
            $params['name'] = $data['name'];
        }
        if (isset($data['city'])) {
            $fields[] = 'city = :city';
            $params['city'] = $data['city'];
        }
        if (isset($data['email'])) {
            $fields[] = 'email = :email';
            $params['email'] = $data['email'];
        }
        if (isset($data['password']) && !empty($data['password'])) {
            $fields[] = 'password = :password';
            $params['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (empty($fields)) {
            return $existingUser;
        }

        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id RETURNING id, name, city, email, created_at, updated_at';
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        
        return $stmt->rowCount() > 0;
    }

    public function verifyPassword(string $email, string $password): ?array
    {
        $user = $this->getByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']);
            return $user;
        }
        
        return null;
    }
}
