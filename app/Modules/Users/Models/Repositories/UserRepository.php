<?php
/**
 * @package     phast/app
 * @subpackage  Modules/Users/Models/Repositories
 * @file        UserRepository
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description User repository implementation
 */

declare(strict_types=1);

namespace Phast\App\Modules\Users\Models\Repositories;

use PDO;
use Phast\App\Modules\Users\Models\Entities\UserEntity;

class UserRepository implements UserRepositoryInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findAll(int $page = 1, int $limit = 10): array
    {
        $offset = ($page - 1) * $limit;
        
        $stmt = $this->db->prepare("
            SELECT * FROM users 
            ORDER BY created_at DESC 
            LIMIT :limit OFFSET :offset
        ");
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = UserEntity::fromArray($row);
        }
        
        return $users;
    }

    public function findById(int $id): ?UserEntity
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row ? UserEntity::fromArray($row) : null;
    }

    public function findByEmail(string $email): ?UserEntity
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row ? UserEntity::fromArray($row) : null;
    }

    public function save(UserEntity $user): UserEntity
    {
        if ($user->getId() === null) {
            return $this->create($user);
        }
        
        return $this->update($user);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    public function existsByEmail(string $email): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        
        return $stmt->fetchColumn() > 0;
    }

    public function count(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM users");
        return (int) $stmt->fetchColumn();
    }

    private function create(UserEntity $user): UserEntity
    {
        $stmt = $this->db->prepare("
            INSERT INTO users (name, email, password, created_at, updated_at) 
            VALUES (:name, :email, :password, :created_at, :updated_at)
        ");
        
        $stmt->execute([
            ':name' => $user->getName(),
            ':email' => $user->getEmail(),
            ':password' => $user->getPassword(),
            ':created_at' => $user->getCreatedAt()?->format('Y-m-d H:i:s'),
            ':updated_at' => $user->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ]);
        
        $user->setId((int) $this->db->lastInsertId());
        
        return $user;
    }

    private function update(UserEntity $user): UserEntity
    {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET name = :name, email = :email, password = :password, updated_at = :updated_at 
            WHERE id = :id
        ");
        
        $stmt->execute([
            ':id' => $user->getId(),
            ':name' => $user->getName(),
            ':email' => $user->getEmail(),
            ':password' => $user->getPassword(),
            ':updated_at' => $user->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ]);
        
        return $user;
    }
}
