<?php
session_start();
require_once '../app/core/Controller.php';
require_once '../app/models/Notification.php';
require_once '../app/Services/NotificationRoutingService.php';

class AdminNotificationsController extends Controller {
    private $model;
    private $routingService;

    public function __construct() {
        $this->model = new NotificationModel();
        $this->routingService = new NotificationRoutingService();
    }

    private function getCurrentUserId(): int {
        return $this->routingService->getAdminUserId($_SESSION);
    }

    private function getLoginRedirectUrl(): string {
        return $this->routingService->getAdminLoginRedirectUrl();
    }

    public function view($view = null, $data = []) {
        if (!isset($_SESSION['user']) || empty($_SESSION['id'])) {
            $this->redirect($this->getLoginRedirectUrl());
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['message'] = 'Invalid notification selected.';
            $_SESSION['msg_type'] = 'error';
            $this->redirect('index.php?controller=correspondence&action=correspondence');
        }

        $userId = $this->getCurrentUserId();
        $notif = $this->model->findByIdForUser($id, $userId);
        if (!$notif) {
            $this->redirect('index.php?controller=correspondence&action=correspondence');
        }

        $this->model->markReadForUser($id, $userId);

        $redirect = $this->routingService->resolveAdminRedirect($notif);
        if ($redirect === '') {
            $redirect = 'index.php?controller=correspondence&action=correspondence';
        }

        header('Location: ' . $redirect);
        exit;
    }

    public function markAllRead() {
        if (!isset($_SESSION['user']) || empty($_SESSION['id'])) {
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }

            $this->redirect($this->getLoginRedirectUrl());
        }

        $userId = $this->getCurrentUserId();
        if ($userId <= 0) {
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid user']);
                exit;
            }
            $this->redirect('index.php?controller=correspondence&action=correspondence');
        }

        $this->model->markAllReadForUser($userId);

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }

        $this->redirect('index.php?controller=correspondence&action=correspondence');
    }
}
