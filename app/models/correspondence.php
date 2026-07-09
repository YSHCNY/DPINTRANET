
<?php
require_once __DIR__ . '/../core/Model.php';

class CorrespondenceModel {

    private $conn;

    public function __construct() {
        $this->conn = Database::connect();
        $this->ensureLifecycleColumns();
        $this->ensureRecipientColumns();
        $this->ensureThreadTables();
    }


    // Transaction helpers
    public function beginTransaction() {
        $this->conn->beginTransaction();
    }

    public function commit() {
        if ($this->conn->inTransaction()) $this->conn->commit();
    }

    public function rollBack() {
        if ($this->conn->inTransaction()) $this->conn->rollBack();
    }

    public function finalizeDraft($id, $finalizedBy = null) {
        $sql = "UPDATE documents SET is_draft = 0, updated_at = NOW(), status = 'Inprogress'";
        $params = [];
        if ($finalizedBy !== null) {
            $sql .= ", created_by = ?";
            $params[] = $finalizedBy;
        }
        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute($params);
        $result2 = true;

        try {
            $existing = $this->getCirculationDetails($id);
            if (empty($existing)) {
                $doc = $this->getById($id);
                $draftRecipients = !empty($doc['draft_recipients']) ? array_filter(array_map('trim', explode(',', $doc['draft_recipients']))) : [];
                $draftCc = !empty($doc['draft_cc']) ? array_filter(array_map('trim', explode(',', $doc['draft_cc']))) : [];

                if (!empty($draftRecipients)) {
                    $this->addRecipients($id, $draftRecipients, false);
                }
                if (!empty($draftCc)) {
                    $this->addRecipients($id, $draftCc, true);
                }

                // clear draft recipient fields to avoid duplication
                $upd = $this->conn->prepare("UPDATE documents SET draft_recipients = NULL, draft_cc = NULL WHERE id = ?");
                $upd->execute([$id]);
            }

            $stmt = $this->conn->prepare("UPDATE document_circulations SET updated_at = NOW(), status = 'Inprogress' WHERE document_id = ?");
            $result2 = $stmt->execute([$id]);
        } catch (Throwable $e) {
            error_log('Finalize draft circulation creation or status update failed: ' . $e->getMessage());
        }

        if ($result && $result2) {
            $this->logCorrespondenceAction("Draft finalized and circulated", $id);
        }

        return $result;
    }

    /**
     * Mark an existing document as draft (used when saving progress)
     */
    public function markAsDraft($id) {
        $stmt = $this->conn->prepare("UPDATE documents SET is_draft = 1, updated_at = NOW() WHERE id = ?");
        $result = $stmt->execute([$id]);
        if ($result) {
            $this->logCorrespondenceAction("Document marked as draft", $id);
        }
        return $result;
    }

    public function setDocumentStatus($id, string $status) {
        $stmt = $this->conn->prepare("UPDATE documents SET status = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    private function ensureThreadTables() {
        $sql1 = "CREATE TABLE IF NOT EXISTS document_threads (
            id INT AUTO_INCREMENT PRIMARY KEY,
            document_id INT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_document_threads (document_id),
            FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $sql2 = "CREATE TABLE IF NOT EXISTS document_thread_entries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            document_id INT NOT NULL,
            thread_id INT NOT NULL,
            actor_type VARCHAR(20) NOT NULL,
            actor_user_id INT NULL,
            actor_name VARCHAR(191) NULL,
            role_label VARCHAR(191) NULL,
            entry_kind VARCHAR(40) NOT NULL,
            content TEXT NULL,
            cycle_reference VARCHAR(191) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
            FOREIGN KEY (thread_id) REFERENCES document_threads(id) ON DELETE CASCADE,
            KEY idx_thread_entries_doc_time (document_id, created_at),
            KEY idx_thread_entries_thread_time (thread_id, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $sql3 = "CREATE TABLE IF NOT EXISTS document_thread_entry_files (
            id INT AUTO_INCREMENT PRIMARY KEY,
            thread_entry_id INT NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            file_path VARCHAR(512) NOT NULL,
            file_size BIGINT NOT NULL DEFAULT 0,
            uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (thread_entry_id) REFERENCES document_thread_entries(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        try {
            $this->conn->exec($sql1);
            $this->conn->exec($sql2);
            $this->conn->exec($sql3);
        } catch (PDOException $e) {
            error_log('Thread table creation failed: ' . $e->getMessage());
        }
    }

    private function ensureRecipientColumns() {
        $columns = [
            'recipient_email' => "ALTER TABLE document_circulations ADD COLUMN recipient_email VARCHAR(255) NULL",
            'recipient_name' => "ALTER TABLE document_circulations ADD COLUMN recipient_name VARCHAR(191) NULL",
        ];

        foreach ($columns as $column => $sql) {
            try {
                $stmt = $this->conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'document_circulations' AND COLUMN_NAME = ?");
                $stmt->execute([$column]);
                if ((int)$stmt->fetchColumn() === 0) {
                    $this->conn->exec($sql);
                }
            } catch (PDOException $e) {
                error_log("Correspondence recipient column setup failed for {$column}: " . $e->getMessage());
            }
        }
    }

    private function ensureLifecycleColumns() {

        $columns = [
            'is_deleted' => "ALTER TABLE documents ADD COLUMN is_deleted TINYINT(1) NOT NULL DEFAULT 0",
            'deleted_at' => "ALTER TABLE documents ADD COLUMN deleted_at DATETIME NULL",
            'deleted_by' => "ALTER TABLE documents ADD COLUMN deleted_by VARCHAR(191) NULL",
            'is_edited' => "ALTER TABLE documents ADD COLUMN is_edited TINYINT(1) NOT NULL DEFAULT 0",
            'edited_at' => "ALTER TABLE documents ADD COLUMN edited_at DATETIME NULL",
            'edited_by' => "ALTER TABLE documents ADD COLUMN edited_by VARCHAR(191) NULL",
            'edit_summary' => "ALTER TABLE documents ADD COLUMN edit_summary TEXT NULL",
            'updated_at' => "ALTER TABLE documents ADD COLUMN updated_at DATETIME NULL",
            'is_draft' => "ALTER TABLE documents ADD COLUMN is_draft TINYINT(1) NOT NULL DEFAULT 0",
            'draft_notified' => "ALTER TABLE documents ADD COLUMN draft_notified TINYINT(1) NOT NULL DEFAULT 0",
            'draft_notified_at' => "ALTER TABLE documents ADD COLUMN draft_notified_at DATETIME NULL",
            'draft_notified_by' => "ALTER TABLE documents ADD COLUMN draft_notified_by VARCHAR(191) NULL",
                'draft_recipients' => "ALTER TABLE documents ADD COLUMN draft_recipients TEXT NULL",
                'draft_cc' => "ALTER TABLE documents ADD COLUMN draft_cc TEXT NULL",
        ];

        foreach ($columns as $column => $sql) {
            try {
                $stmt = $this->conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'documents' AND COLUMN_NAME = ?");
                $stmt->execute([$column]);
                if ((int)$stmt->fetchColumn() === 0) {
                    $this->conn->exec($sql);
                }
            } catch (PDOException $e) {
                error_log("Correspondence lifecycle column setup failed for {$column}: " . $e->getMessage());
            }
        }
    }

    /**
     * Create new document
     */
    public function createDocument($data) {
        $sql = "INSERT INTO documents 
            (tracking_id, title, type, description, sender_email, priority, 
             due_date, is_confidential, notes, created_by, is_draft, draft_recipients, draft_cc) 
            VALUES 
            (:tracking_id, :title, :type, :description, :sender_email, :priority, 
             :due_date, :is_confidential, :notes, :created_by, :is_draft, :draft_recipients, :draft_cc)";

        $stmt = $this->conn->prepare($sql);
        
        $created_by = $_SESSION['id'] ?? 1;
        $is_draft = !empty($data['is_draft']) ? 1 : 0;

        $stmt->execute([
            ':tracking_id'     => $data['tracking_id'],
            ':title'           => $data['title'],
            ':type'            => $data['type'],
            ':description'     => $data['description'],
            ':sender_email'    => $data['sender_email'],
            ':priority'        => $data['priority'],
            ':due_date'        => $data['due_date'],
            ':is_confidential' => $data['is_confidential'],
            ':notes'           => $data['notes'],
            ':created_by'      => $created_by,
            ':is_draft'        => $is_draft,
            ':draft_recipients' => $data['recipients'] ?? null,
            ':draft_cc' => $data['cc'] ?? null,
        ]);

        $documentId = $this->conn->lastInsertId();

        // Create log
        $this->logCorrespondenceAction(
            "Document created • Tracking ID: {$data['tracking_id']} • Title: {$data['title']} • Type: {$data['type']} • Created by: " . $this->getActorName(),
            $documentId
        );

        return $documentId;
    }

    /**
     * Add attachment
     */
    public function addAttachment($documentId, $originalName, $filePath, $fileSize) {
        $sql = "INSERT INTO document_attachments 
                (document_id, file_name, file_path, file_size) 
                VALUES (:doc_id, :fname, :fpath, :fsize)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':doc_id' => $documentId,
            ':fname'  => $originalName,
            ':fpath'  => $filePath,
            ':fsize'  => $fileSize
        ]);
        return true;
    }

    /**
     * Add Recipients or CC to document_circulations
     * Optimized for standardUsers table + future Portal
     */

  public function addRecipients($documentId, $recipientInput, $isCc = false) {
        if (empty($recipientInput)) {
            return true;
        }

        // Convert comma-separated or array to clean recipient values
        if (!is_array($recipientInput)) {
            $recipientItems = array_filter(array_map('trim', preg_split('/\s*,\s*/', trim((string)$recipientInput))));
        } else {
            $recipientItems = array_filter(array_map(function ($value) {
                return trim((string)$value);
            }, $recipientInput));
        }

        if (empty($recipientItems)) {
            return true;
        }

        $values = [];
        $placeholders = [];
        $ccFlag = $isCc ? 1 : 0;

        foreach ($recipientItems as $item) {
            $item = trim((string)$item);
            if ($item === '') {
                continue;
            }

            if (preg_match('/^\d+$/', $item)) {
                $recipientId = (int)$item;
                if ($recipientId > 0) {
                    $placeholders[] = "(?, ?, ?, 'Pending', NULL, NULL, NULL, NULL, NULL)";
                    $values[] = $documentId;
                    $values[] = $recipientId;
                    $values[] = $ccFlag;
                }
                continue;
            }

            if (preg_match('/^email:(.+)$/i', $item, $matches)) {
                $email = trim($matches[1]);
            } else {
                $email = $item;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $displayName = $email;
            $placeholders[] = "(?, ?, ?, 'Pending', NULL, NULL, NULL, ?, ?)";
            $values[] = $documentId;
            $values[] = null;
            $values[] = $ccFlag;
            $values[] = strtolower($email);
            $values[] = $displayName;
        }

        if (empty($placeholders)) {
            return true;
        }

        $sql = "INSERT INTO document_circulations 
                (document_id, recipient_id, cc, status, received_at, pin_code, remarks, recipient_email, recipient_name) 
                VALUES " . implode(', ', $placeholders);

        $stmt = $this->conn->prepare($sql);

        try {
            $result = $stmt->execute($values);
            return $result;
        } catch (PDOException $e) {
            error_log("Bulk Recipients Insert Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get last tracking ID
     */
    public function getLastTrackingId() {
        $sql = "SELECT tracking_id FROM documents ORDER BY id DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['tracking_id'] : null;
    }

    /**
     * Return true when the submitted tracking ID already exists.
     */
    public function trackingIdExists(string $trackingId): bool {
        $sql = "SELECT 1 FROM documents WHERE tracking_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$trackingId]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Get all documents for repository table
     */
    public function getAllDocuments($includeDeleted = false) {
        $sql = "SELECT d.*, 
                       SUM(CASE WHEN dc.recipient_id IS NOT NULL AND dc.recipient_id > 0 THEN 1 ELSE 0 END) as total_recipients,
                       SUM(CASE WHEN dc.recipient_id IS NOT NULL AND dc.recipient_id > 0 AND dc.status = 'Received' THEN 1 ELSE 0 END) as received_count
                FROM documents d
                LEFT JOIN document_circulations dc ON d.id = dc.document_id";

        if (!$includeDeleted) {
            $sql .= " WHERE COALESCE(d.is_deleted, 0) = 0";
        }

        $sql .= " GROUP BY d.id
                  ORDER BY d.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get single document by ID
     */
    public function getById($id) {
        $sql = "SELECT d.*, u.firstName AS created_by_firstName, u.lastName AS created_by_lastName
                FROM documents d
                LEFT JOIN UserTbl u ON u.id = d.created_by
                WHERE d.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $row['created_by_name'] = trim(($row['created_by_firstName'] ?? '') . ' ' . ($row['created_by_lastName'] ?? '')) ?: ($row['created_by'] ?? 'System');
        }

        return $row;
    }

    public function getDashboardMetrics(): array {
        $sql = "SELECT
                    COUNT(*) as total_documents,
                    SUM(CASE WHEN COALESCE(d.is_draft, 0) = 1 THEN 1 ELSE 0 END) as draft_documents,
                    SUM(CASE WHEN LOWER(d.status) = 'inprogress' THEN 1 ELSE 0 END) as inprogress_documents,
                    SUM(CASE WHEN dc.recipient_id IS NOT NULL AND dc.recipient_id > 0 THEN 1 ELSE 0 END) as total_recipients,
                    SUM(CASE WHEN dc.recipient_id IS NOT NULL AND dc.recipient_id > 0 AND dc.status = 'Received' THEN 1 ELSE 0 END) as received_recipients,
                    SUM(CASE WHEN dc.recipient_id IS NOT NULL AND dc.recipient_id > 0 AND dc.status != 'Received' THEN 1 ELSE 0 END) as pending_recipients
                FROM documents d
                LEFT JOIN document_circulations dc ON dc.document_id = d.id
                WHERE COALESCE(d.is_deleted, 0) = 0";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_documents' => (int)($result['total_documents'] ?? 0),
            'draft_documents' => (int)($result['draft_documents'] ?? 0),
            'inprogress_documents' => (int)($result['inprogress_documents'] ?? 0),
            'total_recipients' => (int)($result['total_recipients'] ?? 0),
            'received_recipients' => (int)($result['received_recipients'] ?? 0),
            'pending_recipients' => (int)($result['pending_recipients'] ?? 0),
        ];
    }

    public function getRecentDocuments(int $limit = 6): array {
        $sql = "SELECT d.id, d.tracking_id, d.title, d.type, d.priority, d.status, d.is_draft,
                       d.created_at,
                       SUM(CASE WHEN dc.recipient_id IS NOT NULL AND dc.recipient_id > 0 AND dc.status = 'Received' THEN 1 ELSE 0 END) as received_count,
                       SUM(CASE WHEN dc.recipient_id IS NOT NULL AND dc.recipient_id > 0 AND dc.status != 'Received' THEN 1 ELSE 0 END) as pending_count,
                       SUM(CASE WHEN dc.recipient_id IS NOT NULL AND dc.recipient_id > 0 THEN 1 ELSE 0 END) as recipient_count
                FROM documents d
                LEFT JOIN document_circulations dc ON dc.document_id = d.id
                WHERE COALESCE(d.is_deleted, 0) = 0
                GROUP BY d.id
                ORDER BY d.created_at DESC
                LIMIT :limit";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPendingActions(int $limit = 5): array {
        $sql = "SELECT dc.id as circulation_id,
                       d.id as document_id,
                       d.tracking_id,
                       d.title,
                       dc.recipient_id,
                       CASE WHEN u.id IS NOT NULL THEN CONCAT(u.firstName, ' ', u.lastName) ELSE CONCAT('Recipient #', dc.recipient_id) END as recipient_name,
                       dc.cc,
                       dc.status,
                       dc.received_at,
                       d.created_at
                FROM document_circulations dc
                INNER JOIN documents d ON d.id = dc.document_id
                LEFT JOIN UserTbl u ON u.id = dc.recipient_id
                WHERE COALESCE(d.is_deleted, 0) = 0
                  AND dc.status != 'Received'
                ORDER BY d.created_at DESC
                LIMIT :limit";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * New Log - Following your exact style
     */
    public function newLog($documentId, $title, $trackingId, $type) {
        $this->logCorrespondenceAction(
            "New Document Circulated • Tracking ID: $trackingId • Title: $title • Type: $type • Created by: " . $this->getActorName(),
            $documentId
        );
    }

    private function getActorId() {
        return $_SESSION['id'] ?? 'guest';
    }

    private function getActorName() {
        $first = $_SESSION['firstName'] ?? '';
        $last  = $_SESSION['lastName'] ?? '';
        return trim($first . ' ' . $last) ?: 'System';
    }

    public function logCorrespondenceAction($description, $documentId = null) {
        $stmt = $this->conn->prepare("INSERT INTO systemLogs (`userName`, `logDesc`, `module`, `logDate`) VALUES (?, ?, ?, ?)");
        return $stmt->execute([
            $this->getActorId(),
            $description,
            $documentId !== null ? "Digital Correspondence #{$documentId}" : "Digital Correspondence",
            date("Y-m-d H:i:s")
        ]);
    }

    private function getDocumentEditableRecord($id) {
        $stmt = $this->conn->prepare("SELECT * FROM documents WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function canManageDocument(array $document) {
        if (empty($document)) {
            return false;
        }

        if (!empty($document['is_deleted'])) {
            return false;
        }

        if ((int)($document['created_by'] ?? 0) !== (int)($_SESSION['id'] ?? 0)) {
            return false;
        }

        if (empty($document['created_at'])) {
            return false;
        }

        $deadline = strtotime($document['created_at'] . ' +7 days');
        return $deadline !== false && time() <= $deadline;
    }

    public function getDocumentManageWindow($document) {
        if (empty($document['created_at'])) {
            return null;
        }

        $deadline = strtotime($document['created_at'] . ' +7 days');
        return $deadline !== false ? date('M d, Y g:i A', $deadline) : null;
    }

    public function buildEditSummary(array $before, array $after) {
        $labels = [
            'title' => 'Title',
            'type' => 'Type',
            'priority' => 'Priority',
            'due_date' => 'Due Date',
            'sender_email' => 'Sender Email',
            'description' => 'Description',
            'notes' => 'Notes',
            'is_confidential' => 'Confidential Flag',
        ];

        $changes = [];
        foreach ($labels as $field => $label) {
            $old = (string)($before[$field] ?? '');
            $new = (string)($after[$field] ?? '');

            if ($field === 'is_confidential') {
                $old = !empty($before[$field]) ? 'Yes' : 'No';
                $new = !empty($after[$field]) ? 'Yes' : 'No';
            }

            if ($old !== $new) {
                $changes[] = "{$label}: {$old} -> {$new}";
            }
        }

        return implode(' | ', $changes);
    }

    public function updateDocument($id, array $data) {
        $before = $this->getDocumentEditableRecord($id);
        if (!$before) {
            return false;
        }

        $after = array_merge($before, $data);
        $summary = $this->buildEditSummary($before, $after);

        $sql = "UPDATE documents
                SET title = :title,
                    type = :type,
                    description = :description,
                    sender_email = :sender_email,
                    priority = :priority,
                    due_date = :due_date,
                    is_confidential = :is_confidential,
                    notes = :notes,
                    is_edited = 1,
                    edited_at = NOW(),
                    edited_by = :edited_by,
                    edit_summary = :edit_summary,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([
            ':title' => $data['title'],
            ':type' => $data['type'],
            ':description' => $data['description'],
            ':sender_email' => $data['sender_email'],
            ':priority' => $data['priority'],
            ':due_date' => $data['due_date'],
            ':is_confidential' => $data['is_confidential'],
            ':notes' => $data['notes'],
            ':edited_by' => $this->getActorName(),
            ':edit_summary' => $summary ?: 'Metadata updated',
            ':id' => $id,
        ]);

        if ($result) {
            $this->logCorrespondenceAction(
                "Document edited • Tracking ID: " . ($before['tracking_id'] ?? 'Unknown') . " • Changes: " . ($summary ?: 'Metadata updated'),
                $id
            );
        }

        return $result;
    }

    /**
     * Update draft recipients/cc stored on the documents record
     */
    public function updateDraftRecipients($id, $recipientsCsv = null, $ccCsv = null) {
        $sql = "UPDATE documents SET draft_recipients = :r, draft_cc = :c, updated_at = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':r' => $recipientsCsv,
            ':c' => $ccCsv,
            ':id' => $id
        ]);
    }

    public function replaceDocumentCirculations($documentId, $recipientsCsv = null, $ccCsv = null) {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("DELETE FROM document_circulations WHERE document_id = ?");
            $stmt->execute([$documentId]);

            if (!empty($recipientsCsv)) {
                $this->addRecipients($documentId, $recipientsCsv, false);
            }
            if (!empty($ccCsv)) {
                $this->addRecipients($documentId, $ccCsv, true);
            }

            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log('replaceDocumentCirculations failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark draft as notified to admin-level users and create logs
     */
    public function notifyAdminsOfDraft($documentId) {
        // Find admin users (userLevel 0 or 1) and create notifications
        try {
            $stmt = $this->conn->prepare("SELECT id, firstName, lastName, userLevel FROM UserTbl WHERE userLevel IN (0,1,4,5)");
            $stmt->execute();
            $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Update document to mark draft notified
            $update = $this->conn->prepare("UPDATE documents SET draft_notified = 1, draft_notified_at = NOW(), draft_notified_by = ? WHERE id = ?");
            $update->execute([$this->getActorName(), $documentId]);

            // Build URL to document (opens correspondence list with doc_id)
            $doc = $this->getById($documentId);
            $tracking = $doc['tracking_id'] ?? '';
            $url = "index.php?controller=correspondence&action=correspondence&doc_id={$documentId}";

            // Create Notification entries
            require_once 'Notification.php';
            $notif = new NotificationModel();
            $adminIds = array_map(function($a){ return (int)$a['id']; }, $admins);
            $message = "Draft ready for circulation • {$tracking} • By: " . $this->getActorName();
            if (!empty($adminIds)) {
                $notif->createForMany($adminIds, $message, $url);
            }

            // Also log for audit
            foreach ($admins as $admin) {
                $desc = "Draft created • Tracking ID: {$tracking} • Created by: " . $this->getActorName() . " • Notified: " . trim(($admin['firstName'] ?? '') . ' ' . ($admin['lastName'] ?? ''));
                $this->logCorrespondenceAction($desc, $documentId);
            }

            return true;
        } catch (Throwable $e) {
            error_log('Draft notify failed for document #' . $documentId . ': ' . $e->getMessage());
            return false;
        }
    }

    public function softDeleteDocument($id) {
        $document = $this->getDocumentEditableRecord($id);
        if (!$document) {
            return false;
        }

        $stmt = $this->conn->prepare("UPDATE documents
                                      SET is_deleted = 1,
                                          deleted_at = NOW(),
                                          deleted_by = ?,
                                          updated_at = NOW()
                                      WHERE id = ?");
        $result = $stmt->execute([$this->getActorName(), $id]);

        if ($result) {
            $this->logCorrespondenceAction(
                "Document deleted (soft delete) • Tracking ID: " . ($document['tracking_id'] ?? 'Unknown') . " • Deleted by: " . $this->getActorName(),
                $id
            );
        }

        return $result;
    }

    public function hardDeleteDocument($id) {
        $document = $this->getDocumentEditableRecord($id);
        if (!$document) {
            return false;
        }

        $attachments = $this->getAttachments($id);
        $attachmentPaths = array_filter(array_map(function ($file) {
            return $file['file_path'] ?? null;
        }, $attachments));

        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("DELETE FROM document_attachments WHERE document_id = ?");
            $stmt->execute([$id]);

            $stmt = $this->conn->prepare("DELETE FROM document_circulations WHERE document_id = ?");
            $stmt->execute([$id]);

            $stmt = $this->conn->prepare("DELETE FROM documents WHERE id = ?");
            $result = $stmt->execute([$id]);

            if (!$result) {
                $this->conn->rollBack();
                return false;
            }

            $this->conn->commit();
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log('Hard delete failed for correspondence #' . $id . ': ' . $e->getMessage());
            return false;
        }

        foreach ($attachmentPaths as $path) {
            if (!empty($path) && file_exists($path)) {
                @unlink($path);
            }
        }

        $this->logCorrespondenceAction(
            "Document hard deleted • Tracking ID: " . ($document['tracking_id'] ?? 'Unknown') . " • Permanently removed by: " . $this->getActorName(),
            $id
        );

        return true;
    }


    /**
     * Get Next Tracking ID (Dynamic)
     */
    public function getNextTrackingId() {
    $sql = "SELECT tracking_id FROM documents ORDER BY id DESC LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $lastNum = (int) substr($row['tracking_id'], 7);
        $nextNum = $lastNum + 1;
    } else {
        $nextNum = 1;
    }

    return 'DPEARP-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
}

/**
     * Get full document details for modal (AJAX)
     */
    public function getDocumentWithDetails($id) {
        $sql = "SELECT d.*, 
                       SUM(CASE WHEN dc.recipient_id IS NOT NULL AND dc.recipient_id > 0 THEN 1 ELSE 0 END) as total_recipients,
                       SUM(CASE WHEN dc.recipient_id IS NOT NULL AND dc.recipient_id > 0 AND dc.status = 'Received' THEN 1 ELSE 0 END) as received_count
                FROM documents d
                LEFT JOIN document_circulations dc ON d.id = dc.document_id
                WHERE d.id = ?
                GROUP BY d.id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getDocumentChangeHistory($documentId) {
        $doc = $this->getById($documentId);
        if (!$doc) {
            return [];
        }

        $moduleKey = 'Digital Correspondence #' . (int)$documentId;
        $trackingId = $doc['tracking_id'] ?? '';

        $sql = "SELECT sl.logDesc, sl.logDate, sl.module, sl.userName,
                       u.firstName, u.lastName, u.position
                FROM systemLogs sl
                LEFT JOIN UserTbl u ON sl.userName = u.id
                WHERE sl.module = ?
                ORDER BY sl.logDate DESC
                LIMIT 12";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$moduleKey]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($history) || $trackingId === '') {
            return $history;
        }

        $fallbackSql = "SELECT sl.logDesc, sl.logDate, sl.module, sl.userName,
                               u.firstName, u.lastName, u.position
                        FROM systemLogs sl
                        LEFT JOIN UserTbl u ON sl.userName = u.id
                        WHERE sl.logDesc LIKE ?
                          AND (sl.module = 'Digital Correspondence' OR sl.module LIKE 'Digital Correspondence%')
                        ORDER BY sl.logDate DESC
                        LIMIT 12";

        $fallbackStmt = $this->conn->prepare($fallbackSql);
        $fallbackStmt->execute(['%' . $trackingId . '%']);
        return $fallbackStmt->fetchAll(PDO::FETCH_ASSOC);
    }



    /**
     * Get full circulation details (for modal and portal)
     */
    public function getCirculationDetails($documentId) {
        $sql = "SELECT
                    dc.*,
                    d.tracking_id,
                    d.title,
                    d.created_at AS circulated_at,
                    d.due_date,
                    COALESCE(dc.recipient_name, TRIM(CONCAT(COALESCE(su.firstName, ''), ' ', COALESCE(su.lastName, '')))) AS recipient_name,
                    su.position,
                    su.department,
                    COALESCE(dc.recipient_email, su.email) AS email
                FROM document_circulations dc
                INNER JOIN documents d ON d.id = dc.document_id
                LEFT JOIN standardUsers su ON dc.recipient_id = su.id
                WHERE dc.document_id = ?
                ORDER BY dc.cc ASC, COALESCE(dc.recipient_name, TRIM(CONCAT(COALESCE(su.firstName, ''), ' ', COALESCE(su.lastName, '')))) ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$documentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update recipient status (for Standard Portal later)
     */
    public function updateRecipientStatus($documentId, $recipientId, $status = 'Received', $pinCode = null, $remarks = null) {
        $sql = "UPDATE document_circulations 
                SET status = :status, 
                    received_at = NOW(), 
                    pin_code = :pin_code, 
                    remarks = :remarks 
                WHERE document_id = :doc_id AND recipient_id = :recipient_id";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':status'      => $status,
            ':pin_code'    => $pinCode,
            ':remarks'     => $remarks,
            ':doc_id'      => $documentId,
            ':recipient_id'=> $recipientId
        ]);
    }

    public function updateAllCirculationsStatus(int $documentId, string $status): array
{
    try {
        $this->conn->beginTransaction();

        // First check if the document has circulation rows at all
        $check = $this->conn->prepare("
            SELECT COUNT(*) 
            FROM document_circulations
            WHERE document_id = ?
        ");
        $check->execute([$documentId]);
        $circulationCount = (int) $check->fetchColumn();

        if ($circulationCount === 0) {
            $this->conn->rollBack();
            return [
                'success' => false,
                'message' => 'No circulation records found for this document.'
            ];
        }

        // Update all circulation statuses, but preserve recipients already marked as Received.
        $stmt = $this->conn->prepare("
            UPDATE document_circulations
            SET status = ?
            WHERE document_id = ? AND LOWER(status) != 'received'
        ");
        $stmt->execute([$status, $documentId]);

        // Optional document touch/update
        $actorName = $this->getActorName();
        $doc = $this->conn->prepare("
            UPDATE documents
            SET updated_at = NOW(),
                status = ?,
                closed_by = CASE WHEN LOWER(?) = 'done' THEN ? ELSE NULL END,
                closed_at = CASE WHEN LOWER(?) = 'done' THEN NOW() ELSE NULL END
            WHERE id = ?
        ");
        $doc->execute([$status, $status, $actorName, $status, $documentId]);

        // Optional log
        $this->logCorrespondenceAction("All circulations set to {$status}", $documentId);

        $this->conn->commit();

        return [
            'success' => true,
            'message' => "All circulations updated to {$status}."
        ];
    } catch (Throwable $e) {
        if ($this->conn->inTransaction()) {
            $this->conn->rollBack();
        }

        error_log('updateAllCirculationsStatus failed: ' . $e->getMessage());

        return [
            'success' => false,
            'message' => 'Database error while updating circulations.'
        ];
    }
}



    /**
     * Bulk update circulation statuses for a document.
     * Does not overwrite entries already marked as 'Received'.
     * $status is expected to be values like 'open' or 'close'.
     */
    public function setCirculationsStatus($documentId, $status) {
        $allowed = ['open', 'close', 'done', 'suspended', 'Pending', 'Received'];
        $status = (string)$status;
        // restrict to simple string; caller is responsible for sane values
        $sql = "UPDATE document_circulations SET status = :status WHERE document_id = :doc_id AND status != 'Received'";
        $stmt = $this->conn->prepare($sql);
        try {
            return $stmt->execute([':status' => $status, ':doc_id' => $documentId]);
        } catch (PDOException $e) {
            error_log('setCirculationsStatus failed: ' . $e->getMessage());
            return false;
        }
    }



		public function getAttachments($documentId) {
		        $sql = "SELECT id, file_name, file_path, file_size FROM document_attachments WHERE document_id = ?";
		        $stmt = $this->conn->prepare($sql);
		        $stmt->execute([$documentId]);
		        return $stmt->fetchAll(PDO::FETCH_ASSOC);
		    }

	    public function getAttachmentById($attachmentId) {
	        $sql = "SELECT id, document_id, file_name, file_path, file_size FROM document_attachments WHERE id = ?";
	        $stmt = $this->conn->prepare($sql);
	        $stmt->execute([$attachmentId]);
	        return $stmt->fetch(PDO::FETCH_ASSOC);
	    }
    public function deleteAttachmentById(int $attachmentId) {
        $attachment = $this->getAttachmentById($attachmentId);
        if (!$attachment) {
            return false;
        }

        $filePath = $attachment["file_path"] ?? "";
        $stmt = $this->conn->prepare("DELETE FROM document_attachments WHERE id = ?");
        $result = $stmt->execute([$attachmentId]);

        if ($result && !empty($filePath) && file_exists($filePath)) {
            @unlink($filePath);
        }

        return $result;
    }



    private function ensureThreadForDocument(int $documentId): int {
        $stmt = $this->conn->prepare("SELECT id FROM document_threads WHERE document_id = ? LIMIT 1");
        $stmt->execute([$documentId]);
        $id = $stmt->fetchColumn();
        if ($id) {
            return (int)$id;
        }

        // Concurrency-safe: avoid failing the whole request if another process inserts the thread.
        // document_threads has UNIQUE(document_id), so we can insert-ignore.
        $ins = $this->conn->prepare("INSERT IGNORE INTO document_threads (document_id) VALUES (?)");
        $ins->execute([$documentId]);

        // Re-read to get the real thread id in both cases (insert or ignored).
        $stmt2 = $this->conn->prepare("SELECT id FROM document_threads WHERE document_id = ? LIMIT 1");
        $stmt2->execute([$documentId]);
        $id2 = $stmt2->fetchColumn();
        if ($id2) {
            return (int)$id2;
        }

        // Fallback: if something unexpected happened, return 0 so callers treat it as failure.
        return 0;
    }


    public function getThreadEntries(int $documentId): array {
        $threadIdStmt = $this->conn->prepare("SELECT id FROM document_threads WHERE document_id = ? LIMIT 1");
        $threadIdStmt->execute([$documentId]);
        $threadId = $threadIdStmt->fetchColumn();
        if (!$threadId) return [];

        $sql = "SELECT te.*, d.tracking_id
                FROM document_thread_entries te
                INNER JOIN documents d ON d.id = te.document_id
                WHERE te.document_id = ?
                ORDER BY te.created_at ASC, te.id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$documentId]);
        $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($entries)) return [];

        $entryIds = array_map(fn($e) => (int)$e['id'], $entries);
        $in = implode(',', array_fill(0, count($entryIds), '?'));
        $filesSql = "SELECT * FROM document_thread_entry_files WHERE thread_entry_id IN ({$in}) ORDER BY id ASC";
        $filesStmt = $this->conn->prepare($filesSql);
        $filesStmt->execute($entryIds);
        $files = $filesStmt->fetchAll(PDO::FETCH_ASSOC);

        $fileMap = [];
        foreach ($files as $f) {
            $fileMap[(int)$f['thread_entry_id']][] = $f;
        }

        foreach ($entries as &$e) {
            $eid = (int)$e['id'];
            $e['files'] = $fileMap[$eid] ?? [];
        }
        unset($e);

        return $entries;
    }

    public function addThreadEntry(
        int $documentId,
        string $actorType,
        ?int $actorUserId,
        string $actorName,
        string $roleLabel,
        string $entryKind,
        ?string $content,
        ?string $cycleReference,
        array $uploadedFiles = []
    ): int {
        $this->conn->beginTransaction();
        try {
            $threadId = $this->ensureThreadForDocument($documentId);

            $stmt = $this->conn->prepare("INSERT INTO document_thread_entries
                (document_id, thread_id, actor_type, actor_user_id, actor_name, role_label, entry_kind, content, cycle_reference)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)" );

            $stmt->execute([
                $documentId,
                $threadId,
                $actorType,
                $actorUserId,
                $actorName,
                $roleLabel,
                $entryKind,
                $content,
                $cycleReference
            ]);

            $entryId = (int)$this->conn->lastInsertId();

            // store files
            if (!empty($uploadedFiles)) {
                $fileStmt = $this->conn->prepare("INSERT INTO document_thread_entry_files
                    (thread_entry_id, file_name, file_path, file_size)
                    VALUES (?, ?, ?, ?)");

                foreach ($uploadedFiles as $f) {
                    $fileStmt->execute([
                        $entryId,
                        $f['file_name'],
                        $f['file_path'],
                        (int)($f['file_size'] ?? 0)
                    ]);
                }
            }

            $this->conn->commit();
            return $entryId;
        } catch (Throwable $e) {
            $this->conn->rollBack();
            error_log('addThreadEntry failed: ' . $e->getMessage());
            return 0;
        }
    }

		public function getPortalStats($recipientId, $includeDeleted = false) {
                $sql = "SELECT
                                        COUNT(*) as total,
                                        SUM(CASE WHEN dc.status = 'Received' THEN 1 ELSE 0 END) as received,
                                        SUM(CASE WHEN dc.status != 'Received' THEN 1 ELSE 0 END) as pending,
                                        SUM(CASE WHEN dc.cc = 1 THEN 1 ELSE 0 END) as cc_total
                                FROM document_circulations dc
                                INNER JOIN documents d ON d.id = dc.document_id
                                WHERE dc.recipient_id = ?
                                    " . ($includeDeleted ? "" : "AND COALESCE(d.is_deleted, 0) = 0");
	        $stmt = $this->conn->prepare($sql);
	        $stmt->execute([$recipientId]);
	        return $stmt->fetch(PDO::FETCH_ASSOC);
	    }

	    public function getPortalDocuments($recipientId, $includeDeleted = false) {
                        $sql = "SELECT d.*,
                                                     dc.id as circulation_id,
                                                     dc.cc,
                                                     CASE
                                                             WHEN LOWER(dc.status) = 'open' THEN 'done'
                                                             WHEN LOWER(dc.status) = 'close' THEN 'suspended'
                                                             ELSE LOWER(dc.status)
                                                     END as circulation_status,
                                                     dc.received_at,
                                                     dc.remarks,
                                                     (SELECT COUNT(*) FROM document_attachments da WHERE da.document_id = d.id) as attachment_count
                                        FROM document_circulations dc
                                        INNER JOIN documents d ON d.id = dc.document_id
                                        WHERE dc.recipient_id = ?
                                            " . ($includeDeleted ? "" : "AND COALESCE(d.is_deleted, 0) = 0") . "
                                        ORDER BY
                                                CASE WHEN LOWER(dc.status) = 'received' THEN 1 ELSE 0 END ASC,
                                                d.created_at DESC";
	        $stmt = $this->conn->prepare($sql);
	        $stmt->execute([$recipientId]);
	        return $stmt->fetchAll(PDO::FETCH_ASSOC);
	    }

	    public function getPortalDocument($documentId, $recipientId, $allowDeleted = true) {
                $sql = "SELECT d.*,
                                             dc.id as circulation_id,
                                             dc.cc,
                                             CASE
                                                     WHEN LOWER(dc.status) = 'open' THEN 'done'
                                                     WHEN LOWER(dc.status) = 'close' THEN 'suspended'
                                                     ELSE LOWER(dc.status)
                                             END as circulation_status,
                                             dc.received_at,
                                             dc.remarks,
                                             dc.pin_code
                                FROM document_circulations dc
                                INNER JOIN documents d ON d.id = dc.document_id
                                WHERE dc.document_id = ? AND dc.recipient_id = ?
                                    " . ($allowDeleted ? "" : "AND COALESCE(d.is_deleted, 0) = 0") . "
                                LIMIT 1";
	        $stmt = $this->conn->prepare($sql);
	        $stmt->execute([$documentId, $recipientId]);
	        return $stmt->fetch(PDO::FETCH_ASSOC);
	    }

	    public function getPortalAttachment($attachmentId, $recipientId) {
	        $sql = "SELECT da.*
	                FROM document_attachments da
	                INNER JOIN document_circulations dc ON dc.document_id = da.document_id
	                INNER JOIN documents d ON d.id = dc.document_id
	                WHERE da.id = ? AND dc.recipient_id = ? AND COALESCE(d.is_deleted, 0) = 0
	                LIMIT 1";
	        $stmt = $this->conn->prepare($sql);
	        $stmt->execute([$attachmentId, $recipientId]);
	        return $stmt->fetch(PDO::FETCH_ASSOC);
	    }

	    public function markPortalDocumentReceived($documentId, $recipientId, $pinCode, $remarks = null) {
	        $document = $this->getPortalDocument($documentId, $recipientId, false);
	        if (!$document || !empty($document['is_deleted'])) {
	            return false;
	        }

	        $sql = "UPDATE document_circulations
	                SET status = 'Received',
	                    received_at = NOW(),
	                    pin_code = ?,
	                    remarks = ?
	                WHERE document_id = ? AND recipient_id = ?";
	        $stmt = $this->conn->prepare($sql);
	        $result = $stmt->execute([$pinCode, $remarks, $documentId, $recipientId]);

	        if ($result) {
	            $this->logCorrespondenceAction(
	                "Document received • Tracking ID: " . ($document['tracking_id'] ?? 'Unknown') . " • Received by recipient ID: {$recipientId}",
	                $documentId
	            );
	        }

	        return $result;
	    }
	}
