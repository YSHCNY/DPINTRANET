<?php

declare(strict_types=1);

namespace App\Services;

require_once __DIR__ . '/../models/EmployeeMobilizationModel.php';
require_once __DIR__ . '/../models/StaffDirectoryModel.php';

use EmployeeMobilizationModel;
use InvalidArgumentException;
use DateTime;
use StaffDirectoryModel;

class EmployeeMobilizationService
{
    private EmployeeMobilizationModel $mobilizationModel;
    private StaffDirectoryModel $staffDirectoryModel;

    public function __construct(?EmployeeMobilizationModel $mobilizationModel = null, ?StaffDirectoryModel $staffDirectoryModel = null)
    {
        $this->mobilizationModel = $mobilizationModel ?? new EmployeeMobilizationModel();
        $this->staffDirectoryModel = $staffDirectoryModel ?? new StaffDirectoryModel();
    }

    public function scheduleMobilization(int $employeeId, string $movementDate, ?string $remarks = null, ?int $createdBy = null): int
    {
        return $this->scheduleMovement($employeeId, 'Mobilization', $movementDate, $remarks, $createdBy);
    }

    public function scheduleDemobilization(int $employeeId, string $movementDate, ?string $remarks = null, ?int $createdBy = null): int
    {
        return $this->scheduleMovement($employeeId, 'Demobilization', $movementDate, $remarks, $createdBy);
    }

    public function completeMovement(int $id, ?int $updatedBy = null): bool
    {
        $id = $this->normalizeId($id);

        return $this->mobilizationModel->update($id, [
            'status' => 'Completed',
            'updated_by' => $updatedBy,
        ]);
    }

    public function cancelMovement(int $id, ?int $updatedBy = null): bool
    {
        $id = $this->normalizeId($id);

        return $this->mobilizationModel->update($id, [
            'status' => 'Cancelled',
            'updated_by' => $updatedBy,
        ]);
    }

    public function getDashboardSummary(): array
    {
        $todayMovements = $this->getTodayMovements();
        $upcomingMovements = $this->getUpcomingMovements();
        $now = new DateTime();
        $monthMovements = $this->mobilizationModel->getByMonth((int)$now->format('Y'), (int)$now->format('m'));

        $summary = [
            'today_count' => count($todayMovements),
            'upcoming_count' => count($upcomingMovements),
            'month_count' => count($monthMovements),
            'status_counts' => [
                'Scheduled' => 0,
                'Completed' => 0,
                'Cancelled' => 0,
            ],
            'today' => $todayMovements,
            'upcoming' => $upcomingMovements,
        ];

        foreach ($monthMovements as $item) {
            $status = $item['status'] ?? 'Scheduled';
            if (!isset($summary['status_counts'][$status])) {
                $summary['status_counts'][$status] = 0;
            }
            $summary['status_counts'][$status]++;
        }

        return $summary;
    }

    public function getCalendarEvents(string $startDate, string $endDate): array
    {
        $startDate = $this->normalizeMovementDate($startDate);
        $endDate = $this->normalizeMovementDate($endDate);

        if ($startDate > $endDate) {
            throw new InvalidArgumentException('Start date must be on or before end date');
        }

        $movements = $this->mobilizationModel->getByDateRange($startDate, $endDate);
        return $this->formatCalendarEvents($movements);
    }

    public function getEmployeeHistory(int $employeeId): array
    {
        $employeeId = $this->normalizeEmployeeId($employeeId);
        $movements = $this->mobilizationModel->findByEmployee($employeeId);
        return $this->formatCalendarEvents($movements);
    }

    private function formatCalendarEvents(array $movements): array
    {
        $employeeIds = [];
        foreach ($movements as $movement) {
            if (isset($movement['employee_id']) && $movement['employee_id'] !== null) {
                $employeeIds[] = (int)$movement['employee_id'];
            }
        }

        $employeeDetails = $this->getEmployeeDetails(array_values(array_unique($employeeIds)));
        $events = [];

        foreach ($movements as $movement) {
            $employeeId = isset($movement['employee_id']) ? (int)$movement['employee_id'] : null;
            $employee = $employeeId !== null ? ($employeeDetails[$employeeId] ?? []) : [];
            $events[] = [
                'employee_id' => $employeeId,
                'employee_name' => $employee['employee_name'] ?? 'Unknown Employee',
                'employee_staff_id' => $employee['employee_staff_id'] ?? null,
                'employee_position' => $employee['position'] ?? null,
                'employee_department' => $employee['department'] ?? null,
                'movement_type' => $movement['movement_type'] ?? '',
                'movement_date' => $movement['movement_date'] ?? '',
                'status' => $movement['status'] ?? 'Scheduled',
            ];
        }

        return $events;
    }

    private function getEmployeeDetails(array $employeeIds): array
    {
        $employeeDetails = [];
        if ($employeeIds === []) {
            return $employeeDetails;
        }

        $staffEntries = $this->staffDirectoryModel->getStaffByIds($employeeIds);
        foreach ($staffEntries as $staffEntry) {
            $id = isset($staffEntry['id']) ? (int)$staffEntry['id'] : null;
            if ($id === null) {
                continue;
            }

            $employeeDetails[$id] = [
                'employee_name' => trim((string)($staffEntry['firstName'] ?? '') . ' ' . (string)($staffEntry['lastName'] ?? '')) ?: 'Unknown Employee',
                'employee_staff_id' => (string)($staffEntry['staff_id'] ?? ''),
                'position' => (string)($staffEntry['position'] ?? ''),
                'department' => (string)($staffEntry['department'] ?? ''),
            ];
        }

        return $employeeDetails;
    }

    public function getActiveEmployees(): array
    {
        $allMovements = array_merge($this->mobilizationModel->getUpcoming(), $this->mobilizationModel->getToday());
        $employeeIds = [];

        foreach ($allMovements as $movement) {
            if (isset($movement['employee_id']) && $movement['employee_id'] !== null) {
                $employeeIds[] = (int)$movement['employee_id'];
            }
        }

        return array_values(array_unique($employeeIds));
    }

    public function getMobilizedThisMonth(): array
    {
        $monthMovements = $this->mobilizationModel->getByMonth((int)(new DateTime())->format('Y'), (int)(new DateTime())->format('m'));
        return array_values(array_filter($monthMovements, static function (array $movement) {
            return isset($movement['movement_type']) && $movement['movement_type'] === 'Mobilization';
        }));
    }

    public function getDemobilizedThisMonth(): array
    {
        $monthMovements = $this->mobilizationModel->getByMonth((int)(new DateTime())->format('Y'), (int)(new DateTime())->format('m'));
        return array_values(array_filter($monthMovements, static function (array $movement) {
            return isset($movement['movement_type']) && $movement['movement_type'] === 'Demobilization';
        }));
    }

    public function getUpcomingThisWeek(): array
    {
        $today = new DateTime();
        $endOfWeek = (clone $today)->modify('+6 days');
        $upcoming = $this->mobilizationModel->getUpcoming();

        return array_values(array_filter($upcoming, static function (array $movement) use ($today, $endOfWeek) {
            if (empty($movement['movement_date'])) {
                return false;
            }
            $movementDate = DateTime::createFromFormat('Y-m-d', $movement['movement_date']);
            if ($movementDate === false) {
                return false;
            }
            return $movementDate >= $today && $movementDate <= $endOfWeek;
        }));
    }

    public function getTodayMovements(): array
    {
        return $this->formatMovementsWithEmployeeDetails($this->mobilizationModel->getToday());
    }

    private function formatMovementsWithEmployeeDetails(array $movements): array
    {
        $employeeIds = [];
        foreach ($movements as $movement) {
            if (isset($movement['employee_id']) && $movement['employee_id'] !== null) {
                $employeeIds[] = (int)$movement['employee_id'];
            }
        }

        $employeeDetails = $this->getEmployeeDetails(array_values(array_unique($employeeIds)));
        $formatted = [];

        foreach ($movements as $movement) {
            $employeeId = isset($movement['employee_id']) ? (int)$movement['employee_id'] : null;
            $employee = $employeeId !== null ? ($employeeDetails[$employeeId] ?? []) : [];
            $formatted[] = array_merge($movement, [
                'employee_id' => $employeeId,
                'employee_name' => $employee['employee_name'] ?? 'Unknown Employee',
                'employee_staff_id' => $employee['employee_staff_id'] ?? null,
                'employee_position' => $employee['position'] ?? null,
                'employee_department' => $employee['department'] ?? null,
            ]);
        }

        return $formatted;
    }

    public function getLatestMobilized(int $limit = 5): array
    {
        $monthMovements = $this->mobilizationModel->getByMonth((int)(new DateTime())->format('Y'), (int)(new DateTime())->format('m'));
        $mobilizations = array_values(array_filter($monthMovements, static function (array $movement) {
            return isset($movement['movement_type']) && $movement['movement_type'] === 'Mobilization';
        }));

        usort($mobilizations, static function (array $a, array $b) {
            return strcmp($b['movement_date'] ?? '', $a['movement_date'] ?? '');
        });

        return array_slice($mobilizations, 0, $limit);
    }

    public function getLatestDemobilized(int $limit = 5): array
    {
        $monthMovements = $this->mobilizationModel->getByMonth((int)(new DateTime())->format('Y'), (int)(new DateTime())->format('m'));
        $demobilizations = array_values(array_filter($monthMovements, static function (array $movement) {
            return isset($movement['movement_type']) && $movement['movement_type'] === 'Demobilization';
        }));

        usort($demobilizations, static function (array $a, array $b) {
            return strcmp($b['movement_date'] ?? '', $a['movement_date'] ?? '');
        });

        return array_slice($demobilizations, 0, $limit);
    }

    public function getUpcomingMovements(): array
    {
        return $this->formatMovementsWithEmployeeDetails($this->mobilizationModel->getUpcoming());
    }

    private function scheduleMovement(int $employeeId, string $movementType, string $movementDate, ?string $remarks, ?int $createdBy): int
    {
        $employeeId = $this->normalizeEmployeeId($employeeId);
        $movementType = $this->normalizeMovementType($movementType);
        $movementDate = $this->normalizeMovementDate($movementDate);

        return $this->mobilizationModel->create([
            'employee_id' => $employeeId,
            'movement_type' => $movementType,
            'movement_date' => $movementDate,
            'remarks' => $remarks,
            'status' => 'Scheduled',
            'created_by' => $createdBy,
            'updated_by' => $createdBy,
        ]);
    }

    private function normalizeId(int $id): int
    {
        if ($id < 1) {
            throw new InvalidArgumentException('Invalid movement id');
        }

        return $id;
    }

    private function normalizeEmployeeId(int $employeeId): int
    {
        if ($employeeId < 1) {
            throw new InvalidArgumentException('Invalid employee id');
        }

        return $employeeId;
    }

    private function normalizeMovementType(string $movementType): string
    {
        $normalized = trim($movementType);
        $allowed = ['Mobilization', 'Demobilization'];

        if (!in_array($normalized, $allowed, true)) {
            throw new InvalidArgumentException('Movement type must be Mobilization or Demobilization');
        }

        return $normalized;
    }

    private function normalizeMovementDate(string $movementDate): string
    {
        $date = DateTime::createFromFormat('Y-m-d', $movementDate);
        if ($date === false || $date->format('Y-m-d') !== $movementDate) {
            throw new InvalidArgumentException('movement_date must be a valid date in YYYY-MM-DD format');
        }

        return $movementDate;
    }
}
