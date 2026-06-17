<?php
require_once '../app/core/Model.php';

class CarBookings extends Model {

    private string $table = 'car_bookings';

    public function getVehicleBookingHistory(int $vehicleId, int $limit = 10): array {
        $sql = "SELECT b.id,
                       b.date_trip,
                       b.purpose,
                       b.passengers,
                       b.departure_expected,
                       b.return_expected,
                       b.destinations,
                       b.driver_id,
                       d.driver_name,
                       b.status,
                       b.created_at
                FROM {$this->table} b
                INNER JOIN car_drivers d ON d.id = b.driver_id
                WHERE b.vehicle_id = :vehicleId
                ORDER BY b.departure_expected DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':vehicleId', $vehicleId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBookingStatus(string $departureDate, string $returnDate): string {
        $now = time();
        $startTs = strtotime($departureDate);
        $endTs = strtotime($returnDate);
        if ($startTs === false || $endTs === false) {
            return 'pending';
        }
        if ($now < $startTs) return 'pending';
        if ($now > $endTs) return 'finished';
        return 'ongoing';
    }

    private function statusToCalendarColor(string $status): array {
        // Match the vehicle badge mapping:
        // pending=amber, ongoing=blue, finished=emerald
        switch ($status) {
            case 'pending':
                return ['bg' => '#f59e0b', 'border' => '#d97706']; // amber-500 / amber-600
            case 'ongoing':
                return ['bg' => '#2563eb', 'border' => '#1d4ed8']; // blue-600 / blue-700
            case 'finished':
                return ['bg' => '#10b981', 'border' => '#059669']; // emerald-500 / emerald-600
            default:
                return ['bg' => '#64748b', 'border' => '#475569']; // slate
        }
    }


    public function getCalendarEvents(string $start, string $end, ?int $vehicleId = null, ?int $driverId = null): array {
        // FullCalendar passes ISO strings; use as DATETIME boundaries.
        $startDt = $start ?: date('c');
        $endDt = $end ?: date('c');

        $sql = "SELECT b.id,
                       b.date_trip,
                       b.purpose,
                       b.passengers,
                       b.departure_expected,
                       b.return_expected,
                       b.vehicle_id,
                       v.vehicle_name,
                       v.plate_number,
                       b.driver_id,
                       d.driver_name,
                       b.remarks
                FROM {$this->table} b
                INNER JOIN car_vehicles v ON v.id = b.vehicle_id
                INNER JOIN car_drivers d ON d.id = b.driver_id
                WHERE b.status = 'scheduled'
                  AND b.start_at < :endDt
                  AND b.end_at > :startDt";

        $params = [
            ':startDt' => $startDt,
            ':endDt' => $endDt,
        ];

        if ($vehicleId) {
            $sql .= " AND b.vehicle_id = :vehicleId";
            $params[':vehicleId'] = $vehicleId;
        }

        if ($driverId) {
            $sql .= " AND b.driver_id = :driverId";
            $params[':driverId'] = $driverId;
        }

        $sql .= " ORDER BY b.start_at ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Map to FullCalendar event objects
        $events = [];
        foreach ($rows as $r) {
            $vehicleLabel = $r['vehicle_name'] . ' (' . $r['plate_number'] . ')';
            $title = $r['purpose'] ? $r['purpose'] . ' - ' . $vehicleLabel : $vehicleLabel;

            $departureExpected = $r['departure_expected'] ?? ($r['start_at'] ?? null);
            $returnExpected = $r['return_expected'] ?? ($r['end_at'] ?? null);

            $status = $this->getBookingStatus((string)$departureExpected, (string)$returnExpected);

            $events[] = [
                'id' => (int)$r['id'],
                'title' => $title,
                'start' => $r['start_at'] ?? ($r['departure_expected'] ?? $r['date_trip']),
                'end' => $r['end_at'] ?? ($r['return_expected'] ?? $r['date_trip']),
                'allDay' => false,

                'extendedProps' => [
                    'vehicle_name' => $r['vehicle_name'],
                    'plate_number' => $r['plate_number'],
                    'driver_name' => $r['driver_name'],
                    'passengers' => (int)$r['passengers'],
                    'remarks' => $r['remarks'],

                    'departure_expected' => $departureExpected,
                    'return_expected' => $returnExpected,
                    'status' => $status,
                ],

                // FullCalendar will use these per-event
                'backgroundColor' => $this->statusToCalendarColor($status)['bg'],
                'borderColor' => $this->statusToCalendarColor($status)['border'],
                'textColor' => '#ffffff',
            ];
        }

        return $events;
    }

    public function createBooking(array $data, int $actorUserId): int {
        // Ensure driver & vehicle exist
        $checkV = $this->db->prepare('SELECT id FROM car_vehicles WHERE id = :id AND status = "active" LIMIT 1');
        $checkV->execute([':id' => $data['vehicle_id']]);
        if (!$checkV->fetchColumn()) {
            throw new Exception('Selected vehicle is not active or does not exist.');
        }

        $checkD = $this->db->prepare('SELECT id FROM car_drivers WHERE id = :id AND status = "active" LIMIT 1');
        $checkD->execute([':id' => $data['driver_id']]);
        if (!$checkD->fetchColumn()) {
            throw new Exception('Selected driver is not active or does not exist.');
        }

        $start = trim((string)($data['departure_expected'] ?? ''));
        $end = trim((string)($data['return_expected'] ?? ''));

        // accept datetime-local 'YYYY-MM-DDTHH:MM'
        $startNorm = str_replace('T', ' ', $start);
        $endNorm = str_replace('T', ' ', $end);

        $startTs = strtotime($startNorm);
        $endTs = strtotime($endNorm);

        if ($startNorm === '' || $endNorm === '' || $startTs === false || $endTs === false || $startTs > $endTs) {
            throw new Exception('[TIME_INVALID] Return expected datetime must be equal to or after departure expected datetime.');
        }

        // Allow bookings starting today (time-aware)
        $todayMidnight = strtotime(date('Y-m-d 00:00:00'));
        if ($startTs < $todayMidnight) {
            throw new Exception('[PAST_DEPARTURE] Departure datetime cannot be in the past.');
        }

        $startAt = date('Y-m-d H:i:s', $startTs);
        $endAt = date('Y-m-d H:i:s', $endTs);

        $this->db->beginTransaction();
        try {
            // === Vehicle Conflict Check ===
            $conflictCheck = $this->db->prepare(
                "SELECT id FROM {$this->table}
                 WHERE vehicle_id = :vehicleId
                 AND status = 'scheduled'
                 AND id <> :excludeId
                 AND ((start_at <= :startDt AND end_at > :startDt)
                   OR (start_at < :endDt AND end_at >= :endDt)
                   OR (start_at >= :startDt AND end_at <= :endDt))
                 LIMIT 1"
            );
            $conflictCheck->execute([
                ':vehicleId' => $data['vehicle_id'],
                ':excludeId' => 0,           // not needed for create
                ':startDt'   => $startAt,
                ':endDt'     => $endAt,
            ]);
            if ($conflictCheck->fetchColumn()) {
                // marker used by controller to classify error
                throw new Exception('[VEHICLE_CONFLICT] This vehicle is already booked for the selected period.');
            }

            // === Driver Conflict Check ===
            $driverConflictCheck = $this->db->prepare(
                "SELECT id FROM {$this->table}
                 WHERE driver_id = :driverId
                 AND status = 'scheduled'
                 AND id <> :excludeId
                 AND (
                    (start_at < :endDt AND end_at > :startDt)
                 )
                 LIMIT 1"
            );
            $driverConflictCheck->execute([
                ':driverId'  => $data['driver_id'],
                ':excludeId' => 0,
                ':startDt'   => $startAt,
                ':endDt'     => $endAt,
            ]);
            if ($driverConflictCheck->fetchColumn()) {
                // marker used by controller to classify error
                throw new Exception('[DRIVER_CONFLICT] This driver is already booked for the selected period.');
            }

            // Insert booking
            $stmt = $this->db->prepare(
                "INSERT INTO {$this->table}
                (date_trip, date_requested, destinations, purpose, passengers,
                 departure_expected, return_expected, special_instructions,
                 vehicle_id, driver_id, remarks,
                 status, created_by, created_at, updated_at,
                 start_at, end_at)
                 VALUES
                (:date_trip, :date_requested, :destinations, :purpose, :passengers,
                 :departure_expected, :return_expected, :special_instructions,
                 :vehicle_id, :driver_id, :remarks,
                 'scheduled', :created_by, NOW(), NOW(),
                 :start_at, :end_at)"
            );

            $stmt->execute([
                ':date_trip' => $data['date_trip'],
                ':date_requested' => $data['date_requested'],
                ':destinations' => $data['destinations'],
                ':purpose' => $data['purpose'],
                ':passengers' => (int)$data['passengers'],
                ':departure_expected' => $startAt,
                ':return_expected' => $endAt,
                ':special_instructions' => $data['special_instructions'],
                ':vehicle_id' => (int)$data['vehicle_id'],
                ':driver_id' => (int)$data['driver_id'],
                ':remarks' => $data['remarks'],
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

    // Get booking by id
    public function getBookingById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // Update booking (DB)
   public function updateBooking(int $id, array $data, int $actorUserId): bool {
        $id = (int)$id;
        if ($id < 1) throw new Exception('Invalid booking id');

        $existing = $this->getBookingById($id);
        if (!$existing) throw new Exception('Booking not found');

        $start = isset($data['departure_expected']) ? trim((string)$data['departure_expected']) : $existing['departure_expected'];
        $end = isset($data['return_expected']) ? trim((string)$data['return_expected']) : $existing['return_expected'];

        $startNorm = str_replace('T', ' ', $start);
        $endNorm = str_replace('T', ' ', $end);

        $startTs = strtotime($startNorm);
        $endTs = strtotime($endNorm);

        if ($startNorm === '' || $endNorm === '' || $startTs === false || $endTs === false || $startTs > $endTs) {
            throw new Exception('[TIME_INVALID] Return expected datetime must be equal to or after departure expected datetime.');
        }

        // FIXED: Allow today
        $todayMidnight = strtotime(date('Y-m-d 00:00:00'));
        if ($startTs < $todayMidnight) {
            throw new Exception('[PAST_DEPARTURE] Departure datetime cannot be in the past.');
        }

        $startAt = date('Y-m-d H:i:s', $startTs);
        $endAt = date('Y-m-d H:i:s', $endTs);

        $vehicleId = isset($data['vehicle_id']) ? (int)$data['vehicle_id'] : (int)$existing['vehicle_id'];
        $driverId  = isset($data['driver_id'])  ? (int)$data['driver_id']  : (int)$existing['driver_id'];

        $this->db->beginTransaction();
        try {
            // === Vehicle Conflict Check ===
            $conflictCheck = $this->db->prepare(
                "SELECT id FROM {$this->table}
                 WHERE vehicle_id = :vehicleId
                 AND status = 'scheduled'
                 AND id <> :id
                 AND ((start_at <= :startDt AND end_at > :startDt)
                   OR (start_at < :endDt AND end_at >= :endDt)
                   OR (start_at >= :startDt AND end_at <= :endDt))
                 LIMIT 1"
            );
            $conflictCheck->execute([
                ':vehicleId' => $vehicleId,
                ':id'        => $id,
                ':startDt'   => $startAt,
                ':endDt'     => $endAt,
            ]);
            if ($conflictCheck->fetchColumn()) {
                // marker used by controller to classify error
                throw new Exception('[VEHICLE_CONFLICT] This vehicle is already booked for the selected period.');
            }

            // === Driver Conflict Check ===
            $driverConflictCheck = $this->db->prepare(
                "SELECT id FROM {$this->table}
                 WHERE driver_id = :driverId
                 AND status = 'scheduled'
                 AND id <> :id
                 AND (
                    (start_at < :endDt AND end_at > :startDt)
                 )
                 LIMIT 1"
            );
            $driverConflictCheck->execute([
                ':driverId' => $driverId,
                ':id'       => $id,
                ':startDt'  => $startAt,
                ':endDt'    => $endAt,
            ]);
            if ($driverConflictCheck->fetchColumn()) {
                // marker used by controller to classify error
                throw new Exception('[DRIVER_CONFLICT] This driver is already booked for the selected period.');
            }

            // Update booking
            $stmt = $this->db->prepare(
                "UPDATE {$this->table} SET
                   date_trip = :date_trip,
                   date_requested = :date_requested,
                   destinations = :destinations,
                   purpose = :purpose,
                   passengers = :passengers,
                   departure_expected = :departure_expected,
                   return_expected = :return_expected,
                   special_instructions = :special_instructions,
                   vehicle_id = :vehicle_id,
                   driver_id = :driver_id,
                   remarks = :remarks,
                   start_at = :start_at,
                   end_at = :end_at,
                   updated_at = NOW()
                 WHERE id = :id"
            );

            $stmt->execute([
                ':date_trip' => $data['date_trip'] ?? $existing['date_trip'],
                ':date_requested' => $data['date_requested'] ?? $existing['date_requested'],
                ':destinations' => $data['destinations'] ?? $existing['destinations'],
                ':purpose' => $data['purpose'] ?? $existing['purpose'],
                ':passengers' => isset($data['passengers']) ? (int)$data['passengers'] : (int)$existing['passengers'],
                ':departure_expected' => $startAt,
                ':return_expected' => $endAt,
                ':special_instructions' => $data['special_instructions'] ?? $existing['special_instructions'],
                ':vehicle_id' => $vehicleId,
                ':driver_id' => $driverId,
                ':remarks' => $data['remarks'] ?? $existing['remarks'],
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

    // Soft-delete (cancel) booking
    public function deleteBooking(int $id, int $actorUserId): bool {
        $id = (int)$id;
        if ($id < 1) throw new Exception('Invalid booking id');

        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = 'cancelled', updated_at = NOW() WHERE id = :id AND status <> 'cancelled'");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    // -----------------------------
    // JSON file store fallback (storage/carbooking/bookings.json)
    // -----------------------------
    private function jsonFilePath(): string {
        return realpath(__DIR__ . '/..') . '/storage/carbooking/bookings.json';
    }

    private function readJsonStore(): array {
        $path = $this->jsonFilePath();
        if (!file_exists($path)) {
            return ['vehicles' => [], 'bookings' => [], 'nextBookingId' => 1];
        }

        $raw = @file_get_contents($path);
        if ($raw === false) {
            throw new Exception('Unable to read bookings.json');
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            throw new Exception('Malformed bookings.json');
        }
        // normalize
        $data['vehicles'] = $data['vehicles'] ?? [];
        $data['bookings'] = $data['bookings'] ?? [];
        $data['nextBookingId'] = $data['nextBookingId'] ?? 1;
        return $data;
    }

    private function writeJsonStore(array $data): void {
        $path = $this->jsonFilePath();
        $dir = dirname($path);
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0755, true)) {
                throw new Exception('Unable to create storage directory for bookings.json');
            }
        }

        $tmp = $path . '.' . uniqid('tmp', true);
        $ok = file_put_contents($tmp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        if ($ok === false) {
            @unlink($tmp);
            throw new Exception('Failed to write bookings.json');
        }
        if (!@rename($tmp, $path)) {
            @unlink($tmp);
            throw new Exception('Failed to move temporary bookings.json');
        }
    }

    // JSON CRUD: list
    public function listBookingsJson(): array {
        $data = $this->readJsonStore();
        return $data['bookings'];
    }

    // JSON CRUD: create
    public function createBookingJson(array $payload, int $actorUserId): int {
        $data = $this->readJsonStore();
        $id = (int)$data['nextBookingId'];

        $now = date('c');
        $booking = array_merge([
            'id' => $id,
            'vehicle_id' => $payload['vehicle_id'] ?? 0,
            'vehicle_name' => $payload['vehicle_name'] ?? '',
            'seat_count' => $payload['passengers'] ?? 0,
            'title' => $payload['purpose'] ?? '',
            'purpose' => $payload['purpose'] ?? '',
            'driver_name' => $payload['driver_name'] ?? '',
            'driver_contact' => $payload['driver_contact'] ?? '',
            'start_at' => $payload['departure_expected'] ?? '',
            'end_at' => $payload['return_expected'] ?? '',
            'notes' => $payload['remarks'] ?? '',
            'accent' => $payload['accent'] ?? '#000000',
            'created_at' => $now,
            'updated_at' => $now,
        ], $payload);

        $data['bookings'][] = $booking;
        $data['nextBookingId'] = $id + 1;
        $this->writeJsonStore($data);
        return $id;
    }

    // JSON CRUD: update
    public function updateBookingJson(int $id, array $payload, int $actorUserId): bool {
        $data = $this->readJsonStore();
        $found = false;
        foreach ($data['bookings'] as &$b) {
            if ((int)$b['id'] === $id) {
                // merge allowed fields
                foreach ($payload as $k => $v) {
                    if ($k === 'id') continue;
                    $b[$k] = $v;
                }
                $b['updated_at'] = date('c');
                $found = true;
                break;
            }
        }
        unset($b);

        if (!$found) return false;
        $this->writeJsonStore($data);
        return true;
    }

    // JSON CRUD: delete
    public function deleteBookingJson(int $id, int $actorUserId): bool {
        $data = $this->readJsonStore();
        $orig = count($data['bookings']);
        $data['bookings'] = array_values(array_filter($data['bookings'], function ($b) use ($id) {
            return (int)$b['id'] !== $id;
        }));
        if (count($data['bookings']) === $orig) return false;
        $this->writeJsonStore($data);
        return true;
    }
}
