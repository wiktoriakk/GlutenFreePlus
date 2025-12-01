<?php

require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/DashboardController.php';
require_once 'src/controllers/CommunityController.php';


class Routing {
    private static $instance = null;
    
    public static $routes = [
        '' => [
            'controller' => 'SecurityController',
            'action' => 'login'
        ],
        'login' => [
            'controller' => 'SecurityController',
            'action' => 'login'
        ],
        'register' => [
            'controller' => 'SecurityController',
            'action' => 'register'
        ],
        'logout' => [
            'controller' => 'SecurityController',
            'action' => 'logout'
        ],
        'dashboard' => [
            'controller' => 'DashboardController',
            'action' => 'index'
        ],
        'dashboard/(\d+)' => [
            'controller' => 'DashboardController',
            'action' => 'show'
        ],
        'community' => [
            'controller' => 'CommunityController',
            'action' => 'index'
        ],
        'community/members' => [
            'controller' => 'CommunityController',
            'action' => 'getMembers'
        ],
        'scanner' => [
            'controller' => 'ScannerController',
            'action' => 'index'
        ],
        'scanner/search' => [
            'controller' => 'ScannerController',
            'action' => 'search'
        ],
    ];

    private function __construct() {}

    public static function getInstance(): Routing {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function run(string $path): void {
        // Exact match
        if (isset(self::$routes[$path])) {
            $controller = self::$routes[$path]['controller'];
            $action = self::$routes[$path]['action'];
            
            $controllerObj = new $controller;
            $controllerObj->$action();
            return;
        }
        
        // Regex patterns
        foreach (self::$routes as $pattern => $route) {
            if (preg_match('#^' . $pattern . '$#', $path, $matches)) {
                $controller = $route['controller'];
                $action = $route['action'];
                
                $controllerObj = new $controller;
                array_shift($matches);
                $controllerObj->$action(...$matches);
                return;
            }
        }
        
        // 404
        include 'public/views/404.html';
    }
}