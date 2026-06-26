<?php require __DIR__ . '/partials.php'; ?>
<?php portalHeader('Dashboard'); ?>
<?php portalFlash(); ?>

<?php
$total = (int)($stats['total'] ?? 0);
$received = (int)($stats['received'] ?? 0);
$pending = (int)($stats['pending'] ?? 0);
$ccTotal = (int)($stats['cc_total'] ?? 0);
?>

<div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-5">
    <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-slate-500">Assigned</p>
        <p class="text-2xl font-semibold text-slate-900 mt-2"><?= $total ?></p>
    </div>
    <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-slate-500">Pending</p>
        <p class="text-2xl font-semibold text-amber-600 mt-2"><?= $pending ?></p>
    </div>
    <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-slate-500">Received</p>
        <p class="text-2xl font-semibold text-emerald-700 mt-2"><?= $received ?></p>
    </div>
    <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-slate-500">CC</p>
        <p class="text-2xl font-semibold text-slate-700 mt-2"><?= $ccTotal ?></p>
    </div>
</div>

<div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
    <div class="px-4 py-3 border-b border-slate-200 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-emerald-700">Recent</p>
            <h2 class="text-lg font-semibold text-slate-900">Latest Correspondence</h2>
        </div>
        <a href="index.php?controller=StandardPortal&action=inbox" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">View all</a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-3 py-2 text-left font-semibold uppercase tracking-[0.2em] text-slate-500">Tracking</th>
                    <th class="px-3 py-2 text-left font-semibold uppercase tracking-[0.2em] text-slate-500">Title</th>
                    <th class="px-3 py-2 text-center font-semibold uppercase tracking-[0.2em] text-slate-500">Status</th>
                    <th class="px-3 py-2 text-left font-semibold uppercase tracking-[0.2em] text-slate-500">Due</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($documents as $doc): ?>
                    <tr class="hover:bg-emerald-50/70 cursor-pointer transition-colors" onclick="window.location.href='index.php?controller=StandardPortal&action=viewDocument&id=<?= (int)$doc['id'] ?>'">
                        <td class="px-3 py-2 font-mono text-emerald-700"><?= htmlspecialchars($doc['tracking_id']) ?></td>
                        <td class="px-3 py-2 font-semibold text-slate-900"><?= htmlspecialchars($doc['title']) ?></td>
                        <td class="px-3 py-2 text-center"><?= portalStatusBadge($doc['circulation_status']) ?></td>
                        <td class="px-3 py-2 text-slate-500"><?= $doc['due_date'] ? date('M d, Y', strtotime($doc['due_date'])) : 'None' ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($documents)): ?>
                    <tr><td colspan="4" class="px-3 py-8 text-center text-slate-500">No assigned correspondence yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php portalFooter(); ?>
