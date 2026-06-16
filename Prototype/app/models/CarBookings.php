<?php
require_once '../app/core/Model.php';

class CarBookings extends Model {

    private string $table = 'car_bookings';

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
            $title = $r['purpose'] ? $r['purpose'] : $vehicleLabel;

            $events[] = [
                'id' => (int)$r['id'],
                'title' => $title,
                'start' => $r['departure_expected'] ? $r['departure_expected'] : $r['date_trip'],
                // FullCalendar treats all-day `end` as exclusive, so we add +1 day to show the return date.
                'end' => ($r['return_expected'] ? date('Y-m-d', strtotime($r['return_expected'] . ' +1 day')) : date('Y-m-d', strtotime($r['date_trip'] . ' +1 day'))),
                'allDay' => true,

                'extendedProps' => [
                    'vehicle_name' => $r['vehicle_name'],
                    'plate_number' => $r['plate_number'],
                    'driver_name' => $r['driver_name'],
                    'passengers' => (int)$r['passengers'],
                    'remarks' => $r['remarks'],
                ],
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

        $start = $data['departure_expected'];
        $end = $data['return_expected'];

        if ($start === '' || $end === '' || $start > $end) {
            throw new Exception('Return expected date must be equal to or after departure expected date.');
        }

        // Create event span with end as inclusive day; FullCalendar treats end exclusive for allDay.
        // We store end_at as return_expected + 1 day to cover the return day.
        $endPlus = date('Y-m-d', strtotime($end . ' +1 day'));

        $this->db->beginTransaction();
        try {
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
                ':departure_expected' => $data['departure_expected'],
                ':return_expected' => $data['return_expected'],
                ':special_instructions' => $data['special_instructions'],
                ':vehicle_id' => (int)$data['vehicle_id'],
                ':driver_id' => (int)$data['driver_id'],
                ':remarks' => $data['remarks'],
                ':created_by' => $actorUserId,
                ':start_at' => $data['departure_expected'] . ' 00:00:00',
                ':end_at' => $endPlus . ' 00:00:00',
            ]);

            $id = (int)$this->db->lastInsertId();
            $this->db->commit();
            return $id;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}

