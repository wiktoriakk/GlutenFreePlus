<?php

require_once 'AppController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../repository/RecipeRepository.php';
require_once __DIR__ . '/../models/Recipe.php';

class RecipeController extends AppController {

    private RecipeRepository $recipeRepository;

    public function __construct() {
        $this->recipeRepository = new RecipeRepository();
    }

    /**
     * Show recipes list page
     */
    public function index(): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }

        $user = AuthMiddleware::getCurrentUser();
        $this->render('recipes.html', ['user' => $user]);
    }

    /**
     * Get recipes (AJAX)
     */
    public function getRecipes(): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }

        $type = $_GET['type'] ?? 'recipes';

        try {
            if ($type === 'tips') {
                // Get tips
                $recipes = $this->recipeRepository->getAllWithAuthor(50);
                $recipes = array_filter($recipes, function($recipe) {
                    return isset($recipe['recipe_type']) && $recipe['recipe_type'] === 'tip';
                });
                $recipes = array_values($recipes);
            } elseif ($type === 'favourites') {
                // Get user's favorites
                $currentUser = AuthMiddleware::getCurrentUser();
                $userId = $currentUser['id'];
                
                $recipes = $this->recipeRepository->fetchAll(
                    "SELECT r.*, u.name as author_name, u.avatar as author_avatar
                    FROM recipes r 
                    INNER JOIN users u ON r.author_id = u.id 
                    INNER JOIN recipe_favorites rf ON r.id = rf.recipe_id 
                    WHERE rf.user_id = :user_id AND r.is_published = true 
                    ORDER BY rf.created_at DESC",
                    ['user_id' => $userId]
                );
            } else {
                // Get regular recipes
                $recipes = $this->recipeRepository->getAllWithAuthor(50);
                $recipes = array_filter($recipes, function($recipe) {
                    return !isset($recipe['recipe_type']) || $recipe['recipe_type'] === 'recipe';
                });
                $recipes = array_values($recipes);
            }
        
            $this->json([
                'success' => true,
                'recipes' => $recipes
            ]);
        } catch (Exception $e) {
            error_log('Get recipes error: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Failed to load recipes'], 500);
        }
    }

    /**
     * Show recipe details
     */
    public function show(): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }

        $recipeId = (int)($_GET['id'] ?? 0);
        
        if (!$recipeId) {
            http_response_code(404);
            $this->render('404.html');
            return;
        }

        try {
            $recipeData = $this->recipeRepository->findWithAuthor($recipeId);
            
            if (!$recipeData) {
                http_response_code(404);
                $this->render('404.html');
                return;
            }

            $currentUser = AuthMiddleware::getCurrentUser();
            $this->render('recipe-detail.html', [
                'recipe' => $recipeData,
                'currentUser' => $currentUser
            ]);
        } catch (Exception $e) {
            error_log('Show recipe error: ' . $e->getMessage());
            http_response_code(500);
            echo "Error loading recipe";
        }
    }

    /**
     * Show create recipe form
     */
    public function create(): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }

        $user = AuthMiddleware::getCurrentUser();
        $this->render('recipe-form.html', ['user' => $user]);
    }

    /**
     * Store new recipe
     */
    public function store(): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }

        $currentUser = AuthMiddleware::getCurrentUser();
        
        // Validate input
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $ingredients = trim($_POST['ingredients'] ?? '');
        $instructions = trim($_POST['instructions'] ?? '');
        $prepTime = (int)($_POST['prep_time'] ?? 0);
        $cookTime = (int)($_POST['cook_time'] ?? 0);
        $servings = (int)($_POST['servings'] ?? 1);
        $difficulty = $_POST['difficulty'] ?? null;

        $errors = [];

        if (empty($title)) {
            $errors['title'] = 'Title is required';
        } elseif (strlen($title) > 255) {
            $errors['title'] = 'Title too long';
        }

        if (empty($ingredients)) {
            $errors['ingredients'] = 'Ingredients are required';
        }

        if (empty($instructions)) {
            $errors['instructions'] = 'Instructions are required';
        }

        if ($servings < 1) {
            $errors['servings'] = 'Servings must be at least 1';
        }

        if ($difficulty && !in_array($difficulty, ['easy', 'medium', 'hard'])) {
            $errors['difficulty'] = 'Invalid difficulty level';
        }

        if (!empty($errors)) {
            $this->json(['success' => false, 'errors' => $errors], 400);
            return;
        }

        try {
            $recipe = new Recipe(
                $title,
                $ingredients,
                $instructions,
                $currentUser['id'],
                $servings
            );

            if (!empty($description)) {
                $recipe->setDescription($description);
            }

            if ($prepTime > 0) {
                $recipe->setPrepTime($prepTime);
            }

            if ($cookTime > 0) {
                $recipe->setCookTime($cookTime);
            }

            if ($difficulty) {
                $recipe->setDifficulty($difficulty);
            }

            $createdRecipe = $this->recipeRepository->create($recipe);

            if ($createdRecipe) {
                $this->json([
                    'success' => true,
                    'message' => 'Recipe created successfully',
                    'recipe_id' => $createdRecipe->getId()
                ]);
            } else {
                $this->json(['success' => false, 'error' => 'Failed to create recipe'], 500);
            }
        } catch (Exception $e) {
            error_log('Create recipe error: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Server error'], 500);
        }
    }

    /**
     * Toggle favorite (like)
     */
    public function toggleFavorite(): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }

        $currentUser = AuthMiddleware::getCurrentUser();
        $userId = $currentUser['id'];
        $recipeId = (int)($_POST['recipe_id'] ?? 0);
        
        if (!$recipeId) {
            $this->json(['success' => false, 'error' => 'Invalid recipe ID'], 400);
            return;
        }

        try {
            // Check if recipe exists
            $recipe = $this->recipeRepository->findById($recipeId);
            
            if (!$recipe) {
                $this->json(['success' => false, 'error' => 'Recipe not found'], 404);
                return;
            }

            // Check if already favorited
            $existing = $this->recipeRepository->fetch(
                "SELECT id FROM recipe_favorites WHERE user_id = :user_id AND recipe_id = :recipe_id",
                ['user_id' => $userId, 'recipe_id' => $recipeId]
            );

            if ($existing) {
                // Remove from favorites
                $this->recipeRepository->execute(
                    "DELETE FROM recipe_favorites WHERE user_id = :user_id AND recipe_id = :recipe_id",
                    ['user_id' => $userId, 'recipe_id' => $recipeId]
                );
                $this->recipeRepository->decrementLikes($recipeId);
                
                $this->json([
                    'success' => true,
                    'message' => 'Removed from favorites',
                    'liked' => false
                ]);
            } else {
                // Add to favorites
                $this->recipeRepository->execute(
                    "INSERT INTO recipe_favorites (user_id, recipe_id) VALUES (:user_id, :recipe_id)",
                    ['user_id' => $userId, 'recipe_id' => $recipeId]
                );
                $this->recipeRepository->incrementLikes($recipeId);
                
                $this->json([
                    'success' => true,
                    'message' => 'Added to favorites',
                    'liked' => true
                ]);
            }
        } catch (Exception $e) {
            error_log('Toggle favorite error: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Operation failed'], 500);
        }
    }

    /**
     * Delete recipe
     */
    public function delete(): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }

        $currentUser = AuthMiddleware::getCurrentUser();
        $recipeId = (int)($_POST['recipe_id'] ?? 0);
        
        if (!$recipeId) {
            $this->json(['success' => false, 'error' => 'Invalid recipe ID'], 400);
            return;
        }

        try {
            $recipe = $this->recipeRepository->findById($recipeId);
            
            if (!$recipe) {
                $this->json(['success' => false, 'error' => 'Recipe not found'], 404);
                return;
            }

            // Check if user owns the recipe or is admin
            if ($recipe->getAuthorId() !== $currentUser['id'] && $currentUser['role'] !== 'admin') {
                $this->json(['success' => false, 'error' => 'Unauthorized'], 403);
                return;
            }

            if ($this->recipeRepository->delete($recipeId)) {
                $this->json([
                    'success' => true,
                    'message' => 'Recipe deleted successfully'
                ]);
            } else {
                $this->json(['success' => false, 'error' => 'Failed to delete recipe'], 500);
            }
        } catch (Exception $e) {
            error_log('Delete recipe error: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Operation failed'], 500);
        }
    }
}