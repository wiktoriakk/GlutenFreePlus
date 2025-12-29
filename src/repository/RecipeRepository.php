<?php

require_once __DIR__ . '/Repository.php';
require_once __DIR__ . '/../models/Recipe.php';

class RecipeRepository extends Repository {

    public function findById(int $id): ?Recipe {
        $query = "SELECT * FROM recipes WHERE id = :id AND is_published = true";
        $result = $this->fetch($query, ['id' => $id]);
        
        return $result ? Recipe::fromArray($result) : null;
    }

    public function findAll(int $limit = 50, int $offset = 0): array {
        $query = "SELECT * FROM recipes WHERE is_published = true ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $results = $this->fetchAll($query, ['limit' => $limit, 'offset' => $offset]);
        
        return array_map(fn($row) => Recipe::fromArray($row), $results);
    }

    public function findByAuthor(int $authorId, int $limit = 20): array {
        $query = "SELECT * FROM recipes WHERE author_id = :author_id AND is_published = true ORDER BY created_at DESC LIMIT :limit";
        $results = $this->fetchAll($query, ['author_id' => $authorId, 'limit' => $limit]);
        
        return array_map(fn($row) => Recipe::fromArray($row), $results);
    }

    public function search(string $searchTerm, int $limit = 20): array {
        $query = "SELECT * FROM recipes WHERE is_published = true AND (LOWER(title) LIKE LOWER(:search) OR LOWER(description) LIKE LOWER(:search)) ORDER BY created_at DESC LIMIT :limit";
        $results = $this->fetchAll($query, ['search' => '%' . $searchTerm . '%', 'limit' => $limit]);
        
        return array_map(fn($row) => Recipe::fromArray($row), $results);
    }

    public function create(Recipe $recipe): ?Recipe {
        $query = "
            INSERT INTO recipes (title, description, ingredients, instructions, prep_time, cook_time, servings, difficulty, image_url, author_id, is_published) 
            VALUES (:title, :description, :ingredients, :instructions, :prep_time, :cook_time, :servings, :difficulty, :image_url, :author_id, :is_published)
            RETURNING id, created_at, updated_at
        ";

        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute([
                'title' => $recipe->getTitle(),
                'description' => $recipe->getDescription(),
                'ingredients' => $recipe->getIngredients(),
                'instructions' => $recipe->getInstructions(),
                'prep_time' => $recipe->getPrepTime(),
                'cook_time' => $recipe->getCookTime(),
                'servings' => $recipe->getServings(),
                'difficulty' => $recipe->getDifficulty(),
                'image_url' => $recipe->getImageUrl(),
                'author_id' => $recipe->getAuthorId(),
                'is_published' => $recipe->isPublished()
            ]);

            $result = $stmt->fetch();
            if ($result) {
                $recipe->setId((int)$result['id']);
                $recipe->setCreatedAt(new \DateTime($result['created_at']));
                $recipe->setUpdatedAt(new \DateTime($result['updated_at']));
                return $recipe;
            }
        } catch (PDOException $e) {
            error_log("Recipe creation failed: " . $e->getMessage());
        }

        return null;
    }

    public function update(Recipe $recipe): bool {
        $query = "
            UPDATE recipes 
            SET title = :title, 
                description = :description, 
                ingredients = :ingredients,
                instructions = :instructions,
                prep_time = :prep_time,
                cook_time = :cook_time,
                servings = :servings,
                difficulty = :difficulty,
                image_url = :image_url,
                is_published = :is_published
            WHERE id = :id
        ";

        return $this->execute($query, [
            'id' => $recipe->getId(),
            'title' => $recipe->getTitle(),
            'description' => $recipe->getDescription(),
            'ingredients' => $recipe->getIngredients(),
            'instructions' => $recipe->getInstructions(),
            'prep_time' => $recipe->getPrepTime(),
            'cook_time' => $recipe->getCookTime(),
            'servings' => $recipe->getServings(),
            'difficulty' => $recipe->getDifficulty(),
            'image_url' => $recipe->getImageUrl(),
            'is_published' => $recipe->isPublished()
        ]);
    }

    public function delete(int $id): bool {
        // Soft delete
        $query = "UPDATE recipes SET is_published = false WHERE id = :id";
        return $this->execute($query, ['id' => $id]);
    }

    public function hardDelete(int $id): bool {
        $query = "DELETE FROM recipes WHERE id = :id";
        return $this->execute($query, ['id' => $id]);
    }

    public function incrementLikes(int $recipeId): bool {
        $query = "UPDATE recipes SET likes_count = likes_count + 1 WHERE id = :id";
        return $this->execute($query, ['id' => $recipeId]);
    }

    public function decrementLikes(int $recipeId): bool {
        $query = "UPDATE recipes SET likes_count = GREATEST(likes_count - 1, 0) WHERE id = :id";
        return $this->execute($query, ['id' => $recipeId]);
    }

    public function getPopular(int $limit = 10): array {
        $query = "SELECT * FROM recipes WHERE is_published = true ORDER BY likes_count DESC, created_at DESC LIMIT :limit";
        $results = $this->fetchAll($query, ['limit' => $limit]);
        
        return array_map(fn($row) => Recipe::fromArray($row), $results);
    }

    public function countByAuthor(int $authorId): int {
        $query = "SELECT COUNT(*) as count FROM recipes WHERE author_id = :author_id AND is_published = true";
        $result = $this->fetch($query, ['author_id' => $authorId]);
        return $result ? (int)$result['count'] : 0;
    }

    public function findWithAuthor(int $id): ?array {
        $query = "
            SELECT r.*, u.name as author_name, u.avatar as author_avatar, u.user_type as author_type
            FROM recipes r
            INNER JOIN users u ON r.author_id = u.id
            WHERE r.id = :id AND r.is_published = true
        ";
        
        return $this->fetch($query, ['id' => $id]);
    }

    public function getAllWithAuthor(int $limit = 50): array {
        $query = "
            SELECT r.*, u.name as author_name, u.avatar as author_avatar
            FROM recipes r
            INNER JOIN users u ON r.author_id = u.id
            WHERE r.is_published = true
            ORDER BY r.created_at DESC
            LIMIT :limit
        ";
        
        return $this->fetchAll($query, ['limit' => $limit]);
    }

    // Helper methods for raw queries
    public function fetch(string $query, array $params = []): ?array {
        $stmt = $this->database->connect()->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function fetchAll(string $query, array $params = []): array {
        $stmt = $this->database->connect()->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function execute(string $query, array $params = []): bool {
        try {
            $stmt = $this->database->connect()->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Query execution failed: " . $e->getMessage());
            return false;
        }
    }
}