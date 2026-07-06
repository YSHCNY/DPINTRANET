<?php require __DIR__ . '/partials.php'; ?>
<?php portalHeader('Document Details'); ?>
<?php portalFlash(); ?>

<?php
    $isDeleted = !empty($document['is_deleted']);
    $isEdited  = !empty($document['is_edited']);
    $history   = $history ?? [];
    $thread    = $thread ?? [];

    $trackingId       = htmlspecialchars($document['tracking_id'] ?? '—');
    $title            = htmlspecialchars($document['title'] ?? 'Untitled Document');
    $type             = htmlspecialchars($document['type'] ?? '—');
    $senderEmail      = htmlspecialchars($document['sender_email'] ?? '—');
    $priority         = htmlspecialchars($document['priority'] ?? '—');
    $circulationStatus = htmlspecialchars($document['circulation_status'] ?? '—');
    $status = htmlspecialchars($document['status'] ?? '—');


    $createdAt = !empty($document['created_at'])
        ? date('M d, Y g:i A', strtotime($document['created_at']))
        : 'None';

    $dueDate = !empty($document['due_date'])
        ? date('M d, Y', strtotime($document['due_date']))
        : 'None';

    $receivedAt = !empty($document['received_at'])
        ? date('M d, Y g:i A', strtotime($document['received_at']))
        : 'Pending';

    $receiverName = htmlspecialchars($_SESSION['standard_user_name'] ?? '—');
    $receiverPosition = htmlspecialchars($_SESSION['standard_user_position'] ?? '—');
    $receiverDepartment = htmlspecialchars($_SESSION['standard_user_department'] ?? '—');

    $hasReceived = strtolower(trim($document['circulation_status'] ?? '')) === 'received';
    $receiveBadgeLabel = $hasReceived ? 'Received' : 'Pending';
    $receiveBadgeClass = $hasReceived
        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
        : 'border-amber-200 bg-amber-50 text-amber-700';
?>

<?php if ($isDeleted): ?>
    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800 shadow-sm">
        <div class="flex items-start gap-3">
            <div class="mt-0.5 h-2.5 w-2.5 rounded-full bg-rose-500"></div>
            <div>
                <p class="font-semibold">Document unavailable for action</p>
                <p class="mt-1 text-rose-700">
                    This document was deleted by the sender. It remains visible for audit purposes, but you can no longer download or receive it.
                </p>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_320px]">

    <!-- MAIN COLUMN -->
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

        <!-- HEADER -->
        <div class="border-b border-slate-200 bg-slate-50 px-4 py-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0 space-y-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                            Tracking ID
                        </span>
                        <span class="text-sm font-semibold text-slate-700"><?= $trackingId ?></span>
                    </div>

                    <h1 class="text-xl font-semibold tracking-tight text-slate-900">
                        <?= $title ?>
                    </h1>

                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-slate-500">
                        <span><?= $type ?></span>
                        <span class="text-slate-300">•</span>
                        <span><?= $senderEmail ?></span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                    <?= portalStatusBadge($document['status']) ?>
                    <?= portalStatusBadge($document['circulation_status']) ?>
                </div>
            </div>
        </div>

        <div class="space-y-5 p-5">

            <!-- OVERVIEW / META -->
            <section class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                <div class="mb-3">
                    <h2 class="text-sm font-semibold text-slate-900">Document Overview</h2>
                    <p class="text-xs text-slate-500">Core document information and recipient assignment.</p>
                </div>

                <div class="grid grid-cols-2 gap-2 md:grid-cols-3 xl:grid-cols-6">
                    <div class="rounded-2xl border border-slate-200 bg-white px-3 py-3">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">Priority</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900"><?= $priority ?></p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white px-3 py-3">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">Date Circulated</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900"><?= $createdAt ?></p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white px-3 py-3">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">Due Date</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900"><?= $dueDate ?></p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white px-3 py-3">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">Receiver Name</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900"><?= $receiverName ?></p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white px-3 py-3">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">Position</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900"><?= $receiverPosition ?></p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white px-3 py-3">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">Department</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900"><?= $receiverDepartment ?></p>
                    </div>
                </div>
            </section>

            <!-- STATUS SNAPSHOT -->
            <section class="rounded-3xl border border-slate-200 bg-white p-4">
                <div class="mb-3">
                    <h2 class="text-sm font-semibold text-slate-900">Receiving Status</h2>
                    <p class="text-xs text-slate-500">Current circulation state and acknowledgment timing.</p>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-3">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">Current Status</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900"><?= $status ?></p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-3">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">Date of Receive</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900"><?= $receivedAt ?></p>
                    </div>
                </div>
            </section>

            <!-- DESCRIPTION -->
            <section class="rounded-2xl border border-slate-200 bg-white p-4">
                <div class="mb-3">
                    <h2 class="text-sm font-semibold text-slate-900">Description</h2>
                    <p class="text-xs text-slate-500">Document summary and context.</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50/40 p-4 text-sm leading-7 text-slate-700">
                    <?= nl2br(htmlspecialchars(strip_tags($document['description'] ?? ''))) ?>
                </div>
            </section>

            <!-- NOTES -->
            <?php if (!empty($document['notes'])): ?>
                <section class="rounded-2xl border border-slate-200 bg-white p-4">
                    <div class="mb-3">
                        <h2 class="text-sm font-semibold text-slate-900">Notes</h2>
                        <p class="text-xs text-slate-500">Additional internal remarks for this circulation.</p>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-amber-50/40 p-4 text-sm leading-7 text-slate-700">
                        <?= nl2br(htmlspecialchars($document['notes'])) ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- THREAD -->
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-4 border-b border-slate-200 bg-slate-50/70 px-5 py-4">
                    <div>
                        <h2 class="text-base font-semibold tracking-tight text-slate-900">Threaded Messages</h2>
                        <p class="text-xs text-slate-500">Updates, clarifications, feedback, and acknowledgements for this document.</p>
                    </div>
                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] font-medium text-slate-600">
                        Tracking Discussion
                    </span>
                </div>

                <div class="p-5">
                    <div class="max-h-[560px] space-y-4 overflow-y-auto pr-1" id="portal-thread-container">
                        <?php if (!empty($thread)): ?>
                            <?php foreach ($thread as $entry): ?>
                                <?php
                                    $sessionUserId = $_SESSION['id'] ?? null;
                                    $sessionStandardId = $_SESSION['standard_user_id'] ?? null;
                                    $entryActorId = $entry['actor_user_id'] ?? null;

                                    $isMine = false;
                                    if (!empty($entryActorId)) {
                                        if ((int)$entryActorId === (int)$sessionUserId || (int)$entryActorId === (int)$sessionStandardId) {
                                            $isMine = true;
                                        }
                                    }

                                    $actorLabel = htmlspecialchars($entry['actor_name'] ?? 'System');
                                    $kind = $entry['entry_kind'] ?? 'message';

                                    $badgeStyles = [
                                        'feedback'      => 'border-amber-200 bg-amber-50 text-amber-700',
                                        'clarification' => 'border-violet-200 bg-violet-50 text-violet-700',
                                        'return'        => 'border-rose-200 bg-rose-50 text-rose-700',
                                        'acceptance'    => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                    ];

                                    $badgeClass = $badgeStyles[strtolower($kind)] ?? 'border-slate-200 bg-slate-100 text-slate-700';
                                ?>

                                <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex min-w-0 items-start gap-3">
                                            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full text-sm font-semibold uppercase tracking-wide <?= $isMine ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700' ?>">
                                                <?= mb_substr($actorLabel, 0, 1) ?>
                                            </div>

                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                                    <span class="text-sm font-semibold text-slate-900"><?= $actorLabel ?></span>

                                             

                                                    <?php if (!empty($entry['role_label'])): ?>
                                                        <span class="text-slate-300">•</span>
                                                        <span class="text-xs text-slate-500"><?= htmlspecialchars($entry['role_label']) ?></span>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="mt-0.5 text-[11px] text-slate-500">
                                                    <?= !empty($entry['created_at']) ? date('M d, Y \a\t g:i A', strtotime($entry['created_at'])) : '—' ?>
                                                </div>
                                            </div>
                                        </div>

                                        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide <?= $badgeClass ?>">
                                            <?= htmlspecialchars($kind) ?>
                                        </span>
                                    </div>

                                    <div class="mt-3 whitespace-pre-wrap break-words text-sm leading-7 text-slate-700">
                                        <?= nl2br(htmlspecialchars($entry['content'] ?? '')) ?>
                                    </div>

                                    <?php if (!empty($entry['files'])): ?>
                                        <div class="mt-4 flex flex-wrap gap-2 border-t border-slate-100 pt-4">
                                            <?php foreach ($entry['files'] as $f): ?>
                                                <?php
                                                    $projectRoot = rtrim(str_replace('\\', '/', dirname(__DIR__, 3)), '/');
                                                    $storedPath = str_replace('\\', '/', (string)($f['file_path'] ?? ''));
                                                    $relativePath = $projectRoot !== '' && strpos($storedPath, $projectRoot . '/') === 0
                                                        ? substr($storedPath, strlen($projectRoot) + 1)
                                                        : basename($storedPath);
                                                    $downloadUrl = rtrim((defined('BASE_URL') ? BASE_URL : '/'), '/') . '/' . ltrim($relativePath, '/');
                                                ?>
                                                <a href="<?= htmlspecialchars($downloadUrl) ?>" target="_blank" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-100">
                                                    <svg class="h-3.5 w-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.414a4 4 0 00-5.656-5.656l-6.415 6.414a6 6 0 108.486 8.486L20.5 13"></path>
                                                    </svg>
                                                    <span class="max-w-[220px] truncate"><?= htmlspecialchars($f['file_name']) ?></span>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($entry['cycle_reference'])): ?>
                                        <div class="mt-3 flex items-center justify-end text-[11px] font-mono text-slate-400">
                                            Ref: <?= htmlspecialchars($entry['cycle_reference']) ?>
                                        </div>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-12 text-center">
                                <p class="text-sm font-medium text-slate-500">No updates posted to this thread yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- THREAD FORM -->
                    <form method="POST"
                          action="index.php?controller=StandardPortal&action=postThreadEntry&id=<?= (int)$document['id'] ?>"
                          enctype="multipart/form-data"
                          class="mt-5 space-y-4 border-t border-slate-200 pt-5">

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-slate-600">
                                    Update Category
                                </label>
                                <select name="entry_kind"
                                        class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-100">
                                    <option value="feedback">Feedback</option>
                                    <option value="clarification">Clarification</option>
                                    <option value="return">Return</option>
                                    <option value="acceptance">Acceptance</option>
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-slate-600">
                                    Add Attachments
                                </label>
                                <input type="file"
                                       name="thread_files[]"
                                       multiple
                                       accept="*/*"
                                       class="block w-full rounded-xl border border-slate-300 bg-white p-1 text-xs text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200" />
                            </div>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-slate-600">
                                Message
                            </label>
                            <textarea name="content"
                                      rows="4"
                                      placeholder="Write an update..."
                                      class="w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-100"></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                    class="inline-flex h-11 items-center justify-center rounded-xl bg-slate-900 px-5 text-sm font-semibold text-white transition hover:bg-slate-800">
                                Post to Thread
                            </button>
                        </div>
                    </form>
                </div>
            </section>

        </div>
    </div>

    <!-- SIDEBAR -->
    <div class="space-y-6">

        <!-- ATTACHMENTS -->
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50/70 px-5 py-4">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Circulated Files</p>
                <h3 class="mt-1 text-lg font-semibold tracking-tight text-slate-900">Attachments</h3>
            </div>

            <div class="space-y-3 p-5">
                <?php foreach ($attachments as $file): ?>
                    <?php if ($isDeleted): ?>
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-400">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold"><?= htmlspecialchars($file['file_name']) ?></p>
                                <p class="text-xs">Unavailable for deleted documents</p>
                            </div>
                            <span class="text-xs font-semibold uppercase tracking-wide">Locked</span>
                        </div>
                    <?php else: ?>
                        <a href="index.php?controller=StandardPortal&action=download&id=<?= (int)$file['id'] ?>"
                           class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 transition hover:border-slate-300 hover:bg-slate-50">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900"><?= htmlspecialchars($file['file_name']) ?></p>
                                <p class="text-xs text-slate-500"><?= number_format(((int)($file['file_size'] ?? 0)) / 1024, 1) ?> KB</p>
                            </div>
                            <span class="text-sm font-semibold text-slate-700">Download</span>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php if (empty($attachments)): ?>
                    <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center">
                        <p class="text-sm text-slate-500">No attachments uploaded.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- RECEIVE DOCUMENT -->
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50/70 px-5 py-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Acknowledgement</p>
                    <h3 class="mt-1 text-lg font-semibold tracking-tight text-slate-900">Receive Document</h3>
                </div>
                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide <?= $receiveBadgeClass ?>">
                    <?= $receiveBadgeLabel ?>
                </span>
            </div>
        </div>

        <form method="POST"
              action="index.php?controller=StandardPortal&action=receive&id=<?= (int)$document['id'] ?>"
              class="space-y-4 p-5 <?= $hasReceived ? 'opacity-80' : '' ?>">
                <?php if ($isDeleted): ?>
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                        This document has been deleted and cannot be received.
                    </div>
                <?php elseif ($hasReceived): ?>
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                        This document has already been marked as received.
                    </div>
                <?php else: ?>
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-700">
                        This document is pending acknowledgement.
                    </div>
                <?php endif; ?>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-700">Assigned PIN Code</label>
                    <input type="password"
                           name="pin_code"
                           required
                           <?= (($document['circulation_status'] ?? '') === 'Received' || $isDeleted) ? 'disabled' : '' ?>
                           class="h-11 w-full rounded-xl border border-slate-300 px-3 text-sm text-slate-900 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-100 disabled:cursor-not-allowed disabled:bg-slate-100">
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-700">Remarks</label>
                    <textarea name="remarks"
                              rows="4"
                              <?= (($document['circulation_status'] ?? '') === 'Received' || $isDeleted) ? 'disabled' : '' ?>
                              class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-100 disabled:cursor-not-allowed disabled:bg-slate-100"></textarea>
                </div>

                <button type="submit"
                        <?= ($hasReceived || $isDeleted) ? 'disabled' : '' ?>
                        class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-slate-900 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50">
                    <?= $hasReceived ? 'Received' : 'Mark as Received' ?>
                </button>
            </form>
        </div>
    </div>
</div>

<?php portalFooter(); ?>