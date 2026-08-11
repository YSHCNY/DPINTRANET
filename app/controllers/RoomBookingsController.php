<?php
session_start();
require_once '../app/core/Controller.php';
require_once '../app/core/Database.php';
require_once '../app/models/RoomBookings.php';
require_once '../app/models/Rooms.php';

class RoomBookingsController extends Controller {
    private RoomBookings $bookingsModel;
    private Rooms $roomsModel;

    public function __construct() {
        $this->bookingsModel = new RoomBookings();
        $this->roomsModel = new Rooms();
    }

    private function classifyBookingError(string $message): string {
        if (str_contains($message, '[ROOM_CONFLICT]')) return 'room_conflict';
        if (str_contains($message, '[CAPACITY_EXCEEDED]')) return 'capacity_exceeded';
        if (str_contains($message, '[PAST_DEPARTURE]')) return 'past_departure';
        if (str_contains($message, '[TIME_INVALID]')) return 'time_invalid';
        return 'unknown';
    }

    private function logRoomModuleAction(string $action, int $targetId, array $context = []): void {
        $actorId = (int)($_SESSION['id'] ?? $_SESSION['user_id'] ?? 0);
        $actorName = (string)($_SESSION['user'] ?? ($actorId > 0 ? $actorId : 'system'));

        $details = [];
        $targetRef = $targetId > 0 ? "#{$targetId}" : 'new entry';

        if (!empty($context['room_id'])) {
            $details[] = 'Room ID: ' . $context['room_id'];
        }
        if (!empty($context['room_name'])) {
            $details[] = 'Room: ' . $context['room_name'];
        }
        if (!empty($context['purpose'])) {
            $details[] = 'Purpose: ' . $context['purpose'];
        }
        if (!empty($context['date_trip'])) {
            $details[] = 'Date: ' . $context['date_trip'];
        }
        if (!empty($context['message'])) {
            $details[] = $context['message'];
        }

        $description = match ($action) {
            'create_booking' => 'Created room booking ' . $targetRef . ($details ? ' • ' . implode(' • ', $details) : ''),
            'update_booking' => 'Updated room booking ' . $targetRef . ($details ? ' • ' . implode(' • ', $details) : ''),
            'delete_booking' => 'Deleted room booking ' . $targetRef . ($details ? ' • ' . implode(' • ', $details) : ''),
            'create_room' => 'Created room ' . $targetRef . ($details ? ' • ' . implode(' • ', $details) : ''),
            'update_room' => 'Updated room ' . $targetRef . ($details ? ' • ' . implode(' • ', $details) : ''),
            'delete_room' => 'Deleted room ' . $targetRef . ($details ? ' • ' . implode(' • ', $details) : ''),
            default => 'Room booking action ' . $action . ' for ' . $targetRef,
        };

        try {
            $db = Database::connect();
            $stmt = $db->prepare("INSERT INTO systemLogs (userName, logDesc, module, logDate) VALUES (?, ?, ?, ?)");
            $stmt->execute([$actorName, $description, 'Room Bookings', date('Y-m-d H:i:s')]);
        } catch (Throwable $e) {
            // Keep the booking flow intact even if logging fails.
        }
    }

    public function calendar() {
        $this->requireLogin();
        $rooms = $this->roomsModel->listRooms();
        $content = $this->renderView('room_bookings/calendar', [
            'rooms' => $rooms,
        ]);
        $this->view('layout/main', ['content' => $content]);
    }

    public function list() {
        $this->requireLogin();
        header('Content-Type: application/json');

        $start = $_GET['start'] ?? '';
        $end = $_GET['end'] ?? '';
        $roomId = isset($_GET['room_id']) && $_GET['room_id'] !== '' ? (int)$_GET['room_id'] : null;

        $events = $this->bookingsModel->getCalendarEvents($start, $end, $roomId);
        echo json_encode(['success' => true, 'events' => $events]);
        exit;
    }

    public function roomHistory() {
        $this->requireLogin();
        header('Content-Type: application/json');
        try {
            $roomId = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;
            if ($roomId < 1) {
                throw new Exception('Invalid room_id');
            }
            $history = $this->bookingsModel->getRoomBookingHistory($roomId, 10);
            echo json_encode(['success' => true, 'bookings' => $history]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function rooms() {
        $this->requireLogin();
        header('Content-Type: application/json');
        try {
            $rooms = $this->roomsModel->listRooms();
            echo json_encode(['success' => true, 'rooms' => $rooms]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function createRoom() {
        $this->requireLogin();
        $level = $this->currentUserLevel();
        if (!in_array($level, [0, 1, 2, 4, 5, 6], true)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'You do not have permission to create rooms.']);
            exit;
        }

        header('Content-Type: application/json');
        try {
            $data = $this->getRoomCreatePostData();
            $id = $this->roomsModel->createRoom($data, (int)($_SESSION['id'] ?? 0));
            if ($id) {
                $this->logRoomModuleAction('create_room', (int)$id, [
                    'room_name' => (string)($data['room_name'] ?? ''),
                    'room_id' => (int)$id,
                ]);
            }
            echo json_encode(['success' => true, 'room_id' => $id]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function updateRoom() {
        $this->requireLogin();
        $level = $this->currentUserLevel();
        if (!in_array($level, [0, 1, 2, 4, 5, 6], true)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'You do not have permission to update rooms.']);
            exit;
        }

        header('Content-Type: application/json');
        try {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id < 1) {
                throw new Exception('Invalid room id.');
            }
            $data = $this->getRoomCreatePostData();
            $ok = $this->roomsModel->updateRoom($id, $data, (int)($_SESSION['id'] ?? 0));
            if ($ok) {
                $this->logRoomModuleAction('update_room', $id, [
                    'room_name' => (string)($data['room_name'] ?? ''),
                    'room_id' => $id,
                ]);
            }
            echo json_encode(['success' => $ok]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function deleteRoom() {
        $this->requireLogin();
        $level = $this->currentUserLevel();
        if (!in_array($level, [0, 1, 2, 4, 5, 6], true)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'You do not have permission to delete rooms.']);
            exit;
        }

        header('Content-Type: application/json');
        try {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id < 1) {
                throw new Exception('Invalid room id.');
            }
            $ok = $this->roomsModel->deleteRoom($id, (int)($_SESSION['id'] ?? 0));
            if ($ok) {
                $this->logRoomModuleAction('delete_room', $id, ['message' => 'Room deleted from room bookings module']);
            }
            echo json_encode(['success' => $ok]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    private function getRoomCreatePostData(): array {
        $required = ['room_name', 'room_code', 'capacity'];
        $missing = [];
        foreach ($required as $k) {
            if (!isset($_POST[$k]) || trim((string)$_POST[$k]) === '') {
                $missing[] = $k;
            }
        }

        if ($missing) {
            throw new Exception('Missing required fields: ' . implode(', ', $missing));
        }

        return [
            'room_name' => trim((string)$_POST['room_name']),
            'room_code' => trim((string)$_POST['room_code']),
            'capacity' => max(1, (int)($_POST['capacity'] ?? 1)),
            'status' => trim((string)($_POST['status'] ?? 'active')),
        ];
    }

    public function get() {
        $this->requireLogin();
        header('Content-Type: application/json');
        try {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($id < 1) {
                throw new Exception('Invalid id');
            }
            $row = $this->bookingsModel->getBookingById($id);
            if (!$row) {
                throw new Exception('Booking not found');
            }
            echo json_encode(['success' => true, 'booking' => $row]);
        } catch (Exception $e) {
            http_response_code(400);
            $msg = $e->getMessage();
            echo json_encode(['success' => false, 'message' => $msg, 'error_code' => $this->classifyBookingError($msg)]);
        }
        exit;
    }

    public function create() {
        $this->requireLogin();
        $level = $this->currentUserLevel();
        if (!in_array($level, [0, 1, 2, 4, 5, 6], true)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'You do not have permission to create room bookings.']);
            exit;
        }

        header('Content-Type: application/json');
        try {
            $data = $this->getBookingPostData();
            $id = $this->bookingsModel->createBooking($data, (int)($_SESSION['id'] ?? 0));
            if ($id) {
                $this->logRoomModuleAction('create_booking', (int)$id, [
                    'room_id' => (int)($data['room_id'] ?? 0),
                    'purpose' => (string)($data['purpose'] ?? ''),
                    'date_trip' => (string)($data['date_trip'] ?? ''),
                ]);
            }
            echo json_encode(['success' => true, 'booking_id' => $id]);
        } catch (Exception $e) {
            http_response_code(400);
            $msg = $e->getMessage();
            echo json_encode(['success' => false, 'message' => $msg, 'error_code' => $this->classifyBookingError($msg)]);
        }
        exit;
    }

    public function update() {
        $this->requireLogin();
        $level = $this->currentUserLevel();
        if (!in_array($level, [0, 1, 2, 4, 5, 6], true)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'You do not have permission to modify room bookings.']);
            exit;
        }

        header('Content-Type: application/json');
        try {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id < 1) {
                throw new Exception('Invalid id');
            }
            $existing = $this->bookingsModel->getBookingById($id);
            if (!$existing) {
                throw new Exception('Booking not found');
            }
            $merged = $existing;
            foreach ($_POST as $k => $v) {
                if ($k === 'id') continue;
                $merged[$k] = $v;
            }
            $ok = $this->bookingsModel->updateBooking($id, $merged, (int)($_SESSION['id'] ?? 0));
            if ($ok) {
                $this->logRoomModuleAction('update_booking', $id, [
                    'room_id' => (int)($merged['room_id'] ?? 0),
                    'purpose' => (string)($merged['purpose'] ?? ''),
                    'date_trip' => (string)($merged['date_trip'] ?? ''),
                ]);
            }
            echo json_encode(['success' => $ok]);
        } catch (Exception $e) {
            http_response_code(400);
            $msg = $e->getMessage();
            echo json_encode(['success' => false, 'message' => $msg, 'error_code' => $this->classifyBookingError($msg)]);
        }
        exit;
    }

    public function delete() {
        $this->requireLogin();
        $level = $this->currentUserLevel();
        if (!in_array($level, [0, 1, 2, 4, 5, 6], true)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'You do not have permission to delete room bookings.']);
            exit;
        }

        header('Content-Type: application/json');
        try {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id < 1) {
                throw new Exception('Invalid id');
            }
            $ok = $this->bookingsModel->deleteBooking($id, (int)($_SESSION['id'] ?? 0));
            if ($ok) {
                $this->logRoomModuleAction('delete_booking', $id, ['message' => 'Booking canceled from room bookings module']);
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
            'purpose',
            'attendees',
            'departure_expected',
            'return_expected',
            'room_id',
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

        $get = fn($k) => is_string($_POST[$k]) ? trim($_POST[$k]) : $_POST[$k];

        return [
            'date_trip' => $get('date_trip'),
            'date_requested' => $get('date_requested'),
            'purpose' => $get('purpose'),
            'attendees' => (int)$_POST['attendees'],
            'departure_expected' => $get('departure_expected'),
            'return_expected' => $get('return_expected'),
            'special_instructions' => $get('special_instructions') ?? '',
            'remarks' => $get('remarks') ?? '',
            'room_id' => (int)$_POST['room_id'],
        ];
    }
}
