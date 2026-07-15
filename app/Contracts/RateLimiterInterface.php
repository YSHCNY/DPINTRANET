<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTO\RateLimitResult;

interface RateLimiterInterface
{
    public function isBlocked(string $username, string $ipAddress, ?string $actorType = null, ?int $actorId = null): bool;

    public function registerFailure(string $username, string $ipAddress, ?string $reason = null, ?string $actorType = null, ?int $actorId = null, ?string $userAgent = null): void;

    public function registerSuccess(string $username, string $ipAddress, ?string $actorType = null, ?int $actorId = null, ?string $userAgent = null): void;

    public function remainingLockSeconds(string $username, string $ipAddress, ?string $actorType = null, ?int $actorId = null): int;

    public function reset(string $username, string $ipAddress, ?string $actorType = null, ?int $actorId = null): void;

    public function getFailureCount(string $username, string $ipAddress, ?string $actorType = null, ?int $actorId = null): int;

    public function evaluateAttempt(string $username, string $ipAddress, bool $success, ?string $reason = null, ?string $actorType = null, ?int $actorId = null, ?string $userAgent = null): RateLimitResult;
}
