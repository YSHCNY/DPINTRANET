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
            $employee_id = trim((string)($_POST['employee_id'] ?? ''));
            $first_name = trim((string)($_POST['first_name'] ?? ''));
            $last_name = trim((string)($_POST['last_name'] ?? ''));
            $mobile_number = trim((string)($_POST['mobile_number'] ?? ''));
            $email = trim((string)($_POST['email'] ?? ''));
            $license_number = trim((string)($_POST['license_number'] ?? ''));
            $license_class = trim((string)($_POST['license_class'] ?? ''));
            $license_expiry = trim((string)($_POST['license_expiry'] ?? ''));
            $status = isset($_POST['status']) ? (string)$_POST['status'] : 'active';

            if ($first_name === '') {
                throw new Exception('first_name is required');
            }
            if ($last_name === '') {
                throw new Exception('last_name is required');
            }
            if ($license_expiry !== '' && strtotime($license_expiry) === false) {
                throw new Exception('license_expiry must be a valid date');
            }

            $id = $this->driversModel->create([
                'driver_name' => $driver_name,
                'employee_id' => $employee_id !== '' ? $employee_id : null,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'mobile_number' => $mobile_number !== '' ? $mobile_number : null,
                'email' => $email !== '' ? $email : null,
                'license_number' => $license_number !== '' ? $license_number : null,
                'license_class' => $license_class !== '' ? $license_class : null,
                'license_expiry' => $license_expiry !== '' ? date('Y-m-d', strtotime($license_expiry)) : null,
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
            $employee_id = trim((string)($_POST['employee_id'] ?? ''));
            $first_name = trim((string)($_POST['first_name'] ?? ''));
            $last_name = trim((string)($_POST['last_name'] ?? ''));
            $mobile_number = trim((string)($_POST['mobile_number'] ?? ''));
            $email = trim((string)($_POST['email'] ?? ''));
            $license_number = trim((string)($_POST['license_number'] ?? ''));
            $license_class = trim((string)($_POST['license_class'] ?? ''));
            $license_expiry = trim((string)($_POST['license_expiry'] ?? ''));
            if ($id < 1) {
                throw new Exception('id is required');
            }
            if ($first_name === '') {
                throw new Exception('first_name is required');
            }
            if ($last_name === '') {
                throw new Exception('last_name is required');
            }
            if ($license_expiry !== '' && strtotime($license_expiry) === false) {
                throw new Exception('license_expiry must be a valid date');
            }

            $status = isset($_POST['status']) ? (string)$_POST['status'] : 'active';

            $ok = $this->driversModel->update($id, [
                'driver_name' => $driver_name,
                'employee_id' => $employee_id !== '' ? $employee_id : null,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'mobile_number' => $mobile_number !== '' ? $mobile_number : null,
                'email' => $email !== '' ? $email : null,
                'license_number' => $license_number !== '' ? $license_number : null,
                'license_class' => $license_class !== '' ? $license_class : null,
                'license_expiry' => $license_expiry !== '' ? date('Y-m-d', strtotime($license_expiry)) : null,
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



