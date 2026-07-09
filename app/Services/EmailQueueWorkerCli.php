<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/EmailQueueWorker.php';

use App\Services\EmailQueueWorker;

$worker = new EmailQueueWorker();
$worker->runLoop();
