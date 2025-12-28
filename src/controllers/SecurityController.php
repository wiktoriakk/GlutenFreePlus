<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/UserRepository.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../middleware/RateLimitMiddleware.php';

class SecurityController extends AppController {
    
    private UserRepository $userRepository;
    
    // Password requirements
    private const MIN_PASSWORD_LENGTH = 8;
    private const MAX_PASSWORD_LENGTH = 128;
    
    // Input length limits
    private const MAX_EMAIL_LENGTH = 255;
    private const MAX_NAME_LENGTH = 100;

    public function __construct() {
        $this->userRepository = new UserRepository();
    }

    public function login(): void {
        // GET - show login form
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Generate CSRF token for form
            $csrfToken = CsrfMiddleware::generateToken();
            $this->render('login.html', ['csrf_token' => $csrfToken]);
            return;
        }
        
        // POST - process login
        if (!CsrfMiddleware::verify()) {
            $this->json(['success' => false, 'error' => 'Invalid request. Please refresh the page.'], 403);
            return;
        }
        RateLimitMiddleware::check('login'); // Check rate limit
        
        $email = $this->sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validate input
        $errors = $this->validateLoginInput($email, $password);
        if (!empty($errors)) {
            RateLimitMiddleware::recordAttempt('login');
            $this->logFailedLogin($email, 'Invalid input');
            $this->json(['success' => false, 'error' => 'Invalid email or password'], 400);
            return;
        }
        
        // Attempt login
        $user = $this->userRepository->findByEmail($email);
        
        if (!$user || !password_verify($password, $user->getPassword())) {
            RateLimitMiddleware::recordAttempt('login');
            $this->logFailedLogin($email, 'Invalid credentials');
            
            // Generic error message - don't reveal if email exists
            $this->json(['success' => false, 'error' => 'Invalid email or password'], 401);
            return;
        }
        
        // Check if account is active
        if (!$user->isActive()) {
            RateLimitMiddleware::recordAttempt('login');
            $this->logFailedLogin($email, 'Account inactive');
            $this->json(['success' => false, 'error' => 'Account is disabled'], 403);
            return;
        }
        
        // Successful login
        RateLimitMiddleware::clearAttempts('login');
        $this->createSession($user);
        
        $this->json([
            'success' => true,
            'redirect' => '/dashboard'
        ]);
    }

    public function register(): void {
        // GET - show registration form
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $csrfToken = CsrfMiddleware::generateToken();
            $this->render('register.html', ['csrf_token' => $csrfToken]);
            return;
        }
        
        // POST - process registration
        if (!CsrfMiddleware::verify()) {
            $this->json(['success' => false, 'error' => 'Invalid request. Please refresh the page.'], 403);
            return;
        }
        RateLimitMiddleware::check('register');
        
        $email = $this->sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $name = $this->sanitizeInput($_POST['name'] ?? '');
        $userType = $this->sanitizeInput($_POST['user_type'] ?? '');
        
        // Validate input
        $errors = $this->validateRegistrationInput($email, $password, $name);
        if (!empty($errors)) {
            RateLimitMiddleware::recordAttempt('register');
            $this->json(['success' => false, 'errors' => $errors], 400);
            return;
        }
        
        // Check if email already exists
        if ($this->userRepository->findByEmail($email)) {
            RateLimitMiddleware::recordAttempt('register');
            $this->json(['success' => false, 'error' => 'An account with this email already exists'], 409);
            return;
        }
        
        // Create user
        try {
            $user = new User($email, password_hash($password, PASSWORD_BCRYPT), $name);
            
            if ($userType && in_array($userType, ['Celiac', 'Nutritionist', 'Food Blogger', 'Chef'])) {
                $user->setUserType($userType);
            }
            
            $this->userRepository->create($user);
            
            RateLimitMiddleware::clearAttempts('register');
            
            $this->json([
                'success' => true,
                'message' => 'Registration successful',
                'redirect' => '/login'
            ]);
            
        } catch (Exception $e) {
            error_log('Registration error: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Registration failed. Please try again.'], 500);
        }
    }

    public function logout(): void {
        // Clear session data
        $_SESSION = [];
        
        // Destroy session cookie
        if (isset($_COOKIE[session_name()])) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 3600,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        // Destroy session
        session_destroy();
        
        // Redirect to login
        $this->redirect('/login');
    }

    /**
     * Create user session with security measures
     */
    private function createSession(User $user): void {
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        
        // Store minimal user data in session
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_email'] = $user->getEmail();
        $_SESSION['user_name'] = $user->getName();
        $_SESSION['user_role'] = $user->getRole();
        $_SESSION['last_activity'] = time();
        
        // Update last login time
        try {
            $this->userRepository->updateLastLogin($user->getId());
        } catch (Exception $e) {
            error_log('Failed to update last login: ' . $e->getMessage());
        }
    }
    
    /**
     * Validate login input
     */
    private function validateLoginInput(string $email, string $password): array {
        $errors = [];
        
        if (empty($email) || !$this->isValidEmail($email)) {
            $errors[] = 'Invalid email format';
        }
        
        if (strlen($email) > self::MAX_EMAIL_LENGTH) {
            $errors[] = 'Email too long';
        }
        
        if (empty($password)) {
            $errors[] = 'Password is required';
        }
        
        if (strlen($password) > self::MAX_PASSWORD_LENGTH) {
            $errors[] = 'Password too long';
        }
        
        return $errors;
    }
    
    /**
     * Validate registration input
     */
    private function validateRegistrationInput(string $email, string $password, string $name): array {
        $errors = [];
        
        // Email validation
        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!$this->isValidEmail($email)) {
            $errors['email'] = 'Invalid email format';
        } elseif (strlen($email) > self::MAX_EMAIL_LENGTH) {
            $errors['email'] = 'Email too long';
        }
        
        // Password validation
        if (empty($password)) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($password) < self::MIN_PASSWORD_LENGTH) {
            $errors['password'] = 'Password must be at least ' . self::MIN_PASSWORD_LENGTH . ' characters';
        } elseif (strlen($password) > self::MAX_PASSWORD_LENGTH) {
            $errors['password'] = 'Password too long';
        } elseif (!$this->isStrongPassword($password)) {
            $errors['password'] = 'Password must contain at least one uppercase letter, one lowercase letter, and one number';
        }
        
        // Name validation
        if (empty($name)) {
            $errors['name'] = 'Name is required';
        } elseif (strlen($name) > self::MAX_NAME_LENGTH) {
            $errors['name'] = 'Name too long';
        } elseif (!preg_match('/^[a-zA-Z\s\'-]+$/', $name)) {
            $errors['name'] = 'Name contains invalid characters';
        }
        
        return $errors;
    }
    
    /**
     * Check if email format is valid
     */
    private function isValidEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Check password strength
     */
    private function isStrongPassword(string $password): bool {
        // At least one uppercase, one lowercase, one number
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $password) === 1;
    }
    
    /**
     * Sanitize user input
     */
    private function sanitizeInput(string $input): string {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Log failed login attempts (without password)
     */
    private function logFailedLogin(string $email, string $reason): void {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $timestamp = date('Y-m-d H:i:s');
        
        $logMessage = sprintf(
            "[%s] Failed login attempt - Email: %s, IP: %s, Reason: %s, User-Agent: %s\n",
            $timestamp,
            $email,
            $ip,
            $reason,
            $userAgent
        );
        
        error_log($logMessage, 3, __DIR__ . '/../../logs/failed_logins.log');
    }
}
