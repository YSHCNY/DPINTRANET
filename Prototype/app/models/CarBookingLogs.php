<?php
require_once '../app/core/Model.php';

class CarBookingLogs extends Model {
    private string $table = 'car_booking_logs';

    public function log(int $bookingId, int $actorUserId, string $action, array $payload = []): void {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (booking_id, actor_user_id, action, payload_json, created_at)
             VALUES (:booking_id, :actor_user_id, :action, :payload_json, NOW())"
        );

        $stmt->execute([
            ':booking_id' => $bookingId,
            ':actor_user_id' => $actorUserId,
            ':action' => $action,
            ':payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);
    }
}

