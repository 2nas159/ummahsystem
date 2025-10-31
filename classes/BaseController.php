<?php
/**
 * Base Controller Class
 * Provides common functionality for all controllers
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Logger.php';

abstract class BaseController {
    protected $db;
    protected $user;
    protected $security;
    protected $logger;
    
    public function __construct() {
        $this->security = new Security();
        $this->user = new User();
        $this->logger = new Logger();
        $this->initializeDatabase();
    }
    
    /**
     * Initialize database connection
     */
    protected function initializeDatabase() {
        $config = require __DIR__ . '/../config/database.php';
        $this->db = Database::getInstance($config);
    }
    
    /**
     * Check if user is authenticated
     */
    protected function requireAuth() {
        if (!$this->user->isLoggedIn()) {
            $this->redirect('index.php');
        }
    }
    
    /**
     * Check if user has specific role
     */
    protected function requireRole($role) {
        $this->requireAuth();
        $currentUser = $this->user->getCurrentUser();
        
        if ($currentUser['usertype'] !== $role) {
            $this->redirect('index.php');
        }
    }
    
    /**
     * Redirect to URL
     */
    protected function redirect($url) {
        header("Location: $url");
        exit();
    }
    
    /**
     * Render view with data
     */
    protected function render($view, $data = []) {
        extract($data);
        include __DIR__ . "/../views/$view.php";
    }
    
    /**
     * Return JSON response
     */
    protected function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    /**
     * Log user action
     */
    protected function logAction($action, $details = []) {
        $user = $this->user->getCurrentUser();
        $this->logger->info("User action: $action", array_merge($details, [
            'user_id' => $user['user_id'] ?? null,
            'username' => $user['username'] ?? null
        ]));
    }
    
    /**
     * Handle form submission
     */
    protected function handleFormSubmission($callback) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->security->verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                return $this->jsonResponse(['error' => 'رمز الأمان غير صحيح'], 400);
            }
            
            return $callback();
        }
        
        return $this->jsonResponse(['error' => 'طريقة الطلب غير صحيحة'], 405);
    }
    
    /**
     * Get paginated results
     */
    protected function getPaginatedResults($sql, $params = [], $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM ($sql) as count_table";
        $totalResult = $this->db->fetchOne($countSql, $params);
        $total = $totalResult['total'];
        
        // Get paginated results
        $paginatedSql = "$sql LIMIT $limit OFFSET $offset";
        $results = $this->db->fetchAll($paginatedSql, $params);
        
        return [
            'data' => $results,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }
}
