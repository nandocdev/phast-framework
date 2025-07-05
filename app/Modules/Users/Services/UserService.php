<?php
/**
 * @package     phast/app
 * @subpackage  Modules/Users/Services
 * @file        UserService
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description User service layer with DTOs and improved exception handling
 */

declare(strict_types=1);

namespace Phast\App\Modules\Users\Services;

use Phast\App\Modules\Users\Models\Entities\UserEntity;
use Phast\App\Modules\Users\Models\Repositories\UserRepositoryInterface;
use Phast\App\Modules\Users\DataTransfer\CreateUserDTO;
use Phast\App\Modules\Users\DataTransfer\UpdateUserDTO;
use Phast\App\Modules\Users\Events\UserCreated;
use Phast\App\Modules\Users\Events\UserUpdated;
use Phast\App\Modules\Users\Events\UserDeleted;
use Phast\Core\Exceptions\Domain\EntityNotFoundException;
use Phast\Core\Exceptions\Domain\DuplicateEntityException;
use Phast\Core\Events\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

class UserService {
   private UserRepositoryInterface $userRepository;
   private LoggerInterface $logger;
   private EventDispatcherInterface $eventDispatcher;

   public function __construct(
      UserRepositoryInterface $userRepository,
      LoggerInterface $logger,
      EventDispatcherInterface $eventDispatcher
   ) {
      $this->userRepository = $userRepository;
      $this->logger = $logger;
      $this->eventDispatcher = $eventDispatcher;
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

   public function getUserById(int $id): UserEntity {
      $this->logger->info('Fetching user by ID', ['id' => $id]);

      try {
         return $this->userRepository->findById($id);
      } catch (EntityNotFoundException $e) {
         $this->logger->warning('User not found', ['id' => $id]);
         throw $e;
      }
   }

   public function getUserByIdOrNull(int $id): ?UserEntity {
      $this->logger->info('Fetching user by ID (nullable)', ['id' => $id]);

      return $this->userRepository->findByIdOrNull($id);
   }

   public function createUser(array $data): UserEntity {
      $this->logger->info('Creating new user', ['email' => $data['email'] ?? 'unknown']);

      try {
         // Create and validate DTO
         $dto = CreateUserDTO::fromArray($data);

         // Create entity
         $user = new UserEntity(
            $dto->name,
            $dto->email,
            $dto->getHashedPassword()
         );

         $savedUser = $this->userRepository->save($user);

         $this->logger->info('User created successfully', ['id' => $savedUser->getId()]);

         // Dispatch user created event
         $this->eventDispatcher->dispatch(new UserCreated($savedUser));

         return $savedUser;
      } catch (DuplicateEntityException $e) {
         $this->logger->warning('Failed to create user: email already exists', ['email' => $data['email'] ?? 'unknown']);
         throw $e;
      }
   }

   public function updateUser(int $id, array $data): UserEntity {
      $this->logger->info('Updating user', ['id' => $id]);

      try {
         // Find existing user (will throw if not found)
         $user = $this->userRepository->findById($id);

         // Create and validate DTO
         $dto = UpdateUserDTO::fromArray($data);

         if (!$dto->hasUpdates()) {
            $this->logger->info('No updates provided for user', ['id' => $id]);
            return $user;
         }

         // Update user properties
         $updates = $dto->getModifiedFields();

         if (isset($updates['name'])) {
            $user->setName($updates['name']);
         }

         if (isset($updates['email'])) {
            $user->setEmail($updates['email']);
         }

         if (isset($updates['password'])) {
            $user->setPassword($dto->getHashedPassword());
         }

         $updatedUser = $this->userRepository->save($user);

         $this->logger->info('User updated successfully', ['id' => $id]);

         // Dispatch user updated event
         $this->eventDispatcher->dispatch(new UserUpdated($updatedUser, $updates));

         return $updatedUser;
      } catch (DuplicateEntityException $e) {
         $this->logger->warning('Failed to update user: email already exists', ['id' => $id, 'email' => $data['email'] ?? 'unknown']);
         throw $e;
      }
   }

   public function deleteUser(int $id): bool {
      $this->logger->info('Deleting user', ['id' => $id]);

      try {
         // Get user info before deletion for the event
         $user = $this->userRepository->findById($id);
         $userEmail = $user->getEmail();

         $result = $this->userRepository->delete($id);

         if ($result) {
            $this->logger->info('User deleted successfully', ['id' => $id]);

            // Dispatch user deleted event
            $this->eventDispatcher->dispatch(new UserDeleted($id, $userEmail));
         }

         return $result;
      } catch (EntityNotFoundException $e) {
         $this->logger->warning('Cannot delete user: not found', ['id' => $id]);
         throw $e;
      }
   }

   public function getUserByEmail(string $email): ?UserEntity {
      $this->logger->info('Fetching user by email', ['email' => $email]);

      return $this->userRepository->findByEmailOrNull($email);
   }
}
