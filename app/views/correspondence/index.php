<?php require __DIR__ . '/../partials/icons.php'; ?>

<div class="min-h-screen theme-palette">
    <div class="max-w-[1400px] mx-auto px-3 py-6 text-xs sm:text-xs md:text-sm correspondence-ui">
        <?php
            $currentUserLevel = (int)($_SESSION['user_level'] ?? 3);
            $isSuperAdmin = $currentUserLevel === 0;
            $canCreateCorrespondence = !empty($canCreateCorrespondence) || in_array($currentUserLevel, [0, 1, 2, 4, 5, 6], true);
            $canEditCorrespondence = !empty($canEditCorrespondence) || in_array($currentUserLevel, [0, 1, 2, 4, 5, 6], true);
            $canDeleteCorrespondence = !empty($canDeleteCorrespondence) || in_array($currentUserLevel, [0, 1, 4, 5], true);
            $canHardDeleteCorrespondence = !empty($canHardDeleteCorrespondence) || $isSuperAdmin;
            // Only admin (1), PM/DPM and super-admin (0) may finalize drafts
            $canFinalize = in_array($currentUserLevel, [0,1,4,5], true);
            $selectableUsers = $users ?? [];

            usort($selectableUsers, function ($left, $right) {
                $leftDepartment = strtolower(trim($left['department'] ?? ''));
                $rightDepartment = strtolower(trim($right['department'] ?? ''));
                $leftName = strtolower(trim(($left['firstName'] ?? '') . ' ' . ($left['lastName'] ?? '')));
                $rightName = strtolower(trim(($right['firstName'] ?? '') . ' ' . ($right['lastName'] ?? '')));

                return $leftDepartment <=> $rightDepartment ?: $leftName <=> $rightName;
            });
        ?>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="mb-6 p-4 rounded-xl <?= $_SESSION['msg_type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                <?= htmlspecialchars($_SESSION['message']) ?>
            </div>
            <?php unset($_SESSION['message'], $_SESSION['msg_type']); ?>
        <?php endif; ?>

        <!-- ====================== NEW CIRCULATION CTA (moved to separate page) ====================== -->
        <?php if ($canCreateCorrespondence): ?>
            <div class="mb-6 overflow-hidden rounded-lg border border-slate-300 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase text-blue-600">New Circulation</p>
                        <h2 class="text-base font-semibold text-slate-900 mt-1">Create a new circulation</h2>
                        <p class="mt-1 text-xs text-slate-500">Open the dedicated form to create a circulation entry.</p>
                    </div>
                    <div>
                                <a href="index.php?controller=correspondence&action=newCirculation" class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">New Circulation</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="mb-6 rounded-lg border border-slate-300 bg-white p-4 shadow-sm">
                <div class="rounded-lg border border-slate-300 bg-slate-50 px-3 py-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">View Only</p>
                    <h2 class="mt-1 text-lg font-semibold text-slate-900">New Circulation is disabled for your account</h2>
                    <p class="mt-2 max-w-3xl text-xs leading-6 text-slate-600">
                        You can review correspondence history and repository records, but creating, editing, and deleting circulation entries are restricted by your role.
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <!-- ====================== DOCUMENT REPOSITORY ====================== -->
        <?php require __DIR__ . '/_shared.php'; ?>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-3 py-3 border-b border-gray-200 bg-white">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase text-blue-600">Repository</p>
                        <h2 class="text-xs sm:text-sm md:text-base font-semibold text-gray-900 mt-0.5">Document Repository</h2>
                    </div>
                    <span class="inline-flex w-fit items-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-medium text-gray-600">
                        <?= count($documents) ?> document(s)
                    </span>
                </div>

                <div class="mt-4 rounded-md border border-slate-300 bg-slate-50/80 p-3 shadow-sm shadow-slate-100">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-[200px_200px_minmax(0,1fr)] gap-3 flex-1">
                            <div>
                                <label for="priorityFilter" class="mb-1 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Priority</label>
                                <select id="priorityFilter" class="h-9 w-full rounded-md border border-slate-300 bg-white px-2 text-xs text-slate-700 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-100">
                                    <option value="">All priorities</option>
                                    <option value="Low">Low</option>
                                    <option value="Medium">Medium</option>
                                    <option value="High">High</option>
                                    <option value="Urgent">Urgent</option>
                                </select>
                            </div>
                            <div>
                                <label for="statusFilter" class="mb-1 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Status</label>
                                <select id="statusFilter" class="h-9 w-full rounded-md border border-slate-300 bg-white px-2 text-xs text-slate-700 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-100">
                                <option value="">All status</option>
                                <option value="Suspended">Suspended</option>
                                <option value="Inprogress">Inprogress</option>
                                <option value="Done">Done</option>
                                <option value="draft">draft</option>

                                </select>
                            </div>
                            <div class="flex items-end">
                                <p class="text-xs leading-5 text-slate-500">
                                    Filters apply instantly. Keep the table clean by narrowing to priority, status, or removed items.
                                </p>
                            </div>
                        </div>
                        <div class="xl:min-w-[220px]">
                            <label class="inline-flex w-full items-center justify-between gap-3 rounded-full border border-slate-300 bg-white px-4 py-2.5 shadow-sm shadow-slate-100 transition hover:border-slate-400">
                                <span class="inline-flex items-center gap-2 min-w-0">
                                    <span class="h-2.5 w-2.5 rounded-full <?= !empty($showRemovedItems) ? 'bg-amber-500' : 'bg-slate-400' ?>"></span>
                                    <span class="truncate text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Show removed</span>
                                </span>
                                <input type="checkbox" id="showRemovedItems" <?= !empty($showRemovedItems) ? 'checked' : '' ?> class="h-4 w-4 rounded border-slate-400 text-blue-600 focus:ring-blue-500">
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-5">
                <!-- Top bar: title, search, filters, compact toggle -->
                <div class="mb-4 flex flex-col gap-3 sm:flex-row lg:hidden sm:block  sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase text-blue-600">Repository</p>
                        <h2 class="text-sm sm:text-base font-semibold text-gray-900">Document Repository</h2>
                    </div>

                    <div class="flex items-center gap-3 flex-wrap">
                        <div class="relative">
                            <input id="repoSearch" type="search" placeholder="Search documents" class="h-10 w-64 rounded-xl border border-slate-200 px-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>

                        <div>
                            <select id="statusFilter" class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none">
                                <option value="">All status</option>
                                <option value="Suspended">Suspended</option>
                                <option value="Inprogress">Inprogress</option>
                                <option value="Done">Done</option>
                                <option value="draft">draft</option>

                            </select>
                        </div>

                        <div class="hidden sm:block">
                            <!-- reuse existing priorityFilter if present, otherwise show small select -->
                            <?php if (!empty($documents)): ?>
                                <select id="priorityFilterMobile" class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none">
                                    <option value="">All priorities</option>
                                    <option value="Low">Low</option>
                                    <option value="Medium">Medium</option>
                                    <option value="High">High</option>
                                    <option value="Urgent">Urgent</option>
                                </select>
                            <?php endif; ?>
                        </div>

                        <!-- <label class="inline-flex items-center gap-2 text-sm">
                            <input id="compactViewToggle" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-blue-600">
                            <span class="text-sm text-slate-600">Compact View</span>
                        </label> -->
                    </div>
                </div>

                <!-- Desktop: table view -->
                <div class="hidden md:block overflow-x-auto">
                    <table id="documentTable" class="w-full text-sm compact">
                    <thead class="bg-gray-50 border-y border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Tracking ID</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Document Title</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Sender</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500">Received</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Circulated</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Due Date</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500">Priority</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500 w-32">Actions</th>
                            <th class="hidden">State</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($documents as $doc): ?>
                        <?php
                            $receivedCount = (int)($doc['received_count'] ?? 0);
                            $totalRecipients = (int)($doc['total_recipients'] ?? 0);
                            $doneCount = (int)($doc['done_count'] ?? 0);
                            // Status is driven by Open/Close which marks circulations as 'Done'
                            $status =  $doc['status'];
                            $isDeleted = !empty($doc['is_deleted']);
                            $isEdited = !empty($doc['is_edited']);
                            $canManage = false;
                            if (!empty($doc['created_by']) && (int)$doc['created_by'] === (int)($_SESSION['id'] ?? 0) && !$isDeleted && !empty($doc['created_at'])) {
                                $deadline = strtotime($doc['created_at'] . ' +7 days');
                                $canManage = $deadline !== false && time() <= $deadline;
                            }
                            $manageUntil = !empty($doc['created_at']) ? date('M d, Y g:i A', strtotime($doc['created_at'] . ' +7 days')) : null;
                            $st = strtolower(trim((string)($status ?? 'inprogress')));
                   // STATUS COLORS = document state
                    switch ($st) {
                        case 'done':
                        case 'completed':
                            $statusClass = 'border border-emerald-200 bg-emerald-50 text-emerald-700';
                            break;

                        case 'suspended':
                            $statusClass = 'border border-rose-200 bg-rose-50 text-rose-700';
                            break;

                        case 'draft':
                            $statusClass = 'border border-sky-200 bg-sky-50 text-sky-700';
                            break;

                        case 'pending':
                        case 'in progress':
                        case 'in_progress':
                        default:
                            $statusClass = 'border border-slate-200 bg-slate-50 text-slate-700';
                            break;
                    }

                    // PRIORITY COLORS = urgency
                    $priority = strtolower(trim((string)($doc['priority'] ?? '')));

                    $priorityClass = match ($priority) {
                        'urgent' => 'border border-red-200 bg-red-50 text-red-700',
                        'high'   => 'border border-amber-200 bg-amber-50 text-amber-700',
                        'medium' => 'border border-violet-200 bg-violet-50 text-violet-700',
                        'low'    => 'border border-zinc-200 bg-zinc-50 text-zinc-700',
                        default  => 'border border-slate-200 bg-slate-50 text-slate-700',
                    };
                        ?>
                        <tr onclick="viewDocument(<?= $doc['id'] ?>)"
                            class="<?= $isDeleted ? 'bg-slate-50 text-slate-500' : '' ?> hover:bg-slate-50 cursor-pointer transition-colors">
                            <td class="px-4 py-3 font-mono <?= $isDeleted ? 'text-slate-400' : 'text-blue-700' ?> whitespace-nowrap"><?= htmlspecialchars($doc['tracking_id']) ?></td>
                            <td class="px-4 py-3 font-medium min-w-[220px] <?= $isDeleted ? 'text-slate-500' : 'text-gray-900' ?>">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="<?= $isDeleted ? 'text-slate-400' : '' ?>"><?= htmlspecialchars($doc['title']) ?></span>
                                    <?php if ($isEdited): ?>
                                        <span class="inline-flex items-center rounded-full border border-sky-200 bg-sky-50 px-2 py-0.5 text-[11px] font-semibold text-sky-700">Edited</span>
                                    <?php endif; ?>
                                    <?php if ($isDeleted): ?>
                                        <span class="inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-2 py-0.5 text-[11px] font-semibold text-rose-700">Removed</span>
                                    <?php endif; ?>
                                </div>
                             
                            </td>
                            <td class="px-4 py-3 <?= $isDeleted ? 'text-slate-400' : 'text-slate-600' ?> min-w-[180px]"><?= htmlspecialchars($doc['sender_email']) ?></td>
                            <td class="px-4 py-3 text-center font-semibold <?= $isDeleted ? 'text-slate-400' : 'text-emerald-700' ?> whitespace-nowrap">
                                <?= $receivedCount ?>/<?= $totalRecipients ?>
                            </td>
                            <td class="px-4 py-3 text-center" data-search="<?= htmlspecialchars($status) ?>">
                                <span class="inline-flex items-center justify-center rounded-md border px-2.5 py-1 text-xs font-semibold <?= $statusClass ?>">
                                    <?= htmlspecialchars($status) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 <?= $isDeleted ? 'text-slate-400' : 'text-slate-500' ?> whitespace-nowrap"><?= date('M d, Y', strtotime($doc['created_at'])) ?></td>
                            <td class="px-4 py-3 <?= $isDeleted ? 'text-slate-400' : 'text-slate-500' ?> whitespace-nowrap"><?= $doc['due_date'] ? date('M d, Y', strtotime($doc['due_date'])) : '—' ?></td>
                            <td class="px-4 py-3 text-center" data-search="<?= htmlspecialchars($doc['priority']) ?>">
                                <span class="inline-flex items-center justify-center rounded-md border px-2.5 py-1 text-xs font-semibold <?= $priorityClass ?>">
                                    <?= htmlspecialchars($doc['priority']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="inline-flex flex-wrap items-center justify-center gap-2" onclick="event.stopPropagation()">
                                     <button type="button"  onclick='openEditDocument(<?= $doc["id"] ?>, <?= json_encode($doc["title"], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                        <?= (!$canEditCorrespondence || $isDeleted) ? 'disabled' : '' ?>
                                        class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-slate-300  px-3 text-xs font-semibold text-slate-700 hover:border-amber-200 hover:bg-amber-50 hover:text-amber-700 transition"
                                        title="Edit document">
                                       Edit
                                    </button>
                                    <?php if (!empty($doc['is_draft']) && $canEditCorrespondence && !$isDeleted && $canFinalize): ?>
                                        <button type="button" onclick='openFinalizeDraft(<?= $doc["id"] ?>)'
                                            class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-sky-200 bg-sky-50 px-3 text-xs font-semibold text-sky-700 hover:bg-sky-100 transition"
                                            title="Finalize draft">
                                           Finalize
                                        </button>
                                    <?php endif; ?>
                                    <button type="button" onclick='openDeleteConfirm(<?= $doc["id"] ?>, <?= json_encode($doc["title"], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                        <?= (!$canDeleteCorrespondence || $isDeleted) ? 'disabled' : '' ?>
                                        class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50 px-3 text-xs font-semibold text-rose-700 hover:bg-rose-100 transition "
                                        title="Delete document">
                                        Delete
                                    </button>
                                    <?php if ($canHardDeleteCorrespondence): ?>
                                        <button type="button" onclick='openHardDeleteConfirm(<?= $doc["id"] ?>, <?= json_encode($doc["title"], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                            class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-rose-300 bg-rose-100 px-3 text-xs font-semibold text-rose-800 hover:bg-rose-200 transition"
                                            title="Permanently remove document">
                                            Hard Delete
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="hidden"><?= $isDeleted ? 'Removed' : 'Active' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    </table>
                </div>

                <!-- Mobile: card-based list -->
                <div id="mobileCards" class="md:hidden space-y-3">
                    <?php foreach ($documents as $doc):
                        $receivedCount = (int)($doc['received_count'] ?? 0);
                        $totalRecipients = (int)($doc['total_recipients'] ?? 0);
                        $status =  $doc['status'];
                        $isDeleted = !empty($doc['is_deleted']);
                        $cardState = $isDeleted ? 'Removed' : 'Active';
                        $st = strtolower(trim((string)($status ?? 'inprogress')));
                        if ($st === 'done' || $st === 'completed') {
                            $statusClass = 'bg-emerald-50 text-emerald-700';
                        } elseif ($st === 'suspended') {
                            $statusClass = 'bg-rose-50 text-rose-700';
                        } elseif ($st === 'draft') {
                            $statusClass = 'bg-sky-50 text-sky-700';
                        } else {
                            $statusClass = 'bg-slate-50 text-slate-700';
                        }
                        $priorityClass = match ($doc['priority']) {
                            'Urgent' => 'bg-red-50 text-red-700',
                            'High' => 'bg-orange-50 text-orange-700',
                            'Medium' => 'bg-blue-50 text-blue-700',
                            default => 'bg-gray-50 text-gray-700',
                        };
                    ?>
                    <article data-state="<?= $cardState ?>" onclick="viewDocument(<?= $doc['id'] ?>)" class="group cursor-pointer rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs text-blue-700"><?= htmlspecialchars($doc['tracking_id']) ?></span>
                                    <h3 class="truncate text-sm font-semibold text-slate-900"><?= htmlspecialchars($doc['title']) ?></h3>
                                </div>
                                <p class="mt-1 text-xs text-slate-500 truncate"><?= htmlspecialchars($doc['sender_email']) ?></p>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?= $statusClass ?>"><?= htmlspecialchars($status) ?></span>
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold <?= $priorityClass ?>"><?= htmlspecialchars($doc['priority'] ?? '—') ?></span>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center justify-between">
                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                <span>Due: <?= $doc['due_date'] ? date('M d, Y', strtotime($doc['due_date'])) : '—' ?></span>
                            </div>
                            <div class="inline-flex items-center gap-2" onclick="event.stopPropagation()">
                                <button type="button" onclick='openEditDocument(<?= $doc["id"] ?>, <?= json_encode($doc["title"], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                    <?= (!$canEditCorrespondence || $isDeleted) ? 'disabled' : '' ?>
                                    class="h-8 w-8 inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50">
                                    ✎
                                </button>
                                <button type="button" onclick='openDeleteConfirm(<?= $doc["id"] ?>, <?= json_encode($doc["title"], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                    <?= (!$canDeleteCorrespondence || $isDeleted) ? 'disabled' : '' ?>
                                    class="h-8 w-8 inline-flex items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100">
                                    🗑
                                </button>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>


<!-- (shared modals moved to _shared.php) -->

<style>
    .modal-panel {
        transform: translateY(18px) scale(0.99);
        opacity: 0;
        transition: transform 180ms ease, opacity 180ms ease;
    }

    #documentModal:not(.hidden) .modal-panel {
        transform: translateY(0) scale(1);
        opacity: 1;
    }

    #editDocumentModal:not(.hidden) .modal-panel,
    #confirmModal:not(.hidden) .modal-panel {
        transform: translateY(0) scale(1);
        opacity: 1;
    }

    /* DataTables control styling removed: handled by shared Tailwind DataTables stylesheet */

    #documentTable tbody tr {
        transition: transform 140ms ease, background-color 140ms ease, box-shadow 140ms ease;
    }

    #documentTable tbody tr:hover {
        transform: translateY(-1px);
    }
    /* Correspondence UI overrides: minimalist, muted, premium look */
    .correspondence-ui {
        --cb-bg: #f8faf9;
        --cb-surface: #ffffff;
        --cb-border: #e6e9ee;
        --cb-muted: #6b7280;
        --cb-accent: #2563eb;
        color: #0f172a;
        background-color: var(--cb-bg);
    }

    /* Card breathing room (reduced for denser layout) */
    .correspondence-ui .rounded-3xl { border-radius: 12px; }
    .correspondence-ui section.rounded-3xl { padding: 0.6rem 0.75rem !important; background: var(--cb-surface); border: none !important; box-shadow: 0 1px 6px rgba(2,6,23,0.03); }
    .correspondence-ui .space-y-6 > * + * { margin-top: 0.75rem !important; }
    .correspondence-ui .space-y-4 > * + * { margin-top: 0.6rem !important; }

    /* Borders & surfaces: visually quieter but still accessible */
  

    /* Inputs: emphasize focus, keep normal state muted */
    .correspondence-ui input,
    .correspondence-ui select,
    .correspondence-ui textarea,
    .correspondence-ui [contenteditable] {
        background: var(--cb-surface) !important;
        border: 1px solid rgba(15,23,42,0.06) !important;
        box-shadow: none !important;
        color: #0f172a !important;
        padding: 0.5rem 0.75rem !important;
        min-height: 2.2rem !important;
        transition: box-shadow 160ms ease, border-color 160ms ease;
    }
    .correspondence-ui input:focus,
    .correspondence-ui select:focus,
    .correspondence-ui textarea:focus,
    .correspondence-ui [contenteditable]:focus {
        border-color: var(--cb-accent) !important;
        box-shadow: 0 6px 18px rgba(37,99,235,0.06) !important;
        outline: none !important;
    }

    /* User cards & lists: reduce border noise, add subtle hover / selected states */
    .correspondence-ui [data-user-card] { padding: 0.5rem 0.75rem !important; border-radius: 10px; background: transparent; box-shadow: none; border: none !important; }
    .correspondence-ui [data-user-card]:hover { background: rgba(2,6,23,0.02); box-shadow: 0 6px 18px rgba(2,6,23,0.03); }
    .correspondence-ui label.group { border: none !important; }

    /* Controls and buttons: keep subtle but clear */
    .correspondence-ui .inline-flex.items-center.rounded-lg,
    .correspondence-ui .inline-flex.h-8.items-center { background: transparent !important; border: 1px solid transparent !important; }
    .correspondence-ui button.bg-blue-600,
    .correspondence-ui .bg-blue-600 { background: var(--cb-accent) !important; border-color: transparent !important; box-shadow: 0 6px 24px rgba(37,99,235,0.06); }

    .correspondence-ui button[disabled],
    .correspondence-ui .disabled\:opacity-40 { opacity: 0.55 !important; }

    /* Tame background utility classes that add visual noise */
    .correspondence-ui .bg-slate-50,
    .correspondence-ui .bg-slate-50\/, .correspondence-ui .bg-slate-50\/70 { background-color: transparent !important; }

    /* Modal refinement */
    .correspondence-ui .modal-panel { border: 0; box-shadow: 0 8px 32px rgba(2,6,23,0.04); }
    .correspondence-ui .modal-panel .px-5, .correspondence-ui .modal-panel .px-6 { padding-left: 1.25rem !important; padding-right: 1.25rem !important; }

    /* Cleaner table with more vertical rhythm: compact CRM-style */
    .correspondence-ui table#documentTable { border-collapse: separate; border-spacing: 0 6px; font-size: 0.82rem; }
    .correspondence-ui #documentTable thead th { background: transparent; color: var(--cb-muted); border-bottom: none; padding: 0.35rem 0.5rem; font-size: 0.66rem; letter-spacing: 0.04em; }
    .correspondence-ui #documentTable tbody tr { background: transparent; box-shadow: none; }
    .correspondence-ui #documentTable td, .correspondence-ui #documentTable th { padding: 0.45rem 0.5rem; vertical-align: middle; }

    /* Default compact tweaks (applied by `compact` class) */
    .correspondence-ui table#documentTable.compact { border-spacing: 0 6px; }
    .correspondence-ui table#documentTable.compact td, .correspondence-ui table#documentTable.compact th { padding: 0.25rem 0.35rem; }
    .correspondence-ui table#documentTable.compact tbody tr { border-radius: 8px; }

    /* Smaller, sleeker badges */
    .correspondence-ui .inline-flex.items-center.justify-center.rounded-md { background: rgba(15,23,42,0.03); border: none; padding: 0.18rem 0.5rem; font-size: 0.72rem; }

    /* Action buttons: leaner */
    .correspondence-ui table#documentTable .inline-flex.h-8 { height: 34px; padding: 0 .6rem; font-size: 0.78rem; }
    .correspondence-ui table#documentTable button.h-8 { height: 34px; }

    /* Row hover — subtle elevation */
    #documentTable tbody tr:hover { background: rgba(15,23,42,0.02); }

    /* Subtle badges */
    .correspondence-ui .inline-flex.items-center.justify-center.rounded-md { background: rgba(15,23,42,0.03); border: none; }

    /* Less visual noise for headings */
    .correspondence-ui p.text-xs { color: var(--cb-muted); letter-spacing: 0.02em; }

    /* Compact/smaller-screen adjustments to reduce vertical scrolling */
    @media (max-width: 1024px) {
        .correspondence-ui section.rounded-3xl { padding: 0.5rem 0.6rem !important; }
        .correspondence-ui .space-y-6 > * + * { margin-top: 0.5rem !important; }
        .correspondence-ui .space-y-4 > * + * { margin-top: 0.45rem !important; }
        .correspondence-ui input,
        .correspondence-ui select,
        .correspondence-ui textarea,
        .correspondence-ui [contenteditable] { padding: 0.45rem 0.6rem !important; min-height: 2rem !important; }
        .correspondence-ui [data-user-card] { padding: 0.45rem 0.6rem !important; }
        .correspondence-ui table#documentTable { border-spacing: 0 6px; }
        .correspondence-ui #documentTable td, .correspondence-ui #documentTable th { padding: 0.45rem 0.5rem; }

        /* Compact table reduces padding for dense mode */
        .correspondence-ui table#documentTable.compact td, .correspondence-ui table#documentTable.compact th { padding: 0.25rem 0.35rem; }

        /* Mobile card styles */
        #mobileCards article { border-radius: 12px; }
        #mobileCards .group:hover { transform: translateY(-2px); }

        /* Sticky action bar so users can submit without excessive scrolling */
        #formActions {
            position: sticky;
            bottom: 8px;
            z-index: 60;
            padding-top: 0.45rem;
            backdrop-filter: blur(4px);
            background: linear-gradient(180deg, rgba(248,250,249,0), rgba(248,250,249,0.85));
        }
        #formActions button { min-width: 100px; }
    }
</style>

<script>
// DataTables
$(document).ready(function() {
    const documentTable = $('#documentTable').DataTable({
        pageLength: 25,
        order: [[5, 'desc']],
        columnDefs: [
            { targets: 9, visible: false, searchable: true }
        ],
        dom: '<"flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4"lf>rt<"flex flex-col md:flex-row md:items-center md:justify-between gap-3 mt-4"ip>',
        language: {
            search: '',
            searchPlaceholder: 'Search documents'
        }
    });

    const showRemovedItems = <?= !empty($showRemovedItems) ? 'true' : 'false' ?>;
    $('#showRemovedItems').prop('checked', showRemovedItems);
    documentTable.column(9).search(showRemovedItems ? '' : '^Active$', true, false).draw();

    $('#priorityFilter').on('change', function() {
        documentTable.column(7).search(this.value ? '^' + this.value + '$' : '', true, false).draw();
    });

    $('#statusFilter').on('change', function() {
        documentTable.column(4).search(this.value ? '^' + this.value + '$' : '', true, false).draw();
    });

    $('#showRemovedItems').on('change', function() {
        const enabled = this.checked;
        documentTable.column(9).search(enabled ? '' : '^Active$', true, false).draw();

        // also update mobile card list visibility
        if (document.getElementById('mobileCards')) {
            // show removed when enabled, hide removed when not
            $('#mobileCards article[data-state="Removed"]').toggle(enabled);
        }

        fetch('index.php?controller=correspondence&action=setRemovedItemsPreference', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                enabled: enabled ? '1' : '0'
            })
        }).catch(() => {});
    });

    // Topbar search binds to DataTable search
    $('#repoSearch').on('input', function() {
        documentTable.search(this.value).draw();
    });

    // Compact view toggle reduces padding for denser rows
    $('#compactViewToggle').on('change', function() {
        if (this.checked) {
            $('#documentTable').addClass('compact');
            $('#documentTable_wrapper').addClass('compact-mode');
            documentTable.draw(false);
        } else {
            $('#documentTable').removeClass('compact');
            $('#documentTable_wrapper').removeClass('compact-mode');
            documentTable.draw(false);
        }
    });

    // Mirror mobile priority select to main priorityFilter if present
    $('#priorityFilterMobile').on('change', function() {
        $('#priorityFilter').val(this.value).trigger('change');
    });
});
// include form JS only when the form exists on the page
if (document.getElementById('circulationForm')) {
    <?php include __DIR__ . '/_form_js.php'; ?>
}

// Collapsible panels: toggle handlers
const recipientsToggle = document.getElementById('recipients-toggle');
const recipientsPanel = document.getElementById('recipients-panel');
const ccToggle = document.getElementById('cc-toggle');
const ccPanel = document.getElementById('cc-panel');

function togglePanel(toggleBtn, panel) {
    if (!toggleBtn || !panel) return;
    const isHidden = panel.classList.toggle('hidden');
    toggleBtn.setAttribute('aria-expanded', String(!isHidden));
    const svg = toggleBtn.querySelector('svg');
    if (svg) svg.style.transform = isHidden ? 'rotate(0deg)' : 'rotate(180deg)';
}

if (recipientsToggle && recipientsPanel) {
    recipientsToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        togglePanel(recipientsToggle, recipientsPanel);
    });
}

if (ccToggle && ccPanel) {
    ccToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        togglePanel(ccToggle, ccPanel);
    });
}

// Drawer for recipient/CC picker
const recipientDrawer = document.getElementById('recipientDrawer');
const recipientDrawerOverlay = document.getElementById('recipientDrawerOverlay');
const drawerSearch = document.getElementById('drawer-search');
let drawerMode = 'recipients';

function openRecipientDrawer(mode) {
    drawerMode = mode === 'cc' ? 'cc' : 'recipients';
    document.getElementById('drawerTitleSmall').textContent = mode === 'cc' ? 'Add CC' : 'Add Recipients';
    document.getElementById('drawerTitle').textContent = mode === 'cc' ? 'Select CC recipients' : 'Select recipients';
    // pre-check boxes based on existing hidden inputs
    const existing = Array.from(document.querySelectorAll(`input[name="${mode}[]"]`)).map(i => String(i.value));
    document.querySelectorAll('[data-drawer-id]').forEach(cb => {
        cb.checked = existing.includes(String(cb.dataset.drawerId));
    });
    recipientDrawer.classList.remove('hidden');
    recipientDrawerOverlay.classList.remove('hidden');
    // slide in
    requestAnimationFrame(() => {
        recipientDrawer.classList.remove('translate-x-full');
        if (drawerSearch) drawerSearch.focus();
    });
}

function closeRecipientDrawer() {
    recipientDrawer.classList.add('translate-x-full');
    recipientDrawerOverlay.classList.add('hidden');
    setTimeout(() => recipientDrawer.classList.add('hidden'), 250);
}

if (drawerSearch) {
    drawerSearch.addEventListener('input', function() {
        const term = this.value.toLowerCase().trim();
        document.querySelectorAll('#drawerList [data-drawer-filter]').forEach(el => {
            const hay = el.getAttribute('data-drawer-filter') || '';
            el.classList.toggle('hidden', term !== '' && !hay.includes(term));
        });
    });
}

function applyRecipientDrawer() {
    // gather checked
    const checked = Array.from(document.querySelectorAll('#drawerList input[type="checkbox"]:checked'));
    // remove existing inputs for current mode
    document.querySelectorAll(`input[name="${drawerMode}[]"]`).forEach(n => n.remove());

    const recipientsContainer = document.getElementById('recipients-chips');
    const ccContainer = document.getElementById('cc-chips');
    // only clear the container for the active drawer mode
    if (drawerMode === 'recipients') {
        recipientsContainer.innerHTML = '';
    } else {
        ccContainer.innerHTML = '';
    }

    checked.forEach(cb => {
        const id = cb.dataset.drawerId;
        const name = cb.dataset.drawerName || cb.dataset.drawerEmail || id;

        // create a hidden checkbox input (so it participates in selection logic)
        const input = document.createElement('input');
        input.type = 'checkbox';
        input.name = `${drawerMode}[]`;
        input.value = id;
        input.checked = true;
        input.style.display = 'none';
        input.setAttribute('data-selection-section', drawerMode);
        input.setAttribute('data-user-id', id);
        document.getElementById('circulationForm').appendChild(input);

        // create chip
        const chip = document.createElement('span');
        chip.className = 'inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-700';
        chip.textContent = name;
        const container = drawerMode === 'cc' ? ccContainer : recipientsContainer;
        // remove placeholder if exists
        const placeholder = container.querySelector('.text-sm.text-slate-500');
        if (placeholder) placeholder.remove();
        container.appendChild(chip);
    });

    // if nothing selected, restore placeholder
    if (!recipientsContainer.children.length) recipientsContainer.innerHTML = '<span id="recipients-placeholder" class="text-sm text-slate-500">No recipients selected</span>';
    if (!ccContainer.children.length) ccContainer.innerHTML = '<span id="cc-placeholder" class="text-sm text-slate-500">No CC selected</span>';

    closeRecipientDrawer();
    // refresh UI counts and chips
    syncSelectionState();
    updateRecipientBadge();
    updateSummaryPanel();
}

function updateRecipientBadge() {
    const badge = document.getElementById('recipients-count-badge');
    if (!badge) return;
    const count = document.querySelectorAll('input[name="recipients[]"]').length;
    if (count > 0) {
        badge.textContent = String(count);
        badge.classList.remove('hidden');
    } else {
        badge.classList.add('hidden');
    }
}

// Close drawer on Escape when open
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (recipientDrawer && !recipientDrawer.classList.contains('hidden')) {
            closeRecipientDrawer();
        }
    }
});

// initialize badge state on page load
updateRecipientBadge();

// update summary counts (recipients, cc, attachments, due date)
function updateSummaryPanel() {
    const rCount = document.querySelectorAll('input[name="recipients[]"]').length;
    const cCount = document.querySelectorAll('input[name="cc[]"]').length;
    const aCount = (document.querySelector('input[name="attachments[]"]')?.files || []).length;
    const due = document.querySelector('input[name="due_date"]')?.value || '—';

    const rEl = document.getElementById('summary-recipient-count');
    const cEl = document.getElementById('summary-cc-count');
    const aEl = document.getElementById('summary-attachment-count');
    const dEl = document.getElementById('summary-due-date');

    if (rEl) rEl.textContent = String(rCount);
    if (cEl) cEl.textContent = String(cCount);
    if (aEl) aEl.textContent = `${aCount} file(s)`;
    if (dEl) dEl.textContent = due;
}

// call when selection changes or attachments/due date change
document.addEventListener('change', function(e) {
    if (['recipients[]','cc[]','attachments[]','due_date'].some(name => (e.target.name || '') === name)) {
        updateSummaryPanel();
        updateRecipientBadge();
    }
});

// initialize summary
updateSummaryPanel();

const circulationForm = document.getElementById('circulationForm');
if (circulationForm) {
    circulationForm.addEventListener('submit', function() {
        const desc = document.getElementById('description');
        if (desc) {
            document.getElementById('description-hidden').value = desc.innerHTML;
        }
    });
}

function resetForm() {
    if (!circulationForm) return;

    circulationForm.reset();
    const desc = document.getElementById('description');
    if (desc) {
        desc.innerHTML = '';
    }

    document.querySelectorAll('[data-selection-filter]').forEach(input => {
        input.value = '';
    });

    document.querySelectorAll('[data-user-card]').forEach(card => {
        card.classList.remove('hidden');
    });

    syncSelectionState();
    updateAttachmentFeedback();
}

function saveDraftFromFinalize() {
    const form = document.getElementById('circulationForm');
    if (!form) return;
    // remove finalize marker so update() treats this as a save-draft
    const fin = document.getElementById('finalizeInput'); if (fin) fin.remove();
    // ensure save flag exists
    let save = document.getElementById('saveDraftInput');
    if (!save) { save = document.createElement('input'); save.type = 'hidden'; save.name = 'save_draft'; save.id = 'saveDraftInput'; save.value = '1'; form.appendChild(save); }
    form.submit();
}

function updateAttachmentFeedback() {
    const input = document.querySelector('input[name="attachments[]"]');
    const feedback = document.getElementById('attachment-feedback');
    if (!input || !feedback) return true;

    const files = Array.from(input.files || []);
    const maxFiles = parseInt(input.dataset.maxFiles || '4', 10);
    const maxTotal = parseInt(input.dataset.maxTotal || '41943040', 10);
    const totalSize = files.reduce((sum, file) => sum + (file.size || 0), 0);

    feedback.classList.remove('text-rose-600', 'text-amber-600', 'text-emerald-600', 'text-slate-500');

    if (files.length === 0) {
        feedback.textContent = 'No files selected.';
        feedback.classList.add('text-slate-500');
        return true;
    }

    if (files.length > maxFiles) {
        feedback.textContent = `Too many files selected. Max ${maxFiles} files allowed.`;
        feedback.classList.add('text-rose-600');
        return false;
    }

    if (totalSize > maxTotal) {
        feedback.textContent = 'Selected files exceed the 40MB total limit.';
        feedback.classList.add('text-rose-600');
        return false;
    }

    feedback.textContent = `${files.length} file(s) selected, ${(totalSize / (1024 * 1024)).toFixed(1)} MB total.`;
    feedback.classList.add('text-emerald-600');
    return true;
}

const attachmentInput = document.querySelector('input[name="attachments[]"]');
if (attachmentInput) {
    attachmentInput.addEventListener('change', updateAttachmentFeedback);
}

if (circulationForm) {
    circulationForm.addEventListener('submit', function(e) {
        if (!updateAttachmentFeedback()) {
            e.preventDefault();
        }
    }, { capture: true });
}


// Enhanced View Document
const _isAdminOrAbove = <?= in_array($currentUserLevel, [0,1], true) ? 'true' : 'false' ?>;
function viewDocument(id) {
    // Admins and super-admins get a dedicated document page; others see modal
        if (_isAdminOrAbove) {
            window.location.href = `index.php?controller=correspondence&action=show&id=${encodeURIComponent(id)}`;
        return;
    }

    const modal = document.getElementById('documentModal');
    const body = document.getElementById('modalBody');
    const title = document.getElementById('modalTitle');

    title.textContent = 'Loading...';
    body.innerHTML = `<div class="flex justify-center py-24"><div class="h-12 w-12 animate-spin rounded-full border-4 border-slate-300 border-t-blue-600"></div></div>`;
    modal.classList.remove('hidden');

    fetch(`index.php?controller=correspondence&action=getDocumentDetails&id=${id}`)
        .then(r => r.text())
        .then(html => {
            body.innerHTML = html;
            title.textContent = 'Document Details';
        })
        .catch(() => body.innerHTML = `<p class="text-red-600">Failed to load.</p>`);
}

function closeModal() {
    document.getElementById('documentModal').classList.add('hidden');
}

document.addEventListener('click', function(e) {
    const btn = e.target.closest('[data-history-toggle="1"]');
    if (!btn) return;

    const descId = btn.getAttribute('data-history-target');
    const desc = descId ? document.getElementById(descId) : null;
    if (!desc) return;

    const isExpanded = btn.getAttribute('data-expanded') === '1';
    const fullText = btn.getAttribute('data-history-full') || '';
    const previewText = btn.getAttribute('data-history-preview') || '';

    if (isExpanded) {
        desc.textContent = previewText;
        btn.textContent = 'Read more';
        btn.setAttribute('data-expanded', '0');
    } else {
        desc.textContent = fullText;
        btn.textContent = 'See less';
        btn.setAttribute('data-expanded', '1');
    }
});

function openEditDocument(id, title) {
    const modal = document.getElementById('editDocumentModal');
    const body = document.getElementById('editModalBody');
    const modalTitle = modal && modal.querySelector('h3');

    if (modalTitle) modalTitle.textContent = title ? title : 'Edit Document';
    body.innerHTML = `<div class="flex justify-center py-12"><div class="h-12 w-12 animate-spin rounded-full border-4 border-slate-300 border-t-blue-600"></div></div>`;
    modal.classList.remove('hidden');

    fetch(`index.php?controller=correspondence&action=getEditDocumentForm&id=${id}`)
        .then(r => {
            if (!r.ok) throw new Error('Network response was not ok: ' + r.status);
            return r.text();
        })
        .then(html => {
            body.innerHTML = html;
            // allow any scripts inside returned HTML to run by re-inserting them
            Array.from(body.querySelectorAll('script')).forEach(oldScript => {
                const newScript = document.createElement('script');
                if (oldScript.src) newScript.src = oldScript.src;
                else newScript.textContent = oldScript.textContent;
                document.body.appendChild(newScript).parentNode.removeChild(newScript);
            });
        })
        .catch(err => {
            console.error('openEditDocument error:', err);
            body.innerHTML = `<p class="text-red-600 p-6">Failed to load edit form.</p>`;
        });
}

function openFinalizeDraft(id) {
    const form = document.getElementById('circulationForm');
    // If the create form is not on this page (we're on the repository landing), redirect to the new circulation page and populate the form there
    if (!form) {
        window.location.href = `index.php?controller=correspondence&action=newCirculation&draftId=${encodeURIComponent(id)}&fromFinalize=1`;
        return;
    }
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn ? submitBtn.textContent : null;
    if (submitBtn) submitBtn.textContent = 'Loading...';

    fetch(`index.php?controller=correspondence&action=getDocumentData&id=${id}`)
        .then(r => r.json())
        .then(json => {
            if (!json || !json.success) throw new Error(json.message || 'Failed to load draft');
            const doc = json.document || {};
            const recipients = json.recipients || [];
            const cc = json.cc || [];
            const attachments = json.attachments || [];

            // populate fields (same as edit)
            form.querySelector('[name="tracking_id"]').value = doc.tracking_id || '';
            form.querySelector('[name="title"]').value = doc.title || '';
            form.querySelector('[name="type"]').value = doc.type || 'Memo';
            const descEl = document.getElementById('description'); if (descEl) descEl.innerHTML = doc.description || '';
            const descHidden = document.getElementById('description-hidden'); if (descHidden) descHidden.value = doc.description || '';
            form.querySelector('[name="sender_email"]').value = doc.sender_email || '';
            form.querySelector('[name="priority"]').value = doc.priority || 'Medium';
            form.querySelector('[name="due_date"]').value = doc.due_date || '';
            form.querySelector('[name="notes"]').value = doc.notes || '';
            form.querySelector('[name="is_confidential"]').checked = !!doc.is_confidential;

            // clear existing recipient/cc hidden inputs
            document.querySelectorAll('input[name^="recipients"]').forEach(n => n.remove());
            document.querySelectorAll('input[name^="cc"]').forEach(n => n.remove());
            const recipientsContainer = document.getElementById('recipients-chips');
            const ccContainer = document.getElementById('cc-chips');
            recipientsContainer.innerHTML = '';
            ccContainer.innerHTML = '';

            function addSelectionObj(section, item) {
                const input = document.createElement('input');
                input.type = 'checkbox'; input.name = `${section}[]`; input.value = item.id; input.checked = true; input.style.display = 'none';
                form.appendChild(input);
                const chip = document.createElement('span');
                chip.className = 'inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-700 mr-2';
                chip.textContent = item.name || item.email || item.id;
                const container = section === 'cc' ? ccContainer : recipientsContainer;
                container.appendChild(chip);
            }

            recipients.forEach(r => addSelectionObj('recipients', r));
            cc.forEach(c => addSelectionObj('cc', c));

            // attachments: show list of links and disable file input
            const attachmentInput = document.querySelector('input[name="attachments[]"]');
            const attachmentFeedback = document.getElementById('attachment-feedback');
            if (attachmentInput) attachmentInput.style.display = 'none';
            if (attachmentFeedback) {
                if (attachments.length === 0) {
                    attachmentFeedback.textContent = 'No attachments.';
                } else {
                    attachmentFeedback.innerHTML = attachments.map(a => `<div class="text-sm"><a href="${a.download_url}" target="_blank" class="text-blue-600 hover:underline">${a.file_name}</a></div>`).join('');
                }
            }

            // set form to update mode and mark finalize
            let editIdInput = document.getElementById('editIdInput');
            if (!editIdInput) { editIdInput = document.createElement('input'); editIdInput.type = 'hidden'; editIdInput.name = 'id'; editIdInput.id = 'editIdInput'; form.appendChild(editIdInput); }
            editIdInput.value = id;
            let finalizeInput = document.getElementById('finalizeInput');
            if (!finalizeInput) { finalizeInput = document.createElement('input'); finalizeInput.type = 'hidden'; finalizeInput.name = 'finalize'; finalizeInput.id = 'finalizeInput'; finalizeInput.value = '1'; form.appendChild(finalizeInput); }

            form.dataset.originalAction = form.action;
            form.action = `index.php?controller=correspondence&action=update&id=${id}`;
            if (submitBtn) submitBtn.textContent = 'Finalize & Circulate';

            // repurpose Reset button to 'Save as Draft' while finalizing
            const resetBtn = document.querySelector('#formActions button[type="button"]');
            if (resetBtn) {
                resetBtn.textContent = 'Save as Draft';
                resetBtn.onclick = saveDraftFromFinalize;
            }

            // ensure cancel button exists and label it for finalization
            if (!document.getElementById('cancelEditButton')) {
                const cancelBtn = document.createElement('button');
                cancelBtn.type = 'button';
                cancelBtn.id = 'cancelEditButton';
                cancelBtn.className = 'h-11 px-4 rounded-2xl border border-slate-300 bg-white text-sm font-semibold text-slate-700 transition hover:bg-slate-50';
                cancelBtn.textContent = 'Cancel Finalization';
                cancelBtn.addEventListener('click', function() {
                    // revert to empty create form (same logic as generic cancel)
                    if (form.dataset.originalAction) form.action = form.dataset.originalAction;
                    if (submitBtn) submitBtn.textContent = form.dataset.originalSubmitText || 'Circulate Document';
                    const eid = document.getElementById('editIdInput'); if (eid) eid.remove();
                    const fin = document.getElementById('finalizeInput'); if (fin) fin.remove();
                    const snapshotContainer = document.getElementById('editSnapshotContainer'); if (snapshotContainer) snapshotContainer.innerHTML = '';
                    form.reset(); const desc = document.getElementById('description'); if (desc) desc.innerHTML = '';
                    const attachmentInput = document.querySelector('input[name="attachments[]"]');
                    const attachmentFeedback = document.getElementById('attachment-feedback');
                    if (attachmentInput) { attachmentInput.style.display = ''; try { attachmentInput.value = null; } catch(e){} }
                    if (attachmentFeedback) attachmentFeedback.textContent = 'No files selected.';
                    document.querySelectorAll('input[name^="recipients"]').forEach(n => n.remove());
                    document.querySelectorAll('input[name^="cc"]').forEach(n => n.remove());
                    const recipientsContainer = document.getElementById('recipients-chips'); if (recipientsContainer) recipientsContainer.innerHTML = '<span class="text-sm text-slate-500">No recipients selected</span>';
                    const ccContainer = document.getElementById('cc-chips'); if (ccContainer) ccContainer.innerHTML = '<span class="text-sm text-slate-500">No CC selected</span>';
                    syncSelectionState(); updateRecipientBadge(); updateSummaryPanel();
                    // restore Reset button behavior
                    const resetBtn = document.querySelector('#formActions button[type="button"]');
                    if (resetBtn) { resetBtn.textContent = 'Reset'; resetBtn.onclick = resetForm; }
                    this.remove();
                });
                const actions = document.getElementById('formActions');
                if (actions) actions.prepend(cancelBtn);
            } else {
                // if already present, update label
                document.getElementById('cancelEditButton').textContent = 'Cancel Finalization';
            }

            // show snapshot/history area
            const snapshotContainer = document.getElementById('editSnapshotContainer');
            if (snapshotContainer) snapshotContainer.innerHTML = '';

            // focus title
            form.querySelector('[name="title"]').focus();
        })
        .catch(err => {
            console.error('openFinalizeDraft error', err);
            alert('Failed to load draft: ' + (err.message || 'Unknown error'));
        })
        .finally(() => { if (submitBtn && originalText) submitBtn.textContent = originalText; });
}

function closeEditModal() {
    document.getElementById('editDocumentModal').classList.add('hidden');
}

let confirmActionCallback = null;

function openConfirmModal(title, message, buttonText, heading, callback, buttonClass = 'bg-red-600 hover:bg-red-700') {
    document.getElementById('confirmTitle').textContent = heading || title;
    document.getElementById('confirmMessage').textContent = message;
    const actionButton = document.getElementById('confirmActionButton');
    actionButton.textContent = buttonText || 'Confirm';
    actionButton.className = `inline-flex items-center justify-center rounded-2xl px-4 py-3 text-sm font-semibold text-white ${buttonClass}`;
    confirmActionCallback = callback;
    document.getElementById('confirmModal').classList.remove('hidden');
}

function closeConfirmModal() {
    confirmActionCallback = null;
    document.getElementById('confirmModal').classList.add('hidden');
}

document.getElementById('confirmActionButton').addEventListener('click', function() {
    if (typeof confirmActionCallback === 'function') {
        const callback = confirmActionCallback;
        closeConfirmModal();
        callback();
    }
});

function openDeleteConfirm(id, title) {
    openConfirmModal(
        'Delete document?',
        `Delete "${title}"? This will soft-delete the document, keep it visible for audit purposes, and disable edits, downloads, and receiving actions.`,
        'Delete Document',
        'Delete Document',
        () => {
            const form = document.getElementById('hiddenDeleteForm');
            form.action = `index.php?controller=correspondence&action=delete&id=${id}`;
            form.submit();
        },
        'bg-red-600 hover:bg-red-700'
    );
}

function openHardDeleteConfirm(id, title) {
    openConfirmModal(
        'Permanently delete document?',
        `Hard delete "${title}"? This removes the document, circulations, and attachments permanently. This action is only available to Super Admin and cannot be undone.`,
        'Hard Delete',
        'Hard Delete',
        () => {
            const form = document.getElementById('hiddenHardDeleteForm');
            form.action = `index.php?controller=correspondence&action=hardDelete&id=${id}`;
            form.submit();
        },
        'bg-rose-700 hover:bg-rose-800'
    );
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
        closeEditModal();
        closeConfirmModal();
    }
});

function downloadAttachment(event, id) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }
    window.location.href = `index.php?controller=correspondence&action=download&attachment_id=${id}`;
}

function downloadDocument(event, id) {
    downloadAttachment(event, id);
}
</script>
