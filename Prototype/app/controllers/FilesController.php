<?php
session_start();
require_once '../app/core/Controller.php';
require_once '../app/models/User.php';
require_once "../app/models/Files.php";
require_once "../app/models/FilesCateg.php";

class FilesController extends Controller {

    private $model;
    private $filesCategModel;

    public function __construct() {
        $this->model = new FileModel();
        $this->filesCategModel = new FilesCategModel();
    }

    public function files() {
        $this->requireLogin();

        // render files list view
        $content = $this->renderView('files/index', [
            'files' => $this->model->getAll(),
            'filesCateg' => $this->filesCategModel->getAllCateg(),
            'canManageFiles' => $this->canManageFiles(),
        ]);

        $this->view('layout/main', [
            'content' => $content
        ]);
    }

    // Show form
    public function create() {
        $this->requireAnyRole([0, 1, 2], 'You do not have permission to upload files.');

        // Get categories
        $filesCateg = $this->filesCategModel->getAllCateg();
        $recipientsCateg = $this->filesCategModel->getAllRecipientsCateg();

        require __DIR__ . '/../views/files/create.php'; 
    }

    // Store new file
    public function store() {
        $this->requireAnyRole([0, 1, 2], 'You do not have permission to upload files.');

        if (isset($_FILES['file'])) {
            $fileName = $_FILES['file']['name'];
            $targetDir = __DIR__ . "/../uploads/";
            $targetFile = $targetDir . basename($fileName);

            $description   = $_POST['description'] ?? '';
            $uploaded_by   = $_SESSION['id'] ?? 'guest';
            $position      = $_SESSION['position'] ?? 'guest';
            $directionFrom = $_POST['fromCategory'] ?? '';
            $directionTo   = $_POST['toCategory'] ?? '';
            $fileCategory  = $_POST['fileCategory'] ?? 'Uncategorized';

            $first    = $_SESSION['firstName'] ?? '';
            $last     = $_SESSION['lastName'] ?? '';
            $uploader = trim($first . ' ' . $last) ?: 'System';
            $userID   = $_SESSION['id'] ?? 'guest';

            if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
                $this->model->create($fileName, $targetFile, $description, $uploaded_by, $fileCategory, $position, $directionFrom, $directionTo);
                $this->model->newLog($userID, $fileName, $uploaded_by, $position, $fileCategory, $directionFrom, $directionTo, $uploader, $description);
                
                $_SESSION['message'] = "File uploaded successfully!";
                $_SESSION['msg_type'] = "success";
                header("Location: index.php?controller=Files&action=files");
                exit;
            } else {
                $_SESSION['message'] = "Failed to upload file.";
                $_SESSION['msg_type'] = "error";
                header("Location: index.php?controller=Files&action=files");
                exit;
            }
        }
    }

    // Edit form
    public function edit($id) {
        $this->requireAnyRole([0, 1, 2], 'You do not have permission to edit files.');

        $file = $this->model->getById($id);
        $filesCateg = $this->filesCategModel->getAllCateg();
        $recipientsCateg = $this->filesCategModel->getAllRecipientsCateg();

        require __DIR__ . '/../views/files/edit.php'; 
    }

    // Update file
    public function update($id) {
        $this->requireAnyRole([0, 1, 2], 'You do not have permission to edit files.');

        $file = $this->model->getById($id);
        $filename = $file['filename'];
        $filepath = $file['filepath'];
        $description = $_POST['description'] ?? $file['desc'] ?? '';
        $fileCategory = $_POST['fileCategory'] ?? $file['category'] ?? '';

        $first = $_SESSION['firstName'] ?? '';
        $last  = $_SESSION['lastName'] ?? '';
        $userID = $_SESSION['id'] ?? 'guest';
        $uploader = trim($first . ' ' . $last) ?: 'System';

        // Check if a new file is uploaded
        if (isset($_FILES['file']) && $_FILES['file']['name'] != "") {
            if (file_exists($filepath)) {
                unlink($filepath);
            }

            $filename = $_FILES['file']['name'];
            $filepath = "uploads/" . $filename;
            move_uploaded_file($_FILES['file']['tmp_name'], $filepath);
        }

        $this->model->update($id, $filename, $filepath, $description, $fileCategory);
        $this->model->updateLog($id, $userID, $filename, $filepath, $description, $fileCategory, $uploader);

        $_SESSION['message'] = $filename . " has been edited successfully!";
        $_SESSION['msg_type'] = "success";
        header("Location: index.php?controller=Files&action=files");
        exit;
    }

    // Delete file
    public function delete($id) {
        $this->requireAnyRole([0, 1, 2], 'You do not have permission to delete files.');

        $first = $_SESSION['firstName'] ?? '';
        $last  = $_SESSION['lastName'] ?? '';
        $userID = $_SESSION['id'] ?? 'guest';
        $uploader = trim($first . ' ' . $last) ?: 'System';

        $file = $this->model->getById($id);
        
        $filename     = $file['filename'] ?? '';
        $filepath     = $file['filepath'] ?? '';
        $description  = $file['desc'] ?? '';
        $fileCategory = $file['category'] ?? '';

        if (file_exists($filepath)) {
            unlink($filepath);
        }

        $this->model->deleteLog($id, $userID, $filename, $filepath, $description, $fileCategory, $uploader);
        $this->model->delete($id);

        $_SESSION['message'] = "File deleted successfully!";
        $_SESSION['msg_type'] = "success";
        header("Location: index.php?controller=Files&action=files");
        exit;
    }

    public function download() {
        $this->requireLogin();

        if (empty($_GET['file'])) {
            die("No file specified.");
        }

        $fileName = basename(urldecode($_GET['file']));
        $uploadDir = __DIR__ . "/../uploads/";
        $fullPath = $uploadDir . $fileName;

        if (!file_exists($fullPath)) {
            die("File not found.");
        }

        if (ob_get_level()) {
            ob_end_clean();
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fullPath);
        finfo_close($finfo);

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($fullPath));

        $fp = fopen($fullPath, 'rb');
        if ($fp) {
            while (!feof($fp)) {
                echo fread($fp, 8192);
                flush();
            }
            fclose($fp);
        }
        exit;
    }

    public function logout() {
        session_destroy();
        $this->redirect('index.php?controller=Auth&action=login');
    }

    private function canManageFiles(): bool {
        return $this->hasAnyRole([0, 1, 2]);
    }
}
