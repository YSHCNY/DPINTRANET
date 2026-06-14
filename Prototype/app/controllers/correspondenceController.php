<?php
session_start();
require_once '../app/core/Controller.php';
require_once '../app/models/User.php';
require_once "../app/models/correspondence.php";
require_once '../app/models/UserModel.php';


class CorrespondenceController extends Controller {

    private $model;

    public function __construct() {
        $this->model = new CorrespondenceModel();
    }

    /**
     * Main page - Form + Document Repository
     */


    public function correspondence() {
        if (!isset($_SESSION['user'])) {
            $this->redirect('index.php?controller=Auth&action=login');
        }

        $userModel = new UserModel();
        $nextTrackingId = $this->model->getNextTrackingId();
        $documents = $this->model->getAllDocuments();
        $users     = $userModel->getAllUsers();           // For Recipients & CC

        $content = $this->renderView('correspondence/index', [
            'documents' => $documents,
            'users'     => $users,
            'nextTrackingId' => $nextTrackingId
        ]);

        $this->view('layout/main', ['content' => $content]);
    }

    /**
     * AJAX - Get Document Details for Modal
     */
    public function getDocumentDetails() {
        if (!isset($_SESSION['user'])) {
            echo "<p class='text-red-600'>Unauthorized</p>";
            exit;
        }

        $id = $_GET['id'] ?? 0;
        $doc = $this->model->getDocumentWithDetails($id);

        if (!$doc) {
            echo "<p class='text-red-600 p-8'>Document not found.</p>";
            exit;
        }

        // Simple HTML output for modal
        ?>
        <div class="space-y-8">
            <div>
                <h4 class="font-medium text-gray-500">Tracking ID</h4>
                <p class="text-2xl font-mono text-blue-700"><?= htmlspecialchars($doc['tracking_id']) ?></p>
            </div>

            <div>
                <h4 class="font-medium text-gray-500">Title</h4>
                <p class="text-xl font-semibold"><?= htmlspecialchars($doc['title']) ?></p>
            </div>

            <div class="grid grid-cols-2 gap-8">
                <div>
                    <h4 class="font-medium text-gray-500">Description</h4>
                    <div class="prose mt-2"><?= nl2br(htmlspecialchars($doc['description'])) ?></div>
                </div>
                <div>
                    <h4 class="font-medium text-gray-500">Recipients Status</h4>
                    <p class="mt-2 text-green-600 font-medium"><?= $doc['received_count'] ?? 0 ?> / <?= $doc['total_recipients'] ?? 0 ?> received</p>
                </div>
            </div>

            <div>
                <h4 class="font-medium text-gray-500 mb-3">Actions</h4>
                <button onclick="alert('Download feature coming soon')" 
                        class="px-6 py-3 bg-blue-600 text-white rounded-2xl hover:bg-blue-700">
                    Download Attachments
                </button>
            </div>
        </div>
        <?php
        exit;
    }

    /**
     * Handle form submission (Store new circulation)
     */
public function store() {
    if (!isset($_SESSION['user'])) {
        $this->redirect('index.php?controller=Auth&action=login');
    }

    try {
        // Handle both string and array input from form
        $recipientsRaw = $_POST['recipients'] ?? '';
        $ccRaw         = $_POST['cc'] ?? '';

        // Convert array to comma-separated string
        if (is_array($recipientsRaw)) {
            $recipientsRaw = implode(',', array_filter($recipientsRaw));
        } else {
            $recipientsRaw = trim($recipientsRaw);
        }

        if (is_array($ccRaw)) {
            $ccRaw = implode(',', array_filter($ccRaw));
        } else {
            $ccRaw = trim($ccRaw);
        }

        $data = [
            'tracking_id'     => $this->generateTrackingId(),
            'title'           => trim($_POST['title'] ?? ''),
            'type'            => $_POST['type'] ?? 'Memo',
            'description'     => trim($_POST['description'] ?? ''),
            'sender_email'    => trim($_POST['sender_email'] ?? 'noreply@dalton.com.ph'),
            'priority'        => $_POST['priority'] ?? 'Medium',
            'due_date'        => $_POST['due_date'] ?? null,
            'is_confidential' => isset($_POST['is_confidential']) ? 1 : 0,
            'notes'           => trim($_POST['notes'] ?? ''),
            'recipients'      => $recipientsRaw,
            'cc'              => $ccRaw
        ];

        if (empty($data['title'])) {
            throw new Exception("Document title is required.");
        }

        $documentId = $this->model->createDocument($data);

        if (!$documentId) {
            throw new Exception("Failed to create document.");
        }

        $this->handleFileUploads($documentId);

        // Process into document_circulations
        if (!$this->processRecipients($documentId, $data['recipients'], $data['cc'])) {
            throw new Exception("Document was created, but recipients/CC could not be saved.");
        }


        $_SESSION['message'] = "Document circulated successfully! Tracking ID: " . $data['tracking_id'];
        $_SESSION['msg_type'] = "success";

    } catch (Exception $e) {
        $_SESSION['message'] = $e->getMessage();
        $_SESSION['msg_type'] = "error";
    }

    header("Location: index.php?controller=correspondence&action=correspondence");
    exit;
}

    /**
     * Generate Tracking ID (DPEARP-00001)
     */
    private function generateTrackingId() {
        $lastId = $this->model->getLastTrackingId();
        
        if ($lastId) {
            $number = (int) substr($lastId, 7) + 1;
        } else {
            $number = 1;
        }

        return 'DPEARP-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Handle multiple file uploads
     */
    private function handleFileUploads($documentId) {
        if (empty($_FILES['attachments']['name'][0])) {
            return [];
        }

        $uploaded = [];
        $uploadDir = __DIR__ . "/../uploads/correspondence/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        foreach ($_FILES['attachments']['name'] as $key => $name) {
            if ($_FILES['attachments']['error'][$key] === UPLOAD_ERR_OK) {
                $fileSize = $_FILES['attachments']['size'][$key];

                // 10MB limit
                if ($fileSize > 10 * 1024 * 1024) {
                    continue;
                }

                $tmpName = $_FILES['attachments']['tmp_name'][$key];
                $fileExt = pathinfo($name, PATHINFO_EXTENSION);
                $newFileName = uniqid('doc_') . '.' . strtolower($fileExt);
                $destination = $uploadDir . $newFileName;

                if (move_uploaded_file($tmpName, $destination)) {
                    $this->model->addAttachment($documentId, $name, $destination, $fileSize);
                    $uploaded[] = $name;
                }
            }
        }

        return $uploaded;
    }

    /**
     * Process Recipients and CC
     */
    private function processRecipients($documentId, $recipients, $cc) {
        $saved = true;

        // Both $recipients and $cc are comma-separated strings from hidden fields
        if (!empty($recipients)) {
            $saved = $this->model->addRecipients($documentId, $recipients, false) && $saved;
        }

        if (!empty($cc)) {
            $saved = $this->model->addRecipients($documentId, $cc, true) && $saved;
        }

        return $saved;
    }

       public function download() {
        if (!isset($_SESSION['user'])) {
            $this->redirect('index.php?controller=Auth&action=login');
        }

        $id = $_GET['id'] ?? 0;   // Document ID or Attachment ID

        if (empty($id)) {
            $_SESSION['message'] = "No file specified.";
            $_SESSION['msg_type'] = "error";
            header("Location: index.php?controller=Correspondence&action=correspondence");
            exit;
        }

        // Get attachments for this document
        $attachments = $this->model->getAttachments($id);

        if (empty($attachments)) {
            $_SESSION['message'] = "No attachments found for this document.";
            $_SESSION['msg_type'] = "error";
            header("Location: index.php?controller=Correspondence&action=correspondence");
            exit;
        }

        // For now, download the first attachment (you can extend to choose specific file later)
        $file = $attachments[0];
        $fullPath = $file['file_path'];

        if (!file_exists($fullPath)) {
            $_SESSION['message'] = "File not found on server.";
            $_SESSION['msg_type'] = "error";
            header("Location: index.php?controller=Correspondence&action=correspondence");
            exit;
        }

        $fileName = basename($fullPath);

        // Clean output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Get correct MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fullPath);
        finfo_close($finfo);

        // Force download headers
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($fullPath));

        // Read and output file
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
    // You can add more methods later: view(), edit(), delete(), download(), etc.
}
