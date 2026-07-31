<?php
session_start();
require_once '../app/core/Controller.php';
require_once '../app/models/UserModel.php';
require_once '../app/models/correspondence.php';
require_once '../app/Services/PasswordResetService.php';
if (file_exists(__DIR__ . '/../config.php')) {
    require_once __DIR__ . '/../config.php';
} elseif (file_exists(__DIR__ . '/../../app/config.php')) {
    require_once __DIR__ . '/../../app/config.php';
}


use App\Contracts\RateLimiterInterface;
use App\Services\Security\StandardUserLoginRateLimiter;

class StandardPortalController extends Controller {
    private $userModel;
    private $correspondenceModel;
    private $passwordResetService;
    private RateLimiterInterface $rateLimiter;

    public function __construct(?RateLimiterInterface $rateLimiter = null) {
        $this->userModel = new UserModel();
        $this->correspondenceModel = new CorrespondenceModel();
        $this->passwordResetService = new PasswordResetService($this->userModel);
        $this->rateLimiter = $rateLimiter ?? new StandardUserLoginRateLimiter();
    }

    public function login() {
        if (isset($_SESSION['standard_user_id'])) {
            $this->redirect('index.php?controller=StandardPortal&action=dashboard');
        }

        $flashMessage = $_SESSION['portal_message'] ?? null;
        $flashType = $_SESSION['portal_msg_type'] ?? 'info';
        unset($_SESSION['portal_message'], $_SESSION['portal_msg_type']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim((string)($_POST['username'] ?? ''));
            $password = (string)($_POST['password'] ?? '');
            $ipAddress = (string)($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
            $userAgent = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
            $user = $this->userModel->findPortalUserByUsername($username);

            if ($this->rateLimiter->isBlocked($username, $ipAddress, 'standard', null)) {
                $remainingSeconds = $this->rateLimiter->remainingLockSeconds($username, $ipAddress, 'standard', null);
                $this->rateLimiter->evaluateAttempt($username, $ipAddress, false, 'rate_limited', 'standard', null, $userAgent);
                $this->view('standard_portal/login', ['error' => 'Login temporarily blocked. Please try again in ' . $remainingSeconds . ' seconds.', 'flashMessage' => $flashMessage, 'flashType' => $flashType]);
                return;
            }

            if ($user && password_verify($password, $user['password'])) {
                $this->rateLimiter->registerSuccess($username, $ipAddress, 'standard', (int)($user['id'] ?? 0), $userAgent);
                $_SESSION['standard_user_id'] = $user['id'];
                $_SESSION['standard_user_name'] = trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? ''));
                $_SESSION['standard_user_position'] = $user['position'] ?? '';
                $_SESSION['standard_user_department'] = $user['department'] ?? '';
                $_SESSION['standard_user_avatar'] = $user['avatar'] ?? null;
                $_SESSION['standard_user_pin'] = $user['pin_code'] ?? '';
                $this->redirect('index.php?controller=StandardPortal&action=dashboard');
            }

            $this->rateLimiter->registerFailure($username, $ipAddress, 'invalid_credentials', 'standard', (int)($user['id'] ?? 0), $userAgent);
            $failureCount = $this->rateLimiter->getFailureCount($username, $ipAddress, 'standard', (int)($user['id'] ?? 0));
            $remainingAttempts = max(0, 4 - $failureCount);
            $attemptMessage = $remainingAttempts > 0
                ? ($remainingAttempts === 1 ? '1 login attempt remaining before a temporary lockout.' : $remainingAttempts . ' login attempts remaining before a temporary lockout.')
                : 'No further login attempts are available until the temporary lockout expires.';

            $this->view('standard_portal/login', ['error' => 'Invalid portal credentials or portal access is disabled. ' . $attemptMessage, 'flashMessage' => $flashMessage, 'flashType' => $flashType]);
            return;
        }

        $this->view('standard_portal/login', ['flashMessage' => $flashMessage, 'flashType' => $flashType]);
    }

    public function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $identifier = trim($_POST['identifier'] ?? '');
            $email = trim($_POST['email'] ?? '');

            $result = $this->passwordResetService->startReset($identifier, $email);

            $_SESSION['password_reset_identifier'] = $identifier;
            $_SESSION['password_reset_email'] = $email;
            $_SESSION['password_reset_user_id'] = null;

            $user = $this->userModel->findPortalUserByIdentifier($identifier);
            if ($user && strtolower((string)($user['email'] ?? '')) === strtolower($email)) {
                $_SESSION['password_reset_user_id'] = (int) $user['id'];
            }

            $_SESSION['portal_message'] = $result['message'];
            $_SESSION['portal_msg_type'] = 'info';
            $this->redirect('index.php?controller=StandardPortal&action=verifyOtp');
        }

        $this->view('standard_portal/forgot_password');
    }

    public function verifyOtp() {
        $userId = (int)($_SESSION['password_reset_user_id'] ?? 0);
        if ($userId <= 0) {
            $_SESSION['portal_message'] = 'Please start the password reset process again.';
            $_SESSION['portal_msg_type'] = 'error';
            $this->redirect('index.php?controller=StandardPortal&action=forgotPassword');
        }

        $remainingAttempts = (int)($_SESSION['password_reset_remaining_attempts'] ?? 5);
        $message = $_SESSION['portal_message'] ?? null;
        $type = $_SESSION['portal_msg_type'] ?? 'info';
        unset($_SESSION['portal_message'], $_SESSION['portal_msg_type']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $otp = trim($_POST['otp'] ?? '');
            $result = $this->passwordResetService->verifyOtp($userId, $otp);
            $remainingAttempts = (int)($result['remainingAttempts'] ?? $remainingAttempts);
            $_SESSION['password_reset_remaining_attempts'] = $remainingAttempts;
            $_SESSION['portal_message'] = $result['message'];
            $_SESSION['portal_msg_type'] = $result['success'] ? 'success' : 'error';

            if ($result['success']) {
                $_SESSION['password_reset_verified'] = true;
                $this->redirect('index.php?controller=StandardPortal&action=resetPassword');
            }

            $this->view('standard_portal/verify_otp', ['message' => $result['message'], 'msgType' => $result['success'] ? 'success' : 'error', 'remainingAttempts' => $remainingAttempts]);
            return;
        }

        $this->view('standard_portal/verify_otp', ['message' => $message, 'msgType' => $type, 'remainingAttempts' => $remainingAttempts]);
    }

    public function resendOtp() {
        $identifier = $_SESSION['password_reset_identifier'] ?? '';
        $email = $_SESSION['password_reset_email'] ?? '';
        $result = $this->passwordResetService->startReset($identifier, $email);

        $_SESSION['portal_message'] = $result['message'];
        $_SESSION['portal_msg_type'] = 'info';
        $this->redirect('index.php?controller=StandardPortal&action=verifyOtp');
    }

    public function resetPassword() {
        if (empty($_SESSION['password_reset_verified']) || empty($_SESSION['password_reset_user_id'])) {
            $_SESSION['portal_message'] = 'Please verify your code before resetting your password.';
            $_SESSION['portal_msg_type'] = 'error';
            $this->redirect('index.php?controller=StandardPortal&action=forgotPassword');
        }

        $message = $_SESSION['portal_message'] ?? null;
        $type = $_SESSION['portal_msg_type'] ?? 'info';
        unset($_SESSION['portal_message'], $_SESSION['portal_msg_type']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if ($newPassword === '' || $confirmPassword === '') {
                $_SESSION['portal_message'] = 'Please complete both password fields.';
                $_SESSION['portal_msg_type'] = 'error';
                $this->view('standard_portal/reset_password', ['message' => 'Please complete both password fields.', 'msgType' => 'error']);
                return;
            }

            if ($newPassword !== $confirmPassword) {
                $_SESSION['portal_message'] = 'Passwords do not match.';
                $_SESSION['portal_msg_type'] = 'error';
                $this->view('standard_portal/reset_password', ['message' => 'Passwords do not match.', 'msgType' => 'error']);
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
                $this->redirect('index.php?controller=StandardPortal&action=login');
            }

            $_SESSION['portal_message'] = $result['message'];
            $_SESSION['portal_msg_type'] = 'error';
            $this->view('standard_portal/reset_password', ['message' => $result['message'], 'msgType' => 'error']);
            return;
        }

        $this->view('standard_portal/reset_password', ['message' => $message, 'msgType' => $type]);
    }

    public function dashboard() {
        $this->requirePortalLogin();

        $recipientId = $_SESSION['standard_user_id'];
        $showRemovedItems = $this->getRemovedItemsPreference();
        $this->view('standard_portal/dashboard', [
            'stats' => $this->correspondenceModel->getPortalStats($recipientId, $showRemovedItems),
            'documents' => array_slice($this->correspondenceModel->getPortalDocuments($recipientId, $showRemovedItems), 0, 6),
            'showRemovedItems' => $showRemovedItems,
        ]);
    }

    public function inbox() {
        $this->requirePortalLogin();

        $showRemovedItems = $this->getRemovedItemsPreference();
        $this->view('standard_portal/inbox', [
            'documents' => $this->correspondenceModel->getPortalDocuments($_SESSION['standard_user_id'], $showRemovedItems),
            'showRemovedItems' => $showRemovedItems,
        ]);
    }

    public function profileSettings() {
        $this->requirePortalLogin();

        $userId = (int)($_SESSION['standard_user_id'] ?? 0);
        $user = $this->userModel->getProfileById($userId);

        if (!$user) {
            $_SESSION['portal_message'] = 'Profile not found.';
            $_SESSION['portal_msg_type'] = 'error';
            $this->redirect('index.php?controller=StandardPortal&action=dashboard');
        }

        $portalMessage = $_SESSION['portal_message'] ?? null;
        $portalMessageType = $_SESSION['portal_msg_type'] ?? null;
        unset($_SESSION['portal_message'], $_SESSION['portal_msg_type']);

        $this->view('standard_portal/profile_settings', [
            'user' => $user,
            'errors' => [],
            'old' => [],
            'portalMessage' => $portalMessage,
            'portalMessageType' => $portalMessageType,
        ]);
    }

    public function updateProfileSettings() {
        $this->requirePortalLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?controller=StandardPortal&action=profileSettings');
        }

        $userId = (int)($_SESSION['standard_user_id'] ?? 0);
        $existing = $this->userModel->getProfileById($userId);

        if (!$existing) {
            $_SESSION['portal_message'] = 'Profile not found.';
            $_SESSION['portal_msg_type'] = 'error';
            $this->redirect('index.php?controller=StandardPortal&action=dashboard');
        }

        $firstName = trim((string)($_POST['firstName'] ?? ''));
        $middleName = trim((string)($_POST['middleName'] ?? ''));
        $lastName = trim((string)($_POST['lastName'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));
        $currentPassword = (string)($_POST['current_password'] ?? '');
        $newPassword = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');
        $currentPin = trim((string)($_POST['current_pin'] ?? ''));
        $newPin = trim((string)($_POST['new_pin'] ?? ''));
        $confirmPin = trim((string)($_POST['confirm_pin'] ?? ''));
        $removeAvatar = isset($_POST['remove_avatar']) && $_POST['remove_avatar'] === '1';

        $errors = [];
        $old = [
            'firstName' => $firstName,
            'middleName' => $middleName,
            'lastName' => $lastName,
            'email' => $email,
            'phone' => $phone,
        ];

        if ($firstName === '') {
            $errors['firstName'] = 'First name is required.';
        }

        if ($lastName === '') {
            $errors['lastName'] = 'Last name is required.';
        }

        if ($email === '') {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        } elseif ($this->userModel->findByEmail($email, $userId)) {
            $errors['email'] = 'This email is already in use.';
        }

        if ($phone !== '' && !preg_match('/^[0-9\+\(\)\-\s]{7,25}$/', $phone)) {
            $errors['phone'] = 'Enter a valid phone number.';
        }

        $changingPassword = $currentPassword !== '' || $newPassword !== '' || $confirmPassword !== '';
        $changingPin = $currentPin !== '' || $newPin !== '' || $confirmPin !== '';

        if ($changingPassword) {
            if ($currentPassword === '') {
                $errors['current_password'] = 'Current password is required.';
            } elseif (!password_verify($currentPassword, $existing['password'] ?? '')) {
                $errors['current_password'] = 'Current password is incorrect.';
            }

            if ($newPassword === '') {
                $errors['new_password'] = 'New password is required.';
            } elseif (strlen($newPassword) < 8) {
                $errors['new_password'] = 'New password must have at least 8 characters.';
            }

            if ($confirmPassword === '') {
                $errors['confirm_password'] = 'Confirm password is required.';
            } elseif ($newPassword !== $confirmPassword) {
                $errors['confirm_password'] = 'Passwords do not match.';
            }
        }

        if ($changingPin) {
            if ($currentPin === '') {
                $errors['current_pin'] = 'Current PIN is required.';
            } elseif (($existing['pin_code'] ?? '') === '' || $existing['pin_code'] !== $currentPin) {
                $errors['current_pin'] = 'Current PIN is incorrect.';
            }

            if ($newPin === '') {
                $errors['new_pin'] = 'New PIN is required.';
            } elseif (!preg_match('/^[0-9]{4,10}$/', $newPin)) {
                $errors['new_pin'] = 'PIN must be 4 to 10 digits.';
            } elseif (!empty($existing['password']) && password_verify($newPin, $existing['password'])) {
                $errors['new_pin'] = 'PIN must be different from your current password.';
            }

            if ($confirmPin === '') {
                $errors['confirm_pin'] = 'Confirm PIN is required.';
            } elseif ($newPin !== $confirmPin) {
                $errors['confirm_pin'] = 'PINs do not match.';
            }

            if ($newPin !== '' && $newPassword !== '' && $newPin === $newPassword) {
                $errors['new_pin'] = 'PIN must be different from your password.';
            }
        }

        $avatarFileName = null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
            try {
                $avatarFileName = $this->saveStandardUserAvatar($_FILES['avatar']);
            } catch (Exception $e) {
                $errors['avatar'] = $e->getMessage();
            }
        }

        if (!empty($errors)) {
            $user = $existing;
            $this->view('standard_portal/profile_settings', [
                'user' => $user,
                'errors' => $errors,
                'old' => $old,
                'portalMessage' => null,
                'portalMessageType' => null,
            ]);
            return;
        }

        $updatedAvatar = $existing['avatar'] ?? null;
        if ($avatarFileName !== null) {
            $updatedAvatar = $avatarFileName;
        } elseif ($removeAvatar) {
            $updatedAvatar = null;
        }

        $this->userModel->updateProfile($userId, [
            'firstName' => $firstName,
            'middleName' => $middleName,
            'lastName' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'avatar' => $updatedAvatar,
        ]);

        if ($changingPassword) {
            $this->userModel->updatePassword($userId, $newPassword);
        }

        if ($changingPin) {
            $this->userModel->updatePincode($userId, $newPin);
        }

        if ($avatarFileName !== null || $removeAvatar) {
            if (!empty($existing['avatar'])) {
                $this->deleteStandardUserAvatar($existing['avatar']);
            }
        }

        $_SESSION['standard_user_avatar'] = $updatedAvatar;
        $_SESSION['standard_user_name'] = trim($firstName . ' ' . $lastName);
        $_SESSION['portal_message'] = 'Your profile has been updated successfully.';
        $_SESSION['portal_msg_type'] = 'success';
        $this->redirect('index.php?controller=StandardPortal&action=profileSettings');
    }

    private function saveStandardUserAvatar(array $file): string {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Avatar upload failed.');
        }

        if ($file['size'] > 3 * 1024 * 1024) {
            throw new Exception('Avatar must be 3MB or smaller.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (!isset($extensions[$mimeType])) {
            throw new Exception('Avatar must be a JPG, PNG, or WEBP image.');
        }

        $extension = $extensions[$mimeType];
        $uploadDir = __DIR__ . '/../../uploads/standard_users/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = uniqid('standard_user_', true) . '.' . $extension;
        $destination = $uploadDir . $fileName;

        $image = $this->createImageResource($file['tmp_name'], $mimeType);
        if ($image === false) {
            throw new Exception('Unable to process avatar image.');
        }

        $maxDimension = 1200;
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width > $maxDimension || $height > $maxDimension) {
            $ratio = $width / $height;
            if ($ratio > 1) {
                $newWidth = $maxDimension;
                $newHeight = (int)round($maxDimension / $ratio);
            } else {
                $newHeight = $maxDimension;
                $newWidth = (int)round($maxDimension * $ratio);
            }

            $resized = imagecreatetruecolor($newWidth, $newHeight);
            if ($mimeType === 'image/png' || $mimeType === 'image/webp') {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
            }
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $resized;
        }

        $this->saveImageToFile($image, $destination, $mimeType);
        imagedestroy($image);

        return $fileName;
    }

    private function createImageResource(string $path, string $mimeType) {
        return match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
            default => false,
        };
    }

    private function saveImageToFile($image, string $destination, string $mimeType): void {
        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($image, $destination, 90);
                break;
            case 'image/png':
                imagepng($image, $destination, 6);
                break;
            case 'image/webp':
                imagewebp($image, $destination, 90);
                break;
        }
    }

    private function deleteStandardUserAvatar(string $filename): void {
        if ($filename === '' || strtolower($filename) === 'default.png') {
            return;
        }

        $path = __DIR__ . '/../../uploads/standard_users/' . $filename;
        if (is_file($path)) {
            @unlink($path);
        }
    }

    public function setRemovedItemsPreference() {
        $this->requirePortalLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $enabled = filter_var($_POST['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $this->setRemovedItemsPreferenceValue($enabled);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'show_removed_items' => $enabled,
        ]);
        exit;
    }

    public function viewDocument($id) {
        $this->requirePortalLogin();

        $document = $this->correspondenceModel->getPortalDocument($id, $_SESSION['standard_user_id']);
        if (!$document) {
            $_SESSION['portal_message'] = 'Document not found or not assigned to you.';
            $_SESSION['portal_msg_type'] = 'error';
            $this->redirect('index.php?controller=StandardPortal&action=inbox');
        }

        if (!empty($document['is_deleted'])) {
            $_SESSION['portal_message'] = 'This document was deleted by the sender. View-only access is available.';
            $_SESSION['portal_msg_type'] = 'error';
        }

        $this->view('standard_portal/details', [
            'document' => $document,
            'attachments' => $this->correspondenceModel->getAttachments($id),
            'history' => $this->correspondenceModel->getDocumentChangeHistory($id),
            'thread' => $this->correspondenceModel->getThreadEntries($id),
        ]);
    }

    /**
     * Endpoint for portal users to post a thread entry
     */
    public function postThreadEntry($id) {
        $this->requirePortalLogin();

        $documentId = (int)$id;
        $document = $this->correspondenceModel->getPortalDocument($documentId, $_SESSION['standard_user_id']);
        if (!$document) {
            $_SESSION['portal_message'] = 'Document not found or not assigned to you.';
            $_SESSION['portal_msg_type'] = 'error';
            $this->redirect('index.php?controller=StandardPortal&action=inbox');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['portal_message'] = 'Invalid request.';
            $_SESSION['portal_msg_type'] = 'error';
            $this->redirect('index.php?controller=StandardPortal&action=viewDocument&id=' . $documentId);
        }

        $entryKind = trim($_POST['entry_kind'] ?? 'feedback');
        $content = trim($_POST['content'] ?? '');
        $cycleRef = trim($_POST['cycle_reference'] ?? '');

        $actorType = 'standard_user';
        $actorUserId = (int)($_SESSION['standard_user_id'] ?? 0) ?: null;
        $actorName = trim($_SESSION['standard_user_name'] ?? 'Recipient');
        $roleLabel = $_SESSION['department'] ?? 'Recipient';

        // handle files (max 4, max 40MB total)
        $uploaded = [];
        $maxFiles = 4;
        $maxBytes = 41943040; // 40MB

        if (!empty($_FILES['thread_files'])) {
            $files = $_FILES['thread_files'];
            $count = min((int)count($files['name']), $maxFiles);

            $totalSize = 0;
            for ($i = 0; $i < $count; $i++) {
                $totalSize += (int)($files['size'][$i] ?? 0);
            }

            if ($totalSize > $maxBytes) {
                $_SESSION['portal_message'] = 'Thread upload exceeds the 40MB total limit.';
                $_SESSION['portal_msg_type'] = 'error';
                $this->redirect('index.php?controller=StandardPortal&action=viewDocument&id=' . (int)$documentId);
            }

            $targetDir = __DIR__ . '/../../uploads/thread/' . $documentId;
            if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

            for ($i = 0; $i < $count; $i++) {
                if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;
                $name = basename((string)($files['name'][$i] ?? ''));
                if ($name === '') continue;

                $uniq = time() . '_' . bin2hex(random_bytes(6)) . '_' . $name;
                $path = $targetDir . '/' . $uniq;

                if (move_uploaded_file($files['tmp_name'][$i], $path)) {
                    $uploaded[] = [
                        'file_name' => $name,
                        'file_path' => $path,
                        'file_size' => (int)($files['size'][$i] ?? 0)
                    ];
                }
            }
        }


        $entryId = $this->correspondenceModel->addThreadEntry(
            $documentId,
            $actorType,
            $actorUserId,
            $actorName,
            $roleLabel,
            $entryKind,
            $content,
            $cycleRef,
            $uploaded
        );

        if ($entryId) {
            $_SESSION['portal_message'] = 'Posted.';
            $_SESSION['portal_msg_type'] = 'success';
        } else {
            $_SESSION['portal_message'] = 'Failed to post entry.';
            $_SESSION['portal_msg_type'] = 'error';
        }

        $this->redirect('index.php?controller=StandardPortal&action=viewDocument&id=' . $documentId);
    }

    public function receive($id) {
        $this->requirePortalLogin();

        $document = $this->correspondenceModel->getPortalDocument($id, $_SESSION['standard_user_id']);
        if (!$document) {
            $_SESSION['portal_message'] = 'Document not found or not assigned to you.';
            $_SESSION['portal_msg_type'] = 'error';
            $this->redirect('index.php?controller=StandardPortal&action=inbox');
        }

        if (!empty($document['is_deleted'])) {
            $_SESSION['portal_message'] = 'This document was deleted by the sender and can no longer be received.';
            $_SESSION['portal_msg_type'] = 'error';
            $this->redirect('index.php?controller=StandardPortal&action=viewDocument&id=' . (int)$id);
        }

        $pin = trim($_POST['pin_code'] ?? '');
        $remarks = trim($_POST['remarks'] ?? '');

        if ($pin === '' || !hash_equals((string)($_SESSION['standard_user_pin'] ?? ''), $pin)) {
            $_SESSION['portal_message'] = 'Invalid PIN code.';
            $_SESSION['portal_msg_type'] = 'error';
            $this->redirect('index.php?controller=StandardPortal&action=viewDocument&id=' . (int)$id);
        }

        $this->correspondenceModel->markPortalDocumentReceived(
            $id,
            $_SESSION['standard_user_id'],
            $pin,
            $remarks,
            $_SESSION['standard_user_name'] ?? 'Standard User'
        );

        $_SESSION['portal_message'] = 'Document marked as received.';
        $_SESSION['portal_msg_type'] = 'success';
        $this->redirect('index.php?controller=StandardPortal&action=viewDocument&id=' . (int)$id);
    }

    public function download($id) {
        $this->requirePortalLogin();

        $attachment = $this->correspondenceModel->getPortalAttachment($id, $_SESSION['standard_user_id']);
        if (!$attachment || empty($attachment['file_path']) || !file_exists($attachment['file_path'])) {
            $_SESSION['portal_message'] = 'Attachment not found.';
            $_SESSION['portal_msg_type'] = 'error';
            $this->redirect('index.php?controller=StandardPortal&action=inbox');
        }

        if (ob_get_level()) {
            ob_end_clean();
        }

        $fullPath = $attachment['file_path'];
        $downloadName = $attachment['file_name'] ?: basename($fullPath);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fullPath);
        finfo_close($finfo);

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . basename($downloadName) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }

    public function logout() {
        unset(
            $_SESSION['standard_user_id'],
            $_SESSION['standard_user_name'],
            $_SESSION['standard_user_position'],
            $_SESSION['standard_user_department'],
            $_SESSION['standard_user_avatar'],
            $_SESSION['standard_user_pin']
        );
        $this->redirect('index.php?controller=StandardPortal&action=login');
    }

    private function requirePortalLogin() {
        if (!isset($_SESSION['standard_user_id'])) {
            $this->redirect('index.php?controller=StandardPortal&action=login');
        }
    }

    private function getRemovedItemsPreference(): bool {
        $userId = (int)($_SESSION['standard_user_id'] ?? 0);
        return (bool)($_SESSION['standard_portal_prefs'][$userId]['show_removed_items'] ?? false);
    }

    private function setRemovedItemsPreferenceValue(bool $enabled): void {
        $userId = (int)($_SESSION['standard_user_id'] ?? 0);

        if (!isset($_SESSION['standard_portal_prefs'])) {
            $_SESSION['standard_portal_prefs'] = [];
        }

        if (!isset($_SESSION['standard_portal_prefs'][$userId])) {
            $_SESSION['standard_portal_prefs'][$userId] = [];
        }

        $_SESSION['standard_portal_prefs'][$userId]['show_removed_items'] = $enabled;
    }
}
