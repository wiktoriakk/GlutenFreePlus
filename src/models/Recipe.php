<?php

class Recipe {
    private ?int $id = null;
    private string $title;
    private int $authorId;
    private ?string $authorName = null;
    private ?string $description = null;
    private ?string $imageUrl = null;
    private int $likes = 0;
    private int $comments = 0;
    private \DateTime $createdAt;
    
    public function __construct(string $title, int $authorId) {
        $this->title = $title;
        $this->authorId = $authorId;
        $this->createdAt = new \DateTime();
    }
    
    // Getters
    public function getId(): ?int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getAuthorId(): int { return $this->authorId; }
    public function getAuthorName(): ?string { return $this->authorName; }
    public function getDescription(): ?string { return $this->description; }
    public function getImageUrl(): ?string { return $this->imageUrl; }
    public function getLikes(): int { return $this->likes; }
    public function getComments(): int { return $this->comments; }
    public function getCreatedAt(): \DateTime { return $this->createdAt; }
    
    // Setters
    public function setId(int $id): void { $this->id = $id; }
    public function setAuthorName(string $name): void { $this->authorName = $name; }
    public function setDescription(?string $desc): void { $this->description = $desc; }
    public function setImageUrl(?string $url): void { $this->imageUrl = $url; }
    public function setLikes(int $likes): void { $this->likes = $likes; }
    public function setComments(int $count): void { $this->comments = $count; }
    
    public function toArray(): array {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author_id' => $this->authorId,
            'author_name' => $this->authorName,
            'description' => $this->description,
            'image_url' => $this->imageUrl,
            'likes' => $this->likes,
            'comments' => $this->comments,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}