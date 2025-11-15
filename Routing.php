<?php

require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/DashboardController.php';
require_once 'src/controllers/CommunityController.php';
require_once 'src/controllers/ProductController.php';
require_once 'src/controllers/PlaceController.php';
require_once 'src/controllers/RecipeController.php';

class Routing {
    private static $instance = null;
    
    public static $routes = [
        // Authentication routes
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
        
        // Dashboard
        'dashboard' => [
            'controller' => 'DashboardController',
            'action' => 'index'
        ],
        
        // Community routes
        'community' => [
            'controller' => 'CommunityController',
            'action' => 'index'
        ],
        'community/search' => [
            'controller' => 'CommunityController',
            'action' => 'search'
        ],
        'community/profile/(\d+)' => [
            'controller' => 'CommunityController',
            'action' => 'profile'
        ],
        
        // Product Scanner routes
        'scanner' => [
            'controller' => 'ProductController',
            'action' => 'index'
        ],
        'scanner/search' => [
            'controller' => 'ProductController',
            'action' => 'search'
        ],
        'scanner/product/(\d+)' => [
            'controller' => 'ProductController',
            'action' => 'show'
        ],
        
        // Places routes
        'places' => [
            'controller' => 'PlaceController',
            'action' => 'index'
        ],
        'places/search' => [
            'controller' => 'PlaceController',
            'action' => 'search'
        ],
        'places/(\d+)' => [
            'controller' => 'PlaceController',
            'action' => 'show'
        ],
        
        // Recipes routes
        'recipes' => [
            'controller' => 'RecipeController',
            'action' => 'index'
        ],
        'recipes/(\d+)' => [
            'controller' => 'RecipeController',
            'action' => 'show'
        ],
        'recipes/like/(\d+)' => [
            'controller' => 'RecipeController',
            'action' => 'like'
        ],
        'recipes/favorite/(\d+)' => [
            'controller' => 'RecipeController',
            'action' => 'favorite'
        ],
        'recipes/search' => [
            'controller' => 'RecipeController',
            'action' => 'search'
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
        http_response_code(404);
        include 'public/views/404.html';
    }
    
    /**
     * Sprawdza czy użytkownik jest zalogowany
     */
    public static function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }
    }
    
    /**
     * Zwraca zalogowanego użytkownika
     */
    public static function getAuthUser() {
        return $_SESSION['user'] ?? null;
    }
}