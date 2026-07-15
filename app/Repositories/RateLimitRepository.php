<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use RuntimeException;

class RateLimitRepository
{
    private PDO $connection;

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?? \Database::connect();
    }

    public function getState(string $scope, string $identifier): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT scope, identifier, failure_count, last_failure_at, locked_until, created_at, updated_at
             FROM rate_limit_states
             WHERE scope = :scope AND identifier = :identifier
             LIMIT 1'
        );

        $statement->execute([
            ':scope' => $scope,
            ':identifier' => $identifier,
        ]);

        $state = $statement->fetch(PDO::FETCH_ASSOC);

        return $state ?: null;
    }

    public function saveState(string $scope, string $identifier, int $failureCount, ?string $lockedUntil, string $lastFailureAt): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO rate_limit_states (scope, identifier, failure_count, last_failure_at, locked_until, created_at, updated_at)
             VALUES (:scope, :identifier, :failure_count, :last_failure_at, :locked_until, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                failure_count = VALUES(failure_count),
                last_failure_at = VALUES(last_failure_at),
                locked_until = VALUES(locked_until),
                updated_at = NOW()'
        );

        $statement->execute([
            ':scope' => $scope,
            ':identifier' => $identifier,
            ':failure_count' => $failureCount,
            ':last_failure_at' => $lastFailureAt,
            ':locked_until' => $lockedUntil,
        ]);
    }

    public function clearState(string $scope, string $identifier): void
    {
        $statement = $this->connection->prepare(
            'DELETE FROM rate_limit_states WHERE scope = :scope AND identifier = :identifier'
        );

        $statement->execute([
            ':scope' => $scope,
            ':identifier' => $identifier,
        ]);
    }

    public function recordLoginAttempt(string $username, string $ipAddress, bool $success, ?string $failureReason, ?string $actorType, ?int $actorId, ?string $userAgent): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO login_attempts (actor_type, actor_id, username, ip_address, attempted_at, success, failure_reason, user_agent, created_at)
             VALUES (:actor_type, :actor_id, :username, :ip_address, NOW(), :success, :failure_reason, :user_agent, NOW())'
        );

        $statement->execute([
            ':actor_type' => $actorType ?? 'system',
            ':actor_id' => $actorId,
            ':username' => $username !== '' ? $username : null,
            ':ip_address' => $ipAddress !== '' ? $ipAddress : null,
            ':success' => $success ? 1 : 0,
            ':failure_reason' => $failureReason,
            ':user_agent' => $userAgent,
        ]);
    }

    public function writeAuditLog(string $action, ?string $actorType, ?int $actorId, string $ipAddress, array $metadata = []): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO audit_logs (actor_type, actor_id, action, entity_type, entity_id, ip_address, metadata, created_at)
             VALUES (:actor_type, :actor_id, :action, :entity_type, :entity_id, :ip_address, :metadata, NOW())'
        );

        $statement->execute([
            ':actor_type' => $actorType ?? 'system',
            ':actor_id' => $actorId,
            ':action' => $action,
            ':entity_type' => 'login_rate_limit',
            ':entity_id' => null,
            ':ip_address' => $ipAddress !== '' ? $ipAddress : null,
            ':metadata' => $metadata === [] ? null : json_encode($metadata, JSON_UNESCAPED_SLASHES),
        ]);
    }
}
