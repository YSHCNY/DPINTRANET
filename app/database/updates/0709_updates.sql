CREATE TABLE IF NOT EXISTS correspondence_email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_id INT NOT NULL,
    recipient_email VARCHAR(255) NOT NULL,
    recipient_type VARCHAR(20) NOT NULL DEFAULT 'recipient',
    status VARCHAR(20) NOT NULL,
    error_message TEXT NULL,
    subject VARCHAR(255) NULL,
    attachment_count INT NOT NULL DEFAULT 0,
    attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_correspondence_email_logs_document (document_id),
    INDEX idx_correspondence_email_logs_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
