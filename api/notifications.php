<?php
/**
 * Notifications API Endpoint
 * Handles notification operations
 */

require_once __DIR__ . '/../secure_init.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/NotificationSystem.php';

// Check authentication
$userAuth = new User();
if (!$userAuth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'غير مصرح بالوصول']);
    exit();
}

$currentUser = $userAuth->getCurrentUser();
$notificationSystem = new NotificationSystem();

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get':
            $unreadOnly = $_GET['unread_only'] === 'true';
            $limit = (int)($_GET['limit'] ?? 10);
            
            $notifications = $notificationSystem->getUserNotifications(
                $currentUser['user_id'], 
                $limit, 
                $unreadOnly
            );
            
            $html = '';
            foreach ($notifications as $notification) {
                $html .= $notificationSystem->generateNotificationHTML($notification);
            }
            
            echo json_encode([
                'notifications' => $notifications,
                'html' => $html,
                'unread_count' => $notificationSystem->getUnreadCount($currentUser['user_id'])
            ], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'mark_read':
            $notificationId = $_POST['notification_id'] ?? '';
            if ($notificationId) {
                $result = $notificationSystem->markAsRead($notificationId, $currentUser['user_id']);
                echo json_encode(['success' => $result]);
            } else {
                echo json_encode(['error' => 'معرف الإشعار مطلوب']);
            }
            break;
            
        case 'mark_all_read':
            $result = $notificationSystem->markAllAsRead($currentUser['user_id']);
            echo json_encode(['success' => $result]);
            break;
            
        case 'unread_count':
            $count = $notificationSystem->getUnreadCount($currentUser['user_id']);
            echo json_encode(['count' => $count]);
            break;
            
        default:
            echo json_encode(['error' => 'عملية غير مدعومة']);
    }
    
} catch (Exception $e) {
    Logger::error("Notifications API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'حدث خطأ في النظام']);
}
?>
