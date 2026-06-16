<?php
session_start();
require_once '../app/core/Controller.php';
require_once '../app/models/CarVehicles.php';

class VehiclesController extends Controller {

    private CarVehicles $vehiclesModel;

    public function __construct() {
        $this->vehiclesModel = new CarVehicles();
    }

    private function requireVehicleWritePermission(): void {
        $level = $this->currentUserLevel();
        if (!in_array($level, [0, 1, 2], true)) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'You do not have permission to modify vehicles.']);
            exit;
        }
    }

    public function create() {
        $this->requireLogin();
        $this->requireVehicleWritePermission();

        header('Content-Type: application/json');

        try {
            $plate = trim($_POST['plate_number'] ?? '');
            $name = trim($_POST['vehicle_name'] ?? '');
            $capacity = (int)($_POST['capacity'] ?? 0);
            $status = trim($_POST['status'] ?? 'active');

            if ($plate === '' || $name === '' || $capacity < 1) {
                throw new Exception('Plate number, vehicle name, and capacity are required.');
            }

            $allowedStatus = ['active', 'inactive'];
            if (!in_array($status, $allowedStatus, true)) {
                $status = 'active';
            }

            $id = $this->vehiclesModel->createVehicle([
                'plate_number' => $plate,
                'vehicle_name' => $name,
                'capacity' => $capacity,
                'status' => $status,
            ], (int)($_SESSION['id'] ?? 0));

            echo json_encode(['success' => true, 'vehicle_id' => $id]);
            exit;
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function update() {
        $this->requireLogin();
        $this->requireVehicleWritePermission();

        header('Content-Type: application/json');

        try {
            $actorUserId = (int)($_SESSION['id'] ?? 0);

            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $plate = trim((string)($_POST['plate_number'] ?? ''));
            $name = trim((string)($_POST['vehicle_name'] ?? ''));
            $capacity = (int)($_POST['capacity'] ?? 0);
            $status = isset($_POST['status']) ? (string)$_POST['status'] : 'active';

            if ($id < 1) {
                throw new Exception('id is required');
            }
            if ($plate === '' || $name === '' || $capacity < 1) {
                throw new Exception('Plate number, vehicle name, and capacity are required.');
            }

            $allowedStatus = ['active', 'inactive'];
            if (!in_array($status, $allowedStatus, true)) {
                $status = 'active';
            }

            $ok = $this->vehiclesModel->updateVehicle($id, [
                'plate_number' => $plate,
                'vehicle_name' => $name,
                'capacity' => $capacity,
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
        $this->requireVehicleWritePermission();

        header('Content-Type: application/json');

        try {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id < 1) {
                throw new Exception('id is required');
            }

            $ok = $this->vehiclesModel->disableVehicle($id, (int)($_SESSION['id'] ?? 0));
            echo json_encode(['success' => $ok, 'message' => $ok ? 'Disabled' : 'Invalid id']);
            exit;
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function listAjax() {
        $this->requireLogin();
        header('Content-Type: application/json');

        // Keep existing behavior: calendar dropdown needs only active vehicles
        $vehicles = $this->vehiclesModel->getActiveVehicles();
        echo json_encode(['success' => true, 'vehicles' => $vehicles]);
        exit;
    }

    public function listAllAjax() {
        $this->requireLogin();
        header('Content-Type: application/json');

        $status = isset($_GET['status']) ? (string)$_GET['status'] : null;
        $vehicles = $this->vehiclesModel->listVehicles($status);
        echo json_encode(['success' => true, 'vehicles' => $vehicles]);
        exit;
    }
}


