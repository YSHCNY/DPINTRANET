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

    public function getActiveDrivers(): array {
        $stmt = $this->db->query(
            "SELECT id, driver_name, status FROM {$this->table} WHERE status = 'active' ORDER BY driver_name ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Used by dropdowns and modals
    public function listDrivers(?string $status = null): array {
        $params = [];
        $sql = "SELECT id, driver_name, status FROM {$this->table}";

        $statusNorm = null;
        if ($status !== null && $status !== '') {
            $statusNorm = $this->normalizeStatus($status);
            $sql .= " WHERE status = :status";
            $params[':status'] = $statusNorm;
        }

        $sql .= " ORDER BY driver_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // JSON-friendly rows
    public function listAjax(): array {
        return $this->listDrivers();
    }

    public function create(array $data, int $actorUserId): int {
        $name = trim((string)($data['driver_name'] ?? ''));
        if ($name === '') {
            throw new Exception('driver_name is required');
        }

        $status = $this->normalizeStatus($data['status'] ?? 'active');

        // Optional uniqueness: allow same name? keep permissive.

        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (driver_name, status, created_at, updated_at)
             VALUES (:driver_name, :status, NOW(), NOW())"
        );
        $stmt->execute([
            ':driver_name' => $name,
            ':status' => $status,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data, int $actorUserId): bool {
        $id = (int)$id;
        if ($id < 1) {
            throw new Exception('Invalid driver id');
        }

        $name = trim((string)($data['driver_name'] ?? ''));
        if ($name === '') {
            throw new Exception('driver_name is required');
        }

        $status = $this->normalizeStatus($data['status'] ?? 'active');

        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET driver_name = :driver_name, status = :status, updated_at = NOW()
             WHERE id = :id"
        );
        $stmt->execute([
            ':driver_name' => $name,
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



