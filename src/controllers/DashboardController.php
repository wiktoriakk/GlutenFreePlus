<?php

require_once 'AppController.php';

class DashboardController extends AppController {
    
    public function index(): void {
        // Tymczasowa implementacja - będzie rozbudowana
        $this->render('dashboard.html');
    }
    
    public function show(int $id): void {
        // Do implementacji
        $this->render('dashboard.html', ['userId' => $id]);
    }
}