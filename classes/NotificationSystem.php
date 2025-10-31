<?php
/**
 * Notification System
 * Handles system notifications and alerts
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Logger.php';

class NotificationSystem {
    private $db;
    
    public function __construct() {
        $config = require __DIR__ . '/../config/database.php';
        $this->db = Database::getInstance($config);
        $this->db->selectDatabase('u850876726_users');
    }
    
    /**
     * Add notification
     */
    public function addNotification($userId, $title, $message, $type = 'info', $actionUrl = null) {
        try {
            $sql = "INSERT INTO notifications (user_id, title, message, type, action_url, created_at, is_read) VALUES (?, ?, ?, ?, ?, NOW(), 0)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$userId, $title, $message, $type, $actionUrl]);
            
            if ($result) {
                Logger::info("Notification added", [
                    'user_id' => $userId,
                    'title' => $title,
                    'type' => $type
                ]);
                return true;
            }
            return false;
        } catch (Exception $e) {
            Logger::error("Add notification error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user notifications
     */
    public function getUserNotifications($userId, $limit = 10, $unreadOnly = false) {
        try {
            $sql = "SELECT * FROM notifications WHERE user_id = ?";
            $params = [$userId];
            
            if ($unreadOnly) {
                $sql .= " AND is_read = 0";
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT ?";
            $params[] = $limit;
            
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            Logger::error("Get notifications error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $userId) {
        try {
            $sql = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$notificationId, $userId]);
        } catch (Exception $e) {
            Logger::error("Mark notification as read error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllAsRead($userId) {
        try {
            $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$userId]);
        } catch (Exception $e) {
            Logger::error("Mark all notifications as read error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get unread count
     */
    public function getUnreadCount($userId) {
        try {
            $sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
            $result = $this->db->fetchOne($sql, [$userId]);
            return $result['count'];
        } catch (Exception $e) {
            Logger::error("Get unread count error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Delete old notifications
     */
    public function cleanupOldNotifications($days = 30) {
        try {
            $sql = "DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$days]);
            
            Logger::info("Cleaned up old notifications", ['days' => $days]);
            return $result;
        } catch (Exception $e) {
            Logger::error("Cleanup notifications error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send system notification to all users
     */
    public function sendSystemNotification($title, $message, $type = 'info', $actionUrl = null) {
        try {
            // Get all users
            $users = $this->db->fetchAll("SELECT id FROM login");
            
            $successCount = 0;
            foreach ($users as $user) {
                if ($this->addNotification($user['id'], $title, $message, $type, $actionUrl)) {
                    $successCount++;
                }
            }
            
            Logger::info("System notification sent", [
                'title' => $title,
                'recipients' => $successCount
            ]);
            
            return $successCount;
        } catch (Exception $e) {
            Logger::error("Send system notification error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Generate notification HTML
     */
    public function generateNotificationHTML($notification) {
        $typeClass = [
            'success' => 'alert-success',
            'danger' => 'alert-danger',
            'warning' => 'alert-warning',
            'info' => 'alert-info'
        ][$notification['type']] ?? 'alert-info';
        
        $readClass = $notification['is_read'] ? '' : 'fw-bold';
        $actionLink = $notification['action_url'] ? 
            "<a href='{$notification['action_url']}' class='btn btn-sm btn-outline-primary'>عرض</a>" : '';
        
        return "
        <div class='notification-item border-bottom p-3 $readClass' data-id='{$notification['id']}'>
            <div class='d-flex justify-content-between align-items-start'>
                <div class='flex-grow-1'>
                    <h6 class='mb-1'>{$notification['title']}</h6>
                    <p class='mb-1 text-muted'>{$notification['message']}</p>
                    <small class='text-muted'>{$this->formatTimeAgo($notification['created_at'])}</small>
                </div>
                <div class='ms-3'>
                    $actionLink
                    <button class='btn btn-sm btn-outline-secondary mark-read' data-id='{$notification['id']}'>
                        <i class='fas fa-check'></i>
                    </button>
                </div>
            </div>
        </div>";
    }
    
    /**
     * Format time ago
     */
    private function formatTimeAgo($datetime) {
        $time = time() - strtotime($datetime);
        
        if ($time < 60) return 'الآن';
        if ($time < 3600) return floor($time/60) . ' دقيقة';
        if ($time < 86400) return floor($time/3600) . ' ساعة';
        if ($time < 2592000) return floor($time/86400) . ' يوم';
        
        return date('Y-m-d', strtotime($datetime));
    }
}
