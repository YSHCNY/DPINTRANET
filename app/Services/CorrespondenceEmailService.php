<?php

declare(strict_types=1);

namespace App\Services;

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/MailService.php';
require_once __DIR__ . '/../config.php';

use Database;
use PDO;
use PDOException;
use Throwable;

class CorrespondenceEmailService
{
    private ?MailService $mailService;
    private ?PDO $conn = null;

    public function __construct(?MailService $mailService = null, bool $withPersistence = true)
    {
        $this->mailService = $mailService;
        if ($withPersistence) {
            $this->conn = Database::connect();
            $this->ensureEmailLogTable();
        }
    }

    private function getMailService(): MailService
    {
        if ($this->mailService === null) {
            $this->mailService = new MailService();
        }

        return $this->mailService;
    }

    public function sendForDocument(array $document, array $circulations, array $attachments = []): array
    {
        $documentId = (int)($document['id'] ?? 0);
        $emailPayload = $this->renderEmail($document, ['recipient_type' => 'recipient']);
        $subject = $emailPayload['subject'];
        $body = $emailPayload['html'];
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
            $success = $this->getMailService()->send($email, $subject, $body);
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

        $recipientType = trim((string)($document['recipient_type'] ?? 'recipient'));
        $emailPayload = $this->renderEmail($document, ['recipient_type' => $recipientType]);

        $success = $this->getMailService()->send($email, $emailPayload['subject'], $emailPayload['html']);
        return [
            'success' => $success,
            'message' => $success ? 'Delivered' : 'Failed to send email',
        ];
    }

    public function renderEmail(array $document, array $options = []): array
    {
        $recipientType = trim((string)($options['recipient_type'] ?? $document['recipient_type'] ?? 'recipient'));
        $portalUrl = trim((string)($options['portal_url'] ?? $document['portal_url'] ?? ''));
        $organizationName = trim((string)($options['organization_name'] ?? $document['organization_name'] ?? 'DPEARP'));
        $brandName = trim((string)($options['brand_name'] ?? $document['brand_name'] ?? 'DPEARP Correspondence Management System'));

        $payload = [
            'tracking_id' => trim((string)($document['tracking_id'] ?? '')),
            'title' => trim((string)($document['title'] ?? 'Untitled document')),
            'description' => trim((string)($document['description'] ?? $document['summary'] ?? '')),
            'due_date' => trim((string)($document['due_date'] ?? '')),
            'sender_name' => trim((string)($document['sender_name'] ?? '')),
            'sender_email' => trim((string)($document['sender_email'] ?? '')),
            'circulated_by' => trim((string)($document['created_by_name'] ?? '')),
            'recipient_type' => $recipientType !== '' ? $recipientType : 'recipient',
            'portal_access_note' => $recipientType !== 'cc',
            'portal_url' => $portalUrl,
            'organization_name' => $organizationName,
            'brand_name' => $brandName,
        ];

        return $this->renderTemplatePayload($payload);
    }

    private function renderTemplatePayload(array $payload): array
    {
        $trackingId = $this->escape($payload['tracking_id'] ?? '');
        $title = $this->escape($payload['title'] ?? 'Untitled correspondence');
        $description = $this->escape($this->truncateText($payload['description'] ?? '', 220));
        $dueDate = $this->escape($payload['due_date'] ?? '');
        $senderName = $this->escape($payload['sender_name'] ?? $payload['sender_email'] ?? '');
        $senderEmail = $this->escape($payload['sender_email'] ?? '');
        $circulatedBy = $this->escape($payload['circulated_by'] ?? '');
        $recipientType = $this->escape($payload['recipient_type'] ?? 'recipient');
        $portalAccessNote = !empty($payload['portal_access_note']);
        $portalUrl = isset($payload['portal_url']) && is_string($payload['portal_url']) && $payload['portal_url'] !== ''
            ? $this->escape($payload['portal_url'])
            : '';
        $organizationName = $this->escape($payload['organization_name'] ?? 'DPEARP');
        $brandName = $this->escape($payload['brand_name'] ?? 'DPEARP Correspondence Management System');
        $year = (string)date('Y');
        $subject = $trackingId !== ''
            ? sprintf('[%s] Correspondence Notification: %s', $trackingId, $title)
            : sprintf('Correspondence Notification: %s', $title);
        $heading = $recipientType === 'cc'
            ? 'Correspondence Notification'
            : 'New Correspondence Assigned';
        $intro = $recipientType === 'cc'
            ? 'A correspondence update has been shared with you for review.'
            : 'You have been assigned a correspondence item that requires your attention.';

        $html = $this->renderHtmlTemplate(
            $subject,
            $brandName,
            $heading,
            $intro,
            $trackingId,
            $title,
            $description,
            $dueDate,
            $senderName,
            $senderEmail,
            $circulatedBy,
            $portalAccessNote,
            $portalUrl,
            $organizationName,
            $year
        );

        $text = $this->renderTextTemplate(
            $subject,
            $brandName,
            $heading,
            $intro,
            $trackingId,
            $title,
            $description,
            $dueDate,
            $senderName,
            $senderEmail,
            $circulatedBy,
            $portalAccessNote,
            $portalUrl,
            $organizationName,
            $year
        );

        return [
            'subject' => $subject,
            'html' => $html,
            'text' => $text,
        ];
    }

    private function renderHtmlTemplate(
        string $subject,
        string $brandName,
        string $heading,
        string $intro,
        string $trackingId,
        string $title,
        string $description,
        string $dueDate,
        string $senderName,
        string $senderEmail,
        string $circulatedBy,
        bool $portalAccessNote,
        string $portalUrl,
        string $organizationName,
        string $year
    ): string {
        $detailRows = [];
        $detailRows[] = $this->renderInfoRow('Tracking ID', $trackingId !== '' ? $trackingId : 'Not available');
        $detailRows[] = $this->renderInfoRow('Correspondence Title', $title !== '' ? $title : 'Untitled correspondence');
        $descriptionValue = $description !== '' ? $description : 'No description provided.';
        $descriptionHtml = $this->formatMultilineHtml($descriptionValue);
        $detailRows[] = $this->renderInfoRow('Description', $descriptionHtml);
        $detailRows[] = $this->renderInfoRow('Due Date', $dueDate !== '' ? $dueDate : 'Not specified');
        $senderDisplay = $senderName !== '' ? $senderName : ($senderEmail !== '' ? $senderEmail : 'Not available');
        $detailRows[] = $this->renderInfoRow('Sender', $senderDisplay);
        $detailRows[] = $this->renderInfoRow('Circulated By', $circulatedBy !== '' ? $circulatedBy : 'Not available');

        return <<<HTML
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>{$subject}</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:Segoe UI, Arial, sans-serif; color:#111827;">
  <div style="display:none; max-height:0; overflow:hidden; opacity:0; mso-hide:all;">{$subject}</div>
  <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#f3f4f6; margin:0; padding:24px 12px;">
    <tr>
      <td align="center">
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width:640px; background-color:#ffffff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden;">
          <tr>
            <td style="padding:24px 24px 18px 24px; border-bottom:1px solid #e5e7eb; background-color:#f8fafc;">
              <div style="font-size:12px; letter-spacing:1.5px; text-transform:uppercase; color:#64748b; margin-bottom:8px;">{$brandName}</div>
              <div style="font-size:22px; font-weight:700; color:#0f172a;">{$heading}</div>
            </td>
          </tr>
          <tr>
            <td style="padding:24px;">
              <p style="margin:0 0 12px 0; font-size:15px; line-height:24px; color:#334155;">{$intro}</p>
              <div style="background-color:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:16px 18px; margin:0 0 16px 0;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                  <tr>
                    <td colspan="2" style="padding-bottom:10px; font-size:13px; font-weight:700; color:#475569; text-transform:uppercase; letter-spacing:0.04em;">Correspondence Details</td>
                  </tr>
                  {$this->implodeRows($detailRows)}
                </table>
              </div>
              <div style="background:linear-gradient(90deg, #eff6ff 0%, #f8fbff 100%); border:1px solid #bfdbfe; border-left:4px solid #2563eb; border-radius:10px; padding:16px 18px; margin:0 0 16px 0;">
                <p style="margin:0 0 6px 0; font-size:15px; font-weight:700; color:#0f172a;">Access Your Correspondence to see file</p>
                <p style="margin:0 0 10px 0; font-size:14px; line-height:22px; color:#334155;">If you have an account on the organization's Intranet or Correspondence Management System, you can log in to view the correspondence, track its status, and access all associated documents and updates.</p>
                <div style="display:inline-block; padding:8px 12px; border:1px solid #93c5fd; border-radius:999px; background-color:#ffffff; font-size:13px; font-weight:600; color:#1d4ed8;">Log In to the Intranet</div>
              </div>
              <p style="margin:0; font-size:13px; line-height:21px; color:#64748b;">Please review the correspondence at your earliest convenience.</p>
            </td>
          </tr>
          <tr>
            <td style="padding:0 24px 24px 24px;">
              <div style="border-top:1px solid #e5e7eb; padding-top:16px; font-size:12px; line-height:18px; color:#64748b;">
                <p style="margin:0 0 4px 0;">This is an automated notification from {$organizationName}.</p>
                <p style="margin:0 0 4px 0;">Please do not reply to this email.</p>
                <p style="margin:0;">© {$year} {$organizationName}. All rights reserved.</p>
              </div>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }

    private function renderTextTemplate(
        string $subject,
        string $brandName,
        string $heading,
        string $intro,
        string $trackingId,
        string $title,
        string $description,
        string $dueDate,
        string $senderName,
        string $senderEmail,
        string $circulatedBy,
        bool $portalAccessNote,
        string $portalUrl,
        string $organizationName,
        string $year
    ): string {
        $lines = [];
        $lines[] = $brandName;
        $lines[] = $heading;
        $lines[] = '';
        $lines[] = $intro;
        $lines[] = '';
        $lines[] = 'Correspondence Details';
        $lines[] = 'Tracking ID: ' . ($trackingId !== '' ? $trackingId : 'Not available');
        $lines[] = 'Title: ' . ($title !== '' ? $title : 'Untitled correspondence');
        $lines[] = 'Description: ' . ($description !== '' ? $this->normalizePlainText($description) : 'No description provided.');
        $lines[] = 'Due Date: ' . ($dueDate !== '' ? $dueDate : 'Not specified');
        $lines[] = 'Sender: ' . ($senderName !== '' ? $senderName : ($senderEmail !== '' ? $senderEmail : 'Not available'));
        $lines[] = 'Circulated By: ' . ($circulatedBy !== '' ? $circulatedBy : 'Not available');
        $lines[] = '';
        $lines[] = 'Access Your Correspondence';
        $lines[] = 'If you have an account on the organization\'s Intranet or Correspondence Management System, you can log in to view the correspondence, track its status, and access all associated documents and updates.';
        if ($portalAccessNote) {
            $lines[] = 'If you are an internal recipient, please sign in to the intranet to review the record.';
        }
        if ($portalUrl !== '') {
            $lines[] = 'Portal: ' . $portalUrl;
        }
        $lines[] = '';
        $lines[] = 'This is an automated notification from ' . $organizationName . '.';
        $lines[] = 'Please do not reply to this email.';
        $lines[] = '© ' . $year . ' ' . $organizationName . '. All rights reserved.';

        return implode(PHP_EOL, $lines);
    }

    private function renderInfoRow(string $label, string $value): string
    {
        return '<tr><td style="padding:6px 0; font-size:14px; color:#64748b; width:35%; vertical-align:top;">' . $label . '</td><td style="padding:6px 0; font-size:14px; color:#0f172a; font-weight:600; vertical-align:top;">' . $value . '</td></tr>';
    }

    private function formatMultilineHtml(string $value): string
    {
        return nl2br($this->escape($value), false);
    }

    private function normalizePlainText(string $value): string
    {
        return preg_replace('/\r\n|\r|\n/', PHP_EOL, trim($value)) ?? trim($value);
    }

    private function implodeRows(array $rows): string
    {
        return implode('', $rows);
    }

    private function truncateText(string $value, int $limit): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (mb_strlen($value) <= $limit) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, $limit - 3)) . '...';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
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
