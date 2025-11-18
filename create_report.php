<?php
session_start();
include("reports_db.php");
include("includes/csrf_helper.php");

// Check authentication
if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'خطأ أمني: رمز التحقق غير صحيح';
        header('Location: reports.php');
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Validate and sanitize inputs
        $report_name  = trim($_POST['report_name'] ?? '');
        $report_month = $_POST['report_month'] ?? '';
        $report_year  = $_POST['report_year'] ?? '';
        $report_type  = $_POST['report_type'] ?? 'monthly';
        $status       = $_POST['status'] ?? 'draft';
        
        // Validation
        if (empty($report_name)) {
            throw new Exception('اسم التقرير مطلوب');
        }

        // Determine stored report_month value based on type
        if ($report_type === 'annual') {
            if (empty($report_year)) {
                throw new Exception('سنة التقرير مطلوبة للتقرير السنوي');
            }
            if (!preg_match('/^\d{4}$/', $report_year)) {
                throw new Exception('صيغة السنة غير صحيحة');
            }
            // Store as last day of the year (e.g. 2024-12-31) to avoid month conflicts
            $report_month_value = $report_year . '-12-31';
        } else {
            if (empty($report_month)) {
                throw new Exception('شهر التقرير مطلوب');
            }
            // Validate date format YYYY-MM
            if (!preg_match('/^\d{4}-\d{2}$/', $report_month)) {
                throw new Exception('صيغة التاريخ غير صحيحة');
            }
            $report_month_value = $report_month . '-01';
        }
        
        $user_id = getCurrentUserId();
        
        // Insert report metadata
        $stmt = $pdo->prepare("
            INSERT INTO reports (report_name, report_month, report_type, created_by, status) 
            VALUES (:report_name, :report_month, :report_type, :created_by, :status)
        ");
        $stmt->execute([
            ':report_name' => $report_name,
            ':report_month' => $report_month_value,
            ':report_type' => $report_type,
            ':created_by' => $user_id,
            ':status' => $status
        ]);
        
        $report_id = $pdo->lastInsertId();
        
        // Insert report items
        $goals = $_POST['goals'] ?? [];
        $stmt = $pdo->prepare("
            INSERT INTO report_items 
            (report_id, goal, tasks, number_of_individuals, negatives_and_obstacles, 
             evaluation, amount, completion_percentage, notes_and_recommendations, item_order) 
            VALUES (:report_id, :goal, :tasks, :number_of_individuals, :negatives_and_obstacles, 
                    :evaluation, :amount, :completion_percentage, :notes_and_recommendations, :item_order)
        ");
        
        $item_count = 0;
        foreach ($goals as $index => $goal) {
            $goal = trim($goal);
            if (empty($goal)) continue; // Skip empty goals
            
            $stmt->execute([
                ':report_id' => $report_id,
                ':goal' => $goal,
                ':tasks' => trim($_POST['tasks'][$index] ?? ''),
                ':number_of_individuals' => (int)($_POST['number_of_individuals'][$index] ?? 0),
                ':negatives_and_obstacles' => trim($_POST['negatives_and_obstacles'][$index] ?? ''),
                ':evaluation' => trim($_POST['evaluation'][$index] ?? ''),
                ':amount' => (float)($_POST['amount'][$index] ?? 0),
                ':completion_percentage' => min(100, max(0, (float)($_POST['completion_percentage'][$index] ?? 0))),
                ':notes_and_recommendations' => trim($_POST['notes_and_recommendations'][$index] ?? ''),
                ':item_order' => $item_count++
            ]);
        }
        
        if ($item_count === 0) {
            throw new Exception('يجب إضافة هدف واحد على الأقل');
        }
        
        $pdo->commit();
        $_SESSION['success_message'] = 'تم إنشاء التقرير بنجاح';
        header('Location: view_reports.php');
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
        header('Location: reports.php');
        exit;
    }
}

// If not POST, redirect to reports.php
header('Location: reports.php');
exit;
?>
