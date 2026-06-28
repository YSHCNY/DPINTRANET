<?php
require_once '../app/core/Model.php';

class User extends Model {
    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM UserTbl WHERE username = :username");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function usernameExists($username, $excludeId = null) {
        $sql = "SELECT id FROM UserTbl WHERE username = ?";
        $params = [$username];

        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($username, $password, $firstName, $lastName, $position, $userLevel, $email = '', $profilePicture = null) {
        $stmt = $this->db->prepare(
            "INSERT INTO UserTbl (`username`, `password`, `firstName`, `lastName`, `email`, `position`, `userLevel`, `profile_picture`)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        return $stmt->execute([
            $username,
            password_hash($password, PASSWORD_DEFAULT),
            $firstName,
            $lastName,
            $email,
            $position,
            $userLevel,
            $profilePicture
        ]);
    }

    public function update($id, $data) {
        $passwordSql = '';
        $params = [
            ':username' => $data['username'],
            ':firstName' => $data['firstName'],
            ':lastName' => $data['lastName'],
            ':email' => $data['email'] ?? '',
            ':position' => $data['position'],
            ':userLevel' => $data['userLevel'],
            ':profile_picture' => $data['profile_picture'],
            ':id' => $id,
        ];

        if (!empty($data['password'])) {
            $passwordSql = ", `password` = :password";
            $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $sql = "UPDATE UserTbl
                SET username = :username,
                    firstName = :firstName,
                    lastName = :lastName,
                    email = :email,
                    position = :position,
                    userLevel = :userLevel,
                    profile_picture = :profile_picture
                    {$passwordSql}
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function log($username, $firstName, $lastName, $position, $uploader, $userLevel) {
        $labels = [
            0 => 'Super Admin',
            1 => 'Admin',
            2 => 'Encoder',
            3 => 'Viewer',
        ];

        $levelLabel = $labels[(int)$userLevel] ?? 'Viewer';
        $customDesc = "New core user: $lastName, $firstName ($username) • Position: $position • Level: $levelLabel • Registered by: $uploader";

        $stmt = $this->db->prepare("INSERT INTO systemLogs (`userName`, `logDesc`, `module`, `logDate`) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$uploader, $customDesc, "Core Users", date("Y-m-d H:i:s")]);
    }

    public function getAllUser() {
        $stmt = $this->db->prepare("SELECT * FROM UserTbl ORDER BY userLevel ASC, lastName ASC, firstName ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findUserById($id) {
        $stmt = $this->db->prepare("SELECT * FROM UserTbl WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM UserTbl WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function hasSuperAdmin() {
        return $this->countSuperAdmins() > 0;
    }

    public function countSuperAdmins() {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM UserTbl WHERE userLevel = 0");
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
}
