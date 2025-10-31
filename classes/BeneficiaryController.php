<?php
/**
 * Beneficiary Controller
 * Handles all beneficiary-related operations
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/FileUpload.php';

class BeneficiaryController extends BaseController {
    private $fileUpload;
    
    public function __construct($database = 'customized') {
        parent::__construct();
        $this->fileUpload = new FileUpload('uploads', ['jpg', 'jpeg', 'png', 'gif'], 5242880);
        
        // Select appropriate database
        if ($database === 'urgent') {
            $this->db->selectDatabase('u850876726_urgent');
        } else {
            $this->db->selectDatabase('u850876726_customized');
        }
    }
    
    /**
     * Get all beneficiaries with pagination
     */
    public function getAllBeneficiaries($page = 1, $limit = 20) {
        $sql = "SELECT * FROM beneficiaries ORDER BY created_at DESC";
        return $this->getPaginatedResults($sql, [], $page, $limit);
    }
    
    /**
     * Search beneficiaries
     */
    public function searchBeneficiaries($query, $page = 1, $limit = 20) {
        $searchTerm = "%$query%";
        $sql = "SELECT * FROM beneficiaries WHERE name LIKE ? OR phone LIKE ? ORDER BY name ASC";
        return $this->getPaginatedResults($sql, [$searchTerm, $searchTerm], $page, $limit);
    }
    
    /**
     * Get beneficiary by ID
     */
    public function getBeneficiaryById($id) {
        $sql = "SELECT * FROM beneficiaries WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Add new beneficiary
     */
    public function addBeneficiary($data, $files = []) {
        $this->requireAuth();
        
        // Validate data
        $errors = $this->validateBeneficiaryData($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            // Handle file upload
            $kimlikImage = null;
            if (isset($files['kimlik_image']) && $files['kimlik_image']['error'] == 0) {
                $uploadResult = $this->fileUpload->uploadFile($files['kimlik_image'], 'beneficiary_');
                if ($uploadResult['success']) {
                    $kimlikImage = $uploadResult['filename'];
                } else {
                    return ['success' => false, 'errors' => $uploadResult['errors']];
                }
            }
            
            // Set created_at date
            $createdAt = isset($data['year']) && isset($data['month']) 
                ? date("Y-m-d", strtotime("{$data['year']}-{$data['month']}-01"))
                : date('Y-m-d');
            
            $sql = "INSERT INTO beneficiaries (name, phone, kimlik_number, iban, monthly_amount, kimlik_image, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['name'],
                $data['phone'],
                $data['kimlik_number'],
                $data['iban'],
                $data['monthly_amount'] ?? null,
                $kimlikImage,
                $createdAt
            ]);
            
            if ($result) {
                $this->logAction('beneficiary_added', ['beneficiary_name' => $data['name']]);
                return ['success' => true, 'message' => 'تم إضافة المستفيد بنجاح'];
            } else {
                return ['success' => false, 'errors' => ['فشل في إضافة المستفيد']];
            }
        } catch (Exception $e) {
            $this->logger->error("Add beneficiary error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['حدث خطأ في النظام']];
        }
    }
    
    /**
     * Update beneficiary
     */
    public function updateBeneficiary($id, $data, $files = []) {
        $this->requireAuth();
        
        // Validate data
        $errors = $this->validateBeneficiaryData($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            // Handle file upload
            $kimlikImage = null;
            if (isset($files['kimlik_image']) && $files['kimlik_image']['error'] == 0) {
                $uploadResult = $this->fileUpload->uploadFile($files['kimlik_image'], 'beneficiary_');
                if ($uploadResult['success']) {
                    $kimlikImage = $uploadResult['filename'];
                } else {
                    return ['success' => false, 'errors' => $uploadResult['errors']];
                }
            }
            
            $sql = "UPDATE beneficiaries SET name = ?, phone = ?, kimlik_number = ?, iban = ?, monthly_amount = ?";
            $params = [$data['name'], $data['phone'], $data['kimlik_number'], $data['iban'], $data['monthly_amount']];
            
            if ($kimlikImage) {
                $sql .= ", kimlik_image = ?";
                $params[] = $kimlikImage;
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $id;
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result) {
                $this->logAction('beneficiary_updated', ['beneficiary_id' => $id]);
                return ['success' => true, 'message' => 'تم تحديث المستفيد بنجاح'];
            } else {
                return ['success' => false, 'errors' => ['فشل في تحديث المستفيد']];
            }
        } catch (Exception $e) {
            $this->logger->error("Update beneficiary error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['حدث خطأ في النظام']];
        }
    }
    
    /**
     * Delete beneficiary
     */
    public function deleteBeneficiary($id) {
        $this->requireAuth();
        
        try {
            // Get beneficiary info for logging
            $beneficiary = $this->getBeneficiaryById($id);
            if (!$beneficiary) {
                return ['success' => false, 'errors' => ['المستفيد غير موجود']];
            }
            
            $sql = "DELETE FROM beneficiaries WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$id]);
            
            if ($result) {
                // Delete associated file if exists
                if ($beneficiary['kimlik_image']) {
                    $this->fileUpload->deleteFile($beneficiary['kimlik_image']);
                }
                
                $this->logAction('beneficiary_deleted', ['beneficiary_id' => $id]);
                return ['success' => true, 'message' => 'تم حذف المستفيد بنجاح'];
            } else {
                return ['success' => false, 'errors' => ['فشل في حذف المستفيد']];
            }
        } catch (Exception $e) {
            $this->logger->error("Delete beneficiary error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['حدث خطأ في النظام']];
        }
    }
    
    /**
     * Get beneficiary statistics
     */
    public function getStatistics() {
        try {
            $totalBeneficiaries = $this->db->fetchOne("SELECT COUNT(*) as count FROM beneficiaries")['count'];
            $totalAmount = $this->db->fetchOne("SELECT SUM(monthly_amount) as total FROM beneficiaries WHERE monthly_amount IS NOT NULL")['total'] ?? 0;
            
            return [
                'total_beneficiaries' => $totalBeneficiaries,
                'total_monthly_amount' => $totalAmount,
                'recent_additions' => $this->getRecentAdditions()
            ];
        } catch (Exception $e) {
            $this->logger->error("Get statistics error: " . $e->getMessage());
            return ['total_beneficiaries' => 0, 'total_monthly_amount' => 0, 'recent_additions' => []];
        }
    }
    
    /**
     * Get recent additions
     */
    private function getRecentAdditions() {
        $sql = "SELECT * FROM beneficiaries ORDER BY created_at DESC LIMIT 5";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Validate beneficiary data
     */
    private function validateBeneficiaryData($data) {
        $errors = [];
        
        if (empty($data['name'])) {
            $errors[] = 'اسم المستفيد مطلوب';
        }
        
        if (!empty($data['phone']) && !$this->security->validatePhone($data['phone'])) {
            $errors[] = 'رقم الهاتف غير صحيح';
        }
        
        if (isset($data['monthly_amount']) && !empty($data['monthly_amount']) && (!is_numeric($data['monthly_amount']) || $data['monthly_amount'] < 0)) {
            $errors[] = 'المبلغ الشهري يجب أن يكون رقماً موجباً';
        }
        
        return $errors;
    }
}
