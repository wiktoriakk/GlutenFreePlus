<?php

require_once 'AppController.php';

class SecurityController extends AppController {
    
    public function login(): void {
        // Tymczasowa implementacja - będzie rozbudowana
        $this->render('login.html');
    }
    
    public function register(): void {
        // Tymczasowa implementacja - będzie rozbudowana
        $this->render('register.html');
    }
    
    public function logout(): void {
        // Do implementacji
        $this->redirect('/login');
    }
}