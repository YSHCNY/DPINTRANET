<?php
require_once '../app/core/Model.php';

class CarVehicles extends Model {

    private string $table = 'car_vehicles';

    private function normalizeStatus(?string $status): string {
        $allowed = ['active', 'inactive'];
        $s = strtolower(trim((string)$status));
        if (!in_array($s, $allowed, true)) {
            return 'active';
        }
        return $s;
    }

    public function getActiveVehicles(): array {
        $stmt = $this->db->query(
            "SELECT id, vehicle_name, plate_number, capacity, status
             FROM {$this->table}
             WHERE status = 'active'
             ORDER BY vehicle_name ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Used by dropdowns/modals
    public function listVehicles(?string $status = null): array {
        $params = [];
        $sql = "SELECT id, vehicle_name, plate_number, capacity, status FROM {$this->table}";

        if ($status !== null && $status !== '') {
            $statusNorm = $this->normalizeStatus($status);
            $sql .= " WHERE status = :status";
            $params[':status'] = $statusNorm;
        }

        $sql .= " ORDER BY vehicle_name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createVehicle(array $data, int $actorUserId): int {
        $this->db->beginTransaction();
        try {
            $plate = trim((string)($data['plate_number'] ?? ''));
            $name = trim((string)($data['vehicle_name'] ?? ''));
            $capacity = (int)($data['capacity'] ?? 0);
            $status = $this->normalizeStatus($data['status'] ?? 'active');

            if ($plate === '' || $name === '' || $capacity < 1) {
                throw new Exception('Plate number, vehicle name, and capacity are required.');
            }

            // Unique plate validation
            $check = $this->db->prepare("SELECT id FROM {$this->table} WHERE plate_number = :plate LIMIT 1");
            $check->execute([':plate' => $plate]);
            if ($check->fetchColumn()) {
                throw new Exception('Plate number already exists.');
            }

            $stmt = $this->db->prepare(
                "INSERT INTO {$this->table}
                 (plate_number, vehicle_name, capacity, status, created_by, created_at, updated_at)
                 VALUES
                 (:plate, :name, :capacity, :status, :created_by, NOW(), NOW())"
            );

            $stmt->execute([
                ':plate' => $plate,
                ':name' => $name,
                ':capacity' => $capacity,
                ':status' => $status,
                ':created_by' => $actorUserId,
            ]);

            $id = (int)$this->db->lastInsertId();
            $this->db->commit();
            return $id;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateVehicle(int $id, array $data, int $actorUserId): bool {
        $id = (int)$id;
        if ($id < 1) {
            throw new Exception('Invalid vehicle id');
        }

        $plate = trim((string)($data['plate_number'] ?? ''));
        $name = trim((string)($data['vehicle_name'] ?? ''));
        $capacity = (int)($data['capacity'] ?? 0);
        $status = $this->normalizeStatus($data['status'] ?? 'active');

        if ($plate === '' || $name === '' || $capacity < 1) {
            throw new Exception('Plate number, vehicle name, and capacity are required.');
        }

        // Unique plate validation on update (allow same vehicle)
        $check = $this->db->prepare("SELECT id FROM {$this->table} WHERE plate_number = :plate AND id <> :id LIMIT 1");
        $check->execute([':plate' => $plate, ':id' => $id]);
        if ($check->fetchColumn()) {
            throw new Exception('Plate number already exists.');
        }

        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET plate_number = :plate,
                 vehicle_name = :name,
                 capacity = :capacity,
                 status = :status,
                 updated_at = NOW()
             WHERE id = :id"
        );

        $stmt->execute([
            ':plate' => $plate,
            ':name' => $name,
            ':capacity' => $capacity,
            ':status' => $status,
            ':id' => $id,
        ]);

        // Might be unchanged values. Treat no rows affected as false.
        return $stmt->rowCount() > 0;
    }

    // Soft delete via status = inactive
    public function disableVehicle(int $id, int $actorUserId): bool {
        $id = (int)$id;
        if ($id < 1) {
            throw new Exception('Invalid vehicle id');
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


