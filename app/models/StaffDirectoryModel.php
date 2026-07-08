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

    public function getStaffById(string $staffId): ?array {
        $sql = "SELECT * FROM staff_directory WHERE staff_id = :staff_id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':staff_id' => $staffId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result === false ? null : $result;
    }

    public function updateStaff(array $data): bool {
        $sql = "UPDATE staff_directory SET firstName = :firstName, lastName = :lastName, position = :position, department = :department, email = :email, contact_number = :contact_number, deployment_date = :deployment_date, image = :image, status = :status WHERE staff_id = :staff_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':staff_id' => $data['staff_id'],
            ':firstName' => $data['firstName'],
            ':lastName' => $data['lastName'],
            ':position' => $data['position'],
            ':department' => $data['department'],
            ':email' => $data['email'],
            ':contact_number' => $data['contact_number'],
            ':deployment_date' => $data['deployment_date'],
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
