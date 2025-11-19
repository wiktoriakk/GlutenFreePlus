<?php

require_once __DIR__ . '/../services/Database.php';

abstract class Repository {
    protected Database $database;
    protected PDO $connection;
    
    public function __construct() {
        $this->database = Database::getInstance();
        $this->connection = $this->database->connect();
    }
    
    protected function execute(string $query, array $params = []): bool {
        try {
            $stmt = $this->connection->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Query execution failed: " . $e->getMessage());
            return false;
        }
    }
    
    protected function fetch(string $query, array $params = []): ?array {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Query fetch failed: " . $e->getMessage());
            return null;
        }
    }
    
    protected function fetchAll(string $query, array $params = []): array {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Query fetchAll failed: " . $e->getMessage());
            return [];
        }
    }
}