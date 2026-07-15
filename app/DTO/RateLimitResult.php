<?php

declare(strict_types=1);

namespace App\DTO;

final class RateLimitResult
{
    public function __construct(
        private bool $isBlocked,
        private int $remainingLockSeconds,
        private int $failureCount = 0,
        private string $message = ''
    ) {
    }

    public function isBlocked(): bool
    {
        return $this->isBlocked;
    }

    public function remainingLockSeconds(): int
    {
        return $this->remainingLockSeconds;
    }

    public function failureCount(): int
    {
        return $this->failureCount;
    }

    public function message(): string
    {
        return $this->message;
    }
}
