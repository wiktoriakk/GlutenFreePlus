<?php

class Database {
    private static $instance = null;
    private $connection;
    
    private $host = 'db';
    private $database = 'glutenfree_db';
    private $user = 'glutenfree_user';
    private $password = 'glutenfree_pass';
    
    private function __construct() {
        try {
            $this->connection = new PDO(
                "pgsql:host={$this->host};dbname={$this->database}",
                $this->user,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Wykonuje zapytanie SELECT i zwraca wszystkie wyniki
     */
    public function select($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Database SELECT error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Wykonuje zapytanie SELECT i zwraca jeden wiersz
     */
    public function selectOne($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Database SELECT ONE error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Wykonuje zapytanie INSERT/UPDATE/DELETE
     */
    public function execute($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Database EXECUTE error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Wykonuje INSERT i zwraca ID nowego rekordu
     */
    public function insert($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $this->connection->lastInsertId();
        } catch (PDOException $e) {
            error_log("Database INSERT error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Rozpoczyna transakcję
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Zatwierdza transakcję
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Cofa transakcję
     */
    public function rollback() {
        return $this->connection->rollBack();
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}