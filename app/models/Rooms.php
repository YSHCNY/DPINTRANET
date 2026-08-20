<?php
require_once __DIR__ . '/../core/Model.php';

class Rooms extends Model {
    private string $table = 'rooms';

    private function normalizeStatus(?string $status): string {
        $allowed = ['active', 'inactive'];
        $s = strtolower(trim((string)$status));
        if (!in_array($s, $allowed, true)) {
            return 'active';
        }
        return $s;
    }

    public function getActiveRooms(): array {
        $stmt = $this->db->query(
            "SELECT id, room_name, room_code, capacity, status FROM {$this->table} WHERE status = 'active' ORDER BY room_name ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countActiveRooms(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE status = 'active'");
        return (int)$stmt->fetchColumn();
    }

    public function listRooms(?string $status = null): array {
        $params = [];
        $sql = "SELECT id, room_name, room_code, capacity, status FROM {$this->table}";

        if ($status !== null && $status !== '') {
            $statusNorm = $this->normalizeStatus($status);
            $sql .= " WHERE status = :status";
            $params[':status'] = $statusNorm;
        }

        $sql .= " ORDER BY room_name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRoomById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT id, room_name, room_code, capacity, status FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function createRoom(array $data, int $actorUserId = 0): int {
        $name = trim((string)($data['room_name'] ?? ''));
        $code = trim((string)($data['room_code'] ?? ''));
        $capacity = max(1, (int)($data['capacity'] ?? 1));
        $status = $this->normalizeStatus($data['status'] ?? 'active');

        if ($name === '' || $code === '') {
            throw new Exception('Room name and code are required.');
        }

        $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE room_code = :room_code LIMIT 1");
        $stmt->execute([':room_code' => $code]);
        if ($stmt->fetchColumn()) {
            throw new Exception('Room code already exists. Choose a different code.');
        }

        $insert = $this->db->prepare(
            "INSERT INTO {$this->table} (room_name, room_code, capacity, status, created_at, updated_at)
             VALUES (:room_name, :room_code, :capacity, :status, NOW(), NOW())"
        );

        $insert->execute([
            ':room_name' => $name,
            ':room_code' => $code,
            ':capacity' => $capacity,
            ':status' => $status
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function updateRoom(int $id, array $data, int $actorUserId = 0): bool {
        $id = (int)$id;
        if ($id < 1) {
            throw new Exception('Invalid room id.');
        }

        $name = trim((string)($data['room_name'] ?? ''));
        $code = trim((string)($data['room_code'] ?? ''));
        $capacity = max(1, (int)($data['capacity'] ?? 1));
        $status = $this->normalizeStatus($data['status'] ?? 'active');

        if ($name === '' || $code === '') {
            throw new Exception('Room name and code are required.');
        }

        $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE room_code = :room_code AND id <> :id LIMIT 1");
        $stmt->execute([':room_code' => $code, ':id' => $id]);
        if ($stmt->fetchColumn()) {
            throw new Exception('Room code already exists. Choose a different code.');
        }

        $update = $this->db->prepare(
            "UPDATE {$this->table} SET room_name = :room_name, room_code = :room_code, capacity = :capacity, status = :status, updated_at = NOW() WHERE id = :id"
        );
        $update->execute([
            ':room_name' => $name,
            ':room_code' => $code,
            ':capacity' => $capacity,
            ':status' => $status,
            ':id' => $id,
        ]);

        return $update->rowCount() > 0;
    }

    public function deleteRoom(int $id, int $actorUserId = 0): bool {
        $id = (int)$id;
        if ($id < 1) {
            throw new Exception('Invalid room id.');
        }

        $update = $this->db->prepare("UPDATE {$this->table} SET status = 'inactive', updated_at = NOW() WHERE id = :id");
        $update->execute([':id' => $id]);
        return $update->rowCount() > 0;
    }
}
