<?php

class NotificationRoutingService {
    public function resolveAdminRedirect(array $notification): string {
        $notifModule = strtolower(trim((string)($notification['module'] ?? '')));
        $url = trim((string)($notification['url'] ?? ''));
        $entityId = (int)($notification['entity_id'] ?? 0);
        $eventKey = strtolower(trim((string)($notification['event_key'] ?? '')));

        $correspondenceEventKeys = [
            'document_finalized',
            'draft_circulated',
            'draft_ready_for_circulation',
            'document_circulated',
            'document_done',
            'document_reopened'
        ];

        if ($entityId > 0 && ($notifModule === 'correspondence' || in_array($eventKey, $correspondenceEventKeys, true))) {
            return 'index.php?controller=correspondence&action=show&id=' . $entityId;
        }

        if ($url !== '' && stripos($url, 'controller=StandardPortal') === false) {
            return $url;
        }

        return 'index.php?controller=correspondence&action=correspondence';
    }

    public function resolvePortalRedirect(array $notification): string {
        $notifModule = strtolower(trim((string)($notification['module'] ?? '')));
        $url = trim((string)($notification['url'] ?? ''));
        $entityId = (int)($notification['entity_id'] ?? 0);
        $eventKey = strtolower(trim((string)($notification['event_key'] ?? '')));

        $correspondenceEventKeys = [
            'document_finalized',
            'draft_circulated',
            'draft_ready_for_circulation',
            'document_circulated',
            'document_done',
            'document_reopened'
        ];

        if ($entityId > 0 && ($notifModule === 'correspondence' || in_array($eventKey, $correspondenceEventKeys, true))) {
            return 'index.php?controller=StandardPortal&action=viewDocument&id=' . $entityId;
        }

        if ($url !== '' && stripos($url, 'controller=StandardPortal') !== false) {
            return $url;
        }

        return '';
    }

    public function getAdminUserId(array $session): int {
        return (int)($session['id'] ?? 0);
    }

    public function getPortalUserId(array $session): int {
        return (int)($session['standard_user_id'] ?? 0);
    }

    public function getAdminLoginRedirectUrl(): string {
        return 'index.php?controller=Auth&action=login';
    }

    public function getPortalLoginRedirectUrl(): string {
        return 'index.php?controller=StandardPortal&action=login';
    }
}
