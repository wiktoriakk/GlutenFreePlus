<?php

require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/DashboardController.php';
require_once 'src/controllers/CommunityController.php';
require_once 'src/controllers/ScannerController.php';
require_once 'src/controllers/DiscoverController.php';
require_once 'src/controllers/RecipeController.php';
require_once 'src/controllers/ProfileController.php';
require_once 'src/controllers/AdminController.php';
require_once 'src/controllers/ModeratorController.php';

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
        'discover' => [
            'controller' => 'DiscoverController',
            'action' => 'index'
        ],
        'discover/places' => [
            'controller' => 'DiscoverController',
            'action' => 'getPlaces'
        ],
        'recipes' => [
            'controller' => 'RecipeController',
            'action' => 'index'
        ],
        'recipes/get' => [
            'controller' => 'RecipeController',
            'action' => 'getRecipes'
        ],
        'profile/(\d+)' => [
            'controller' => 'ProfileController',
            'action' => 'show'
        ],
        'profile/(\d+)/data' => [
            'controller' => 'ProfileController', 
            'action' => 'getData'
        ],
        'admin/users' => [
            'controller' => 'AdminController', 
            'action' => 'users'
        ],
        'admin/users/list' => [
            'controller' => 'AdminController', 
            'action' => 'getUsersList'
        ],
        'admin/users/toggle-status' => [
            'controller' => 'AdminController', 
            'action' => 'toggleUserStatus'
        ],
        'admin/users/change-role' => [
            'controller' => 'AdminController', 
            'action' => 'changeUserRole'
        ],
        'admin/users/delete' => [
            'controller' => 'AdminController', 
            'action' => 'deleteUser'
        ],
        'moderator/content' => [
            'controller' => 'ModeratorController', 
            'action' => 'content'
        ],
        'moderator/posts/list' => [
            'controller' => 'ModeratorController', 
            'action' => 'getPostsList'
        ],
        'moderator/comments/list' => [
            'controller' => 'ModeratorController', 
            'action' => 'getCommentsList'
        ],
        'moderator/posts/delete' => [
            'controller' => 'ModeratorController', 
            'action' => 'deletePost'
        ],
        'moderator/posts/hide' => [
            'controller' => 'ModeratorController', 
            'action' => 'hidePost'
        ],
        'moderator/comments/delete' => [
            'controller' => 'ModeratorController', 
            'action' => 'deleteComment'
        ],
        'profile' => [
            'controller' => 'ProfileController',
            'action' => 'showCurrent'  
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