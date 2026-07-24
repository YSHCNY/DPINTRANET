<?php

declare(strict_types=1);

namespace App\Services;

require_once __DIR__ . '/../models/Notification.php';

use NotificationModel;
use InvalidArgumentException;

class NotificationService
{
    private NotificationModel $notificationModel;

    public function __construct(?NotificationModel $notificationModel = null)
    {
        $this->notificationModel = $notificationModel ?? new NotificationModel();
    }

    public function notify(array $data): bool
    {
        $required = [
            'user_id',
            'module',
            'event_key',
            'entity_id',
            'title',
            'message',
            'priority',
            'icon',
            'created_by',
        ];

        foreach ($required as $field) {
            if (!array_key_exists($field, $data)) {
                throw new InvalidArgumentException('Notification data missing required field: ' . $field);
            }
        }

        $userIds = $data['user_id'];
        if (is_array($userIds)) {
            $validUserIds = [];
            foreach ($userIds as $rawUserId) {
                $userId = (int)$rawUserId;
                if ($userId > 0) {
                    $validUserIds[] = $userId;
                }
            }
            $validUserIds = array_values(array_unique($validUserIds));

            if ($validUserIds === []) {
                return true;
            }

            $result = true;
            foreach ($validUserIds as $userId) {
                $result = $this->notifySingle($userId, $data) && $result;
            }
            return $result;
        }

        $userId = (int)$userIds;
        if ($userId <= 0) {
            return true;
        }

        return $this->notifySingle($userId, $data);
    }

    private function notifySingle(int $userId, array $data): bool
    {
        $module = trim((string)($data['module'] ?? ''));
        $eventKey = trim((string)($data['event_key'] ?? ''));
        if ($module === '' || $eventKey === '') {
            return false;
        }

        $entityId = null;
        if (array_key_exists('entity_id', $data) && $data['entity_id'] !== null) {
            $entityId = (int)$data['entity_id'];
            if ($entityId <= 0) {
                $entityId = null;
            }
        }

        $portal = strtolower(trim((string)($data['portal'] ?? '')));
        if (!in_array($portal, ['admin', 'standard'], true)) {
            $portal = 'admin';
        }

        return $this->notificationModel->create(
            $userId,
            (string)$data['message'],
            $data['url'] ?? null,
            $module,
            $eventKey,
            $entityId,
            (string)$data['title'],
            (string)$data['priority'],
            $data['icon'] ?? null,
            $data['created_by'] !== null ? (int)$data['created_by'] : null,
            $portal
        );
    }
}
