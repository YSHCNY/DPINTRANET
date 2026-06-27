<?php
require_once '../app/core/Model.php';

class RoomBookings extends Model {
    private string $table = 'room_bookings';

    public function getRoomBookingHistory(int $roomId, int $limit = 10): array {
        $sql = "SELECT b.id,
                       b.date_trip,
                       b.purpose,
                       b.attendees,
                       b.departure_expected,
                       b.return_expected,
                       b.status,
                       b.created_at,
                       r.room_name,
                       r.room_code,
                       r.capacity
                FROM {$this->table} b
                INNER JOIN rooms r ON r.id = b.room_id
                WHERE b.room_id = :roomId
                  AND b.status = 'scheduled'
                ORDER BY b.departure_expected ASC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':roomId', $roomId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countScheduledBookings(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE status = 'scheduled'");
        return (int)$stmt->fetchColumn();
    }

    public function getBookingStatus(string $startDate, string $endDate): string {
        $now = time();
        $startTs = strtotime($startDate);
        $endTs = strtotime($endDate);
        if ($startTs === false || $endTs === false) {
            return 'pending';
        }
        if ($now < $startTs) return 'pending';
        if ($now > $endTs) return 'finished';
        return 'ongoing';
    }

    private function statusToCalendarColor(string $status): array {
        switch ($status) {
            case 'pending':
                return ['bg' => '#f59e0b', 'border' => '#d97706'];
            case 'ongoing':
                return ['bg' => '#2563eb', 'border' => '#1d4ed8'];
            case 'finished':
                return ['bg' => '#10b981', 'border' => '#059669'];
            default:
                return ['bg' => '#64748b', 'border' => '#475569'];
        }
    }

    private function normalizeDateValue(string $value): string {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        $timestamp = strtotime(str_replace('T', ' ', $value));
        if ($timestamp === false) {
            return '';
        }
        return date('Y-m-d', $timestamp);
    }

    public function getCalendarEvents(string $start, string $end, ?int $roomId = null): array {
        $startDt = $start ?: date('c');
        $endDt = $end ?: date('c');

        $sql = "SELECT b.id,
                       b.date_trip,
                       b.purpose,
                       b.attendees,
                       b.departure_expected,
                       b.return_expected,
                       b.room_id,
                       r.room_name,
                       r.room_code
                FROM {$this->table} b
                INNER JOIN rooms r ON r.id = b.room_id
                WHERE b.status = 'scheduled'
                  AND b.start_at < :endDt
                  AND b.end_at > :startDt";

        $params = [
            ':startDt' => $startDt,
            ':endDt' => $endDt,
        ];

        if ($roomId) {
            $sql .= " AND b.room_id = :roomId";
            $params[':roomId'] = $roomId;
        }

        $sql .= " ORDER BY b.start_at ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $events = [];
        foreach ($rows as $r) {
            $title = ($r['purpose'] ? $r['purpose'] . ' - ' . ($r['room_name'] ?? '') : ($r['room_name'] ?? 'Room booking'));
            $status = $this->getBookingStatus((string)$r['departure_expected'], (string)$r['return_expected']);
            $colors = $this->statusToCalendarColor($status);
            $events[] = [
                'id' => (int)$r['id'],
                'title' => $title,
                'start' => $r['departure_expected'],
                'end' => $r['return_expected'],
                'allDay' => false,
                'backgroundColor' => $colors['bg'],
                'borderColor' => $colors['border'],
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'room_name' => $r['room_name'],
                    'room_code' => $r['room_code'],
                    'attendees' => (int)$r['attendees'],
                    'purpose' => $r['purpose'],
                    'status' => $status,
                ],
            ];
        }

        return $events;
    }

    public function createBooking(array $data, int $actorUserId): int {
        $roomId = (int)($data['room_id'] ?? 0);
        if ($roomId < 1) {
            throw new Exception('Please select a room.');
        }

        $roomStmt = $this->db->prepare('SELECT id, room_name, capacity, status FROM rooms WHERE id = :id LIMIT 1');
        $roomStmt->execute([':id' => $roomId]);
        $room = $roomStmt->fetch(PDO::FETCH_ASSOC);
        if (!$room || $room['status'] !== 'active') {
            throw new Exception('Selected room is not active or does not exist.');
        }

        $dateTrip = trim((string)($data['date_trip'] ?? ''));
        $dateRequested = trim((string)($data['date_requested'] ?? ''));
        $start = trim((string)($data['departure_expected'] ?? ''));
        $end = trim((string)($data['return_expected'] ?? ''));
        $attendees = max(0, (int)($data['attendees'] ?? 0));

        $dateTripNorm = $this->normalizeDateValue($dateTrip);
        $dateRequestedNorm = $this->normalizeDateValue($dateRequested);
        if ($dateTripNorm === '' || $dateRequestedNorm === '') {
            throw new Exception('[DATE_INVALID] Trip date and request date must be valid dates.');
        }

        $startNorm = str_replace('T', ' ', $start);
        $endNorm = str_replace('T', ' ', $end);
        $startTs = strtotime($startNorm);
        $endTs = strtotime($endNorm);

        if ($startNorm === '' || $endNorm === '' || $startTs === false || $endTs === false || $startTs > $endTs) {
            throw new Exception('[TIME_INVALID] Return expected datetime must be equal to or after departure expected datetime.');
        }

        $todayMidnight = strtotime(date('Y-m-d 00:00:00'));
        if ($startTs < $todayMidnight) {
            throw new Exception('[PAST_DEPARTURE] Departure datetime cannot be in the past.');
        }

        if ($attendees < 1) {
            throw new Exception('Attendee count must be at least 1.');
        }

        if ($attendees > (int)$room['capacity']) {
            throw new Exception('[CAPACITY_EXCEEDED] The room capacity is ' . (int)$room['capacity'] . ' guests.');
        }

        $startAt = date('Y-m-d H:i:s', $startTs);
        $endAt = date('Y-m-d H:i:s', $endTs);

        $this->db->beginTransaction();
        try {
            $conflictSql = "SELECT id FROM {$this->table}
                 WHERE room_id = :roomId
                   AND status = 'scheduled'
                   AND id <> :excludeId
                   AND (start_at < :endDt AND end_at > :startDt)
                 LIMIT 1";

            $conflictStmt = $this->db->prepare($conflictSql);
            $conflictStmt->execute([
                ':roomId' => $roomId,
                ':excludeId' => 0,
                ':startDt' => $startAt,
                ':endDt' => $endAt,
            ]);
            if ($conflictStmt->fetchColumn()) {
                throw new Exception('[ROOM_CONFLICT] This room is already booked for the selected period.');
            }

            $stmt = $this->db->prepare(
                "INSERT INTO {$this->table}
                 (date_trip, date_requested, purpose, attendees,
                  departure_expected, return_expected, special_instructions, remarks,
                  room_id, status, created_by, created_at, updated_at,
                  start_at, end_at)
                 VALUES
                 (:date_trip, :date_requested, :purpose, :attendees,
                  :departure_expected, :return_expected, :special_instructions, :remarks,
                  :room_id, 'scheduled', :created_by, NOW(), NOW(),
                  :start_at, :end_at)"
            );

            $stmt->execute([
                ':date_trip' => $dateTripNorm,
                ':date_requested' => $dateRequestedNorm,
                ':purpose' => $data['purpose'],
                ':attendees' => $attendees,
                ':departure_expected' => $startAt,
                ':return_expected' => $endAt,
                ':special_instructions' => $data['special_instructions'] ?? null,
                ':remarks' => $data['remarks'] ?? null,
                ':room_id' => $roomId,
                ':created_by' => $actorUserId,
                ':start_at' => $startAt,
                ':end_at' => $endAt,
            ]);

            $id = (int)$this->db->lastInsertId();
            $this->db->commit();
            return $id;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getBookingById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT b.*, r.room_name, r.room_code, r.capacity
            FROM {$this->table} b
            LEFT JOIN rooms r ON r.id = b.room_id
            WHERE b.id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function updateBooking(int $id, array $data, int $actorUserId): bool {
        $id = (int)$id;
        if ($id < 1) {
            throw new Exception('Invalid booking id');
        }

        $existing = $this->getBookingById($id);
        if (!$existing) {
            throw new Exception('Booking not found');
        }

        $roomId = isset($data['room_id']) ? (int)$data['room_id'] : (int)$existing['room_id'];
        if ($roomId < 1) {
            throw new Exception('Please select a room.');
        }

        $roomStmt = $this->db->prepare('SELECT id, room_name, capacity, status FROM rooms WHERE id = :id LIMIT 1');
        $roomStmt->execute([':id' => $roomId]);
        $room = $roomStmt->fetch(PDO::FETCH_ASSOC);
        if (!$room || $room['status'] !== 'active') {
            throw new Exception('Selected room is not active or does not exist.');
        }

        $dateTrip = trim((string)($data['date_trip'] ?? $existing['date_trip']));
        $dateRequested = trim((string)($data['date_requested'] ?? $existing['date_requested']));
        $start = isset($data['departure_expected']) ? trim((string)$data['departure_expected']) : $existing['departure_expected'];
        $end = isset($data['return_expected']) ? trim((string)$data['return_expected']) : $existing['return_expected'];
        $attendees = isset($data['attendees']) ? max(0, (int)$data['attendees']) : (int)$existing['attendees'];

        $dateTripNorm = $this->normalizeDateValue($dateTrip);
        $dateRequestedNorm = $this->normalizeDateValue($dateRequested);
        if ($dateTripNorm === '' || $dateRequestedNorm === '') {
            throw new Exception('[DATE_INVALID] Trip date and request date must be valid dates.');
        }

        $startNorm = str_replace('T', ' ', $start);
        $endNorm = str_replace('T', ' ', $end);
        $startTs = strtotime($startNorm);
        $endTs = strtotime($endNorm);

        if ($startNorm === '' || $endNorm === '' || $startTs === false || $endTs === false || $startTs > $endTs) {
            throw new Exception('[TIME_INVALID] Return expected datetime must be equal to or after departure expected datetime.');
        }

        $todayMidnight = strtotime(date('Y-m-d 00:00:00'));
        if ($startTs < $todayMidnight) {
            throw new Exception('[PAST_DEPARTURE] Departure datetime cannot be in the past.');
        }

        if ($attendees < 1) {
            throw new Exception('Attendee count must be at least 1.');
        }

        if ($attendees > (int)$room['capacity']) {
            throw new Exception('[CAPACITY_EXCEEDED] The room capacity is ' . (int)$room['capacity'] . ' guests.');
        }

        $startAt = date('Y-m-d H:i:s', $startTs);
        $endAt = date('Y-m-d H:i:s', $endTs);

        $this->db->beginTransaction();
        try {
            $conflictSql = "SELECT id FROM {$this->table}
                 WHERE room_id = :roomId
                   AND status = 'scheduled'
                   AND id <> :id
                   AND (start_at < :endDt AND end_at > :startDt)
                 LIMIT 1";
            $conflictStmt = $this->db->prepare($conflictSql);
            $conflictStmt->execute([
                ':roomId' => $roomId,
                ':id' => $id,
                ':startDt' => $startAt,
                ':endDt' => $endAt,
            ]);
            if ($conflictStmt->fetchColumn()) {
                throw new Exception('[ROOM_CONFLICT] This room is already booked for the selected period.');
            }

            $stmt = $this->db->prepare(
                "UPDATE {$this->table} SET
                   date_trip = :date_trip,
                   date_requested = :date_requested,
                   purpose = :purpose,
                   attendees = :attendees,
                   departure_expected = :departure_expected,
                   return_expected = :return_expected,
                   special_instructions = :special_instructions,
                   remarks = :remarks,
                   room_id = :room_id,
                   start_at = :start_at,
                   end_at = :end_at,
                   updated_at = NOW()
                 WHERE id = :id"
            );

            $stmt->execute([
                ':date_trip' => $dateTripNorm,
                ':date_requested' => $dateRequestedNorm,
                ':purpose' => $data['purpose'] ?? $existing['purpose'],
                ':attendees' => $attendees,
                ':departure_expected' => $startAt,
                ':return_expected' => $endAt,
                ':special_instructions' => $data['special_instructions'] ?? $existing['special_instructions'],
                ':remarks' => $data['remarks'] ?? $existing['remarks'],
                ':room_id' => $roomId,
                ':start_at' => $startAt,
                ':end_at' => $endAt,
                ':id' => $id,
            ]);

            $ok = $stmt->rowCount() > 0;
            $this->db->commit();
            return $ok;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function deleteBooking(int $id, int $actorUserId): bool {
        $id = (int)$id;
        if ($id < 1) {
            throw new Exception('Invalid booking id');
        }

        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = 'cancelled', updated_at = NOW() WHERE id = :id AND status <> 'cancelled'");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
