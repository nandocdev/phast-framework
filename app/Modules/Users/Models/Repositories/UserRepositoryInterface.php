<?php
/**
 * @package     phast/app
 * @subpackage  Modules/Users/Models/Repositories
 * @file        UserRepositoryInterface
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description User repository interface
 */

declare(strict_types=1);

namespace Phast\App\Modules\Users\Models\Repositories;

use Phast\App\Modules\Users\Models\Entities\UserEntity;
use Phast\Core\Exceptions\Domain\EntityNotFoundException;

interface UserRepositoryInterface {
   /**
    * Find all users with pagination
    */
   public function findAll(int $page = 1, int $limit = 10): array;

   /**
    * Find user by ID
    * @throws EntityNotFoundException
    */
   public function findById(int $id): UserEntity;

   /**
    * Find user by ID or return null
    */
   public function findByIdOrNull(int $id): ?UserEntity;

   /**
    * Find user by email
    * @throws EntityNotFoundException
    */
   public function findByEmail(string $email): UserEntity;

   /**
    * Find user by email or return null
    */
   public function findByEmailOrNull(string $email): ?UserEntity;

   /**
    * Save user (create or update)
    */
   public function save(UserEntity $user): UserEntity;

   /**
    * Delete user
    */
   public function delete(int $id): bool;

   /**
    * Check if email exists
    */
   public function existsByEmail(string $email): bool;

   /**
    * Count total users
    */
   public function count(): int;
}
