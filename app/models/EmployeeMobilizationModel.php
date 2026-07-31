<?php
require_once '../app/core/Model.php';

class EmployeeMobilizationModel extends Model {
    private string $table = 'employee_mobilizations';

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (
                employee_id,
                movement_type,
                movement_date,
                remarks,
                status,
                created_by,
                updated_by
            ) VALUES (
                :employee_id,
                :movement_type,
                :movement_date,
                :remarks,
                :status,
                :created_by,
                :updated_by
            )"
        );

        $stmt->execute([
            ':employee_id' => isset($data['employee_id']) ? (int)$data['employee_id'] : null,
            ':movement_type' => $data['movement_type'] ?? null,
            ':movement_date' => $data['movement_date'] ?? null,
            ':remarks' => $data['remarks'] ?? null,
            ':status' => $data['status'] ?? 'Scheduled',
            ':created_by' => isset($data['created_by']) ? (int)$data['created_by'] : null,
            ':updated_by' => isset($data['updated_by']) ? (int)$data['updated_by'] : null,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET employee_id = :employee_id,
                 movement_type = :movement_type,
                 movement_date = :movement_date,
                 remarks = :remarks,
                 status = :status,
                 created_by = :created_by,
                 updated_by = :updated_by,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id"
        );

        $stmt->execute([
            ':employee_id' => isset($data['employee_id']) ? (int)$data['employee_id'] : null,
            ':movement_type' => $data['movement_type'] ?? null,
            ':movement_date' => $data['movement_date'] ?? null,
            ':remarks' => $data['remarks'] ?? null,
            ':status' => $data['status'] ?? 'Scheduled',
            ':created_by' => isset($data['created_by']) ? (int)$data['created_by'] : null,
            ':updated_by' => isset($data['updated_by']) ? (int)$data['updated_by'] : null,
            ':id' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    public function findByEmployee(int $employeeId): array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE employee_id = :employee_id ORDER BY movement_date ASC");
        $stmt->execute([':employee_id' => $employeeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUpcoming(): array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE movement_date >= CURRENT_DATE ORDER BY movement_date ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByMonth(int $year, int $month): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE YEAR(movement_date) = :year
               AND MONTH(movement_date) = :month
             ORDER BY movement_date ASC"
        );
        $stmt->execute([
            ':year' => $year,
            ':month' => $month,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByDateRange(string $startDate, string $endDate): array {
        $stmt = $this->db->prepare(
            "SELECT employee_id, movement_type, movement_date, status
             FROM {$this->table}
             WHERE movement_date BETWEEN :start_date AND :end_date
             ORDER BY movement_date ASC"
        );
        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date' => $endDate,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getToday(): array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE movement_date = CURRENT_DATE ORDER BY id ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
