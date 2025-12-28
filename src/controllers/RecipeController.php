<?php

require_once 'AppController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/Recipe.php';

class RecipeController extends AppController {

    public function index(): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }

        $user = AuthMiddleware::getCurrentUser();
        $this->render('recipes.html', ['user' => $user]);
    }

    public function getRecipes(): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }

        $type = $_GET['type'] ?? 'recipes';

        if ($type === 'tips') {
            $recipes = $this->getMockTips();
        } else {
            $recipes = $this->getMockRecipes();
        }

        $this->json([
            'success' => true,
            'recipes' => $recipes
        ]);
    }

    private function getMockRecipes(): array {
        return [
            [
                'id' => 1,
                'title' => 'Gluten free fluffy Pancakes',
                'author_name' => 'GF_lady',
                'image_url' => '/public/images/recipes/pancakes.jpg',
                'likes' => 60,
                'comments' => 41
            ],
            [
                'id' => 2,
                'title' => 'Gluten free banana bread',
                'author_name' => 'GF CuppaTea',
                'image_url' => '/public/images/recipes/banana_bread.jpg',
                'likes' => 60,
                'comments' => 41
            ],
            [
                'id' => 3,
                'title' => 'Gluten free dumplings',
                'author_name' => 'AniaCooking',
                'image_url' => '/public/images/recipes/dumplings.jpg',
                'likes' => 60,
                'comments' => 41
            ],
            [
                'id' => 4,
                'title' => 'Gluten free Lasagna',
                'author_name' => 'Lexis_kitchen',
                'image_url' => '/public/images/recipes/lasagna.jpg',
                'likes' => 13,
                'comments' => 5
            ],
            [
                'id' => 5,
                'title' => 'Gluten free pizza dough',
                'author_name' => 'gfJules',
                'image_url' => '/public/images/recipes/pizza.jpg',
                'likes' => 60,
                'comments' => 41
            ],
            [
                'id' => 6,
                'title' => 'Gluten free Strawberry cake',
                'author_name' => 'Lola',
                'image_url' => '/public/images/recipes/cake.jpg',
                'likes' => 60,
                'comments' => 41
            ],
        ];
    }

private function getMockTips(): array {
    return [
        [
            'id' => 1,
            'title' => 'How to avoid cross-contamination in the kitchen',
            'author_name' => 'SafetyFirst',
            'image_url' => '',
            'likes' => 145,
            'comments' => 32
        ],
        [
            'id' => 2,
            'title' => 'Best gluten-free flour substitutes',
            'author_name' => 'BakingPro',
            'image_url' => '',
            'likes' => 203,
            'comments' => 67
        ],
        [
            'id' => 3,
            'title' => 'Dining out gluten-free: What to ask waiters',
            'author_name' => 'CeliacTraveler',
            'image_url' => '',
            'likes' => 98,
            'comments' => 41
        ],
        [
            'id' => 4,
            'title' => 'Reading food labels: Hidden gluten sources',
            'author_name' => 'NutriExpert',
            'image_url' => '',
            'likes' => 187,
            'comments' => 55
        ],
    ];
}
}