<?php

require_once 'AppController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';


class DashboardController extends AppController {
    
    public function index(): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }
        
        $user = AuthMiddleware::getCurrentUser();
        $this->render('dashboard.html', ['user' => $user]);
    }
    
    public function show(int $id): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }
        
        $user = AuthMiddleware::getCurrentUser();
        $this->render('dashboard.html', ['user' => $user, 'profileId' => $id]);
    }
}