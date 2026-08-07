<?php
namespace App\Services;

class AuthContextService
{
    /**
     * Resolve the authenticated user context from the authoritative login session.
     *
     * Returns:
     *  [ 'userType' => 'admin'|'standard', 'userId' => int ]
     *
     * Throws RuntimeException when no valid authenticated context is present.
     */
    public static function getAuthenticatedUser(): array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $userType = isset($_SESSION['auth_user_type']) ? strtolower((string)$_SESSION['auth_user_type']) : null;
        $userId = isset($_SESSION['auth_user_id']) ? (int)$_SESSION['auth_user_id'] : 0;

        if ($userType === 'admin') {
            if ($userId <= 0) {
                throw new \RuntimeException('Authenticated admin context missing user id');
            }
            return ['userType' => 'admin', 'userId' => $userId];
        }

        if ($userType === 'standard') {
            if ($userId <= 0) {
                throw new \RuntimeException('Authenticated standard context missing user id');
            }
            return ['userType' => 'standard', 'userId' => $userId];
        }

        // Compatibility fallback for sessions that still use legacy login state.
        if (!empty($_SESSION['standard_user_id'])) {
            return ['userType' => 'standard', 'userId' => (int)$_SESSION['standard_user_id']];
        }

        if (!empty($_SESSION['user_type']) && !empty($_SESSION['user_id'])) {
            $fallbackType = strtolower((string)$_SESSION['user_type']);
            if (in_array($fallbackType, ['admin', 'standard'], true)) {
                return ['userType' => $fallbackType, 'userId' => (int)$_SESSION['user_id']];
            }
        }

        // Last resort fallback for existing admin sessions that only have user_id.
        if (!empty($_SESSION['user_id'])) {
            return ['userType' => 'admin', 'userId' => (int)$_SESSION['user_id']];
        }

        throw new \RuntimeException('Missing authenticated user context');
    }
}
