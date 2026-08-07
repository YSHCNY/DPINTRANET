<?php
session_start();
require_once '../app/core/Controller.php';
require_once '../app/models/UserModel.php';
require_once '../app/models/correspondence.php';
require_once '../app/Services/PasswordResetService.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../Services/OTPService.php';
require_once __DIR__ . '/../Services/MailService.php';
require_once __DIR__ . '/../Services/EmailTemplateService.php';
require_once __DIR__ . '/../Services/TrustedDeviceService.php';
require_once __DIR__ . '/../Services/TwoFactorService.php';
require_once __DIR__ . '/../Services/CookieHelper.php';

use App\Contracts\RateLimiterInterface;
use App\Services\Security\StandardUserLoginRateLimiter;

class StandardPortalController extends Controller {
    private $userModel;
    private $correspondenceModel;
    private $passwordResetService;
    private RateLimiterInterface $rateLimiter;
    private \App\Services\OTPService $otpService;
    private \App\Services\TrustedDeviceService $trustedDeviceService;
    private \App\Services\TwoFactorService $twoFactorService;

    public function __construct(?RateLimiterInterface $rateLimiter = null) {
        $this->userModel = new UserModel();
        $this->correspondenceModel = new CorrespondenceModel();
        $this->passwordResetService = new PasswordResetService($this->userModel);
        $this->rateLimiter = $rateLimiter ?? new StandardUserLoginRateLimiter();
        $pdo = Database::connect();
        $this->otpService = new \App\Services\OTPService($pdo);
        $this->trustedDeviceService = new \App\Services\TrustedDeviceService($pdo, $_ENV['TRUSTED_DEVICE_HMAC_KEY'] ?? 'replace_me_in_env');
        $this->twoFactorService = new \App\Services\TwoFactorService($pdo, $this->trustedDeviceService, $this->otpService);
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

                $userId = (int)($user['id'] ?? 0);
                $email = (string)($user['email'] ?? '');
                $trustedCookieName = \App\Services\CookieHelper::trustedDeviceCookieName('standard', $userId);
                $trustedToken = $_COOKIE[$trustedCookieName] ?? null;
                $deviceInfo = $this->buildTrustedDeviceInfo($userAgent, $ipAddress);

                $check = $this->twoFactorService->handlePostCredentialCheck(
                    'standard',
                    $userId,
                    $email,
                    $trustedToken,
                    $deviceInfo,
                    $ipAddress,
                    $userAgent
                );

                if ($check['status'] === 'trusted') {
                    $rotatedToken = $check['token'] ?? null;
                    if (!empty($rotatedToken)) {
                        $cookie = \App\Services\CookieHelper::trustedDeviceCookie($rotatedToken, 'standard', $userId);
                        setcookie($cookie['name'], $cookie['value'], $cookie['options']);
                    }

                    $_SESSION['standard_user_id'] = $user['id'];
                    $_SESSION['user_type'] = 'standard';
                    $_SESSION['auth_user_type'] = 'standard';
                    $_SESSION['user_id'] = (int)($user['id'] ?? 0);
                    $_SESSION['auth_user_id'] = (int)($user['id'] ?? 0);
                    $_SESSION['user'] = $user['username'] ?? $user['email'] ?? '';
                    $_SESSION['is_portal_user'] = !empty($user['is_portal_user']) ? 1 : 0;
                    $_SESSION['standard_user_name'] = trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? ''));
                    $_SESSION['standard_user_position'] = $user['position'] ?? '';
                    $_SESSION['standard_user_department'] = $user['department'] ?? '';
                    $_SESSION['standard_user_avatar'] = $user['avatar'] ?? null;
                    $_SESSION['standard_user_pin'] = $user['pin_code'] ?? '';
                    $_SESSION['last_activity'] = time();
                    session_regenerate_id(true);

                    $displayName = trim((string)($user['firstName'] ?? $user['username'] ?? '')); 
                    if ($displayName === '') {
                        $displayName = 'User';
                    }
                    $_SESSION['portal_welcome_message'] = 'Welcome back, ' . $displayName . '!';

                    $this->redirect('index.php?controller=StandardPortal&action=dashboard');
                    return;
                }

                if ($check['status'] === 'otp_sent') {
                    $_SESSION['standard_twofactor_user_id'] = $userId;
                    $_SESSION['standard_twofactor_email'] = $email;
                    $_SESSION['standard_twofactor_reference'] = $check['reference'] ?? null;
                    $_SESSION['standard_twofactor_expires_at'] = $check['expires_at'] ?? null;
                    $_SESSION['standard_twofactor_remaining_attempts'] = 5;

                    try {
                        $plainOtp = $check['otp'] ?? null;
                        if ($plainOtp !== null) {
                            $tpl = (new \App\Services\EmailTemplateService())->renderOtpEmail([
                                'otp' => $plainOtp,
                                'valid_minutes' => 5,
                                'brand_name' => $_ENV['MAIL_FROM_NAME'] ?? 'DPINTRANET',
                                'organization_name' => $_ENV['ORG_NAME'] ?? ($_ENV['MAIL_FROM_NAME'] ?? 'DPINTRANET'),
                            ]);
                            $mail = new \App\Services\MailService();
                            $sent = $mail->send($email, $tpl['subject'], $tpl['html']);
                            if (! $sent) {
                                throw new \RuntimeException('Mail send failed');
                            }
                        }
                    } catch (\Throwable $e) {
                        error_log('StandardPortal OTP send failed: ' . $e->getMessage());
                        $this->view('standard_portal/login', ['error' => 'Unable to send verification code. Please try again later.', 'flashMessage' => $flashMessage, 'flashType' => $flashType]);
                        return;
                    }

                    $this->redirect('index.php?controller=StandardPortal&action=verify2fa');
                    return;
                }

                // fallback in case the shared service returns an unexpected state
                $this->view('standard_portal/login', ['error' => 'Unable to verify your login attempt. Please try again.', 'flashMessage' => $flashMessage, 'flashType' => $flashType]);
                return;
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

    public function verify2fa() {
        $userId = (int)($_SESSION['standard_twofactor_user_id'] ?? 0);
        if ($userId <= 0) {
            $_SESSION['portal_message'] = 'Please sign in before verifying your code.';
            $_SESSION['portal_msg_type'] = 'error';
            $this->redirect('index.php?controller=StandardPortal&action=login');
        }

        $message = $_SESSION['portal_message'] ?? null;
        $type = $_SESSION['portal_msg_type'] ?? 'info';
        unset($_SESSION['portal_message'], $_SESSION['portal_msg_type']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' || $this->wantsJsonResponse()) {
            $ipAddress = (string)($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
            $userAgent = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true);
            if (!is_array($data)) {
                $data = $_POST;
            }

            $otp = trim((string)($data['otp'] ?? ''));
            $otp = preg_replace('/\D/', '', $otp);
            if (strlen($otp) > 6) {
                $otp = substr($otp, 0, 6);
            }
            $reference = $data['reference'] ?? null;
            $sessionReference = $_SESSION['standard_twofactor_reference'] ?? null;
            if (!empty($sessionReference) && (empty($reference) || (string)$reference !== (string)$sessionReference)) {
                $reference = $sessionReference;
            }
            $remember = !empty($data['remember']) && (int)$data['remember'] === 1;
            $email = $_SESSION['standard_twofactor_email'] ?? '';
            $result = $this->twoFactorService->verifyOtpAndMaybeRemember(
                'standard',
                $userId,
                $otp,
                (string)$reference,
                $email,
                $this->buildTrustedDeviceInfo($userAgent, $ipAddress),
                $remember,
                $ipAddress,
                $userAgent
            );

            if (!empty($result['status']) && $result['status'] === 'ok') {
                $user = $this->userModel->getUserById($userId);
                if ($user) {
                    $_SESSION['standard_user_id'] = $user['id'];
                    $_SESSION['user_type'] = 'standard';
                    $_SESSION['auth_user_type'] = 'standard';
                    $_SESSION['user_id'] = (int)($user['id'] ?? 0);
                    $_SESSION['auth_user_id'] = (int)($user['id'] ?? 0);
                    $_SESSION['user'] = $user['username'] ?? $user['email'] ?? '';
                    $_SESSION['is_portal_user'] = !empty($user['is_portal_user']) ? 1 : 0;
                    $_SESSION['standard_user_name'] = trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? ''));
                    $_SESSION['standard_user_position'] = $user['position'] ?? '';
                    $_SESSION['standard_user_department'] = $user['department'] ?? '';
                    $_SESSION['standard_user_avatar'] = $user['avatar'] ?? null;
                    $_SESSION['standard_user_pin'] = $user['pin_code'] ?? '';
                    $_SESSION['last_activity'] = time();
                    unset(
                        $_SESSION['standard_twofactor_user_id'],
                        $_SESSION['standard_twofactor_email'],
                        $_SESSION['standard_twofactor_reference'],
                        $_SESSION['standard_twofactor_expires_at'],
                        $_SESSION['standard_twofactor_remaining_attempts']
                    );
                    session_regenerate_id(true);

                    $displayName = trim((string)($user['firstName'] ?? $user['username'] ?? ''));
                    if ($displayName === '') {
                        $displayName = 'User';
                    }
                    $_SESSION['portal_welcome_message'] = 'Welcome back, ' . $displayName . '!';

                    if (!empty($result['cookie']) && is_array($result['cookie'])) {
                        $cookie = $result['cookie'];
                        setcookie($cookie['name'], $cookie['value'], $cookie['options']);
                    }
                }

                if ($this->wantsJsonResponse()) {
                    $this->jsonResponse(['success' => true, 'message' => 'Verification successful', 'redirectUrl' => 'index.php?controller=StandardPortal&action=dashboard']);
                }

                $this->redirect('index.php?controller=StandardPortal&action=dashboard');
            }

            $msg = 'The verification code is incorrect.';
            if (!empty($result['reason'])) {
                switch ($result['reason']) {
                    case 'expired':
                        $msg = 'Your verification code has expired.';
                        break;
                    case 'attempts_exceeded':
                        $msg = 'Too many incorrect attempts. Please request a new verification code.';
                        break;
                    case 'no_active_otp':
                        $msg = 'No verification code found. Please request a new code.';
                        break;
                    default:
                        $msg = 'The verification code is incorrect.';
                        break;
                }
            }

            if (isset($result['remainingAttempts'])) {
                $_SESSION['standard_twofactor_remaining_attempts'] = (int)$result['remainingAttempts'];
            }

            if ($this->wantsJsonResponse()) {
                $this->jsonResponse(['success' => false, 'message' => $msg, 'remainingAttempts' => $_SESSION['standard_twofactor_remaining_attempts'] ?? 0]);
            }

            $_SESSION['portal_message'] = $msg;
            $_SESSION['portal_msg_type'] = 'error';
            $this->view('auth/verify_2fa', [
                'email' => $email,
                'reference' => $reference,
                'remainingAttempts' => $_SESSION['standard_twofactor_remaining_attempts'] ?? 5,
                'verifyAction' => 'index.php?controller=StandardPortal&action=verify2fa',
                'resendAction' => 'index.php?controller=StandardPortal&action=resend2fa',
            ]);
            return;
        }

        $this->view('auth/verify_2fa', [
            'email' => $_SESSION['standard_twofactor_email'] ?? null,
            'reference' => $_SESSION['standard_twofactor_reference'] ?? null,
            'remainingAttempts' => $_SESSION['standard_twofactor_remaining_attempts'] ?? 5,
            'verifyAction' => 'index.php?controller=StandardPortal&action=verify2fa',
            'resendAction' => 'index.php?controller=StandardPortal&action=resend2fa',
        ]);
    }

    public function resend2fa() {
        header('Content-Type: application/json');
        $userId = (int)($_SESSION['standard_twofactor_user_id'] ?? 0);
        if ($userId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No pending verification found']);
            exit;
        }

        $email = $_SESSION['standard_twofactor_email'] ?? '';
        $ipAddress = (string)($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $userAgent = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');

        try {
            $otpResult = $this->otpService->generate('standard', $userId, $email, 'login', $ipAddress, $userAgent, true);
            if (!($otpResult['success'] ?? false)) {
                throw new \RuntimeException($otpResult['message'] ?? 'OTP generation failed');
            }

            $plainOtp = $otpResult['otp'] ?? null;
            if ($plainOtp !== null) {
                $tpl = (new \App\Services\EmailTemplateService())->renderOtpEmail([
                    'otp' => $plainOtp,
                    'valid_minutes' => 5,
                    'brand_name' => $_ENV['MAIL_FROM_NAME'] ?? 'DPINTRANET',
                    'organization_name' => $_ENV['ORG_NAME'] ?? ($_ENV['MAIL_FROM_NAME'] ?? 'DPINTRANET'),
                ]);
                $mail = new \App\Services\MailService();
                $sent = $mail->send($email, $tpl['subject'], $tpl['html']);
                if (! $sent) {
                    throw new \RuntimeException('Mail send failed');
                }
            }
        } catch (\Throwable $e) {
            error_log('StandardPortal OTP resend failed: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Unable to resend verification code']);
            exit;
        }

        $_SESSION['standard_twofactor_reference'] = $otpResult['reference'] ?? $_SESSION['standard_twofactor_reference'];
        $_SESSION['standard_twofactor_expires_at'] = $otpResult['expires_at'] ?? $_SESSION['standard_twofactor_expires_at'];

        echo json_encode(['success' => true]);
        exit;
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

    public function profileSettings() {
        $this->requirePortalLogin();

        $userId = (int)($_SESSION['standard_user_id'] ?? 0);
        $user = $this->userModel->getProfileById($userId);
        if (!$user) {
            $_SESSION['portal_message'] = 'Your profile could not be loaded.';
            $_SESSION['portal_msg_type'] = 'error';
            $this->redirect('index.php?controller=StandardPortal&action=dashboard');
        }

        $this->view('standard_portal/profile_settings', [
            'user' => $user,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function updateProfileSettings() {
        $this->requirePortalLogin();

        $userId = (int)($_SESSION['standard_user_id'] ?? 0);
        $user = $this->userModel->getProfileById($userId);
        if (!$user) {
            $_SESSION['portal_message'] = 'Your profile could not be loaded.';
            $_SESSION['portal_msg_type'] = 'error';
            $this->redirect('index.php?controller=StandardPortal&action=dashboard');
        }

        $errors = [];
        $old = [];

        $firstName = trim((string)($_POST['firstName'] ?? ''));
        $middleName = trim((string)($_POST['middleName'] ?? ''));
        $lastName = trim((string)($_POST['lastName'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));
        $currentPassword = (string)($_POST['current_password'] ?? '');
        $newPassword = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');
        $currentPin = (string)($_POST['current_pin'] ?? '');
        $newPin = (string)($_POST['new_pin'] ?? '');
        $confirmPin = (string)($_POST['confirm_pin'] ?? '');

        if ($firstName === '') {
            $errors['firstName'] = 'First name is required.';
        }
        if ($lastName === '') {
            $errors['lastName'] = 'Last name is required.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'A valid email address is required.';
        }

        $existingEmail = $this->userModel->findByEmail($email, $userId);
        if ($existingEmail) {
            $errors['email'] = 'This email address is already in use.';
        }

        if ($currentPassword !== '' || $newPassword !== '' || $confirmPassword !== '') {
            if (!password_verify($currentPassword, (string)($user['password'] ?? ''))) {
                $errors['current_password'] = 'Current password is incorrect.';
            }
            if ($newPassword === '' || strlen($newPassword) < 8) {
                $errors['new_password'] = 'New password must be at least 8 characters.';
            }
            if ($newPassword !== '' && $confirmPassword !== '' && $newPassword !== $confirmPassword) {
                $errors['confirm_password'] = 'Passwords do not match.';
            }
        }

        if ($currentPin !== '' || $newPin !== '' || $confirmPin !== '') {
            if (!hash_equals((string)($user['pin_code'] ?? ''), $currentPin)) {
                $errors['current_pin'] = 'Current PIN is incorrect.';
            }
            if ($newPin === '' || strlen($newPin) < 4) {
                $errors['new_pin'] = 'New PIN must be at least 4 digits.';
            }
            if ($newPin !== '' && $confirmPin !== '' && $newPin !== $confirmPin) {
                $errors['confirm_pin'] = 'PINs do not match.';
            }
            if ($newPin !== '' && $newPassword !== '' && $newPin === $newPassword) {
                $errors['new_pin'] = 'PIN must be different from the password.';
            }
        }

        $avatarPath = $user['avatar'] ?? null;
        $removeAvatar = !empty($_POST['remove_avatar']);
        if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($fileInfo, $_FILES['avatar']['tmp_name']);
            finfo_close($fileInfo);
            if (!in_array($mimeType, $allowed, true)) {
                $errors['avatar'] = 'Only JPG, PNG, and WebP images are allowed.';
            } else {
                $targetDir = __DIR__ . '/../../uploads/standard_users/';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                $fileName = time() . '_' . bin2hex(random_bytes(8)) . '.' . pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                $targetFile = $targetDir . $fileName;
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile)) {
                    $avatarPath = $fileName;
                } else {
                    $errors['avatar'] = 'Unable to upload avatar.';
                }
            }
        } elseif ($removeAvatar) {
            $avatarPath = null;
        }

        $old = [
            'firstName' => $firstName,
            'middleName' => $middleName,
            'lastName' => $lastName,
            'email' => $email,
            'phone' => $phone,
        ];

        if ($errors) {
            $_SESSION['portal_message'] = 'Please correct the highlighted fields.';
            $_SESSION['portal_msg_type'] = 'error';
            $this->view('standard_portal/profile_settings', [
                'user' => array_merge($user, $old),
                'errors' => $errors,
                'old' => $old,
            ]);
            return;
        }

        $payload = [
            'firstName' => $firstName,
            'middleName' => $middleName,
            'lastName' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'avatar' => $avatarPath,
        ];

        $updated = $this->userModel->updateProfile($userId, $payload);
        if ($updated) {
            if ($currentPassword !== '' && $newPassword !== '' && $confirmPassword !== '') {
                $this->userModel->updatePassword($userId, $newPassword);
            }
            if ($currentPin !== '' && $newPin !== '' && $confirmPin !== '') {
                $this->userModel->updatePincode($userId, $newPin);
            }

            $_SESSION['portal_message'] = 'Profile updated successfully.';
            $_SESSION['portal_msg_type'] = 'success';
            $this->redirect('index.php?controller=StandardPortal&action=profileSettings');
        }

        $_SESSION['portal_message'] = 'Unable to update your profile.';
        $_SESSION['portal_msg_type'] = 'error';
        $this->view('standard_portal/profile_settings', [
            'user' => array_merge($user, $old),
            'errors' => ['general' => 'Unable to update your profile.'],
            'old' => $old,
        ]);
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

        $this->correspondenceModel->markPortalDocumentReceived($id, $_SESSION['standard_user_id'], $pin, $remarks);

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

    private function buildTrustedDeviceInfo(string $userAgent, string $ipAddress): array
    {
        $browserName = 'Unknown Browser';
        $operatingSystem = 'Unknown OS';
        $deviceName = 'Desktop';

        if (preg_match('/Edg\//i', $userAgent)) {
            $browserName = 'Microsoft Edge';
        } elseif (preg_match('/OPR\//i', $userAgent) || preg_match('/Opera/i', $userAgent)) {
            $browserName = 'Opera';
        } elseif (preg_match('/Chrome\//i', $userAgent)) {
            $browserName = 'Chrome';
        } elseif (preg_match('/Firefox\//i', $userAgent)) {
            $browserName = 'Firefox';
        } elseif (preg_match('/Safari\//i', $userAgent)) {
            $browserName = 'Safari';
        } elseif (preg_match('/Trident\//i', $userAgent) || preg_match('/MSIE/i', $userAgent)) {
            $browserName = 'Internet Explorer';
        }

        if (preg_match('/Windows/i', $userAgent)) {
            $operatingSystem = 'Windows';
        } elseif (preg_match('/Mac OS X|Macintosh/i', $userAgent)) {
            $operatingSystem = 'macOS';
        } elseif (preg_match('/Android/i', $userAgent)) {
            $operatingSystem = 'Android';
        } elseif (preg_match('/iPhone|iPad|iOS/i', $userAgent)) {
            $operatingSystem = 'iOS';
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $operatingSystem = 'Linux';
        }

        if (preg_match('/iPad/i', $userAgent)) {
            $deviceName = 'iPad';
        } elseif (preg_match('/iPhone/i', $userAgent)) {
            $deviceName = 'iPhone';
        } elseif (preg_match('/Android/i', $userAgent)) {
            $deviceName = 'Android Device';
        } elseif (preg_match('/Mobile/i', $userAgent)) {
            $deviceName = 'Mobile';
        }

        return [
            'browser_name' => $browserName,
            'operating_system' => $operatingSystem,
            'device_name' => $deviceName,
            'registration_ip' => $ipAddress,
        ];
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
