<div class="min-h-screen bg-gray-50">
    <div class="max-w-[1600px] mx-auto px-4 py-8">
        <?php
            $currentUserLevel = (int)($_SESSION['user_level'] ?? 3);
            $isSuperAdmin = $currentUserLevel === 0;
            $canCreateCorrespondence = !empty($canCreateCorrespondence) || in_array($currentUserLevel, [0, 1, 2], true);
            $canEditCorrespondence = !empty($canEditCorrespondence) || in_array($currentUserLevel, [0, 1, 2], true);
            $canDeleteCorrespondence = !empty($canDeleteCorrespondence) || in_array($currentUserLevel, [0, 1], true);
            $canHardDeleteCorrespondence = !empty($canHardDeleteCorrespondence) || $isSuperAdmin;
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

        <!-- ====================== NEW CIRCULATION FORM ====================== -->
        <?php if ($canCreateCorrespondence): ?>
            <div class="mb-8 overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-[0_18px_60px_rgba(15,23,42,0.08)]">
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-slate-50 px-6 py-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="max-w-3xl">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-blue-600">Digital Correspondence</p>
                            <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">New Circulation</h2>
                            <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-500">
                                Keep the entry focused: capture the document details first, then assign recipients and CC with the least friction.
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-sm">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                <span>Role-enabled workflow</span>
                            </div>
                            <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-sm">
                                <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                <span>Live recipient filters</span>
                            </div>
                        </div>
                    </div>
                </div>

                <form id="circulationForm" action="index.php?controller=correspondence&action=store" method="POST" enctype="multipart/form-data" class="p-6 lg:p-7">
                    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_460px] gap-6">
                        <div class="space-y-6">
                            <section class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5 shadow-sm">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Document Identity</p>
                                        <h3 class="mt-1 text-base font-semibold text-slate-900">Core details</h3>
                                    </div>
                                    <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Required</span>
                                </div>

                                <div class="mt-4 grid grid-cols-1 md:grid-cols-[190px_minmax(0,1fr)] gap-4">
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 mb-1.5">Tracking ID</label>
                                    <input type="text" id="tracking_id" name="tracking_id"
                                        value="<?= htmlspecialchars($nextTrackingId) ?>"
                                        class="w-full h-11 rounded-2xl border border-slate-200 bg-slate-50 px-3 font-mono text-sm font-semibold text-slate-700" readonly>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 mb-1.5">Document Title <span class="text-red-500">*</span></label>
                                    <input type="text" name="title" required placeholder="Enter document title"
                                        class="w-full h-11 rounded-2xl border border-slate-200 px-4 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                </div>
                            </div>
                            </section>

                            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Document Metadata</p>
                                    <h3 class="mt-1 text-base font-semibold text-slate-900">Classification and timing</h3>
                                </div>

                                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Type</label>
                                    <select name="type" class="w-full h-11 rounded-2xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">
                                        <option value="Memo">Memo</option>
                                        <option value="Letter">Letter</option>
                                        <option value="Report">Report</option>
                                        <option value="Circular">Circular</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Priority</label>
                                    <select name="priority" class="w-full h-11 rounded-2xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">
                                        <option value="Low">Low</option>
                                        <option value="Medium" selected>Medium</option>
                                        <option value="High">High</option>
                                        <option value="Urgent">Urgent</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Due Date</label>
                                    <input type="date" name="due_date" class="w-full h-11 rounded-2xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Sender</label>
                                    <input type="email" name="sender_email" value="noreply@dalton.com.ph"
                                        class="w-full h-11 rounded-2xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">
                                </div>
                            </div>
                            </section>

                            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Document Body</p>
                                    <h3 class="mt-1 text-base font-semibold text-slate-900">Description / remarks</h3>
                                </div>
                                <div class="mt-4">
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Body</label>
                                <div id="description" contenteditable="true"
                                    class="min-h-[190px] rounded-3xl border border-slate-200 bg-slate-50 p-4 text-sm leading-7 text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100 prose max-w-none"></div>
                                <input type="hidden" name="description" id="description-hidden">
                                </div>
                            </section>

                            <section class="grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_220px] gap-4">
                                <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-4 shadow-sm">
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Attachments</label>
                                    <p class="mb-3 text-sm text-slate-500">Add up to 4 files, 40MB total.</p>
                                    <input type="file" name="attachments[]" multiple data-max-files="4" data-max-total="41943040"
                                        class="w-full rounded-2xl border border-dashed border-slate-300 bg-white px-3 py-3 text-sm file:mr-3 file:border-0 file:bg-slate-100 file:text-slate-700 file:rounded-lg file:px-3 file:py-1.5 hover:file:bg-slate-200">
                                    <p id="attachment-feedback" class="mt-2 text-xs font-medium text-slate-500">No files selected.</p>
                                </div>
                                <label for="confidential" class="flex items-start gap-3 rounded-3xl border border-slate-200 bg-white px-4 py-4 shadow-sm transition hover:border-blue-200">
                                    <input type="checkbox" name="is_confidential" id="confidential" class="mt-1 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <div>
                                        <span class="block text-sm font-semibold text-slate-800">Confidential</span>
                                        <span class="mt-1 block text-sm leading-6 text-slate-500">Limit visibility for sensitive circulations.</span>
                                    </div>
                                </label>
                            </section>

                            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Internal Notes</p>
                                    <h3 class="mt-1 text-base font-semibold text-slate-900">Remarks</h3>
                                </div>
                                <div class="mt-4">
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Notes / Remarks</label>
                                <textarea name="notes" rows="2" placeholder="Optional internal notes"
                                    class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100"></textarea>
                                </div>
                            </section>
                        </div>

                        <div class="space-y-4">
                            <section class="rounded-3xl border border-slate-200 bg-gradient-to-b from-slate-50 to-white p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Recipients</label>
                                        <p class="mt-1 text-sm text-slate-500">Choose the people who should receive the circulation.</p>
                                    </div>
                                    <span id="recipients-count" class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 border border-slate-200">0 selected</span>
                                </div>
                                <div class="mt-3">
                                    <input type="search" data-selection-filter="recipients" placeholder="Filter by department, name, or email"
                                        class="w-full h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                </div>
                                <div class="mt-4 max-h-[360px] overflow-auto pr-1 space-y-2" data-selection-list="recipients">
                                    <?php foreach ($selectableUsers as $user): ?>
                                        <?php
                                            $department = trim($user['department'] ?? '') !== '' ? trim($user['department']) : 'Unassigned';
                                            $fullName = trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? ''));
                                            $email = trim($user['email'] ?? '');
                                            $filterText = strtolower(trim($department . ' ' . $fullName . ' ' . $email));
                                        ?>
                                        <label class="group flex items-start gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 transition hover:border-blue-300 hover:bg-blue-50/60" data-user-card data-user-filter="<?= htmlspecialchars($filterText) ?>">
                                            <input type="checkbox" name="recipients[]" value="<?= (int)$user['id'] ?>"
                                                data-selection-section="recipients"
                                                data-user-id="<?= (int)$user['id'] ?>"
                                                class="mt-1 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                            <div class="min-w-0 flex-1">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($department) ?></span>
                                                    <?php if ($fullName !== ''): ?>
                                                        <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-medium text-slate-600"><?= htmlspecialchars($fullName) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <p class="mt-1 text-sm text-slate-500"><?= htmlspecialchars($email !== '' ? $email : 'No email on file') ?></p>
                                            </div>
                                        </label>
                                    <?php endforeach; ?>
                                    <?php if (empty($selectableUsers)): ?>
                                        <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-8 text-center text-sm text-slate-500">
                                            No active users available.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </section>

                            <section class="rounded-3xl border border-slate-200 bg-gradient-to-b from-white to-slate-50 p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">CC</label>
                                        <p class="mt-1 text-sm text-slate-500">Carbon-copy recipients stay separate from the main recipient list.</p>
                                    </div>
                                    <span id="cc-count" class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 border border-slate-200">0 selected</span>
                                </div>
                                <div class="mt-3">
                                    <input type="search" data-selection-filter="cc" placeholder="Filter by department, name, or email"
                                        class="w-full h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                </div>
                                <div class="mt-4 max-h-[360px] overflow-auto pr-1 space-y-2" data-selection-list="cc">
                                    <?php foreach ($selectableUsers as $user): ?>
                                        <?php
                                            $department = trim($user['department'] ?? '') !== '' ? trim($user['department']) : 'Unassigned';
                                            $fullName = trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? ''));
                                            $email = trim($user['email'] ?? '');
                                            $filterText = strtolower(trim($department . ' ' . $fullName . ' ' . $email));
                                        ?>
                                        <label class="group flex items-start gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 transition hover:border-blue-300 hover:bg-blue-50/60" data-user-card data-user-filter="<?= htmlspecialchars($filterText) ?>">
                                            <input type="checkbox" name="cc[]" value="<?= (int)$user['id'] ?>"
                                                data-selection-section="cc"
                                                data-user-id="<?= (int)$user['id'] ?>"
                                                class="mt-1 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                            <div class="min-w-0 flex-1">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($department) ?></span>
                                                    <?php if ($fullName !== ''): ?>
                                                        <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-medium text-slate-600"><?= htmlspecialchars($fullName) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <p class="mt-1 text-sm text-slate-500"><?= htmlspecialchars($email !== '' ? $email : 'No email on file') ?></p>
                                            </div>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </section>

                            <div class="flex flex-col-reverse sm:flex-row xl:flex-col-reverse gap-3 pt-1">
                                <button type="button" onclick="resetForm()"
                                    class="h-11 px-4 rounded-2xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Reset</button>
                                <button type="submit"
                                    class="h-11 px-5 rounded-2xl bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700">Circulate Document</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="mb-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="rounded-3xl border border-slate-200 bg-slate-50 px-5 py-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-600">View Only</p>
                    <h2 class="mt-1 text-xl font-semibold text-slate-900">New Circulation is disabled for your account</h2>
                    <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-600">
                        You can review correspondence history and repository records, but creating, editing, and deleting circulation entries are restricted by your role.
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <!-- ====================== DOCUMENT REPOSITORY ====================== -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200 bg-white">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase text-blue-600">Repository</p>
                        <h2 class="text-xl font-semibold text-gray-900 mt-0.5">Document Repository</h2>
                    </div>
                    <span class="inline-flex w-fit items-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-medium text-gray-600">
                        <?= count($documents) ?> document(s)
                    </span>
                </div>

                <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50/80 p-4 shadow-sm shadow-slate-100">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-[200px_200px_minmax(0,1fr)] gap-3 flex-1">
                            <div>
                                <label for="priorityFilter" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Priority</label>
                                <select id="priorityFilter" class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    <option value="">All priorities</option>
                                    <option value="Low">Low</option>
                                    <option value="Medium">Medium</option>
                                    <option value="High">High</option>
                                    <option value="Urgent">Urgent</option>
                                </select>
                            </div>
                            <div>
                                <label for="statusFilter" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Status</label>
                                <select id="statusFilter" class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    <option value="">All statuses</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Completed">Completed</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <p class="text-xs leading-5 text-slate-500">
                                    Filters apply instantly. Keep the table clean by narrowing to priority, status, or removed items.
                                </p>
                            </div>
                        </div>
                        <div class="xl:min-w-[220px]">
                            <label class="inline-flex w-full items-center justify-between gap-3 rounded-full border border-slate-200 bg-white px-4 py-2.5 shadow-sm shadow-slate-100 transition hover:border-slate-300">
                                <span class="inline-flex items-center gap-2 min-w-0">
                                    <span class="h-2.5 w-2.5 rounded-full <?= !empty($showRemovedItems) ? 'bg-amber-500' : 'bg-slate-300' ?>"></span>
                                    <span class="truncate text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Show removed</span>
                                </span>
                                <input type="checkbox" id="showRemovedItems" <?= !empty($showRemovedItems) ? 'checked' : '' ?> class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-5 overflow-x-auto">
                <table id="documentTable" class="w-full text-sm">
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
                            $status = ($totalRecipients > 0 && $receivedCount >= $totalRecipients) ? 'Completed' : 'Pending';
                            $isDeleted = !empty($doc['is_deleted']);
                            $isEdited = !empty($doc['is_edited']);
                            $canManage = false;
                            if (!empty($doc['created_by']) && (int)$doc['created_by'] === (int)($_SESSION['id'] ?? 0) && !$isDeleted && !empty($doc['created_at'])) {
                                $deadline = strtotime($doc['created_at'] . ' +7 days');
                                $canManage = $deadline !== false && time() <= $deadline;
                            }
                            $manageUntil = !empty($doc['created_at']) ? date('M d, Y g:i A', strtotime($doc['created_at'] . ' +7 days')) : null;
                            $statusClass = $status === 'Completed'
                                ? 'bg-green-50 text-green-700 border-green-100'
                                : 'bg-amber-50 text-amber-700 border-amber-100';
                            $priorityClass = match ($doc['priority']) {
                                'Urgent' => 'bg-red-50 text-red-700 border-red-100',
                                'High' => 'bg-orange-50 text-orange-700 border-orange-100',
                                'Medium' => 'bg-blue-50 text-blue-700 border-blue-100',
                                default => 'bg-gray-50 text-gray-700 border-gray-100',
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
                                <?php if ($manageUntil && !$isDeleted): ?>
                                    <p class="mt-1 text-[11px] text-slate-500">Manage until <?= htmlspecialchars($manageUntil) ?></p>
                                <?php endif; ?>
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
                                     <button type="button" onclick='openEditDocument(<?= $doc["id"] ?>, <?= json_encode($doc["title"], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                        <?= (!$canEditCorrespondence || $isDeleted) ? 'disabled' : '' ?>
                                        class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 hover:border-amber-200 hover:bg-amber-50 hover:text-amber-700 transition disabled:cursor-not-allowed disabled:opacity-40"
                                        title="Edit document">
                                        Edit
                                    </button>
                                    <button type="button" onclick='openDeleteConfirm(<?= $doc["id"] ?>, <?= json_encode($doc["title"], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                        <?= (!$canDeleteCorrespondence || $isDeleted) ? 'disabled' : '' ?>
                                        class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50 px-3 text-xs font-semibold text-rose-700 hover:bg-rose-100 transition disabled:cursor-not-allowed disabled:opacity-40"
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
        </div>


<!-- Enhanced Modal -->
<div id="documentModal" class="hidden fixed inset-0 z-50 p-4 sm:p-6">
    <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm" onclick="closeModal()"></div>
    <div class="relative mx-auto flex h-full w-full max-w-7xl items-center justify-center">
        <div class="modal-panel w-full max-h-[94vh] overflow-hidden rounded-3xl border border-white/10 bg-white shadow-[0_30px_120px_rgba(15,23,42,0.35)]">
            <div class="flex items-start justify-between gap-4 border-b border-slate-200 bg-white/90 px-5 py-4 sm:px-6">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-blue-600">Document Preview</p>
                    <h3 id="modalTitle" class="mt-1 text-lg font-semibold text-slate-900 sm:text-2xl"></h3>
                </div>
                <button onclick="closeModal()" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:bg-slate-100 hover:text-slate-700" aria-label="Close modal">
                    <span class="text-2xl leading-none">×</span>
                </button>
            </div>
            <div id="modalBody" class="max-h-[calc(94vh-72px)] overflow-auto px-4 py-4 sm:px-6 sm:py-6"></div>
        </div>
    </div>
</div>

<div id="editDocumentModal" class="hidden fixed inset-0 z-[60] p-4 sm:p-6">
    <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm" onclick="closeEditModal()"></div>
    <div class="relative mx-auto flex h-full w-full max-w-4xl items-center justify-center">
        <div class="modal-panel w-full max-h-[94vh] overflow-hidden rounded-3xl border border-white/10 bg-white shadow-[0_30px_120px_rgba(15,23,42,0.35)]">
            <div class="flex items-start justify-between gap-4 border-b border-slate-200 bg-white/90 px-5 py-4 sm:px-6">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-amber-600">Edit Document</p>
                    <h3 class="mt-1 text-lg font-semibold text-slate-900 sm:text-2xl">Update circulation details</h3>
                </div>
                <button onclick="closeEditModal()" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:bg-slate-100 hover:text-slate-700" aria-label="Close edit modal">
                    <span class="text-2xl leading-none">×</span>
                </button>
            </div>
            <div id="editModalBody" class="max-h-[calc(94vh-72px)] overflow-auto px-4 py-4 sm:px-6 sm:py-6"></div>
        </div>
    </div>
</div>

<div id="confirmModal" class="hidden fixed inset-0 z-[70] p-4 sm:p-6">
    <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm" onclick="closeConfirmModal()"></div>
    <div class="relative mx-auto flex h-full w-full max-w-lg items-center justify-center">
        <div class="modal-panel w-full rounded-3xl border border-white/10 bg-white shadow-[0_30px_120px_rgba(15,23,42,0.35)]">
            <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-red-600">Confirm Action</p>
                <h3 id="confirmTitle" class="mt-1 text-lg font-semibold text-slate-900">Confirm</h3>
            </div>
            <div class="px-5 py-5 sm:px-6">
                <p id="confirmMessage" class="text-sm leading-7 text-slate-600"></p>
                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button type="button" onclick="closeConfirmModal()" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="button" id="confirmActionButton" class="inline-flex items-center justify-center rounded-2xl bg-red-600 px-4 py-3 text-sm font-semibold text-white hover:bg-red-700">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="hiddenDeleteForm" method="POST" class="hidden"></form>
<form id="hiddenHardDeleteForm" method="POST" class="hidden"></form>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

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

    #documentTable_wrapper .dataTables_length label,
    #documentTable_wrapper .dataTables_filter label,
    #documentTable_wrapper .dataTables_info {
        color: #4b5563;
        font-size: 0.875rem;
    }

    #documentTable_wrapper .dataTables_filter input,
    #documentTable_wrapper .dataTables_length select {
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        color: #111827;
        font-size: 0.875rem;
        min-height: 2.5rem;
        outline: none;
    }

    #documentTable_wrapper .dataTables_filter input {
        margin-left: 0;
        padding: 0 0.75rem;
        width: min(100%, 260px);
    }

    #documentTable_wrapper .dataTables_length select {
        margin: 0 0.35rem;
        padding: 0 2rem 0 0.75rem;
    }

    #documentTable_wrapper .dataTables_paginate .paginate_button {
        border: 1px solid transparent !important;
        border-radius: 0.5rem !important;
        color: #4b5563 !important;
        margin-left: 0.25rem;
    }

    #documentTable_wrapper .dataTables_paginate .paginate_button.current,
    #documentTable_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
    }

    #documentTable_wrapper .dataTables_paginate .paginate_button:hover {
        background: #eff6ff !important;
        border-color: #dbeafe !important;
        color: #1d4ed8 !important;
    }

    #documentTable tbody tr {
        transition: transform 140ms ease, background-color 140ms ease, box-shadow 140ms ease;
    }

    #documentTable tbody tr:hover {
        transform: translateY(-1px);
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
});

function syncSelectionState() {
    const recipientChecks = Array.from(document.querySelectorAll('input[data-selection-section="recipients"]'));
    const ccChecks = Array.from(document.querySelectorAll('input[data-selection-section="cc"]'));
    const selectedRecipients = new Set(recipientChecks.filter(cb => cb.checked).map(cb => String(cb.dataset.userId)));
    const selectedCc = new Set(ccChecks.filter(cb => cb.checked).map(cb => String(cb.dataset.userId)));

    const recipientCount = document.getElementById('recipients-count');
    const ccCount = document.getElementById('cc-count');

    if (recipientCount) {
        recipientCount.textContent = `${selectedRecipients.size} selected`;
    }

    if (ccCount) {
        ccCount.textContent = `${selectedCc.size} selected`;
    }

    document.querySelectorAll('input[data-selection-section]').forEach(cb => {
        const section = cb.dataset.selectionSection;
        const userId = String(cb.dataset.userId || '');
        const label = cb.closest('[data-user-card]');
        const counterpartSelected = section === 'recipients'
            ? selectedCc.has(userId)
            : selectedRecipients.has(userId);

        cb.disabled = counterpartSelected && !cb.checked;

        if (label) {
            label.classList.toggle('border-blue-300', cb.checked);
            label.classList.toggle('bg-blue-50', cb.checked);
            label.classList.toggle('ring-2', cb.checked);
            label.classList.toggle('ring-blue-100', cb.checked);
            label.classList.toggle('opacity-60', counterpartSelected && !cb.checked);
        }
    });
}

function bindSelectionFilter(section) {
    const filter = document.querySelector(`[data-selection-filter="${section}"]`);
    const cards = Array.from(document.querySelectorAll(`[data-selection-list="${section}"] [data-user-card]`));

    if (!filter) return;

    filter.addEventListener('input', function() {
        const term = this.value.toLowerCase().trim();
        cards.forEach(card => {
            const haystack = String(card.dataset.userFilter || '');
            card.classList.toggle('hidden', term !== '' && !haystack.includes(term));
        });
    });
}

document.querySelectorAll('input[data-selection-section]').forEach(cb => {
    cb.addEventListener('change', function() {
        if (this.checked) {
            const oppositeSection = this.dataset.selectionSection === 'recipients' ? 'cc' : 'recipients';
            document.querySelectorAll(`input[data-selection-section="${oppositeSection}"][data-user-id="${this.dataset.userId}"]`).forEach(other => {
                other.checked = false;
            });
        }

        syncSelectionState();
    });
});

bindSelectionFilter('recipients');
bindSelectionFilter('cc');
syncSelectionState();

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
function viewDocument(id) {
    const modal = document.getElementById('documentModal');
    const body = document.getElementById('modalBody');
    const title = document.getElementById('modalTitle');

    title.textContent = 'Loading...';
    body.innerHTML = `<div class="flex justify-center py-24"><div class="h-12 w-12 animate-spin rounded-full border-4 border-slate-200 border-t-blue-600"></div></div>`;
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

function openEditDocument(id) {
    const modal = document.getElementById('editDocumentModal');
    const body = document.getElementById('editModalBody');
    modal.classList.remove('hidden');
    body.innerHTML = `<div class="flex flex-col items-center justify-center gap-3 py-24 text-slate-500"><div class="h-12 w-12 animate-spin rounded-full border-4 border-slate-200 border-t-amber-600"></div><p class="text-sm font-medium">Loading edit form...</p></div>`;

    fetch(`index.php?controller=correspondence&action=getEditDocumentForm&id=${id}`)
        .then(r => r.text())
        .then(html => {
            const trimmed = (html || '').trim();
            body.innerHTML = trimmed !== '' ? html : `<div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">No edit content returned.</div>`;
            const form = document.getElementById('editDocumentForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    openConfirmModal(
                        'Save document changes?',
                        'This will update the sender copy and flag the document as edited for standard users.',
                        'Save Changes',
                        'Edit Document',
                        () => form.submit(),
                        'bg-amber-600 hover:bg-amber-700'
                    );
                }, { once: true });
            }
        })
        .catch(() => body.innerHTML = `<div class="rounded-3xl border border-red-100 bg-red-50 px-4 py-4 text-sm text-red-700">Failed to load edit form.</div>`);
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
