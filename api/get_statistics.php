<?php
/**
 * Statistics API Endpoint
 * Provides real-time statistics for dashboard
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

try {
    $detail = isset($_GET['detail']) && $_GET['detail'] === 'full' ? 'full' : 'summary';
    $recentLimit = isset($_GET['recent_limit']) ? max(0, min(20, (int)$_GET['recent_limit'])) : 0;
    // Initialize controllers
    $donatorController = new DonatorController();
    $customizedBeneficiaryController = new BeneficiaryController('customized');
    $urgentBeneficiaryController = new BeneficiaryController('urgent');
    
    // Get statistics
    $donatorStats = $donatorController->getStatistics();
    $customizedStats = $customizedBeneficiaryController->getStatistics();
    $urgentStats = $urgentBeneficiaryController->getStatistics();
    
    // Calculate totals
    $statistics = [
        'donators' => [
            'total' => $donatorStats['total_donators'],
            'recent' => count($donatorStats['recent_additions'])
        ],
        'customized_beneficiaries' => [
            'total' => $customizedStats['total_beneficiaries'],
            'total_amount' => $customizedStats['total_monthly_amount'],
            'recent' => count($customizedStats['recent_additions'])
        ],
        'urgent_beneficiaries' => [
            'total' => $urgentStats['total_beneficiaries'],
            'total_amount' => $urgentStats['total_monthly_amount'],
            'recent' => count($urgentStats['recent_additions'])
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if ($detail === 'full') {
        if ($recentLimit > 0) {
            $statistics['recent_details'] = [
                'donators' => array_slice($donatorStats['recent_additions'], 0, $recentLimit),
                'customized' => array_slice($customizedStats['recent_additions'], 0, $recentLimit),
                'urgent' => array_slice($urgentStats['recent_additions'], 0, $recentLimit)
            ];
        } else {
            $statistics['recent_details'] = [
                'donators' => $donatorStats['recent_additions'],
                'customized' => $customizedStats['recent_additions'],
                'urgent' => $urgentStats['recent_additions']
            ];
        }
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($statistics, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    Logger::error("Statistics API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'حدث خطأ في النظام']);
}
?>
