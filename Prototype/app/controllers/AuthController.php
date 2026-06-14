<?php
session_start();
require_once '../app/core/Controller.php';
require_once '../app/models/User.php';

class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $user = $this->userModel->findByUsername($username);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user'] = $user['username'];
                $_SESSION['user_level'] = $user['userLevel'];
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['position'] = $user['position'];
                $_SESSION['firstName'] = $user['firstName'];
                $_SESSION['lastName'] = $user['lastName'];
                $_SESSION['profile_picture'] = $user['profile_picture'] ?? 'default.png';
                $_SESSION['id'] = $user['id'];

                $this->redirect('index.php?controller=Auth&action=dashboard&wc=welcome');
            }

            $this->view('auth/login', ['error' => 'Invalid username or password']);
            return;
        }

        $this->view('auth/login');
    }

    public function dashboard() {
        $this->requireLogin();
        $content = $this->renderView('auth/dashboard', ['username' => $_SESSION['user']]);
        $this->view('layout/main', ['content' => $content]);
    }

    public function users() {
        $this->requireCoreUserAccess();
        $this->renderCoreUsersPage();
    }

    public function register() {
        $this->requireCoreUserAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?controller=Auth&action=users');
        }

        try {
            $data = $this->validatedCoreUserData();

            if ($data['password'] !== ($_POST['confirm_password'] ?? '')) {
                throw new Exception('Passwords do not match.');
            }

            if ($this->userModel->usernameExists($data['username'])) {
                throw new Exception('Username already exists.');
            }

            $data['profile_picture'] = $this->uploadProfilePicture();

            $this->userModel->create(
                $data['username'],
                $data['password'],
                $data['firstName'],
                $data['lastName'],
                $data['position'],
                $data['userLevel'],
                $data['profile_picture']
            );

            $uploader = trim(($_SESSION['firstName'] ?? '') . ' ' . ($_SESSION['lastName'] ?? '')) ?: 'System';
            $this->userModel->log($data['username'], $data['firstName'], $data['lastName'], $data['position'], $uploader, $data['userLevel']);

            $_SESSION['message'] = 'Core user created successfully.';
            $_SESSION['msg_type'] = 'success';
            $this->redirect('index.php?controller=Auth&action=users');
        } catch (Exception $e) {
            $this->renderCoreUsersPage(null, $e->getMessage());
        }
    }

    public function edit(int $id) {
        $this->requireCoreUserAccess();

        $editingUser = $this->userModel->findUserById($id);
        if (!$editingUser) {
            $_SESSION['message'] = 'Core user not found.';
            $_SESSION['msg_type'] = 'error';
            $this->redirect('index.php?controller=Auth&action=users');
        }

        $this->renderCoreUsersPage($editingUser);
    }

    public function update(int $id) {
        $this->requireCoreUserAccess();

        try {
            $existing = $this->userModel->findUserById($id);
            if (!$existing) {
                throw new Exception('Core user not found.');
            }

            $data = $this->validatedCoreUserData(false);

            if (!empty($data['password']) && $data['password'] !== ($_POST['confirm_password'] ?? '')) {
                throw new Exception('Passwords do not match.');
            }

            if ($this->userModel->usernameExists($data['username'], $id)) {
                throw new Exception('Username already exists.');
            }

            if ((int)$existing['userLevel'] === 0 && (int)$data['userLevel'] !== 0 && $this->userModel->countSuperAdmins() <= 1) {
                throw new Exception('Create another Super Admin before changing this account level.');
            }

            $uploaded = $this->uploadProfilePicture();
            $data['profile_picture'] = $uploaded ?: ($existing['profile_picture'] ?? null);
            $this->userModel->update($id, $data);

            if ((int)($_SESSION['id'] ?? 0) === $id) {
                $_SESSION['user'] = $data['username'];
                $_SESSION['user_level'] = $data['userLevel'];
                $_SESSION['position'] = $data['position'];
                $_SESSION['firstName'] = $data['firstName'];
                $_SESSION['lastName'] = $data['lastName'];
                $_SESSION['profile_picture'] = $data['profile_picture'] ?? 'default.png';
            }

            $_SESSION['message'] = 'Core user updated successfully.';
            $_SESSION['msg_type'] = 'success';
            $this->redirect('index.php?controller=Auth&action=users');
        } catch (Exception $e) {
            $editingUser = $this->userModel->findUserById($id);
            $this->renderCoreUsersPage($editingUser, $e->getMessage());
        }
    }

    public function delete(int $id) {
        $this->requireCoreUserAccess();

        $user = $this->userModel->findUserById($id);
        if (!$user) {
            $_SESSION['message'] = 'Core user not found.';
            $_SESSION['msg_type'] = 'error';
            $this->redirect('index.php?controller=Auth&action=users');
        }

        if ((int)($_SESSION['id'] ?? 0) === $id) {
            $_SESSION['message'] = 'You cannot delete your own core user account.';
            $_SESSION['msg_type'] = 'error';
            $this->redirect('index.php?controller=Auth&action=users');
        }

        if ((int)$user['userLevel'] === 0 && $this->userModel->countSuperAdmins() <= 1) {
            $_SESSION['message'] = 'Create another Super Admin before deleting this account.';
            $_SESSION['msg_type'] = 'error';
            $this->redirect('index.php?controller=Auth&action=users');
        }

        $this->userModel->delete($id);
        $_SESSION['message'] = 'Core user removed successfully.';
        $_SESSION['msg_type'] = 'success';
        $this->redirect('index.php?controller=Auth&action=users');
    }

    public function logout() {
        session_destroy();
        $this->redirect('index.php?controller=Auth&action=login');
    }

    private function renderCoreUsersPage($editingUser = null, $error = null) {
        $content = $this->renderView('auth/users', [
            'users' => $this->userModel->getAllUser(),
            'editingUser' => $editingUser,
            'error' => $error,
            'canCreateSuperAdmin' => $this->canCreateSuperAdmin(),
            'accessLevels' => $this->coreUserLevels(),
        ]);

        $this->view('layout/main', ['content' => $content]);
    }

    private function requireCoreUserAccess() {
        $this->requireLogin();

        if ($this->canManageCoreUsers()) {
            return;
        }

        $_SESSION['message'] = 'Only Super Admin can access Core Users.';
        $_SESSION['msg_type'] = 'error';
        $this->redirect('index.php?controller=Auth&action=dashboard');
    }

    private function canManageCoreUsers() {
        $level = (string)($_SESSION['user_level'] ?? '');

        if ($level === '0') {
            return true;
        }

        return $level === '1' && !$this->userModel->hasSuperAdmin();
    }

    private function canCreateSuperAdmin() {
        return !$this->userModel->hasSuperAdmin() || (string)($_SESSION['user_level'] ?? '') === '0';
    }

    private function coreUserLevels() {
        $levels = [
            1 => 'Admin',
            2 => 'Encoder',
            3 => 'Viewer',
        ];

        if ($this->canCreateSuperAdmin()) {
            return [0 => 'Super Admin'] + $levels;
        }

        return $levels;
    }

    private function validatedCoreUserData($requirePassword = true) {
        $data = [
            'username' => trim($_POST['username'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'firstName' => trim($_POST['firstName'] ?? ''),
            'lastName' => trim($_POST['lastName'] ?? ''),
            'position' => trim($_POST['position'] ?? ''),
            'userLevel' => (int)($_POST['user_level'] ?? 3),
            'profile_picture' => null,
        ];

        if ($data['username'] === '' || $data['firstName'] === '' || $data['lastName'] === '' || $data['position'] === '') {
            throw new Exception('First name, last name, position, and username are required.');
        }

        if ($requirePassword && $data['password'] === '') {
            throw new Exception('Password is required.');
        }

        if (!array_key_exists($data['userLevel'], $this->coreUserLevels())) {
            $data['userLevel'] = 3;
        }

        return $data;
    }

    private function uploadProfilePicture() {
        if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Profile picture upload failed.');
        }

        if ($_FILES['profile_picture']['size'] > 2 * 1024 * 1024) {
            throw new Exception('Profile picture must be 2MB or smaller.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $_FILES['profile_picture']['tmp_name']);
        finfo_close($finfo);

        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (!isset($extensions[$mimeType])) {
            throw new Exception('Profile picture must be a JPG, PNG, or WEBP image.');
        }

        $uploadDir = __DIR__ . '/../assets/profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = uniqid('core_user_', true) . '.' . $extensions[$mimeType];
        $targetFile = $uploadDir . $fileName;

        if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
            throw new Exception('Failed to save profile picture.');
        }

        return $fileName;
    }
}
