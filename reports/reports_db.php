<?php
/**
 * Reports Database Connection
 */

// Load database config (which also loads .env)
$config = require __DIR__ . '/../config/database.php';

$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['REPORTS_DB_NAME'] ?? 'reports';
$username = $_ENV['REPORTS_DB_USER'] ?? 'root';
$password = $_ENV['REPORTS_DB_PASS'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ensure report_templates table exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS report_templates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            template_name VARCHAR(255) NOT NULL,
            template_type VARCHAR(50) DEFAULT 'custom',
            template_data LONGTEXT NOT NULL,
            created_by INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Ensure reports table has approval workflow columns
    try {
        $pdo->exec("ALTER TABLE reports
            ADD COLUMN IF NOT EXISTS approved_by INT NULL,
            ADD COLUMN IF NOT EXISTS approved_at DATETIME NULL");
    } catch (Exception $inner) {
        // Ignore if ALTER not supported (older MySQL versions)
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
