<?php

require_once 'AppController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/Product.php';

class ScannerController extends AppController {

    public function index(): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }

        $user = AuthMiddleware::getCurrentUser();
        $this->render('scanner.html', ['user' => $user]);
    }

    public function search(): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }

        $query = $_GET['q'] ?? '';

        if (empty($query)) {
            $this->json(['success' => false, 'error' => 'Search query required'], 400);
            return;
        }

        // Mock data - w produkcji byłoby połączenie z API lub bazą
        $products = $this->getMockProducts();
        
        $results = array_filter($products, function($product) use ($query) {
            return stripos($product['name'], $query) !== false || 
                   stripos($product['brand'], $query) !== false;
        });

        $this->json([
            'success' => true,
            'product' => !empty($results) ? array_values($results)[0] : null,
            'alternatives' => $this->getAlternatives()
        ]);
    }

    private function getMockProducts(): array {
        return [
            [
                'id' => 1,
                'name' => 'Organic Wholewheat Bread',
                'brand' => "Nature's Bakery",
                'barcode' => '5901234123457',
                'is_gluten_free' => true,
                'safety_status' => 'safe',
                'ingredients' => 'Rice flour, water, yeast, salt, olive oil',
                'image_url' => '/public/images/products/bread.jpg',
                'category' => 'Bakery',
                'certified' => true
            ],
            [
                'id' => 2,
                'name' => 'Gluten Free Pasta',
                'brand' => 'Barilla',
                'barcode' => '5901234123458',
                'is_gluten_free' => true,
                'safety_status' => 'safe',
                'ingredients' => 'Corn flour, rice flour',
                'image_url' => '/public/images/products/pasta.jpg',
                'category' => 'Pasta',
                'certified' => true
            ],
        ];
    }

    private function getAlternatives(): array {
        return [
            [
                'name' => 'GF Yeasted Puff Pastry',
                'brand' => 'Simple Mills',
                'image_url' => '/public/images/products/pastry.jpg'
            ],
            [
                'name' => 'Multigrain GF Bread',
                'brand' => 'Canyon Bakehouse',
                'image_url' => '/public/images/products/multigrain.jpg'
            ],
            [
                'name' => 'Pistachio tart',
                'brand' => 'Vanilla and sugar',
                'image_url' => '/public/images/products/tart.jpg'
            ],
            [
                'name' => 'Bread of three flours',
                'brand' => 'Gluten Free Bakery',
                'image_url' => '/public/images/products/bread3.jpg'
            ],
            [
                'name' => 'Chocolate cookies',
                'brand' => 'GF shop',
                'image_url' => '/public/images/products/cookies.jpg'
            ],
        ];
    }
}