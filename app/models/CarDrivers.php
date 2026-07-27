<?php
require_once '../app/core/Model.php';

class CarDrivers extends Model {
    private string $table = 'car_drivers';

    private function normalizeStatus(?string $status): string {
        $allowed = ['active', 'inactive'];
        $s = strtolower(trim((string)$status));
        if (!in_array($s, $allowed, true)) {
            return 'active';
        }
        return $s;
    }

    private function validateUniqueValue(string $field, string $value, ?int $excludeId = null): void {
        if ($value === '') {
            return;
        }

        $sql = "SELECT id FROM {$this->table} WHERE {$field} = :value";
        $params = [':value' => $value];

        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
            $params[':exclude_id'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        if ($stmt->fetchColumn() !== false) {
            throw new Exception($field . ' already exists');
        }
    }

    private function validateEmail(?string $email): void {
        if ($email === null || $email === '') {
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('email must be a valid email address');
        }
    }

    public function getActiveDrivers(): array {
        $stmt = $this->db->query(
            "SELECT id, driver_name, status FROM {$this->table} WHERE status = 'active' ORDER BY driver_name ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Used by dropdowns and modals
    public function listDrivers(?string $status = null): array {
        $params = [];
        $sql = "SELECT id, driver_name, employee_id, first_name, last_name, mobile_number, email, license_number, license_class, license_expiry, status FROM {$this->table}";

        $statusNorm = null;
        if ($status !== null && $status !== '') {
            $statusNorm = $this->normalizeStatus($status);
            $sql .= " WHERE status = :status";
            $params[':status'] = $statusNorm;
        }

        $sql .= " ORDER BY first_name ASC, last_name ASC, driver_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // JSON-friendly rows
    public function listAjax(): array {
        return $this->listDrivers();
    }

    public function create(array $data, int $actorUserId): int {
        $employeeId = trim((string)($data['employee_id'] ?? ''));
        $firstName = trim((string)($data['first_name'] ?? ''));
        $lastName = trim((string)($data['last_name'] ?? ''));
        $licenseNumber = trim((string)($data['license_number'] ?? ''));
        $licenseClass = trim((string)($data['license_class'] ?? ''));
        $licenseExpiry = trim((string)($data['license_expiry'] ?? ''));
        $mobileNumber = trim((string)($data['mobile_number'] ?? ''));
        $email = trim((string)($data['email'] ?? ''));
        $name = trim((string)($data['driver_name'] ?? ''));

        if ($employeeId === '') {
            throw new Exception('employee_id is required');
        }
        if ($firstName === '') {
            throw new Exception('first_name is required');
        }
        if ($lastName === '') {
            throw new Exception('last_name is required');
        }
        if ($licenseNumber === '') {
            throw new Exception('license_number is required');
        }
        if ($licenseClass === '') {
            throw new Exception('license_class is required');
        }
        if ($licenseExpiry === '') {
            throw new Exception('license_expiry is required');
        }
        if (strtotime($licenseExpiry) === false) {
            throw new Exception('license_expiry must be a valid date');
        }
        $this->validateUniqueValue('employee_id', $employeeId);
        $this->validateUniqueValue('license_number', $licenseNumber);
        $this->validateEmail($email !== '' ? $email : null);
        if ($name === '' && ($firstName !== '' || $lastName !== '')) {
            $name = trim($firstName . ' ' . $lastName);
        }

        $status = $this->normalizeStatus($data['status'] ?? 'active');

        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (
                driver_name,
                employee_id,
                first_name,
                last_name,
                mobile_number,
                email,
                license_number,
                license_class,
                license_expiry,
                status,
                created_at,
                updated_at
            ) VALUES (
                :driver_name,
                :employee_id,
                :first_name,
                :last_name,
                :mobile_number,
                :email,
                :license_number,
                :license_class,
                :license_expiry,
                :status,
                NOW(),
                NOW()
            )"
        );
        $stmt->execute([
            ':driver_name' => $name !== '' ? $name : null,
            ':employee_id' => $employeeId,
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':mobile_number' => $mobileNumber !== '' ? $mobileNumber : null,
            ':email' => $email !== '' ? $email : null,
            ':license_number' => $licenseNumber,
            ':license_class' => $licenseClass,
            ':license_expiry' => date('Y-m-d', strtotime($licenseExpiry)),
            ':status' => $status,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data, int $actorUserId): bool {
        $id = (int)$id;
        if ($id < 1) {
            throw new Exception('Invalid driver id');
        }

        $employeeId = trim((string)($data['employee_id'] ?? ''));
        $firstName = trim((string)($data['first_name'] ?? ''));
        $lastName = trim((string)($data['last_name'] ?? ''));
        $licenseNumber = trim((string)($data['license_number'] ?? ''));
        $licenseClass = trim((string)($data['license_class'] ?? ''));
        $licenseExpiry = trim((string)($data['license_expiry'] ?? ''));
        $mobileNumber = trim((string)($data['mobile_number'] ?? ''));
        $email = trim((string)($data['email'] ?? ''));
        $name = trim((string)($data['driver_name'] ?? ''));

        if ($employeeId === '') {
            throw new Exception('employee_id is required');
        }
        if ($firstName === '') {
            throw new Exception('first_name is required');
        }
        if ($lastName === '') {
            throw new Exception('last_name is required');
        }
        if ($licenseNumber === '') {
            throw new Exception('license_number is required');
        }
        if ($licenseClass === '') {
            throw new Exception('license_class is required');
        }
        if ($licenseExpiry === '') {
            throw new Exception('license_expiry is required');
        }
        if (strtotime($licenseExpiry) === false) {
            throw new Exception('license_expiry must be a valid date');
        }
        $this->validateUniqueValue('employee_id', $employeeId, $id);
        $this->validateUniqueValue('license_number', $licenseNumber, $id);
        $this->validateEmail($email !== '' ? $email : null);
        if ($name === '' && ($firstName !== '' || $lastName !== '')) {
            $name = trim($firstName . ' ' . $lastName);
        }

        $status = $this->normalizeStatus($data['status'] ?? 'active');

        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET driver_name = :driver_name,
                 employee_id = :employee_id,
                 first_name = :first_name,
                 last_name = :last_name,
                 mobile_number = :mobile_number,
                 email = :email,
                 license_number = :license_number,
                 license_class = :license_class,
                 license_expiry = :license_expiry,
                 status = :status,
                 updated_at = NOW()
             WHERE id = :id"
        );
        $stmt->execute([
            ':driver_name' => $name !== '' ? $name : null,
            ':employee_id' => $employeeId,
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':mobile_number' => $mobileNumber !== '' ? $mobileNumber : null,
            ':email' => $email !== '' ? $email : null,
            ':license_number' => $licenseNumber,
            ':license_class' => $licenseClass,
            ':license_expiry' => date('Y-m-d', strtotime($licenseExpiry)),
            ':status' => $status,
            ':id' => $id,
        ]);

        if ($stmt->rowCount() < 1) {
            // Might be unchanged values or id not found. Treat as not updated.
            return false;
        }
        return true;
    }

    // Soft delete via status = inactive
    public function delete(int $id): bool {
        $id = (int)$id;
        if ($id < 1) {
            throw new Exception('Invalid driver id');
        }

        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET status = 'inactive', updated_at = NOW()
             WHERE id = :id AND status <> 'inactive'"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}



