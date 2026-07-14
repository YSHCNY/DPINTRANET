<?php
session_start();
require_once '../app/core/Controller.php';
require_once '../app/models/User.php';
require_once '../app/Services/PasswordResetService.php';

class AuthController extends Controller {
    private $userModel;
    private $passwordResetService;

    public function __construct() {
        $this->userModel = new User();
        $this->passwordResetService = new PasswordResetService($this->userModel);
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

    public function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $identifier = trim($_POST['identifier'] ?? '');
            $email = trim($_POST['email'] ?? '');

            $result = $this->passwordResetService->startReset($identifier, $email);

            $_SESSION['password_reset_identifier'] = $identifier;
            $_SESSION['password_reset_email'] = $email;
            $_SESSION['password_reset_user_id'] = null;

            $user = $this->userModel->findByIdentifier($identifier);
            if ($user && strtolower((string)($user['email'] ?? '')) === strtolower($email)) {
                $_SESSION['password_reset_user_id'] = (int) $user['id'];
            }

            $_SESSION['portal_message'] = $result['message'];
            $_SESSION['portal_msg_type'] = 'info';
            $this->redirect('index.php?controller=Auth&action=verifyOtp');
        }

        $this->view('auth/forgot_password');
    }

    public function verifyOtp() {
        $userId = (int)($_SESSION['password_reset_user_id'] ?? 0);
        if ($userId <= 0) {
            $_SESSION['portal_message'] = 'Please start the password reset process again.';
            $_SESSION['portal_msg_type'] = 'error';
            $this->redirect('index.php?controller=Auth&action=forgotPassword');
        }

        $message = $_SESSION['portal_message'] ?? null;
        $type = $_SESSION['portal_msg_type'] ?? 'info';
        unset($_SESSION['portal_message'], $_SESSION['portal_msg_type']);
        $remainingAttempts = (int)($_SESSION['password_reset_remaining_attempts'] ?? 5);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $otp = trim($_POST['otp'] ?? '');
            $result = $this->passwordResetService->verifyOtp($userId, $otp);
            $remainingAttempts = (int)($result['remainingAttempts'] ?? $remainingAttempts);
            $_SESSION['password_reset_remaining_attempts'] = $remainingAttempts;
            $_SESSION['portal_message'] = $result['message'];
            $_SESSION['portal_msg_type'] = $result['success'] ? 'success' : 'error';

            if ($result['success']) {
                $_SESSION['password_reset_verified'] = true;
                $this->redirect('index.php?controller=Auth&action=resetPassword');
            }

            $this->view('auth/verify_otp', ['message' => $result['message'], 'msgType' => $result['success'] ? 'success' : 'error', 'remainingAttempts' => $remainingAttempts]);
            return;
        }

        $this->view('auth/verify_otp', ['message' => $message, 'msgType' => $type, 'remainingAttempts' => $remainingAttempts]);
    }

    public function resendOtp() {
        $identifier = $_SESSION['password_reset_identifier'] ?? '';
        $email = $_SESSION['password_reset_email'] ?? '';
        $result = $this->passwordResetService->startReset($identifier, $email);

        $_SESSION['portal_message'] = $result['message'];
        $_SESSION['portal_msg_type'] = 'info';
        $this->redirect('index.php?controller=Auth&action=verifyOtp');
    }

    public function resetPassword() {
        if (empty($_SESSION['password_reset_verified']) || empty($_SESSION['password_reset_user_id'])) {
            $_SESSION['portal_message'] = 'Please verify your code before resetting your password.';
            $_SESSION['portal_msg_type'] = 'error';
            $this->redirect('index.php?controller=Auth&action=forgotPassword');
        }

        $message = $_SESSION['portal_message'] ?? null;
        $type = $_SESSION['portal_msg_type'] ?? 'info';
        unset($_SESSION['portal_message'], $_SESSION['portal_msg_type']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if ($newPassword === '' || $confirmPassword === '') {
                $this->view('auth/reset_password', ['message' => 'Please complete both password fields.', 'msgType' => 'error']);
                return;
            }

            if ($newPassword !== $confirmPassword) {
                $this->view('auth/reset_password', ['message' => 'Passwords do not match.', 'msgType' => 'error']);
                return;
            }

            $userId = (int)$_SESSION['password_reset_user_id'];
            $result = $this->passwordResetService->resetPassword($userId, $newPassword);
            if ($result['success']) {
                session_regenerate_id(true);
                unset(
                    $_SESSION['password_reset_user_id'],
                    $_SESSION['password_reset_identifier'],
                    $_SESSION['password_reset_email'],
                    $_SESSION['password_reset_verified'],
                    $_SESSION['password_reset_remaining_attempts']
                );
                $_SESSION['portal_message'] = $result['message'];
                $_SESSION['portal_msg_type'] = 'success';
                $this->redirect('index.php?controller=Auth&action=login');
            }

            $this->view('auth/reset_password', ['message' => $result['message'], 'msgType' => 'error']);
            return;
        }

        $this->view('auth/reset_password', ['message' => $message, 'msgType' => $type]);
    }

    public function dashboard() {
        $this->requireLogin();

        require_once '../app/models/correspondence.php';
        require_once '../app/models/Files.php';
        require_once '../app/models/CarBookings.php';
        require_once '../app/models/RoomBookings.php';
        require_once '../app/models/CarVehicles.php';
        require_once '../app/models/Rooms.php';

        $correspondenceModel = new CorrespondenceModel();
        $fileModel = new FileModel();
        $carBookings = new CarBookings();
        $roomBookings = new RoomBookings();
        $carVehicles = new CarVehicles();
        $roomsModel = new Rooms();

        $correspondenceMetrics = $correspondenceModel->getDashboardMetrics();
        $recentDocuments = $correspondenceModel->getRecentDocuments(6);
        $latestFiles = $fileModel->getLatestUpdatedFiles(6);

        $fileMetrics = [
            'total' => $fileModel->countFiles(),
            'recent30Days' => $fileModel->countRecentFiles(30),
        ];

        $carMetrics = [
            'activeVehicles' => $carVehicles->countActiveVehicles(),
            'scheduledBookings' => $carBookings->countScheduledBookings(),
        ];

        $roomMetrics = [
            'activeRooms' => $roomsModel->countActiveRooms(),
            'scheduledBookings' => $roomBookings->countScheduledBookings(),
        ];

        $content = $this->renderView('auth/dashboard', [
            'username' => $_SESSION['user'],
            'firstName' => $_SESSION['firstName'] ?? 'User',
            'correspondenceMetrics' => $correspondenceMetrics,
            'recentDocuments' => $recentDocuments,
            'latestFiles' => $latestFiles,
            'fileMetrics' => $fileMetrics,
            'carMetrics' => $carMetrics,
            'roomMetrics' => $roomMetrics,
        ]);
        $this->view('layout/main', ['content' => $content]);
    }

    public function dashboardEvents() {
        $this->requireLogin();
        require_once '../app/models/CarBookings.php';
        require_once '../app/models/RoomBookings.php';

        $start = $_GET['start'] ?? '';
        $end = $_GET['end'] ?? '';
        $type = strtolower(trim($_GET['type'] ?? 'all'));

        $carBookings = new CarBookings();
        $roomBookings = new RoomBookings();
        $events = [];

        if ($type === 'all' || $type === 'car') {
            $carEvents = $carBookings->getCalendarEvents($start, $end);
            foreach ($carEvents as $event) {
                $event['id'] = 'car-' . $event['id'];
                $event['extendedProps']['source'] = 'Vehicle';
                $event['extendedProps']['booking_type'] = 'Car Booking';
                $event['extendedProps']['module_route'] = 'index.php?controller=CarBookings&action=calendar';
                $events[] = $event;
            }
        }

        if ($type === 'all' || $type === 'room') {
            $roomEvents = $roomBookings->getCalendarEvents($start, $end);
            foreach ($roomEvents as $event) {
                $event['id'] = 'room-' . $event['id'];
                $event['extendedProps']['source'] = 'Room';
                $event['extendedProps']['booking_type'] = 'Room Booking';
                $event['extendedProps']['module_route'] = 'index.php?controller=RoomBookings&action=calendar';
                $events[] = $event;
            }
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'events' => $events]);
        exit;
    }

    public function profile() {
        $this->requireLogin();

        $userId = (int)($_SESSION['id'] ?? 0);
        $user = $this->userModel->findUserById($userId);

        if (!$user) {
            $_SESSION['message'] = 'Profile not found.';
            $_SESSION['msg_type'] = 'error';
            $this->redirect('index.php?controller=Auth&action=dashboard');
        }

        $content = $this->renderView('auth/profile', [
            'user' => $user,
        ]);

        $this->view('layout/main', ['content' => $content]);
    }

    public function updateProfile() {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?controller=Auth&action=profile');
        }

        try {
            $userId = (int)($_SESSION['id'] ?? 0);
            $existing = $this->userModel->findUserById($userId);

            if (!$existing) {
                throw new Exception('Profile not found.');
            }

            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $changingPassword = ($currentPassword !== '' || $newPassword !== '' || $confirmPassword !== '');

            if ($changingPassword) {
                if ($currentPassword === '') {
                    throw new Exception('Current password is required to change your password.');
                }

                if (!password_verify($currentPassword, $existing['password'])) {
                    throw new Exception('Current password is incorrect.');
                }

                if ($newPassword === '' || $confirmPassword === '') {
                    throw new Exception('Please complete the new password fields.');
                }

                if ($newPassword !== $confirmPassword) {
                    throw new Exception('New passwords do not match.');
                }
            }

            $uploadedPicture = $this->uploadProfilePicture();
            $data = [
                'username' => $existing['username'],
                'firstName' => $existing['firstName'],
                'lastName' => $existing['lastName'],
                'position' => $existing['position'],
                'userLevel' => $existing['userLevel'],
                'password' => $changingPassword ? $newPassword : '',
                'profile_picture' => $uploadedPicture ?: ($existing['profile_picture'] ?? null),
            ];

            $this->userModel->update($userId, $data);

            if (!empty($uploadedPicture) && !empty($existing['profile_picture']) && $existing['profile_picture'] !== 'default.png') {
                $oldFile = BASE_URL . 'uploads/profile/' . $existing['profile_picture'];
                if (is_file($oldFile)) {
                    @unlink($oldFile);
                }
            }

            $_SESSION['profile_picture'] = $data['profile_picture'] ?? 'default.png';

            $_SESSION['message'] = 'Profile updated successfully.';
            $_SESSION['msg_type'] = 'success';
            $this->redirect('index.php?controller=Auth&action=profile');
        } catch (Exception $e) {
            $user = $this->userModel->findUserById((int)($_SESSION['id'] ?? 0));
            $content = $this->renderView('auth/profile', [
                'user' => $user,
                'error' => $e->getMessage(),
            ]);

            $this->view('layout/main', ['content' => $content]);
        }
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
                $data['email'],
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
        $this->requireAnyRole([0], 'Only Super Admin can access Core Users.');
    }

    private function canManageCoreUsers() {
        return $this->isSuperAdmin();
    }

    private function canCreateSuperAdmin() {
        return !$this->userModel->hasSuperAdmin() || (string)($_SESSION['user_level'] ?? '') === '0';
    }

    private function coreUserLevels() {
        $levels = [
            1 => 'Admin',
            4 => 'PROJECT MANAGER (PM)',
            5 => 'DEPUTY PROJECT MANAGER (DPM)',
            2 => 'Encoder',
            6 => 'User / GRP Head',
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
            'email' => trim($_POST['email'] ?? ''),
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

        if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Enter a valid email address.');
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

        $uploadDir = dirname(__DIR__, 2) . '/uploads/profile/';
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
