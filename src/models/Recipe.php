<?php

class Recipe {
    
    private ?int $id = null;
    private string $title;
    private ?string $description = null;
    private string $ingredients;
    private string $instructions;
    private ?int $prepTime = null;
    private ?int $cookTime = null;
    private int $servings = 1;
    private ?string $difficulty = null;
    private ?string $imageUrl = null;
    private int $authorId;
    private int $likesCount = 0;
    private int $commentsCount = 0;
    private ?\DateTime $createdAt = null;
    private ?\DateTime $updatedAt = null;
    private bool $isPublished = true;

    public function __construct(
        string $title,
        string $ingredients,
        string $instructions,
        int $authorId,
        int $servings = 1
    ) {
        $this->title = $title;
        $this->ingredients = $ingredients;
        $this->instructions = $instructions;
        $this->authorId = $authorId;
        $this->servings = $servings;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): ?string { return $this->description; }
    public function getIngredients(): string { return $this->ingredients; }
    public function getInstructions(): string { return $this->instructions; }
    public function getPrepTime(): ?int { return $this->prepTime; }
    public function getCookTime(): ?int { return $this->cookTime; }
    public function getServings(): int { return $this->servings; }
    public function getDifficulty(): ?string { return $this->difficulty; }
    public function getImageUrl(): ?string { return $this->imageUrl; }
    public function getAuthorId(): int { return $this->authorId; }
    public function getLikesCount(): int { return $this->likesCount; }
    public function getCommentsCount(): int { return $this->commentsCount; }
    public function getCreatedAt(): ?\DateTime { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTime { return $this->updatedAt; }
    public function isPublished(): bool { return $this->isPublished; }

    // Setters
    public function setId(int $id): void { $this->id = $id; }
    public function setTitle(string $title): void { $this->title = $title; }
    public function setDescription(?string $description): void { $this->description = $description; }
    public function setIngredients(string $ingredients): void { $this->ingredients = $ingredients; }
    public function setInstructions(string $instructions): void { $this->instructions = $instructions; }
    public function setPrepTime(?int $prepTime): void { $this->prepTime = $prepTime; }
    public function setCookTime(?int $cookTime): void { $this->cookTime = $cookTime; }
    public function setServings(int $servings): void { $this->servings = $servings; }
    public function setDifficulty(?string $difficulty): void { $this->difficulty = $difficulty; }
    public function setImageUrl(?string $imageUrl): void { $this->imageUrl = $imageUrl; }
    public function setLikesCount(int $count): void { $this->likesCount = $count; }
    public function setCommentsCount(int $count): void { $this->commentsCount = $count; }
    public function setCreatedAt(\DateTime $createdAt): void { $this->createdAt = $createdAt; }
    public function setUpdatedAt(\DateTime $updatedAt): void { $this->updatedAt = $updatedAt; }
    public function setIsPublished(bool $isPublished): void { $this->isPublished = $isPublished; }

    // Helper methods
    public function getTotalTime(): int {
        return ($this->prepTime ?? 0) + ($this->cookTime ?? 0);
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'ingredients' => $this->ingredients,
            'instructions' => $this->instructions,
            'prep_time' => $this->prepTime,
            'cook_time' => $this->cookTime,
            'servings' => $this->servings,
            'difficulty' => $this->difficulty,
            'image_url' => $this->imageUrl,
            'author_id' => $this->authorId,
            'likes_count' => $this->likesCount,
            'comments_count' => $this->commentsCount,
            'created_at' => $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updatedAt ? $this->updatedAt->format('Y-m-d H:i:s') : null,
            'is_published' => $this->isPublished
        ];
    }

    public static function fromArray(array $data): Recipe {
        $recipe = new Recipe(
            $data['title'],
            $data['ingredients'],
            $data['instructions'],
            (int)$data['author_id'],
            (int)($data['servings'] ?? 1)
        );

        if (isset($data['id'])) $recipe->setId((int)$data['id']);
        if (isset($data['description'])) $recipe->setDescription($data['description']);
        if (isset($data['prep_time'])) $recipe->setPrepTime((int)$data['prep_time']);
        if (isset($data['cook_time'])) $recipe->setCookTime((int)$data['cook_time']);
        if (isset($data['difficulty'])) $recipe->setDifficulty($data['difficulty']);
        if (isset($data['image_url'])) $recipe->setImageUrl($data['image_url']);
        if (isset($data['likes_count'])) $recipe->setLikesCount((int)$data['likes_count']);
        if (isset($data['comments_count'])) $recipe->setCommentsCount((int)$data['comments_count']);
        if (isset($data['created_at'])) $recipe->setCreatedAt(new \DateTime($data['created_at']));
        if (isset($data['updated_at'])) $recipe->setUpdatedAt(new \DateTime($data['updated_at']));
        if (isset($data['is_published'])) $recipe->setIsPublished((bool)$data['is_published']);

        return $recipe;
    }
}