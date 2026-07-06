<?php
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
        if (!isset($_SESSION['user'])) {
            $isAjax = false;
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                $isAjax = true;
            } elseif (!empty($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                $isAjax = true;
            }

            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Authentication required']);
                exit;
            }

            header("Location: index.php?controller=Auth&action=login");
            exit;
        }
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
