<!-- Shared modals, drawers, and scripts for correspondence views -->
<!-- Enhanced Modal -->
<div id="documentModal" class="hidden fixed inset-0 z-50 p-2 md:p-4 lg:p-6">
    <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm" onclick="closeModal()"></div>
    <div class="relative mx-auto flex h-full w-full max-w-6xl md:max-w-5xl lg:max-w-7xl items-center justify-center">
        <div class="modal-panel w-full max-h-[96vh] md:max-h-[94vh] overflow-hidden rounded-lg md:rounded-xl border border-white/10 bg-white shadow-[0_30px_120px_rgba(15,23,42,0.35)]">
            <div class="flex items-start justify-between gap-2 md:gap-4 border-b border-slate-300 bg-white/90 px-3 md:px-4 lg:px-6 py-2 md:py-3 lg:py-4">
                <div class="min-w-0">
                    <p class="text-[10px] md:text-[11px] font-semibold uppercase tracking-[0.22em] text-blue-600">Document Preview</p>
                    <h3 id="modalTitle" class="mt-0.5 md:mt-1 text-base md:text-lg lg:text-2xl font-semibold text-slate-900 truncate"></h3>
                </div>
                <button onclick="closeModal()" class="inline-flex h-8 md:h-10 w-8 md:w-10 flex-shrink-0 items-center justify-center rounded-full border border-slate-300 text-slate-500 transition hover:border-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="Close modal">
                    <span class="text-lg md:text-2xl leading-none">×</span>
                </button>
            </div>
            <div id="modalBody" class="max-h-[calc(96vh-50px)] md:max-h-[calc(94vh-60px)] overflow-auto px-3 md:px-4 lg:px-6 py-2 md:py-3 lg:py-6"></div>
        </div>
    </div>
</div>

<!-- Recipient / CC Drawer (side modal) -->
<div id="recipientDrawerOverlay" class="hidden fixed inset-0 bg-slate-950/50 z-[99998]" onclick="closeRecipientDrawer()"></div>
<aside id="recipientDrawer" class="hidden fixed inset-y-0 right-0 w-full sm:w-80 bg-white shadow-2xl z-[99999] transform translate-x-full transition-transform">
    <div class="flex h-full flex-col">
        <div class="flex items-center justify-between px-3 md:px-4 py-2 md:py-3 border-b border-gray-100">
            <div>
                <p id="drawerTitleSmall" class="text-[10px] md:text-[11px] font-semibold uppercase tracking-[0.18em] text-blue-600">Add Recipients</p>
                <h4 id="drawerTitle" class="mt-0.5 md:mt-1 text-base md:text-lg font-semibold text-slate-900">Select people</h4>
            </div>
            <div class="flex items-center gap-1 md:gap-2">
                <button type="button" onclick="closeRecipientDrawer()" class="inline-flex h-8 md:h-9 w-8 md:w-9 items-center justify-center rounded-full border border-slate-200 text-slate-500 hover:bg-slate-50">×</button>
            </div>
        </div>
        <div class="p-2 md:p-3 flex-1 overflow-auto">
            <div class="mb-2 md:mb-3">
                <input id="drawer-search" type="search" placeholder="Filter by department, name, or email" class="w-full rounded-lg md:rounded-xl border border-slate-200 px-2 md:px-3 py-1.5 md:py-2 text-xs md:text-sm text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            </div>
            <div id="drawerList" class="space-y-1 md:space-y-1.5">
                <?php foreach ($selectableUsers as $user):
                    $department = trim($user['department'] ?? '') !== '' ? trim($user['department']) : 'Unassigned';
                    $fullName = trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? ''));
                    $email = trim($user['email'] ?? '');
                    $userId = (int)$user['id'];
                    $filterText = htmlspecialchars(strtolower(trim($department . ' ' . $fullName . ' ' . $email)));
                ?>
                <label data-drawer-filter="<?= $filterText ?>" class="group flex items-start gap-2 md:gap-3 rounded-lg md:rounded-xl px-2 md:px-3 py-1.5 md:py-2 transition hover:bg-slate-50">
                    <input type="checkbox" data-drawer-id="<?= $userId ?>" data-drawer-name="<?= htmlspecialchars($fullName ?: $department) ?>" data-drawer-email="<?= htmlspecialchars($email) ?>" class="mt-1 h-3.5 md:h-4 w-3.5 md:w-4 rounded border-slate-300 text-blue-600">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5 md:gap-2">
                            <span class="text-xs md:text-sm font-semibold text-slate-900"><?= htmlspecialchars($department) ?></span>
                            <?php if ($fullName !== ''): ?>
                                <span class="user-fullname rounded-full bg-slate-100 px-1.5 md:px-2 py-0.5 text-[10px] md:text-[11px] font-medium text-slate-600"><?= htmlspecialchars($fullName) ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="mt-0.5 md:mt-1 text-xs md:text-sm text-slate-500"><?= htmlspecialchars($email !== '' ? $email : 'No email') ?></p>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="flex items-center justify-between gap-2 md:gap-3 border-t border-gray-100 px-3 md:px-4 py-2 md:py-3">
            <button type="button" onclick="closeRecipientDrawer()" class="inline-flex items-center justify-center rounded-lg md:rounded-xl border border-slate-200 bg-white px-3 md:px-4 py-1.5 md:py-2 text-xs md:text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
            <div>
                <button id="drawerApply" type="button" onclick="applyRecipientDrawer()" class="inline-flex items-center justify-center rounded-lg md:rounded-xl bg-blue-600 px-3 md:px-4 py-1.5 md:py-2 text-xs md:text-sm font-semibold text-white hover:bg-blue-700">Apply</button>
            </div>
        </div>
    </div>
</aside>

<div id="editDocumentModal" class="hidden fixed inset-0 z-[60] p-2 md:p-3 lg:p-4">
     
    <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm" onclick="closeEditModal()"></div>
    <div class="relative mx-auto flex h-full w-full max-w-4xl items-center justify-center">
        <div class="modal-panel w-full max-h-[94vh] overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_20px_80px_rgba(15,23,42,0.24)] flex flex-col">
            <div class="flex items-start justify-between gap-2 border-b border-slate-200 bg-white px-4 py-3 flex-shrink-0">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-600">Edit Document</p>
                    <h3 class="mt-0.5 text-base font-semibold text-slate-900">Update details</h3>
                </div>
                <button onclick="closeEditModal()" class="inline-flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-700" aria-label="Close edit modal">
                    <span class="text-lg leading-none">×</span>
                </button>
            </div>
            <div id="editModalBody" class="flex-1 overflow-auto px-4 py-3"></div>
            <div id="editModalFooter" class="border-t border-slate-200 bg-slate-50/70 px-4 py-3 flex-shrink-0"></div>
        </div>
    </div>
</div>

<div id="confirmModal" class="hidden fixed inset-0 z-[70] p-2 md:p-4 lg:p-6">
    <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm" onclick="closeConfirmModal()"></div>
    <div class="relative mx-auto flex h-full w-full max-w-sm md:max-w-md items-center justify-center">
        <div class="modal-panel w-full rounded-lg md:rounded-xl border border-white/10 bg-white shadow-[0_30px_120px_rgba(15,23,42,0.35)]">
            <div class="border-b border-slate-300 px-3 md:px-4 lg:px-6 py-2 md:py-3 lg:py-4">
                <p class="text-[10px] md:text-[11px] font-semibold uppercase tracking-[0.22em] text-red-600">Confirm Action</p>
                <h3 id="confirmTitle" class="mt-0.5 md:mt-1 text-base md:text-lg font-semibold text-slate-900">Confirm</h3>
            </div>
            <div class="px-3 md:px-4 lg:px-6 py-3 md:py-4 lg:py-5">
                <p id="confirmMessage" class="text-xs md:text-sm leading-6 text-slate-600"></p>
                <div class="mt-4 md:mt-6 flex flex-col-reverse gap-2 md:gap-3 sm:flex-row sm:justify-end">
                    <button type="button" onclick="closeConfirmModal()" class="inline-flex items-center justify-center rounded-lg md:rounded-xl border border-slate-300 bg-white px-3 md:px-4 py-2 md:py-2.5 lg:py-3 text-xs md:text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="button" id="confirmActionButton" class="inline-flex items-center justify-center rounded-lg md:rounded-xl bg-red-600 px-3 md:px-4 py-2 md:py-2.5 lg:py-3 text-xs md:text-sm font-semibold text-white hover:bg-red-700">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="hiddenDeleteForm" method="POST" class="hidden"></form>
<form id="hiddenHardDeleteForm" method="POST" class="hidden"></form>

<!-- scripts/styles used by correspondence views -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

<?php // the rest of the JS/CSS from index.php is intentionally kept in the main view to avoid duplication ?>
