<?php
class Controller {
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
            header("Location: index.php?controller=Auth&action=login");
            exit;
        }
    }

    protected function currentUserLevel(): int {
        return (int)($_SESSION['user_level'] ?? 3);
    }

    protected function isSuperAdmin(): bool {
        return $this->currentUserLevel() === 0;
    }

    protected function isAdmin(): bool {
        return $this->currentUserLevel() === 1;
    }

    protected function isEditor(): bool {
        return $this->currentUserLevel() === 2;
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
