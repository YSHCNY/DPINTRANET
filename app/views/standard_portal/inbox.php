<?php require __DIR__ . '/partials.php'; ?>
<?php portalHeader('Correspondence'); ?>
<?php portalFlash(); ?>

<?php
$assignedCount = count($documents);
$pendingCount = 0;
$completedCount = 0;

foreach ($documents as $doc) {
    $status = strtolower((string)($doc['status'] ?? $doc['circulation_status'] ?? ''));

    if ($status === 'pending' || $status === 'for action') {
        $pendingCount++;
    }

    if ($status === 'received' || $status === 'acknowledged' || $status === 'accepted') {
        $completedCount++;
    }
}
?>

<div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 bg-white px-3 py-3 sm:px-4 lg:px-4 xl:px-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-1.5">
                <p class="text-[9px] font-semibold uppercase tracking-[0.24em] text-emerald-700">Inbox</p>
                <div class="space-y-1">
                    <h2 class="text-base font-semibold text-slate-900">Correspondence to Receive</h2>
                    <p class="text-sm leading-5 text-slate-500">Assigned correspondence awaiting review and acknowledgement.</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-[11px] font-semibold text-slate-600">
                    <?= count($documents) ?> assigned
                </span>
                <label class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-[11px] font-semibold text-slate-600">
                    <span class="h-2.5 w-2.5 rounded-full <?= !empty($showRemovedItems) ? 'bg-emerald-600' : 'bg-slate-300' ?>"></span>
                    Show removed
                    <input type="checkbox" id="showRemovedItems" <?= !empty($showRemovedItems) ? 'checked' : '' ?> class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                </label>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table id="portalInboxTable" class="w-full text-xs">
            <thead class="border-b border-slate-200 bg-slate-50/80">
                <tr>
                    <th class="px-3 py-2 text-left text-[9px] font-semibold uppercase tracking-[0.2em] text-slate-500 lg:px-3 xl:px-4">Document</th>
                    <th class="px-3 py-2 text-left text-[9px] font-semibold uppercase tracking-[0.2em] text-slate-500 lg:px-3 xl:px-4">Sender</th>
                    <th class="px-3 py-2 text-center text-[9px] font-semibold uppercase tracking-[0.2em] text-slate-500 lg:px-3 xl:px-4">Status</th>
                    <th class="px-3 py-2 text-center text-[9px] font-semibold uppercase tracking-[0.2em] text-slate-500 lg:px-3 xl:px-4">Priority</th>
                    <th class="px-3 py-2 text-center text-[9px] font-semibold uppercase tracking-[0.2em] text-slate-500 lg:px-3 xl:px-4">Files</th>
                    <th class="px-3 py-2 text-right text-[9px] font-semibold uppercase tracking-[0.2em] text-slate-500 lg:px-3 xl:px-4">Due Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($documents as $doc): ?>
                    <tr class="align-middle <?= !empty($doc['is_deleted']) ? 'bg-slate-50 text-slate-500' : 'cursor-pointer transition-colors hover:bg-slate-50' ?>"
                        onclick="window.location.href='index.php?controller=StandardPortal&action=viewDocument&id=<?= (int)$doc['id'] ?>'">
                        <td class="px-3 py-1.5 align-middle lg:px-3 xl:px-4">
                            <div class="flex min-w-0 flex-col">
                                <span class="max-w-[300px] truncate font-semibold <?= !empty($doc['is_deleted']) ? 'text-slate-400' : 'text-slate-900' ?>"><?= htmlspecialchars($doc['title']) ?></span>
                                <span class="mt-0.5 font-mono text-[9px] <?= !empty($doc['is_deleted']) ? 'text-slate-400' : 'text-emerald-700' ?>">
                                    <?= htmlspecialchars($doc['tracking_id']) ?>
                                </span>
                                <?php if (!empty($doc['is_edited'])): ?>
                                    <span class="mt-1 inline-flex w-fit rounded-full border border-sky-200 bg-sky-50 px-2 py-0.5 text-[9px] font-semibold text-sky-700">Edited</span>
                                <?php endif; ?>
                                <?php if (!empty($doc['is_deleted'])): ?>
                                    <span class="mt-1 inline-flex w-fit rounded-full border border-rose-200 bg-rose-50 px-2 py-0.5 text-[9px] font-semibold text-rose-700">Removed</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-3 py-1.5 align-middle text-slate-500 lg:px-3 xl:px-4">
                            <div class="min-w-[170px] truncate font-medium <?= !empty($doc['is_deleted']) ? 'text-slate-400' : 'text-slate-500' ?>"><?= htmlspecialchars($doc['sender_email']) ?></div>
                        </td>
                        <td class="px-3 py-1.5 align-middle text-center lg:px-3 xl:px-4">
                            <div class="flex items-center justify-center">
                                <?= portalStatusBadge($doc['status'] ?? $doc['circulation_status']) ?>
                            </div>
                        </td>
                        <td class="px-3 py-1.5 align-middle text-center lg:px-3 xl:px-4">
                            <span class="inline-flex min-h-[20px] items-center justify-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-[9px] font-semibold text-slate-700">
                                <?= htmlspecialchars($doc['priority']) ?>
                            </span>
                        </td>
                        <td class="px-3 py-1.5 align-middle text-center font-semibold <?= !empty($doc['is_deleted']) ? 'text-slate-400' : 'text-slate-700' ?> lg:px-3 xl:px-4"><?= (int)$doc['attachment_count'] ?></td>
                        <td class="px-3 py-1.5 align-middle text-right whitespace-nowrap <?= !empty($doc['is_deleted']) ? 'text-slate-400' : 'text-slate-500' ?> lg:px-3 xl:px-4"><?= $doc['due_date'] ? date('M d, Y', strtotime($doc['due_date'])) : 'None' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    #portalInboxTable_wrapper .dataTables_length label,
    #portalInboxTable_wrapper .dataTables_filter label,
    #portalInboxTable_wrapper .dataTables_info {
        color: #4b5563;
        font-size: 0.875rem;
    }

    #portalInboxTable_wrapper .dataTables_filter input,
    #portalInboxTable_wrapper .dataTables_length select {
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        color: var(--secondary);
        font-size: 0.875rem;
        min-height: 2.5rem;
        outline: none;
    }

    #portalInboxTable_wrapper .dataTables_filter input {
        margin-left: 0;
        padding: 0 0.75rem;
        width: min(100%, 260px);
    }
</style>

<script>
$(document).ready(function() {
    $('#portalInboxTable').DataTable({
        pageLength: 25,
        order: [[3, 'asc'], [0, 'desc']],
        dom: '<"flex flex-col gap-3 border-b border-slate-200 bg-slate-50/70 px-4 py-3 sm:px-5 lg:px-6 md:flex-row md:items-center md:justify-between"lf>rt<"flex flex-col gap-3 border-t border-slate-200 bg-slate-50/70 px-4 py-3 sm:px-5 lg:px-6 md:flex-row md:items-center md:justify-between"ip>',
        language: {
            search: '',
            searchPlaceholder: 'Search correspondence'
        }
    });

    $('#showRemovedItems').on('change', function() {
        fetch('index.php?controller=StandardPortal&action=setRemovedItemsPreference', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                enabled: this.checked ? '1' : '0'
            })
        }).finally(() => window.location.reload());
    });
});
</script>

<?php portalFooter(); ?>
