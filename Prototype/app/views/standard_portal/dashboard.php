<?php require __DIR__ . '/partials.php'; ?>
<?php portalHeader('Dashboard'); ?>
<?php portalFlash(); ?>

<?php
$total = (int)($stats['total'] ?? 0);
$received = (int)($stats['received'] ?? 0);
$pending = (int)($stats['pending'] ?? 0);
$ccTotal = (int)($stats['cc_total'] ?? 0);
?>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase text-gray-500">Assigned</p>
        <p class="text-3xl font-semibold text-gray-900 mt-2"><?= $total ?></p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase text-gray-500">Pending</p>
        <p class="text-3xl font-semibold text-amber-600 mt-2"><?= $pending ?></p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase text-gray-500">Received</p>
        <p class="text-3xl font-semibold text-green-600 mt-2"><?= $received ?></p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase text-gray-500">CC</p>
        <p class="text-3xl font-semibold text-blue-600 mt-2"><?= $ccTotal ?></p>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-200 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase text-blue-600">Recent</p>
            <h2 class="text-xl font-semibold text-gray-900">Latest Correspondence</h2>
        </div>
        <a href="index.php?controller=StandardPortal&action=inbox" class="text-sm font-semibold text-blue-600 hover:text-blue-700">View all</a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Tracking</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Title</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Due</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($documents as $doc): ?>
                    <tr class="hover:bg-blue-50/60 cursor-pointer" onclick="window.location.href='index.php?controller=StandardPortal&action=viewDocument&id=<?= (int)$doc['id'] ?>'">
                        <td class="px-4 py-3 font-mono text-blue-700"><?= htmlspecialchars($doc['tracking_id']) ?></td>
                        <td class="px-4 py-3 font-semibold text-gray-900"><?= htmlspecialchars($doc['title']) ?></td>
                        <td class="px-4 py-3 text-center"><?= portalStatusBadge($doc['circulation_status']) ?></td>
                        <td class="px-4 py-3 text-gray-500"><?= $doc['due_date'] ? date('M d, Y', strtotime($doc['due_date'])) : 'None' ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($documents)): ?>
                    <tr><td colspan="4" class="px-4 py-10 text-center text-gray-500">No assigned correspondence yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php portalFooter(); ?>
