<?php

class RateLimitMiddleware {
    
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_TIME = 900; // 15 minutes in seconds
    private const CLEANUP_CHANCE = 100; // 1% chance to cleanup old records
    
    /**
     * Check if IP is rate limited
     */
    public static function check(string $action = 'login'): bool {
        $ip = self::getClientIp();
        $key = self::getKey($action, $ip);
        
        self::maybeCleanup();
        
        if (self::isLocked($key)) {
            http_response_code(429);
            $remainingTime = self::getRemainingLockoutTime($key);
            die(json_encode([
                'success' => false,
                'error' => "Too many attempts. Please try again in " . ceil($remainingTime / 60) . " minutes."
            ]));
        }
        
        return true;
    }
    
    /**
     * Record failed attempt
     */
    public static function recordAttempt(string $action = 'login'): void {
        $ip = self::getClientIp();
        $key = self::getKey($action, $ip);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'attempts' => 0,
                'first_attempt' => time()
            ];
        }
        
        $_SESSION[$key]['attempts']++;
        $_SESSION[$key]['last_attempt'] = time();
        
        // Lock if exceeded max attempts
        if ($_SESSION[$key]['attempts'] >= self::MAX_ATTEMPTS) {
            $_SESSION[$key]['locked_until'] = time() + self::LOCKOUT_TIME;
        }
    }
    
    /**
     * Clear attempts on successful action
     */
    public static function clearAttempts(string $action = 'login'): void {
        $ip = self::getClientIp();
        $key = self::getKey($action, $ip);
        
        unset($_SESSION[$key]);
    }
    
    /**
     * Check if IP is currently locked
     */
    private static function isLocked(string $key): bool {
        if (!isset($_SESSION[$key]['locked_until'])) {
            return false;
        }
        
        if ($_SESSION[$key]['locked_until'] > time()) {
            return true;
        }
        
        // Lock expired, clear it
        unset($_SESSION[$key]);
        return false;
    }
    
    /**
     * Get remaining lockout time in seconds
     */
    private static function getRemainingLockoutTime(string $key): int {
        if (!isset($_SESSION[$key]['locked_until'])) {
            return 0;
        }
        
        $remaining = $_SESSION[$key]['locked_until'] - time();
        return max(0, $remaining);
    }
    
    /**
     * Get rate limit key
     */
    private static function getKey(string $action, string $ip): string {
        return 'rate_limit_' . $action . '_' . md5($ip);
    }
    
    /**
     * Get client IP address
     */
    private static function getClientIp(): string {
        $headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                // Handle multiple IPs (take first one)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return '0.0.0.0';
    }
    
    /**
     * Randomly cleanup old rate limit records
     */
    private static function maybeCleanup(): void {
        if (rand(1, self::CLEANUP_CHANCE) !== 1) {
            return;
        }
        
        $now = time();
        foreach ($_SESSION as $key => $value) {
            if (strpos($key, 'rate_limit_') === 0) {
                if (isset($value['locked_until']) && $value['locked_until'] < $now) {
                    unset($_SESSION[$key]);
                } elseif (isset($value['last_attempt']) && ($now - $value['last_attempt']) > self::LOCKOUT_TIME) {
                    unset($_SESSION[$key]);
                }
            }
        }
    }
}