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
use PDOException;
use Phast\App\Modules\Users\Models\Entities\UserEntity;
use Phast\Core\Exceptions\Domain\EntityNotFoundException;
use Phast\Core\Exceptions\Domain\DuplicateEntityException;
use Phast\Core\Exceptions\Infrastructure\DatabaseException;

class UserRepository implements UserRepositoryInterface {
   private PDO $db;

   public function __construct(PDO $db) {
      $this->db = $db;
   }

   public function findAll(int $page = 1, int $limit = 10): array {
      try {
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
      } catch (PDOException $e) {
         throw DatabaseException::fromPDOException($e, 'finding all users');
      }
   }

   public function findById(int $id): UserEntity {
      try {
         $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
         $stmt->bindValue(':id', $id, PDO::PARAM_INT);
         $stmt->execute();

         $row = $stmt->fetch(PDO::FETCH_ASSOC);

         if (!$row) {
            throw EntityNotFoundException::withId('User', $id);
         }

         return UserEntity::fromArray($row);
      } catch (PDOException $e) {
         throw DatabaseException::fromPDOException($e, "finding user with ID {$id}");
      }
   }

   public function findByIdOrNull(int $id): ?UserEntity {
      try {
         return $this->findById($id);
      } catch (EntityNotFoundException $e) {
         return null;
      }
   }

   public function findByEmail(string $email): UserEntity {
      try {
         $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
         $stmt->bindValue(':email', $email);
         $stmt->execute();

         $row = $stmt->fetch(PDO::FETCH_ASSOC);

         if (!$row) {
            throw EntityNotFoundException::withCriteria('User', ['email' => $email]);
         }

         return UserEntity::fromArray($row);
      } catch (PDOException $e) {
         throw DatabaseException::fromPDOException($e, "finding user with email {$email}");
      }
   }

   public function findByEmailOrNull(string $email): ?UserEntity {
      try {
         return $this->findByEmail($email);
      } catch (EntityNotFoundException $e) {
         return null;
      }
   }

   public function save(UserEntity $user): UserEntity {
      try {
         if ($user->getId() === null) {
            // Check for email duplication before creating
            if ($this->existsByEmail($user->getEmail())) {
               throw DuplicateEntityException::withField('User', 'email', $user->getEmail());
            }
            return $this->create($user);
         }

         return $this->update($user);
      } catch (PDOException $e) {
         // Check if it's a duplicate key error
         if ($e->getCode() === '23000') {
            throw DuplicateEntityException::withField('User', 'email', $user->getEmail());
         }
         
         throw DatabaseException::fromPDOException($e, 'saving user');
      }
   }

   public function delete(int $id): bool {
      try {
         // Verify user exists first
         $this->findById($id);
         
         $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
         $stmt->bindValue(':id', $id, PDO::PARAM_INT);

         return $stmt->execute() && $stmt->rowCount() > 0;
      } catch (PDOException $e) {
         throw DatabaseException::fromPDOException($e, "deleting user with ID {$id}");
      }
   }

   public function existsByEmail(string $email): bool {
      try {
         $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
         $stmt->bindValue(':email', $email);
         $stmt->execute();

         return $stmt->fetchColumn() > 0;
      } catch (PDOException $e) {
         throw DatabaseException::fromPDOException($e, 'checking email existence');
      }
   }

   public function count(): int {
      try {
         $stmt = $this->db->query("SELECT COUNT(*) FROM users");
         return (int) $stmt->fetchColumn();
      } catch (PDOException $e) {
         throw DatabaseException::fromPDOException($e, 'counting users');
      }
   }

   private function create(UserEntity $user): UserEntity {
      try {
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
      } catch (PDOException $e) {
         throw DatabaseException::fromPDOException($e, 'creating user');
      }
   }

   private function update(UserEntity $user): UserEntity {
      try {
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
      } catch (PDOException $e) {
         throw DatabaseException::fromPDOException($e, "updating user with ID {$user->getId()}");
      }
   }
}
