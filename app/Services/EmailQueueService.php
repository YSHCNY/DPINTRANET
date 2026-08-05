<?php

declare(strict_types=1);

namespace App\Services;

require_once __DIR__ . '/../core/Database.php';

use Database;
use PDO;
use PDOException;
use Throwable;

class EmailQueueService
{
    private $conn;
    private int $maxRetries;

    public function __construct(
        $conn = null,
        ?int $maxRetries = null
    ) {
        $this->conn = $conn ?? Database::connect();
        $this->setConnectionAttributes();
        $this->maxRetries = $maxRetries ?? (int)($_ENV['EMAIL_QUEUE_MAX_RETRIES'] ?? 3);
        $this->ensureTable();
    }

    private function setConnectionAttributes(): void
    {
        if (!($this->conn instanceof PDO)) {
            return;
        }

        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
        $this->conn->setAttribute(PDO::ATTR_TIMEOUT, (int)($_ENV['DB_CONNECT_TIMEOUT'] ?? 5));
    }

    private function reconnect(): void
    {
        $this->conn = Database::connect();
        $this->setConnectionAttributes();
    }

    public function ensureTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS email_queue (
            id INT AUTO_INCREMENT PRIMARY KEY,
            correspondence_id INT NOT NULL,
            recipient_type VARCHAR(20) NOT NULL DEFAULT 'recipient',
            recipient_email VARCHAR(255) NOT NULL,
            notification_type VARCHAR(30) NOT NULL DEFAULT 'circulated',
            subject VARCHAR(255) NULL,
            body LONGTEXT NULL,
            attachments JSON NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'Pending',
            attempts INT NOT NULL DEFAULT 0,
            error_message TEXT NULL,
            queued_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            started_at DATETIME NULL,
            completed_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email_queue_status (status),
            INDEX idx_email_queue_correspondence (correspondence_id),
            INDEX idx_email_queue_queued_at (queued_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        try {
            $this->conn->exec($sql);
        } catch (PDOException $e) {
            error_log('Email queue table setup failed: ' . $e->getMessage());
        }
    }

    public function queueJobs(
        int $correspondenceId,
        array $recipients,
        string $subject,
        string $body,
        array $attachments = [],
        string $notificationType = 'circulated'
    ): array {
        $normalizedAttachments = $this->normalizeAttachments($attachments);
        $seen = [];
        $queued = 0;

        foreach ($recipients as $recipient) {
            if (!is_array($recipient)) {
                continue;
            }

            $email = strtolower(trim((string)($recipient['email'] ?? '')));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            if (isset($seen[$email])) {
                continue;
            }
            $seen[$email] = true;

            $recipientType = ((string)($recipient['recipient_type'] ?? 'recipient')) !== ''
                ? (string)$recipient['recipient_type']
                : 'recipient';

            $stmt = $this->conn->prepare(
                'INSERT INTO email_queue (
                    correspondence_id,
                    recipient_type,
                    recipient_email,
                    notification_type,
                    subject,
                    body,
                    attachments,
                    status,
                    attempts,
                    error_message,
                    queued_at,
                    created_at,
                    updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW())'
            );

            $stmt->execute([
                $correspondenceId,
                $recipientType,
                $email,
                $notificationType,
                $subject,
                $body,
                $normalizedAttachments === [] ? null : json_encode($normalizedAttachments),
                'Pending',
                0,
                null,
            ]);

            $queued++;
        }

        return [
            'total' => $queued,
            'queued' => $queued,
            'skipped' => 0,
        ];
    }

    public function startWorkerProcess(): void
    {
        $autoStart = filter_var($_ENV['EMAIL_QUEUE_WORKER_AUTO_START'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
        if (!$autoStart) {
            return;
        }

        $phpBinary = '/usr/local/bin/php';

        if (!file_exists($phpBinary)) {
            $phpBinary = 'php';
        }
        $scriptPath = dirname(__DIR__) . '/Services/EmailQueueWorkerCli.php';
        $logPath = dirname(__DIR__, 2) . '/storage/email-worker.log';
        if (!is_file($scriptPath)) {
            return;
        }

        if (!is_dir(dirname($logPath))) {
            @mkdir(dirname($logPath), 0755, true);
        }

        if (PHP_OS_FAMILY === 'Windows') {
            @pclose(@popen('start /B "" ' . escapeshellarg($phpBinary) . ' ' . escapeshellarg($scriptPath) . ' > ' . escapeshellarg($logPath) . ' 2>&1', 'r'));
            return;
        }

        $command = escapeshellcmd($phpBinary) . ' ' .
                escapeshellarg($scriptPath) .
                ' >> ' .
                escapeshellarg($logPath) .
                ' 2>&1 &';

        file_put_contents(
            dirname(__DIR__, 2) . '/storage/worker-command.log',
            date('c') . PHP_EOL .
            "PHP_BINARY = {$phpBinary}" . PHP_EOL .
            "SCRIPT     = {$scriptPath}" . PHP_EOL .
            "COMMAND    = {$command}" . PHP_EOL . PHP_EOL,
            FILE_APPEND
        );

        exec($command, $output, $result);

        file_put_contents(
            dirname(__DIR__, 2) . '/storage/worker-command.log',
            "RESULT={$result}" . PHP_EOL .
            print_r($output, true) . PHP_EOL,
            FILE_APPEND
        );
    }

   
   public function claimBatch(int $limit = 20): array
    {
        error_log("=== claimBatch() entered ===");
        $limit = max(1, min(100, $limit));
        $jobs = [];
        $attempts = 0;

        while ($attempts < 2) {
            $attempts++;
            $conn = $this->getConnection();

            try {
                $conn->beginTransaction();

                $stmt = $conn->prepare(
                    'SELECT * FROM email_queue
                     WHERE status IN ("Pending", "Failed")
                     AND attempts < :max_retries
                     ORDER BY queued_at ASC, id ASC
                     LIMIT :limit'
                );

                $stmt->bindValue(':max_retries', $this->maxRetries, PDO::PARAM_INT);
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();

                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rows as $row) {
                    $updateStmt = $conn->prepare(
                        'UPDATE email_queue
                         SET status = "Sending",
                             attempts = attempts + 1,
                             started_at = NOW(),
                             updated_at = NOW()
                         WHERE id = ?'
                    );

                    $updateStmt->execute([(int)$row['id']]);

                    $row['attempts'] = (int)$row['attempts'] + 1;
                    $jobs[] = $row;
                }

                $conn->commit();
                return $jobs;

            } catch (Throwable $e) {
                if ($conn->inTransaction()) {
                    $conn->rollBack();
                }

                $message = $e->getMessage();
                error_log('Email queue batch claim failed: ' . $message);

                if (strpos($message, 'MySQL server has gone away') !== false
                    || strpos($message, 'gone away') !== false
                    || strpos($message, 'server has gone away') !== false) {
                    $this->reconnect();
                    continue;
                }

                break;
            }
        }

        return $jobs;
    }

    private function getConnection(): PDO
    {
        try {
            if (!($this->conn instanceof PDO)) {
                throw new PDOException('Invalid PDO connection');
            }

            $this->conn->query('SELECT 1');
        } catch (Throwable $e) {
            error_log('[EmailQueue] Reconnecting PDO: ' . $e->getMessage());
            $this->reconnect();
        }

        return $this->conn;
    }


    public function markSent(int $jobId): void
    {
        $stmt = $this->conn->prepare(
            'UPDATE email_queue SET status = "Sent", completed_at = NOW(), updated_at = NOW(), error_message = NULL WHERE id = ?'
        );
        $stmt->execute([$jobId]);
    }

    public function markFailed(int $jobId, string $message = '', bool $retryable = false): void
    {
        $stmt = $this->conn->prepare(
            'SELECT attempts FROM email_queue WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$jobId]);
        $attempts = (int)$stmt->fetchColumn();

        if ($retryable && $attempts < $this->maxRetries) {
            $stmt = $this->conn->prepare(
                'UPDATE email_queue SET status = "Pending", completed_at = NULL, updated_at = NOW(), error_message = ? WHERE id = ?'
            );
            $stmt->execute([$message, $jobId]);
            return;
        }

        $stmt = $this->conn->prepare(
            'UPDATE email_queue SET status = "Failed", completed_at = NOW(), updated_at = NOW(), error_message = ? WHERE id = ?'
        );
        $stmt->execute([$message, $jobId]);
    }

    public function getProgress(int $correspondenceId): array
    {
        $stmt = $this->conn->prepare(
            'SELECT COUNT(*) AS total_count,
                    SUM(CASE WHEN status = "Sent" THEN 1 ELSE 0 END) AS sent_count,
                    SUM(CASE WHEN status = "Failed" THEN 1 ELSE 0 END) AS failed_count,
                    SUM(CASE WHEN status IN ("Pending", "Sending") THEN 1 ELSE 0 END) AS queued_count
             FROM email_queue WHERE correspondence_id = ?'
        );
        $stmt->execute([$correspondenceId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $total = max(0, (int)($row['total_count'] ?? 0));
        $sent = max(0, (int)($row['sent_count'] ?? 0));
        $failed = max(0, (int)($row['failed_count'] ?? 0));
        $queued = max(0, (int)($row['queued_count'] ?? 0));
        $remaining = max(0, $total - $sent - $failed);
        $progress = $total > 0 ? (int)round(($sent / $total) * 100) : 0;

        return [
            'total' => $total,
            'queued' => $queued,
            'sent' => $sent,
            'failed' => $failed,
            'remaining' => $remaining,
            'progress' => $progress,
            'completed' => $total > 0 && ($sent + $failed) >= $total,
        ];
    }

    private function normalizeAttachments(array $attachments): array
    {
        $normalized = [];
        foreach ($attachments as $attachment) {
            if (is_string($attachment) && $attachment !== '' && file_exists($attachment)) {
                $normalized[] = $attachment;
            } elseif (is_array($attachment)) {
                $path = (string)($attachment['file_path'] ?? $attachment['path'] ?? '');
                if ($path !== '' && file_exists($path)) {
                    $normalized[] = $path;
                }
            }
        }

        return array_values(array_unique($normalized));
    }
}
