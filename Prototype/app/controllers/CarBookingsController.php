<?php
session_start();
require_once '../app/core/Controller.php';
require_once '../app/models/CarBookings.php';
require_once '../app/models/CarVehicles.php';
require_once '../app/models/CarDrivers.php';
require_once '../app/models/User.php';
// Load app config if available (try sensible locations)
if (file_exists(__DIR__ . '/../config.php')) {
    require_once __DIR__ . '/../config.php';
} elseif (file_exists(__DIR__ . '/../../app/config.php')) {
    require_once __DIR__ . '/../../app/config.php';
}

class CarBookingsController extends Controller {

    private function classifyBookingError(string $message): string {
        // Use unique markers from the model first.
        if (str_contains($message, '[VEHICLE_CONFLICT]')) return 'vehicle_conflict';
        if (str_contains($message, '[DRIVER_CONFLICT]')) return 'driver_conflict';
        if (str_contains($message, '[PAST_DEPARTURE]')) return 'past_departure';
        if (str_contains($message, '[TIME_INVALID]')) return 'time_invalid';

        // Backwards-compatible fallback (loose string matching)
        $m = strtolower($message);
        if (str_contains($m, 'vehicle')) return 'vehicle_conflict';
        if (str_contains($m, 'driver')) return 'driver_conflict';
        if (str_contains($m, 'past')) return 'past_departure';
        if (str_contains($m, 'return expected') || str_contains($m, 'after departure') || str_contains($m, 'time')) return 'time_invalid';

        return 'unknown';
    }


    private CarBookings $bookingsModel;
    private CarVehicles $vehiclesModel;
    private CarDrivers $driversModel;

    public function __construct() {
        $this->bookingsModel = new CarBookings();
        $this->vehiclesModel = new CarVehicles();
        $this->driversModel = new CarDrivers();
        // Force database-backed storage for bookings (disable JSON fallback)
        // This ensures the module always uses the database for production usage.
        $this->useJsonStore = false;
    }

    public function calendar() {
        $this->requireLogin();

        // Permission: allow view for everyone, but creation is handled by role checks below.
        $vehicles = $this->vehiclesModel->getActiveVehicles();
        $drivers = $this->driversModel->getActiveDrivers();

        $content = $this->renderView('car_bookings/calendar', [
            'vehicles' => $vehicles,
            'drivers' => $drivers,
        ]);

        $this->view('layout/main', ['content' => $content]);
    }

    // AJAX: list events for calendar range
    public function list() {
        $this->requireLogin();

        header('Content-Type: application/json');
        $start = $_GET['start'] ?? '';
        $end = $_GET['end'] ?? '';
        $vehicleId = isset($_GET['vehicle_id']) && $_GET['vehicle_id'] !== '' ? (int)$_GET['vehicle_id'] : null;
        $driverId = isset($_GET['driver_id']) && $_GET['driver_id'] !== '' ? (int)$_GET['driver_id'] : null;
        if ($this->useJsonStore) {
            $bookings = $this->bookingsModel->listBookingsJson();
            // map JSON bookings to FullCalendar events
            $events = [];
            foreach ($bookings as $b) {
                if ($vehicleId && (int)($b['vehicle_id'] ?? 0) !== (int)$vehicleId) continue;
                $startVal = $b['start_at'] ?? ($b['departure_expected'] ?? ($b['date_trip'] ?? null));
                $endVal = $b['end_at'] ?? ($b['return_expected'] ?? null);
                $events[] = [
                    'id' => (int)($b['id'] ?? 0),
                    'title' => ($b['purpose'] ? $b['purpose'] . ' - ' . ($b['vehicle_name'] ?? '') : ($b['vehicle_name'] ?? 'Booking')),
                    'start' => $startVal,
                    'end' => $endVal ?? ($b['date_trip'] ?? null),
                    'allDay' => false,
                    'extendedProps' => $b,
                ];
            }
            echo json_encode(['success' => true, 'events' => $events]);
        } else {
            $events = $this->bookingsModel->getCalendarEvents($start, $end, $vehicleId, $driverId);
            echo json_encode(['success' => true, 'events' => $events]);
        }
        exit;
    }

    public function vehicleHistory() {
        $this->requireLogin();
        header('Content-Type: application/json');

        try {
            $vehicleId = isset($_GET['vehicle_id']) ? (int)$_GET['vehicle_id'] : 0;
            if ($vehicleId < 1) {
                throw new Exception('Invalid vehicle_id');
            }

            $history = $this->bookingsModel->getVehicleBookingHistory($vehicleId, 15);
            echo json_encode(['success' => true, 'bookings' => $history]);
        } catch (Exception $e) {
            http_response_code(400);
            $msg = $e->getMessage();
            $errorCode = match (true) {
                str_contains(strtolower($msg), 'vehicle') => 'vehicle_conflict',
                str_contains(strtolower($msg), 'driver') => 'driver_conflict',
                str_contains(strtolower($msg), 'past') => 'past_departure',
                default => 'unknown',
            };
            echo json_encode(['success' => false, 'message' => $msg, 'error_code' => $errorCode]);
        }
        exit;
    }

    // AJAX create booking
    public function create() {
        $this->requireLogin();
        $actorUserId = (int)($_SESSION['id'] ?? 0);


        // Role gate: allow Admin/Editor/SuperAdmin (levels 0/1/2). Viewers cannot create.
        $level = $this->currentUserLevel();
        if (!in_array($level, [0, 1, 2], true)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'You do not have permission to create bookings.']);
            exit;
        }

        header('Content-Type: application/json');

        try {
            $data = $this->getBookingPostData();
            if ($this->useJsonStore) {
                $id = $this->bookingsModel->createBookingJson($data, $actorUserId);
            } else {
                $id = $this->bookingsModel->createBooking($data, $actorUserId);
            }
            echo json_encode(['success' => true, 'booking_id' => $id]);
        } catch (Exception $e) {
            http_response_code(400);
            $msg = $e->getMessage();
            $errorCode = match (true) {
                str_contains(strtolower($msg), 'vehicle') => 'vehicle_conflict',
                str_contains(strtolower($msg), 'driver') => 'driver_conflict',
                str_contains(strtolower($msg), 'past') => 'past_departure',
                default => 'unknown',
            };
            echo json_encode(['success' => false, 'message' => $msg, 'error_code' => $errorCode]);
        }
        exit;
    }

    // JSON file endpoints (fallback) ---------------------------------
    public function jsonList() {
        $this->requireLogin();
        header('Content-Type: application/json');
        try {
            $events = $this->bookingsModel->listBookingsJson();
            echo json_encode(['success' => true, 'bookings' => $events]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function jsonCreate() {
        $this->requireLogin();
        $level = $this->currentUserLevel();
        if (!in_array($level, [0,1,2], true)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'You do not have permission to create bookings.']);
            exit;
        }
        header('Content-Type: application/json');
        try {
            $data = $_POST;
            $id = $this->bookingsModel->createBookingJson($data, (int)($_SESSION['id'] ?? 0));
            echo json_encode(['success' => true, 'booking_id' => $id]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function jsonUpdate() {
        $this->requireLogin();
        $level = $this->currentUserLevel();
        if (!in_array($level, [0,1,2], true)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'You do not have permission to modify bookings.']);
            exit;
        }
        header('Content-Type: application/json');
        try {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id < 1) throw new Exception('Invalid id');
            $ok = $this->bookingsModel->updateBookingJson($id, $_POST, (int)($_SESSION['id'] ?? 0));
            echo json_encode(['success' => $ok]);
        } catch (Exception $e) {
            http_response_code(400);
            $msg = $e->getMessage();
            $errorCode = match (true) {
                str_contains(strtolower($msg), 'vehicle') => 'vehicle_conflict',
                str_contains(strtolower($msg), 'driver') => 'driver_conflict',
                str_contains(strtolower($msg), 'past') => 'past_departure',
                default => 'unknown',
            };
            echo json_encode(['success' => false, 'message' => $msg, 'error_code' => $errorCode]);
        }
        exit;
    }

    public function jsonDelete() {
        $this->requireLogin();
        $level = $this->currentUserLevel();
        if (!in_array($level, [0,1,2], true)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'You do not have permission to delete bookings.']);
            exit;
        }
        header('Content-Type: application/json');
        try {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id < 1) throw new Exception('Invalid id');
            $ok = $this->bookingsModel->deleteBookingJson($id, (int)($_SESSION['id'] ?? 0));
            echo json_encode(['success' => $ok]);
        } catch (Exception $e) {
            http_response_code(400);
            $msg = $e->getMessage();
            $errorCode = match (true) {
                str_contains(strtolower($msg), 'vehicle') => 'vehicle_conflict',
                str_contains(strtolower($msg), 'driver') => 'driver_conflict',
                str_contains(strtolower($msg), 'past') => 'past_departure',
                default => 'unknown',
            };
            echo json_encode(['success' => false, 'message' => $msg, 'error_code' => $errorCode]);
        }
        exit;
    }


    // Get single booking (DB or JSON)
    public function get() {
        $this->requireLogin();
        header('Content-Type: application/json');
        try {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($id < 1) throw new Exception('Invalid id');

            if ($this->useJsonStore) {
                $bookings = $this->bookingsModel->listBookingsJson();
                $found = null;
                foreach ($bookings as $b) {
                    if ((int)($b['id'] ?? 0) === $id) { $found = $b; break; }
                }
                if (!$found) throw new Exception('Booking not found');
                echo json_encode(['success' => true, 'booking' => $found]);
            } else {
                $row = $this->bookingsModel->getBookingById($id);
                if (!$row) throw new Exception('Booking not found');
                echo json_encode(['success' => true, 'booking' => $row]);
            }
        } catch (Exception $e) {
            http_response_code(400);
            $msg = $e->getMessage();
            $errorCode = match (true) {
                str_contains(strtolower($msg), 'vehicle') => 'vehicle_conflict',
                str_contains(strtolower($msg), 'driver') => 'driver_conflict',
                str_contains(strtolower($msg), 'past') => 'past_departure',
                default => 'unknown',
            };
            echo json_encode(['success' => false, 'message' => $msg, 'error_code' => $errorCode]);
        }
        exit;
    }


    // DB update booking
    public function update() {
        $this->requireLogin();
        $level = $this->currentUserLevel();
        if (!in_array($level, [0,1,2], true)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'You do not have permission to modify bookings.']);
            exit;
        }

        header('Content-Type: application/json');
        try {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id < 1) throw new Exception('Invalid id');

            if ($this->useJsonStore) {
                $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
                if ($id < 1) throw new Exception('Invalid id');
                $ok = $this->bookingsModel->updateBookingJson($id, $_POST, (int)($_SESSION['id'] ?? 0));
            } else {
                $existing = $this->bookingsModel->getBookingById($id);
                if (!$existing) throw new Exception('Booking not found');

                // Merge existing with provided POST values (support partial updates)
                $merged = $existing;
                foreach ($_POST as $k => $v) {
                    if ($k === 'id') continue;
                    $merged[$k] = $v;
                }

                $ok = $this->bookingsModel->updateBooking($id, $merged, (int)($_SESSION['id'] ?? 0));

                // log
                require_once '../app/models/CarBookingLogs.php';
                $logs = new CarBookingLogs();
                $logs->log($id, (int)($_SESSION['id'] ?? 0), 'update', ['before' => $existing, 'after' => $merged]);
            }

            echo json_encode(['success' => $ok]);
        } catch (Exception $e) {
            http_response_code(400);
            $msg = $e->getMessage();
            $errorCode = match (true) {
                str_contains(strtolower($msg), 'vehicle') => 'vehicle_conflict',
                str_contains(strtolower($msg), 'driver') => 'driver_conflict',
                str_contains(strtolower($msg), 'past') => 'past_departure',
                default => 'unknown',
            };
            echo json_encode(['success' => false, 'message' => $msg, 'error_code' => $errorCode]);
        }
        exit;
    }

    // DB delete (cancel)
    public function delete() {
        $this->requireLogin();
        $level = $this->currentUserLevel();
        if (!in_array($level, [0,1,2], true)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'You do not have permission to delete bookings.']);
            exit;
        }

        header('Content-Type: application/json');
        try {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id < 1) throw new Exception('Invalid id');

            if ($this->useJsonStore) {
                $ok = $this->bookingsModel->deleteBookingJson($id, (int)($_SESSION['id'] ?? 0));
            } else {
                $ok = $this->bookingsModel->deleteBooking($id, (int)($_SESSION['id'] ?? 0));

                require_once '../app/models/CarBookingLogs.php';
                $logs = new CarBookingLogs();
                $logs->log($id, (int)($_SESSION['id'] ?? 0), 'delete', ['id' => $id]);
            }

            echo json_encode(['success' => $ok]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    private function getBookingPostData(): array {
        $required = [
            'date_trip',
            'date_requested',
            'destinations',
            'purpose',
            'passengers',
            'departure_expected',
            'return_expected',
            'vehicle_id',
            'driver_id',
        ];

        $missing = [];
        foreach ($required as $k) {
            if (!isset($_POST[$k]) || (string)$_POST[$k] === '') {
                $missing[] = $k;
            }
        }

        if ($missing) {
            throw new Exception('Missing required fields: ' . implode(', ', $missing));
        }

        // Sanitize basic strings
        $get = fn($k) => is_string($_POST[$k]) ? trim($_POST[$k]) : $_POST[$k];

        return [
            'date_trip' => $get('date_trip'),
            'date_requested' => $get('date_requested'),
            'destinations' => $get('destinations'),
            'purpose' => $get('purpose'),
            'passengers' => (int)$_POST['passengers'],
            'departure_expected' => $get('departure_expected'),
            'return_expected' => $get('return_expected'),
            'special_instructions' => $get('special_instructions') ?? '',
            'vehicle_id' => (int)$_POST['vehicle_id'],
            'driver_id' => (int)$_POST['driver_id'],
            'remarks' => $get('remarks') ?? '',
        ];
    }
}

