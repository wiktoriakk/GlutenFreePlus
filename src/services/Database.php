<?php

class Database {
    private static ?Database $instance = null;
    private $connection;
    
    private function __construct() {
        // Konfiguracja zostanie dodana w następnym commit
    }
    
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function connect(): ?\PDO {
        // Implementacja w następnym commit
        return null;
    }
    
    private function __clone() {}
    
    public function __wakeup() {
        throw new \Exception("Cannot unserialize singleton");
    }
}