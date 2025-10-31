<?php
/**
 * Global Search API
 * Provides unified search across all modules
 */

require_once __DIR__ . '/../secure_init.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/DonatorController.php';
require_once __DIR__ . '/../classes/BeneficiaryController.php';

// Check authentication
$userAuth = new User();
if (!$userAuth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'غير مصرح بالوصول']);
    exit();
}

$query = $_GET['q'] ?? '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = isset($_GET['per_page']) ? max(1, min(20, (int)$_GET['per_page'])) : 5;

if (strlen($query) < 2) {
    echo json_encode(['results' => []]);
    exit();
}

try {
    $results = [];
    
    // Search donators
    $donatorController = new DonatorController();
    $donatorResults = $donatorController->searchDonators($query, $page, $perPage);
    
    foreach ($donatorResults['data'] as $donator) {
        $results[] = [
            'title' => $donator['ADI'],
            'description' => 'متبرع - ' . $donator['TEL'],
            'type' => 'متبرع',
            'url' => 'donators.php?search=' . urlencode($query)
        ];
    }
    
    // Search customized beneficiaries
    $customizedController = new BeneficiaryController('customized');
    $customizedResults = $customizedController->searchBeneficiaries($query, $page, $perPage);
    
    foreach ($customizedResults['data'] as $beneficiary) {
        $results[] = [
            'title' => $beneficiary['name'],
            'description' => 'مستفيد من المساعدات الخاصة - ' . $beneficiary['phone'],
            'type' => 'مستفيد',
            'url' => 'customized_subsidies/beneficiaries_list.php?search=' . urlencode($query)
        ];
    }
    
    // Search urgent beneficiaries
    $urgentController = new BeneficiaryController('urgent');
    $urgentResults = $urgentController->searchBeneficiaries($query, $page, $perPage);
    
    foreach ($urgentResults['data'] as $beneficiary) {
        $results[] = [
            'title' => $beneficiary['name'],
            'description' => 'مستفيد من المساعدات العاجلة - ' . $beneficiary['phone'],
            'type' => 'مستفيد عاجل',
            'url' => 'urgent_subsidies/beneficiaries_list.php?search=' . urlencode($query)
        ];
    }
    
    // Limit results and include meta
    $maxItems = $perPage * 3; // across 3 modules
    $results = array_slice($results, 0, $maxItems);
    
    echo json_encode([
        'results' => $results,
        'meta' => [
            'page' => $page,
            'per_page' => $perPage
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    Logger::error("Global search error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'حدث خطأ في البحث']);
}
?>
