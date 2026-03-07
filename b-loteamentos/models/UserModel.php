<?php
declare(strict_types=1);

namespace Models;

use Core\Model;

final class UserModel extends Model
{
    public function getActiveByEmail(string $email): array|false
    {
        $sql = 'SELECT id, name, email, password, role, active FROM users WHERE email = :email AND active = 1 LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function listAll(): array
    {
        $sql = 'SELECT id, name, email, role, active, created_at, updated_at FROM users ORDER BY id DESC';
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function getById(int $id): array|false
    {
        $sql = 'SELECT id, name, email, role, active, created_at, updated_at FROM users WHERE id = :id LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function existsEmail(string $email, ?int $ignoreId = null): bool
    {
        if ($ignoreId !== null) {
            $sql = 'SELECT 1 FROM users WHERE email = :email AND id <> :id LIMIT 1';
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['email' => $email, 'id' => $ignoreId]);
            return $stmt->fetchColumn() !== false;
        }

        $sql = 'SELECT 1 FROM users WHERE email = :email LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetchColumn() !== false;
    }

    public function create(string $name, string $email, string $passwordHash, string $role): int
    {
        $sql = 'INSERT INTO users (name, email, password, role, active) VALUES (:name, :email, :password, :role, 1)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => $passwordHash,
            'role' => $role,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function updateUser(int $id, string $name, string $email, string $role): void
    {
        $sql = 'UPDATE users SET name = :name, email = :email, role = :role WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'role' => $role,
        ]);
    }

    public function updatePassword(int $id, string $passwordHash): void
    {
        $sql = 'UPDATE users SET password = :password WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'password' => $passwordHash,
        ]);
    }

    public function setActive(int $id, int $active): void
    {
        $sql = 'UPDATE users SET active = :active WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'active' => $active,
        ]);
    }
}
