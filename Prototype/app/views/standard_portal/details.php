<?php require __DIR__ . '/partials.php'; ?>
<?php portalHeader('Document Details'); ?>
<?php portalFlash(); ?>

<div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_340px] gap-6">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 flex flex-col md:flex-row md:items-start md:justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-600"><?= htmlspecialchars($document['tracking_id']) ?></p>
                <h2 class="text-2xl font-semibold text-gray-900 mt-1"><?= htmlspecialchars($document['title']) ?></h2>
                <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($document['type']) ?> • <?= htmlspecialchars($document['sender_email']) ?></p>
            </div>
            <?= portalStatusBadge($document['circulation_status']) ?>
        </div>

        <div class="p-5 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                    <p class="text-xs font-semibold uppercase text-gray-500">Priority</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1"><?= htmlspecialchars($document['priority']) ?></p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                    <p class="text-xs font-semibold uppercase text-gray-500">Due Date</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1"><?= $document['due_date'] ? date('M d, Y', strtotime($document['due_date'])) : 'None' ?></p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                    <p class="text-xs font-semibold uppercase text-gray-500">Assigned As</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1"><?= !empty($document['cc']) ? 'CC' : 'Recipient' ?></p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                    <p class="text-xs font-semibold uppercase text-gray-500">Received At</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1"><?= $document['received_at'] ? date('M d, Y g:i A', strtotime($document['received_at'])) : 'Pending' ?></p>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-900 mb-2">Description</h3>
                <div class="rounded-lg border border-gray-200 p-4 text-sm text-gray-700 leading-relaxed bg-white">
                    <?= nl2br(htmlspecialchars(strip_tags($document['description'] ?? ''))) ?>
                </div>
            </div>

            <?php if (!empty($document['notes'])): ?>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Notes</h3>
                    <div class="rounded-lg border border-gray-200 p-4 text-sm text-gray-700 bg-gray-50">
                        <?= nl2br(htmlspecialchars($document['notes'])) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200">
                <p class="text-xs font-semibold uppercase text-blue-600">Files</p>
                <h3 class="text-lg font-semibold text-gray-900">Attachments</h3>
            </div>
            <div class="p-5 space-y-3">
                <?php foreach ($attachments as $file): ?>
                    <a href="index.php?controller=StandardPortal&action=download&id=<?= (int)$file['id'] ?>"
                       class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 px-3 py-3 hover:bg-blue-50 hover:border-blue-100 transition">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate"><?= htmlspecialchars($file['file_name']) ?></p>
                            <p class="text-xs text-gray-500"><?= number_format(((int)($file['file_size'] ?? 0)) / 1024, 1) ?> KB</p>
                        </div>
                        <span class="text-sm font-semibold text-blue-600">Download</span>
                    </a>
                <?php endforeach; ?>
                <?php if (empty($attachments)): ?>
                    <p class="text-sm text-gray-500">No attachments uploaded.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200">
                <p class="text-xs font-semibold uppercase text-blue-600">Acknowledgement</p>
                <h3 class="text-lg font-semibold text-gray-900">Receive Document</h3>
            </div>

            <form method="POST" action="index.php?controller=StandardPortal&action=receive&id=<?= (int)$document['id'] ?>" class="p-5 space-y-4">
                <?php if ($document['circulation_status'] === 'Received'): ?>
                    <div class="rounded-lg border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                        This document has already been marked as received.
                    </div>
                <?php endif; ?>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Assigned PIN Code</label>
                    <input type="password" name="pin_code" required <?= $document['circulation_status'] === 'Received' ? 'disabled' : '' ?>
                           class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Remarks</label>
                    <textarea name="remarks" rows="3" <?= $document['circulation_status'] === 'Received' ? 'disabled' : '' ?>
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                </div>

                <button type="submit" <?= $document['circulation_status'] === 'Received' ? 'disabled' : '' ?>
                        class="w-full h-10 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    Mark as Received
                </button>
            </form>
        </div>
    </div>
</div>

<?php portalFooter(); ?>
