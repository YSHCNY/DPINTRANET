<?php
session_start();
require_once '../app/core/Controller.php';
require_once '../app/models/User.php';
require_once '../app/Services/PasswordResetService.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../Services/OTPService.php';
require_once __DIR__ . '/../Services/MailService.php';
require_once __DIR__ . '/../Services/EmailTemplateService.php';
require_once __DIR__ . '/../Services/TrustedDeviceService.php';
require_once __DIR__ . '/../Services/CookieHelper.php';

use App\Contracts\RateLimiterInterface;
use App\Services\Security\LoginRateLimiter;

class AuthController extends Controller {
    private $userModel;
    private $passwordResetService;
    private RateLimiterInterface $rateLimiter;
    private \App\Services\OTPService $otpService;
    private \App\Services\TrustedDeviceService $trustedDeviceService;

    public function __construct(?RateLimiterInterface $rateLimiter = null) {
        $this->userModel = new User();
        $this->passwordResetService = new PasswordResetService($this->userModel);
        $this->rateLimiter = $rateLimiter ?? new LoginRateLimiter();
        $pdo = Database::connect();
        $this->otpService = new \App\Services\OTPService($pdo);
        $this->trustedDeviceService = new \App\Services\TrustedDeviceService($pdo, $_ENV['TRUSTED_DEVICE_HMAC_KEY'] ?? 'replace_me_in_env');
    }

    private function logAuthEvent(string $message, ?string $username = null, ?int $userId = null): void {
        $actorName = trim((string)($username ?? ''));
        if ($actorName === '' && $userId !== null && $userId > 0) {
            $actorName = (string)$userId;
        }
        if ($actorName === '') {
            $actorName = 'system';
        }

        try {
            $db = Database::connect();
            $stmt = $db->prepare("INSERT INTO systemLogs (userName, logDesc, module, logDate) VALUES (?, ?, ?, ?)");
            $stmt->execute([$actorName, $message, 'Authentication', date('Y-m-d H:i:s')]);
        } catch (Throwable $e) {
            // Keep auth flow intact even if logging fails.
        }
    }

    public function login() {
        error_log('[recover-debug] AuthController::login() entered');
        error_log('[recover-debug] GET recover=' . (isset($_GET['recover']) ? (string)$_GET['recover'] : 'absent'));
        error_log('[recover-debug] GET params=' . json_encode($_GET));

        $dismissExpiredSession = (!empty($_GET['dismissExpired']) && (string)$_GET['dismissExpired'] === '1');
        if ($dismissExpiredSession) {
            unset($_SESSION['session_expired'], $_SESSION['session_expired_message'], $_SESSION['session_recovery_user'], $_SESSION['session_recovery_user_id']);
        }

        $showExpiredSessionModal = (!empty($_GET['expired']) && (string)$_GET['expired'] === '1') || !empty($_SESSION['session_expired']);
        if ($showExpiredSessionModal) {
            unset($_SESSION['session_expired']);
        }

        $recoverSession = (!empty($_GET['recover']) && (string)$_GET['recover'] === '1');
        error_log('[recover-debug] recoverSession=' . ($recoverSession ? 'true' : 'false'));
        if ($recoverSession) {
            error_log('[recover-debug] recovery block entered');
            $recoveryUser = $_SESSION['session_recovery_user'] ?? null;
            $recoveryUserId = (int)($_SESSION['session_recovery_user_id'] ?? 0);
            error_log('[recover-debug] recoveryUserId=' . $recoveryUserId);

            if (empty($recoveryUser) && $recoveryUserId > 0) {
                error_log('[recover-debug] fetching recovery user from database');
                $recoveryUser = $this->userModel->findUserById($recoveryUserId);
            }

            if (!empty($recoveryUser['username'])) {
                error_log('[recover-debug] recovery user found: ' . $recoveryUser['username']);
                error_log('[recover-debug] recovery user payload=' . json_encode($recoveryUser));
                $_SESSION['user'] = $recoveryUser['username'];
                $_SESSION['user_level'] = $recoveryUser['userLevel'] ?? 3;
                $_SESSION['user_id'] = $recoveryUser['id'] ?? 0;
                $_SESSION['auth_user_type'] = 'admin';
                $_SESSION['auth_user_id'] = $_SESSION['user_id'];
                $_SESSION['position'] = $recoveryUser['position'] ?? '';
                $_SESSION['firstName'] = $recoveryUser['firstName'] ?? '';
                $_SESSION['lastName'] = $recoveryUser['lastName'] ?? '';
                $_SESSION['profile_picture'] = $recoveryUser['profile_picture'] ?? 'default.png';
                $_SESSION['id'] = $recoveryUser['id'] ?? 0;
                $_SESSION['last_activity'] = time();
                unset($_SESSION['session_expired'], $_SESSION['session_expired_message'], $_SESSION['session_recovery_user'], $_SESSION['session_recovery_user_id']);
                session_regenerate_id(true);

                $restoredSession = [
                    'user' => $_SESSION['user'] ?? null,
                    'user_id' => $_SESSION['user_id'] ?? null,
                    'user_level' => $_SESSION['user_level'] ?? null,
                    'firstName' => $_SESSION['firstName'] ?? null,
                    'lastName' => $_SESSION['lastName'] ?? null,
                    'position' => $_SESSION['position'] ?? null,
                    'profile_picture' => $_SESSION['profile_picture'] ?? null,
                    'last_activity' => $_SESSION['last_activity'] ?? null,
                ];

                $missingValues = [];
                foreach ($restoredSession as $key => $value) {
                    $isMissing = false;
                    if (in_array($key, ['user_id', 'user_level'], true)) {
                        $isMissing = $value === null || $value === '' || $value === 0;
                    } else {
                        $isMissing = $value === null || $value === '';
                    }

                    if ($isMissing) {
                        $missingValues[] = $key;
                    }
                }

                error_log('[recover-debug] restored session state=' . json_encode($restoredSession));
                if (!empty($missingValues)) {
                    error_log('[recover-debug] missing restored values=' . implode(', ', $missingValues));
                }

                error_log('[recover-debug] redirecting to dashboard');
                $this->redirect('index.php?controller=Auth&action=dashboard&wc=welcome');
            }

            error_log('[recover-debug] recovery block completed without matching user');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim((string)($_POST['username'] ?? ''));
            // Normalize common invisible whitespace that may be pasted into the username field
            $username = preg_replace('/[\p{C}\s]+/u', ' ', $username);
            $password = (string)($_POST['password'] ?? '');
            $ipAddress = (string)($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
            $userAgent = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
            $wantsJson = $this->wantsJsonResponse();

            if ($this->rateLimiter->isBlocked($username, $ipAddress, 'admin', null)) {
                $remainingSeconds = $this->rateLimiter->remainingLockSeconds($username, $ipAddress, 'admin', null);
                $this->rateLimiter->evaluateAttempt($username, $ipAddress, false, 'rate_limited', 'admin', null, $userAgent);

                $this->logAuthEvent('Login temporarily blocked for ' . $username, $username, null);

                if ($wantsJson) {
                    $this->sendJsonResponse([
                        'success' => false,
                        'message' => 'Login temporarily blocked. Please try again in ' . $remainingSeconds . ' seconds.',
                        'remainingAttempts' => 0,
                        'isLocked' => true,
                        'lockExpiresInSeconds' => max(0, $remainingSeconds),
                    ]);
                }

                $this->view('auth/login', ['error' => 'Login temporarily blocked. Please try again in ' . $remainingSeconds . ' seconds.', 'showExpiredSessionModal' => $showExpiredSessionModal]);
                return;
            }

            // Support login by username OR email for robustness
            $user = $this->userModel->findByIdentifier($username);
            if ($user) {
                error_log('[auth-debug] user found for login attempt: id=' . ($user['id'] ?? 'n/a') . ' username=' . ($user['username'] ?? 'n/a') . ' hash_len=' . (isset($user['password']) ? strlen($user['password']) : '0'));
            } else {
                error_log('[auth-debug] no user found for identifier=' . $username);
            }

            if ($user && password_verify($password, $user['password'])) {
                // Credentials valid — do NOT create a login session yet.
                $this->rateLimiter->registerSuccess($username, $ipAddress, 'admin', (int)($user['id'] ?? 0), $userAgent);

                $userId = (int)($user['id'] ?? 0);
                $email = (string)($user['email'] ?? '');

                // If this browser already has a valid trusted-device token, skip OTP and sign in directly.
                $trustedCookieName = \App\Services\CookieHelper::trustedDeviceCookieName('admin', $userId);
                $trustedToken = $_COOKIE[$trustedCookieName] ?? null;
                if (!empty($trustedToken)) {
                    $validated = $this->trustedDeviceService->validateTrustedDevice('admin', $userId, (string)$trustedToken, $ipAddress, $userAgent, true);
                    if ($validated !== null) {
                        $rotatedToken = $validated['rotated_token'] ?? null;
                        if (!empty($rotatedToken)) {
                            $cookie = \App\Services\CookieHelper::trustedDeviceCookie($rotatedToken, 'admin', $userId);
                            setcookie($cookie['name'], $cookie['value'], $cookie['options']);
                        }

                        $_SESSION['user'] = $user['username'];
                        $_SESSION['user_level'] = $user['userLevel'];
                        $_SESSION['user_type'] = 'admin';
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['auth_user_type'] = 'admin';
                        $_SESSION['auth_user_id'] = $_SESSION['user_id'];
                        $_SESSION['position'] = $user['position'];
                        $_SESSION['firstName'] = $user['firstName'];
                        $_SESSION['lastName'] = $user['lastName'];
                        $_SESSION['profile_picture'] = $user['profile_picture'] ?? 'default.png';
                        $_SESSION['id'] = $user['id'];
                        $_SESSION['last_activity'] = time();
                        session_regenerate_id(true);

                        $this->logAuthEvent('Login successful via trusted browser for ' . $user['username'], $user['username'], $userId);

                        if ($wantsJson) {
                            $this->sendJsonResponse(['success' => true, 'message' => 'Trusted device recognized.', 'redirectUrl' => 'index.php?controller=Auth&action=dashboard&wc=welcome']);
                        }

                        $this->redirect('index.php?controller=Auth&action=dashboard&wc=welcome');
                    }
                }

                // Generate and email OTP for second factor. Store minimal state in session
                try {
                    $otpResult = $this->otpService->generate('admin', $userId, $email, 'login', $ipAddress, $userAgent, true);
                    if (!($otpResult['success'] ?? false)) {
                        throw new \RuntimeException($otpResult['message'] ?? 'OTP generation failed');
                    }

                    $plainOtp = $otpResult['otp'] ?? null;
                    if ($plainOtp !== null) {
                        $tpl = (new \App\Services\EmailTemplateService())->renderOtpEmail([
                            'otp' => $plainOtp,
                            'valid_minutes' => 5,
                            'brand_name' => $_ENV['MAIL_FROM_NAME'] ?? 'Organization',
                            'organization_name' => $_ENV['ORG_NAME'] ?? ($_ENV['MAIL_FROM_NAME'] ?? 'Organization'),
                        ]);
                        $mail = new \App\Services\MailService();
                        $sent = $mail->send($email, $tpl['subject'], $tpl['html']);
                        if (! $sent) {
                            throw new \RuntimeException('Mail send failed');
                        }
                    }
                } catch (\Throwable $e) {
                    error_log('OTP generation/send failed: ' . $e->getMessage());
                    if ($wantsJson) {
                        $this->sendJsonResponse(['success' => false, 'message' => 'Unable to send verification code. Please try again later.']);
                    }
                    $this->view('auth/login', ['error' => 'Unable to send verification code. Please try again later.', 'showExpiredSessionModal' => $showExpiredSessionModal]);
                    return;
                }

                // Store a pending two-factor session state (no login session created)
                $_SESSION['twofactor_user_type'] = 'admin';
                $_SESSION['twofactor_user_id'] = $userId;
                $_SESSION['twofactor_email'] = $email;
                $_SESSION['twofactor_reference'] = $otpResult['reference'] ?? null;
                $_SESSION['twofactor_expires_at'] = $otpResult['expires_at'] ?? null;
                $_SESSION['twofactor_remaining_attempts'] = 5;

                if ($wantsJson) {
                    $this->sendJsonResponse([
                        'success' => true,
                        'message' => 'Verification code sent.',
                        'redirectUrl' => 'index.php?controller=Auth&action=verify2fa',
                    ]);
                }

                $this->redirect('index.php?controller=Auth&action=verify2fa');
            }

            if ($user) {
                $this->logAuthEvent('Login failed for ' . $username, $username, (int)($user['id'] ?? 0));
                error_log('[auth-debug] password verification failed for user id=' . ($user['id'] ?? 'n/a') . ' username=' . ($user['username'] ?? 'n/a'));
                $stored = (string)($user['password'] ?? '');
                error_log('[auth-debug] stored password length=' . strlen($stored) . ' prefix=' . substr($stored, 0, 8));

                // Backwards compatibility: check legacy MD5 or SHA1 hashed passwords and migrate to password_hash()
                $stored = (string)($user['password'] ?? '');
                $pwMatched = false;
                if ($stored !== '') {
                    if (preg_match('/^[0-9a-f]{32}$/i', $stored)) {
                        if (hash_equals($stored, md5($password))) {
                            $pwMatched = true;
                        }
                    } elseif (preg_match('/^[0-9a-f]{40}$/i', $stored)) {
                        if (hash_equals($stored, sha1($password))) {
                            $pwMatched = true;
                        }
                    }
                }

                if ($pwMatched) {
                    // Migrate password to current algorithm
                    try {
                        $this->userModel->updatePassword((int)$user['id'], $password);
                    } catch (\Throwable $e) {
                        error_log('[auth-debug] password migration failed for user id=' . ($user['id'] ?? 'n/a') . ' err=' . $e->getMessage());
                    }

                    // Credentials accepted (legacy hash). Continue with OTP flow (do not create session yet)
                    $this->rateLimiter->registerSuccess($username, $ipAddress, 'admin', (int)($user['id'] ?? 0), $userAgent);
                    $userId = (int)($user['id'] ?? 0);
                    $email = (string)($user['email'] ?? '');
                    try {
                        $otpResult = $this->otpService->generate('admin', $userId, $email, 'login', $ipAddress, $userAgent, true);
                        if (!($otpResult['success'] ?? false)) {
                            throw new \RuntimeException($otpResult['message'] ?? 'OTP generation failed');
                        }
                        $plainOtp = $otpResult['otp'] ?? null;
                        if ($plainOtp !== null) {
                            $tpl = (new \App\Services\EmailTemplateService())->renderOtpEmail([
                                'otp' => $plainOtp,
                                'valid_minutes' => 5,
                                'brand_name' => $_ENV['MAIL_FROM_NAME'] ?? 'Organization',
                                'organization_name' => $_ENV['ORG_NAME'] ?? ($_ENV['MAIL_FROM_NAME'] ?? 'Organization'),
                            ]);
                            $mail = new \App\Services\MailService();
                            $sent = $mail->send($email, $tpl['subject'], $tpl['html']);
                            if (! $sent) {
                                throw new \RuntimeException('Mail send failed');
                            }
                        }
                    } catch (\Throwable $e) {
                        error_log('OTP generation/send failed (legacy match): ' . $e->getMessage());
                        if ($wantsJson) {
                            $this->sendJsonResponse(['success' => false, 'message' => 'Unable to send verification code. Please try again later.']);
                        }
                        $this->view('auth/login', ['error' => 'Unable to send verification code. Please try again later.', 'showExpiredSessionModal' => $showExpiredSessionModal]);
                        return;
                    }

                    $_SESSION['twofactor_user_type'] = 'admin';
                    $_SESSION['twofactor_user_id'] = $userId;
                    $_SESSION['twofactor_email'] = $email;
                    $_SESSION['twofactor_reference'] = $otpResult['reference'] ?? null;
                    $_SESSION['twofactor_expires_at'] = $otpResult['expires_at'] ?? null;
                    $_SESSION['twofactor_remaining_attempts'] = 5;

                    if ($wantsJson) {
                        $this->sendJsonResponse([
                            'success' => true,
                            'message' => 'Verification code sent.',
                            'redirectUrl' => 'index.php?controller=Auth&action=verify2fa',
                        ]);
                    }

                    $this->redirect('index.php?controller=Auth&action=verify2fa');
                    return;
                }
            }
            $this->rateLimiter->registerFailure($username, $ipAddress, 'invalid_credentials', 'admin', (int)($user['id'] ?? 0), $userAgent);
            $failureCount = $this->rateLimiter->getFailureCount($username, $ipAddress, 'admin', (int)($user['id'] ?? 0));
            $remainingAttempts = max(0, 4 - $failureCount);

            if ($wantsJson) {
                $this->sendJsonResponse([
                    'success' => false,
                    'message' => 'Invalid username or password.',
                    'remainingAttempts' => $remainingAttempts,
                    'isLocked' => false,
                    'lockExpiresInSeconds' => 0,
                ]);
            }

            $this->view('auth/login', ['error' => 'Invalid username or password', 'showExpiredSessionModal' => $showExpiredSessionModal]);
            return;
        }

        $this->view('auth/login', ['showExpiredSessionModal' => $showExpiredSessionModal]);
    }

    protected function wantsJsonResponse(): bool {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            return true;
        }

        if (!empty($_SERVER['HTTP_ACCEPT']) && stripos((string)$_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            return true;
        }

        return false;
    }

    private function sendJsonResponse(array $payload): void {
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }

    public function forgotPassword() {
        if (!empty($_SESSION['session_expired_message']) && empty($_SESSION['portal_message'])) {
            $_SESSION['portal_message'] = $_SESSION['session_expired_message'];
            $_SESSION['portal_msg_type'] = 'error';
        }

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

    public function verify2fa() {
        $userId = (int)($_SESSION['twofactor_user_id'] ?? 0);
        if ($userId <= 0) {
            $_SESSION['portal_message'] = 'Please authenticate first.';
            $_SESSION['portal_msg_type'] = 'error';
            $this->redirect('index.php?controller=Auth&action=login');
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
            // sanitize OTP: remove any non-digit chars and trim to 6 digits
            $otp = preg_replace('/\D/', '', $otp);
            if (strlen($otp) > 6) $otp = substr($otp, 0, 6);
            $sessionReference = $_SESSION['twofactor_reference'] ?? null;
            $reference = $data['reference'] ?? null;
            if (!empty($sessionReference) && (empty($reference) || (string)$reference !== (string)$sessionReference)) {
                $reference = $sessionReference;
            }
            $remember = !empty($data['remember']) && (int)$data['remember'] === 1;

            $ok = false;
            $result = ['success' => false, 'reason' => 'error', 'remainingAttempts' => 0];
            error_log('OTP RESULT=' . json_encode($result));
            error_log('OTP OK=' . ($ok ? 'YES' : 'NO'));
            try {
                $result = $this->otpService->verify('admin', $userId, $otp, 'login', $reference);
                $ok = (bool)$result['success'];
            } catch (\Throwable $e) {
                error_log('OTP verify error: ' . $e->getMessage());
            }

            if ($ok) {
                // create real login session now
                $user = $this->userModel->findUserById($userId);
                if ($user) {
                    $_SESSION['user'] = $user['username'];
                    $_SESSION['user_level'] = $user['userLevel'];
                        $_SESSION['user_type'] = 'admin';
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['auth_user_type'] = 'admin';
                        $_SESSION['auth_user_id'] = $_SESSION['user_id'];
                    $_SESSION['position'] = $user['position'];
                    $_SESSION['firstName'] = $user['firstName'];
                    $_SESSION['lastName'] = $user['lastName'];
                    $_SESSION['profile_picture'] = $user['profile_picture'] ?? 'default.png';
                    $_SESSION['id'] = $user['id'];
                    $_SESSION['last_activity'] = time();
                    unset($_SESSION['twofactor_user_id'], $_SESSION['twofactor_user_type'], $_SESSION['twofactor_email'], $_SESSION['twofactor_reference'], $_SESSION['twofactor_expires_at'], $_SESSION['twofactor_remaining_attempts']);
                    session_regenerate_id(true);

                    if ($remember) {
                        $deviceInfo = $this->buildTrustedDeviceInfo($userAgent, $ipAddress);
                        $trustedDevice = $this->trustedDeviceService->createTrustedDevice(
                            'admin',
                            $userId,
                            $deviceInfo,
                            30,
                            null,
                            true
                        );

                        if (!empty($trustedDevice['token'])) {
                            $cookie = \App\Services\CookieHelper::trustedDeviceCookie($trustedDevice['token'], 'admin', $userId);
                            setcookie($cookie['name'], $cookie['value'], $cookie['options']);
                        }
                    }
                }

                $this->logAuthEvent('2FA verification successful for ' . $user['username'], $user['username'], $userId);

                if ($this->wantsJsonResponse()) {
                    $this->sendJsonResponse(['success' => true, 'message' => 'Verification successful', 'redirectUrl' => 'index.php?controller=Auth&action=dashboard&wc=welcome']);
                }

                $this->redirect('index.php?controller=Auth&action=dashboard&wc=welcome');
            }

            // failure - surface specific messages
            $msg = 'The verification code is incorrect.';
            if (!empty($result['reason'])) {
                switch ($result['reason']) {
                    case 'expired': $msg = 'Your verification code has expired.'; break;
                    case 'attempts_exceeded': $msg = 'Too many incorrect attempts. Please request a new verification code.'; break;
                    case 'no_active_otp': $msg = 'No verification code found. Please request a new code.'; break;
                    default: $msg = 'The verification code is incorrect.'; break;
                }
            }

            // update remaining attempts in session if provided
            if (isset($result['remainingAttempts'])) {
                $_SESSION['twofactor_remaining_attempts'] = (int)$result['remainingAttempts'];
            }

            $this->logAuthEvent('2FA verification failed for ' . ($userId > 0 ? (string)$userId : 'unknown') . ' (' . $msg . ')', null, $userId);

            if ($this->wantsJsonResponse()) {
                $this->sendJsonResponse(['success' => false, 'message' => $msg, 'remainingAttempts' => $_SESSION['twofactor_remaining_attempts'] ?? 0]);
            }

            $_SESSION['portal_message'] = $msg;
            $_SESSION['portal_msg_type'] = 'error';
            $this->view('auth/verify_2fa', ['message' => $msg, 'msgType' => 'error', 'remainingAttempts' => $_SESSION['twofactor_remaining_attempts'] ?? 0]);
            return;
        }

        $this->view('auth/verify_2fa', ['email' => $_SESSION['twofactor_email'] ?? null, 'reference' => $_SESSION['twofactor_reference'] ?? null, 'remainingAttempts' => $_SESSION['twofactor_remaining_attempts'] ?? 5]);
    }

    public function resend2fa() {
        header('Content-Type: application/json');
        $userId = (int)($_SESSION['twofactor_user_id'] ?? 0);
        if ($userId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No pending verification found']);
            exit;
        }

        $email = $_SESSION['twofactor_email'] ?? '';
        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $agent = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');

        try {
            $otpResult = $this->otpService->generate('admin', $userId, $email, 'login', $ip, $agent, true);
            if (!($otpResult['success'] ?? false)) {
                throw new \RuntimeException($otpResult['message'] ?? 'OTP generation failed');
            }
            $plainOtp = $otpResult['otp'] ?? null;
            $sent = true;
            if ($plainOtp !== null) {
                $tpl = (new \App\Services\EmailTemplateService())->renderOtpEmail([
                    'otp' => $plainOtp,
                    'valid_minutes' => 5,
                    'brand_name' => $_ENV['MAIL_FROM_NAME'] ?? 'Organization',
                    'organization_name' => $_ENV['ORG_NAME'] ?? ($_ENV['MAIL_FROM_NAME'] ?? 'Organization'),
                ]);
                $mail = new \App\Services\MailService();
                $sent = (bool)$mail->send($email, $tpl['subject'], $tpl['html']);
            }
        } catch (\Throwable $e) {
            error_log('OTP resend failed: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Unable to resend verification code']);
            exit;
        }

        $_SESSION['twofactor_reference'] = $otpResult['reference'] ?? $_SESSION['twofactor_reference'];
        $_SESSION['twofactor_expires_at'] = $otpResult['expires_at'] ?? $_SESSION['twofactor_expires_at'];

        echo json_encode(['success' => $sent]);
        exit;
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
        error_log('===== TRUSTED DEVICES =====');
        error_log('SESSION=' . json_encode($_SESSION));
        error_log('GET=' . json_encode($_GET));

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
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $userId = (int)($_SESSION['id'] ?? $_SESSION['user_id'] ?? 0);
        $username = trim((string)($_SESSION['user'] ?? ''));

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        $this->logAuthEvent('Logout successful' . ($username !== '' ? ' for ' . $username : ''), $username !== '' ? $username : null, $userId > 0 ? $userId : null);
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
