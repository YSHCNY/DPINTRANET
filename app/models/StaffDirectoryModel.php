<?php
require_once '../app/core/Model.php';

class StaffDirectoryModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::connect();
    }

    public function getAllStaff() {
        $sql = "SELECT * FROM staff_directory ORDER BY deployment_date DESC, lastName ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStaffIds(): array {
        $sql = "SELECT staff_id FROM staff_directory";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'staff_id');
    }

    public function getStaffByIds(array $ids): array {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static function ($value) {
            return $value > 0;
        })));

        if ($ids === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT id, staff_id, firstName, lastName, position, department FROM staff_directory WHERE id IN ({$placeholders})";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($ids);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStaffById($staffId): ?array {
        $sql = "SELECT * FROM staff_directory WHERE id = :staff_id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':staff_id' => $staffId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result === false ? null : $result;
    }

    public function createStaff(array $data): bool {
        $sql = "INSERT INTO staff_directory (staff_id, firstName, lastName, position, department, firm, email, contact_number, deployment_date, image, status)
                VALUES (:staff_id, :firstName, :lastName, :position, :department, :firm, :email, :contact_number, :deployment_date, :image, :status)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':staff_id' => $data['staff_id'],
            ':firstName' => $data['firstName'],
            ':lastName' => $data['lastName'],
            ':position' => trim((string)($data['position'] ?? '')),
            ':department' => trim((string)($data['department'] ?? '')),
            ':firm' => trim((string)($data['firm'] ?? '')),
            ':email' => trim((string)($data['email'] ?? '')),
            ':contact_number' => trim((string)($data['contact_number'] ?? '')),
            ':deployment_date' => !empty($data['deployment_date']) ? $data['deployment_date'] : null,
            ':image' => $data['image'],
            ':status' => $data['status'],
        ]);
    }

    public function updateStaff(array $data): bool {
        $sql = "UPDATE staff_directory SET firstName = :firstName, lastName = :lastName, position = :position, department = :department, firm = :firm, email = :email, contact_number = :contact_number, deployment_date = :deployment_date, image = :image, status = :status WHERE staff_id = :staff_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':staff_id' => $data['staff_id'],
            ':firstName' => $data['firstName'],
            ':lastName' => $data['lastName'],
            ':position' => trim((string)($data['position'] ?? '')),
            ':department' => trim((string)($data['department'] ?? '')),
            ':firm' => trim((string)($data['firm'] ?? '')),
            ':email' => trim((string)($data['email'] ?? '')),
            ':contact_number' => trim((string)($data['contact_number'] ?? '')),
            ':deployment_date' => !empty($data['deployment_date']) ? $data['deployment_date'] : null,
            ':image' => $data['image'],
            ':status' => $data['status'],
        ]);
    }

    public function deleteStaff(string $staffId): bool {
        $sql = "DELETE FROM staff_directory WHERE staff_id = :staff_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':staff_id' => $staffId]);
    }
}
