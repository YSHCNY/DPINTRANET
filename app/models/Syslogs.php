<?php
require_once '../app/core/Model.php';

class SyslogsModel {
    private $db;
    private $table = "systemLogs";
    

    public function __construct() {
     $this->db = Database::connect();
    }

    public function getAllData() {
        $stmt = $this->db->query("SELECT * FROM " . $this->table . " ORDER BY logDate DESC");
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($logs as &$log) {
            $rawUser = trim((string)($log['userName'] ?? ''));
            $resolvedUser = null;

            if ($rawUser !== '') {
                if (ctype_digit($rawUser)) {
                    $stmtUser = $this->db->prepare("SELECT id, firstName, lastName, username, position FROM UserTbl WHERE id = ? LIMIT 1");
                    $stmtUser->execute([(int)$rawUser]);
                    $resolvedUser = $stmtUser->fetch(PDO::FETCH_ASSOC);
                }

                if (!$resolvedUser) {
                    $stmtUser = $this->db->prepare("SELECT id, firstName, lastName, username, position FROM UserTbl WHERE LOWER(username) = LOWER(?) LIMIT 1");
                    $stmtUser->execute([$rawUser]);
                    $resolvedUser = $stmtUser->fetch(PDO::FETCH_ASSOC);
                }

                if (!$resolvedUser) {
                    $stmtUser = $this->db->prepare("SELECT id, firstName, lastName, username, position FROM UserTbl WHERE LOWER(CONCAT(firstName, ' ', lastName)) = LOWER(?) OR LOWER(firstName) = LOWER(?) OR LOWER(lastName) = LOWER(?) LIMIT 1");
                    $stmtUser->execute([$rawUser, $rawUser, $rawUser]);
                    $resolvedUser = $stmtUser->fetch(PDO::FETCH_ASSOC);
                }
            }

            $firstName = $resolvedUser['firstName'] ?? null;
            $lastName = $resolvedUser['lastName'] ?? null;
            $userId = $resolvedUser['id'] ?? null;

            $displayName = trim(($firstName ?? '') . ' ' . ($lastName ?? ''));
            if ($displayName === '') {
                $displayName = $rawUser !== '' ? $rawUser : 'System';
            }

            $log['resolved_user_id'] = $userId;
            $log['resolved_first_name'] = $firstName;
            $log['resolved_last_name'] = $lastName;
            $log['display_name'] = $displayName;
        }

        return $logs;
    }
}