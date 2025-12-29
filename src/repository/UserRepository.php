<?php

require_once __DIR__ . '/Repository.php';
require_once __DIR__ . '/../models/User.php';

class UserRepository extends Repository {

    public function findById(int $id): ?User {
        $query = "SELECT * FROM users WHERE id = :id AND is_active = true";
        $result = $this->fetch($query, ['id' => $id]);
        
        return $result ? User::fromArray($result) : null;
    }

    public function findByEmail(string $email): ?User {
        $query = "SELECT * FROM users WHERE email = :email AND is_active = true";
        $result = $this->fetch($query, ['email' => $email]);
        
        return $result ? User::fromArray($result) : null;
    }

    public function findAll(int $limit = 50, int $offset = 0): array {
        $query = "SELECT * FROM users WHERE is_active = true ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $results = $this->fetchAll($query, ['limit' => $limit, 'offset' => $offset]);
        
        return array_map(fn($row) => User::fromArray($row), $results);
    }

    public function findByUserType(string $userType, int $limit = 50): array {
        $query = "SELECT * FROM users WHERE user_type = :user_type AND is_active = true ORDER BY name LIMIT :limit";
        $results = $this->fetchAll($query, ['user_type' => $userType, 'limit' => $limit]);
        
        return array_map(fn($row) => User::fromArray($row), $results);
    }

    public function searchByName(string $searchTerm, int $limit = 20): array {
        $query = "SELECT * FROM users WHERE is_active = true AND LOWER(name) LIKE LOWER(:search) ORDER BY name LIMIT :limit";
        $results = $this->fetchAll($query, ['search' => '%' . $searchTerm . '%', 'limit' => $limit]);
        
        return array_map(fn($row) => User::fromArray($row), $results);
    }

    public function create(User $user): ?User {
        $query = "
            INSERT INTO users (email, password, name, role, avatar, user_type) 
            VALUES (:email, :password, :name, :role, :avatar, :user_type)
            RETURNING id, created_at
        ";

        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute([
                'email' => $user->getEmail(),
                'password' => $user->getPassword(),
                'name' => $user->getName(),
                'role' => $user->getRole(),
                'avatar' => $user->getAvatar(),
                'user_type' => $user->getUserType(),
            ]);

            $result = $stmt->fetch();
            if ($result) {
                $user->setId((int)$result['id']);
                $user->setCreatedAt(new \DateTime($result['created_at']));
                return $user;
            }
        } catch (PDOException $e) {
            error_log("User creation failed: " . $e->getMessage());
        }

        return null;
    }

    public function update(User $user): bool {
        $query = "
            UPDATE users 
            SET email = :email, 
                name = :name, 
                avatar = :avatar, 
                user_type = :user_type,
                role = :role
            WHERE id = :id
        ";

        return $this->execute($query, [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'avatar' => $user->getAvatar(),
            'user_type' => $user->getUserType(),
            'role' => $user->getRole(),
        ]);
    }

    public function updatePassword(int $userId, string $hashedPassword): bool {
        $query = "UPDATE users SET password = :password WHERE id = :id";
        return $this->execute($query, ['id' => $userId, 'password' => $hashedPassword]);
    }

    public function updateLastLogin(int $userId): void {
        $stmt = $this->database->connect()->prepare(
            'UPDATE users SET last_login = NOW() WHERE id = :id'
        );
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    public function delete(int $id): bool {
        // Soft delete
        $query = "UPDATE users SET is_active = false WHERE id = :id";
        return $this->execute($query, ['id' => $id]);
    }

    public function hardDelete(int $id): bool {
        $query = "DELETE FROM users WHERE id = :id";
        return $this->execute($query, ['id' => $id]);
    }

    public function emailExists(string $email): bool {
        $query = "SELECT COUNT(*) as count FROM users WHERE email = :email";
        $result = $this->fetch($query, ['email' => $email]);
        return $result && $result['count'] > 0;
    }

    public function countAll(): int {
        $query = "SELECT COUNT(*) as count FROM users WHERE is_active = true";
        $result = $this->fetch($query, []);
        return $result ? (int)$result['count'] : 0;
    }
}