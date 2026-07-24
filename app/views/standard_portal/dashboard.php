<?php require __DIR__ . '/partials.php'; ?>
<?php portalHeader('Dashboard'); ?>
<?php portalFlash(); ?>

<?php
$total = (int)($stats['total'] ?? 0);
$received = (int)($stats['received'] ?? 0);
$pending = (int)($stats['pending'] ?? 0);
$ccTotal = (int)($stats['cc_total'] ?? 0);
$displayName = trim((string)($_SESSION['standard_user_name'] ?? 'User'));
$firstName = $displayName !== '' ? explode(' ', $displayName)[0] : 'User';
$updatedAt = date('M d, Y g:i A');

$documentsList = is_array($documents ?? null) ? $documents : [];
$pendingDocuments = [];
$receivedDocuments = [];
$acknowledgedDocuments = [];

foreach ($documentsList as $doc) {
    $status = strtolower((string)($doc['status'] ?? $doc['circulation_status'] ?? ''));
    $circulationStatus = strtolower((string)($doc['circulation_status'] ?? ''));

    if ($status === 'pending' || $circulationStatus === 'pending' || $status === 'for action' || $circulationStatus === 'for action') {
        $pendingDocuments[] = $doc;
    }

    if ($status === 'received' || $circulationStatus === 'received') {
        $receivedDocuments[] = $doc;
    }

    if ($status === 'acknowledged' || $circulationStatus === 'acknowledged' || $status === 'accepted' || $circulationStatus === 'accepted') {
        $acknowledgedDocuments[] = $doc;
    }
}

$latestPendingDoc = !empty($pendingDocuments) ? $pendingDocuments[0] : null;
$latestReceivedDoc = !empty($receivedDocuments) ? $receivedDocuments[0] : null;
$latestAcknowledgedDoc = !empty($acknowledgedDocuments) ? $acknowledgedDocuments[0] : null;
?>

<div class="space-y-2 lg:space-y-3">
    <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm lg:p-4">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0 space-y-2">
                <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-slate-500">RECEIVER USER PORTAL</p>
                <div class="space-y-1">
                    <h1 class="text-base font-semibold leading-5 text-slate-900">Dashboard</h1>
                    <p class="text-sm leading-5 text-slate-500">
                        Welcome back, <?= htmlspecialchars($firstName) ?>. You have <?= (int)$pending ?> document(s) awaiting acknowledgement.
                    </p>
                </div>
            </div>
            <a href="index.php?controller=StandardPortal&action=inbox"
               class="inline-flex h-9 items-center justify-center rounded-lg bg-slate-900 px-3 text-sm font-semibold text-white transition duration-150 ease-out hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-200">
                View Correspondence
            </a>
        </div>
        <p class="pt-2 text-xs leading-5 text-slate-500">Last updated: <?= htmlspecialchars($updatedAt) ?></p>
    </div>

    <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
        <div class="flex h-full flex-col rounded-xl border border-slate-200 bg-white p-3 shadow-sm lg:p-4">
            <div class="mb-2 h-0.5 w-full rounded-full bg-slate-400"></div>
            <div class="space-y-2">
                <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-slate-500">Assigned</p>
                <p class="text-xl font-semibold leading-5 text-slate-900"><?= $total ?></p>
                <p class="text-sm leading-5 text-slate-500">Documents</p>
            </div>
        </div>
        <div class="flex h-full flex-col rounded-xl border border-slate-200 bg-white p-3 shadow-sm lg:p-4">
            <div class="mb-2 h-0.5 w-full rounded-full bg-amber-500"></div>
            <div class="space-y-2">
                <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-slate-500">Pending</p>
                <p class="text-xl font-semibold leading-5 text-slate-900"><?= $pending ?></p>
                <p class="text-sm leading-5 text-slate-500">Waiting</p>
            </div>
        </div>
        <div class="flex h-full flex-col rounded-xl border border-slate-200 bg-white p-3 shadow-sm lg:p-4">
            <div class="mb-2 h-0.5 w-full rounded-full bg-emerald-500"></div>
            <div class="space-y-2">
                <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-slate-500">Received</p>
                <p class="text-xl font-semibold leading-5 text-slate-900"><?= $received ?></p>
                <p class="text-sm leading-5 text-slate-500">Completed</p>
            </div>
        </div>
        <div class="flex h-full flex-col rounded-xl border border-slate-200 bg-white p-3 shadow-sm lg:p-4">
            <div class="mb-2 h-0.5 w-full rounded-full bg-slate-500"></div>
            <div class="space-y-2">
                <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-slate-500">CC</p>
                <p class="text-xl font-semibold leading-5 text-slate-900"><?= $ccTotal ?></p>
                <p class="text-sm leading-5 text-slate-500">Shared</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm lg:p-4">
            <div class="space-y-2">
                <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-slate-500">Quick Actions</p>
                <div class="space-y-1">
                    <h3 class="text-sm font-semibold leading-5 text-slate-900">Continue Working</h3>
                    <p class="text-sm leading-5 text-slate-500">
                        <?php if ($latestPendingDoc): ?>
                            <?= htmlspecialchars((string)($latestPendingDoc['title'] ?? 'Untitled document')) ?>
                        <?php else: ?>
                            No pending document available.
                        <?php endif; ?>
                    </p>
                </div>
                <?php if ($latestPendingDoc): ?>
                    <a href="index.php?controller=StandardPortal&action=viewDocument&id=<?= (int)($latestPendingDoc['id'] ?? 0) ?>"
                       class="inline-flex h-9 items-center justify-center rounded-lg bg-slate-900 px-3 text-sm font-semibold text-white transition duration-150 ease-out hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-200">
                        Open Document
                    </a>
                <?php else: ?>
                    <span class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 px-3 text-sm font-semibold text-slate-500">
                        No pending document
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm lg:p-4">
            <div class="space-y-2">
                <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-slate-500">Quick Actions</p>
                <div class="space-y-1">
                    <h3 class="text-sm font-semibold leading-5 text-slate-900">Recent Activity</h3>
                </div>
                <div class="space-y-2 text-sm text-slate-600">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500">Last received</p>
                        <p class="pt-1 font-medium leading-5 text-slate-900">
                            <?php if ($latestReceivedDoc): ?>
                                <?= htmlspecialchars((string)($latestReceivedDoc['title'] ?? 'Untitled document')) ?>
                            <?php else: ?>
                                No received document yet.
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500">Last acknowledged</p>
                        <p class="pt-1 font-medium leading-5 text-slate-900">
                            <?php if ($latestAcknowledgedDoc): ?>
                                <?= htmlspecialchars((string)($latestAcknowledgedDoc['title'] ?? 'Untitled document')) ?>
                            <?php else: ?>
                                No acknowledged document yet.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-200 p-3 lg:flex-row lg:items-center lg:justify-between lg:p-4">
            <div class="space-y-1">
                <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-slate-500">Recent</p>
                <h2 class="text-base font-semibold leading-5 text-slate-900">Recent Correspondence</h2>
                <p class="text-xs leading-5 text-slate-500">Recently assigned documents.</p>
            </div>
            <a href="index.php?controller=StandardPortal&action=inbox" class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 px-3 text-sm font-semibold text-slate-700 transition duration-150 ease-out hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-200">View All</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="p-3 text-left text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500 lg:p-4">Tracking</th>
                        <th class="p-3 text-left text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500 lg:p-4">Title</th>
                        <th class="p-3 text-center text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500 lg:p-4">Status</th>
                        <th class="p-3 text-left text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500 lg:p-4">Due</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($documents as $doc): ?>
                        <tr class="cursor-pointer transition-colors duration-150 hover:bg-slate-50" onclick="window.location.href='index.php?controller=StandardPortal&action=viewDocument&id=<?= (int)$doc['id'] ?>'">
                            <td class="p-3 lg:p-4">
                                <span class="font-medium leading-5 text-slate-700"><?= htmlspecialchars($doc['tracking_id']) ?></span>
                            </td>
                            <td class="p-3 lg:p-4">
                                <span class="font-semibold leading-5 text-slate-900"><?= htmlspecialchars($doc['title']) ?></span>
                            </td>
                            <td class="p-3 text-center lg:p-4">
                                <div class="inline-flex">
                                    <?= portalStatusBadge($doc['status'] ?? $doc['circulation_status']) ?>
                                </div>
                            </td>
                            <td class="p-3 text-slate-500 lg:p-4">
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-xs leading-5 text-slate-500">
                                        <?= $doc['due_date'] ? date('M d, Y', strtotime($doc['due_date'])) : 'None' ?>
                                    </span>
                                    <svg class="h-3.5 w-3.5 text-slate-400 opacity-0 transition-opacity duration-150 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($documents)): ?>
                        <tr>
                            <td colspan="4" class="p-3 lg:p-4">
                                <div class="flex flex-col items-center justify-center rounded-lg border border-dashed border-slate-200 bg-slate-50 p-3 text-center lg:p-4">
                                    <p class="text-sm font-medium leading-5 text-slate-700">No correspondence assigned yet.</p>
                                    <p class="pt-1 text-xs leading-5 text-slate-500">Documents assigned to you will appear here.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php portalFooter(); ?>
