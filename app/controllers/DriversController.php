<?php
session_start();
require_once '../app/core/Controller.php';
require_once '../app/models/CarDrivers.php';
if (file_exists(__DIR__ . '/../config.php')) {
    require_once __DIR__ . '/../config.php';
} elseif (file_exists(__DIR__ . '/../../app/config.php')) {
    require_once __DIR__ . '/../../app/config.php';
}

class DriversController extends Controller {
    private CarDrivers $driversModel;

    public function __construct() {
        $this->driversModel = new CarDrivers();
    }

    private function requireDriverWritePermission(): void {
        $level = $this->currentUserLevel();
        if (!in_array($level, [0, 1, 2, 4, 5, 6], true)) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'You do not have permission to modify drivers.']);
            exit;
        }
    }

    // AJAX: list drivers for dropdowns/modals
    public function listAjax() {
        $this->requireLogin();
        header('Content-Type: application/json');

        $status = isset($_GET['status']) ? (string)$_GET['status'] : null;
        $drivers = $this->driversModel->listDrivers($status);

        echo json_encode(['success' => true, 'drivers' => $drivers]);
        exit;
    }

    public function create() {
        $this->requireLogin();
        $this->requireDriverWritePermission();

        header('Content-Type: application/json');

        try {
            $actorUserId = (int)($_SESSION['id'] ?? 0);

            $driver_name = trim((string)($_POST['driver_name'] ?? ''));
            if ($driver_name === '') {
                throw new Exception('driver_name is required');
            }

            $status = isset($_POST['status']) ? (string)$_POST['status'] : 'active';

            $id = $this->driversModel->create([
                'driver_name' => $driver_name,
                'status' => $status,
            ], $actorUserId);

            echo json_encode(['success' => true, 'driver_id' => $id]);
            exit;
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function update() {
        $this->requireLogin();
        $this->requireDriverWritePermission();

        header('Content-Type: application/json');

        try {
            $actorUserId = (int)($_SESSION['id'] ?? 0);

            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $driver_name = trim((string)($_POST['driver_name'] ?? ''));
            if ($id < 1) {
                throw new Exception('id is required');
            }
            if ($driver_name === '') {
                throw new Exception('driver_name is required');
            }

            $status = isset($_POST['status']) ? (string)$_POST['status'] : 'active';

            $ok = $this->driversModel->update($id, [
                'driver_name' => $driver_name,
                'status' => $status,
            ], $actorUserId);

            echo json_encode(['success' => $ok, 'message' => $ok ? 'Updated' : 'No changes/invalid id']);
            exit;
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function delete() {
        $this->requireLogin();
        $this->requireDriverWritePermission();

        header('Content-Type: application/json');

        try {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id < 1) {
                throw new Exception('id is required');
            }

            $ok = $this->driversModel->delete($id);
            echo json_encode(['success' => $ok, 'message' => $ok ? 'Deleted' : 'Invalid id']);
            exit;
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }
}



