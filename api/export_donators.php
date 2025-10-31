<?php
/**
 * Export Donators API
 * Exports donators data in various formats
 */

require_once __DIR__ . '/../secure_init.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/DonatorController.php';

// Check authentication
$userAuth = new User();
if (!$userAuth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'غير مصرح بالوصول']);
    exit();
}

$format = $_GET['format'] ?? 'excel';
$search = $_GET['search'] ?? '';

try {
    $donatorController = new DonatorController();
    
    // Get data
    if (!empty($search)) {
        $result = $donatorController->searchDonators($search, 1, 1000);
    } else {
        $result = $donatorController->getAllDonators(1, 1000);
    }
    
    $donators = $result['data'];
    
    if ($format === 'excel') {
        // Generate Excel file
        $filename = 'donators_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Add BOM for UTF-8
        echo "\xEF\xBB\xBF";
        
        $output = fopen('php://output', 'w');
        
        // Headers
        fputcsv($output, ['الرقم', 'الاسم', 'رقم الهاتف', 'تاريخ الإضافة'], ',');
        
        // Data
        foreach ($donators as $donator) {
            fputcsv($output, [
                $donator['NO'],
                $donator['ADI'],
                $donator['TEL'],
                date('Y-m-d', strtotime($donator['created_at'] ?? 'now'))
            ], ',');
        }
        
        fclose($output);
        
    } elseif ($format === 'pdf') {
        // Generate PDF (simplified version)
        $filename = 'donators_' . date('Y-m-d_H-i-s') . '.pdf';
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Simple PDF generation (you might want to use a proper PDF library like TCPDF)
        echo "%PDF-1.4\n";
        echo "1 0 obj\n";
        echo "<<\n";
        echo "/Type /Catalog\n";
        echo "/Pages 2 0 R\n";
        echo ">>\n";
        echo "endobj\n";
        
        echo "2 0 obj\n";
        echo "<<\n";
        echo "/Type /Pages\n";
        echo "/Kids [3 0 R]\n";
        echo "/Count 1\n";
        echo ">>\n";
        echo "endobj\n";
        
        echo "3 0 obj\n";
        echo "<<\n";
        echo "/Type /Page\n";
        echo "/Parent 2 0 R\n";
        echo "/MediaBox [0 0 612 792]\n";
        echo "/Contents 4 0 R\n";
        echo ">>\n";
        echo "endobj\n";
        
        echo "4 0 obj\n";
        echo "<<\n";
        echo "/Length 100\n";
        echo ">>\n";
        echo "stream\n";
        echo "BT\n";
        echo "/F1 12 Tf\n";
        echo "50 750 Td\n";
        echo "(Donators Report) Tj\n";
        echo "ET\n";
        echo "endstream\n";
        echo "endobj\n";
        
        echo "xref\n";
        echo "0 5\n";
        echo "0000000000 65535 f \n";
        echo "0000000009 00000 n \n";
        echo "0000000058 00000 n \n";
        echo "0000000115 00000 n \n";
        echo "0000000204 00000 n \n";
        echo "trailer\n";
        echo "<<\n";
        echo "/Size 5\n";
        echo "/Root 1 0 R\n";
        echo ">>\n";
        echo "startxref\n";
        echo "350\n";
        echo "%%EOF\n";
        
    } else {
        throw new Exception('تنسيق غير مدعوم');
    }
    
} catch (Exception $e) {
    Logger::error("Export error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'حدث خطأ في التصدير']);
}
?>
