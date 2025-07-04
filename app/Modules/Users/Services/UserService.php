<?php
/**
 * @package     phast/app
 * @subpackage  Modules/Users/Services
 * @file        UserService
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description User service layer
 */

declare(strict_types=1);

namespace Phast\App\Modules\Users\Services;

use Phast\App\Modules\Users\Models\Entities\UserEntity;
use Phast\App\Modules\Users\Models\Repositories\UserRepositoryInterface;
use Phast\App\Modules\Users\Models\ValueObjects\Email;
use Psr\Log\LoggerInterface;

class UserService {
   private UserRepositoryInterface $userRepository;
   private LoggerInterface $logger;

   public function __construct(
      UserRepositoryInterface $userRepository,
      LoggerInterface $logger
   ) {
      $this->userRepository = $userRepository;
      $this->logger = $logger;
   }

   public function getAllUsers(int $page = 1, int $limit = 10): array {
      $this->logger->info('Fetching all users', ['page' => $page, 'limit' => $limit]);

      $users = $this->userRepository->findAll($page, $limit);
      $total = $this->userRepository->count();

      return [
         'data' => array_map(fn($user) => $user->toArray(), $users),
         'pagination' => [
            'current_page' => $page,
            'per_page' => $limit,
            'total' => $total,
            'total_pages' => ceil($total / $limit),
         ]
      ];
   }

   public function getUserById(int $id): ?UserEntity {
      $this->logger->info('Fetching user by ID', ['id' => $id]);

      $user = $this->userRepository->findById($id);

      if (!$user) {
         $this->logger->warning('User not found', ['id' => $id]);
      }

      return $user;
   }

   public function createUser(array $data): UserEntity {
      $this->logger->info('Creating new user', ['email' => $data['email']]);

      // Validate email format
      new Email($data['email']);

      // Check if email already exists
      if ($this->userRepository->existsByEmail($data['email'])) {
         throw new \InvalidArgumentException('Email already exists');
      }

      $user = new UserEntity(
         $data['name'],
         $data['email'],
         $data['password']
      );

      $savedUser = $this->userRepository->save($user);

      $this->logger->info('User created successfully', ['id' => $savedUser->getId()]);

      return $savedUser;
   }

   public function updateUser(int $id, array $data): UserEntity {
      $this->logger->info('Updating user', ['id' => $id]);

      $user = $this->userRepository->findById($id);

      if (!$user) {
         throw new \InvalidArgumentException('User not found');
      }

      // Validate email if provided and different
      if (isset($data['email']) && $data['email'] !== $user->getEmail()) {
         new Email($data['email']);

         if ($this->userRepository->existsByEmail($data['email'])) {
            throw new \InvalidArgumentException('Email already exists');
         }

         $user->setEmail($data['email']);
      }

      if (isset($data['name'])) {
         $user->setName($data['name']);
      }

      if (isset($data['password'])) {
         $user->setPassword($data['password']);
      }

      $updatedUser = $this->userRepository->save($user);

      $this->logger->info('User updated successfully', ['id' => $id]);

      return $updatedUser;
   }

   public function deleteUser(int $id): bool {
      $this->logger->info('Deleting user', ['id' => $id]);

      $user = $this->userRepository->findById($id);

      if (!$user) {
         throw new \InvalidArgumentException('User not found');
      }

      $deleted = $this->userRepository->delete($id);

      if ($deleted) {
         $this->logger->info('User deleted successfully', ['id' => $id]);
      } else {
         $this->logger->error('Failed to delete user', ['id' => $id]);
      }

      return $deleted;
   }

   public function getUserByEmail(string $email): ?UserEntity {
      $this->logger->info('Fetching user by email', ['email' => $email]);

      // Validate email format
      new Email($email);

      return $this->userRepository->findByEmail($email);
   }
}
