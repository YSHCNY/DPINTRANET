<?php
require_once '../app/core/Model.php';

class NotificationModel extends Model {
    private $conn;

    public function __construct() {
        parent::__construct();
        $this->conn = $this->db;
        $this->ensureTable();
    }

    private function ensureTable() {
        $sql = "CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            portal ENUM('admin','standard') NOT NULL DEFAULT 'admin',
            module VARCHAR(50) NOT NULL DEFAULT '',
            event_key VARCHAR(100) NOT NULL DEFAULT '',
            entity_id INT NULL,
            title VARCHAR(255) NOT NULL DEFAULT '',
            message TEXT NOT NULL,
            url VARCHAR(255) NULL,
            priority ENUM('low','normal','high','critical') NOT NULL DEFAULT 'normal',
            icon VARCHAR(50) NULL,
            created_by INT NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_notifications_user_read (user_id, is_read),
            INDEX idx_notifications_module (module),
            INDEX idx_notifications_event_key (event_key),
            INDEX idx_notifications_entity_id (entity_id),
            INDEX idx_notifications_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        try {
            $this->conn->exec($sql);
        } catch (PDOException $e) {
            error_log('Notification table creation failed: ' . $e->getMessage());
        }
    }

    public function create($userId, $message, $url = null, $module = '', $eventKey = '', $entityId = null, $title = '', $priority = 'normal', $icon = null, $createdBy = null, $portal = 'admin') {
        $userId = (int)$userId;
        $module = trim((string)$module);
        $eventKey = trim((string)$eventKey);
        $portal = strtolower(trim((string)$portal));

        if (!in_array($portal, ['admin', 'standard'], true)) {
            $portal = 'admin';
        }

        if ($userId <= 0 || $module === '' || $eventKey === '') {
            return false;
        }

        $sql = "INSERT INTO notifications (user_id, portal, module, event_key, entity_id, title, message, url, priority, icon, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$userId, $portal, $module, $eventKey, $entityId, $title, $message, $url, $priority, $icon, $createdBy]);
    }

    public function createForMany(array $userIds, $message, $url = null, $module = '', $eventKey = '', $entityId = null, $title = '', $priority = 'normal', $icon = null, $createdBy = null, $portal = 'admin') {
        $result = true;
        $module = trim((string)$module);
        $eventKey = trim((string)$eventKey);
        $portal = strtolower(trim((string)$portal));

        if (!in_array($portal, ['admin', 'standard'], true)) {
            $portal = 'admin';
        }

        if ($module === '' || $eventKey === '') {
            return false;
        }

        $stmt = $this->conn->prepare("INSERT INTO notifications (user_id, portal, module, event_key, entity_id, title, message, url, priority, icon, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        foreach ($userIds as $uid) {
            $uid = (int)$uid;
            if ($uid <= 0) {
                continue;
            }

            try {
                $result = $stmt->execute([$uid, $portal, $module, $eventKey, $entityId, $title, $message, $url, $priority, $icon, $createdBy]) && $result;
            } catch (Throwable $e) {
                error_log('Notification insert failed for user ' . $uid . ': ' . $e->getMessage());
            }
        }
        return $result;
    }

    public function getForUser($userId, $limit = 12) {
        $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByIdForUser(int $notificationId, int $userId) {
        $sql = "SELECT * FROM notifications WHERE id = ? AND user_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$notificationId, $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function markReadForUser(int $notificationId, int $userId) {
        $sql = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$notificationId, $userId]);
    }

    public function markAllReadForUser(int $userId) {
        $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$userId]);
    }

    public function delete($id) {
        $sql = "DELETE FROM notifications WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
}
