<?php

class AppController {
    
    protected function render(string $template, array $variables = []): void {
        $templatePath = 'public/views/' . $template;
        
        if (file_exists($templatePath)) {
            extract($variables);
            require_once $templatePath;
        } else {
            require_once 'public/views/404.html';
        }
    }
    
    protected function redirect(string $location): void {
        header("Location: $location");
        exit;
    }
    
    protected function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}