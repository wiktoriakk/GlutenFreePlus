<?php

class ErrorHandler {
    
    /**
     * Initialize error handler
     */
    public static function init(): void {
        // Don't display errors in production
        if (self::isProduction()) {
            ini_set('display_errors', '0');
            ini_set('display_startup_errors', '0');
            error_reporting(E_ALL);
        } else {
            // Development mode
            ini_set('display_errors', '1');
            ini_set('display_startup_errors', '1');
            error_reporting(E_ALL);
        }
        
        // Set custom error handler
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleFatalError']);
    }
    
    /**
     * Handle regular errors
     */
    public static function handleError($severity, $message, $file, $line): bool {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $errorType = self::getErrorType($severity);
        
        // Log error
        self::logError($errorType, $message, $file, $line);
        
        // Show user-friendly error in production
        if (self::isProduction()) {
            self::showErrorPage(500, 'An error occurred. Please try again later.');
        }
        
        return true;
    }
    
    /**
     * Handle exceptions
     */
    public static function handleException($exception): void {
        $message = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $trace = $exception->getTraceAsString();
        
        // Log exception
        self::logError('Exception', $message, $file, $line, $trace);
        
        // Show user-friendly error
        if (self::isProduction()) {
            self::showErrorPage(500, 'An unexpected error occurred. Please try again later.');
        } else {
            echo '<pre>';
            echo 'Exception: ' . $message . "\n";
            echo 'File: ' . $file . ':' . $line . "\n";
            echo 'Trace: ' . $trace;
            echo '</pre>';
        }
    }
    
    /**
     * Handle fatal errors
     */
    public static function handleFatalError(): void {
        $error = error_get_last();
        
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            self::logError('Fatal Error', $error['message'], $error['file'], $error['line']);
            
            if (self::isProduction()) {
                self::showErrorPage(500, 'A critical error occurred. Please contact support.');
            }
        }
    }
    
    /**
     * Log error to file
     */
    private static function logError(string $type, string $message, string $file, int $line, string $trace = ''): void {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = sprintf(
            "[%s] %s: %s in %s:%d\n%s\n\n",
            $timestamp,
            $type,
            $message,
            $file,
            $line,
            $trace ? "Stack trace:\n" . $trace : ''
        );
        
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        error_log($logMessage, 3, $logDir . '/errors.log');
    }
    
    /**
     * Show user-friendly error page
     */
    private static function showErrorPage(int $code, string $message): void {
        http_response_code($code);
        
        // Try to load custom error page
        $errorPage = __DIR__ . '/../../public/views/' . $code . '.html';
        if (file_exists($errorPage)) {
            include $errorPage;
        } else {
            // Fallback error page
            echo '<!DOCTYPE html>
<html>
<head>
    <title>Error</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f5f5f5; }
        .error-container { max-width: 500px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #d32f2f; }
        p { color: #666; }
        a { color: #1B5E20; text-decoration: none; }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>Oops! Something went wrong</h1>
        <p>' . htmlspecialchars($message) . '</p>
        <p><a href="/">← Back to Home</a></p>
    </div>
</body>
</html>';
        }
        exit;
    }
    
    /**
     * Check if running in production
     */
    private static function isProduction(): bool {
        return !isset($_ENV['APP_ENV']) || $_ENV['APP_ENV'] === 'production';
    }
    
    /**
     * Get human-readable error type
     */
    private static function getErrorType(int $severity): string {
        $types = [
            E_ERROR => 'Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parse Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Strict Notice',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated',
        ];
        
        return $types[$severity] ?? 'Unknown Error';
    }
}

// Initialize error handler
ErrorHandler::init();