<?php require __DIR__ . '/partials.php'; ?>
<?php portalHeader('Correspondence'); ?>
<?php portalFlash(); ?>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase text-blue-600">Inbox</p>
            <h2 class="text-xl font-semibold text-gray-900">Correspondence to Receive</h2>
        </div>
        <span class="inline-flex w-fit rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-medium text-gray-600">
            <?= count($documents) ?> assigned document(s)
        </span>
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
                    <tr class="hover:bg-blue-50/60 cursor-pointer transition-colors"
                        onclick="window.location.href='index.php?controller=StandardPortal&action=viewDocument&id=<?= (int)$doc['id'] ?>'">
                        <td class="px-4 py-3 font-mono text-blue-700 whitespace-nowrap"><?= htmlspecialchars($doc['tracking_id']) ?></td>
                        <td class="px-4 py-3 font-semibold text-gray-900 min-w-[240px]"><?= htmlspecialchars($doc['title']) ?></td>
                        <td class="px-4 py-3 text-gray-600 min-w-[180px]"><?= htmlspecialchars($doc['sender_email']) ?></td>
                        <td class="px-4 py-3 text-center"><?= portalStatusBadge($doc['circulation_status']) ?></td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex rounded-md border px-2.5 py-1 text-xs font-semibold bg-gray-50 text-gray-700 border-gray-100">
                                <?= htmlspecialchars($doc['priority']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center font-semibold text-gray-700"><?= (int)$doc['attachment_count'] ?></td>
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap"><?= $doc['due_date'] ? date('M d, Y', strtotime($doc['due_date'])) : 'None' ?></td>
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
        color: #111827;
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
});
</script>

<?php portalFooter(); ?>
