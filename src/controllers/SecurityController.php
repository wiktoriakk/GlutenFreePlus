<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class SecurityController extends AppController {
    
    private UserRepository $userRepository;

    public function __construct() {
        $this->userRepository = new UserRepository();
    }

    public function login(): void {
        // Jeśli już zalogowany, przekieruj do dashboard
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
            return;
        }

        // GET request - wyświetl formularz
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->render('login.html');
            return;
        }

        // POST request - logowanie przez AJAX
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleLogin();
            return;
        }
    }

    public function register(): void {
        // Jeśli już zalogowany, przekieruj do dashboard
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
            return;
        }

        // GET request - wyświetl formularz
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->render('register.html');
            return;
        }

        // POST request - rejestracja przez AJAX
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleRegister();
            return;
        }
    }

    public function logout(): void {
        // Wyczyść sesję
        $_SESSION = [];
    
        // Zniszcz cookie sesji
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
    
        // Zniszcz sesję
        session_destroy();
    
        // Przekieruj
        $this->redirect('/login');
    }

    // ==================== PRIVATE METHODS ====================

    private function handleLogin(): void {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($contentType, 'application/json') !== false) {
            $data = json_decode(file_get_contents('php://input'), true);
        } else {
            $data = $_POST;
        }

        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        // Walidacja
        $errors = $this->validateLoginData($email, $password);
        if (!empty($errors)) {
            $this->json(['success' => false, 'errors' => $errors], 400);
            return;
        }

        // Sprawdź użytkownika
        $user = $this->userRepository->findByEmail($email);
        
        if (!$user) {
            $this->json([
                'success' => false, 
                'errors' => ['email' => 'User with this email does not exist']
            ], 401);
            return;
        }

        // Sprawdź hasło
        if (!$user->verifyPassword($password)) {
            $this->json([
                'success' => false, 
                'errors' => ['password' => 'Incorrect password']
            ], 401);
            return;
        }

        // Aktualizuj last_login
        $this->userRepository->updateLastLogin($user->getId());

        // Utwórz sesję
        $this->createSession($user);

        $this->json([
            'success' => true, 
            'message' => 'Login successful',
            'redirect' => '/dashboard'
        ]);
    }

    private function handleRegister(): void {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($contentType, 'application/json') !== false) {
            $data = json_decode(file_get_contents('php://input'), true);
        } else {
            $data = $_POST;
        }

        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';
        $userType = $data['user_type'] ?? null;

        // Walidacja
        $errors = $this->validateRegisterData($name, $email, $password, $confirmPassword);
        if (!empty($errors)) {
            $this->json(['success' => false, 'errors' => $errors], 400);
            return;
        }

        // Sprawdź czy email już istnieje
        if ($this->userRepository->emailExists($email)) {
            $this->json([
                'success' => false, 
                'errors' => ['email' => 'User with this email already exists']
            ], 400);
            return;
        }

        // Utwórz użytkownika
        $user = new User($email, '', $name);
        $user->setPassword($user->hashPassword($password));
        if ($userType) {
            $user->setUserType($userType);
        }

        $createdUser = $this->userRepository->create($user);

        if (!$createdUser) {
            $this->json([
                'success' => false, 
                'errors' => ['general' => 'Registration failed. Please try again.']
            ], 500);
            return;
        }

        // Utwórz sesję
        $this->createSession($createdUser);

        $this->json([
            'success' => true, 
            'message' => 'Registration successful',
            'redirect' => '/dashboard'
        ]);
    }

    private function validateLoginData(string $email, string $password): array {
        $errors = [];

        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        if (empty($password)) {
            $errors['password'] = 'Password is required';
        }

        return $errors;
    }

    private function validateRegisterData(string $name, string $email, string $password, string $confirmPassword): array {
        $errors = [];

        if (empty($name)) {
            $errors['name'] = 'Name is required';
        } elseif (strlen($name) < 2) {
            $errors['name'] = 'Name must be at least 2 characters';
        } elseif (strlen($name) > 100) {
            $errors['name'] = 'Name cannot exceed 100 characters';
        }

        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        if (empty($password)) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($password) < 6) {
            $errors['password'] = 'Password must be at least 6 characters';
        }

        if ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match';
        }

        return $errors;
    }

    private function createSession(User $user): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_email'] = $user->getEmail();
        $_SESSION['user_name'] = $user->getName();
        $_SESSION['user_role'] = $user->getRole();
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
    }

    private function isLoggedIn(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
}