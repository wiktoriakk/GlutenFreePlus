<?php

require_once 'AppController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class CommunityController extends AppController {
    
    private UserRepository $userRepository;

    public function __construct() {
        $this->userRepository = new UserRepository();
    }

    public function index(): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }

        $user = AuthMiddleware::getCurrentUser();
        $this->render('community.html', ['user' => $user]);
    }

    public function getMembers(): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }

        $search = $_GET['search'] ?? '';
        $userType = $_GET['user_type'] ?? '';
        $limit = (int)($_GET['limit'] ?? 50);

        try {
            if (!empty($search)) {
                $users = $this->userRepository->searchByName($search, $limit);
            } elseif (!empty($userType)) {
                $users = $this->userRepository->findByUserType($userType, $limit);
            } else {
                $users = $this->userRepository->findAll($limit);
            }

            $usersData = array_map(function($user) {
                return [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'user_type' => $user->getUserType(),
                    'avatar' => $user->getAvatar() ?? '/public/images/avatars/default.png',
                    'bio' => $user->getBio(),
                ];
            }, $users);

            $this->json(['success' => true, 'users' => $usersData]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => 'Failed to fetch members'], 500);
        }
    }
}