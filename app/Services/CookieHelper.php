<?php

declare(strict_types=1);

namespace App\Services;

final class CookieHelper
{
    private static function isSecureRequest(): bool
    {
        if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') {
            return true;
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
            return true;
        }

        return false;
    }

    /**
     * Build a user-scoped trusted device cookie name.
     */
    public static function trustedDeviceCookieName(string $userType, int $userId): string
    {
        $normalizedType = strtolower(trim($userType));
        return sprintf('trusted_device_%s_%d', $normalizedType, $userId);
    }

    /**
     * Return parameters suitable for `setcookie()` when setting a trusted device token.
     * Controller should call: setcookie($name, $value, $options)
     *
     * @return array{name:string, value:string, options:array}
     */
    public static function trustedDeviceCookie(string $token, string $userType, int $userId, int $days = 30): array
    {
        $expires = time() + ($days * 24 * 60 * 60);

        $options = [
            'expires' => $expires,
            'path' => '/',
            'domain' => '',
            'secure' => self::isSecureRequest(),
            'httponly' => true,
            'samesite' => 'Lax',
        ];

        return [
            'name' => self::trustedDeviceCookieName($userType, $userId),
            'value' => $token,
            'options' => $options,
        ];
    }
}
