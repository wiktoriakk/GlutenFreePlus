<?php

require_once 'AppController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class AdminController extends AppController {
    
    private UserRepository $userRepository;

    public function __construct() {
        $this->userRepository = new UserRepository();
    }

    /**
     * Admin dashboard - user management
     */
    public function users(): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }

        $currentUser = AuthMiddleware::getCurrentUser();
        
        // Check if admin
        if ($currentUser['role'] !== 'admin') {
            http_response_code(403);
            $this->render('403.html');
            return;
        }

        $this->render('admin-users.html', ['user' => $currentUser]);
    }
    
    /**
     * Get users list 
     */
    public function getUsersList(): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }

        $currentUser = AuthMiddleware::getCurrentUser();
        
        if ($currentUser['role'] !== 'admin') {
            $this->json(['success' => false, 'error' => 'Unauthorized'], 403);
            return;
        }

        try {
            $users = $this->userRepository->findAll(100);
            
            $usersData = array_map(function($user) {
                return [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'role' => $user->getRole(),
                    'user_type' => $user->getUserType(),
                    'is_active' => $user->isActive(),
                    'created_at' => $user->getCreatedAt()->format('Y-m-d H:i')
                ];
            }, $users);
            
            $this->json([
                'success' => true,
                'users' => $usersData,
                'total' => count($usersData)
            ]);
            
        } catch (Exception $e) {
            error_log('Admin getUsersList error: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Failed to fetch users'], 500);
        }
    }
    
    /**
     * Toggle user active status
     */
    public function toggleUserStatus(): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }

        $currentUser = AuthMiddleware::getCurrentUser();
        
        if ($currentUser['role'] !== 'admin') {
            $this->json(['success' => false, 'error' => 'Unauthorized'], 403);
            return;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        
        if (!$userId) {
            $this->json(['success' => false, 'error' => 'Invalid user ID'], 400);
            return;
        }
        
        // Prevent admin from disabling themselves
        if ($userId === $currentUser['id']) {
            $this->json(['success' => false, 'error' => 'Cannot disable your own account'], 400);
            return;
        }

        try {
            $user = $this->userRepository->findById($userId);
            
            if (!$user) {
                $this->json(['success' => false, 'error' => 'User not found'], 404);
                return;
            }
            
            // Toggle status
            $newStatus = !$user->isActive();
            $user->setIsActive($newStatus);
            
            if ($this->userRepository->update($user)) {
                $this->json([
                    'success' => true,
                    'message' => $newStatus ? 'User activated' : 'User blocked',
                    'is_active' => $newStatus
                ]);
            } else {
                $this->json(['success' => false, 'error' => 'Failed to update user'], 500);
            }
            
        } catch (Exception $e) {
            error_log('Admin toggleUserStatus error: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Operation failed'], 500);
        }
    }
    
    /**
     * Change user role
     */
    public function changeUserRole(): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }

        $currentUser = AuthMiddleware::getCurrentUser();
        
        if ($currentUser['role'] !== 'admin') {
            $this->json(['success' => false, 'error' => 'Unauthorized'], 403);
            return;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        $newRole = $_POST['role'] ?? '';
        
        if (!$userId || !in_array($newRole, ['user', 'moderator', 'admin'])) {
            $this->json(['success' => false, 'error' => 'Invalid parameters'], 400);
            return;
        }
        
        // Prevent admin from demoting themselves
        if ($userId === $currentUser['id']) {
            $this->json(['success' => false, 'error' => 'Cannot change your own role'], 400);
            return;
        }

        try {
            $user = $this->userRepository->findById($userId);
            
            if (!$user) {
                $this->json(['success' => false, 'error' => 'User not found'], 404);
                return;
            }
            
            $user->setRole($newRole);
            
            if ($this->userRepository->update($user)) {
                $this->json([
                    'success' => true,
                    'message' => 'Role updated successfully',
                    'role' => $newRole
                ]);
            } else {
                $this->json(['success' => false, 'error' => 'Failed to update role'], 500);
            }
            
        } catch (Exception $e) {
            error_log('Admin changeUserRole error: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Operation failed'], 500);
        }
    }
    
    /**
     * Delete user (soft delete)
     */
    public function deleteUser(): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }

        $currentUser = AuthMiddleware::getCurrentUser();
        
        if ($currentUser['role'] !== 'admin') {
            $this->json(['success' => false, 'error' => 'Unauthorized'], 403);
            return;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        
        if (!$userId) {
            $this->json(['success' => false, 'error' => 'Invalid user ID'], 400);
            return;
        }
        
        // Prevent admin from deleting themselves
        if ($userId === $currentUser['id']) {
            $this->json(['success' => false, 'error' => 'Cannot delete your own account'], 400);
            return;
        }

        try {
            if ($this->userRepository->delete($userId)) {
                $this->json([
                    'success' => true,
                    'message' => 'User deleted successfully'
                ]);
            } else {
                $this->json(['success' => false, 'error' => 'Failed to delete user'], 500);
            }
            
        } catch (Exception $e) {
            error_log('Admin deleteUser error: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Operation failed'], 500);
        }
    }
}