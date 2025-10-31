<?php
/**
 * Secure Database Connection
 * Replaces the old database connection files with secure implementation
 */

require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Security.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get database configuration
$config = require __DIR__ . '/config/database.php';

// Create database instance
$db = Database::getInstance($config);

// Select the main database
$db->selectDatabase('u850876726_users');

// Legacy compatibility functions for existing code
function getCount_help($tableName) {
    global $db;
    try {
        $sql = "SELECT COUNT(*) as count FROM `$tableName`";
        $result = $db->fetchOne($sql);
        return $result['count'];
    } catch (Exception $e) {
        error_log("Count query error: " . $e->getMessage());
        return 0;
    }
}

// Legacy mysqli compatibility (for existing code)
class LegacyMySQLi {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function query($sql) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return new LegacyResult($stmt);
        } catch (Exception $e) {
            error_log("Query error: " . $e->getMessage());
            return false;
        }
    }
    
    public function prepare($sql) {
        return $this->pdo->prepare($sql);
    }
    
    public function real_escape_string($string) {
        return $this->pdo->quote($string);
    }
    
    public function close() {
        $this->pdo = null;
    }
}

class LegacyResult {
    private $stmt;
    private $data;
    private $position = 0;
    
    public function __construct($stmt) {
        $this->stmt = $stmt;
        $this->data = $stmt->fetchAll();
    }
    
    public function fetch_assoc() {
        if ($this->position < count($this->data)) {
            return $this->data[$this->position++];
        }
        return false;
    }
    
    public function fetch_array() {
        return $this->fetch_assoc();
    }
    
    public function num_rows() {
        return count($this->data);
    }
}

// Create legacy mysqli object for compatibility
$mysqli = new LegacyMySQLi($db->getConnection());

// Export for global use
return $db;
