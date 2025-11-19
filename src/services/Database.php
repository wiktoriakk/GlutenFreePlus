<?php

class Database {
    private static ?Database $instance = null;
    private ?\PDO $connection = null;
    
    private string $host;
    private string $port;
    private string $database;
    private string $username;
    private string $password;
    
    private function __construct() {
        $config = require __DIR__ . '/../../config/database.php';
        
        $this->host = $config['host'];
        $this->port = $config['port'];
        $this->database = $config['database'];
        $this->username = $config['username'];
        $this->password = $config['password'];
    }
    
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function connect(): PDO {
        if ($this->connection !== null) {
            return $this->connection;
        }
        
        try {
            $dsn = sprintf(
                "pgsql:host=%s;port=%s;dbname=%s",
                $this->host,
                $this->port,
                $this->database
            );
            
            $this->connection = new PDO(
                $dsn,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
            
            return $this->connection;
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Could not connect to database");
        }
    }
    
    public function disconnect(): void {
        $this->connection = null;
    }
    
    private function __clone() {}
    
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}