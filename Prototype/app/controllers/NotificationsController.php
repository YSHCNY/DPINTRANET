<?php
session_start();
require_once '../app/core/Controller.php';
require_once '../app/models/Notification.php';

class NotificationsController extends Controller {
    private $model;

    public function __construct() {
        $this->model = new NotificationModel();
    }

    // View notification: mark as read and redirect to stored url
    public function view($view = null, $data = []) {
        if (!isset($_SESSION['user'])) {
            $this->redirect('index.php?controller=Auth&action=login');
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('index.php?controller=correspondence&action=correspondence');
        }

        $notif = $this->model->findById($id);
        if (!$notif) {
            $this->redirect('index.php?controller=correspondence&action=correspondence');
        }

        $userId = (int)($_SESSION['id'] ?? 0);
        // Only allow owner to mark/read (or allow admins)
        if ($notif['user_id'] !== null && (int)$notif['user_id'] !== $userId) {
            // Not allowed; just redirect
            $this->redirect('index.php?controller=correspondence&action=correspondence');
        }

        // Mark as read
        $this->model->markRead($id);

        $url = trim((string)($notif['url'] ?? ''));
        if ($url === '') {
            $url = 'index.php?controller=correspondence&action=correspondence';
        }

        $redirect = $url;
        $parsed = parse_url($url);
        if (!empty($parsed['query'])) {
            parse_str($parsed['query'], $params);
            if (!empty($params['controller']) && strtolower($params['controller']) === 'correspondence') {
                if (!empty($params['doc_id'])) {
                    $docId = (int)$params['doc_id'];
                    if ($docId > 0) {
                        $redirect = 'index.php?controller=correspondence&action=show&id=' . $docId;
                    }
                }
            }
        }

        header('Location: ' . $redirect);
        exit;
    }
}
