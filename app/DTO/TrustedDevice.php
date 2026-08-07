<?php

declare(strict_types=1);

namespace App\DTO;

use DateTimeImmutable;

final class TrustedDevice
{
    public function __construct(
        private string $userType,
        private int $userId,
        private ?string $browserName,
        private ?string $operatingSystem,
        private ?string $deviceName,
        private ?string $registrationIp,
        private ?string $lastUsedIp,
        private DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $lastUsedAt,
        private ?DateTimeImmutable $expiresAt,
        private ?DateTimeImmutable $revokedAt,
        private ?string $revokedReason,
        private ?int $createdBy
    ) {
    }

    public function toArray(): array
    {
        return [
            'user_type' => $this->userType,
            'browser_name' => $this->browserName,
            'operating_system' => $this->operatingSystem,
            'device_name' => $this->deviceName,
            'registration_ip' => $this->registrationIp,
            'last_used_ip' => $this->lastUsedIp,
            'created_at' => $this->createdAt->format(DATE_ATOM),
            'last_used_at' => $this->lastUsedAt?->format(DATE_ATOM),
            'expires_at' => $this->expiresAt?->format(DATE_ATOM),
            'revoked_at' => $this->revokedAt?->format(DATE_ATOM),
            'revoked_reason' => $this->revokedReason,
            'created_by' => $this->createdBy,
        ];
    }
}
