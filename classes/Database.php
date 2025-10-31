<?php
/**
 * Secure Database Connection Class
 * Singleton pattern with prepared statements support
 */

class Database {
    private static $instances = [];
    private $connection;
    private $config;

    private function __construct($config) {
        $this->config = $config;
        $this->connect();
    }

    public static function getInstance($config = null) {
        if ($config === null) {
            $config = require __DIR__ . '/../config/database.php';
        }
        
        $key = md5(serialize($config));
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new self($config);
        }
        return self::$instances[$key];
    }

    private function connect() {
        try {
            $dsn = "mysql:host={$this->config['host']};dbname={$this->config['database']};charset={$this->config['charset']}";
            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $this->config['options']
            );
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    public function selectDatabase($database) {
        try {
            $this->connection->exec("USE `$database`");
        } catch (PDOException $e) {
            error_log("Database selection failed: " . $e->getMessage());
            throw new Exception("Database selection failed");
        }
    }

    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query failed: " . $e->getMessage());
            throw new Exception("Query execution failed");
        }
    }

    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    public function commit() {
        return $this->connection->commit();
    }

    public function rollback() {
        return $this->connection->rollback();
    }

    public function close() {
        $this->connection = null;
    }
}
