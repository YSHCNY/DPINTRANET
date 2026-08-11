<?php
session_start();
require_once '../app/core/Controller.php';
require_once '../app/core/Database.php';
require_once __DIR__ . '/../Services/EmployeeMobilizationService.php';

use App\Services\EmployeeMobilizationService;

class EmployeeMobilizationController extends Controller {
    private EmployeeMobilizationService $mobilizationService;

    public function __construct() {
        $this->mobilizationService = new EmployeeMobilizationService();
    }

    private function logMobilizationAction(string $action, int $movementId, array $context = []): void {
        $actorId = (int)($_SESSION['id'] ?? $_SESSION['user_id'] ?? 0);
        $actorName = (string)($_SESSION['user'] ?? ($actorId > 0 ? $actorId : 'system'));

        $details = [];
        $targetRef = $movementId > 0 ? "#{$movementId}" : 'new movement';

        if (!empty($context['employee_id'])) {
            $details[] = 'Employee ID: ' . $context['employee_id'];
        }
        if (!empty($context['movement_date'])) {
            $details[] = 'Date: ' . $context['movement_date'];
        }
        if (!empty($context['movement_type'])) {
            $details[] = 'Type: ' . $context['movement_type'];
        }
        if (!empty($context['message'])) {
            $details[] = $context['message'];
        }

        $description = match ($action) {
            'create' => 'Created employee mobilization ' . $targetRef . ($details ? ' • ' . implode(' • ', $details) : ''),
            'update' => 'Updated employee mobilization ' . $targetRef . ($details ? ' • ' . implode(' • ', $details) : ''),
            'complete' => 'Completed employee mobilization ' . $targetRef . ($details ? ' • ' . implode(' • ', $details) : ''),
            'cancel' => 'Canceled employee mobilization ' . $targetRef . ($details ? ' • ' . implode(' • ', $details) : ''),
            'delete' => 'Deleted employee mobilization ' . $targetRef . ($details ? ' • ' . implode(' • ', $details) : ''),
            default => 'Employee mobilization action ' . $action . ' for ' . $targetRef,
        };

        try {
            $db = Database::connect();
            $stmt = $db->prepare("INSERT INTO systemLogs (userName, logDesc, module, logDate) VALUES (?, ?, ?, ?)");
            $stmt->execute([$actorName, $description, 'Employee Mobilization', date('Y-m-d H:i:s')]);
        } catch (Throwable $e) {
            // Keep the flow intact even if logging fails.
        }
    }

    public function calendar() {
        $this->requireLogin();
        $content = $this->renderView('employee_mobilizations/dashboard', $this->getDashboardViewData());
        $this->view('layout/main', ['content' => $content]);
    }

    public function dashboard() {
        $this->requireLogin();
        $content = $this->renderView('employee_mobilizations/dashboard', $this->getDashboardViewData());
        $this->view('layout/main', ['content' => $content]);
    }

    public function summary() {
        $this->requireLogin();
        header('Content-Type: application/json');

        try {
            $summary = $this->mobilizationService->getDashboardSummary();
            echo json_encode(['success' => true, 'summary' => $summary]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function monthlyEvents() {
        $this->requireLogin();
        header('Content-Type: application/json');

        try {
            // Return all mobilization and demobilization events, without month filtering.
            $events = $this->mobilizationService->getAllCalendarEvents();

            echo json_encode(['success' => true, 'events' => $events]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function dailyEvents() {
        $this->requireLogin();
        header('Content-Type: application/json');

        try {
            $date = trim((string)($_GET['date'] ?? ''));
            if ($date === '') {
                throw new Exception('date is required');
            }

            $events = $this->mobilizationService->getCalendarEvents($date, $date);
            echo json_encode(['success' => true, 'events' => $events]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function employeeHistory() {
        $this->requireLogin();
        header('Content-Type: application/json');

        try {
            $employeeId = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;
            if ($employeeId < 1) {
                throw new Exception('employee_id is required');
            }

            $events = $this->mobilizationService->getEmployeeHistory($employeeId);
            echo json_encode(['success' => true, 'events' => $events]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function upcoming() {
        $this->requireLogin();
        header('Content-Type: application/json');

        try {
            $movements = $this->mobilizationService->getUpcomingMovements();
            echo json_encode(['success' => true, 'movements' => $movements]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function today() {
        $this->requireLogin();
        header('Content-Type: application/json');

        try {
            $movements = $this->mobilizationService->getTodayMovements();
            echo json_encode(['success' => true, 'movements' => $movements]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function scheduleMobilization() {
        $this->requireLogin();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        try {
            $movementIdParam = isset($_POST['movement_id']) ? (int)$_POST['movement_id'] : 0;
            $data = $this->getMovementDataFromPost();

            if ($movementIdParam > 0) {
                $ok = $this->mobilizationService->updateMovement(
                    $movementIdParam,
                    $data['employee_id'],
                    'Mobilization',
                    $data['movement_date'],
                    $data['remarks'],
                    (int)($_SESSION['id'] ?? 0)
                );
                if ($ok) {
                    $this->logMobilizationAction('update', $movementIdParam, [
                        'employee_id' => $data['employee_id'],
                        'movement_date' => $data['movement_date'],
                        'movement_type' => 'Mobilization',
                    ]);
                }
                echo json_encode(['success' => (bool)$ok, 'movement_id' => $movementIdParam]);
            } else {
                $movementId = $this->mobilizationService->scheduleMobilization(
                    $data['employee_id'],
                    $data['movement_date'],
                    $data['remarks'],
                    $data['created_by']
                );
                if ($movementId) {
                    $this->logMobilizationAction('create', (int)$movementId, [
                        'employee_id' => $data['employee_id'],
                        'movement_date' => $data['movement_date'],
                        'movement_type' => 'Mobilization',
                    ]);
                }
                echo json_encode(['success' => true, 'movement_id' => $movementId]);
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function scheduleDemobilization() {
        $this->requireLogin();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        try {
            $movementIdParam = isset($_POST['movement_id']) ? (int)$_POST['movement_id'] : 0;
            $data = $this->getMovementDataFromPost();

            if ($movementIdParam > 0) {
                $ok = $this->mobilizationService->updateMovement(
                    $movementIdParam,
                    $data['employee_id'],
                    'Demobilization',
                    $data['movement_date'],
                    $data['remarks'],
                    (int)($_SESSION['id'] ?? 0)
                );
                if ($ok) {
                    $this->logMobilizationAction('update', $movementIdParam, [
                        'employee_id' => $data['employee_id'],
                        'movement_date' => $data['movement_date'],
                        'movement_type' => 'Demobilization',
                    ]);
                }
                echo json_encode(['success' => (bool)$ok, 'movement_id' => $movementIdParam]);
            } else {
                $movementId = $this->mobilizationService->scheduleDemobilization(
                    $data['employee_id'],
                    $data['movement_date'],
                    $data['remarks'],
                    $data['created_by']
                );
                if ($movementId) {
                    $this->logMobilizationAction('create', (int)$movementId, [
                        'employee_id' => $data['employee_id'],
                        'movement_date' => $data['movement_date'],
                        'movement_type' => 'Demobilization',
                    ]);
                }
                echo json_encode(['success' => true, 'movement_id' => $movementId]);
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function completeMovement() {
        $this->requireLogin();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        try {
            $movementId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($movementId < 1) {
                throw new Exception('Movement id is required');
            }

            $ok = $this->mobilizationService->completeMovement($movementId, (int)($_SESSION['id'] ?? 0));
            if ($ok) {
                $this->logMobilizationAction('complete', $movementId, ['message' => 'Movement completed']);
            }
            echo json_encode(['success' => $ok]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function cancelMovement() {
        $this->requireLogin();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        try {
            $movementId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($movementId < 1) {
                throw new Exception('Movement id is required');
            }

            $ok = $this->mobilizationService->cancelMovement($movementId, (int)($_SESSION['id'] ?? 0));
            if ($ok) {
                $this->logMobilizationAction('cancel', $movementId, ['message' => 'Movement canceled']);
            }
            echo json_encode(['success' => $ok]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function deleteMovement() {
        $this->requireLogin();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        try {
            $movementId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($movementId < 1) {
                throw new Exception('Movement id is required');
            }

            $ok = $this->mobilizationService->deleteMovement($movementId);
            if ($ok) {
                $this->logMobilizationAction('delete', $movementId, ['message' => 'Movement deleted']);
            }
            echo json_encode(['success' => $ok]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    private function getDashboardViewData(): array {
        $summary = $this->mobilizationService->getDashboardSummary();
        $upcomingMovements = $this->mobilizationService->getUpcomingMovements();
        $todayMovements = $this->mobilizationService->getTodayMovements();
        $activeEmployees = $this->mobilizationService->getActiveEmployees();

        $now = new DateTime();
        $monthStart = $now->format('Y-m-01');
        $monthEnd = (new DateTime($monthStart))->format('Y-m-t');
        $calendarEvents = $this->mobilizationService->getCalendarEvents($monthStart, $monthEnd);

        $calendarEventsByDay = [];
        foreach ($calendarEvents as $event) {
            $dayKey = substr((string)($event['movement_date'] ?? ''), 0, 10);
            if ($dayKey !== '') {
                $calendarEventsByDay[$dayKey][] = $event;
            }
        }

        $activityItems = [];
        foreach (array_merge($upcomingMovements, $todayMovements) as $movement) {
            if (!empty($movement['employee_name']) || !empty($movement['employee_id'])) {
                $activityItems[] = $movement;
            }
        }

        $activityItems = array_slice($activityItems, 0, 3);
        $focusItems = array_slice($upcomingMovements, 0, 3);

        return [
            'summary' => $summary,
            'scheduledCount' => (int)($summary['upcoming_count'] ?? 0),
            'completedCount' => (int)($summary['status_counts']['Completed'] ?? 0),
            'pendingCount' => (int)($summary['upcoming_count'] ?? 0),
            'activeTeamsCount' => count($activeEmployees),
            'monthLabel' => $now->format('F Y'),
            'monthStart' => $monthStart,
            'calendarEventsByDay' => $calendarEventsByDay,
            'activityItems' => $activityItems,
            'focusItems' => $focusItems,
            'todayCount' => (int)($summary['today_count'] ?? 0),
        ];
    }

    private function getMovementDataFromPost(): array {
        $employeeId = isset($_POST['employee_id']) ? (int)$_POST['employee_id'] : 0;
        $movementDate = trim((string)($_POST['movement_date'] ?? ''));
        $remarks = trim((string)($_POST['remarks'] ?? ''));
        $createdBy = isset($_SESSION['id']) ? (int)$_SESSION['id'] : null;

        if ($employeeId < 1) {
            throw new Exception('employee_id is required');
        }
        if ($movementDate === '') {
            throw new Exception('movement_date is required');
        }

        return [
            'employee_id' => $employeeId,
            'movement_date' => $movementDate,
            'remarks' => $remarks !== '' ? $remarks : null,
            'created_by' => $createdBy,
        ];
    }
}
