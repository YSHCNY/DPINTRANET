<?php
$doc = $document ?? [];
$attachments = $attachments ?? [];
$circulations = $circulations ?? [];
$history = $history ?? [];
$status = $status ?? 'Pending';
$documentDetailsStatus = $documentDetailsStatus ?? $status;
$receivedCount = $receivedCount ?? 0;
$totalRecipients = $totalRecipients ?? 0;

$isDeleted = !empty($doc['is_deleted']);
$priorityClass = match ($doc['priority'] ?? 'Medium') {
    'Urgent' => 'bg-red-50 text-red-700',
    'High' => 'bg-orange-50 text-orange-700',
    'Medium' => 'bg-blue-50 text-blue-700',
    default => 'bg-gray-50 text-gray-700',
};
$statusClass = match (strtolower((string)$status)) {
    'completed', 'done' => 'bg-emerald-50 text-emerald-700',
    'rejected', 'suspended' => 'bg-amber-50 text-amber-700',
    default => 'bg-slate-100 text-slate-600',
};

?>

<div class="max-w-6xl mx-auto py-3 md:py-4 lg:py-6 correspondence-ui">
    <!-- Header card -->
    <div class="rounded-lg md:rounded-xl border border-slate-200 bg-white p-2 md:p-3 lg:p-4 shadow-sm mb-3 md:mb-4">
        <div class="flex items-start justify-between gap-2 md:gap-3">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5 md:gap-2 flex-wrap">
                    <div class="font-mono text-[10px] md:text-xs text-blue-700 px-1.5 md:px-2 py-1 rounded bg-blue-50"><?= htmlspecialchars($doc['tracking_id'] ?? '—') ?></div>
                    <h1 class="text-base md:text-lg lg:text-xl font-semibold text-slate-900 truncate"><?= htmlspecialchars($doc['title'] ?? 'Untitled') ?></h1>
                </div>
                <div class="mt-1.5 md:mt-2 flex flex-wrap items-center gap-2 text-xs md:text-sm text-slate-500">
                    <span class="font-medium"><?= htmlspecialchars($doc['type'] ?? '—') ?></span>
                    <span class="text-slate-300">•</span>
                    <span><?= htmlspecialchars($doc['created_by_name'] ?? ($doc['created_by'] ?? 'System')) ?></span>
                </div>
            </div>
            <div class="flex flex-col items-end gap-1.5 md:gap-2">
                <a type="button" href= 'index.php?controller=correspondence&action=correspondence' class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-2 md:px-3 py-1 md:py-1.5 text-xs md:text-sm font-medium text-slate-700 hover:bg-slate-50 transition">← Back</a>
                <div class="flex items-center gap-1 md:gap-2 flex-wrap justify-end">
                    <span class="inline-flex items-center rounded-full px-2 md:px-2.5 py-0.5 md:py-1 text-[10px] md:text-xs font-bold uppercase tracking-wider <?= $statusClass ?>"><?= htmlspecialchars($status) ?></span>
                    <span class="inline-flex items-center rounded-full px-2 md:px-2.5 py-0.5 md:py-1 text-[10px] md:text-xs font-bold uppercase tracking-wider <?= $priorityClass ?>"><?= htmlspecialchars($doc['priority'] ?? '—') ?></span>
                    <?php if (!empty($doc['is_confidential'])): ?>
                        <span class="inline-flex items-center rounded-full px-2 md:px-2.5 py-0.5 md:py-1 text-[10px] md:text-xs font-bold uppercase tracking-wider bg-red-50 text-red-700">🔒 Confidential</span>
                    <?php endif; ?>
                </div>
                <div class="text-right text-xs md:text-sm text-slate-500 leading-tight">
                    <div>Due: <?= $doc['due_date'] ? date('M d, Y', strtotime($doc['due_date'])) : '—' ?></div>
                    <div class="mt-0.5">Updated: <?= !empty($doc['updated_at']) ? date('M d, Y', strtotime($doc['updated_at'])) : '—' ?></div>
                </div>
            </div>
        </div>

        <!-- Quick stats row -->
        <div class="mt-2 md:mt-3 grid grid-cols-3 gap-1.5 md:gap-2">
            <div class="rounded-lg border border-slate-200 bg-gradient-to-br from-blue-50 to-blue-100/50 px-2 md:px-3 py-1.5 md:py-2">
                <p class="text-[9px] md:text-[10px] font-bold uppercase tracking-wider text-blue-600">Recipients</p>
                <p class="mt-0.5 text-sm md:text-base font-bold text-blue-900"><?= $receivedCount ?>/<?= $totalRecipients ?></p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-gradient-to-br from-emerald-50 to-emerald-100/50 px-2 md:px-3 py-1.5 md:py-2">
                <p class="text-[9px] md:text-[10px] font-bold uppercase tracking-wider text-emerald-600">Status</p>
                <p class="mt-0.5 text-sm md:text-base font-bold text-emerald-900"><?= $receivedCount ?> of <?= $totalRecipients ?></p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-gradient-to-br from-amber-50 to-amber-100/50 px-2 md:px-3 py-1.5 md:py-2">
                <p class="text-[9px] md:text-[10px] font-bold uppercase tracking-wider text-amber-600">Attachments</p>
                <p class="mt-0.5 text-sm md:text-base font-bold text-amber-900"><?= count($attachments ?? []) ?></p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 md:gap-4">
        <!-- Left: main workspace -->
        <main class="lg:col-span-2 space-y-3 md:space-y-4">
            <section class="rounded-lg md:rounded-xl border border-slate-200 bg-white p-2 md:p-3 lg:p-4 shadow-sm">
                <h3 class="text-xs md:text-sm font-bold uppercase tracking-wider text-slate-600">Description</h3>
                <div class="mt-2 md:mt-3 text-xs md:text-sm text-slate-700 leading-6 line-clamp-4 md:line-clamp-none">
                    <?= nl2br(htmlspecialchars($doc['description'] ?? '—')) ?>
                </div>
            </section>

    <section class="rounded-lg md:rounded-xl border border-slate-200 bg-white shadow-sm font-sans overflow-hidden">

    <!-- Header -->
    <div class="flex items-center justify-between p-2 md:p-3 lg:p-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white">
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
    <div class="h-[300px] md:h-[380px] lg:h-[420px] overflow-y-auto px-2 md:px-3 lg:px-4 py-2 md:py-3 lg:py-4 space-y-2 md:space-y-3 bg-slate-50/50" id="portal-thread-container">

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
                            <?php foreach ($entry['files'] as $f): ?>
                                <a
                                    href="<?= htmlspecialchars((defined('BASE_URL') ? BASE_URL : '/') . 'uploads/correspondence/' . basename((string)($f['file_path'] ?? ''))) ?>"
                                    download
                                    class="text-[9px] font-medium inline-flex items-center gap-1 px-1.5 md:px-2 py-0.5 rounded-md border border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 transition"
                                >
                                    <svg class="w-2.5 md:w-3 h-2.5 md:h-3 text-slate-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"></path></svg>
                                    <span class="truncate max-w-[120px] md:max-w-[160px]"><?= htmlspecialchars($f['file_name']) ?></span>
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
          class="border-t border-slate-100 bg-slate-50/60 p-2 md:p-3 lg:p-4">

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
                           multiple
                           class="hidden" />
                </label>
            </div>

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
</section>

        <!-- Change History -->
        <section class="rounded-lg md:rounded-xl border border-slate-200 bg-white p-2 md:p-3 lg:p-4 shadow-sm">
            <h3 class="text-xs md:text-sm font-bold uppercase tracking-wider text-slate-600">History</h3>
            <div class="mt-2 md:mt-3 space-y-1.5 md:space-y-2 text-xs md:text-sm text-slate-700 max-h-[200px] overflow-y-auto">
                <?php if (!empty($history)): ?>
                    <?php foreach ($history as $h): ?>
                        <div class="rounded-lg border border-slate-200 bg-slate-50/60 p-1.5 md:p-2">
                            <div class="text-xs md:text-sm font-medium"><?= htmlspecialchars($h['logDesc'] ?? '') ?></div>
                            <div class="mt-0.5 text-[10px] md:text-xs text-slate-500"><?= !empty($h['logDate']) ? date('M d, g:i A', strtotime($h['logDate'])) : '—' ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-xs text-slate-500">No history.</div>
                <?php endif; ?>
            </div>
        </section>
        </main>

        <!-- Right: sidebar -->
        <aside class="space-y-3 md:space-y-4">
            <!-- Attachments -->
            <section class="rounded-lg md:rounded-xl border border-slate-200 bg-white p-2 md:p-3 lg:p-4 shadow-sm">
                <div class="flex items-center justify-between mb-2 md:mb-3">
                    <h3 class="text-xs md:text-sm font-bold uppercase tracking-wider text-slate-600">Attachments</h3>
                    <span class="text-xs text-slate-400 font-medium"><?= count($attachments ?? []) ?></span>
                </div>

                <div class="space-y-1.5 md:space-y-2">
                <?php if (!empty($attachments)): ?>
                    <?php foreach ($attachments as $a): ?>
                        <a href="index.php?controller=correspondence&action=download&attachment_id=<?= (int)($a['id'] ?? 0) ?>" class="flex items-center justify-between rounded-lg px-2 md:px-3 py-1 md:py-1.5 text-xs bg-slate-50 hover:bg-slate-100 transition border border-slate-100">
                            <span class="truncate text-slate-700 font-medium"><?= htmlspecialchars($a['file_name'] ?? 'file') ?></span>
                            <svg class="w-3 md:w-3.5 h-3 md:h-3.5 text-slate-400 shrink-0 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-xs text-slate-500 py-2">No attachments.</div>
                <?php endif; ?>
                </div>
            </section>

            <!-- Recipients -->
            <section class="rounded-lg md:rounded-xl border border-slate-200 bg-white p-2 md:p-3 lg:p-4 shadow-sm">

                <p class="text-xs font-bold uppercase tracking-wider text-slate-600">Recipients</p>

                <div class="mt-2 md:mt-3 space-y-3 md:space-y-4">

                    <!-- TO SECTION -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5 md:mb-2">
                            <span class="text-[10px] md:text-xs font-bold uppercase tracking-widest text-emerald-600">TO</span>
                            <span class="text-[10px] md:text-xs text-slate-400 font-medium"><?= count($recipientsTo ?? []) ?></span>
                        </div>

                        <div class="space-y-1">
                            <?php if (!empty($recipientsTo)): ?>
                                <?php foreach ($recipientsTo as $r): ?>
                                    <?php
                                        $statusValue = strtolower(trim($r['status'] ?? ''));
                                        $isReceived = $statusValue === 'received';
                                        $displayStatus = $isReceived ? 'Received' : 'Pending';
                                        $badgeClass = $isReceived
                                            ? 'bg-emerald-50 text-emerald-700'
                                            : 'bg-amber-50 text-amber-700';
                                    ?>
                                    <div class="flex items-center justify-between gap-2 rounded-lg bg-slate-50/50 border border-slate-100 px-2 md:px-3 py-1.5 md:py-2">
                                        <div class="min-w-0">
                                            <div class="truncate text-xs md:text-sm font-medium text-slate-900"><?= htmlspecialchars($r['name'] ?? '—') ?></div>
                                            <div class="truncate text-[10px] md:text-xs text-slate-500"><?= htmlspecialchars($r['office'] ?? '') ?></div>
                                        </div>
                                        <span class="shrink-0 text-[9px] md:text-[10px] font-bold px-1.5 md:px-2 py-0.5 rounded-full <?= $badgeClass ?>"><?= htmlspecialchars($displayStatus) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-xs text-slate-500">No recipients.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- CC SECTION -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5 md:mb-2">
                            <span class="text-[10px] md:text-xs font-bold uppercase tracking-widest text-blue-600">CC</span>
                            <span class="text-[10px] md:text-xs text-slate-400 font-medium"><?= count($recipientsCc ?? []) ?></span>
                        </div>

                        <div class="space-y-1">
                            <?php if (!empty($recipientsCc)): ?>
                                <?php foreach ($recipientsCc as $r): ?>
                                    <?php
                                        $statusValue = strtolower(trim($r['status'] ?? ''));
                                        $isReceived = $statusValue === 'received';
                                        $displayStatus = $isReceived ? 'Received' : 'Pending';
                                        $badgeClass = $isReceived
                                            ? 'bg-emerald-50 text-emerald-700'
                                            : 'bg-amber-50 text-amber-700';
                                    ?>
                                    <div class="flex items-center justify-between gap-2 rounded-lg bg-slate-50/50 border border-slate-100 px-2 md:px-3 py-1.5 md:py-2">
                                        <div class="min-w-0">
                                            <div class="truncate text-xs md:text-sm font-medium text-slate-900"><?= htmlspecialchars($r['name'] ?? '—') ?></div>
                                            <div class="truncate text-[10px] md:text-xs text-slate-500"><?= htmlspecialchars($r['office'] ?? '') ?></div>
                                        </div>
                                        <span class="shrink-0 text-[9px] md:text-[10px] font-bold px-1.5 md:px-2 py-0.5 rounded-full <?= $badgeClass ?>"><?= htmlspecialchars($displayStatus) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-xs text-slate-500">No CC recipients.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </section>

            <!-- Metadata -->
            <section class="rounded-lg md:rounded-xl border border-slate-200 bg-white p-2 md:p-3 lg:p-4 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-600">Metadata</p>
                <div class="mt-2 md:mt-3 space-y-1.5 md:space-y-2 text-xs md:text-sm text-slate-700">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-slate-500 font-medium">Sender:</span>
                        <span class="text-right text-slate-900"><?= htmlspecialchars($doc['sender_email'] ?? '—') ?></span>
                    </div>
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-slate-500 font-medium">Priority:</span>
                        <span class="text-right text-slate-900"><?= htmlspecialchars($doc['priority'] ?? '—') ?></span>
                    </div>
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-slate-500 font-medium">Due:</span>
                        <span class="text-right text-slate-900 font-mono text-xs"><?= $doc['due_date'] ? date('M d', strtotime($doc['due_date'])) : '—' ?></span>
                    </div>
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-slate-500 font-medium">Confidential:</span>
                        <span class="text-right text-slate-900"><?= !empty($doc['is_confidential']) ? 'Yes' : 'No' ?></span>
                    </div>
                </div>
            </section>

        <?php if ((int)($_SESSION['user_level'] ?? 3) <= 1): ?>
        <!-- Admin Controls -->
        <section class="rounded-lg md:rounded-xl border border-slate-200 bg-slate-50 p-2 md:p-3 lg:p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-600">Admin Controls</p>
            <p class="mt-2 md:mt-3 text-xs md:text-sm text-slate-700 leading-5">
                Close sets status to <strong>Done</strong>; Open sets to <strong>Suspended</strong>.
            </p>
            <form method="POST" action="index.php?controller=correspondence&action=toggleOpenClose" class="mt-2 md:mt-3 flex items-center gap-1.5 md:gap-2">
                <input type="hidden" name="document_id" value="<?= (int)($doc['id'] ?? 0) ?>">
                <button type="submit" name="toggle_action" value="close" class="flex-1 rounded-lg border border-emerald-300 bg-emerald-100 px-2 md:px-3 py-1 md:py-1.5 text-xs md:text-sm font-bold text-emerald-700 hover:bg-emerald-200 transition">
                    Close
                </button>
                <button type="submit" name="toggle_action" value="open" class="flex-1 rounded-lg border border-rose-300 bg-rose-100 px-2 md:px-3 py-1 md:py-1.5 text-xs md:text-sm font-bold text-rose-700 hover:bg-rose-200 transition">
                    Open
                </button>
            </form>
        </section>
        <?php endif; ?>
        </aside>
    </div>
</div>

