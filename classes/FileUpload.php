<?php
/**
 * Secure File Upload Handler
 * Handles file uploads with proper validation and security
 */

class FileUpload {
    private $uploadDir;
    private $allowedTypes;
    private $maxSize;
    
    public function __construct($uploadDir = 'uploads', $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'], $maxSize = 5242880) {
        $this->uploadDir = $uploadDir;
        $this->allowedTypes = $allowedTypes;
        $this->maxSize = $maxSize;
    }
    
    /**
     * Upload file securely
     */
    public function uploadFile($file, $prefix = '') {
        // Validate file upload
        $errors = $this->validateFile($file);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Generate secure filename
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $secureFileName = $prefix . uniqid() . '_' . time() . '.' . $fileExtension;
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
        
        $uploadPath = $this->uploadDir . '/' . $secureFileName;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return [
                'success' => true, 
                'filename' => $secureFileName,
                'path' => $uploadPath,
                'url' => $this->uploadDir . '/' . $secureFileName
            ];
        } else {
            return ['success' => false, 'errors' => ['فشل في رفع الملف']];
        }
    }
    
    /**
     * Validate uploaded file
     */
    private function validateFile($file) {
        $errors = [];
        
        // Check if file was uploaded
        if (!isset($file['error']) || is_array($file['error'])) {
            $errors[] = 'خطأ في رفع الملف';
            return $errors;
        }
        
        // Check upload errors
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                $errors[] = 'لم يتم رفع أي ملف';
                return $errors;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errors[] = 'حجم الملف كبير جداً';
                return $errors;
            default:
                $errors[] = 'خطأ غير معروف في رفع الملف';
                return $errors;
        }
        
        // Check file size
        if ($file['size'] > $this->maxSize) {
            $errors[] = 'حجم الملف يتجاوز الحد المسموح';
        }
        
        // Check file extension
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $this->allowedTypes)) {
            $errors[] = 'نوع الملف غير مسموح';
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
            $errors[] = 'نوع الملف غير صحيح';
        }
        
        return $errors;
    }
    
    /**
     * Delete file
     */
    public function deleteFile($filename) {
        $filePath = $this->uploadDir . '/' . $filename;
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }
    
    /**
     * Get file info
     */
    public function getFileInfo($filename) {
        $filePath = $this->uploadDir . '/' . $filename;
        if (file_exists($filePath)) {
            return [
                'exists' => true,
                'size' => filesize($filePath),
                'modified' => filemtime($filePath),
                'url' => $this->uploadDir . '/' . $filename
            ];
        }
        return ['exists' => false];
    }
}
