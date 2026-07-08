<?php

require_once '../app/models/User.php';
require_once '../app/models/UserModel.php';
require_once '../app/models/PasswordResetOtp.php';
require_once '../app/Services/OtpService.php';
require_once '../app/Services/MailService.php';

use App\Services\MailService;

class PasswordResetService
{
    private $userModel;
    private PasswordResetOtp $otpModel;
    private OtpService $otpService;
    private MailService $mailService;

    public function __construct($userModel = null)
    {
        $this->userModel = $userModel ?? new User();
        $this->otpModel = new PasswordResetOtp();
        $this->otpService = new OtpService();
        $this->mailService = new MailService();
    }

    public function startReset(string $identifier, string $email): array
    {
        $user = $this->resolveUser($identifier);
        $normalizedEmail = strtolower(trim($email));

        if (!$user || strtolower((string)($user['email'] ?? '')) !== $normalizedEmail) {
            $this->logAttempt($identifier, 'invalid_request');
            return ['success' => true, 'message' => 'If the account details are valid, a verification code has been sent.'];
        }

        if (!$this->isAllowedToRequest($user['id'])) {
            $this->logAttempt($identifier, 'rate_limited');
            return ['success' => true, 'message' => 'Please wait before requesting another code.'];
        }

        $code = $this->otpService->generateCode();
        $hash = $this->otpService->hashCode($code);
        $expiresAt = (new DateTimeImmutable('+5 minutes'))->format('Y-m-d H:i:s');

        $this->otpModel->createForUser((int)$user['id'], $hash, $expiresAt);

        $this->mailService->send(
            $user['email'],
            'Your verification code',
            $this->buildEmailBody($code)
        );

        $this->logAttempt($identifier, 'otp_sent');

        return ['success' => true, 'message' => 'If the account details are valid, a verification code has been sent.'];
    }

    public function verifyOtp(int $userId, string $code): array
    {
        $otp = $this->otpModel->getActiveOtpForUser($userId);
        if (!$otp) {
            return ['success' => false, 'message' => 'The verification code is invalid or has expired.', 'remainingAttempts' => 0];
        }

        if ($this->otpService->isExpired($otp['expires_at'])) {
            $this->otpModel->deleteByUserId($userId);
            return ['success' => false, 'message' => 'The verification code has expired.', 'remainingAttempts' => 0];
        }

        if ((int)($otp['attempts'] ?? 0) >= 5) {
            $this->otpModel->deleteByUserId($userId);
            return ['success' => false, 'message' => 'Too many attempts. Please request a new code.', 'remainingAttempts' => 0];
        }

        if (!$this->otpService->verifyCode($code, $otp['otp_hash'])) {
            $this->otpModel->incrementAttempts($otp['id']);
            $remaining = 5 - ((int)($otp['attempts'] ?? 0) + 1);
            return ['success' => false, 'message' => 'The verification code is incorrect. ' . max($remaining, 0) . ' attempt(s) remaining.', 'remainingAttempts' => max($remaining, 0)];
        }

        $this->otpModel->markVerified($otp['id']);
        return ['success' => true, 'message' => 'Code verified.', 'remainingAttempts' => 5];
    }

    public function resetPassword(int $userId, string $newPassword): array
    {
        $user = method_exists($this->userModel, 'getUserById')
            ? $this->userModel->getUserById($userId)
            : $this->userModel->findUserById($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'Unable to reset password.'];
        }

        if (method_exists($this->userModel, 'updatePassword')) {
            $this->userModel->updatePassword($userId, $newPassword);
        } elseif (method_exists($this->userModel, 'updatePortalPassword')) {
            $this->userModel->updatePortalPassword($userId, $newPassword);
        } else {
            return ['success' => false, 'message' => 'Unable to reset password.'];
        }

        $this->otpModel->deleteByUserId($userId);

        return ['success' => true, 'message' => 'Password updated successfully.'];
    }

    public function getUserForReset(string $identifier): ?array
    {
        return $this->resolveUser($identifier);
    }

    private function resolveUser(string $identifier): ?array
    {
        $normalized = trim($identifier);
        if ($normalized === '') {
            return null;
        }

        if (method_exists($this->userModel, 'findPortalUserByIdentifier')) {
            return $this->userModel->findPortalUserByIdentifier($normalized);
        }

        if (method_exists($this->userModel, 'findByIdentifier')) {
            return $this->userModel->findByIdentifier($normalized);
        }

        return null;
    }

    private function isAllowedToRequest(int $userId): bool
    {
        $otp = $this->otpModel->getActiveOtpForUser($userId);
        if (!$otp) {
            return true;
        }

        $createdAt = new DateTimeImmutable($otp['created_at']);
        $cooldownUntil = $createdAt->modify('+60 seconds');
        return new DateTimeImmutable('now') >= $cooldownUntil;
    }

    private function buildEmailBody(string $code): string
    {
        return sprintf(
            '<p>Your verification code is <strong>%s</strong>.</p><p>This code will expire in 5 minutes.</p><p>Please do not share this code with anyone.</p>',
            htmlspecialchars($code)
        );
    }

    private function logAttempt(string $identifier, string $result): void
    {
        error_log(sprintf('Password reset attempt [%s] for %s', $result, $identifier));
    }
}
