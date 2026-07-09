<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Services/EmailTemplateService.php';

use App\Services\EmailTemplateService;

$templateService = new EmailTemplateService();

$result = $templateService->renderCorrespondenceNotification([
    'tracking_id' => 'DPEARP-00123',
    'title' => 'Quarterly Compliance Memo',
    'description' => 'Please review the attached memo before the end of week.',
    'due_date' => '2026-07-15',
    'sender_name' => 'A. Santos',
    'sender_email' => 'a.santos@company.test',
    'circulated_by' => 'R. Cruz',
    'recipient_type' => 'recipient',
    'attachments_present' => true,
    'portal_access_note' => true,
]);

if (!str_contains($result['html'], 'DPEARP Correspondence Management System')) {
    throw new RuntimeException('HTML template must include the branding header.');
}

if (!str_contains($result['html'], 'New Correspondence Assigned')) {
    throw new RuntimeException('HTML template must include a clear notification heading.');
}

if (!str_contains($result['html'], 'Please do not reply to this email.')) {
    throw new RuntimeException('HTML template must include a professional footer disclaimer.');
}

if (!str_contains($result['text'], 'DPEARP Correspondence Management System')) {
    throw new RuntimeException('Plain-text template must include the branding header.');
}

if (!str_contains($result['text'], 'Access Your Correspondence')) {
    throw new RuntimeException('Plain-text template must include the access your correspondence section.');
}

echo "Email template regression check passed\n";
