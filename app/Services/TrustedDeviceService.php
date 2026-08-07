<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\TrustedDevice;
use DateTimeImmutable;
use PDO;

/**
 * Service responsible for trusted browser/device lifecycle.
 *
 * - Uses HMAC-SHA256 for token hashing (server secret injected)
 * - Never logs or persists raw tokens
 */
final class TrustedDeviceService
{
    private PDO $pdo;
    private string $hmacKey;
    private int $tokenBytes;

    public function __construct(PDO $pdo, string $hmacKey, int $tokenBytes = 32)
    {
        $this->pdo = $pdo;
        $this->hmacKey = $hmacKey;
        $this->tokenBytes = $tokenBytes;
    }

    private function generatePublicId(): string
    {
        $data = random_bytes(16);
        // format as UUID v4
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        $hex = bin2hex($data);
        return sprintf('%s-%s-%s-%s-%s', substr($hex, 0, 8), substr($hex, 8, 4), substr($hex, 12, 4), substr($hex, 16, 4), substr($hex, 20, 12));
    }

    /**
     * Create a trusted device record. By default the raw token is NOT returned.
     * If the caller needs the raw token for immediate cookie setting, pass
     * $returnPlainToken = true (be extremely careful; do not log it).
     *
     * @param string $userType 'admin'|'standard'
     * @param int $userId
     * @param array $deviceInfo keys: browser_name, operating_system, device_name, registration_ip
     * @param int|null $expiresDays
     * @param int|null $createdBy
     * @param bool $returnPlainToken
     *
     * @return array{trusted_device:array, token?:string}
     */
    public function createTrustedDevice(
        string $userType,
        int $userId,
        array $deviceInfo = [],
        ?int $expiresDays = 30,
        ?int $createdBy = null,
        bool $returnPlainToken = false
    ): array {
        $token = base64_encode(random_bytes($this->tokenBytes));
        $hash = hash_hmac('sha256', $token, $this->hmacKey);
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $expiresAt = null;

        if ($expiresDays !== null) {
            $expiresAt = (new DateTimeImmutable("+{$expiresDays} days"))->format('Y-m-d H:i:s');
        }

        $publicId = $this->generatePublicId();

        $sql = 'INSERT INTO trusted_devices
            (public_id, user_type, user_id, device_token_hash, device_token_algo, browser_name, operating_system, device_name, registration_ip, created_at, expires_at, created_by)
            VALUES (:public_id, :user_type, :user_id, :device_token_hash, :device_token_algo, :browser_name, :operating_system, :device_name, :registration_ip, :created_at, :expires_at, :created_by)';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_type' => $userType,
            ':user_id' => $userId,
            ':device_token_hash' => $hash,
            ':device_token_algo' => 'hmac-sha256',
            ':browser_name' => $deviceInfo['browser_name'] ?? null,
            ':operating_system' => $deviceInfo['operating_system'] ?? null,
            ':device_name' => $deviceInfo['device_name'] ?? null,
            ':registration_ip' => isset($deviceInfo['registration_ip']) ? inet_pton($deviceInfo['registration_ip']) : null,
            ':created_at' => $now,
            ':expires_at' => $expiresAt,
            ':created_by' => $createdBy,
                ':public_id' => $publicId,
        ]);

        $dto = new TrustedDevice(
            $userType,
            $userId,
            $deviceInfo['browser_name'] ?? null,
            $deviceInfo['operating_system'] ?? null,
            $deviceInfo['device_name'] ?? null,
            $deviceInfo['registration_ip'] ?? null,
            null,
            new DateTimeImmutable($now),
            null,
            $expiresDays !== null ? new DateTimeImmutable("+{$expiresDays} days") : null,
            null,
            null,
            $createdBy
        );

        $arr = $dto->toArray();
        // include opaque public id for client-side actions (safe to expose)
        $arr['public_id'] = $publicId;
        $result = ['trusted_device' => $arr];

        if ($returnPlainToken) {
            $result['token'] = $token;
        }

        return $result;
    }

    /**
     * Validate a provided token for a user. Returns trusted device info when valid.
     * Caller must provide the raw token (from cookie) for verification.
     */
    /**
     * Validate a provided token for a user. Returns trusted device info when valid.
     * If $rotateToken is true a new token will be issued (old token invalidated) and
     * returned in the `rotated_token` key for immediate cookie provisioning.
     * Caller MUST set the cookie immediately and never log the token.
     *
     * @return array|null  ['device'=>array, 'rotated_token'=>string|null]
     */
    public function validateTrustedDevice(string $userType, int $userId, string $token, ?string $requestIp = null, ?string $userAgent = null, bool $rotateToken = true): ?array
    {
        $hash = hash_hmac('sha256', $token, $this->hmacKey);

        $sql = 'SELECT user_type, user_id, browser_name, operating_system, device_name, INET6_NTOA(registration_ip) AS registration_ip, INET6_NTOA(last_used_ip) AS last_used_ip, created_at, last_used_at, expires_at, revoked_at, revoked_reason, created_by
                FROM trusted_devices
                WHERE user_type = :user_type AND user_id = :user_id AND device_token_hash = :hash
                  AND (revoked_at IS NULL)
                LIMIT 1';

        // Fetch candidate devices for user and perform constant-time comparison
        $stmt = $this->pdo->prepare('SELECT public_id, user_type, user_id, device_token_hash, browser_name, operating_system, device_name, INET6_NTOA(registration_ip) AS registration_ip, INET6_NTOA(last_used_ip) AS last_used_ip, created_at, last_used_at, expires_at, revoked_at, revoked_reason, created_by FROM trusted_devices WHERE user_type = :user_type AND user_id = :user_id AND revoked_at IS NULL');
        $stmt->execute([':user_type' => $userType, ':user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $row = null;
        foreach ($rows as $r) {
            if (isset($r['device_token_hash']) && hash_equals((string)$r['device_token_hash'], $hash)) {
                $row = $r;
                break;
            }
        }

        if (! $row) {
            return null;
        }

        // Check expiry
        if ($row['expires_at'] !== null && strtotime($row['expires_at']) <= time()) {
            return null;
        }

        // Update last used and optionally rotate token
        $rotatedToken = null;

        if ($rotateToken) {
            $rotatedToken = base64_encode(random_bytes($this->tokenBytes));
            $newHash = hash_hmac('sha256', $rotatedToken, $this->hmacKey);

            $update = 'UPDATE trusted_devices SET device_token_hash = :new_hash, device_token_algo = :algo, last_used_at = NOW(), last_used_ip = :last_used_ip, expires_at = DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE user_type = :user_type AND user_id = :user_id AND device_token_hash = :hash AND revoked_at IS NULL';
            $uStmt = $this->pdo->prepare($update);
            $uStmt->execute([
                ':new_hash' => $newHash,
                ':algo' => 'hmac-sha256',
                ':last_used_ip' => $requestIp ? inet_pton($requestIp) : null,
                ':user_type' => $userType,
                ':user_id' => $userId,
                ':hash' => $hash,
            ]);
        } else {
            $update = 'UPDATE trusted_devices SET last_used_at = NOW(), last_used_ip = :last_used_ip WHERE user_type = :user_type AND user_id = :user_id AND device_token_hash = :hash';
            $uStmt = $this->pdo->prepare($update);
            $uStmt->execute([
                ':last_used_ip' => $requestIp ? inet_pton($requestIp) : null,
                ':user_type' => $userType,
                ':user_id' => $userId,
                ':hash' => $hash,
            ]);
        }

        $dto = new TrustedDevice(
            $row['user_type'],
            (int) $row['user_id'],
            $row['browser_name'] ?? null,
            $row['operating_system'] ?? null,
            $row['device_name'] ?? null,
            $row['registration_ip'] ?? null,
            $row['last_used_ip'] ?? null,
            new DateTimeImmutable($row['created_at']),
            $row['last_used_at'] ? new DateTimeImmutable($row['last_used_at']) : null,
            $row['expires_at'] ? new DateTimeImmutable($row['expires_at']) : null,
            $row['revoked_at'] ? new DateTimeImmutable($row['revoked_at']) : null,
            $row['revoked_reason'] ?? null,
            $row['created_by'] ? (int) $row['created_by'] : null
        );

        $result = ['device' => $dto->toArray()];
        if ($rotatedToken !== null) {
            $result['rotated_token'] = $rotatedToken;
        }

        return $result;
    }

    /**
     * Extend device expiry by $days when provided valid token.
     * Returns true when extended, false otherwise.
     */
    public function extendTrustedDevice(string $userType, int $userId, string $token, int $days = 30): bool
    {
        $hash = hash_hmac('sha256', $token, $this->hmacKey);

        $sql = 'UPDATE trusted_devices
                SET expires_at = DATE_ADD(COALESCE(expires_at, NOW()), INTERVAL :days DAY), last_used_at = NOW()
                WHERE user_type = :user_type AND user_id = :user_id AND device_token_hash = :hash AND revoked_at IS NULL';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':days' => $days,
            ':user_type' => $userType,
            ':user_id' => $userId,
            ':hash' => $hash,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Revoke a single trusted device (by token). Returns true if revoked.
     */
    public function revokeTrustedDevice(string $userType, int $userId, string $token, ?string $reason = null, ?int $actor = null): bool
    {
        $hash = hash_hmac('sha256', $token, $this->hmacKey);

        $sql = 'UPDATE trusted_devices SET revoked_at = NOW(), revoked_reason = :reason, created_by = COALESCE(created_by, :actor) WHERE user_type = :user_type AND user_id = :user_id AND device_token_hash = :hash AND revoked_at IS NULL';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':reason' => $reason,
            ':actor' => $actor,
            ':user_type' => $userType,
            ':user_id' => $userId,
            ':hash' => $hash,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Revoke all devices for a user. Returns number of rows revoked.
     */
    public function revokeAllDevices(string $userType, int $userId, ?string $reason = null, ?int $actor = null): int
    {
        $sql = 'UPDATE trusted_devices SET revoked_at = NOW(), revoked_reason = :reason, created_by = COALESCE(created_by, :actor) WHERE user_type = :user_type AND user_id = :user_id AND revoked_at IS NULL';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':reason' => $reason,
            ':actor' => $actor,
            ':user_type' => $userType,
            ':user_id' => $userId,
        ]);

        return $stmt->rowCount();
    }

    /**
     * List trusted devices for a user. Returns safe fields and an opaque `public_id`.
     * `currentPublicId` can be provided to mark which device is current.
     */
    public function listTrustedDevices(string $userType, int $userId, ?string $currentPublicId = null, int $limit = 100): array
    {
        $sql = 'SELECT public_id, user_type, user_id, browser_name, operating_system, device_name, INET6_NTOA(registration_ip) AS registration_ip, INET6_NTOA(last_used_ip) AS last_used_ip, created_at, last_used_at, expires_at, revoked_at, revoked_reason, created_by
                FROM trusted_devices
                WHERE user_type = :user_type AND user_id = :user_id AND revoked_at IS NULL
                ORDER BY last_used_at DESC, created_at DESC
                LIMIT :limit';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_type', $userType);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'public_id' => $row['public_id'],
                'user_type' => $row['user_type'],
                'user_id' => isset($row['user_id']) ? (int)$row['user_id'] : null,
                'browser_name' => $row['browser_name'],
                'operating_system' => $row['operating_system'],
                'device_name' => $row['device_name'],
                'registration_ip' => $row['registration_ip'] ?? null,
                'last_used_ip' => $row['last_used_ip'] ?? null,
                'created_at' => $row['created_at'],
                'last_used_at' => $row['last_used_at'],
                'expires_at' => $row['expires_at'],
                'revoked_at' => $row['revoked_at'],
                'revoked_reason' => $row['revoked_reason'],
                'created_by' => $row['created_by'] ? (int)$row['created_by'] : null,
                'is_current' => $currentPublicId !== null && $currentPublicId === $row['public_id'],
            ];
        }

        return $out;
    }

    /**
     * List trusted devices for a user across all namespaces by user_id only.
     * Useful when the application needs to display devices strictly by numeric user id.
     */
    public function listTrustedDevicesByUserId(int $userId, ?string $currentPublicId = null, int $limit = 100): array
    {
        $sql = 'SELECT public_id, user_type, user_id, browser_name, operating_system, device_name, INET6_NTOA(registration_ip) AS registration_ip, INET6_NTOA(last_used_ip) AS last_used_ip, created_at, last_used_at, expires_at, revoked_at, revoked_reason, created_by
                FROM trusted_devices
                WHERE user_id = :user_id
                ORDER BY last_used_at DESC, created_at DESC
                LIMIT :limit';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'public_id' => $row['public_id'],
                'user_type' => $row['user_type'],
                'user_id' => isset($row['user_id']) ? (int)$row['user_id'] : null,
                'browser_name' => $row['browser_name'],
                'operating_system' => $row['operating_system'],
                'device_name' => $row['device_name'],
                'registration_ip' => $row['registration_ip'] ?? null,
                'last_used_ip' => $row['last_used_ip'] ?? null,
                'created_at' => $row['created_at'],
                'last_used_at' => $row['last_used_at'],
                'expires_at' => $row['expires_at'],
                'revoked_at' => $row['revoked_at'],
                'revoked_reason' => $row['revoked_reason'],
                'created_by' => $row['created_by'] ? (int)$row['created_by'] : null,
                'is_current' => $currentPublicId !== null && $currentPublicId === $row['public_id'],
            ];
        }

        return $out;
    }

    /**
     * Find the public_id associated with a raw token by numeric user_id only.
     */
    public function findPublicIdByTokenByUserId(int $userId, string $token): ?string
    {
        $hash = hash_hmac('sha256', $token, $this->hmacKey);

        $stmt = $this->pdo->prepare('SELECT public_id, device_token_hash FROM trusted_devices WHERE user_id = :user_id AND revoked_at IS NULL');
        $stmt->execute([':user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $r) {
            if (isset($r['device_token_hash']) && hash_equals((string)$r['device_token_hash'], $hash)) {
                return $r['public_id'];
            }
        }

        return null;
    }

    /**
     * Revoke a device by its opaque public id. Returns true if revoked.
     */
    public function revokeByPublicId(string $userType, int $userId, string $publicId, ?string $reason = null, ?int $actor = null): array
    {
        $sql = 'UPDATE trusted_devices SET revoked_at = NOW(), revoked_reason = :reason, created_by = COALESCE(created_by, :actor) WHERE user_type = :user_type AND user_id = :user_id AND public_id = :public_id AND revoked_at IS NULL';
        $params = [
            ':reason' => $reason,
            ':actor' => $actor,
            ':user_type' => $userType,
            ':user_id' => $userId,
            ':public_id' => $publicId,
        ];

        $stmt = $this->pdo->prepare($sql);
        $executed = $stmt->execute($params);
        $rowCount = $stmt->rowCount();
        $debug = [
            'sql' => $sql,
            'params' => $params,
            'executed' => $executed,
            'rowCount' => $rowCount,
            'errorInfo' => $stmt->errorInfo(),
        ];

        if (! $executed) {
            $debug['failure_reason'] = 'db_execute_failed';
            error_log('TrustedDeviceService::revokeByPublicId execute failed: ' . json_encode($debug));
            return ['success' => false, 'message' => 'db_error', 'reason' => 'db_execute_failed', 'data' => ['debug' => $debug], 'errors' => ['sql' => $debug]];
        }

        if ($rowCount === 0) {
            $existsStmt = $this->pdo->prepare('SELECT user_type, user_id, revoked_at, public_id FROM trusted_devices WHERE public_id = :public_id');
            $existsStmt->execute([':public_id' => $publicId]);
            $row = $existsStmt->fetch(PDO::FETCH_ASSOC);

            if ($row === false) {
                $debug['rowExists'] = false;
                $debug['failure_reason'] = 'public_id_not_found';
                return ['success' => false, 'message' => 'device_not_found', 'reason' => 'public_id_not_found', 'data' => ['debug' => $debug], 'errors' => ['reason' => 'device_not_found']];
            }

            $debug['rowExists'] = true;
            $debug['rowUserType'] = $row['user_type'];
            $debug['rowUserId'] = isset($row['user_id']) ? (int)$row['user_id'] : null;
            $debug['rowRevokedAt'] = $row['revoked_at'];
            $debug['rowPublicId'] = $row['public_id'];

            if ($row['revoked_at'] !== null) {
                $debug['failure_reason'] = 'already_revoked';
                return ['success' => false, 'message' => 'already_revoked', 'reason' => 'already_revoked', 'data' => ['debug' => $debug], 'errors' => ['reason' => 'already_revoked']];
            }

            // Ownership is authoritative in DB; if mismatch, report unauthorized
            if ($row['user_type'] !== $userType || (int)$row['user_id'] !== $userId) {
                $debug['failure_reason'] = 'user_mismatch';
                return ['success' => false, 'message' => 'unauthorized', 'reason' => 'user_mismatch', 'data' => ['debug' => $debug], 'errors' => ['reason' => 'user_mismatch']];
            }

            $debug['failure_reason'] = 'unknown';
            return ['success' => false, 'message' => 'revoke_failed', 'reason' => 'unknown', 'data' => ['debug' => $debug], 'errors' => ['reason' => 'unknown']];
        }

        error_log('TrustedDeviceService::revokeByPublicId succeeded: ' . json_encode($debug));
        return ['success' => true, 'message' => 'Trusted device revoked', 'data' => new \stdClass(), 'errors' => new \stdClass()];
    }

    /**
     * Revoke all devices except the given public id. Returns number revoked.
     */
    public function revokeAllExcept(string $userType, int $userId, ?string $exceptPublicId = null, ?string $reason = null, ?int $actor = null): int
    {
        $sql = 'UPDATE trusted_devices SET revoked_at = NOW(), revoked_reason = :reason, created_by = COALESCE(created_by, :actor) WHERE user_type = :user_type AND user_id = :user_id AND revoked_at IS NULL' . ($exceptPublicId ? ' AND public_id != :except_public' : '');
        $stmt = $this->pdo->prepare($sql);
        $params = [
            ':reason' => $reason,
            ':actor' => $actor,
            ':user_type' => $userType,
            ':user_id' => $userId,
        ];
        if ($exceptPublicId) {
            $params[':except_public'] = $exceptPublicId;
        }
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    /**
     * Find the public_id associated with a raw token without rotating or updating the row.
     */
    public function findPublicIdByToken(string $userType, int $userId, string $token): ?string
    {
        $hash = hash_hmac('sha256', $token, $this->hmacKey);

        $stmt = $this->pdo->prepare('SELECT public_id, device_token_hash FROM trusted_devices WHERE user_type = :user_type AND user_id = :user_id AND revoked_at IS NULL');
        $stmt->execute([':user_type' => $userType, ':user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $r) {
            if (isset($r['device_token_hash']) && hash_equals((string)$r['device_token_hash'], $hash)) {
                return $r['public_id'];
            }
        }

        return null;
    }

    /**
     * Cleanup expired or long-revoked devices. Returns number of deleted rows.
     * By default removes devices where expires_at < NOW() or revoked_at older than 30 days.
     */
    public function cleanupExpiredDevices(int $revokedOlderThanDays = 30, int $limit = 1000): int
    {
        $sql = 'DELETE FROM trusted_devices WHERE (expires_at IS NOT NULL AND expires_at < NOW()) OR (revoked_at IS NOT NULL AND revoked_at < DATE_SUB(NOW(), INTERVAL :revoked_days DAY)) LIMIT :limit';
        $stmt = $this->pdo->prepare($sql);
        // PDO requires integers bound explicitly
        $stmt->bindValue(':revoked_days', $revokedOlderThanDays, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount();
    }
}
