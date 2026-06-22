<?php require __DIR__ . '/partials.php'; ?>
<?php portalHeader('Correspondence'); ?>
<?php portalFlash(); ?>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-200 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase text-blue-600">Inbox</p>
            <h2 class="text-xl font-semibold text-gray-900">Correspondence to Receive</h2>
        </div>
        <div class="flex items-center gap-3">
            <label class="inline-flex items-center gap-3 rounded-full border border-slate-200 bg-white px-4 py-2 shadow-sm shadow-slate-100">
                <span class="h-2.5 w-2.5 rounded-full <?= !empty($showRemovedItems) ? 'bg-amber-500' : 'bg-slate-300' ?>"></span>
                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Show removed</span>
                <input type="checkbox" id="showRemovedItems" <?= !empty($showRemovedItems) ? 'checked' : '' ?> class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
            </label>
            <span class="inline-flex w-fit rounded-full border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-medium text-gray-600">
                <?= count($documents) ?> assigned document(s)
            </span>
        </div>
    </div>

    <div class="p-5 overflow-x-auto">
        <table id="portalInboxTable" class="w-full text-sm">
            <thead class="bg-gray-50 border-y border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Tracking ID</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Title</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Sender</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500">Priority</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500">Files</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Due Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($documents as $doc): ?>
                    <tr class="<?= !empty($doc['is_deleted']) ? 'bg-slate-50 text-slate-500' : '' ?> hover:bg-slate-50 cursor-pointer transition-colors"
                        onclick="window.location.href='index.php?controller=StandardPortal&action=viewDocument&id=<?= (int)$doc['id'] ?>'">
                        <td class="px-4 py-3 font-mono <?= !empty($doc['is_deleted']) ? 'text-slate-400' : 'text-blue-700' ?> whitespace-nowrap"><?= htmlspecialchars($doc['tracking_id']) ?></td>
                        <td class="px-4 py-3 font-semibold min-w-[240px] <?= !empty($doc['is_deleted']) ? 'text-slate-500' : 'text-gray-900' ?>">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="<?= !empty($doc['is_deleted']) ? 'text-slate-400' : '' ?>"><?= htmlspecialchars($doc['title']) ?></span>
                                <?php if (!empty($doc['is_edited'])): ?>
                                    <span class="inline-flex rounded-full border border-sky-200 bg-sky-50 px-2 py-0.5 text-[11px] font-semibold text-sky-700">Edited</span>
                                <?php endif; ?>
                                <?php if (!empty($doc['is_deleted'])): ?>
                                    <span class="inline-flex rounded-full border border-rose-200 bg-rose-50 px-2 py-0.5 text-[11px] font-semibold text-rose-700">Removed</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-4 py-3 <?= !empty($doc['is_deleted']) ? 'text-slate-400' : 'text-gray-600' ?> min-w-[180px]"><?= htmlspecialchars($doc['sender_email']) ?></td>
                        <td class="px-4 py-3 text-center"><?= portalStatusBadge($doc['circulation_status']) ?></td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex rounded-md border px-2.5 py-1 text-xs font-semibold bg-slate-50 text-slate-700 border-slate-100">
                                <?= htmlspecialchars($doc['priority']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center font-semibold <?= !empty($doc['is_deleted']) ? 'text-slate-400' : 'text-gray-700' ?>"><?= (int)$doc['attachment_count'] ?></td>
                        <td class="px-4 py-3 <?= !empty($doc['is_deleted']) ? 'text-slate-400' : 'text-gray-500' ?> whitespace-nowrap"><?= $doc['due_date'] ? date('M d, Y', strtotime($doc['due_date'])) : 'None' ?></td>
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
        dom: '<"flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4"lf>rt<"flex flex-col md:flex-row md:items-center md:justify-between gap-3 mt-4"ip>',
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
