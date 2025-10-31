<?php
/**
 * User Authentication Class
 * Secure user authentication with proper password handling
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Security.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Authenticate user with secure password verification
     */
    public function authenticate($username, $password) {
        try {
            // Sanitize input
            $username = Security::sanitizeInput($username);
            
            // Check rate limiting
            if (!Security::checkRateLimit($username)) {
                return ['success' => false, 'message' => 'Too many login attempts. Please try again later.'];
            }
            
            // Use prepared statement to prevent SQL injection
            $sql = "SELECT * FROM login WHERE username = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && Security::verifyPassword($password, $user['password'])) {
                // Reset rate limiting on successful login
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                unset($_SESSION['rate_limit_' . $username]);
                
                // Set session variables
                $_SESSION['username'] = $user['username'];
                $_SESSION['isim'] = $user['isim'] ?? $user['username'];
                $_SESSION['profile_image'] = $user['profile_image'] ?? 'default.png';
                $_SESSION['usertype'] = $user['usertype'] ?? 'user';
                $_SESSION['user_id'] = $user['id'] ?? $user['NO'];
                
                return [
                    'success' => true, 
                    'user' => $user,
                    'redirect' => $this->getRedirectUrl($user['usertype'])
                ];
            } else {
                return ['success' => false, 'message' => 'اسم المستخدم أو كلمة المرور غير صحيحة.'];
            }
        } catch (Exception $e) {
            error_log("Authentication error: " . $e->getMessage());
            return ['success' => false, 'message' => 'حدث خطأ في النظام. يرجى المحاولة لاحقاً.'];
        }
    }
    
    /**
     * Get redirect URL based on user type
     */
    private function getRedirectUrl($usertype) {
        switch ($usertype) {
            case 'admin':
                return 'home_admin.php';
            case 'user':
                return 'home_hr.php';
            default:
                return 'index.php';
        }
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['username']);
    }
    
    /**
     * Get current user data
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'username' => $_SESSION['username'],
            'isim' => $_SESSION['isim'],
            'profile_image' => $_SESSION['profile_image'],
            'usertype' => $_SESSION['usertype'],
            'user_id' => $_SESSION['user_id']
        ];
    }
    
    /**
     * Logout user
     */
    public function logout() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Clear all session data
        $_SESSION = array();
        
        // Destroy session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy session
        session_destroy();
    }
    
    /**
     * Update user password
     */
    public function updatePassword($userId, $newPassword) {
        try {
            $hashedPassword = Security::hashPassword($newPassword);
            $sql = "UPDATE login SET password = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$hashedPassword, $userId]);
        } catch (Exception $e) {
            error_log("Password update error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create new user
     */
    public function createUser($username, $password, $isim, $usertype = 'user', $profileImage = 'default.png') {
        try {
            $hashedPassword = Security::hashPassword($password);
            $sql = "INSERT INTO login (username, password, isim, usertype, profile_image) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$username, $hashedPassword, $isim, $usertype, $profileImage]);
        } catch (Exception $e) {
            error_log("User creation error: " . $e->getMessage());
            return false;
        }
    }
}
