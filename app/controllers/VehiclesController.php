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

            // handle optional image upload
            $imageFilename = null;
            if (isset($_FILES['vehicle_image']) && is_uploaded_file($_FILES['vehicle_image']['tmp_name'])) {
                $uploadDir = __DIR__ . '/../../uploads/vehicle/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $ext = strtolower(pathinfo($_FILES['vehicle_image']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','gif','webp'];
                if (!in_array($ext, $allowed, true)) throw new Exception('Unsupported image type. Allowed: jpg,jpeg,png,gif,webp');
                if ($_FILES['vehicle_image']['size'] > 2 * 1024 * 1024) throw new Exception('Image too large (max 2MB)');
                $imageFilename = uniqid('veh_', true) . '.' . $ext;
                $dest = $uploadDir . $imageFilename;
                if (!move_uploaded_file($_FILES['vehicle_image']['tmp_name'], $dest)) {
                    throw new Exception('Failed to move uploaded file');
                }
            }

            $id = $this->vehiclesModel->createVehicle([
                'plate_number' => $plate,
                'vehicle_name' => $name,
                'capacity' => $capacity,
                'status' => $status,
                'image_filename' => $imageFilename,
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

            // handle optional image upload (replace)
            $imageFilename = null;
            if (isset($_FILES['vehicle_image']) && is_uploaded_file($_FILES['vehicle_image']['tmp_name'])) {
                $uploadDir = __DIR__ . '/../../uploads/vehicle/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $ext = strtolower(pathinfo($_FILES['vehicle_image']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','gif','webp'];
                if (!in_array($ext, $allowed, true)) throw new Exception('Unsupported image type. Allowed: jpg,jpeg,png,gif,webp');
                if ($_FILES['vehicle_image']['size'] > 2 * 1024 * 1024) throw new Exception('Image too large (max 2MB)');
                $imageFilename = uniqid('veh_', true) . '.' . $ext;
                $dest = $uploadDir . $imageFilename;
                if (!move_uploaded_file($_FILES['vehicle_image']['tmp_name'], $dest)) {
                    throw new Exception('Failed to move uploaded file');
                }
            }

            $payload = [
                'plate_number' => $plate,
                'vehicle_name' => $name,
                'capacity' => $capacity,
                'status' => $status,
            ];
            if ($imageFilename !== null) $payload['image_filename'] = $imageFilename;

            $ok = $this->vehiclesModel->updateVehicle($id, $payload, $actorUserId);

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
        // Ensure full URL for image preview if available
        $baseUrl = getBaseUrlForUploads();
        foreach ($vehicles as &$v) {
            if (!empty($v['image_filename'])) {
                $v['image_url'] = $baseUrl . 'uploads/vehicle/' . $v['image_filename'];
            } else {
                $v['image_url'] = $baseUrl . 'uploads/vehicle/default.png';
            }
        }
        echo json_encode(['success' => true, 'vehicles' => $vehicles]);
        exit;
    }

    public function listAllAjax() {
        $this->requireLogin();
        header('Content-Type: application/json');

        $status = isset($_GET['status']) ? (string)$_GET['status'] : null;
        $vehicles = $this->vehiclesModel->listVehicles($status);
        // Add image_url for each vehicle for convenient previews
        $baseUrl = getBaseUrlForUploads();
        foreach ($vehicles as &$v) {
            if (!empty($v['image_filename'])) {
                $v['image_url'] = $baseUrl . 'uploads/vehicle/' . $v['image_filename'];
            } else {
                $v['image_url'] = $baseUrl . 'uploads/vehicle/default.png';
            }
        }
        echo json_encode(['success' => true, 'vehicles' => $vehicles]);
        exit;
    }
}

// helper may be used to build upload URLs (best-effort)
function getBaseUrlForUploads() {
    // Try to compute base from script location
    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
    // uploads are stored at project_root/uploads, which is the parent of the Public script dir
    $projectRoot = dirname($script);
    return rtrim($proto . '://' . $host . $projectRoot, '/') . '/';
}


