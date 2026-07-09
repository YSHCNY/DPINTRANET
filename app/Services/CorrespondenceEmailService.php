<?php

declare(strict_types=1);

namespace App\Services;

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/MailService.php';
require_once __DIR__ . '/EmailTemplateService.php';
require_once __DIR__ . '/../config.php';

use Database;
use PDO;
use PDOException;
use Throwable;

class CorrespondenceEmailService
{
    private MailService $mailService;
    private EmailTemplateService $emailTemplateService;
    private $conn;

    public function __construct(?MailService $mailService = null, ?EmailTemplateService $emailTemplateService = null)
    {
        $this->mailService = $mailService ?? new MailService();
        $this->emailTemplateService = $emailTemplateService ?? new EmailTemplateService();
        $this->conn = Database::connect();
        $this->ensureEmailLogTable();
    }

    public function sendForDocument(array $document, array $circulations, array $attachments = []): array
    {
        $documentId = (int)($document['id'] ?? 0);
        $trackingId = trim((string)($document['tracking_id'] ?? ''));
        $title = trim((string)($document['title'] ?? 'Untitled document'));
        $subject = $trackingId !== ''
            ? sprintf('[%s] Correspondence Notification: %s', $trackingId, $title)
            : sprintf('Correspondence Notification: %s', $title);
        $body = $this->buildEmailBody($document);
        $resolvedAttachments = $this->normalizeAttachments($attachments);

        $results = [
            'sent' => 0,
            'failed' => 0,
            'skipped' => 0,
            'details' => [],
        ];

        if ($documentId <= 0) {
            return $results;
        }

        $seenEmails = [];
        foreach ($this->normalizeCirculations($circulations) as $entry) {
            $email = strtolower(trim((string)($entry['email'] ?? '')));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->logDelivery($documentId, '', (string)($entry['type'] ?? 'recipient'), 'skipped', 'Invalid or empty email address');
                $results['skipped']++;
                $results['details'][] = [
                    'email' => $email,
                    'status' => 'skipped',
                    'message' => 'Invalid or empty email address',
                ];
                continue;
            }

            if (isset($seenEmails[$email])) {
                $this->logDelivery($documentId, $email, (string)($entry['type'] ?? 'recipient'), 'skipped', 'Duplicate recipient address');
                $results['skipped']++;
                $results['details'][] = [
                    'email' => $email,
                    'status' => 'skipped',
                    'message' => 'Duplicate recipient address',
                ];
                continue;
            }

            $seenEmails[$email] = true;
            $success = $this->mailService->send($email, $subject, $body);
            $status = $success ? 'sent' : 'failed';
            $message = $success ? 'Delivered' : 'Failed to send email';

            $this->logDelivery($documentId, $email, (string)($entry['type'] ?? 'recipient'), $status, $message);
            $results[$status === 'sent' ? 'sent' : 'failed']++;
            $results['details'][] = [
                'email' => $email,
                'status' => $status,
                'message' => $message,
            ];
        }

        return $results;
    }

    public function sendJob(array $job, array $document): array
    {
        $email = trim((string)($job['recipient_email'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid recipient email'];
        }

        $subject = trim((string)($job['subject'] ?? 'Correspondence Notification'));
        $template = $this->buildTemplatePayload($document, []);

        $success = $this->mailService->send($email, $subject, $template['html']);
        return [
            'success' => $success,
            'message' => $success ? 'Delivered' : 'Failed to send email',
        ];
    }

    private function buildEmailBody(array $document): string
    {
        $template = $this->buildTemplatePayload($document, []);
        return $template['html'];
    }

    private function buildTemplatePayload(array $document, array $attachments = []): array
    {
        $trackingId = trim((string)($document['tracking_id'] ?? ''));
        $title = trim((string)($document['title'] ?? 'Untitled document'));
        $description = trim((string)($document['description'] ?? $document['summary'] ?? ''));
        $dueDate = trim((string)($document['due_date'] ?? ''));
        $senderEmail = trim((string)($document['sender_email'] ?? ''));
        $senderName = trim((string)($document['sender_name'] ?? ''));
        $circulatorName = trim((string)($document['created_by_name'] ?? ''));
        $recipientType = trim((string)($document['recipient_type'] ?? 'recipient'));
        $portalUrl = trim((string)($document['portal_url'] ?? ''));
        $organizationName = trim((string)($document['organization_name'] ?? 'DPEARP'));
        $brandName = trim((string)($document['brand_name'] ?? 'DPEARP Correspondence Management System'));

        $payload = [
            'tracking_id' => $trackingId,
            'title' => $title,
            'description' => $description,
            'due_date' => $dueDate,
            'sender_name' => $senderName !== '' ? $senderName : $senderEmail,
            'sender_email' => $senderEmail,
            'circulated_by' => $circulatorName,
            'recipient_type' => $recipientType !== '' ? $recipientType : 'recipient',
            'attachments_present' => false,
            'portal_access_note' => $recipientType !== 'cc',
            'portal_url' => $portalUrl,
            'organization_name' => $organizationName,
            'brand_name' => $brandName,
        ];

        return $this->emailTemplateService->renderCorrespondenceNotification($payload);
    }

    private function normalizeCirculations(array $circulations): array
    {
        $normalized = [];

        foreach ($circulations as $row) {
            if (!is_array($row)) {
                continue;
            }

            $email = trim((string)($row['email'] ?? $row['recipient_email'] ?? ''));
            if ($email === '') {
                continue;
            }

            $normalized[] = [
                'email' => $email,
                'type' => ((int)($row['cc'] ?? 0) === 1) ? 'cc' : 'recipient',
            ];
        }

        return $normalized;
    }

    private function normalizeAttachments(array $attachments): array
    {
        $normalized = [];
        foreach ($attachments as $attachment) {
            if (is_string($attachment)) {
                $path = $attachment;
            } elseif (is_array($attachment)) {
                $path = (string)($attachment['file_path'] ?? $attachment['path'] ?? '');
            } else {
                continue;
            }

            if ($path === '' || !file_exists($path)) {
                continue;
            }

            $normalized[] = $path;
        }

        return $normalized;
    }

    private function ensureEmailLogTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS correspondence_email_logs (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        try {
            $this->conn->exec($sql);
        } catch (PDOException $e) {
            error_log('Correspondence email log table setup failed: ' . $e->getMessage());
        }
    }

    private function logDelivery(int $documentId, string $recipientEmail, string $recipientType, string $status, string $message): void
    {
        try {
            $stmt = $this->conn->prepare(
                'INSERT INTO correspondence_email_logs (document_id, recipient_email, recipient_type, status, error_message, attachment_count) VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $documentId,
                $recipientEmail,
                $recipientType !== '' ? $recipientType : 'recipient',
                $status,
                $message,
                0,
            ]);
        } catch (Throwable $e) {
            error_log('Correspondence email log write failed: ' . $e->getMessage());
        }
    }
}
