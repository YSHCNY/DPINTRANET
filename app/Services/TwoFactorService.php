<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

final class TwoFactorService
{
    private PDO $pdo;
    private TrustedDeviceService $trustedDeviceService;
    private OTPService $otpService;

    public function __construct(PDO $pdo, TrustedDeviceService $trustedDeviceService, OTPService $otpService)
    {
        $this->pdo = $pdo;
        $this->trustedDeviceService = $trustedDeviceService;
        $this->otpService = $otpService;
    }

    /**
     * After credentials are validated, call this to decide next step.
     * - If a valid trusted-device token is present, returns ['status' => 'trusted', 'device' => array]
     * - Otherwise generates an OTP and returns ['status' => 'otp_sent', 'reference' => string, 'expires_at' => string]
     *
     * Note: Controller is responsible for reading cookie/token and providing it here.
     */
    public function handlePostCredentialCheck(string $userType, int $userId, string $email, ?string $deviceTokenCookie, array $deviceInfo = [], ?string $requestIp = null, ?string $userAgent = null): array
    {
        if ($deviceTokenCookie !== null) {
            $validated = $this->trustedDeviceService->validateTrustedDevice($userType, $userId, $deviceTokenCookie, $requestIp, $userAgent, true);

            if ($validated !== null) {
                // validated contains ['device'=>..., 'rotated_token' => ...?]
                $rotated = $validated['rotated_token'] ?? null;
                $this->logEvent($userType, $userId, 'login_success', ['trusted_device' => true], $requestIp, $userAgent);

                $result = ['status' => 'trusted', 'device' => $validated['device']];
                if ($rotated !== null) {
                    // provide rotated token to controller for immediate cookie provisioning
                    $result['token'] = $rotated;
                    $result['cookie'] = \App\Services\CookieHelper::trustedDeviceCookie($rotated, $userType, $userId);
                }

                // session fixation protection: regenerate session id on successful trusted-device login
                if (session_status() !== PHP_SESSION_ACTIVE) session_start();
                session_regenerate_id(true);

                return $result;
            }
        }

        // Not trusted: generate OTP and email. Return plain OTP for server-side email delivery.
        $otp = $this->otpService->generate($userType, $userId, $email, 'login', $requestIp, $userAgent, true);

        $this->logEvent($userType, $userId, 'otp_sent', ['reference' => $otp['reference']], $requestIp, $userAgent);

        return [
            'status' => 'otp_sent',
            'reference' => $otp['reference'],
            'expires_at' => $otp['expires_at'],
            'otp' => $otp['otp'] ?? null,
        ];
    }

    /**
     * Verify OTP and optionally create a trusted device token for the client.
     * If $rememberDevice is true, returns a `token` field which the controller
     * must set as a secure, HttpOnly cookie. Token is returned only for immediate
     * cookie provisioning and should not be logged.
     */
    public function verifyOtpAndMaybeRemember(string $userType, int $userId, string $otpPlain, string $reference, string $email, array $deviceInfo = [], bool $rememberDevice = false, ?string $requestIp = null, ?string $userAgent = null): array
    {
        $verification = $this->otpService->verify($userType, $userId, $otpPlain, 'login', $reference);
        $ok = !empty($verification['success']);

        if (! $ok) {
            $this->logEvent($userType, $userId, 'otp_failed', ['reference' => $reference, 'reason' => $verification['reason'] ?? null], $requestIp, $userAgent);
            return [
                'status' => 'invalid',
                'reason' => $verification['reason'] ?? 'invalid',
                'remainingAttempts' => isset($verification['remainingAttempts']) ? (int)$verification['remainingAttempts'] : 0,
            ];
        }

        $this->logEvent($userType, $userId, 'otp_verified', ['reference' => $reference], $requestIp, $userAgent);

        // session fixation protection: regenerate session id after successful OTP verification
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        session_regenerate_id(true);

            if ($rememberDevice) {
                $created = $this->trustedDeviceService->createTrustedDevice($userType, $userId, $deviceInfo, 30, null, true);

                // created contains trusted_device and token when requested
                $this->logEvent($userType, $userId, 'trusted_browser_added', [], $requestIp, $userAgent);

                $ret = ['status' => 'ok', 'trusted_device' => $created['trusted_device'], 'token' => $created['token']];
                $ret['cookie'] = \App\Services\CookieHelper::trustedDeviceCookie($created['token'], $userType, $userId);
                return $ret;
            }

        return ['status' => 'ok'];
    }

    public function revokeDevice(string $userType, int $userId, string $token, ?string $reason = null, ?int $actor = null, ?string $requestIp = null, ?string $userAgent = null): bool
    {
        $ok = $this->trustedDeviceService->revokeTrustedDevice($userType, $userId, $token, $reason, $actor);

        if ($ok) {
            $this->logEvent($userType, $userId, 'trusted_browser_revoked', ['reason' => $reason], $requestIp, $userAgent);
        }

        return $ok;
    }

    public function revokeAll(string $userType, int $userId, ?string $reason = null, ?int $actor = null, ?string $requestIp = null, ?string $userAgent = null): int
    {
        $count = $this->trustedDeviceService->revokeAllDevices($userType, $userId, $reason, $actor);

        if ($count > 0) {
            $this->logEvent($userType, $userId, 'logout_all_devices', ['count' => $count, 'reason' => $reason], $requestIp, $userAgent);
        }

        return $count;
    }

    private function logEvent(string $userType, int $userId, string $eventType, array $metadata = [], ?string $ip = null, ?string $userAgent = null): void
    {
        $sql = 'INSERT INTO user_security_events (user_type, user_id, event_type, event_at, ip, user_agent, metadata, source) VALUES (:user_type, :user_id, :event_type, NOW(), :ip, :user_agent, :metadata, :source)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_type' => $userType,
            ':user_id' => $userId,
            ':event_type' => $eventType,
            ':ip' => $ip ? inet_pton($ip) : null,
            ':user_agent' => $userAgent,
            ':metadata' => $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null,
            ':source' => 'twofactor_service',
        ]);
    }
}
