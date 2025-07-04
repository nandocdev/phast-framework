<?php
/**
 * @package     phast/app
 * @subpackage  Modules/Users/Controllers
 * @file        UserController
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-07-04
 * @version     1.0.0
 * @description User controller
 */

declare(strict_types=1);

namespace Phast\App\Modules\Users\Controllers;

use Phast\Core\Http\Controller;
use Phast\Core\Http\Request;
use Phast\Core\Http\Response;
use Phast\App\Modules\Users\Services\UserService;
use Phast\Core\Validation\ValidationException;

class UserController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Get all users with pagination
     */
    public function index(Request $request): Response
    {
        try {
            $page = (int) $request->getQuery('page', 1);
            $limit = (int) $request->getQuery('limit', 10);
            
            // Validate pagination parameters
            if ($page < 1) $page = 1;
            if ($limit < 1 || $limit > 100) $limit = 10;
            
            $result = $this->userService->getAllUsers($page, $limit);
            
            return $this->json($result);
        } catch (\Throwable $e) {
            logger()->error('Error fetching users', ['error' => $e->getMessage()]);
            return $this->json(['error' => 'Failed to fetch users'], 500);
        }
    }

    /**
     * Get user by ID
     */
    public function show(Request $request): Response
    {
        try {
            $id = (int) $request->id;
            
            if ($id <= 0) {
                return $this->json(['error' => 'Invalid user ID'], 400);
            }
            
            $user = $this->userService->getUserById($id);
            
            if (!$user) {
                return $this->json(['error' => 'User not found'], 404);
            }
            
            return $this->json($user->toArray());
        } catch (\Throwable $e) {
            logger()->error('Error fetching user', ['id' => $request->id, 'error' => $e->getMessage()]);
            return $this->json(['error' => 'Failed to fetch user'], 500);
        }
    }

    /**
     * Create new user
     */
    public function store(Request $request): Response
    {
        try {
            $data = $this->validate($request, [
                'name' => 'required|min:2|max:100',
                'email' => 'required|email',
                'password' => 'required|min:6'
            ]);
            
            $user = $this->userService->createUser($data);
            
            return $this->json($user->toArray(), 201);
        } catch (ValidationException $e) {
            return $this->json(['errors' => $e->getErrors()], 422);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            logger()->error('Error creating user', ['error' => $e->getMessage()]);
            return $this->json(['error' => 'Failed to create user'], 500);
        }
    }

    /**
     * Update user
     */
    public function update(Request $request): Response
    {
        try {
            $id = (int) $request->id;
            
            if ($id <= 0) {
                return $this->json(['error' => 'Invalid user ID'], 400);
            }
            
            $data = $this->validate($request, [
                'name' => 'min:2|max:100',
                'email' => 'email',
                'password' => 'min:6'
            ]);
            
            $user = $this->userService->updateUser($id, $data);
            
            return $this->json($user->toArray());
        } catch (ValidationException $e) {
            return $this->json(['errors' => $e->getErrors()], 422);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            logger()->error('Error updating user', ['id' => $request->id, 'error' => $e->getMessage()]);
            return $this->json(['error' => 'Failed to update user'], 500);
        }
    }

    /**
     * Delete user
     */
    public function destroy(Request $request): Response
    {
        try {
            $id = (int) $request->id;
            
            if ($id <= 0) {
                return $this->json(['error' => 'Invalid user ID'], 400);
            }
            
            $deleted = $this->userService->deleteUser($id);
            
            if (!$deleted) {
                return $this->json(['error' => 'Failed to delete user'], 500);
            }
            
            return $this->json(['message' => 'User deleted successfully']);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            logger()->error('Error deleting user', ['id' => $request->id, 'error' => $e->getMessage()]);
            return $this->json(['error' => 'Failed to delete user'], 500);
        }
    }
}
