<?php
/**
 * Donator Controller
 * Handles all donator-related operations
 */

require_once __DIR__ . '/BaseController.php';

class DonatorController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->db->selectDatabase('u850876726_donators_help');
    }
    
    /**
     * Get all donators with pagination
     */
    public function getAllDonators($page = 1, $limit = 20) {
        $sql = "SELECT * FROM donators ORDER BY NO DESC";
        return $this->getPaginatedResults($sql, [], $page, $limit);
    }
    
    /**
     * Search donators
     */
    public function searchDonators($query, $page = 1, $limit = 20) {
        $searchTerm = "%$query%";
        $sql = "SELECT * FROM donators WHERE ADI LIKE ? OR TEL LIKE ? ORDER BY ADI ASC";
        return $this->getPaginatedResults($sql, [$searchTerm, $searchTerm], $page, $limit);
    }
    
    /**
     * Get donator by ID
     */
    public function getDonatorById($id) {
        $sql = "SELECT * FROM donators WHERE NO = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Add new donator
     */
    public function addDonator($data) {
        $this->requireAuth();
        
        // Validate data
        $errors = $this->validateDonatorData($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Check if donator number already exists
        $existing = $this->getDonatorById($data['NO']);
        if ($existing) {
            return ['success' => false, 'errors' => ['رقم المتبرع موجود بالفعل']];
        }
        
        try {
            $sql = "INSERT INTO donators (NO, ADI, TEL) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$data['NO'], $data['ADI'], $data['TEL']]);
            
            if ($result) {
                $this->logAction('donator_added', ['donator_id' => $data['NO']]);
                return ['success' => true, 'message' => 'تم إضافة المتبرع بنجاح'];
            } else {
                return ['success' => false, 'errors' => ['فشل في إضافة المتبرع']];
            }
        } catch (Exception $e) {
            $this->logger->error("Add donator error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['حدث خطأ في النظام']];
        }
    }
    
    /**
     * Update donator
     */
    public function updateDonator($id, $data) {
        $this->requireAuth();
        
        // Validate data
        $errors = $this->validateDonatorData($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $sql = "UPDATE donators SET ADI = ?, TEL = ? WHERE NO = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$data['ADI'], $data['TEL'], $id]);
            
            if ($result) {
                $this->logAction('donator_updated', ['donator_id' => $id]);
                return ['success' => true, 'message' => 'تم تحديث المتبرع بنجاح'];
            } else {
                return ['success' => false, 'errors' => ['فشل في تحديث المتبرع']];
            }
        } catch (Exception $e) {
            $this->logger->error("Update donator error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['حدث خطأ في النظام']];
        }
    }
    
    /**
     * Delete donator
     */
    public function deleteDonator($id) {
        $this->requireAuth();
        
        try {
            // Check if donator exists
            $donator = $this->getDonatorById($id);
            if (!$donator) {
                return ['success' => false, 'errors' => ['المتبرع غير موجود']];
            }
            
            $sql = "DELETE FROM donators WHERE NO = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$id]);
            
            if ($result) {
                $this->logAction('donator_deleted', ['donator_id' => $id]);
                return ['success' => true, 'message' => 'تم حذف المتبرع بنجاح'];
            } else {
                return ['success' => false, 'errors' => ['فشل في حذف المتبرع']];
            }
        } catch (Exception $e) {
            $this->logger->error("Delete donator error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['حدث خطأ في النظام']];
        }
    }
    
    /**
     * Get donator statistics
     */
    public function getStatistics() {
        try {
            $totalDonators = $this->db->fetchOne("SELECT COUNT(*) as count FROM donators")['count'];
            
            return [
                'total_donators' => $totalDonators,
                'recent_additions' => $this->getRecentAdditions()
            ];
        } catch (Exception $e) {
            $this->logger->error("Get statistics error: " . $e->getMessage());
            return ['total_donators' => 0, 'recent_additions' => []];
        }
    }
    
    /**
     * Get recent additions
     */
    private function getRecentAdditions() {
        $sql = "SELECT * FROM donators ORDER BY NO DESC LIMIT 5";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Validate donator data
     */
    private function validateDonatorData($data) {
        $errors = [];
        
        if (empty($data['NO'])) {
            $errors[] = 'رقم المتبرع مطلوب';
        } elseif (!is_numeric($data['NO'])) {
            $errors[] = 'رقم المتبرع يجب أن يكون رقماً';
        }
        
        if (empty($data['ADI'])) {
            $errors[] = 'اسم المتبرع مطلوب';
        }
        
        if (empty($data['TEL'])) {
            $errors[] = 'رقم الهاتف مطلوب';
        } elseif (!$this->security->validatePhone($data['TEL'])) {
            $errors[] = 'رقم الهاتف غير صحيح';
        }
        
        return $errors;
    }
}
