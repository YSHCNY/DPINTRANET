<?php

declare(strict_types=1);

namespace App\Services\Security;

use App\Contracts\RateLimiterInterface;
use App\DTO\RateLimitResult;
use App\Repositories\RateLimitRepository;

class StandardUserLoginRateLimiter implements RateLimiterInterface
{
    private RateLimiterInterface $decoratedLimiter;

    public function __construct(?RateLimiterInterface $decoratedLimiter = null)
    {
        $this->decoratedLimiter = $decoratedLimiter ?? new LoginRateLimiter();
    }

    public function isBlocked(string $username, string $ipAddress, ?string $actorType = null, ?int $actorId = null): bool
    {
        return $this->decoratedLimiter->isBlocked($username, $ipAddress, 'standard', $actorId);
    }

    public function registerFailure(string $username, string $ipAddress, ?string $reason = null, ?string $actorType = null, ?int $actorId = null, ?string $userAgent = null): void
    {
        $this->decoratedLimiter->registerFailure($username, $ipAddress, $reason, 'standard', $actorId, $userAgent);
    }

    public function registerSuccess(string $username, string $ipAddress, ?string $actorType = null, ?int $actorId = null, ?string $userAgent = null): void
    {
        $this->decoratedLimiter->registerSuccess($username, $ipAddress, 'standard', $actorId, $userAgent);
    }

    public function remainingLockSeconds(string $username, string $ipAddress, ?string $actorType = null, ?int $actorId = null): int
    {
        return $this->decoratedLimiter->remainingLockSeconds($username, $ipAddress, 'standard', $actorId);
    }

    public function reset(string $username, string $ipAddress, ?string $actorType = null, ?int $actorId = null): void
    {
        $this->decoratedLimiter->reset($username, $ipAddress, 'standard', $actorId);
    }

    public function getFailureCount(string $username, string $ipAddress, ?string $actorType = null, ?int $actorId = null): int
    {
        return $this->decoratedLimiter->getFailureCount($username, $ipAddress, 'standard', $actorId);
    }

    public function evaluateAttempt(string $username, string $ipAddress, bool $success, ?string $reason = null, ?string $actorType = null, ?int $actorId = null, ?string $userAgent = null): RateLimitResult
    {
        return $this->decoratedLimiter->evaluateAttempt($username, $ipAddress, $success, $reason, 'standard', $actorId, $userAgent);
    }
}
