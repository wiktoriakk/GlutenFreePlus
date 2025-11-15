<?php

class SecurityController {
    
    public function login() {
        // Jeśli użytkownik już zalogowany, przekieruj do dashboardu
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit();
        }

        // Obsługa POST (logowanie)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // Walidacja
            $errors = [];
            
            if (empty($email)) {
                $errors[] = "Email is required";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email format";
            }
            
            if (empty($password)) {
                $errors[] = "Password is required";
            }
            
            // Jeśli brak błędów, sprawdź użytkownika w bazie
            if (empty($errors)) {
                // TODO: Sprawdź użytkownika w bazie danych
                // Na razie mock - hardcoded użytkownik
                if ($email === 'admin@glutenfree.com' && $password === 'admin123') {
                    // Ustaw sesję
                    $_SESSION['user_id'] = 1;
                    $_SESSION['email'] = $email;
                    $_SESSION['role'] = 'admin';
                    
                    header('Location: /dashboard');
                    exit();
                } else {
                    $errors[] = "Invalid credentials";
                }
            }
            
            // Jeśli są błędy, pokaż formularz z błędami
            return $this->render('login', ['errors' => $errors, 'email' => $email]);
        }
        
        // GET - pokaż formularz logowania
        return $this->render('login');
    }
    
    public function register() {
        // Jeśli użytkownik już zalogowany, przekieruj
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit();
        }

        // Obsługa POST (rejestracja)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Walidacja
            $errors = [];
            
            if (empty($email)) {
                $errors[] = "Email is required";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email format";
            }
            
            if (empty($password)) {
                $errors[] = "Password is required";
            } elseif (strlen($password) < 8) {
                $errors[] = "Password must be at least 8 characters";
            } elseif (!preg_match('/[A-Z]/', $password)) {
                $errors[] = "Password must contain at least one uppercase letter";
            } elseif (!preg_match('/[0-9]/', $password)) {
                $errors[] = "Password must contain at least one number";
            }
            
            if ($password !== $confirmPassword) {
                $errors[] = "Passwords do not match";
            }
            
            // Jeśli brak błędów
            if (empty($errors)) {
                // TODO: Zapisz użytkownika w bazie danych
                // Na razie mock - przekieruj do logowania
                $_SESSION['success'] = "Registration successful! Please login.";
                header('Location: /login');
                exit();
            }
            
            // Jeśli są błędy, pokaż formularz z błędami
            return $this->render('register', [
                'errors' => $errors, 
                'email' => $email
            ]);
        }
        
        // GET - pokaż formularz rejestracji
        return $this->render('register');
    }
    
    public function logout() {
        // Wyczyść sesję
        session_unset();
        session_destroy();
        
        header('Location: /login');
        exit();
    }
    
    // Helper do renderowania widoków
    private function render(string $view, array $data = []) {
        // Wyciągnij zmienne z tablicy
        extract($data);
        
        // Załaduj widok
        $viewPath = "public/views/$view.php";
        
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            die("View not found: $viewPath");
        }
    }
}