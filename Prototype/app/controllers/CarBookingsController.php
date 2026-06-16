<?php
session_start();
require_once '../app/core/Controller.php';
require_once '../app/models/CarBookings.php';
require_once '../app/models/CarVehicles.php';
require_once '../app/models/CarDrivers.php';
require_once '../app/models/User.php';

class CarBookingsController extends Controller {

    private CarBookings $bookingsModel;
    private CarVehicles $vehiclesModel;
    private CarDrivers $driversModel;

    public function __construct() {
        $this->bookingsModel = new CarBookings();
        $this->vehiclesModel = new CarVehicles();
        $this->driversModel = new CarDrivers();
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

        $events = $this->bookingsModel->getCalendarEvents($start, $end, $vehicleId, $driverId);
        echo json_encode(['success' => true, 'events' => $events]);
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
            $id = $this->bookingsModel->createBooking($data, $actorUserId);
            echo json_encode(['success' => true, 'booking_id' => $id]);
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

