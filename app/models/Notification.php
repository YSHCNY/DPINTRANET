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
            message TEXT NOT NULL,
            url VARCHAR(255) NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        try {
            $this->conn->exec($sql);
        } catch (PDOException $e) {
            error_log('Notification table creation failed: ' . $e->getMessage());
        }
    }

    public function create($userId, $message, $url = null) {
        $sql = "INSERT INTO notifications (user_id, message, url, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$userId, $message, $url]);
    }

    public function createForMany(array $userIds, $message, $url = null) {
        $result = true;
        $stmt = $this->conn->prepare("INSERT INTO notifications (user_id, message, url, created_at) VALUES (?, ?, ?, NOW())");
        foreach ($userIds as $uid) {
            try {
                $result = $stmt->execute([$uid, $message, $url]) && $result;
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

    public function getUnreadCount($userId) {
        $sql = "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public function markRead($id) {
        $sql = "UPDATE notifications SET is_read = 1 WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function findById($id) {
        $sql = "SELECT * FROM notifications WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
