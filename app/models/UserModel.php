<?php
// app/models/UserModel.php
require_once '../app/core/Model.php';
class UserModel {

    private $conn;
    private $table = 'standardUsers';

    public function __construct() {
        $this->conn = Database::connect();
    }

    /**
     * Get all active users for Recipients & CC
     */
    public function getAllUsers() {
        $sql = "SELECT id, firstName, lastName, position, department, email 
                FROM {$this->table} 
                WHERE status = 'active' 
                ORDER BY firstName ASC, lastName ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get single user (for future portal use)
     */
    public function getUserById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findPortalUserByUsername($username) {
        $sql = "SELECT * FROM {$this->table}
                WHERE username = ?
                  AND is_portal_user = 1
                  AND status = 'active'
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findPortalUserByIdentifier(string $identifier): ?array {
        $identifier = trim($identifier);
        if ($identifier === '') {
            return null;
        }

        $sql = "SELECT * FROM {$this->table}
                WHERE (username = :identifier OR email = :identifier)
                  AND is_portal_user = 1
                  AND status = 'active'
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['identifier' => $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            return $user;
        }

        if ($this->columnExists('employee_id')) {
            $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE employee_id = :identifier AND is_portal_user = 1 AND status = 'active' LIMIT 1");
            $stmt->execute(['identifier' => $identifier]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                return $user;
            }
        }

        if ($this->columnExists('employeeId')) {
            $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE employeeId = :identifier AND is_portal_user = 1 AND status = 'active' LIMIT 1");
            $stmt->execute(['identifier' => $identifier]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                return $user;
            }
        }

        return null;
    }

    public function updatePortalPassword(int $id, string $password): bool {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET password = :password, updated_at = NOW() WHERE id = :id");
        return $stmt->execute([
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':id' => $id,
        ]);
    }

    public function getAllStandardUsers() {
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC, id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEnumValues($column) {
        $allowedColumns = ['role', 'status'];
        if (!in_array($column, $allowedColumns, true)) {
            return [];
        }

        $stmt = $this->conn->prepare("SHOW COLUMNS FROM {$this->table} LIKE ?");
        $stmt->execute([$column]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || strpos($row['Type'], 'enum(') !== 0) {
            return [];
        }

        preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'/", $row['Type'], $matches);
        return array_map('stripslashes', $matches[1] ?? []);
    }

    public function findByUsername($username, $excludeId = null) {
        $sql = "SELECT id FROM {$this->table} WHERE username = ?";
        $params = [$username];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByEmail($email, $excludeId = null) {
        if (empty($email)) {
            return false;
        }

        $sql = "SELECT id FROM {$this->table} WHERE email = ?";
        $params = [$email];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getConnection() {
        return $this->conn;
    }

    public function findExistingUsernames(array $usernames): array {
        if (empty($usernames)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($usernames), '?'));
        $sql = "SELECT LOWER(username) AS username FROM {$this->table} WHERE LOWER(username) IN ({$placeholders})";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($usernames);

        return array_map(function ($row) {
            return $row['username'];
        }, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findExistingEmails(array $emails): array {
        if (empty($emails)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($emails), '?'));
        $sql = "SELECT LOWER(email) AS email FROM {$this->table} WHERE LOWER(email) IN ({$placeholders})";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($emails);

        return array_map(function ($row) {
            return $row['email'];
        }, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function insertStandardUser(array $data) {
        $sql = "INSERT INTO {$this->table}
                (username, password, firstName, lastName, middleName, email, phone,
                 position, department, role, status, is_portal_user, pin_code, avatar, created_at, updated_at)
                VALUES
                (:username, :password, :firstName, :lastName, :middleName, :email, :phone,
                 :position, :department, :role, :status, :is_portal_user, :pin_code, :avatar, NOW(), NOW())";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':username' => $data['username'],
            ':password' => $data['password'],
            ':firstName' => $data['firstName'],
            ':lastName' => $data['lastName'],
            ':middleName' => $data['middleName'],
            ':email' => $data['email'],
            ':phone' => $data['phone'],
            ':position' => $data['position'],
            ':department' => $data['department'],
            ':role' => $data['role'],
            ':status' => $data['status'],
            ':is_portal_user' => $data['is_portal_user'],
            ':pin_code' => $data['pin_code'],
            ':avatar' => $data['avatar'],
        ]);

        return $this->conn->lastInsertId();
    }

    public function createStandardUser($data) {
        $sql = "INSERT INTO {$this->table}
                (username, password, firstName, lastName, middleName, email, phone,
                 position, department, role, status, is_portal_user, pin_code, avatar, created_at, updated_at)
                VALUES
                (:username, :password, :firstName, :lastName, :middleName, :email, :phone,
                 :position, :department, :role, :status, :is_portal_user, :pin_code, :avatar, NOW(), NOW())";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':username' => $data['username'],
            ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':firstName' => $data['firstName'],
            ':lastName' => $data['lastName'],
            ':middleName' => $data['middleName'],
            ':email' => $data['email'],
            ':phone' => $data['phone'],
            ':position' => $data['position'],
            ':department' => $data['department'],
            ':role' => $data['role'],
            ':status' => $data['status'],
            ':is_portal_user' => $data['is_portal_user'],
            ':pin_code' => $data['pin_code'],
            ':avatar' => $data['avatar'],
        ]);

        return $this->conn->lastInsertId();
    }

    public function updateStandardUser($id, $data) {
        $passwordSql = '';
        $params = [
            ':username' => $data['username'],
            ':firstName' => $data['firstName'],
            ':lastName' => $data['lastName'],
            ':middleName' => $data['middleName'],
            ':email' => $data['email'],
            ':phone' => $data['phone'],
            ':position' => $data['position'],
            ':department' => $data['department'],
            ':role' => $data['role'],
            ':status' => $data['status'],
            ':is_portal_user' => $data['is_portal_user'],
            ':pin_code' => $data['pin_code'],
            ':avatar' => $data['avatar'],
            ':id' => $id,
        ];

        if (!empty($data['password'])) {
            $passwordSql = ", password = :password";
            $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $sql = "UPDATE {$this->table}
                SET username = :username,
                    firstName = :firstName,
                    lastName = :lastName,
                    middleName = :middleName,
                    email = :email,
                    phone = :phone,
                    position = :position,
                    department = :department,
                    role = :role,
                    status = :status,
                    is_portal_user = :is_portal_user,
                    pin_code = :pin_code,
                    avatar = :avatar,
                    updated_at = NOW()
                    {$passwordSql}
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteStandardUser($id) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    private function columnExists(string $column): bool {
        $stmt = $this->conn->prepare("SHOW COLUMNS FROM {$this->table} LIKE ?");
        $stmt->execute([$column]);
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
