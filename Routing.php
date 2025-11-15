<?php

require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/DashboardController.php';


class Routing {
    private static $instance = null;
    
    public static $routes = [
        'login' => [
            'controller' => 'SecurityController',
            'action' => 'login'
        ],
        'register' => [
            'controller' => 'SecurityController',
            'action' => 'register'
        ],
        'dashboard' => [
            'controller' => 'DashboardController',
            'action' => 'index'
        ],
        // Dynamiczna trasa dla /dashboard/{id}
        'dashboard/(\d+)' => [
            'controller' => 'DashboardController',
            'action' => 'show'
        ],
    ];

    private function __construct() {
        // Prywatny konstruktor dla singletona
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function run(string $path) {
        // Sprawdzamy dokładne dopasowanie
        if (isset(self::$routes[$path])) {
            $controller = self::$routes[$path]['controller'];
            $action = self::$routes[$path]['action'];
            
            $controllerObj = new $controller;
            $controllerObj->$action();
            return;
        }
        
        // Sprawdzamy wzorce regex
        foreach (self::$routes as $pattern => $route) {
            if (preg_match('#^' . $pattern . '$#', $path, $matches)) {
                $controller = $route['controller'];
                $action = $route['action'];
                
                $controllerObj = new $controller;
                // Przekazujemy parametry z URL (bez pierwszego elementu - pełnego dopasowania)
                array_shift($matches);
                $controllerObj->$action(...$matches);
                return;
            }
        }
        
        // Jeśli nie znaleziono trasy
        include 'public/views/404.html';
    }
}