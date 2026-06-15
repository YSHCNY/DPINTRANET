<?php
session_start();
require_once '../app/core/Controller.php';
require_once '../app/models/UserModel.php';

class StandardUsersController extends Controller {
    private $userModel;
    private $uploadDir;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->uploadDir = __DIR__ . '/../uploads/standard_users/';
    }

    public function index() {
        $this->requireAnyRole([0, 1], 'Standard Users are available to Super Admin and Admin only.');

        $content = $this->renderView('standard_users/index', $this->viewData());

        $this->view('layout/main', ['content' => $content]);
    }

    public function edit($id) {
        $this->requireAnyRole([0, 1], 'Standard Users are available to Super Admin and Admin only.');

        $editingUser = $this->userModel->getUserById($id);
        if (!$editingUser) {
            $_SESSION['message'] = 'Standard user not found.';
            $_SESSION['msg_type'] = 'error';
            $this->redirect('index.php?controller=StandardUsers&action=index');
        }

        $content = $this->renderView('standard_users/index', $this->viewData($editingUser));

        $this->view('layout/main', ['content' => $content]);
    }

    public function store() {
        $this->requireAnyRole([0, 1], 'Standard Users are available to Super Admin and Admin only.');

        try {
            $data = $this->validatedData();

            if (empty($data['password'])) {
                throw new Exception('Password is required for new users.');
            }

            if ($this->userModel->findByUsername($data['username'])) {
                throw new Exception('Username already exists.');
            }

            if ($this->userModel->findByEmail($data['email'])) {
                throw new Exception('Email already exists.');
            }

            $data['avatar'] = $this->uploadAvatar();
            $this->userModel->createStandardUser($data);

            $_SESSION['message'] = 'Standard user created successfully.';
            $_SESSION['msg_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['message'] = $e->getMessage();
            $_SESSION['msg_type'] = 'error';
        }

        $this->redirect('index.php?controller=StandardUsers&action=index');
    }

    public function update($id) {
        $this->requireAnyRole([0, 1], 'Standard Users are available to Super Admin and Admin only.');

        try {
            $existing = $this->userModel->getUserById($id);
            if (!$existing) {
                throw new Exception('Standard user not found.');
            }

            $data = $this->validatedData(false);

            if ($this->userModel->findByUsername($data['username'], $id)) {
                throw new Exception('Username already exists.');
            }

            if ($this->userModel->findByEmail($data['email'], $id)) {
                throw new Exception('Email already exists.');
            }

            $uploadedAvatar = $this->uploadAvatar();
            $data['avatar'] = $uploadedAvatar ?: ($existing['avatar'] ?? null);

            $this->userModel->updateStandardUser($id, $data);

            $_SESSION['message'] = 'Standard user updated successfully.';
            $_SESSION['msg_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['message'] = $e->getMessage();
            $_SESSION['msg_type'] = 'error';
        }

        $this->redirect('index.php?controller=StandardUsers&action=index');
    }

    public function delete($id) {
        $this->requireAnyRole([0, 1], 'Standard Users are available to Super Admin and Admin only.');

        try {
            $this->userModel->deleteStandardUser($id);
            $_SESSION['message'] = 'Standard user deleted successfully.';
            $_SESSION['msg_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['message'] = 'Unable to delete user. This record may already be used in correspondence history.';
            $_SESSION['msg_type'] = 'error';
        }

        $this->redirect('index.php?controller=StandardUsers&action=index');
    }

    private function validatedData($requirePassword = true) {
        $data = [
            'username' => trim($_POST['username'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'firstName' => trim($_POST['firstName'] ?? ''),
            'lastName' => trim($_POST['lastName'] ?? ''),
            'middleName' => trim($_POST['middleName'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'position' => trim($_POST['position'] ?? ''),
            'department' => trim($_POST['department'] ?? ''),
            'role' => $this->normalizeOption('role', trim($_POST['role'] ?? '')),
            'status' => $_POST['status'] ?? 'active',
            'is_portal_user' => isset($_POST['is_portal_user']) ? 1 : 0,
            'pin_code' => trim($_POST['pin_code'] ?? ''),
            'avatar' => null,
        ];

        if ($data['username'] === '' || $data['firstName'] === '' || $data['lastName'] === '') {
            throw new Exception('Username, first name, and last name are required.');
        }

        if ($requirePassword && $data['password'] === '') {
            throw new Exception('Password is required.');
        }

        if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Enter a valid email address.');
        }

        $data['status'] = $this->normalizeOption('status', $data['status']);

        return $data;
    }

    private function viewData($editingUser = null) {
        $roleOptions = $this->userModel->getEnumValues('role');
        $statusOptions = $this->userModel->getEnumValues('status');

        return [
            'users' => $this->userModel->getAllStandardUsers(),
            'editingUser' => $editingUser,
            'roleOptions' => $roleOptions ?: ['user'],
            'statusOptions' => $statusOptions ?: ['active', 'inactive'],
        ];
    }

    private function normalizeOption($column, $value) {
        $options = $this->userModel->getEnumValues($column);

        if (empty($options)) {
            return $value;
        }

        if (in_array($value, $options, true)) {
            return $value;
        }

        return $options[0];
    }

    private function uploadAvatar() {
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Avatar upload failed.');
        }

        if ($_FILES['avatar']['size'] > 3 * 1024 * 1024) {
            throw new Exception('Avatar must be 3MB or smaller.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $_FILES['avatar']['tmp_name']);
        finfo_close($finfo);

        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (!isset($extensions[$mimeType])) {
            throw new Exception('Avatar must be a JPG, PNG, or WEBP image.');
        }

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        $fileName = uniqid('standard_user_', true) . '.' . $extensions[$mimeType];
        $destination = $this->uploadDir . $fileName;

        if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $destination)) {
            throw new Exception('Failed to save avatar image.');
        }

        return $fileName;
    }
}
