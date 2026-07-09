<?php

declare(strict_types=1);

namespace App\Services;

require_once __DIR__ . '/EmailQueueService.php';
require_once __DIR__ . '/CorrespondenceEmailService.php';
require_once __DIR__ . '/../models/correspondence.php';

use CorrespondenceModel;
use Throwable;

class EmailQueueWorker
{
    private EmailQueueService $queueService;
    private CorrespondenceEmailService $emailService;
    private CorrespondenceModel $correspondenceModel;
    private int $batchSize;
    private int $sleepSeconds;

    public function __construct(
        ?EmailQueueService $queueService = null,
        ?CorrespondenceEmailService $emailService = null,
        ?CorrespondenceModel $correspondenceModel = null,
        ?int $batchSize = null,
        ?int $sleepSeconds = null
    ) {
        $this->queueService = $queueService ?? new EmailQueueService();
        $this->emailService = $emailService ?? new CorrespondenceEmailService();
        $this->correspondenceModel = $correspondenceModel ?? new CorrespondenceModel();
        $this->batchSize = $batchSize ?? (int)($_ENV['EMAIL_QUEUE_BATCH_SIZE'] ?? 20);
        $this->sleepSeconds = $sleepSeconds ?? (int)($_ENV['EMAIL_QUEUE_SLEEP_SECONDS'] ?? 3);
    }

    public function runLoop(int $maxIterations = 0): void
    {
        $iteration = 0;
        while (true) {
            $this->processBatch();
            $iteration++;
            if ($maxIterations > 0 && $iteration >= $maxIterations) {
                break;
            }
            if ($this->sleepSeconds > 0) {
                sleep($this->sleepSeconds);
            }
        }
    }

    public function processBatch(): int
    {
        $jobs = $this->queueService->claimBatch($this->batchSize);
        if ($jobs === []) {
            return 0;
        }

        foreach ($jobs as $job) {
            $jobId = (int)($job['id'] ?? 0);
            if ($jobId <= 0) {
                continue;
            }

            try {
                $documentId = (int)($job['correspondence_id'] ?? 0);
                $document = $this->correspondenceModel->getById($documentId);
                if (!$document) {
                    $this->queueService->markFailed($jobId, 'Correspondence document not found for email job.', false);
                    continue;
                }

                $document['recipient_type'] = trim((string)($job['recipient_type'] ?? 'recipient')) !== ''
                    ? trim((string)$job['recipient_type'])
                    : 'recipient';

                $result = $this->emailService->sendJob($job, $document);
                if ($result['success']) {
                    $this->queueService->markSent($jobId);
                } else {
                    $this->queueService->markFailed($jobId, $result['message'] ?? 'Failed to send email', true);
                }
            } catch (Throwable $e) {
                $this->queueService->markFailed($jobId, $e->getMessage(), true);
            }
        }

        return count($jobs);
    }
}
