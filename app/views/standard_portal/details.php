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

<div class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_320px]">

    <!-- MAIN COLUMN -->
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

        <!-- HEADER -->
        <div class="border-b border-slate-200 bg-white px-4 py-3">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h1 class="text-base font-semibold tracking-tight text-slate-900">
                                <?= $title ?>
                            </h1>
                            <p class="mt-1 text-xs text-slate-500"><?= $trackingId ?></p>
                        </div>

                        <div class="flex flex-wrap items-center justify-end gap-2">
                            <?= portalStatusBadge($document['status']) ?>
                            <?= portalStatusBadge($document['circulation_status']) ?>
                        </div>
                    </div>

                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                        <span class="font-medium text-slate-600"><?= $type ?></span>
                        <span class="text-slate-300">•</span>
                        <span><?= $senderEmail ?></span>
                        <span class="text-slate-300">•</span>
                        <span>Created <?= $createdAt ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4 p-4">

            <!-- OVERVIEW / META -->
            <section class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="mb-3">
                    <h2 class="text-sm font-semibold text-slate-900">Document Information</h2>
                    <p class="text-xs text-slate-500">Key details for quick reference.</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-2.5">
                        <span class="text-xs font-medium text-slate-500">Priority</span>
                        <span class="text-right text-sm font-semibold text-slate-900"><?= $priority ?></span>
                    </div>

                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-2.5">
                        <span class="text-xs font-medium text-slate-500">Status</span>
                        <span class="text-right text-sm font-semibold text-slate-900"><?= $status ?></span>
                    </div>

                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-2.5">
                        <span class="text-xs font-medium text-slate-500">Circulated Date</span>
                        <span class="text-right text-sm font-semibold text-slate-900"><?= $createdAt ?></span>
                    </div>

                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-2.5">
                        <span class="text-xs font-medium text-slate-500">Received Date</span>
                        <span class="text-right text-sm font-semibold text-slate-900"><?= $receivedAt ?></span>
                    </div>

                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-2.5">
                        <span class="text-xs font-medium text-slate-500">Due Date</span>
                        <span class="text-right text-sm font-semibold text-slate-900"><?= $dueDate ?></span>
                    </div>

                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-2.5">
                        <span class="text-xs font-medium text-slate-500">Receiver</span>
                        <span class="text-right text-sm font-semibold text-slate-900"><?= $receiverName ?></span>
                    </div>

                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-2.5">
                        <span class="text-xs font-medium text-slate-500">Position</span>
                        <span class="text-right text-sm font-semibold text-slate-900"><?= $receiverPosition ?></span>
                    </div>

                    <div class="flex items-start justify-between gap-4 pb-1">
                        <span class="text-xs font-medium text-slate-500">Department</span>
                        <span class="text-right text-sm font-semibold text-slate-900"><?= $receiverDepartment ?></span>
                    </div>
                </div>
            </section>

            <!-- DESCRIPTION -->
            <?php $descriptionText = trim((string)($document['description'] ?? '')); ?>
            <?php if ($descriptionText !== ''): ?>
                <section class="rounded-xl border border-slate-200 bg-white p-4">
                    <h2 class="text-sm font-semibold text-slate-900">Description</h2>
                    <div class="mt-2 max-w-3xl text-sm leading-6 text-slate-700">
                        <?= nl2br(htmlspecialchars($descriptionText)) ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- NOTES -->
            <?php if (!empty($document['notes'])): ?>
                <section class="rounded-xl border border-slate-200 bg-white p-4">
                    <div class="mb-2">
                        <h2 class="text-sm font-semibold text-slate-900">Notes</h2>
                        <p class="text-xs text-slate-500">Additional internal remarks for this circulation.</p>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm leading-6 text-slate-700">
                        <?= nl2br(htmlspecialchars($document['notes'])) ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- THREAD -->
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-4 border-b border-slate-200 bg-white px-4 py-3">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">Threaded Messages</h2>
                        <p class="text-xs text-slate-500">Updates, clarifications, feedback, and acknowledgements for this document.</p>
                    </div>
                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-medium text-slate-600">
                        Tracking Discussion
                    </span>
                </div>

                <div class="p-4">
                    <div class="max-h-[480px] space-y-3 overflow-y-auto pr-1" id="portal-thread-container">
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
                                        'feedback'      => 'text-amber-700',
                                        'clarification' => 'text-violet-700',
                                        'return'        => 'text-rose-700',
                                        'acceptance'    => 'text-emerald-700',
                                    ];

                                    $badgeClass = $badgeStyles[strtolower($kind)] ?? 'text-slate-700';
                                ?>

                                <article class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3">
                                    <div class="flex flex-wrap items-start justify-between gap-2">
                                        <div class="flex min-w-0 items-center gap-2.5">
                                            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full text-sm font-semibold uppercase tracking-wide <?= $isMine ? 'bg-slate-900 text-white' : 'bg-slate-200 text-slate-700' ?>">
                                                <?= mb_substr($actorLabel, 0, 1) ?>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="text-sm font-semibold text-slate-900"><?= $actorLabel ?></span>
                                                    <?php if (!empty($entry['role_label'])): ?>
                                                        <span class="text-xs text-slate-500"><?= htmlspecialchars($entry['role_label']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="mt-0.5 text-[11px] text-slate-500">
                                                    <?= !empty($entry['created_at']) ? date('M d, Y \a\t g:i A', strtotime($entry['created_at'])) : '—' ?>
                                                </div>
                                            </div>
                                        </div>

                                        <span class="text-[11px] font-semibold uppercase tracking-[0.16em] <?= $badgeClass ?>">
                                            <?= htmlspecialchars($kind) ?>
                                        </span>
                                    </div>

                                    <div class="mt-2 break-words text-sm leading-6 text-slate-700">
                                        <?= nl2br(htmlspecialchars($entry['content'] ?? '')) ?>
                                    </div>

                                    <?php if (!empty($entry['files'])): ?>
                                        <div class="mt-3 border-t py-2 flex flex-wrap gap-2">
                                            <?php foreach ($entry['files'] as $f): ?>
                                                <?php
                                                    $projectRoot = rtrim(str_replace('\\', '/', dirname(__DIR__, 3)), '/');
                                                    $storedPath = str_replace('\\', '/', (string)($f['file_path'] ?? ''));
                                                    $relativePath = $projectRoot !== '' && strpos($storedPath, $projectRoot . '/') === 0
                                                        ? substr($storedPath, strlen($projectRoot) + 1)
                                                        : basename($storedPath);
                                                    $downloadUrl = rtrim((defined('BASE_URL') ? BASE_URL : '/'), '/') . '/' . ltrim($relativePath, '/');
                                                ?>
                                                <a href="<?= htmlspecialchars($downloadUrl) ?>" target="_blank" class="inline-flex items-center gap-2 rounded-lg bg-white px-3 py-2 text-xs font-medium text-slate-700 transition hover:bg-slate-100">
                                                    <svg class="h-3.5 w-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.414a4 4 0 00-5.656-5.656l-6.415 6.414a6 6 0 108.486 8.486L20.5 13"></path>
                                                    </svg>
                                                    <span class="max-w-[220px] truncate"><?= htmlspecialchars($f['file_name']) ?></span>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($entry['cycle_reference'])): ?>
                                        <div class="mt-2 flex items-center justify-end text-[11px] font-mono text-slate-400">
                                            Ref: <?= htmlspecialchars($entry['cycle_reference']) ?>
                                        </div>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="rounded-lg border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center">
                                <p class="text-sm font-medium text-slate-500">No updates posted to this thread yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- THREAD FORM -->
                    <form method="POST"
                          action="index.php?controller=StandardPortal&action=postThreadEntry&id=<?= (int)$document['id'] ?>"
                          enctype="multipart/form-data"
                          class="mt-4 space-y-3 border-t border-slate-200 pt-4">

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-[minmax(0,180px)_minmax(0,1fr)]">
                            <div>
                                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-slate-600">
                                    Category
                                </label>
                                <select name="entry_kind"
                                        class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-100">
                                    <option value="feedback">Feedback</option>
                                    <option value="clarification">Clarification</option>
                                    <option value="return">Return</option>
                                    <option value="acceptance">Acceptance</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-slate-600">
                                    Attachments
                                </label>
                                <input type="file"
                                       name="thread_files[]"
                                       multiple
                                       accept="*/*"
                                       class="block w-full rounded-lg border border-slate-300 bg-white p-1.5 text-xs text-slate-500 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200" />
                            </div>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-slate-600">
                                Message
                            </label>
                            <textarea name="content"
                                      rows="4"
                                      placeholder="Write an update..."
                                      class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-100"></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                    class="inline-flex h-10 items-center justify-center rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white transition hover:bg-slate-800">
                                Post to Thread
                            </button>
                        </div>
                    </form>
                </div>
            </section>

        </div>
    </div>

    <!-- SIDEBAR -->
    <div class="space-y-3 xl:sticky xl:top-4 xl:self-start">

        <!-- ATTACHMENTS -->
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-white px-4 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Circulated Files</p>
                <h3 class="mt-1 text-sm font-semibold tracking-tight text-slate-900">Attachments</h3>
            </div>

            <div class="p-2">
                <?php foreach ($attachments as $file): ?>
                    <?php if ($isDeleted): ?>
                        <div class="flex items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-slate-400">
                            <div class="flex min-w-0 items-center gap-2.5">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7v10a2 2 0 002 2h6a2 2 0 002-2V9.414a2 2 0 00-.586-1.414l-2.414-2.414A2 2 0 0012.586 5H9a2 2 0 00-2 2z"></path>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-500"><?= htmlspecialchars($file['file_name']) ?></p>
                                    <p class="text-xs">Unavailable for deleted documents</p>
                                </div>
                            </div>
                            <span class="shrink-0 text-xs font-semibold uppercase tracking-wide">Locked</span>
                        </div>
                    <?php else: ?>
                        <a href="index.php?controller=StandardPortal&action=download&id=<?= (int)$file['id'] ?>"
                           class="flex items-center justify-between gap-3 rounded-lg px-3 py-2 transition hover:bg-slate-50">
                            <div class="flex min-w-0 items-center gap-2.5">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7v10a2 2 0 002 2h6a2 2 0 002-2V9.414a2 2 0 00-.586-1.414l-2.414-2.414A2 2 0 0012.586 5H9a2 2 0 00-2 2z"></path>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900"><?= htmlspecialchars($file['file_name']) ?></p>
                                    <p class="text-xs text-slate-500"><?= number_format(((int)($file['file_size'] ?? 0)) / 1024, 1) ?> KB</p>
                                </div>
                            </div>
                            <span class="shrink-0 text-sm font-semibold text-slate-700">Download</span>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php if (empty($attachments)): ?>
                    <div class="rounded-lg bg-slate-50 px-4 py-6 text-center">
                        <p class="text-sm text-slate-500">No attachments uploaded.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- RECEIVE DOCUMENT -->
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-slate-900 text-white shadow-sm">
            <div class="border-b border-white/10 bg-slate-900/95 px-4 py-3">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Acknowledgement</p>
                        <h3 class="mt-1 text-sm font-semibold tracking-tight text-white">Receive Document</h3>
                    </div>
                    <span class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-slate-100">
                        <?= $receiveBadgeLabel ?>
                    </span>
                </div>
            </div>

            <form method="POST"
                  action="index.php?controller=StandardPortal&action=receive&id=<?= (int)$document['id'] ?>"
                  class="space-y-3 p-4 <?= $hasReceived ? 'opacity-90' : '' ?>">
                <?php if ($isDeleted): ?>
                    <div class="rounded-lg border border-rose-400/20 bg-rose-500/10 px-3 py-2.5 text-sm font-medium text-rose-200">
                        This document has been deleted and cannot be received.
                    </div>
                <?php elseif ($hasReceived): ?>
                    <div class="rounded-lg border border-emerald-400/20 bg-emerald-500/10 px-3 py-2.5 text-sm font-medium text-emerald-200">
                        <p class="font-semibold">Document received</p>
                        <p class="mt-1 text-emerald-100/90">This document has already been marked as received and no further action is required.</p>
                    </div>
                <?php else: ?>
                    <div class="rounded-lg border border-amber-400/20 bg-amber-500/10 px-3 py-2.5 text-sm font-medium text-amber-200">
                        <p class="font-semibold">Pending acknowledgement</p>
                        <p class="mt-1 text-amber-100/90">Complete the details below to mark this document as received & download the attachments.</p>
                    </div>
                <?php endif; ?>

                <?php if (!$isDeleted && !$hasReceived): ?>
                    <div class="space-y-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Assigned PIN Code</label>
                            <input type="password"
                                   name="pin_code"
                                   required
                                   class="h-10 w-full rounded-lg border border-white/10 bg-white/10 px-3 text-sm text-white placeholder-slate-400 outline-none transition focus:border-slate-400 focus:bg-white/15">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Remarks</label>
                            <textarea name="remarks"
                                      rows="4"
                                      class="w-full rounded-lg border border-white/10 bg-white/10 px-3 py-2.5 text-sm text-white placeholder-slate-400 outline-none transition focus:border-slate-400 focus:bg-white/15"></textarea>
                        </div>
                    </div>
                <?php endif; ?>

                <button type="submit"
                        <?= ($hasReceived || $isDeleted) ? 'disabled' : '' ?>
                        class="inline-flex h-9 w-full items-center justify-center rounded-lg bg-white text-sm font-semibold text-slate-900 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-60">
                    <?= $hasReceived ? 'Received' : 'Mark as Received' ?>
                </button>
            </form>
        </div>
    </div>
</div>

<?php portalFooter(); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Select all download links for attachments (server uses action=download)
    const downloadLinks = Array.from(document.querySelectorAll('a[href*="action=download"]'));

    if (!downloadLinks.length) return;

    const disableLink = (link) => {
        link.dataset.disabledByPin = 'true';
        link.setAttribute('aria-disabled', 'true');
        link.tabIndex = -1;
        link.classList.add('pointer-events-none', 'opacity-60');
        if (!link._boundPrevent) {
            link._boundPrevent = function (e) { if (link.dataset.disabledByPin === 'true') e.preventDefault(); };
            link.addEventListener('click', link._boundPrevent, { capture: true });
        }
    };

    const enableLink = (link) => {
        link.dataset.disabledByPin = 'false';
        link.removeAttribute('aria-disabled');
        link.tabIndex = 0;
        link.classList.remove('pointer-events-none', 'opacity-60');
    };

    const disableAll = () => downloadLinks.forEach(disableLink);
    const enableAll = () => downloadLinks.forEach(enableLink);

    // Initial state: disabled by default
    disableAll();

    // Helper: detect received state on the page
    const documentIsReceived = () => {
        // 1) Check receive form for opacity-80 class set by server when received
        const receiveForm = document.querySelector('form[action*="action=receive"]');
        if (receiveForm && receiveForm.classList.contains('opacity-80')) return true;

        // 2) Check for the exact green acknowledgement message added by server
        const receivedBanner = Array.from(document.querySelectorAll('div')).find(d => d.textContent && d.textContent.trim().includes('This document has already been marked as received'));
        if (receivedBanner) return true;

        // 3) Check for any portal status badge text that equals "Received"
        const badges = Array.from(document.querySelectorAll('span')).map(s => s.textContent && s.textContent.trim());
        if (badges.includes('Received')) return true;

        return false;
    };

    // If page already indicates received, enable downloads immediately
    if (documentIsReceived()) {
        enableAll();
        return;
    }

    // Observe DOM changes to enable downloads when server-side markup appears (e.g., after AJAX or reload)
    const observer = new MutationObserver((mutations) => {
        if (documentIsReceived()) {
            enableAll();
            observer.disconnect();
        }
    });

    observer.observe(document.body, { childList: true, subtree: true, characterData: true });
});
</script>