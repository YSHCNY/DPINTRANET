<?php
session_start();
require_once '../app/core/Controller.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../Services/TrustedDeviceService.php';
require_once __DIR__ . '/../Services/AuthContextService.php';

use App\Services\TrustedDeviceService;
use App\Services\AuthContextService;

class TrustedDevicesController extends Controller
{
    private TrustedDeviceService $svc;

    public function __construct()
    {
        $pdo = Database::connect();
        $hmacKey = $_ENV['TRUSTED_DEVICE_HMAC_KEY'] ?? 'replace_me_in_env';
        $this->svc = new TrustedDeviceService($pdo, $hmacKey);
    }

    // Authentication context is resolved centrally via AuthContextService
    private function resolveAuthenticatedUser(): array
    {
        // Keep session variable references for compatibility with other code and tests
        $sessionUserType = $_SESSION['user_type'] ?? null;
        $sessionUserId = $_SESSION['user_id'] ?? null;

        // Delegate to centralized resolver
        return AuthContextService::getAuthenticatedUser();
    }

    public function listAjax()
    {
        $this->requireLogin();
        // Resolve authenticated user once
        $context = $this->resolveAuthenticatedUser();
        $userType = $context['userType'];
        $userId = $context['userId'];

        $trustedCookieName = \App\Services\CookieHelper::trustedDeviceCookieName($userType, $userId);
        $currentToken = $_COOKIE[$trustedCookieName] ?? null;
        $currentPublicId = null;
        if ($currentToken) {
            $currentPublicId = $this->svc->findPublicIdByToken($userType, $userId, $currentToken);
        }

        $devices = $this->svc->listTrustedDevices($userType, $userId, $currentPublicId);

        $this->jsonResponse([
            'success' => true,
            'message' => 'Trusted devices listed',
            'data' => ['devices' => $devices],
            'errors' => new \stdClass(),
        ]);
    }

    public function revokeAjax()
    {
        $this->requireLogin();
        require_once __DIR__ . '/../Services/CsrfService.php';

        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($csrfToken) || !\App\Services\CsrfService::validate($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'invalid_csrf', 'data' => new \stdClass(), 'errors' => ['code' => 'invalid_csrf']], 403);
        }

        $publicId = $_POST['public_id'] ?? null;
        if (! $publicId) {
            $this->jsonResponse(['success' => false, 'message' => 'validation_failed', 'data' => new \stdClass(), 'errors' => ['public_id' => 'required']], 400);
        }

        $context = $this->resolveAuthenticatedUser();
        $userType = $context['userType'];
        $userId = $context['userId'];

        $result = $this->svc->revokeByPublicId($userType, $userId, $publicId, 'revoked_by_user', (int)($_SESSION['user_id'] ?? 0));

        if (!empty($result['success'])) {
            $this->jsonResponse(['success' => true, 'message' => 'Trusted device revoked', 'data' => new \stdClass(), 'errors' => new \stdClass()]);
        }

        // service returns failure reason
        $reason = $result['reason'] ?? ($result['debug']['failure_reason'] ?? 'update_failed');
        $msg = match ($reason) {
            'public_id_not_found' => 'device_not_found',
            'already_revoked' => 'already_revoked',
            'user_mismatch' => 'unauthorized',
            'db_execute_failed' => 'db_error',
            default => 'revoke_failed',
        };

        $this->jsonResponse(['success' => false, 'message' => $msg, 'data' => ['debug' => $result['debug'] ?? []], 'errors' => ['reason' => $reason]], 400);
    }

    public function revokeOthersAjax()
    {
        $this->requireLogin();
        require_once __DIR__ . '/../Services/CsrfService.php';

        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($csrfToken) || !\App\Services\CsrfService::validate($csrfToken)) {
            $this->jsonResponse(['success' => false, 'message' => 'invalid_csrf', 'data' => new \stdClass(), 'errors' => ['code' => 'invalid_csrf']], 403);
        }

        $context = $this->resolveAuthenticatedUser();
        $userType = $context['userType'];
        $userId = $context['userId'];
        $except = $_POST['except_public_id'] ?? null;

        $count = $this->svc->revokeAllExcept($userType, $userId, $except, 'revoked_by_user', (int)($_SESSION['user_id'] ?? 0));

        $this->jsonResponse(['success' => true, 'message' => 'revoked_others', 'data' => ['revoked' => $count], 'errors' => new \stdClass()]);
    }
}
