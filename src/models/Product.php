<?php

class Product {
    private ?int $id = null;
    private string $name;
    private string $brand;
    private string $barcode;
    private bool $isGlutenFree;
    private ?string $safetyStatus = null; // 'safe', 'uncertain', 'contains_gluten'
    private ?string $ingredients = null;
    private ?string $imageUrl = null;
    private ?string $category = null;
    
    public function __construct(
        string $name,
        string $brand,
        string $barcode,
        bool $isGlutenFree
    ) {
        $this->name = $name;
        $this->brand = $brand;
        $this->barcode = $barcode;
        $this->isGlutenFree = $isGlutenFree;
        $this->safetyStatus = $isGlutenFree ? 'safe' : 'contains_gluten';
    }
    
    // Getters
    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getBrand(): string { return $this->brand; }
    public function getBarcode(): string { return $this->barcode; }
    public function isGlutenFree(): bool { return $this->isGlutenFree; }
    public function getSafetyStatus(): ?string { return $this->safetyStatus; }
    public function getIngredients(): ?string { return $this->ingredients; }
    public function getImageUrl(): ?string { return $this->imageUrl; }
    public function getCategory(): ?string { return $this->category; }
    
    // Setters
    public function setId(int $id): void { $this->id = $id; }
    public function setName(string $name): void { $this->name = $name; }
    public function setBrand(string $brand): void { $this->brand = $brand; }
    public function setSafetyStatus(string $status): void { $this->safetyStatus = $status; }
    public function setIngredients(?string $ingredients): void { $this->ingredients = $ingredients; }
    public function setImageUrl(?string $imageUrl): void { $this->imageUrl = $imageUrl; }
    public function setCategory(?string $category): void { $this->category = $category; }
    
    public function toArray(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'brand' => $this->brand,
            'barcode' => $this->barcode,
            'is_gluten_free' => $this->isGlutenFree,
            'safety_status' => $this->safetyStatus,
            'ingredients' => $this->ingredients,
            'image_url' => $this->imageUrl,
            'category' => $this->category,
        ];
    }
}