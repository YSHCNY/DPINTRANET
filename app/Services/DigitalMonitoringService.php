<?php

require_once __DIR__ . '/../models/CarVehicles.php';
require_once __DIR__ . '/../models/CarBookings.php';
require_once __DIR__ . '/../models/CarDrivers.php';
require_once __DIR__ . '/../models/Rooms.php';
require_once __DIR__ . '/../models/RoomBookings.php';
require_once __DIR__ . '/../Services/EmployeeMobilizationService.php';

class DigitalMonitoringService
{
    private const DISPLAY_CAPACITY = 6;
    private const VEHICLE_DISPLAY_CAPACITY = 7;
    private const ROOM_DISPLAY_CAPACITY = 7;

    private CarVehicles $vehiclesModel;
    private CarBookings $carBookingsModel;
    private CarDrivers $driversModel;
    private Rooms $roomsModel;
    private RoomBookings $roomBookingsModel;
    private \App\Services\EmployeeMobilizationService $mobilizationService;

    public function __construct()
    {
        $this->vehiclesModel = new CarVehicles();
        $this->carBookingsModel = new CarBookings();
        $this->driversModel = new CarDrivers();
        $this->roomsModel = new Rooms();
        $this->roomBookingsModel = new RoomBookings();
        $this->mobilizationService = new \App\Services\EmployeeMobilizationService();
    }

    public function getSnapshot(): array
    {
        return [
            'updatedAt' => 'Current system data',
            'vehicles' => $this->getVehicles(),
            'rooms' => $this->getRooms(),
            'workforce' => $this->getWorkforce(),
        ];
    }

    public function getVehicles(): array
    {
        $vehicles = $this->vehiclesModel->getActiveVehicles();
        $occupied = $this->indexBy($this->carBookingsModel->getMonitoringBookings('occupied'), 'vehicle_id');
        $upcoming = $this->indexBy($this->carBookingsModel->getMonitoringBookings('upcoming'), 'vehicle_id');
        $drivers = $this->indexBy($this->driversModel->getActiveDrivers(), 'id');
        $monthStart = date('Y-m-01 00:00:00');
        $monthEnd = date('Y-m-t 23:59:59');
        $monthSchedule = $this->carBookingsModel->getCalendarEvents($monthStart, $monthEnd);

        $items = [];
        foreach ($vehicles as $vehicle) {
            $vehicleId = (int)($vehicle['id'] ?? 0);
            $booking = $occupied[$vehicleId] ?? $upcoming[$vehicleId] ?? null;
            $isOccupied = isset($occupied[$vehicleId]);
            $status = $isOccupied ? 'On trip' : (isset($upcoming[$vehicleId]) ? 'Upcoming' : 'Available');
            $driverId = (int)($booking['driver_id'] ?? 0);
            $driver = trim((string)($booking['driver_name'] ?? ''))
                ?: ($drivers[$driverId]['driver_name'] ?? 'Unassigned');

            $items[] = [
                'vehicle_id' => $vehicleId,
                'vehicle_name' => (string)($vehicle['vehicle_name'] ?? 'Vehicle'),
                'plate_number' => trim((string)($vehicle['plate_number'] ?? '')) ?: 'Unassigned plate',
                'driver' => $driver,
                'time_range' => $this->formatTimeRange($booking['start_at'] ?? null, $booking['end_at'] ?? null),
                'context' => (string)($booking['destinations'] ?? $booking['purpose'] ?? ($status === 'Available' ? 'Ready for assignment' : 'Operational booking')),
                'status' => $status,
            ];
        }
        $items = $this->prioritizeStatuses($items, ['On trip', 'Upcoming', 'Available']);

        $display = $this->buildDisplayBuckets($items, ['On trip', 'In use', 'Mobilizing', 'Demobilizing'], self::VEHICLE_DISPLAY_CAPACITY);

        return [
            'summary' => $this->summarize($items, [
                'Available' => ['tone' => 'emerald', 'label' => 'Available'],
                'On trip' => ['tone' => 'amber', 'label' => 'On trip'],
                'Upcoming' => ['tone' => 'sky', 'label' => 'Upcoming'],
            ]),
            'items' => $items,
            'display' => $display,
            'schedule' => $monthSchedule,
            'scheduleMonth' => date('Y-m'),
        ];
    }

    public function getRooms(): array
    {
        $rooms = $this->roomsModel->getActiveRooms();
        $occupied = $this->indexBy($this->roomBookingsModel->getMonitoringBookings('occupied'), 'room_id');
        $upcoming = $this->indexBy($this->roomBookingsModel->getMonitoringBookings('upcoming'), 'room_id');
        $monthStart = date('Y-m-01 00:00:00');
        $monthEnd = date('Y-m-t 23:59:59');
        $monthSchedule = $this->roomBookingsModel->getCalendarEvents($monthStart, $monthEnd);
        $items = [];

        foreach ($rooms as $room) {
            $roomId = (int)($room['id'] ?? 0);
            $booking = $occupied[$roomId] ?? $upcoming[$roomId] ?? null;
            $isOccupied = isset($occupied[$roomId]);
            $status = $isOccupied ? 'In use' : (isset($upcoming[$roomId]) ? 'Upcoming' : 'Available');
            $next = $isOccupied ? 'Currently in use' : (isset($upcoming[$roomId]) ? 'Reserved' : 'Available now');

            $items[] = [
                'room_id' => $roomId,
                'room_name' => (string)($room['room_name'] ?? 'Meeting room'),
                'room_code' => (string)($room['room_code'] ?? 'Room'),
                'capacity' => ((int)($room['capacity'] ?? 0)) . ' seats',
                'time_range' => $this->formatTimeRange($booking['start_at'] ?? null, $booking['end_at'] ?? null),
                'context' => $booking ? (string)($booking['purpose'] ?? $next) : $next,
                'status' => $status,
            ];
        }
        $items = $this->prioritizeStatuses($items, ['In use', 'Upcoming', 'Available']);

        $display = $this->buildDisplayBuckets($items, ['In use'], self::ROOM_DISPLAY_CAPACITY);

        return [
            'summary' => $this->summarize($items, [
                'Available' => ['tone' => 'emerald', 'label' => 'Available'],
                'In use' => ['tone' => 'sky', 'label' => 'In use'],
                'Upcoming' => ['tone' => 'amber', 'label' => 'Upcoming'],
            ]),
            'items' => $items,
            'display' => $display,
            'schedule' => $monthSchedule,
            'scheduleMonth' => date('Y-m'),
        ];
    }

    public function getWorkforce(): array
    {
        $todayMovements = $this->mobilizationService->getTodayMovements();
        $upcomingMovements = $this->mobilizationService->getUpcomingMovements();
        $todayCounts = ['Mobilizing' => 0, 'Demobilizing' => 0];
        $upcomingCounts = ['Mobilizing' => 0, 'Demobilizing' => 0];
        $todayItems = [];
        $upcomingItems = [];

        foreach ($todayMovements as $movement) {
            $type = $this->movementLabel($movement);
            $todayCounts[$type]++;
            $todayItems[] = $this->formatWorkforceMovement($movement, $type, false);
        }

        $todayDate = date('Y-m-d');
        foreach ($upcomingMovements as $movement) {
            if (substr((string)($movement['movement_date'] ?? ''), 0, 10) <= $todayDate) {
                continue;
            }
            $type = $this->movementLabel($movement);
            $upcomingItems[] = $this->formatWorkforceMovement($movement, 'Upcoming ' . $type, true);
        }

        usort($todayItems, static function (array $left, array $right): int {
            return strcmp((string)($left['movement_date'] ?? ''), (string)($right['movement_date'] ?? ''));
        });
        usort($upcomingItems, static function (array $left, array $right): int {
            return strcmp((string)($left['movement_date'] ?? ''), (string)($right['movement_date'] ?? ''));
        });
        $upcomingItems = array_slice($upcomingItems, 0, 15);
        foreach ($upcomingItems as $item) {
            $type = str_contains((string)($item['status'] ?? ''), 'Demobilizing') ? 'Demobilizing' : 'Mobilizing';
            $upcomingCounts[$type]++;
        }

        $upcomingByType = [
            'Mobilizing' => array_values(array_filter($upcomingItems, static fn (array $item): bool => !str_contains((string)($item['status'] ?? ''), 'Demobilizing'))),
            'Demobilizing' => array_values(array_filter($upcomingItems, static fn (array $item): bool => str_contains((string)($item['status'] ?? ''), 'Demobilizing'))),
        ];

        $items = array_merge($todayItems, $upcomingItems);
        $display = $this->buildDisplayBuckets($items, ['Mobilizing', 'Demobilizing']);
        $display['workforceFrames'] = [
            [
                'summary' => [
                    ['label' => 'Today mobilizing', 'value' => $todayCounts['Mobilizing'], 'tone' => 'sky'],
                    ['label' => 'Today demobilizing', 'value' => $todayCounts['Demobilizing'], 'tone' => 'amber'],
                ],
                'columns' => [
                    'Mobilizing' => array_values(array_filter($todayItems, static fn (array $item): bool => ($item['status'] ?? '') === 'Mobilizing')),
                    'Demobilizing' => array_values(array_filter($todayItems, static fn (array $item): bool => ($item['status'] ?? '') === 'Demobilizing')),
                ],
            ],
            [
                'summary' => [
                    ['label' => 'Upcoming mobilizing', 'value' => $upcomingCounts['Mobilizing'], 'tone' => 'sky'],
                    ['label' => 'Upcoming demobilizing', 'value' => $upcomingCounts['Demobilizing'], 'tone' => 'amber'],
                ],
                'columns' => [
                    'Mobilizing' => $upcomingByType['Mobilizing'],
                    'Demobilizing' => $upcomingByType['Demobilizing'],
                ],
            ],
        ];

        return [
            'summary' => [
                ['label' => 'Today mobilizing', 'value' => $todayCounts['Mobilizing'], 'tone' => 'sky'],
                ['label' => 'Today demobilizing', 'value' => $todayCounts['Demobilizing'], 'tone' => 'amber'],
                ['label' => 'Next mobilizing', 'value' => $upcomingCounts['Mobilizing'], 'tone' => 'sky'],
                ['label' => 'Next demobilizing', 'value' => $upcomingCounts['Demobilizing'], 'tone' => 'amber'],
            ],
            'items' => $items,
            'display' => $display,
        ];
    }

    private function movementLabel(array $movement): string
    {
        return strtolower((string)($movement['movement_type'] ?? '')) === 'demobilization'
            ? 'Demobilizing'
            : 'Mobilizing';
    }

    private function formatWorkforceMovement(array $movement, string $status, bool $upcoming): array
    {
        $department = trim((string)($movement['employee_department'] ?? '')) ?: 'Unassigned department';
        $timestamp = strtotime((string)($movement['movement_date'] ?? ''));

        return [
            'employee_name' => (string)($movement['employee_name'] ?? 'Unknown employee'),
            'staff_identifier' => (string)($movement['employee_staff_id'] ?? $movement['employee_id'] ?? 'Unassigned ID'),
            'department' => $department,
            'movement_date' => $timestamp === false ? '' : date('Y-m-d', $timestamp),
            'date' => $timestamp === false ? 'Date unavailable' : date('M j, Y', $timestamp),
            'status' => $status,
            'count' => 1,
            'detail' => ($upcoming ? 'Upcoming · ' : '') . $department,
            'tone' => str_contains($status, 'Demobilizing') ? 'amber' : 'sky',
        ];
    }

    private function indexBy(array $rows, string $key): array
    {
        $indexed = [];
        foreach ($rows as $row) {
            $value = (int)($row[$key] ?? 0);
            if ($value > 0 && !isset($indexed[$value])) {
                $indexed[$value] = $row;
            }
        }

        return $indexed;
    }

    private function summarize(array $items, array $statuses): array
    {
        $counts = array_fill_keys(array_keys($statuses), 0);
        foreach ($items as $item) {
            $status = (string)($item['status'] ?? '');
            if (array_key_exists($status, $counts)) {
                $counts[$status]++;
            }
        }

        $summary = [];
        foreach ($statuses as $status => $config) {
            $summary[] = [
                'label' => $config['label'],
                'value' => $counts[$status],
                'tone' => $config['tone'],
            ];
        }

        return $summary;
    }

    private function prioritizeStatuses(array $items, array $priority): array
    {
        $rank = array_flip($priority);
        usort($items, static function (array $left, array $right) use ($rank): int {
            return ($rank[$left['status'] ?? ''] ?? PHP_INT_MAX) <=> ($rank[$right['status'] ?? ''] ?? PHP_INT_MAX);
        });

        return $items;
    }

    private function buildDisplayBuckets(array $items, array $currentStatuses, ?int $capacity = null): array
    {
        $priority = [];
        $secondaryCandidates = [];

        foreach ($items as $item) {
            if (in_array((string)($item['status'] ?? ''), $currentStatuses, true)) {
                $priority[] = $item;
            } else {
                $secondaryCandidates[] = $item;
            }
        }

        $availableSlots = max(0, ($capacity ?? self::DISPLAY_CAPACITY) - count($priority));

        return [
            'priority' => $priority,
            'secondary' => array_slice($secondaryCandidates, 0, $availableSlots),
            'overflow' => array_slice($secondaryCandidates, $availableSlots),
        ];
    }

    private function formatTimeRange(?string $start, ?string $end): string
    {
        if (empty($start) || empty($end)) {
            return 'No scheduled time';
        }

        $startTime = strtotime($start);
        $endTime = strtotime($end);
        if ($startTime === false || $endTime === false) {
            return 'Time unavailable';
        }

        $startDate = date('Y-m-d', $startTime);
        $endDate = date('Y-m-d', $endTime);
        $timeRange = date('g:i A', $startTime) . ' - ' . date('g:i A', $endTime);
        if ($startDate === $endDate) {
            return $timeRange;
        }

        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $formatDate = static function (int $timestamp) use ($today, $yesterday, $tomorrow): string {
            $date = date('Y-m-d', $timestamp);
            if ($date === $yesterday) {
                return 'YESTERDAY';
            }
            if ($date === $today) {
                return 'TODAY';
            }
            if ($date === $tomorrow) {
                return 'TOMORROW';
            }

            return strtoupper(date('M j', $timestamp));
        };

        return $formatDate($startTime) . ' ' . date('g:i A', $startTime)
            . ' → ' . $formatDate($endTime) . ' ' . date('g:i A', $endTime);
    }
}