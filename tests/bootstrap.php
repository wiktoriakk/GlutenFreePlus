<?php

// Bootstrap file for PHPUnit tests

// Define test environment
define('TEST_MODE', true);

// Autoload dependencies
require_once __DIR__ . '/../vendor/autoload.php';

// Load application files
require_once __DIR__ . '/../src/repository/Repository.php';
require_once __DIR__ . '/../src/repository/UserRepository.php';
require_once __DIR__ . '/../src/repository/RecipeRepository.php';
require_once __DIR__ . '/../src/models/User.php';
require_once __DIR__ . '/../src/models/Recipe.php';

// Set test database configuration (optional - use separate test DB)
// For now, we'll mock database connections in tests