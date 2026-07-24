<?php
session_start();
require_once '../app/core/Controller.php';
require_once '../app/models/User.php';
require_once "../app/models/Files.php";
require_once "../app/models/FilesCateg.php";
if (file_exists(__DIR__ . '/../config.php')) {
    require_once __DIR__ . '/../config.php';
} elseif (file_exists(__DIR__ . '/../../app/config.php')) {
    require_once __DIR__ . '/../../app/config.php';
}

class FilesController extends Controller {

    private $model;
    private $filesCategModel;

    public function __construct() {
        $this->model = new FileModel();
        $this->filesCategModel = new FilesCategModel();
    }

    private function getProjectRootDirectory(): string {
        $projectRoot = realpath(__DIR__ . '/../../');
        return $projectRoot !== false
            ? rtrim($projectRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
            : rtrim(__DIR__ . '/../../', DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    private function getCandidateUploadDirectories(): array {
        $candidates = [];
        $roots = [
            realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..') ?: (__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..'),
            realpath(__DIR__ . DIRECTORY_SEPARATOR . '..') ?: (__DIR__ . DIRECTORY_SEPARATOR . '..'),
        ];

        foreach ($roots as $root) {
            if ($root !== '' && $root !== false) {
                $candidates[] = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR;
            }
        }

        $envRoots = [
            getenv('UPLOADS_DIR'),
            getenv('APP_ROOT'),
            getenv('DOCUMENT_ROOT'),
            $_ENV['UPLOADS_DIR'] ?? null,
            $_ENV['APP_ROOT'] ?? null,
            $_ENV['DOCUMENT_ROOT'] ?? null,
        ];

        foreach ($envRoots as $envRoot) {
            if (!empty($envRoot)) {
                $envRoot = trim($envRoot);
                if ($envRoot === '') {
                    continue;
                }

                $cleanRoot = rtrim($envRoot, DIRECTORY_SEPARATOR);
                if (basename($cleanRoot) === 'files') {
                    $candidates[] = $cleanRoot . DIRECTORY_SEPARATOR;
                } else {
                    $candidates[] = $cleanRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR;
                }
            }
        }

        $candidates[] = '/var/www/html/uploads/files/';
        $candidates[] = '/app/uploads/files/';
        $candidates[] = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'dpintranet_uploads' . DIRECTORY_SEPARATOR;

        return array_values(array_unique($candidates));
    }

    private function getUploadDirectory(): string {
        foreach ($this->getCandidateUploadDirectories() as $candidateDir) {
            if (!is_dir($candidateDir) && !mkdir($candidateDir, 0755, true) && !is_dir($candidateDir)) {
                continue;
            }

            if (is_dir($candidateDir) && is_writable($candidateDir)) {
                return $candidateDir;
            }
        }

        return $this->getProjectRootDirectory() . 'uploads' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR;
    }

    private function getBaseUrl(): string {
        return defined('BASE_URL') && BASE_URL !== ''
            ? rtrim(BASE_URL, '/') . '/'
            : '/';
    }

    private function getUploadBaseUrl(): string {
        return $this->getBaseUrl() . 'uploads/files/';
    }

    private function getUploadErrorMessage(int $errorCode): string {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder for file upload.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write the uploaded file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
        ];

        return $errors[$errorCode] ?? 'Unknown upload error.';
    }

    private function setUploadFailure(string $message, string $targetDir = '', int $errorCode = 0): void {
        $detail = $message;
        if ($errorCode > 0) {
            $detail .= ' (PHP upload error ' . $errorCode . ': ' . $this->getUploadErrorMessage($errorCode) . ')';
        }

        if ($targetDir !== '') {
            $detail .= ' | target: ' . $targetDir;
        }

        error_log('[FilesController] ' . $detail);
        $_SESSION['message'] = $detail;
        $_SESSION['msg_type'] = 'error';
    }

    private function buildSafeFileName(string $fileName): string {
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        $safeBaseName = preg_replace('/[^A-Za-z0-9._-]/', '_', $baseName) ?: 'file';
        $safeBaseName = trim($safeBaseName, '._-') ?: 'file';

        return $safeBaseName . '_' . time() . '_' . uniqid() . ($extension ? '.' . $extension : '');
    }

    private function resolveStoredFilePath(string $filepath): string {
        $trimmedPath = trim($filepath);
        if ($trimmedPath === '') {
            return '';
        }

        if (preg_match('/^[A-Za-z]:[\\\/]/', $trimmedPath) || strpos($trimmedPath, DIRECTORY_SEPARATOR) === 0) {
            return $trimmedPath;
        }

        $rootRelativePath = $this->getProjectRootDirectory() . ltrim($trimmedPath, '/');
        if (file_exists($rootRelativePath)) {
            return $rootRelativePath;
        }

        $legacyPath = $this->getProjectRootDirectory() . 'uploads' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . basename($trimmedPath);
        if (file_exists($legacyPath)) {
            return $legacyPath;
        }

        return $trimmedPath;
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
        $this->requireAnyRole([0, 1, 2, 4, 5, 6], 'You do not have permission to upload files.');

        // Get categories
        $filesCateg = $this->filesCategModel->getAllCateg();
        $recipientsCateg = $this->filesCategModel->getAllRecipientsCateg();

        require __DIR__ . '/../views/files/create.php'; 
    }

    // Store new files (multiple upload support)
    public function store() {
        $this->requireAnyRole([0, 1, 2, 4, 5, 6], 'You do not have permission to upload files.');

        if (!isset($_FILES['files']) || empty($_FILES['files']['name'][0])) {
            $_SESSION['message'] = "No files selected.";
            $_SESSION['msg_type'] = "error";
            header("Location: index.php?controller=Files&action=files");
            exit;
        }

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

        $targetDir = $this->getUploadDirectory();

        // Validate total file size (40MB max)
        $totalSize = 0;
        foreach ($_FILES['files']['size'] as $size) {
            $totalSize += $size;
        }

        $maxTotalSize = 40 * 1024 * 1024; // 40MB
        if ($totalSize > $maxTotalSize) {
            $_SESSION['message'] = "Total file size exceeds 40MB limit.";
            $_SESSION['msg_type'] = "error";
            header("Location: index.php?controller=Files&action=files");
            exit;
        }

        if (!is_dir($targetDir) || !is_writable($targetDir)) {
            $this->setUploadFailure('Upload directory is not writable.', $targetDir);
            header("Location: index.php?controller=Files&action=files");
            exit;
        }

        $successCount = 0;
        $failureCount = 0;
        $failureDetail = null;
        $failureErrorCode = 0;
        $uploadedFiles = [];

        // Process each file
        for ($i = 0; $i < count($_FILES['files']['name']); $i++) {
            $fileName = $_FILES['files']['name'][$i];
            $tmpName = $_FILES['files']['tmp_name'][$i];
            $fileSize = $_FILES['files']['size'][$i];
            $uploadError = $_FILES['files']['error'][$i];

            if ($uploadError !== UPLOAD_ERR_OK) {
                $failureCount++;
                if ($failureDetail === null) {
                    $failureDetail = 'Upload failed for ' . $fileName . '.';
                    $failureErrorCode = $uploadError;
                }
                continue;
            }

            if (!is_uploaded_file($tmpName)) {
                $failureCount++;
                if ($failureDetail === null) {
                    $failureDetail = 'Temporary upload file was not accepted for ' . $fileName . '.';
                }
                continue;
            }

            // Generate unique filename to prevent conflicts
            $uniqueFileName = $this->buildSafeFileName($fileName);
            $targetFile = $targetDir . $uniqueFileName;

            if (move_uploaded_file($tmpName, $targetFile)) {
                $this->model->create($uniqueFileName, $targetFile, $description, $uploaded_by, $fileCategory, $position, $directionFrom, $directionTo);
                $this->model->newLog($userID, $uniqueFileName, $uploaded_by, $position, $fileCategory, $directionFrom, $directionTo, $uploader, $description);
                
                $successCount++;
                $uploadedFiles[] = $uniqueFileName;
            } else {
                $failureCount++;
                if ($failureDetail === null) {
                    $failureDetail = 'Failed to move uploaded file to storage for ' . $fileName . '.';
                }
            }
        }

        // Prepare session messages
        if ($successCount > 0 && $failureCount === 0) {
            $_SESSION['message'] = $successCount . " file(s) uploaded successfully!";
            $_SESSION['msg_type'] = "success";
        } elseif ($successCount > 0 && $failureCount > 0) {
            $_SESSION['message'] = $successCount . " file(s) uploaded. " . $failureCount . " file(s) failed.";
            $_SESSION['msg_type'] = "warning";
        } else {
            if ($failureDetail !== null) {
                $this->setUploadFailure($failureDetail, $targetDir, $failureErrorCode);
            } else {
                $this->setUploadFailure('Failed to upload files. Please check upload size, file type, and folder permissions.', $targetDir);
            }
        }

        header("Location: index.php?controller=Files&action=files");
        exit;
    }

    // Edit form
    public function edit($id) {
        $this->requireAnyRole([0, 1, 2, 4, 5], 'You do not have permission to edit files.');

        $file = $this->model->getById($id);
        $filesCateg = $this->filesCategModel->getAllCateg();
        $recipientsCateg = $this->filesCategModel->getAllRecipientsCateg();

        require __DIR__ . '/../views/files/edit.php'; 
    }

    // Update file
    public function update($id) {
        $this->requireAnyRole([0, 1, 2, 4, 5], 'You do not have permission to edit files.');

        $file = $this->model->getById($id);
        $filename = $file['filename'];
        $filepath = $file['filepath'];
        $description = $_POST['description'] ?? $file['desc'] ?? '';
        $fileCategory = $_POST['fileCategory'] ?? $file['category'] ?? '';
        
        // Check if routing is enabled via hidden input
        $routingEnabled = $_POST['routingEnabled'] ?? '0';
        if ($routingEnabled === '1') {
            // Routing is enabled - use submitted values or fallback to existing
            $directionFrom = $_POST['fromCategory'] ?? $file['directionFrom'] ?? '';
            $directionTo = $_POST['toCategory'] ?? $file['directionTo'] ?? '';
        } else {
            // Routing is disabled - clear routing values
            $directionFrom = '';
            $directionTo = '';
        }
// changes
        $first = $_SESSION['firstName'] ?? '';
        $last  = $_SESSION['lastName'] ?? '';
        $userID = $_SESSION['id'] ?? 'guest';
        $uploader = trim($first . ' ' . $last) ?: 'System';

        // Check if a new file is uploaded
        if (isset($_FILES['file']) && !empty($_FILES['file']['name'])) {
            $storedPath = $this->resolveStoredFilePath($filepath);
            if ($storedPath !== '' && file_exists($storedPath)) {
                unlink($storedPath);
            }

            $targetDir = $this->getUploadDirectory();
            $newFileName = $this->buildSafeFileName($_FILES['file']['name']);
            $targetFile = $targetDir . $newFileName;

            if (!move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
                $this->setUploadFailure('Failed to upload the replacement file.', $targetDir);
                header("Location: index.php?controller=Files&action=files");
                exit;
            }

            $filename = $newFileName;
            $filepath = $targetFile;
        }

        $this->model->update($id, $filename, $filepath, $description, $fileCategory, $directionFrom, $directionTo);
        $this->model->updateLog($id, $userID, $filename, $filepath, $description, $fileCategory, $directionFrom, $directionTo, $uploader);

        $_SESSION['message'] = $filename . " has been edited successfully!";
        $_SESSION['msg_type'] = "success";
        header("Location: index.php?controller=Files&action=files");
        exit;
    }

    // Delete file
    public function delete($id) {
        $this->requireAnyRole([0, 1, 2, 4, 5], 'You do not have permission to delete files.');

        $first = $_SESSION['firstName'] ?? '';
        $last  = $_SESSION['lastName'] ?? '';
        $userID = $_SESSION['id'] ?? 'guest';
        $uploader = trim($first . ' ' . $last) ?: 'System';

        $file = $this->model->getById($id);
        
        $filename     = $file['filename'] ?? '';
        $filepath     = $file['filepath'] ?? '';
        $description  = $file['desc'] ?? '';
        $fileCategory = $file['category'] ?? '';

        $storedPath = $this->resolveStoredFilePath($filepath);
        if ($storedPath !== '' && file_exists($storedPath)) {
            unlink($storedPath);
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
        $candidateDirs = [
            $this->getUploadDirectory(),
            $this->getProjectRootDirectory() . 'uploads' . DIRECTORY_SEPARATOR,
            __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR,
        ];

        $fullPath = null;
        foreach ($candidateDirs as $candidateDir) {
            $possiblePath = rtrim($candidateDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;
            if (file_exists($possiblePath)) {
                $fullPath = $possiblePath;
                break;
            }
        }

        if ($fullPath === null || !file_exists($fullPath)) {
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
        return $this->hasAnyRole([0, 1, 2, 4, 5, 6]);
    }
}
