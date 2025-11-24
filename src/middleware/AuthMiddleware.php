<?php

class AuthMiddleware {
    
    public static function requireAuth(): bool {
        self::startSession();
        
        if (!self::isLoggedIn()) {
            self::handleUnauthorized();
            return false;
        }
        
        // Sprawdź czy sesja nie wygasła (24h)
        if (self::isSessionExpired()) {
            self::logout();
            self::handleUnauthorized();
            return false;
        }
        
        return true;
    }
    
    public static function requireRole(string ...$roles): bool {
        if (!self::requireAuth()) {
            return false;
        }
        
        $userRole = $_SESSION['user_role'] ?? '';
        
        if (!in_array($userRole, $roles)) {
            self::handleForbidden();
            return false;
        }
        
        return true;
    }
    
    public static function isLoggedIn(): bool {
        self::startSession();
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public static function getCurrentUser(): ?array {
        self::startSession();
        
        if (!self::isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'email' => $_SESSION['user_email'] ?? null,
            'name' => $_SESSION['user_name'] ?? null,
            'role' => $_SESSION['user_role'] ?? null,
        ];
    }
    
    public static function getCurrentUserId(): ?int {
        self::startSession();
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }
    
    public static function logout(): void {
        self::startSession();
        
        $_SESSION = [];
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        session_destroy();
    }
    
    // ==================== PRIVATE METHODS ====================
    
    private static function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    private static function isSessionExpired(): bool {
        $maxLifetime = 24 * 60 * 60; // 24 hours
        $loginTime = $_SESSION['login_time'] ?? 0;
        
        return (time() - $loginTime) > $maxLifetime;
    }
    
    private static function handleUnauthorized(): void {
        if (self::isAjaxRequest()) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Unauthorized',
                'redirect' => '/login'
            ]);
            exit;
        }
        
        header('Location: /login');
        exit;
    }
    
    private static function handleForbidden(): void {
        if (self::isAjaxRequest()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Forbidden'
            ]);
            exit;
        }
        
        http_response_code(403);
        include 'public/views/403.html';
        exit;
    }
    
    private static function isAjaxRequest(): bool {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' ||
               strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;
    }
}