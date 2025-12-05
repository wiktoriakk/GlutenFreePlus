<?php

require_once 'AppController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class DiscoverController extends AppController {

    public function index(): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }

        $user = AuthMiddleware::getCurrentUser();
        $this->render('discover.html', ['user' => $user]);
    }

    public function getPlaces(): void {
        if (!AuthMiddleware::requireAuth()) {
            return;
        }

        $type = $_GET['type'] ?? 'all';
        $certified = isset($_GET['certified']) ? (bool)$_GET['certified'] : false;

        $places = $this->getMockPlaces();

        // Filter by type
        if ($type !== 'all') {
            $places = array_filter($places, function($place) use ($type) {
                return $place['type'] === $type;
            });
        }

        // Filter by certified
        if ($certified) {
            $places = array_filter($places, function($place) {
                return $place['certified'] ?? false;
            });
        }

        $this->json([
            'success' => true,
            'places' => array_values($places)
        ]);
    }

    private function getMockPlaces(): array {
        return [
            [
                'id' => 1,
                'name' => 'Ristorante My Heart 2',
                'type' => 'restaurant',
                'cuisine' => 'Italian Restaurant',
                'address' => 'Via Roma 123, Milan',
                'rating' => 0,
                'reviews' => 0,
                'distance' => '1.5 km',
                'certified' => true,
                'image' => '/public/images/places/restaurant1.jpg',
                'lat' => 45.4642,
                'lng' => 9.1900
            ],
            [
                'id' => 2,
                'name' => 'GluFree Bakery',
                'type' => 'bakery',
                'cuisine' => 'Bakery',
                'address' => 'Via Milano 45, Milan',
                'rating' => 0,
                'reviews' => 0,
                'distance' => '2 km',
                'certified' => true,
                'image' => '/public/images/places/bakery1.jpg',
                'lat' => 45.4654,
                'lng' => 9.1859
            ],
            [
                'id' => 3,
                'name' => 'Mama Eat - Gluten-free Restaurant & Pizzeria',
                'type' => 'restaurant',
                'cuisine' => 'Italian Restaurant',
                'address' => 'Corso Buenos Aires 12, Milan',
                'rating' => 0,
                'reviews' => 0,
                'distance' => '1.5 km',
                'certified' => false,
                'image' => '/public/images/places/restaurant2.jpg',
                'lat' => 45.4786,
                'lng' => 9.2072
            ],
            [
                'id' => 4,
                'name' => 'Be Bop Ristorante Senza Glutine Milano',
                'type' => 'restaurant',
                'cuisine' => 'Italian Restaurant',
                'address' => 'Via Tortona 28, Milan',
                'rating' => 0,
                'reviews' => 0,
                'distance' => '3 km',
                'certified' => false,
                'image' => '/public/images/places/restaurant3.jpg',
                'lat' => 45.4519,
                'lng' => 9.1628
            ],
        ];
    }
}