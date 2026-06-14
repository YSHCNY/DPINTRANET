
<?php
require_once '../app/core/Model.php';

class CorrespondenceModel {

    private $conn;

    public function __construct() {
        $this->conn = Database::connect();
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
        $this->newLog($documentId, $data['title'], $data['tracking_id'], $data['type']);

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
    public function getAllDocuments() {
        $sql = "SELECT d.*, 
                       COUNT(dc.id) as total_recipients,
                       SUM(CASE WHEN dc.status = 'Received' THEN 1 ELSE 0 END) as received_count
                FROM documents d
                LEFT JOIN document_circulations dc ON d.id = dc.document_id
                GROUP BY d.id
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
        $first = $_SESSION['firstName'] ?? '';
        $last  = $_SESSION['lastName'] ?? '';
        $uploader = trim($first . ' ' . $last) ?: 'System';
        $userID = $_SESSION['id'] ?? 'guest';

        $customDesc = "New Document Circulated • Tracking ID: $trackingId • Title: $title • Type: $type • Created by: $uploader";

        $stmt2 = $this->conn->prepare("INSERT INTO systemLogs (`userName`, `logDesc`, `module`, `logDate`) 
                                     VALUES (?, ?, ?, ?)");
        
        $stmt2->execute([
            $userID, 
            $customDesc, 
            "Digital Correspondence", 
            date("Y-m-d H:i:s")
        ]);
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



    /**
     * Get full circulation details (for modal and portal)
     */
	    public function getCirculationDetails($documentId) {
	        $sql = "SELECT dc.*, su.firstName, su.lastName, su.position, su.department, su.email
	                FROM document_circulations dc
	                LEFT JOIN standardUsers su ON dc.recipient_id = su.id
	                WHERE dc.document_id = ?
	                ORDER BY dc.cc ASC, su.firstName ASC";

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

	    public function getPortalStats($recipientId) {
	        $sql = "SELECT
	                    COUNT(*) as total,
	                    SUM(CASE WHEN status = 'Received' THEN 1 ELSE 0 END) as received,
	                    SUM(CASE WHEN status != 'Received' THEN 1 ELSE 0 END) as pending,
	                    SUM(CASE WHEN cc = 1 THEN 1 ELSE 0 END) as cc_total
	                FROM document_circulations
	                WHERE recipient_id = ?";
	        $stmt = $this->conn->prepare($sql);
	        $stmt->execute([$recipientId]);
	        return $stmt->fetch(PDO::FETCH_ASSOC);
	    }

	    public function getPortalDocuments($recipientId) {
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
	                ORDER BY
	                    CASE WHEN dc.status = 'Received' THEN 1 ELSE 0 END ASC,
	                    d.created_at DESC";
	        $stmt = $this->conn->prepare($sql);
	        $stmt->execute([$recipientId]);
	        return $stmt->fetchAll(PDO::FETCH_ASSOC);
	    }

	    public function getPortalDocument($documentId, $recipientId) {
	        $sql = "SELECT d.*,
	                       dc.id as circulation_id,
	                       dc.cc,
	                       dc.status as circulation_status,
	                       dc.received_at,
	                       dc.remarks
	                FROM document_circulations dc
	                INNER JOIN documents d ON d.id = dc.document_id
	                WHERE dc.document_id = ? AND dc.recipient_id = ?
	                LIMIT 1";
	        $stmt = $this->conn->prepare($sql);
	        $stmt->execute([$documentId, $recipientId]);
	        return $stmt->fetch(PDO::FETCH_ASSOC);
	    }

	    public function getPortalAttachment($attachmentId, $recipientId) {
	        $sql = "SELECT da.*
	                FROM document_attachments da
	                INNER JOIN document_circulations dc ON dc.document_id = da.document_id
	                WHERE da.id = ? AND dc.recipient_id = ?
	                LIMIT 1";
	        $stmt = $this->conn->prepare($sql);
	        $stmt->execute([$attachmentId, $recipientId]);
	        return $stmt->fetch(PDO::FETCH_ASSOC);
	    }

	    public function markPortalDocumentReceived($documentId, $recipientId, $pinCode, $remarks = null) {
	        $sql = "UPDATE document_circulations
	                SET status = 'Received',
	                    received_at = NOW(),
	                    pin_code = ?,
	                    remarks = ?
	                WHERE document_id = ? AND recipient_id = ?";
	        $stmt = $this->conn->prepare($sql);
	        return $stmt->execute([$pinCode, $remarks, $documentId, $recipientId]);
	    }
	}
