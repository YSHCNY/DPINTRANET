<?php

declare(strict_types=1);

namespace App\Services\Security;

final class LoginRateLimitConfig
{
    /**
     * @var array<int, int>
     */
    private array $lockDurations;

    /**
     * @var array<int, string>
     */
    private array $scopes;

    private int $resetWindowSeconds;

    /**
     * @param array<string, mixed>|null $config
     */
    public function __construct(?array $config = null)
    {
        $config = $config ?? [];

        $this->lockDurations = array_map('intval', (array)($config['lock_durations'] ?? [
            3 => 30,
            4 => 60,
            5 => 300,
            6 => 900,
            7 => 1800,
        ]));

        $this->scopes = array_values((array)($config['scopes'] ?? ['username', 'ip_address', 'username_ip']));
        $this->resetWindowSeconds = (int)($config['reset_window_seconds'] ?? $_ENV['LOGIN_RATE_LIMIT_RESET_WINDOW_SECONDS'] ?? 1800);
    }

    /**
     * @return array<int, string>
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function getResetWindowSeconds(): int
    {
        return $this->resetWindowSeconds;
    }

    /**
     * @return array<int, int>
     */
    public function getLockDurations(): array
    {
        return $this->lockDurations;
    }

    public function getLockDurationForFailureCount(int $failureCount): int
    {
        if ($failureCount < 3) {
            return 0;
        }

        return (int)($this->lockDurations[$failureCount] ?? $this->lockDurations[7] ?? 1800);
    }

    public function getScopeIdentifier(string $scope, string $username, string $ipAddress): string
    {
        return match ($scope) {
            'username' => $username,
            'ip_address' => $ipAddress,
            'username_ip' => $username . '|' . $ipAddress,
            default => $scope . ':' . $username . '|' . $ipAddress,
        };
    }
}
