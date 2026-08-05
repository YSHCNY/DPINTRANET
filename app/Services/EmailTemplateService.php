<?php

declare(strict_types=1);

namespace App\Services;

require_once __DIR__ . '/CorrespondenceEmailService.php';

class EmailTemplateService
{
    public function renderCorrespondenceNotification(array $data): array
    {
        $emailService = new CorrespondenceEmailService(null, false);
        $options = [
            'recipient_type' => trim((string)($data['recipient_type'] ?? 'recipient')) !== '' ? trim((string)$data['recipient_type']) : 'recipient',
            'portal_url' => trim((string)($data['portal_url'] ?? '')),
            'organization_name' => trim((string)($data['organization_name'] ?? 'DPEARP')),
            'brand_name' => trim((string)($data['brand_name'] ?? 'DPEARP Correspondence Management System')),
        ];

        return $emailService->renderEmail($data, $options);
    }
}
