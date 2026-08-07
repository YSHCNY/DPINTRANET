<?php
require_once __DIR__ . '/../Services/Security/SessionTimeoutService.php';

use App\Services\Security\SessionTimeoutService;

class Controller {
    // Role level constants
    public const LEVEL_SUPER_ADMIN = 0;
    public const LEVEL_ADMIN = 1;
    public const LEVEL_ENCODER = 2;
    public const LEVEL_VIEWER = 3;
    public const LEVEL_PROJECT_MANAGER = 4;
    public const LEVEL_DEPUTY_PROJECT_MANAGER = 5;
    public const LEVEL_GRP_HEAD = 6; // "User / GRP Head" — same as encoder
    protected function view($view, $data = []) {
        extract($data);
        require "../app/views/$view.php";
    }

       protected function renderView($view, $data = []) {
        extract($data);
        ob_start();  
        require __DIR__ . "/../views/$view.php";
        return ob_get_clean(); 
    }


    protected function requireLogin() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $timeoutSeconds = (int)($_ENV['SESSION_TIMEOUT_SECONDS'] ?? 900);
        $service = new SessionTimeoutService($timeoutSeconds, true);

        $isAuthenticated = isset($_SESSION['user'])
            || !empty($_SESSION['standard_user_id'])
            || !empty($_SESSION['user_id']);

        if (!$isAuthenticated) {
            if ($this->wantsJsonResponse()) {
                if (!headers_sent()) {
                    header('Content-Type: application/json; charset=utf-8');
                }
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Authentication required']);
                exit;
            }

            header("Location: index.php?controller=Auth&action=login");
            exit;
        }

        if ($service->isExpired()) {
            $recoveryUser = [
                'id' => (int)($_SESSION['id'] ?? $_SESSION['user_id'] ?? 0),
                'username' => (string)($_SESSION['user'] ?? ''),
                'userLevel' => (int)($_SESSION['user_level'] ?? 3),
                'position' => $_SESSION['position'] ?? '',
                'firstName' => $_SESSION['firstName'] ?? '',
                'lastName' => $_SESSION['lastName'] ?? '',
                'profile_picture' => $_SESSION['profile_picture'] ?? 'default.png',
            ];

            $_SESSION['session_expired'] = true;
            $_SESSION['session_expired_message'] = 'You have been logged out due to inactivity.';
            $_SESSION['session_recovery_user'] = $recoveryUser;
            $_SESSION['session_recovery_user_id'] = (int)($recoveryUser['id'] ?? 0);
            $service->clearSession(['session_expired', 'session_expired_message', 'session_recovery_user', 'session_recovery_user_id']);

            if ($this->wantsJsonResponse()) {
                if (!headers_sent()) {
                    header('Content-Type: application/json; charset=utf-8');
                }
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Session expired due to inactivity.']);
                exit;
            }

            header('Location: index.php?controller=Auth&action=login&expired=1');
            exit;
        }

        $service->touch();
    }

    protected function wantsJsonResponse(): bool {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            return true;
        }

        if (!empty($_SERVER['HTTP_ACCEPT']) && stripos((string)$_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            return true;
        }

        if (!empty($_GET['action']) && is_string($_GET['action']) && str_ends_with($_GET['action'], 'Ajax')) {
            return true;
        }

        return false;
    }

    /**
     * Send a standardized JSON response and exit.
     * Payload should contain keys: success, message, data, errors
     */
    protected function jsonResponse(array $payload, int $statusCode = 200): void {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        http_response_code($statusCode);
        // Ensure canonical shape
        $out = array_merge(['success' => false, 'message' => '', 'data' => new \stdClass(), 'errors' => new \stdClass()], $payload);
        echo json_encode($out);
        exit;
    }

    protected function currentUserLevel(): int {
        return (int)($_SESSION['user_level'] ?? 3);
    }

    protected function isSuperAdmin(): bool {
        return $this->currentUserLevel() === self::LEVEL_SUPER_ADMIN;
    }

    protected function isAdmin(): bool {
        $lvl = $this->currentUserLevel();
        return $lvl === self::LEVEL_ADMIN || $lvl === self::LEVEL_PROJECT_MANAGER || $lvl === self::LEVEL_DEPUTY_PROJECT_MANAGER;
    }

    protected function isEditor(): bool {
        $lvl = $this->currentUserLevel();
        return $lvl === self::LEVEL_ENCODER || $lvl === self::LEVEL_GRP_HEAD;
    }

    protected function isViewer(): bool {
        return $this->currentUserLevel() === 3;
    }

    protected function hasAnyRole(array $levels): bool {
        return in_array($this->currentUserLevel(), $levels, true);
    }

    protected function requireAnyRole(array $levels, string $message, string $redirectUrl = 'index.php?controller=Auth&action=dashboard'): void {
        $this->requireLogin();

        if ($this->hasAnyRole($levels)) {
            return;
        }

        $_SESSION['message'] = $message;
        $_SESSION['msg_type'] = 'error';
        $this->redirect($redirectUrl);
    }

    

    protected function redirect($url) {
        header("Location: $url");
        exit;
    }

    
}
