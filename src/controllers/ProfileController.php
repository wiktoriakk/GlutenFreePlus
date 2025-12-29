<?php

require_once 'AppController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class ProfileController extends AppController {
    
    private UserRepository $userRepository;

    public function __construct() {
        $this->userRepository = new UserRepository();
    }

    public function show(string $userId): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }

        $currentUser = AuthMiddleware::getCurrentUser();
        
        try {
            $user = $this->userRepository->findById((int)$userId);
            
            if (!$user) {
                http_response_code(404);
                include 'public/views/404.html';
                return;
            }

            $this->render('profile.html', [
                'user' => $user,
                'currentUser' => $currentUser,
                'isOwnProfile' => ($currentUser['id'] ?? $currentUser['user_id'] ?? null) == $userId
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo "Error loading profile";
        }
    }

    public function getData(string $userId): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }

        try {
            $user = $this->userRepository->findById((int)$userId);
            
            if (!$user) {
                $this->json(['success' => false, 'error' => 'User not found'], 404);
                return;
            }

            $this->json([
                'success' => true,
                'user' => [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'user_type' => $user->getUserType(),
                    'avatar' => $user->getAvatar(),
                    'bio' => $user->getBio(),
                    'created_at' => date('Y-m-d')
                ]
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => 'Server error'], 500);
        }
    }

    /**
 * Show current user's profile
 */
    public function showCurrent(): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }

        $currentUser = AuthMiddleware::getCurrentUser();
        $userId = $currentUser['id'] ?? $currentUser['user_id'] ?? null;
    
        if (!$userId) {
            $this->redirect('/login');
            return;
        }
    
        try {
            $user = $this->userRepository->findById((int)$userId);
        
            if (!$user) {
                http_response_code(404);
                include 'public/views/404.html';
                return;
            }

            // Renderuj z danymi użytkownika
            $this->render('profile.html', [
                'user' => $user->toArray(),
                'currentUser' => $currentUser,
                'isOwnProfile' => true
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo "Error loading profile";
        }
    }
}