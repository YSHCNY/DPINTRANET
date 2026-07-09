<?php

declare(strict_types=1);

namespace App\Services;

require_once __DIR__ . '/EmailQueueService.php';

class EmailProgressService
{
    private EmailQueueService $queueService;

    public function __construct(?EmailQueueService $queueService = null)
    {
        $this->queueService = $queueService ?? new EmailQueueService();
    }

    public function getProgressForCorrespondence(int $correspondenceId): array
    {
        return $this->queueService->getProgress($correspondenceId);
    }
}
