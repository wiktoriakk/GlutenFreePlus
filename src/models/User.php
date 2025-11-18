<?php

class User {
    private ?int $id = null;
    private string $email;
    private string $password;
    private string $name;
    private string $role;
    private \DateTime $createdAt;
    
    public function __construct(
        string $email,
        string $password,
        string $name = '',
        string $role = 'user'
    ) {
        $this->email = $email;
        $this->password = $password;
        $this->name = $name;
        $this->role = $role;
        $this->createdAt = new \DateTime();
    }
    
    // Getters
    public function getId(): ?int {
        return $this->id;
    }
    
    public function getEmail(): string {
        return $this->email;
    }
    
    public function getPassword(): string {
        return $this->password;
    }
    
    public function getName(): string {
        return $this->name;
    }
    
    public function getRole(): string {
        return $this->role;
    }
    
    // Setters
    public function setId(int $id): void {
        $this->id = $id;
    }
    
    public function setEmail(string $email): void {
        $this->email = $email;
    }
    
    public function setPassword(string $password): void {
        $this->password = $password;
    }
    
    public function setName(string $name): void {
        $this->name = $name;
    }
    
    public function setRole(string $role): void {
        $this->role = $role;
    }
}