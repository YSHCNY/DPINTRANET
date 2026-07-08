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

     

        <!-- ====================== DOCUMENT REPOSITORY ====================== -->
        <?php require __DIR__ . '/_shared.php'; ?>
        <div class="rounded-lg  overflow-hidden">
            <div class="">
               <div class="mb-6 overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-[0_18px_40px_rgba(15,23,42,0.06)]">


            <div class="border-b bg-slate-50 border-slate-200  px-5 py-4">
                <div class=" flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">

            <!-- Left -->

               <div class="max-w-3xl ">
                <p class="text-[10px] font-semibold uppercase tracking-[0.26em] text-blue-600">
                    Correspondence
                </p>

                <h2 class=" text-xl font-semibold tracking-tight text-slate-900">Correspondence Dashboard</h2>

                 <p class=" max-w-2xl text-sm leading-6 text-slate-500">
                    Manage correspondence, drafts, and repository documents from one workspace
                        </p>

            </div>

            <!-- Right Stats -->

            <div class="flex flex-wrap items-center gap-3">

                <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-2 shadow-sm">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                    <span class="text-[11px] font-medium text-slate-700">
                        <?= count($documents) ?> file(s)
                    </span>
                </div>

                <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-2 shadow-sm">
                    <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                    <span class="text-[11px] font-medium text-slate-700">
                        <?= $draftsCount ?? 0 ?> draft(s)
                    </span>
                </div>

                <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-2 shadow-sm">
                    <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                    <span class="text-[11px] font-medium text-slate-700">
                        <?= $removedCount ?? 0 ?> removed
                    </span>
                </div>

            </div>

        </div>

    </div>

    <!-- ========================= -->
    <!-- Controls -->
    <!-- ========================= -->

    <div class="p-2">
        <div class="flex flex-col gap-3 rounded-xl  bg-slate-50/70 p-3 lg:flex-row lg:items-end lg:justify-between">
            <div class="flex flex-1 flex-col gap-3 md:flex-row md:items-end">
                <div class="w-full md:w-44">
                    <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">Priority</label>
                    <select id="priorityFilter"
                        class="h-10 w-full rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All priorities</option>
                        <option>Low</option>
                        <option>Medium</option>
                        <option>High</option>
                        <option>Urgent</option>
                    </select>
                </div>

                <div class="w-full md:w-44">
                    <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">Status</label>
                    <select id="statusFilter"
                        class="h-10 w-full rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All status</option>
                        <option>Inprogress</option>
                        <option>Done</option>
                        <option>Suspended</option>
                        <option>Draft</option>
                    </select>
                </div>

                <label class="flex h-9 items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700">
                    <span class="h-2 w-2 rounded-full <?= !empty($showRemovedItems) ? 'bg-amber-500' : 'bg-slate-400' ?>"></span>
                    <span class="font-medium">Show Removed</span>
                    <input
                        id="showRemovedItems"
                        type="checkbox"
                        <?= !empty($showRemovedItems) ? 'checked' : '' ?>
                        class="h-10 w-4 rounded rounded-2xl border-slate-300 text-emerald-600 focus:ring-emerald-500">
                </label>

                <label class="flex h-9 w-full items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-500 md:w-72">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-4.35-4.35m1.85-5.15a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
                    <input id="repoSearch" type="search" placeholder="Search documents" class="w-full border-0 bg-transparent p-0 text-sm text-slate-700 outline-none placeholder:text-slate-400">
                </label>
            </div>

            <a href="index.php?controller=correspondence&action=newCirculation"
                class="inline-flex h-9 items-center rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                Create Correspondence
            </a>
        </div>
    </div>

</div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mt-4">
                

                                <div class="border-b border-slate-200 px-4 py-4 md:px-6">
                    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-slate-500">Document circulation repo</p>
                            <h3 class="mt-1 text-base font-semibold text-slate-900">All circulations</h3>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-medium text-slate-600">
                                <?= count($documents) ?> documents
                            </span>
                        </div>
                    </div>
                </div>

                    <div class="p-5">
                <div id="dropdown-root" class="pointer-events-none fixed inset-0 z-[9999]"></div>

                <!-- Desktop: table view -->
                <div class="hidden md:block overflow-auto ">
                    <table id="documentTable" class="w-full text-sm compact">
                    <thead class="bg-white">
                        <tr>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Tracking</th>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Document</th>
                            <th class="px-3 py-3 text-center text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Received</th>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Status</th>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Closed</th>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Circulated</th>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Due</th>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Priority</th>
                            <th class="w-24 px-3 py-3 text-center text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Actions</th>
                            <th class="hidden">State</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $doc): ?>
                        <?php
                            $receivedCount = (int)($doc['received_count'] ?? 0);
                            $totalRecipients = (int)($doc['total_recipients'] ?? 0);
                            $doneCount = (int)($doc['done_count'] ?? 0);
                            // Status is driven by Open/Close which marks circulations as 'Done'
                            $status =  $doc['status'];
                            $isDeleted = !empty($doc['is_deleted']);
                            $isDraftDocument = !empty($doc['is_draft']) || strtolower(trim((string)($status ?? ''))) === 'draft';
                            $isEdited = !empty($doc['is_edited']);
                            $canManage = false;
                            if (!empty($doc['created_by']) && (int)$doc['created_by'] === (int)($_SESSION['id'] ?? 0) && !$isDeleted && !empty($doc['created_at'])) {
                                $deadline = strtotime($doc['created_at'] . ' +7 days');
                                $canManage = $deadline !== false && time() <= $deadline;
                            }
                            $manageUntil = !empty($doc['created_at']) ? date('M d, Y g:i A', strtotime($doc['created_at'] . ' +7 days')) : null;
                            $st = strtolower(trim((string)($status ?? 'inprogress')));
                            $isClosedDocument = in_array($st, ['done', 'completed'], true);
                            $closedDisplay = $isClosedDocument
                                ? (!empty($doc['closed_at']) ? date('M d, Y', strtotime($doc['closed_at'])) : '—')
                                : 'Open';
                   // STATUS COLORS = document state
                    switch ($st) {
                        case 'done':
                        case 'completed':
                            $statusDotClass = 'bg-emerald-500';
                            break;

                        case 'suspended':
                            $statusDotClass = 'bg-rose-500';
                            break;

                        case 'draft':
                            $statusDotClass = 'bg-sky-500';
                            break;

                        case 'pending':
                        case 'in progress':
                        case 'in_progress':
                        default:
                            $statusDotClass = 'bg-slate-400';
                            break;
                    }

                    // PRIORITY COLORS = urgency
                    $priority = strtolower(trim((string)($doc['priority'] ?? '')));
                    $priorityDotClass = match ($priority) {
                        'urgent' => 'bg-rose-500',
                        'high'   => 'bg-amber-500',
                        'medium' => 'bg-violet-500',
                        'low'    => 'bg-slate-400',
                        default  => 'bg-slate-400',
                    };
                        ?>
                        <tr class="border-b border-slate-100 bg-white align-middle transition-colors hover:bg-slate-50 <?= $isDeleted ? 'text-slate-500' : 'text-slate-900' ?>">
                            <td class="px-3 py-3 align-middle whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-medium text-slate-500">
                                    <?= htmlspecialchars($doc['tracking_id']) ?>
                                </span>
                            </td>
                            <td class="min-w-[260px] px-3 py-3 align-middle">
                                <div class="min-w-0">
                                    <?php $titleText = (string)($doc['title'] ?? ''); $isLongTitle = mb_strlen($titleText) > 60; $previewTitle = $isLongTitle ? mb_substr($titleText, 0, 60) : $titleText; ?>
                                    <div class="title-container break-words whitespace-normal text-sm font-semibold text-slate-900" style="max-width:56ch;">
                                        <span class="title-preview"><?= htmlspecialchars($isLongTitle ? $previewTitle . '...' : $previewTitle) ?></span>
                                        <?php if ($isLongTitle): ?>
                                            <span class="title-full hidden"><?= htmlspecialchars($titleText) ?></span>
                                            <button type="button" data-title-toggle aria-expanded="false" class="ml-2 text-[11px] font-medium text-slate-500 transition hover:text-slate-700">See more</button>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
                                        <span class="font-medium text-slate-400"><?= htmlspecialchars($doc['tracking_id']) ?></span>
                                        <span class="text-slate-300">•</span>
                                        <span><?= !empty($doc['created_at']) ? date('M d, Y', strtotime($doc['created_at'])) : '—' ?></span>
                                        <?php if (!empty($doc['notes'])): ?>
                                            <span class="text-slate-300">•</span>
                                            <span class="truncate"><?= htmlspecialchars($doc['notes']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3 align-middle text-center whitespace-nowrap">
                                <?php $percent = $totalRecipients ? (int) floor(($receivedCount / $totalRecipients) * 100) : 0; ?>
                                <div class="text-sm font-semibold <?= $isDeleted ? 'text-slate-400' : 'text-slate-700' ?>"><?= $receivedCount ?>/<?= $totalRecipients ?></div>
                                <div class="mx-auto mt-1.5 h-1.5 w-20 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-1.5 rounded-full bg-emerald-500" style="width:<?= $percent ?>%"></div>
                                </div>
                            </td>
                            <td class="px-3 py-3 align-middle" data-search="<?= htmlspecialchars($status) ?>">
                                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-600">
                                    <span class="h-2 w-2 rounded-full <?= $statusDotClass ?>"></span>
                                    <span><?= htmlspecialchars($status ?: 'Pending') ?></span>
                                </span>
                            </td>
                            <td class="px-3 py-3 align-middle whitespace-nowrap">
                                <span class="text-sm <?= $isClosedDocument ? 'text-slate-700' : 'text-slate-400' ?>"><?= htmlspecialchars($closedDisplay) ?></span>
                            </td>
                            <td class="px-3 py-3 align-middle whitespace-nowrap text-sm text-slate-600"><?= !empty($doc['created_at']) ? date('M d, Y', strtotime($doc['created_at'])) : '—' ?></td>
                            <td class="px-3 py-3 align-middle whitespace-nowrap text-sm text-slate-600">
                                <?php if ($doc['due_date']): ?>
                                    <?= date('M d, Y', strtotime($doc['due_date'])) ?>
                                <?php else: ?>
                                    <span class="text-slate-400">No due date</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-3 align-middle" data-search="<?= htmlspecialchars($doc['priority']) ?>">
                                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-600">
                                    <span class="h-2 w-2 rounded-full <?= $priorityDotClass ?>"></span>
                                    <span><?= htmlspecialchars($doc['priority'] ?: 'Not set') ?></span>
                                </span>
                            </td>
                            <td class="px-3 py-3 align-middle text-center">
                                <div class="inline-flex items-center justify-center gap-1.5">
                                    <?php if ($isDraftDocument && $canFinalize): ?>
                                        <button type="button"
                                    class="relative z-10 inline-flex h-10 w-10 items-center justify-center rounded-lg border border-blue-200 bg-blue-50 text-blue-600 shadow-sm transition-all duration-300 ease-out hover:-translate-y-1 hover:scale-110 hover:border-blue-300 hover:bg-blue-100 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-400 active:scale-80 animate-bounce"
                                            title="Draft document"
                                            aria-label="Draft document"
                                            data-draft-action
                                            data-document-id="<?= (int)$doc['id'] ?>"
                                            onclick="window.location.href='index.php?controller=correspondence&action=newCirculation&draftId=<?= (int)$doc['id'] ?>&fromFinalize=1'">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 21V7.5A2.5 2.5 0 0 1 5.5 5H10l2 2h6.5A2.5 2.5 0 0 1 21 9.5V21l-4.5-3-4.5 3-4.5-3L3 21Z"/>
                                            </svg>
                                        </button>
                                    <?php endif; ?>
                                    <button type="button"
                                        data-view-action
                                        aria-label="View document"
                                        data-document-id="<?= (int)$doc['id'] ?>"
                                        class="relative z-10 h-10 w-10 inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm transition-all duration-200 ease-out hover:-translate-y-0.5 hover:border-slate-300 hover:bg-slate-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-slate-400 active:translate-y-0"
                                        title="View">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </button>
                                    <button type="button"
                                        data-actions-toggle
                                        aria-haspopup="menu"
                                        aria-expanded="false"
                                        data-document-id="<?= (int)$doc['id'] ?>"
                                        data-document-title="<?= htmlspecialchars((string)($doc['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                        data-can-edit="<?= $canEditCorrespondence && !$isDeleted ? '1' : '0' ?>"
                                        data-can-delete="<?= $canDeleteCorrespondence && !$isDeleted ? '1' : '0' ?>"
                                        data-can-hard-delete="<?= $canHardDeleteCorrespondence ? '1' : '0' ?>"
                                        data-is-deleted="<?= $isDeleted ? '1' : '0' ?>"
                                        class="relative z-10 h-10 w-10 inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm transition-all duration-200 ease-out hover:-translate-y-0.5 hover:border-slate-300 hover:bg-slate-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-slate-400 active:translate-y-0"
                                        title="More options">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M12 6v.01M12 12v.01M12 18v.01"/></svg>
                                    </button>
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
                        $isClosedDocument = in_array(strtolower(trim((string)($doc['status'] ?? ''))), ['done', 'completed'], true);
                        $closedDisplay = $isClosedDocument
                            ? (!empty($doc['closed_at']) ? date('M d, Y', strtotime($doc['closed_at'])) : '—')
                            : 'Open';
                    ?>
                    <article data-state="<?= $cardState ?>" class="group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs text-blue-700"><?= htmlspecialchars($doc['tracking_id']) ?></span>
                                    <?php $mt = (string)($doc['title'] ?? ''); $mtLong = mb_strlen($mt) > 80; $mtPreview = $mtLong ? mb_substr($mt, 0, 80) : $mt; ?>
                                    <h3 class="title-container truncate text-sm font-semibold text-slate-900 break-words whitespace-normal" style="max-width:80ch;">
                                        <span class="title-preview"><?= htmlspecialchars($mtLong ? $mtPreview . '...' : $mtPreview) ?></span>
                                        <?php if ($mtLong): ?>
                                            <span class="title-full hidden"><?= htmlspecialchars($mt) ?></span>
                                            <button type="button" data-title-toggle aria-expanded="false" class="ml-2 text-xs text-blue-600 hover:underline">See more</button>
                                        <?php endif; ?>
                                    </h3>
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
                            <div class="inline-flex items-center gap-2">
                                <button type="button" data-row-action="edit" data-document-id="<?= (int)$doc['id'] ?>" data-document-title="<?= htmlspecialchars((string)($doc['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                    <?= (!$canEditCorrespondence || $isDeleted) ? 'disabled' : '' ?>
                                    class="h-8 w-8 inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50">
                                    ✎
                                </button>
                                <button type="button" data-row-action="delete" data-document-id="<?= (int)$doc['id'] ?>" data-document-title="<?= htmlspecialchars((string)($doc['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                    <?= (!$canDeleteCorrespondence || $isDeleted) ? 'disabled' : '' ?>
                                    class="h-8 w-8 inline-flex items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100">
                                    🗑
                                </button>
                                    <?php if ($canHardDeleteCorrespondence): ?>
                                        <button type="button" data-row-action="hard-delete" data-document-id="<?= (int)$doc['id'] ?>" data-document-title="<?= htmlspecialchars((string)($doc['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                            class="h-8 w-8 inline-flex items-center justify-center rounded-lg border border-rose-600 bg-rose-600 text-white hover:bg-rose-700">
                                            🗡
                                        </button>
                                    <?php endif; ?>
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

    .correspondence-ui .dataTables_wrapper .dataTables_length select,
    .correspondence-ui .dataTables_wrapper .dataTables_filter input {
        border: 1px solid rgba(15, 23, 42, 0.12) !important;
        border-radius: 0.75rem !important;
        background: white !important;
        color: #0f172a !important;
        box-shadow: none !important;
        padding: 0.5rem 0.75rem !important;
        min-height: 2.25rem !important;
        font-size: 0.875rem !important;
    }

    .correspondence-ui .dataTables_wrapper .dataTables_paginate .paginate_button {
        border: 1px solid rgba(15, 23, 42, 0.1) !important;
        border-radius: 0.6rem !important;
        background: white !important;
        color: #475569 !important;
        padding: 0.45rem 0.7rem !important;
        margin: 0 0.2rem !important;
        transition: all 160ms ease !important;
    }

    .correspondence-ui .dataTables_wrapper .dataTables_paginate .paginate_button:hover,
    .correspondence-ui .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #f8fafc !important;
        border-color: rgba(15, 23, 42, 0.18) !important;
        color: #0f172a !important;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.06) !important;
    }
    /* Correspondence UI overrides: minimalist, muted, premium look */


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

    .floating-actions-menu {
        position: fixed;
        min-width: 12rem;
        border-radius: 0.9rem;
        border: 1px solid rgba(15, 23, 42, 0.1);
        background: white;
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.12);
        overflow: hidden;
        z-index: 99999;
        pointer-events: auto;
    }

    .floating-actions-menu button {
        display: block;
        width: 100%;
        border: 0;
        background: transparent;
        padding: 0.7rem 0.9rem;
        text-align: left;
        font-size: 0.875rem;
        color: #334155;
        cursor: pointer;
        transition: background-color 140ms ease, color 140ms ease;
    }

    .floating-actions-menu button:hover,
    .floating-actions-menu button:focus-visible {
        background-color: #f8fafc;
        color: #0f172a;
        outline: none;
    }

    .floating-actions-menu button[data-action="delete"] {
        color: #b91c1c;
    }

    .floating-actions-menu button[data-action="hard-delete"] {
        color: #991b1b;
    }

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
        order: [[4, 'desc']],
        columnDefs: [
            { targets: 9, visible: false, searchable: true }
        ],
        dom: '<"flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4"l>rt<"flex flex-col md:flex-row md:items-center md:justify-between gap-3 mt-4"ip>',
        language: {
            search: '',
            searchPlaceholder: 'Search documents'
        }
    });

    
    // expose instance globally for other handlers
    window.documentTableInstance = documentTable;

    const showRemovedItems = <?= !empty($showRemovedItems) ? 'true' : 'false' ?>;
    $('#showRemovedItems').prop('checked', showRemovedItems);
    function applyShowRemoved(enabled) {
        const dt = window.documentTableInstance;
        if (dt && typeof dt.column === 'function') {
            dt.column(9).search(enabled ? '' : '^Active$', true, false).draw();
        }
        // mirror to mobile cards
        if (document.getElementById('mobileCards')) {
            document.querySelectorAll('#mobileCards article[data-state="Removed"]').forEach(el => {
                el.style.display = enabled ? '' : 'none';
            });
        }
    }

    applyShowRemoved(showRemovedItems);

    $('#priorityFilter').on('change', function() {
        documentTable.column(7).search(this.value ? '^' + this.value + '$' : '', true, false).draw();
    });

    $('#statusFilter').on('change', function() {
        documentTable.column(3).search(this.value ? '^' + this.value + '$' : '', true, false).draw();
    });

    // Sort selector moved to header: map friendly options to DataTables ordering
    $('#sortSelect').on('change', function() {
        const v = this.value;
        switch (v) {
            case 'newest':
                    documentTable.order([4, 'desc']).draw();
                    break;
                case 'oldest':
                    documentTable.order([4, 'asc']).draw();
                break;
            case 'title_asc':
                documentTable.order([1, 'asc']).draw();
                break;
            case 'title_desc':
                documentTable.order([1, 'desc']).draw();
                break;
            default:
                // keep current ordering
                break;
        }
    });

    $('#showRemovedItems').on('change', function() {
        const enabled = this.checked;
        applyShowRemoved(enabled);

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

const dropdownRoot = document.getElementById('dropdown-root');
let activeActionMenu = null;
let activeActionTrigger = null;

function closeFloatingActionsMenu() {
    if (activeActionMenu) {
        activeActionMenu.remove();
    }
    if (activeActionTrigger) {
        activeActionTrigger.setAttribute('aria-expanded', 'false');
    }
    activeActionMenu = null;
    activeActionTrigger = null;
}

function positionFloatingActionsMenu(menu, trigger) {
    const rect = trigger.getBoundingClientRect();
    const width = 192;
    const height = 180;
    const padding = 12;
    let left = rect.right - width;
    let top = rect.bottom + 8;

    if (left < padding) {
        left = padding;
    }

    if (left + width > window.innerWidth - padding) {
        left = window.innerWidth - width - padding;
    }

    if (top + height > window.innerHeight - padding) {
        top = Math.max(padding, rect.top - height - 8);
    }

    menu.style.left = `${left}px`;
    menu.style.top = `${top}px`;
}

function openFloatingActionsMenu(trigger) {
    closeFloatingActionsMenu();

    const id = trigger.getAttribute('data-document-id');
    const title = trigger.getAttribute('data-document-title') || '';
    const canEdit = trigger.getAttribute('data-can-edit') === '1';
    const canDelete = trigger.getAttribute('data-can-delete') === '1';
    const canHardDelete = trigger.getAttribute('data-can-hard-delete') === '1';
    const isDeleted = trigger.getAttribute('data-is-deleted') === '1';

    if (!id) return;

    const menu = document.createElement('div');
    menu.className = 'floating-actions-menu';
    menu.setAttribute('role', 'menu');
    menu.setAttribute('aria-label', 'Document actions');

    const actions = [
        { key: 'view', label: 'View', className: 'text-slate-700' }
    ];

    if (canEdit && !isDeleted) {
        actions.push({ key: 'edit', label: 'Edit', className: 'text-slate-700' });
    }

    if (canDelete && !isDeleted) {
        actions.push({ key: 'delete', label: 'Delete', className: 'text-rose-700' });
    }

    if (canHardDelete) {
        actions.push({ key: 'hard-delete', label: 'Hard delete', className: 'text-rose-800' });
    }

    actions.forEach(action => {
        const button = document.createElement('button');
        button.type = 'button';
        button.setAttribute('role', 'menuitem');
        button.setAttribute('data-action', action.key);
        button.setAttribute('data-document-id', id);
        button.setAttribute('data-document-title', title);
        button.className = action.className;
        button.textContent = action.label;
        menu.appendChild(button);
    });

    if (dropdownRoot) {
        dropdownRoot.appendChild(menu);
    } else {
        document.body.appendChild(menu);
    }

    positionFloatingActionsMenu(menu, trigger);
    activeActionMenu = menu;
    activeActionTrigger = trigger;
    trigger.setAttribute('aria-expanded', 'true');

    const firstAction = menu.querySelector('button');
    if (firstAction) {
        firstAction.focus();
    }
}

document.addEventListener('click', function (e) {
    const viewTrigger = e.target.closest('[data-view-action]');
    if (viewTrigger) {
        e.preventDefault();
        e.stopPropagation();
        const id = viewTrigger.getAttribute('data-document-id');
        if (id) {
            viewDocument(Number(id));
        }
        return;
    }

    const trigger = e.target.closest('[data-actions-toggle]');
    if (trigger) {
        e.stopPropagation();
        if (activeActionTrigger === trigger && activeActionMenu && !activeActionMenu.classList.contains('hidden')) {
            closeFloatingActionsMenu();
            return;
        }
        openFloatingActionsMenu(trigger);
        return;
    }

    if (e.target.closest('.floating-actions-menu')) {
        return;
    }

    closeFloatingActionsMenu();
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeFloatingActionsMenu();
    }
});

document.addEventListener('click', function (e) {
    const actionButton = e.target.closest('.floating-actions-menu button');
    if (!actionButton) return;

    e.preventDefault();
    e.stopPropagation();

    const action = actionButton.getAttribute('data-action');
    const id = actionButton.getAttribute('data-document-id');
    const title = actionButton.getAttribute('data-document-title') || '';

    closeFloatingActionsMenu();

    if (!id) return;

    switch (action) {
        case 'view':
            viewDocument(Number(id));
            break;
        case 'edit':
            openEditDocument(Number(id), title);
            break;
        case 'delete':
            openDeleteConfirm(Number(id), title);
            break;
        case 'hard-delete':
            openHardDeleteConfirm(Number(id), title);
            break;
    }
});

window.addEventListener('resize', closeFloatingActionsMenu);
window.addEventListener('scroll', closeFloatingActionsMenu, true);

// Title see more/less toggle (delegated)
document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-title-toggle]');
    if (!btn) return;
    e.stopPropagation();
    const container = btn.closest('.title-container');
    if (!container) return;
    const preview = container.querySelector('.title-preview');
    const full = container.querySelector('.title-full');
    const expanded = btn.getAttribute('aria-expanded') === 'true';
    if (expanded) {
        // collapse
        if (preview) preview.classList.remove('hidden');
        if (full) full.classList.add('hidden');
        btn.textContent = 'See more';
        btn.setAttribute('aria-expanded', 'false');
    } else {
        // expand
        if (preview) preview.classList.add('hidden');
        if (full) full.classList.remove('hidden');
        btn.textContent = 'See less';
        btn.setAttribute('aria-expanded', 'true');
    }
});

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

function getActiveRecipientTarget() {
    const editModal = document.getElementById('editDocumentModal');
    const activeEditForm = editModal && !editModal.classList.contains('hidden')
        ? editModal.querySelector('form#editDocumentForm')
        : null;
    return activeEditForm || document.getElementById('circulationForm');
}

function getRecipientContainers() {
    const recipientsContainer = document.getElementById('recipients-chips-edit') || document.getElementById('recipients-chips');
    const ccContainer = document.getElementById('cc-chips-edit') || document.getElementById('cc-chips');
    return { recipientsContainer, ccContainer };
}

function openRecipientDrawer(mode) {
    drawerMode = mode === 'cc' ? 'cc' : 'recipients';
    document.getElementById('drawerTitleSmall').textContent = mode === 'cc' ? 'Add CC' : 'Add Recipients';
    document.getElementById('drawerTitle').textContent = mode === 'cc' ? 'Select CC recipients' : 'Select recipients';
    const targetForm = getActiveRecipientTarget();
    const existing = Array.from(targetForm ? targetForm.querySelectorAll(`input[name="${mode}[]"]`) : document.querySelectorAll(`input[name="${mode}[]"]`)).map(i => String(i.value));
    document.querySelectorAll('[data-drawer-id]').forEach(cb => {
        cb.checked = existing.includes(String(cb.dataset.drawerId));
    });
    recipientDrawer.classList.remove('hidden');
    recipientDrawerOverlay.classList.remove('hidden');
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
    const checked = Array.from(document.querySelectorAll('#drawerList input[type="checkbox"]:checked'));
    const targetForm = getActiveRecipientTarget();
    if (!targetForm) return;

    targetForm.querySelectorAll(`input[name="${drawerMode}[]"]`).forEach(n => n.remove());

    const { recipientsContainer, ccContainer } = getRecipientContainers();
    if (drawerMode === 'recipients') {
        if (recipientsContainer) recipientsContainer.innerHTML = '';
    } else if (ccContainer) {
        ccContainer.innerHTML = '';
    }

    checked.forEach(cb => {
        const id = cb.dataset.drawerId;
        const name = cb.dataset.drawerName || cb.dataset.drawerEmail || id;

        const input = document.createElement('input');
        input.type = 'checkbox';
        input.name = `${drawerMode}[]`;
        input.value = id;
        input.checked = true;
        input.style.display = 'none';
        input.setAttribute('data-selection-section', drawerMode);
        input.setAttribute('data-user-id', id);
        targetForm.appendChild(input);

        const chip = document.createElement('span');
        chip.className = 'inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-700';
        chip.textContent = name;
        const container = drawerMode === 'cc' ? ccContainer : recipientsContainer;
        if (container) {
            const placeholder = container.querySelector('.text-sm.text-slate-500');
            if (placeholder) placeholder.remove();
            container.appendChild(chip);
        }
    });

    if (recipientsContainer && !recipientsContainer.children.length) {
        recipientsContainer.innerHTML = '<span class="text-sm text-slate-500">No recipients selected</span>';
    }
    if (ccContainer && !ccContainer.children.length) {
        ccContainer.innerHTML = '<span class="text-sm text-slate-500">No CC selected</span>';
    }

    closeRecipientDrawer();
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
