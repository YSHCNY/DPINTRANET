<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;
use PDO;

final class OTPService
{
    private PDO $pdo;
    private int $otpLength = 6;
    private int $expiryMinutes = 5;
    private int $maxAttempts = 5;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? self::createDefaultConnection();
    }

    private static function createDefaultConnection(): PDO
    {
        return new PDO(
            'mysql:host=localhost;dbname=daltondb',
            'phpmyadmin',
            'pkii@1111',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    /**
     * Compatibility methods for the legacy password reset flow.
     */
    public function generateCode(): string
    {
        return str_pad((string) random_int(0, (int) pow(10, $this->otpLength) - 1), $this->otpLength, '0', STR_PAD_LEFT);
    }

    public function hashCode(string $code): string
    {
        return password_hash($code, PASSWORD_DEFAULT);
    }

    public function verifyCode(string $code, string $hash): bool
    {
        return password_verify($code, $hash);
    }

    public function isExpired(string $expiresAt): bool
    {
        return strtotime($expiresAt) <= time();
    }

    /**
     * Generate an OTP and store its hash. Does NOT send email.
     * Returns structured array. If $returnPlain is true, includes plain otp (use only in secure server-to-server flows).
     */
    public function generate(string $userType, int $userId, ?string $email = null, string $purpose = 'login', ?string $requestIp = null, ?string $agent = null, bool $returnPlain = false): array
    {
        // Rate limit: max 15 per 10 minutes
        // $rateLimitSql = 'SELECT COUNT(1) AS c FROM otp_verifications WHERE user_type = :user_type AND user_id = :user_id AND purpose = :purpose AND created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)';
        // $rl = $this->pdo->prepare($rateLimitSql);
        // $rl->execute([':user_type' => $userType, ':user_id' => $userId, ':purpose' => $purpose]);
        // $c = (int)($rl->fetchColumn() ?: 0);
        // if ($c >= 15) {
        //     return ['success' => false, 'message' => 'Rate limit exceeded', 'reference' => null];
        // }

        $otp = str_pad((string) random_int(0, (int) pow(10, $this->otpLength) - 1), $this->otpLength, '0', STR_PAD_LEFT);
        $reference = bin2hex(random_bytes(8));

        $stored = $this->store($userType, $userId, $otp, $purpose, 'email', $requestIp, $agent, $reference);

        $result = [
            'success' => true,
            'message' => 'OTP generated',
            'reference' => $reference,
            'expires_at' => $stored['expires_at'] ?? null,
            'id' => $stored['id'] ?? null,
        ];

        if ($returnPlain) {
            $result['otp'] = $otp;
        }

        return $result;
    }

    /**
     * Store an OTP hash in DB. Returns metadata.
     */
    public function store(string $userType, int $userId, string $otpPlain, string $purpose = 'login', string $method = 'email', ?string $requestIp = null, ?string $agent = null, ?string $reference = null): array
    {
        // Invalidate previous unused OTPs for this user/purpose
        $invalidateSql = 'UPDATE otp_verifications SET consumed_at = NOW() WHERE user_type = :user_type AND user_id = :user_id AND purpose = :purpose AND consumed_at IS NULL';
        $invStmt = $this->pdo->prepare($invalidateSql);
        $invStmt->execute([':user_type' => $userType, ':user_id' => $userId, ':purpose' => $purpose]);

        $hash = password_hash($otpPlain, PASSWORD_DEFAULT);

        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $expiresAt = (new DateTimeImmutable("+{$this->expiryMinutes} minutes"))->format('Y-m-d H:i:s');

        $sql = 'INSERT INTO otp_verifications (user_type, user_id, otp_hash, otp_algo, purpose, method, attempts, max_attempts, consumed_at, expires_at, created_at, request_ip, request_agent, reference)
                VALUES (:user_type, :user_id, :otp_hash, :otp_algo, :purpose, :method, 0, :max_attempts, NULL, :expires_at, :created_at, :request_ip, :request_agent, :reference)';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_type' => $userType,
            ':user_id' => $userId,
            ':otp_hash' => $hash,
            ':otp_algo' => 'password_hash',
            ':purpose' => $purpose,
            ':method' => $method,
            ':max_attempts' => $this->maxAttempts,
            ':expires_at' => $expiresAt,
            ':created_at' => $now,
            ':request_ip' => $requestIp ? inet_pton($requestIp) : null,
            ':request_agent' => $agent,
            ':reference' => $reference,
        ]);

        return [
            'id' => (int) $this->pdo->lastInsertId(),
            'expires_at' => $expiresAt,
            'created_at' => $now,
            'reference' => $reference,
        ];
    }

    /**
     * Verify an OTP. Returns structured result.
     */
    public function verify(string $userType, int $userId, string $otpPlain, string $purpose = 'login', ?string $reference = null): array
    {
        // Locking and detailed checks
        try {
            $this->pdo->beginTransaction();

            $sql = 'SELECT id, otp_hash, attempts, max_attempts, consumed_at, expires_at FROM otp_verifications WHERE user_type = :user_type AND user_id = :user_id AND purpose = :purpose' . ($reference ? ' AND reference = :reference' : '') . ' ORDER BY created_at DESC LIMIT 1 FOR UPDATE';
            $stmt = $this->pdo->prepare($sql);
            $params = [':user_type' => $userType, ':user_id' => $userId, ':purpose' => $purpose];
            if ($reference) $params[':reference'] = $reference;
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (! $row) {
                $this->pdo->commit();
                return ['success' => false, 'message' => 'No active OTP found', 'reason' => 'no_active_otp', 'remainingAttempts' => 0];
            }

            if ($row['consumed_at'] !== null) {
                $this->pdo->commit();
                return ['success' => false, 'message' => 'OTP already used', 'reason' => 'consumed', 'remainingAttempts' => 0];
            }

            if (strtotime($row['expires_at']) <= time()) {
                $u = $this->pdo->prepare('UPDATE otp_verifications SET consumed_at = NOW() WHERE id = :id');
                $u->execute([':id' => $row['id']]);
                $this->pdo->commit();
                return ['success' => false, 'message' => 'OTP expired', 'reason' => 'expired', 'remainingAttempts' => 0];
            }

            if ((int)$row['attempts'] >= (int)$row['max_attempts']) {
                $this->pdo->commit();
                return ['success' => false, 'message' => 'Too many attempts', 'reason' => 'attempts_exceeded', 'remainingAttempts' => 0];
            }

            $verified = password_verify($otpPlain, $row['otp_hash']);
            if ($verified) {
                $u = $this->pdo->prepare('UPDATE otp_verifications SET consumed_at = NOW(), attempts = attempts + 1 WHERE id = :id');
                $u->execute([':id' => $row['id']]);
                $this->pdo->commit();
                return ['success' => true, 'message' => 'OTP verified', 'reason' => 'verified', 'remainingAttempts' => 0, 'id' => (int)$row['id']];
            }

            // incorrect
            $u = $this->pdo->prepare('UPDATE otp_verifications SET attempts = attempts + 1 WHERE id = :id');
            $u->execute([':id' => $row['id']]);
            $r = $this->pdo->prepare('SELECT attempts, max_attempts FROM otp_verifications WHERE id = :id');
            $r->execute([':id' => $row['id']]);
            $n = $r->fetch(PDO::FETCH_ASSOC);
            $remaining = max(0, (int)$n['max_attempts'] - (int)$n['attempts']);
            if ((int)$n['attempts'] >= (int)$n['max_attempts']) {
                $this->invalidate((int)$row['id']);
            }
            $this->pdo->commit();
            return ['success' => false, 'message' => 'Incorrect OTP', 'reason' => 'incorrect', 'remainingAttempts' => $remaining];
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Consume - mark OTP consumed by id
     */
    public function consume(int $otpId): bool
    {
        $stmt = $this->pdo->prepare('UPDATE otp_verifications SET consumed_at = NOW() WHERE id = :id AND consumed_at IS NULL');
        $stmt->execute([':id' => $otpId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Invalidate OTP (alias for consume)
     */
    public function invalidate(int $otpId): bool
    {
        return $this->consume($otpId);
    }

    /**
     * Resend: generate a fresh OTP for the same user/purpose. Does NOT send email.
     */
    public function resend(string $userType, int $userId, ?string $email = null, string $purpose = 'login', ?string $requestIp = null, ?string $agent = null, bool $returnPlain = false): array
    {
        return $this->generate($userType, $userId, $email, $purpose, $requestIp, $agent, $returnPlain);
    }

    /**
     * Cleanup expired or old consumed OTPs. Returns number deleted.
     */
    public function cleanupExpired(int $olderThanMinutes = 60, int $limit = 1000): int
    {
        $sql = 'DELETE FROM otp_verifications WHERE (expires_at IS NOT NULL AND expires_at < NOW()) OR (consumed_at IS NOT NULL AND consumed_at < DATE_SUB(NOW(), INTERVAL :minutes MINUTE)) LIMIT :limit';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':minutes', $olderThanMinutes, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount();
    }
}
