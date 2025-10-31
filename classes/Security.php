<?php
/**
 * Security Helper Class
 * Provides security functions for input validation, CSRF protection, and password handling
 */

class Security {
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     */
    public static function verifyCSRFToken($token) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Sanitize input data
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone number (basic validation)
     */
    public static function validatePhone($phone) {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        return strlen($phone) >= 10 && strlen($phone) <= 15;
    }
    
    /**
     * Hash password securely
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Generate secure random string
     */
    public static function generateRandomString($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Validate file upload
     */
    public static function validateFileUpload($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'], $maxSize = 5242880) {
        $errors = [];
        
        if (!isset($file['error']) || is_array($file['error'])) {
            $errors[] = 'Invalid file upload';
            return $errors;
        }
        
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                $errors[] = 'No file uploaded';
                return $errors;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errors[] = 'File too large';
                return $errors;
            default:
                $errors[] = 'Unknown upload error';
                return $errors;
        }
        
        if ($file['size'] > $maxSize) {
            $errors[] = 'File too large';
        }
        
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $allowedTypes)) {
            $errors[] = 'Invalid file type';
        }
        
        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif'
        ];
        
        if (!isset($allowedMimes[$fileExtension]) || $mimeType !== $allowedMimes[$fileExtension]) {
            $errors[] = 'Invalid file type';
        }
        
        return $errors;
    }
    
    /**
     * Secure file upload
     */
    public static function secureFileUpload($file, $uploadDir, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'], $maxSize = 5242880) {
        $errors = self::validateFileUpload($file, $allowedTypes, $maxSize);
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $newFileName = uniqid() . '_' . time() . '.' . $fileExtension;
        $uploadPath = $uploadDir . '/' . $newFileName;
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return ['success' => true, 'filename' => $newFileName, 'path' => $uploadPath];
        } else {
            return ['success' => false, 'errors' => ['Failed to move uploaded file']];
        }
    }
    
    /**
     * Rate limiting (simple implementation)
     */
    public static function checkRateLimit($identifier, $maxAttempts = 10, $timeWindow = 60) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $key = 'rate_limit_' . $identifier;
        $now = time();
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'reset_time' => $now + $timeWindow];
        }
        
        $rateLimit = $_SESSION[$key];
        
        if ($now > $rateLimit['reset_time']) {
            $_SESSION[$key] = ['count' => 0, 'reset_time' => $now + $timeWindow];
            $rateLimit = $_SESSION[$key];
        }
        
        if ($rateLimit['count'] >= $maxAttempts) {
            return false;
        }
        
        $_SESSION[$key]['count']++;
        return true;
    }
}
