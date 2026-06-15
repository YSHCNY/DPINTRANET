
<?php
require_once '../app/core/Model.php';

class CorrespondenceModel {

    private $conn;

    public function __construct() {
        $this->conn = Database::connect();
        $this->ensureLifecycleColumns();
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
                 due_date, is_confidential, notes, created_by) 
                VALUES 
                (:tracking_id, :title, :type, :description, :sender_email, :priority, 
                 :due_date, :is_confidential, :notes, :created_by)";

        $stmt = $this->conn->prepare($sql);
        
        $created_by = $_SESSION['id'] ?? 1;

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
            ':created_by'      => $created_by
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

        // Convert comma-separated or array to clean integer IDs
        if (!is_array($recipientInput)) {
            $recipientIds = array_filter(array_map('trim', explode(',', $recipientInput)));
        } else {
            $recipientIds = array_filter($recipientInput);
        }

        if (empty($recipientIds)) {
            return true;
        }

        $values = [];
        $placeholders = [];
        $ccFlag = $isCc ? 1 : 0;

        foreach ($recipientIds as $id) {
            $recipientId = (int)$id;
            if ($recipientId > 0) {
                $placeholders[] = "(?, ?, ?, 'Pending', NULL, NULL, NULL)";
                $values[] = $documentId;
                $values[] = $recipientId;
                $values[] = $ccFlag;
            }
        }

        if (empty($placeholders)) {
            return true;
        }

        $sql = "INSERT INTO document_circulations 
                (document_id, recipient_id, cc, status, received_at, pin_code, remarks) 
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
     * Get all documents for repository table
     */
    public function getAllDocuments($includeDeleted = false) {
        $sql = "SELECT d.*, 
                       COUNT(dc.id) as total_recipients,
                       SUM(CASE WHEN dc.status = 'Received' THEN 1 ELSE 0 END) as received_count
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
        $sql = "SELECT * FROM documents WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
                       COUNT(dc.id) as total_recipients,
                       SUM(CASE WHEN dc.status = 'Received' THEN 1 ELSE 0 END) as received_count
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
	                    TRIM(CONCAT(COALESCE(su.firstName, ''), ' ', COALESCE(su.lastName, ''))) AS recipient_name,
	                    su.position,
	                    su.department,
	                    su.email
	                FROM document_circulations dc
	                INNER JOIN documents d ON d.id = dc.document_id
	                LEFT JOIN standardUsers su ON dc.recipient_id = su.id
	                WHERE dc.document_id = ?
	                ORDER BY dc.cc ASC, COALESCE(su.firstName, '') ASC, COALESCE(su.lastName, '') ASC";

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

    public function getPortalStats($recipientId, $includeDeleted = false) {
	        $sql = "SELECT
	                    COUNT(*) as total,
	                    SUM(CASE WHEN status = 'Received' THEN 1 ELSE 0 END) as received,
	                    SUM(CASE WHEN status != 'Received' THEN 1 ELSE 0 END) as pending,
	                    SUM(CASE WHEN cc = 1 THEN 1 ELSE 0 END) as cc_total
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
	                       dc.status as circulation_status,
	                       dc.received_at,
	                       dc.remarks,
	                       (SELECT COUNT(*) FROM document_attachments da WHERE da.document_id = d.id) as attachment_count
	                FROM document_circulations dc
	                INNER JOIN documents d ON d.id = dc.document_id
	                WHERE dc.recipient_id = ?
	                  " . ($includeDeleted ? "" : "AND COALESCE(d.is_deleted, 0) = 0") . "
	                ORDER BY
	                    CASE WHEN dc.status = 'Received' THEN 1 ELSE 0 END ASC,
	                    d.created_at DESC";
	        $stmt = $this->conn->prepare($sql);
	        $stmt->execute([$recipientId]);
	        return $stmt->fetchAll(PDO::FETCH_ASSOC);
	    }

	    public function getPortalDocument($documentId, $recipientId, $allowDeleted = true) {
	        $sql = "SELECT d.*,
	                       dc.id as circulation_id,
	                       dc.cc,
	                       dc.status as circulation_status,
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
