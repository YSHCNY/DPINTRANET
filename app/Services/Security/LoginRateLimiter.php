<?php

declare(strict_types=1);

namespace App\Services\Security;

use App\Contracts\RateLimiterInterface;
use App\DTO\RateLimitResult;
use App\Repositories\RateLimitRepository;

class LoginRateLimiter implements RateLimiterInterface
{
    private LoginRateLimitConfig $config;

    private RateLimitRepository $repository;

    public function __construct(?RateLimitRepository $repository = null, ?LoginRateLimitConfig $config = null)
    {
        $this->repository = $repository ?? new RateLimitRepository();
        $this->config = $config ?? new LoginRateLimitConfig();
    }

    public function isBlocked(string $username, string $ipAddress): bool
    {
        foreach ($this->config->getScopes() as $scope) {
            $identifier = $this->config->getScopeIdentifier($scope, $username, $ipAddress);
            $state = $this->repository->getState($scope, $identifier);

            if ($state === null) {
                continue;
            }

            if ($this->shouldResetFailureCount($state)) {
                $this->repository->clearState($scope, $identifier);
                continue;
            }

            if ($this->hasActiveLock($state)) {
                return true;
            }

            if ($this->hasExpiredLock($state)) {
                $this->repository->clearState($scope, $identifier);
                $this->repository->writeAuditLog('login_rate_limit_lockout_expired', null, $ipAddress, [
                    'scope' => $scope,
                    'identifier' => $identifier,
                ]);
            }
        }

        return false;
    }

    public function registerFailure(string $username, string $ipAddress, ?string $reason = null, ?int $userId = null, ?string $userAgent = null): void
    {
        $this->evaluateAttempt($username, $ipAddress, false, $reason ?? 'invalid_credentials', $userId, $userAgent);
    }

    public function registerSuccess(string $username, string $ipAddress, ?int $userId = null, ?string $userAgent = null): void
    {
        $this->evaluateAttempt($username, $ipAddress, true, null, $userId, $userAgent);
    }

    public function remainingLockSeconds(string $username, string $ipAddress): int
    {
        $remainingSeconds = 0;

        foreach ($this->config->getScopes() as $scope) {
            $identifier = $this->config->getScopeIdentifier($scope, $username, $ipAddress);
            $state = $this->repository->getState($scope, $identifier);

            if ($state === null) {
                continue;
            }

            if ($this->shouldResetFailureCount($state)) {
                $this->repository->clearState($scope, $identifier);
                continue;
            }

            if ($this->hasExpiredLock($state)) {
                $this->repository->clearState($scope, $identifier);
                continue;
            }

            if ($this->hasActiveLock($state)) {
                $remainingSeconds = max($remainingSeconds, $this->getRemainingLockSeconds($state));
            }
        }

        return $remainingSeconds;
    }

    public function reset(string $username, string $ipAddress): void
    {
        foreach ($this->config->getScopes() as $scope) {
            $identifier = $this->config->getScopeIdentifier($scope, $username, $ipAddress);
            $this->repository->clearState($scope, $identifier);
        }
    }

    public function getFailureCount(string $username, string $ipAddress): int
    {
        $maxFailureCount = 0;

        foreach ($this->config->getScopes() as $scope) {
            $identifier = $this->config->getScopeIdentifier($scope, $username, $ipAddress);
            $state = $this->repository->getState($scope, $identifier);

            if ($state === null) {
                continue;
            }

            if ($this->shouldResetFailureCount($state)) {
                $this->repository->clearState($scope, $identifier);
                continue;
            }

            $maxFailureCount = max($maxFailureCount, (int)($state['failure_count'] ?? 0));
        }

        return $maxFailureCount;
    }

    public function evaluateAttempt(string $username, string $ipAddress, bool $success, ?string $reason = null, ?int $userId = null, ?string $userAgent = null): RateLimitResult
    {
        $reason = $reason ?? 'invalid_credentials';

        if ($success) {
            foreach ($this->config->getScopes() as $scope) {
                $identifier = $this->config->getScopeIdentifier($scope, $username, $ipAddress);
                $this->repository->clearState($scope, $identifier);
            }

            $this->repository->recordLoginAttempt($username, $ipAddress, true, null, $userId, $userAgent);
            return new RateLimitResult(false, 0, 0, '');
        }

        if ($reason === 'rate_limited') {
            foreach ($this->config->getScopes() as $scope) {
                $identifier = $this->config->getScopeIdentifier($scope, $username, $ipAddress);
                $state = $this->repository->getState($scope, $identifier);

                if ($state === null) {
                    continue;
                }

                if ($this->hasActiveLock($state)) {
                    $this->repository->writeAuditLog('login_rate_limit_repeated_abuse', $userId, $ipAddress, [
                        'scope' => $scope,
                        'identifier' => $identifier,
                        'remaining_lock_seconds' => $this->getRemainingLockSeconds($state),
                    ]);
                }
            }

            $this->repository->recordLoginAttempt($username, $ipAddress, false, 'rate_limited', $userId, $userAgent);
            return new RateLimitResult(true, $this->remainingLockSeconds($username, $ipAddress), $this->getFailureCount($username, $ipAddress), 'Login temporarily blocked.');
        }

        $maxFailureCount = 0;

        foreach ($this->config->getScopes() as $scope) {
            $identifier = $this->config->getScopeIdentifier($scope, $username, $ipAddress);
            $state = $this->repository->getState($scope, $identifier);
            $now = new \DateTimeImmutable('now');

            if ($state === null) {
                $failureCount = 1;
                $lastFailureAt = $now->format('Y-m-d H:i:s');
                $lockedUntil = null;
            } else {
                if ($this->shouldResetFailureCount($state)) {
                    $this->repository->clearState($scope, $identifier);
                    $failureCount = 1;
                    $lastFailureAt = $now->format('Y-m-d H:i:s');
                    $lockedUntil = null;
                } elseif ($this->hasExpiredLock($state)) {
                    $this->repository->clearState($scope, $identifier);
                    $failureCount = 1;
                    $lastFailureAt = $now->format('Y-m-d H:i:s');
                    $lockedUntil = null;
                } else {
                    $failureCount = (int)($state['failure_count'] ?? 0) + 1;
                    $lastFailureAt = $now->format('Y-m-d H:i:s');
                    $lockedUntil = null;
                }
            }

            $lockDurationSeconds = $this->config->getLockDurationForFailureCount($failureCount);
            if ($lockDurationSeconds > 0) {
                $lockedUntil = $now->modify('+' . $lockDurationSeconds . ' seconds')->format('Y-m-d H:i:s');
            }

            $this->repository->saveState($scope, $identifier, $failureCount, $lockedUntil, $lastFailureAt);
            $maxFailureCount = max($maxFailureCount, $failureCount);

            if ($lockDurationSeconds > 0) {
                $this->repository->writeAuditLog('login_rate_limit_lockout_created', $userId, $ipAddress, [
                    'scope' => $scope,
                    'identifier' => $identifier,
                    'failure_count' => $failureCount,
                    'lock_seconds' => $lockDurationSeconds,
                ]);
            }
        }

        $this->repository->recordLoginAttempt($username, $ipAddress, false, 'invalid_credentials', $userId, $userAgent);

        return new RateLimitResult(false, 0, $maxFailureCount, '');
    }

    private function hasActiveLock(array $state): bool
    {
        if (empty($state['locked_until'])) {
            return false;
        }

        return (new \DateTimeImmutable($state['locked_until'])) > new \DateTimeImmutable('now');
    }

    private function hasExpiredLock(array $state): bool
    {
        if (empty($state['locked_until'])) {
            return false;
        }

        return (new \DateTimeImmutable($state['locked_until'])) <= new \DateTimeImmutable('now');
    }

    private function getRemainingLockSeconds(array $state): int
    {
        if (empty($state['locked_until'])) {
            return 0;
        }

        $lockedUntil = new \DateTimeImmutable($state['locked_until']);
        $now = new \DateTimeImmutable('now');

        return max(0, $lockedUntil->getTimestamp() - $now->getTimestamp());
    }

    private function shouldResetFailureCount(array $state): bool
    {
        if (empty($state['last_failure_at'])) {
            return false;
        }

        $lastFailureAt = new \DateTimeImmutable($state['last_failure_at']);
        $resetThreshold = (new \DateTimeImmutable('now'))->modify('-' . $this->config->getResetWindowSeconds() . ' seconds');

        return $lastFailureAt < $resetThreshold;
    }
}
