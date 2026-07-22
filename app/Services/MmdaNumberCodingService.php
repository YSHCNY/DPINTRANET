<?php
namespace App\Services;

class MmdaNumberCodingService
{
    public static function isVehicleCoding(?string $plateNumber, ?string $bookingDate, ?string $bookingStartTime): array
    {
        $plate = trim((string) $plateNumber);
        $datePart = trim((string) $bookingDate);
        $timePart = trim((string) $bookingStartTime);

        if ($plate === '' || $datePart === '' || $timePart === '') {
            return ['coding' => false, 'reason' => '', 'windowHours' => false];
        }

        $matches = [];
        preg_match('/(\d)(?!.*\d)/', $plate, $matches);
        $lastDigit = isset($matches[1]) ? (int) $matches[1] : null;
        if ($lastDigit === null) {
            preg_match('/(\d)/', $plate, $matches);
            $lastDigit = isset($matches[1]) ? (int) $matches[1] : null;
        }
        if ($lastDigit === null) {
            return ['coding' => false, 'reason' => '', 'windowHours' => false];
        }

        $day = strtolower((new \DateTime($datePart))->format('l'));
        $dayMap = [
            'monday' => [1, 2],
            'tuesday' => [3, 4],
            'wednesday' => [5, 6],
            'thursday' => [7, 8],
            'friday' => [9, 0],
        ];

        if (!isset($dayMap[$day])) {
            return ['coding' => false, 'reason' => '', 'windowHours' => false];
        }

        if (!in_array($lastDigit, $dayMap[$day], true)) {
            return ['coding' => false, 'reason' => '', 'windowHours' => false];
        }

        $time = self::normalizeTime($timePart);
        if ($time === null) {
            return ['coding' => false, 'reason' => '', 'windowHours' => false];
        }

        $morningStart = 7 * 60;
        $morningEnd = 10 * 60;
        $eveningStart = 17 * 60;
        $eveningEnd = 20 * 60;
        $windowStart = 10 * 60 + 1;
        $windowEnd = 16 * 60 + 59;

        if (($time >= $morningStart && $time <= $morningEnd) || ($time >= $eveningStart && $time <= $eveningEnd)) {
            return ['coding' => true, 'reason' => 'MMDA Number Coding', 'windowHours' => false];
        }

        if ($time >= $windowStart && $time <= $windowEnd) {
            return ['coding' => false, 'reason' => '', 'windowHours' => true];
        }

        return ['coding' => false, 'reason' => '', 'windowHours' => false];
    }

    private static function normalizeTime(string $value): ?int
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $value = str_replace('T', ' ', $value);
        if (preg_match('/^(\d{1,2}):(\d{2})(?::\d{2})?$/', $value, $matches)) {
            $hours = (int) $matches[1];
            $minutes = (int) $matches[2];
            if ($hours < 0 || $hours > 23 || $minutes < 0 || $minutes > 59) {
                return null;
            }
            return $hours * 60 + $minutes;
        }

        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
        if ($date instanceof \DateTime) {
            return (int) $date->format('H') * 60 + (int) $date->format('i');
        }

        $date = \DateTime::createFromFormat('Y-m-d H:i', $value);
        if ($date instanceof \DateTime) {
            return (int) $date->format('H') * 60 + (int) $date->format('i');
        }

        return null;
    }
}
