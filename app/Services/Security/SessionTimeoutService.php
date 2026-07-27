<?php

namespace App\Services\Security;

class SessionTimeoutService
{
    private int $timeoutSeconds;
    private bool $autoRefresh;

    public function __construct(int $timeoutSeconds = 900, bool $autoRefresh = true)
    {
        $this->timeoutSeconds = max(5, $timeoutSeconds);
        $this->autoRefresh = $autoRefresh;
    }

    public function initialize(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
            return;
        }

        if ($this->isExpired()) {
            $this->clearSession();
            return;
        }

        if ($this->autoRefresh) {
            $this->touch();
        }
    }

    public function touch(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION['last_activity'] = time();
    }

    public function isExpired(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }

        $lastActivity = $_SESSION['last_activity'] ?? null;
        if (!is_numeric($lastActivity)) {
            $_SESSION['last_activity'] = time();
            return false;
        }

        return (time() - (int) $lastActivity) > $this->timeoutSeconds;
    }

    public function clearSession(array $preserveKeys = []): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $preservedValues = [];
        foreach ($preserveKeys as $key) {
            if (array_key_exists($key, $_SESSION)) {
                $preservedValues[$key] = $_SESSION[$key];
            }
        }

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_unset();
        $_SESSION = $preservedValues;
        session_regenerate_id(true);
    }

    public function getTimeoutMinutes(): int
    {
        return (int) ceil($this->timeoutSeconds / 60);
    }
}
