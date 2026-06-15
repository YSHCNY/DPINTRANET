<?php
session_start();
require_once '../app/core/Controller.php';
require_once '../app/models/UserModel.php';
require_once '../app/models/correspondence.php';

class StandardPortalController extends Controller {
    private $userModel;
    private $correspondenceModel;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->correspondenceModel = new CorrespondenceModel();
    }

    public function login() {
        if (isset($_SESSION['standard_user_id'])) {
            $this->redirect('index.php?controller=StandardPortal&action=dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $user = $this->userModel->findPortalUserByUsername($username);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['standard_user_id'] = $user['id'];
                $_SESSION['standard_user_name'] = trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? ''));
                $_SESSION['standard_user_position'] = $user['position'] ?? '';
                $_SESSION['standard_user_department'] = $user['department'] ?? '';
                $_SESSION['standard_user_avatar'] = $user['avatar'] ?? null;
                $_SESSION['standard_user_pin'] = $user['pin_code'] ?? '';
                $this->redirect('index.php?controller=StandardPortal&action=dashboard');
            }

            $this->view('standard_portal/login', ['error' => 'Invalid portal credentials or portal access is disabled.']);
            return;
        }

        $this->view('standard_portal/login');
    }

    public function dashboard() {
        $this->requirePortalLogin();

        $recipientId = $_SESSION['standard_user_id'];
        $showRemovedItems = $this->getRemovedItemsPreference();
        $this->view('standard_portal/dashboard', [
            'stats' => $this->correspondenceModel->getPortalStats($recipientId, $showRemovedItems),
            'documents' => array_slice($this->correspondenceModel->getPortalDocuments($recipientId, $showRemovedItems), 0, 6),
            'showRemovedItems' => $showRemovedItems,
        ]);
    }

    public function inbox() {
        $this->requirePortalLogin();

        $showRemovedItems = $this->getRemovedItemsPreference();
        $this->view('standard_portal/inbox', [
            'documents' => $this->correspondenceModel->getPortalDocuments($_SESSION['standard_user_id'], $showRemovedItems),
            'showRemovedItems' => $showRemovedItems,
        ]);
    }

    public function setRemovedItemsPreference() {
        $this->requirePortalLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $enabled = filter_var($_POST['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $this->setRemovedItemsPreferenceValue($enabled);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'show_removed_items' => $enabled,
        ]);
        exit;
    }

    public function viewDocument($id) {
        $this->requirePortalLogin();

        $document = $this->correspondenceModel->getPortalDocument($id, $_SESSION['standard_user_id']);
        if (!$document) {
            $_SESSION['portal_message'] = 'Document not found or not assigned to you.';
            $_SESSION['portal_msg_type'] = 'error';
            $this->redirect('index.php?controller=StandardPortal&action=inbox');
        }

        if (!empty($document['is_deleted'])) {
            $_SESSION['portal_message'] = 'This document was deleted by the sender. View-only access is available.';
            $_SESSION['portal_msg_type'] = 'error';
        }

        $this->view('standard_portal/details', [
            'document' => $document,
            'attachments' => $this->correspondenceModel->getAttachments($id),
            'history' => $this->correspondenceModel->getDocumentChangeHistory($id),
        ]);
    }

    public function receive($id) {
        $this->requirePortalLogin();

        $document = $this->correspondenceModel->getPortalDocument($id, $_SESSION['standard_user_id']);
        if (!$document) {
            $_SESSION['portal_message'] = 'Document not found or not assigned to you.';
            $_SESSION['portal_msg_type'] = 'error';
            $this->redirect('index.php?controller=StandardPortal&action=inbox');
        }

        if (!empty($document['is_deleted'])) {
            $_SESSION['portal_message'] = 'This document was deleted by the sender and can no longer be received.';
            $_SESSION['portal_msg_type'] = 'error';
            $this->redirect('index.php?controller=StandardPortal&action=viewDocument&id=' . (int)$id);
        }

        $pin = trim($_POST['pin_code'] ?? '');
        $remarks = trim($_POST['remarks'] ?? '');

        if ($pin === '' || !hash_equals((string)($_SESSION['standard_user_pin'] ?? ''), $pin)) {
            $_SESSION['portal_message'] = 'Invalid PIN code.';
            $_SESSION['portal_msg_type'] = 'error';
            $this->redirect('index.php?controller=StandardPortal&action=viewDocument&id=' . (int)$id);
        }

        $this->correspondenceModel->markPortalDocumentReceived($id, $_SESSION['standard_user_id'], $pin, $remarks);

        $_SESSION['portal_message'] = 'Document marked as received.';
        $_SESSION['portal_msg_type'] = 'success';
        $this->redirect('index.php?controller=StandardPortal&action=viewDocument&id=' . (int)$id);
    }

    public function download($id) {
        $this->requirePortalLogin();

        $attachment = $this->correspondenceModel->getPortalAttachment($id, $_SESSION['standard_user_id']);
        if (!$attachment || empty($attachment['file_path']) || !file_exists($attachment['file_path'])) {
            $_SESSION['portal_message'] = 'Attachment not found.';
            $_SESSION['portal_msg_type'] = 'error';
            $this->redirect('index.php?controller=StandardPortal&action=inbox');
        }

        if (ob_get_level()) {
            ob_end_clean();
        }

        $fullPath = $attachment['file_path'];
        $downloadName = $attachment['file_name'] ?: basename($fullPath);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fullPath);
        finfo_close($finfo);

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . basename($downloadName) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }

    public function logout() {
        unset(
            $_SESSION['standard_user_id'],
            $_SESSION['standard_user_name'],
            $_SESSION['standard_user_position'],
            $_SESSION['standard_user_department'],
            $_SESSION['standard_user_avatar'],
            $_SESSION['standard_user_pin']
        );
        $this->redirect('index.php?controller=StandardPortal&action=login');
    }

    private function requirePortalLogin() {
        if (!isset($_SESSION['standard_user_id'])) {
            $this->redirect('index.php?controller=StandardPortal&action=login');
        }
    }

    private function getRemovedItemsPreference(): bool {
        $userId = (int)($_SESSION['standard_user_id'] ?? 0);
        return (bool)($_SESSION['standard_portal_prefs'][$userId]['show_removed_items'] ?? false);
    }

    private function setRemovedItemsPreferenceValue(bool $enabled): void {
        $userId = (int)($_SESSION['standard_user_id'] ?? 0);

        if (!isset($_SESSION['standard_portal_prefs'])) {
            $_SESSION['standard_portal_prefs'] = [];
        }

        if (!isset($_SESSION['standard_portal_prefs'][$userId])) {
            $_SESSION['standard_portal_prefs'][$userId] = [];
        }

        $_SESSION['standard_portal_prefs'][$userId]['show_removed_items'] = $enabled;
    }
}
