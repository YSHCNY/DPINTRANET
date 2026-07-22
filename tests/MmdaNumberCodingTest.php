<?php
require_once __DIR__ . '/../app/Services/MmdaNumberCodingService.php';

use App\Services\MmdaNumberCodingService;

$cases = [
    ['ABC1234', '2026-07-20', '08:00', false, false],
    ['ABC1234', '2026-07-20', '11:30', false, false],
    ['ABC1234', '2026-07-20', '07:00', false, false],
    ['ABC1234', '2026-07-20', '17:00', false, false],
    ['ABC1231', '2026-07-13', '08:00', true, false],
    ['ABC1232', '2026-07-13', '11:30', false, true],
    ['NAA9080', '2026-07-17', '07:15', true, false],
    ['XYZ7810', '2026-07-16', '08:00', false, false],
    ['ABC1234', '2026-07-18', '08:00', false, false],
    ['ABC1234', '2026-07-11', '08:00', false, false],
];

foreach ($cases as $index => [$plate, $date, $time, $expectedCoding, $expectedWindow]) {
    $result = MmdaNumberCodingService::isVehicleCoding($plate, $date, $time);
    if ((bool)$result['coding'] !== $expectedCoding || (bool)$result['windowHours'] !== $expectedWindow) {
        fwrite(STDERR, "Case {$index} failed: " . json_encode([$plate, $date, $time, $result]) . PHP_EOL);
        exit(1);
    }
}

echo "MMDA number coding tests passed" . PHP_EOL;
