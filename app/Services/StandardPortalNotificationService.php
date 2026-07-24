<?php

declare(strict_types=1);

namespace App\Services;

require_once __DIR__ . '/NotificationService.php';

use Throwable;

class StandardPortalNotificationService
{
    private NotificationService $notificationService;

    public function __construct(?NotificationService $notificationService = null)
    {
        $this->notificationService = $notificationService ?? new NotificationService();
    }

    public function notifyCirculatedDocument(int $documentId, array $circulations, array $document, int $createdBy): bool
    {
        return $this->notifyStandardUsers(
            $documentId,
            $circulations,
            $document,
            $createdBy,
            'portal_document_circulated',
            'Document circulated',
            'A document has been circulated to you',
            'An item has been circulated to your Standard User Portal'
        );
    }

    public function notifyFinalizedDocument(int $documentId, array $circulations, array $document, int $createdBy): bool
    {
        return $this->notifyStandardUsers(
            $documentId,
            $circulations,
            $document,
            $createdBy,
            'portal_document_finalized',
            'Document finalized',
            'A document has been finalized for you',
            'A document has been finalized and is ready in your Standard User Portal'
        );
    }

    private function notifyStandardUsers(
        int $documentId,
        array $circulations,
        array $document,
        int $createdBy,
        string $eventKey,
        string $title,
        string $messagePrefix,
        string $defaultMessage
    ): bool {
        $recipientIds = $this->getStandardPortalRecipientIds($circulations);
        if (empty($recipientIds)) {
            return true;
        }

        $tracking = trim((string)($document['tracking_id'] ?? ''));
        $message = $tracking !== ''
            ? $messagePrefix . " • {$tracking}"
            : $defaultMessage;

        $url = "index.php?controller=StandardPortal&action=viewDocument&id={$documentId}";

        try {
            return $this->notificationService->notify([
                'user_id' => $recipientIds,
                'module' => 'standard_portal',
                'event_key' => $eventKey,
                'entity_id' => $documentId,
                'title' => $title,
                'message' => $message,
                'url' => $url,
                'priority' => 'normal',
                'icon' => 'document',
                'created_by' => $createdBy,
                'portal' => 'standard',
            ]);
        } catch (Throwable $e) {
            error_log('Standard portal notification failed: ' . $e->getMessage());
            return false;
        }
    }

    private function getStandardPortalRecipientIds(array $circulations): array
    {
        $recipientIds = [];
        foreach ($circulations as $row) {
            $recipientId = (int)($row['recipient_id'] ?? 0);
            if ($recipientId <= 0) {
                continue;
            }

            $recipientIds[] = $recipientId;
        }

        return array_values(array_unique($recipientIds));
    }
}
