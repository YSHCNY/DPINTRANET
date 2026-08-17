<?php

class OtpService
{
    public function generateCode(): string
    {
        return sprintf('%06d', random_int(0, 999999));
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
        $expiry = new DateTimeImmutable($expiresAt);
        return $expiry <= new DateTimeImmutable('now');
    }
}