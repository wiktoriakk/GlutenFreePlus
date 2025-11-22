<?php

class User {
    private ?int $id = null;
    private string $email;
    private string $password;
    private string $name;
    private string $role;
    private ?string $avatar = null;
    private ?string $bio = null;
    private ?string $userType = null; // Celiac, Nutritionist, Food Blogger, Chef
    private \DateTime $createdAt;
    private ?\DateTime $lastLogin = null;
    private bool $isActive = true;

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

    // ==================== GETTERS ====================
    
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

    public function getAvatar(): ?string {
        return $this->avatar;
    }

    public function getBio(): ?string {
        return $this->bio;
    }

    public function getUserType(): ?string {
        return $this->userType;
    }

    public function getCreatedAt(): \DateTime {
        return $this->createdAt;
    }

    public function getLastLogin(): ?\DateTime {
        return $this->lastLogin;
    }

    public function isActive(): bool {
        return $this->isActive;
    }

    // ==================== SETTERS ====================
    
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

    public function setAvatar(?string $avatar): void {
        $this->avatar = $avatar;
    }

    public function setBio(?string $bio): void {
        $this->bio = $bio;
    }

    public function setUserType(?string $userType): void {
        $this->userType = $userType;
    }

    public function setCreatedAt(\DateTime $createdAt): void {
        $this->createdAt = $createdAt;
    }

    public function setLastLogin(?\DateTime $lastLogin): void {
        $this->lastLogin = $lastLogin;
    }

    public function setIsActive(bool $isActive): void {
        $this->isActive = $isActive;
    }

    // ==================== HELPERS ====================

    public function verifyPassword(string $password): bool {
        return password_verify($password, $this->password);
    }

    public function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'role' => $this->role,
            'avatar' => $this->avatar,
            'bio' => $this->bio,
            'user_type' => $this->userType,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'is_active' => $this->isActive,
        ];
    }

    public static function fromArray(array $data): User {
        $user = new self(
            $data['email'],
            $data['password'],
            $data['name'] ?? '',
            $data['role'] ?? 'user'
        );

        if (isset($data['id'])) {
            $user->setId((int)$data['id']);
        }
        if (isset($data['avatar'])) {
            $user->setAvatar($data['avatar']);
        }
        if (isset($data['bio'])) {
            $user->setBio($data['bio']);
        }
        if (isset($data['user_type'])) {
            $user->setUserType($data['user_type']);
        }
        if (isset($data['created_at'])) {
            $user->setCreatedAt(new \DateTime($data['created_at']));
        }
        if (isset($data['last_login']) && $data['last_login']) {
            $user->setLastLogin(new \DateTime($data['last_login']));
        }
        if (isset($data['is_active'])) {
            $user->setIsActive((bool)$data['is_active']);
        }

        return $user;
    }
}