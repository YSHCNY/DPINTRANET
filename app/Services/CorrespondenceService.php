<?php

declare(strict_types=1);

namespace App\Services;

require_once __DIR__ . '/../models/correspondence.php';
require_once __DIR__ . '/EmailQueueService.php';

use CorrespondenceModel;
use Throwable;

class CorrespondenceService
{
    private CorrespondenceModel $correspondenceModel;
    private EmailQueueService $emailQueueService;

    public function __construct(?CorrespondenceModel $correspondenceModel = null, ?EmailQueueService $emailQueueService = null)
    {
        $this->correspondenceModel = $correspondenceModel ?? new CorrespondenceModel();
        $this->emailQueueService = $emailQueueService ?? new EmailQueueService();
    }

    public function queueCorrespondenceNotifications(
        int $documentId,
        array $document,
        array $circulations,
        array $attachments = [],
        string $notificationType = 'circulated'
    ): array {
        $recipients = [];
        foreach ($circulations as $row) {
            if (!is_array($row)) {
                continue;
            }

            $email = trim((string)($row['email'] ?? $row['recipient_email'] ?? ''));
            if ($email === '') {
                continue;
            }

            $recipients[] = [
                'email' => strtolower($email),
                'recipient_type' => ((int)($row['cc'] ?? 0) === 1) ? 'cc' : 'recipient',
            ];
        }

        $subject = $this->buildSubject($document);
        $body = $this->buildBody($document);

        $result = $this->emailQueueService->queueJobs(
            $documentId,
            $recipients,
            $subject,
            $body,
            $attachments,
            $notificationType
        );

        if (($result['queued'] ?? 0) > 0) {
            $this->emailQueueService->startWorkerProcess();
        }

        return $result;
    }

    private function buildSubject(array $document): string
    {
        $trackingId = trim((string)($document['tracking_id'] ?? ''));
        $title = trim((string)($document['title'] ?? 'Correspondence'));
        return $trackingId !== ''
            ? sprintf('[%s] Correspondence Notification: %s', $trackingId, $title)
            : sprintf('Correspondence Notification: %s', $title);
    }

    private function buildBody(array $document): string
    {
        $trackingId = trim((string)($document['tracking_id'] ?? ''));
        $title = trim((string)($document['title'] ?? 'Correspondence'));
        $description = trim((string)($document['description'] ?? ''));
        $dueDate = trim((string)($document['due_date'] ?? ''));
        $senderEmail = trim((string)($document['sender_email'] ?? ''));
        $createdBy = trim((string)($document['created_by_name'] ?? ''));
        $body = "Dear Recipient,\n\n";
        $body .= "A correspondence document has been circulated to you with the following details:\n\n";
        $body .= "- Tracking ID: {$trackingId}\n";
        $body .= "- Title: {$title}\n";
        if ($description !== '') {
            $body .= "- Description: {$description}\n";
        }
        if ($dueDate !== '') {
            $body .= "- Due Date: {$dueDate}\n";
        }
        if ($senderEmail !== '') {
            $body .= "- Sender: {$senderEmail}\n";
        }
        if ($createdBy !== '') {
            $body .= "- Circulated by: {$createdBy}\n";
        }
        $body .= "\nPlease review the attached materials and take the appropriate action.\n\n";
        $body .= "This is an automated notification from the DPEARP Correspondence System.\n";
        return $body;
    }
}
