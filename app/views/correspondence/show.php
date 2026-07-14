<?php
$doc = $document ?? [];
$attachments = $attachments ?? [];
$circulations = $circulations ?? [];
$history = $history ?? [];
$status = $status ?? 'Pending';
$documentDetailsStatus = $documentDetailsStatus ?? $status;
$receivedCount = $receivedCount ?? 0;
$totalRecipients = $totalRecipients ?? 0;

// $base = rtrim(defined('BASE_URL') ? BASE_URL : '/', '/') . '/';

$isDeleted = !empty($doc['is_deleted']);
$isClosedDocument = in_array(strtolower(trim((string)($doc['status'] ?? ''))), ['done', 'completed'], true);
$closedDateDisplay = $isClosedDocument && !empty($doc['closed_at']) ? date('M d, Y', strtotime($doc['closed_at'])) : null;
$statusDotClass = match (strtolower((string)$status)) {
    'completed', 'done' => 'bg-emerald-500',
    'rejected', 'suspended' => 'bg-amber-500',
    default => 'bg-slate-400',
};
$priorityDotClass = match (strtolower((string)($doc['priority'] ?? 'medium'))) {
    'urgent' => 'bg-rose-500',
    'high' => 'bg-orange-500',
    'medium' => 'bg-sky-500',
    'low' => 'bg-slate-400',
    default => 'bg-slate-400',
};

?>

<div class="max-w-6xl mx-auto py-3 md:py-4 lg:py-6 correspondence-ui">
    <div class="mb-3 rounded-xl border border-slate-200 bg-white p-3 shadow-sm md:p-4 lg:p-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2 text-[11px] font-medium uppercase tracking-[0.18em] text-slate-500">
                    <span><?= htmlspecialchars($doc['type'] ?? 'Document') ?></span>
                    <span class="text-slate-300">•</span>
                    <span><?= htmlspecialchars($doc['tracking_id'] ?? '—') ?></span>
                </div>
                <h1 class="mt-2 text-base font-semibold tracking-tight text-slate-900"><?= htmlspecialchars($doc['title'] ?? 'Untitled') ?></h1>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-slate-500">
                    <span><?= htmlspecialchars($doc['created_by_name'] ?? ($doc['created_by'] ?? 'System')) ?></span>
                    <?php if (!empty($doc['sender_email'])): ?>
                        <span class="text-slate-300">•</span>
                        <span><?= htmlspecialchars($doc['sender_email']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex flex-col items-start gap-2 lg:items-end">
                <a href="index.php?controller=correspondence&action=correspondence" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">← Back</a>
                <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-600">
                        <span class="h-2 w-2 rounded-full <?= $statusDotClass ?>"></span>
                        <span><?= htmlspecialchars($status) ?></span>
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-600">
                        <span class="h-2 w-2 rounded-full <?= $priorityDotClass ?>"></span>
                        <span><?= htmlspecialchars($doc['priority'] ?? '—') ?></span>
                    </span>
                    <?php if (!empty($doc['is_confidential'])): ?>
                        <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-600">
                            <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                            <span>Confidential</span>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-3 text-sm text-slate-500">
            <span class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-600">Recipients <?= $receivedCount ?>/<?= $totalRecipients ?></span>
            <span class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-600">Attachments <?= count($attachments ?? []) ?></span>
            <span class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-600">Due <?= $doc['due_date'] ? date('M d, Y', strtotime($doc['due_date'])) : '—' ?></span>
            <span class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-600">Updated <?= !empty($doc['updated_at']) ? date('M d, Y', strtotime($doc['updated_at'])) : '—' ?></span>
            <?php if ($isClosedDocument): ?>
                <span class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-600">Closed <?= !empty($closedDateDisplay) ? htmlspecialchars($closedDateDisplay) : '—' ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 md:gap-4">
        <!-- Left: main workspace -->
        <main class="lg:col-span-2 space-y-3 md:space-y-4">
            <section class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm md:p-4">
                <div class="flex items-center justify-between gap-2">
                    <h3 class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Description</h3>
                    <?php
                        $descriptionText = trim((string)($document['description'] ?? ''));
                        $descriptionPreview = $descriptionText !== '' ? nl2br(htmlspecialchars(strip_tags($descriptionText))) : 'No description provided.';
                        $descriptionNeedsToggle = mb_strlen(strip_tags($descriptionText)) > 260;
                    ?>
                </div>
                <div class="mt-2 text-sm leading-7 text-slate-700">
                    <?php if ($descriptionNeedsToggle): ?>
                        <div id="description-collapsed" class="space-y-2">
                            <?= nl2br(htmlspecialchars(mb_substr(strip_tags($descriptionText), 0, 260) . '...')) ?>
                        </div>
                        <div id="description-expanded" class="hidden space-y-2">
                            <?= nl2br(htmlspecialchars(strip_tags($descriptionText))) ?>
                        </div>
                        <button type="button" id="description-toggle" class="mt-2 text-sm font-medium text-slate-600 transition hover:text-slate-900">
                            See more
                        </button>
                    <?php else: ?>
                        <div class="space-y-2">
                            <?= $descriptionPreview ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

    <section class="rounded-lg md:rounded-xl border border-slate-200 bg-white shadow-sm font-sans overflow-hidden">

    <!-- Header -->
    <div class="flex items-center justify-between p-2 md:p-3 lg:p-4 border-b border-slate-100 bg-white">
        <div>
            <p class="text-[9px] md:text-[10px] font-bold uppercase tracking-wider text-slate-600">
                Conversation
            </p>
            <h2 class="text-sm md:text-base font-bold text-slate-900 mt-0.5">
                Thread
            </h2>
        </div>
        <p class="text-xs text-slate-400 font-medium"><?= count($threadEntries ?? []) ?> messages</p>
    </div>

    <!-- Scroll Area -->
    <div class="h-[300px] md:h-[380px] lg:h-[420px] overflow-y-auto px-2 md:px-3 lg:px-4 py-2 md:py-3 lg:py-4 space-y-2 md:space-y-3 bg-slate-100" id="portal-thread-container">

        <?php $threadEntries = $threadEntries ?? []; ?>

        <?php if (!empty($threadEntries)): ?>
            <?php foreach ($threadEntries as $entry): ?>

                <?php
                   
                    $kind = htmlspecialchars($entry['entry_kind'] ?? 'comment');
                    
                    $badgeStyles = [
                        'instruction' => 'bg-blue-100 text-blue-700 border-blue-200',
                        'clarification' => 'bg-purple-100 text-purple-700 border-purple-200',
                        'revision' => 'bg-rose-100 text-rose-700 border-rose-200',
                        'workflow' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                        'comment' => 'bg-slate-200 text-slate-700 border-slate-300'
                    ];
                    $badgeClass = $badgeStyles[strtolower($kind)] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                ?>

                <!-- Timeline Element -->
                <div class="w-full bg-white rounded-lg border border-slate-200 p-2 md:p-3 shadow-sm hover:shadow-md transition">
                    
                    <!-- Meta Row -->
                    <div class="flex flex-wrap items-center justify-between gap-1 md:gap-2 border-b border-slate-100 pb-1.5 md:pb-2 mb-1.5 md:mb-2">
                        <div class="flex items-center gap-1.5 md:gap-2">
                            <!-- Avatar -->
                            <div class="w-5 md:w-6 h-5 md:h-6 rounded-full bg-gradient-to-br from-slate-300 to-slate-400 text-slate-700 flex items-center justify-center text-[9px] md:text-[10px] font-bold uppercase">
                                <?= mb_substr(($entry['actor_name'] ?? 'S'), 0, 1) ?>
                            </div>
                            
                            <div class="flex items-center gap-1 text-[10px] md:text-xs text-slate-600">
                                <span class="font-semibold text-slate-900"><?= htmlspecialchars($entry['actor_name'] ?? 'System') ?></span>
                                <?php if (!empty($entry['role_label'])): ?>
                                    <span class="text-slate-300">•</span>
                                    <span class="text-slate-500 font-mono text-[9px]"><?= htmlspecialchars($entry['role_label']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Right Meta -->
                        <div class="flex items-center gap-1 md:gap-2">
                            <span class="inline-flex items-center rounded-full border px-1.5 md:px-2 py-0.5 text-[8px] md:text-[9px] font-bold uppercase tracking-wider <?= $badgeClass ?>">
                                <?= $kind ?>
                            </span>
                            <span class="text-[9px] md:text-[10px] text-slate-400 font-medium">
                                <?= !empty($entry['created_at']) ? date('m/d, g:iA', strtotime($entry['created_at'])) : '—' ?>
                            </span>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="text-xs md:text-sm text-slate-800 break-words line-clamp-2 md:line-clamp-3">
                        <?= htmlspecialchars($entry['content'] ?? '') ?>
                    </div>

                    <!-- Files -->
                    <?php if (!empty($entry['files'])): ?>
                        <div class="mt-2 flex flex-wrap gap-1">
                            <?php
                                $projectRoot = rtrim(str_replace('\\', '/', dirname(__DIR__, 3)), '/');
                                foreach ($entry['files'] as $f):
                                    $storedPath = str_replace('\\', '/', (string)($f['file_path'] ?? ''));
                                    $relativePath = $projectRoot !== '' && strpos($storedPath, $projectRoot . '/') === 0
                                        ? substr($storedPath, strlen($projectRoot) + 1)
                                        : basename($storedPath);
                                    $downloadUrl = rtrim((defined('BASE_URL') ? BASE_URL : '/'), '/') . '/' . ltrim($relativePath, '/');
                            ?>
                                <a
                                    href="<?= htmlspecialchars($downloadUrl) ?>"
                                    download
                                    class="text-[9px] font-medium inline-flex items-center gap-1 px-1.5 md:px-2 py-0.5 rounded-md border border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 transition"
                                >
                                    <svg class="w-2.5 md:w-3 h-2.5 md:h-3 text-slate-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"></path>
                                    </svg>

                                    <span class="truncate max-w-[120px] md:max-w-[160px]">
                                        <?= htmlspecialchars($f['file_name']) ?>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                </div>

            <?php endforeach; ?>
        <?php else: ?>
            <div class="py-8 text-center border border-dashed border-slate-200 rounded-lg bg-white">
                <p class="text-xs md:text-sm text-slate-400 font-medium">No messages yet.</p>
            </div>
        <?php endif; ?>

    </div>

    <!-- Composer -->
    <?php $userLevel = (int)($_SESSION['user_level'] ?? 3); ?>
    <?php if (!empty($_SESSION['user']) && ($userLevel <= 1 || !empty($canEditCorrespondence))): ?>
    <form method="POST"
          action="index.php?controller=correspondence&action=postThreadEntry"
          enctype="multipart/form-data"
          class="border-t border-slate-100 bg-white p-2 md:p-3 lg:p-4">

        <input type="hidden" name="document_id" value="<?= (int)($document['id'] ?? 0) ?>" />

        <div class="space-y-2 md:space-y-3">

            <!-- Actions row -->
            <div class="flex items-center gap-2 md:gap-3">
                <select name="entry_kind"
                        class="rounded-lg border border-slate-200 bg-white px-2 md:px-3 py-1 md:py-1.5 text-xs md:text-sm text-slate-700 focus:outline-none focus:ring-1 focus:ring-slate-400 cursor-pointer shadow-sm">
                    <option value="instruction">💡 Instruction</option>
                    <option value="clarification">❓ Clarification</option>
                    <option value="revision">↩️ Revision</option>
                    <option value="workflow">⛓️ Workflow</option>
                    <option value="comment" selected>💬 Comment</option>
                </select>

                <label class="cursor-pointer inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2 md:px-3 py-1 md:py-1.5 text-xs md:text-sm text-slate-600 hover:bg-slate-50 transition font-medium">
                    <svg class="w-3 md:w-3.5 h-3 md:h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 0l-3.536 3.536m3.536-3.536L6.586 17.414m0 0a2 2 0 102.828 2.828l7.778-7.778a4 4 0 10-5.656-5.656l-6.364 6.364a6 6 0 108.486 8.486L20.5 13"></path></svg>
                    <span class="hidden md:inline">Attach</span>
                    <input type="file"
                           name="thread_files[]"
                           multiple                           data-max-files="4"
                           data-max-total="41943040"                           class="hidden" />
                </label>
            </div>

            <!-- File status helper -->
            <div id="thread-attachment-status" class="text-[10px] text-slate-500 mt-1 ml-1 hidden"></div>

            <!-- Textarea -->
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm focus-within:ring-1 focus-within:ring-slate-400 focus-within:border-slate-400 overflow-hidden">
                <textarea name="content"
                          rows="2"
                          placeholder="Add message..."
                          class="w-full resize-none px-2 md:px-3 py-1.5 md:py-2 text-xs md:text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none"></textarea>

                <!-- Footer -->
                <div class="flex items-center justify-between border-t border-slate-100 px-2 md:px-3 py-1 md:py-1.5 bg-slate-50/50">
                    <span class="text-[10px] text-slate-400 font-normal">
                        Enter to send • Shift+Enter for newline
                    </span>

                    <button type="submit"
                            class="rounded-lg bg-slate-900 px-3 md:px-4 py-1 md:py-1.5 text-xs font-semibold text-white hover:bg-slate-800 active:scale-[0.98] transition shadow-sm">
                        Send
                    </button>
                </div>
            </div>

        </div>
    </form>
    <?php endif; ?>

    <script>
        (function() {
            const descriptionToggle = document.getElementById('description-toggle');
            const descriptionCollapsed = document.getElementById('description-collapsed');
            const descriptionExpanded = document.getElementById('description-expanded');

            if (descriptionToggle && descriptionCollapsed && descriptionExpanded) {
                descriptionToggle.addEventListener('click', function() {
                    const isExpanded = descriptionExpanded.classList.contains('hidden');
                    descriptionCollapsed.classList.toggle('hidden', isExpanded);
                    descriptionExpanded.classList.toggle('hidden', !isExpanded);
                    descriptionToggle.textContent = isExpanded ? 'See less' : 'See more';
                });
            }

            const threadInput = document.querySelector('input[name="thread_files[]"]');
            const statusEl = document.getElementById('thread-attachment-status');

            if (!threadInput || !statusEl) {
                return;
            }

            const maxFiles = parseInt(threadInput.dataset.maxFiles || '4', 10);
            const maxTotal = parseInt(threadInput.dataset.maxTotal || '41943040', 10);

            function formatBytes(bytes) {
                if (bytes >= 1024 * 1024) {
                    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
                }
                if (bytes >= 1024) {
                    return (bytes / 1024).toFixed(1) + ' KB';
                }
                return bytes + ' bytes';
            }

            function updateThreadAttachmentStatus() {
                const files = Array.from(threadInput.files || []);
                const totalSize = files.reduce((sum, file) => sum + (file.size || 0), 0);

                let text = '';
                let valid = true;

                if (files.length === 0) {
                    text = `No attachments selected. Max ${maxFiles} files, ${formatBytes(maxTotal)} total.`;
                    statusEl.className = 'text-[10px] text-slate-500 mt-1 ml-1';
                } else {
                    text = `${files.length} file(s) selected • ${formatBytes(totalSize)} total of ${formatBytes(maxTotal)}`;
                    if (files.length > maxFiles) {
                        text = `Too many files selected: ${files.length}/${maxFiles}. ${text}`;
                        statusEl.className = 'text-[10px] text-rose-600 mt-1 ml-1';
                        valid = false;
                    } else if (totalSize > maxTotal) {
                        text = `Total size limit exceeded: ${formatBytes(totalSize)} / ${formatBytes(maxTotal)}. ${text}`;
                        statusEl.className = 'text-[10px] text-rose-600 mt-1 ml-1';
                        valid = false;
                    } else {
                        statusEl.className = 'text-[10px] text-emerald-600 mt-1 ml-1';
                    }
                }

                statusEl.textContent = text;
                statusEl.classList.remove('hidden');
                return valid;
            }

            threadInput.addEventListener('change', updateThreadAttachmentStatus);
            updateThreadAttachmentStatus();

            const composerForm = threadInput.closest('form');
            if (composerForm) {
                composerForm.addEventListener('submit', function(event) {
                    if (!updateThreadAttachmentStatus()) {
                        event.preventDefault();
                    }
                });
            }
        })();

        (function() {
            const toggleButton = document.getElementById('toggleActionButton');
            const modal = document.getElementById('confirmToggleStatusModal');
            const modalTitle = document.getElementById('confirmToggleStatusTitle');
            const modalMessage = document.getElementById('confirmToggleStatusMessage');
            const confirmButton = document.getElementById('confirmToggleStatusButton');
            const cancelButton = document.getElementById('cancelToggleStatusButton');
            const toggleActionInput = document.getElementById('toggleActionInput');
            const form = document.getElementById('toggleOpenCloseForm');

            if (!toggleButton || !modal || !confirmButton || !cancelButton || !toggleActionInput || !form) {
                return;
            }

            toggleButton.addEventListener('click', function() {
                const action = this.dataset.action;
                const isReopen = action === 'open';

                modalTitle.textContent = isReopen ? 'Confirm Reopen Document' : 'Confirm Close Document';
                modalMessage.textContent = isReopen
                    ? 'Reopening will mark the document as suspended and resume its workflow. Do you want to continue?'
                    : 'Closing will mark the document circulation as done. Do you want to continue?';
                toggleActionInput.value = action;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });

            cancelButton.addEventListener('click', function() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });

            confirmButton.addEventListener('click', function() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                form.submit();
            });
        })();
    </script>
</section>

        <!-- Change History -->
        <section class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm md:p-4">
            <div class="flex items-center justify-between gap-2">
                <h3 class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">History</h3>
                <span class="text-xs font-medium text-slate-400"><?= count($history ?? []) ?></span>
            </div>

            <div class="mt-3 max-h-[280px] space-y-2 overflow-y-auto pr-1">
                <?php if (!empty($history)): ?>
                    <?php foreach ($history as $h): ?>
                        <?php
                            $fullDesc = trim((string)($h['logDesc'] ?? ''));
                            $activityTitle = $fullDesc !== ''
                                ? (mb_strlen($fullDesc) > 72 ? mb_substr($fullDesc, 0, 69) . '…' : $fullDesc)
                                : 'Activity recorded';
                            $actorName = trim((string)($h['userName'] ?? 'System'));
                            $timestamp = !empty($h['logDate']) ? date('M d, Y · g:i A', strtotime($h['logDate'])) : '—';
                        ?>
                        <div class="relative pl-4">
                            <span class="absolute left-0 top-3 h-2.5 w-2.5 rounded-full bg-slate-300"></span>
                            <div class="rounded-lg border border-slate-100 bg-white p-2.5 shadow-sm">
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-semibold text-slate-800">
                                        <?= htmlspecialchars($activityTitle) ?>
                                    </div>
                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
                                        <span class="font-medium text-slate-600"><?= htmlspecialchars($actorName ?: 'System') ?></span>
                                        <span class="text-slate-300">•</span>
                                        <span><?= htmlspecialchars($timestamp) ?></span>
                                    </div>
                                </div>

                                <details class="mt-2 group">
                                    <summary class="cursor-pointer list-none text-[11px] font-medium text-slate-500 transition hover:text-slate-700">
                                        <span class="group-open:hidden">View details</span>
                                        <span class="hidden group-open:inline">Hide details</span>
                                    </summary>
                                    <div class="mt-2 rounded-md bg-slate-50 px-2.5 py-2 text-sm leading-6 text-slate-600">
                                        <?= nl2br(htmlspecialchars($fullDesc !== '' ? $fullDesc : 'No details recorded.')) ?>
                                    </div>
                                </details>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="rounded-lg border border-dashed border-slate-200 bg-slate-50/70 px-3 py-3 text-sm text-slate-500">No history yet.</div>
                <?php endif; ?>
            </div>
        </section>
        </main>

        <!-- Right: sidebar -->
        <aside class="space-y-3 md:space-y-4">
            <!-- Attachments -->
            <section class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm md:p-4">
                <div class="flex items-center justify-between gap-2">
                    <h3 class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Attachments</h3>
                    <span class="text-xs font-medium text-slate-400"><?= count($attachments ?? []) ?></span>
                </div>

                <div class="mt-3 space-y-1">
                <?php if (!empty($attachments)): ?>
                    <?php foreach ($attachments as $a): ?>
                        <a href="index.php?controller=correspondence&action=download&attachment_id=<?= (int)($a['id'] ?? 0) ?>" class="flex items-center justify-between gap-2 rounded-lg px-2 py-2 text-sm text-slate-700 transition hover:bg-slate-50">
                            <div class="flex min-w-0 items-center gap-2">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-slate-100 text-slate-500">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7.5V5.25A2.25 2.25 0 0 1 9.25 3h5.5A2.25 2.25 0 0 1 17 5.25v2.25m-10 0h10m-10 0v11.25A2.25 2.25 0 0 0 9.25 21h5.5A2.25 2.25 0 0 0 17 18.75V7.5"></path></svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="truncate font-medium text-slate-700"><?= htmlspecialchars($a['file_name'] ?? 'file') ?></div>
                                    <?php if (!empty($a['file_size'])): ?>
                                        <div class="mt-0.5 text-xs text-slate-400"><?= number_format((int)$a['file_size'] / 1024, 1) ?> KB</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="shrink-0 text-xs font-medium text-slate-500">Download</span>
                        </a>
                        <div class="h-px bg-slate-100 last:hidden"></div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="rounded-lg border border-dashed border-slate-200 bg-slate-50/70 px-3 py-3 text-sm text-slate-500">No attachments.</div>
                <?php endif; ?>
                </div>
            </section>

            <!-- Recipients -->
            <section class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm md:p-4">
                <div class="flex items-center justify-between gap-2">
                    <h3 class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Recipients</h3>
                    <span class="text-xs font-medium text-slate-400"><?= (int)count($recipientsTo ?? []) + (int)count($recipientsCc ?? []) ?></span>
                </div>

                <div class="mt-3 space-y-3">
                    <?php
                        $renderRecipientRow = function($recipient, $toneClass, $statusDotClass) {
                            $name = htmlspecialchars($recipient['name'] ?? '—');
                            $office = htmlspecialchars($recipient['office'] ?? '');
                            $initials = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 2));
                            $statusValue = strtolower(trim($recipient['status'] ?? ''));
                            $isReceived = $statusValue === 'received';
                            $displayStatus = $isReceived ? 'Received' : 'Pending';
                            $dotClass = $isReceived ? 'bg-emerald-500' : 'bg-amber-500';
                            echo '<div class="flex items-center gap-2 rounded-lg px-2 py-2 transition hover:bg-slate-50">';
                            echo '<div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[11px] font-semibold text-slate-600">' . ($initials ?: '•') . '</div>';
                            echo '<div class="min-w-0 flex-1">';
                            echo '<div class="truncate text-sm font-medium text-slate-900">' . $name . '</div>';
                            echo '<div class="truncate text-xs text-slate-500">' . ($office !== '' ? $office : 'No organization') . '</div>';
                            echo '</div>';
                            echo '<span class="inline-flex items-center gap-1.5 shrink-0 text-xs font-medium text-slate-500">';
                            echo '<span class="h-2 w-2 rounded-full ' . $dotClass . '"></span>';
                            echo '<span>' . htmlspecialchars($displayStatus) . '</span>';
                            echo '</span>';
                            echo '</div>';
                        };
                    ?>

                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-[11px] font-semibold uppercase tracking-[0.2em] text-emerald-600">To</span>
                            <span class="text-xs font-medium text-slate-400"><?= count($recipientsTo ?? []) ?></span>
                        </div>
                        <div class="space-y-1">
                            <?php if (!empty($recipientsTo)): ?>
                                <?php foreach ($recipientsTo as $r): ?>
                                    <?php $renderRecipientRow($r, 'emerald', 'bg-emerald-500'); ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="px-2 py-2 text-sm text-slate-500">No recipients.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-[11px] font-semibold uppercase tracking-[0.2em] text-sky-600">Cc</span>
                            <span class="text-xs font-medium text-slate-400"><?= count($recipientsCc ?? []) ?></span>
                        </div>
                        <div class="space-y-1">
                            <?php if (!empty($recipientsCc)): ?>
                                <?php foreach ($recipientsCc as $r): ?>
                                    <?php $renderRecipientRow($r, 'sky', 'bg-sky-500'); ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="px-2 py-2 text-sm text-slate-500">No CC recipients.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Metadata -->
            <section class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm md:p-4">
                <div class="flex items-center justify-between gap-2">
                    <h3 class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Metadata</h3>
                    <span class="text-xs font-medium text-slate-400">Properties</span>
                </div>

                <div class="mt-3 space-y-3">
                    <div>
                        <div class="mb-1.5 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">Overview</div>
                        <div class="space-y-1">
                            <div class="grid grid-cols-[88px_minmax(0,1fr)] items-center gap-2 rounded-md px-2 py-1.5">
                                <span class="text-xs font-medium text-slate-500">Sender</span>
                                <span class="text-sm font-medium text-slate-700"><?= htmlspecialchars($doc['sender_email'] ?? '—') ?></span>
                            </div>
                            <div class="grid grid-cols-[88px_minmax(0,1fr)] items-center gap-2 rounded-md px-2 py-1.5">
                                <span class="text-xs font-medium text-slate-500">Priority</span>
                                <span class="text-sm font-medium text-slate-700"><?= htmlspecialchars($doc['priority'] ?? '—') ?></span>
                            </div>
                            <div class="grid grid-cols-[88px_minmax(0,1fr)] items-center gap-2 rounded-md px-2 py-1.5">
                                <span class="text-xs font-medium text-slate-500">Due Date</span>
                                <span class="text-sm font-medium text-slate-700"><?= $doc['due_date'] ? date('M d, Y', strtotime($doc['due_date'])) : '—' ?></span>
                            </div>
                            <div class="grid grid-cols-[88px_minmax(0,1fr)] items-center gap-2 rounded-md px-2 py-1.5">
                                <span class="text-xs font-medium text-slate-500">Confidentiality</span>
                                <span class="text-sm font-medium text-slate-700"><?= !empty($doc['is_confidential']) ? 'Yes' : 'No' ?></span>
                            </div>
                        </div>
                    </div>

                    <?php if ($isClosedDocument): ?>
                        <div class="border-t border-slate-100 pt-2.5">
                            <div class="mb-1.5 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">Closure</div>
                            <div class="space-y-1">
                                <div class="grid grid-cols-[88px_minmax(0,1fr)] items-center gap-2 rounded-md px-2 py-1.5">
                                    <span class="text-xs font-medium text-slate-500">Closed At</span>
                                    <span class="text-sm font-medium text-slate-700"><?= !empty($closedDateDisplay) ? htmlspecialchars($closedDateDisplay) : '—' ?></span>
                                </div>
                                <div class="grid grid-cols-[88px_minmax(0,1fr)] items-center gap-2 rounded-md px-2 py-1.5">
                                    <span class="text-xs font-medium text-slate-500">Closed By</span>
                                    <span class="text-sm font-medium text-slate-700"><?= htmlspecialchars($doc['closed_by'] ?? '—') ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

        <?php if ((int)($_SESSION['user_level'] ?? 3) <= 1): ?>
        <!-- Admin Controls -->
        <section class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm md:p-4">
            <div class="flex items-center justify-between gap-2">
                <h3 class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Admin</h3>
                <span class="text-xs font-medium text-slate-400"><?= $isClosedDocument ? 'Closed' : 'Open' ?></span>
            </div>

            <form id="toggleOpenCloseForm" method="POST" action="index.php?controller=correspondence&action=toggleOpenClose" class="mt-3">
                <input type="hidden" name="document_id" value="<?= (int)($doc['id'] ?? 0) ?>">
                <input type="hidden" name="toggle_action" id="toggleActionInput" value="">
                <?php if ($isClosedDocument): ?>
                    <button type="button" id="toggleActionButton" data-action="open" onclick="(function(){var m=document.getElementById('confirmToggleStatusModal');document.getElementById('confirmToggleStatusTitle').textContent='Confirm Reopen Document';document.getElementById('confirmToggleStatusMessage').textContent='Reopening will mark the document as suspended and resume its workflow. Do you want to continue?';document.getElementById('toggleActionInput').value='open';m.classList.remove('hidden');m.classList.add('flex');})();" class="w-full rounded-lg border border-rose-200 bg-white px-3 py-2 text-sm font-semibold text-rose-700 shadow-sm transition hover:border-rose-300 hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-400">
                        Reopen document
                    </button>
                <?php else: ?>
                    <button type="button" id="toggleActionButton" data-action="close" onclick="(function(){var m=document.getElementById('confirmToggleStatusModal');document.getElementById('confirmToggleStatusTitle').textContent='Confirm Close Document';document.getElementById('confirmToggleStatusMessage').textContent='Closing will mark the document circulation as done. Do you want to continue?';document.getElementById('toggleActionInput').value='close';m.classList.remove('hidden');m.classList.add('flex');})();" class="w-full rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        Close document
                    </button>
                <?php endif; ?>
            </form>
        </section>
        <?php endif; ?>
        </aside>
    </div>

    <div id="confirmToggleStatusModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 px-4 py-6">
        <div class="w-full max-w-lg rounded-3xl bg-white shadow-2xl ring-1 ring-slate-200">
            <div class="px-6 py-5 border-b border-slate-200">
                <h2 id="confirmToggleStatusTitle" class="text-lg font-semibold text-slate-900">Confirm action</h2>
            </div>
            <div class="px-6 py-5">
                <p id="confirmToggleStatusMessage" class="text-sm leading-6 text-slate-700">Are you sure you want to perform this action?</p>
            </div>
            <div class="flex flex-col gap-3 px-6 pb-6 md:flex-row md:justify-end">
                <button type="button" id="cancelToggleStatusButton" onclick="(function(){var m=document.getElementById('confirmToggleStatusModal');m.classList.add('hidden');m.classList.remove('flex');})();" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Cancel</button>
                <button type="button" id="confirmToggleStatusButton" onclick="(function(){var m=document.getElementById('confirmToggleStatusModal');m.classList.add('hidden');m.classList.remove('flex');document.getElementById('toggleOpenCloseForm').submit();})();" class="inline-flex h-11 items-center justify-center rounded-xl bg-slate-900 px-4 text-sm font-semibold text-white transition hover:bg-slate-800">Confirm</button>
            </div>
        </div>
    </div>
</div>

