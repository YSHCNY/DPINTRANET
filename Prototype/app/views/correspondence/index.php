<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-8">

        <?php if (isset($_SESSION['message'])): ?>
            <div class="mb-6 p-4 rounded-xl <?= $_SESSION['msg_type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                <?= htmlspecialchars($_SESSION['message']) ?>
            </div>
            <?php unset($_SESSION['message'], $_SESSION['msg_type']); ?>
        <?php endif; ?>

        <!-- ====================== NEW CIRCULATION FORM ====================== -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8 overflow-visible">
            <div class="px-5 py-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase text-blue-600">Digital Correspondence</p>
                    <h2 class="text-xl font-semibold text-gray-900 mt-0.5">New Circulation</h2>
                </div>
                <div class="inline-flex items-center gap-2 text-xs text-gray-600 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    <span>Auto tracking enabled</span>
                </div>
            </div>

            <form id="circulationForm" action="index.php?controller=correspondence&action=store" method="POST" enctype="multipart/form-data" class="p-5">
                <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_360px] gap-6">
                    <div class="space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-[190px_minmax(0,1fr)] gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Tracking ID</label>
                                <input type="text" id="tracking_id" name="tracking_id"
                                    value="<?= htmlspecialchars($nextTrackingId) ?>"
                                    class="w-full h-10 bg-gray-50 border border-gray-300 rounded-lg px-3 font-mono text-sm font-semibold text-gray-700" readonly>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Document Title <span class="text-red-500">*</span></label>
                                <input type="text" name="title" required placeholder="Enter document title"
                                    class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Type</label>
                                <select name="type" class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    <option value="Memo">Memo</option>
                                    <option value="Letter">Letter</option>
                                    <option value="Report">Report</option>
                                    <option value="Circular">Circular</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Priority</label>
                                <select name="priority" class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    <option value="Low">Low</option>
                                    <option value="Medium" selected>Medium</option>
                                    <option value="High">High</option>
                                    <option value="Urgent">Urgent</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Due Date</label>
                                <input type="date" name="due_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Sender</label>
                                <input type="email" name="sender_email" value="noreply@dalton.com.ph"
                                    class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Description / Body</label>
                            <div id="description" contenteditable="true"
                                class="min-h-[150px] border border-gray-300 rounded-lg p-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 prose max-w-none"></div>
                            <input type="hidden" name="description" id="description-hidden">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_220px] gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Attachments</label>
                                <input type="file" name="attachments[]" multiple
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm file:mr-3 file:border-0 file:bg-gray-100 file:text-gray-700 file:rounded-md file:px-3 file:py-1.5 hover:file:bg-gray-200">
                            </div>
                            <label for="confidential" class="flex items-center gap-3 border border-gray-200 rounded-lg px-3 py-2.5 bg-gray-50 mt-0 md:mt-6">
                                <input type="checkbox" name="is_confidential" id="confidential" class="w-4 h-4 accent-blue-600">
                                <span class="text-sm font-medium text-gray-700">Confidential</span>
                            </label>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Notes / Remarks</label>
                            <textarea name="notes" rows="2" placeholder="Optional internal notes"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-semibold text-gray-700">Recipients</label>
                                <span id="recipients-count" class="text-xs font-medium text-gray-500">0 selected</span>
                            </div>
                            <div class="relative border border-gray-300 rounded-lg bg-white p-2.5 min-h-[112px] focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-100 transition" id="recipients-container">
                                <div class="flex flex-wrap gap-1.5 mb-1.5" id="recipients-tags"></div>
                                <input type="text" id="recipients-input"
                                    class="w-full min-h-8 outline-none bg-transparent text-sm text-gray-900 placeholder:text-gray-400"
                                    placeholder="Search active users">
                                <div id="recipients-suggestions" class="hidden absolute left-0 right-0 top-full bg-white border border-gray-200 rounded-lg shadow-xl mt-2 max-h-64 overflow-auto z-50"></div>
                            </div>
                            <input type="hidden" name="recipients" id="recipients-hidden">
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-semibold text-gray-700">CC</label>
                                <span id="cc-count" class="text-xs font-medium text-gray-500">0 selected</span>
                            </div>
                            <div class="relative border border-gray-300 rounded-lg bg-white p-2.5 min-h-[112px] focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-100 transition" id="cc-container">
                                <div class="flex flex-wrap gap-1.5 mb-1.5" id="cc-tags"></div>
                                <input type="text" id="cc-input"
                                    class="w-full min-h-8 outline-none bg-transparent text-sm text-gray-900 placeholder:text-gray-400"
                                    placeholder="Search active users">
                                <div id="cc-suggestions" class="hidden absolute left-0 right-0 top-full bg-white border border-gray-200 rounded-lg shadow-xl mt-2 max-h-64 overflow-auto z-50"></div>
                            </div>
                            <input type="hidden" name="cc" id="cc-hidden">
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row xl:flex-col-reverse gap-3 pt-2">
                            <button type="button" onclick="resetForm()"
                                class="h-10 px-4 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50">Reset</button>
                            <button type="submit"
                                class="h-10 px-5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm">Circulate Document</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

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

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-[180px_180px_minmax(0,1fr)] gap-3">
                    <div>
                        <label for="priorityFilter" class="block text-xs font-semibold text-gray-700 mb-1.5">Priority</label>
                        <select id="priorityFilter" class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">All priorities</option>
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                            <option value="Urgent">Urgent</option>
                        </select>
                    </div>
                    <div>
                        <label for="statusFilter" class="block text-xs font-semibold text-gray-700 mb-1.5">Status</label>
                        <select id="statusFilter" class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">All statuses</option>
                            <option value="Pending">Pending</option>
                            <option value="Completed">Completed</option>
                        </select>
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
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500 w-14">File</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($documents as $doc): ?>
                        <?php
                            $receivedCount = (int)($doc['received_count'] ?? 0);
                            $totalRecipients = (int)($doc['total_recipients'] ?? 0);
                            $status = ($totalRecipients > 0 && $receivedCount >= $totalRecipients) ? 'Completed' : 'Pending';
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
                            class="hover:bg-blue-50/60 cursor-pointer transition-colors">
                            <td class="px-4 py-3 font-mono text-blue-700 whitespace-nowrap"><?= htmlspecialchars($doc['tracking_id']) ?></td>
                            <td class="px-4 py-3 font-medium text-gray-900 min-w-[220px]"><?= htmlspecialchars($doc['title']) ?></td>
                            <td class="px-4 py-3 text-gray-600 min-w-[180px]"><?= htmlspecialchars($doc['sender_email']) ?></td>
                            <td class="px-4 py-3 text-center font-semibold text-green-700 whitespace-nowrap">
                                <?= $receivedCount ?>/<?= $totalRecipients ?>
                            </td>
                            <td class="px-4 py-3 text-center" data-search="<?= htmlspecialchars($status) ?>">
                                <span class="inline-flex items-center justify-center rounded-md border px-2.5 py-1 text-xs font-semibold <?= $statusClass ?>">
                                    <?= htmlspecialchars($status) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap"><?= date('M d, Y', strtotime($doc['created_at'])) ?></td>
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap"><?= $doc['due_date'] ? date('M d, Y', strtotime($doc['due_date'])) : '—' ?></td>
                            <td class="px-4 py-3 text-center" data-search="<?= htmlspecialchars($doc['priority']) ?>">
                                <span class="inline-flex items-center justify-center rounded-md border px-2.5 py-1 text-xs font-semibold <?= $priorityClass ?>">
                                    <?= htmlspecialchars($doc['priority']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center" onclick="event.stopImmediatePropagation()">
                                <button onclick="downloadDocument(<?= $doc['id'] ?>)"
                                        class="inline-flex h-8 w-8 items-center justify-center text-gray-500 hover:text-blue-700 rounded-lg hover:bg-blue-50 transition"
                                        title="Download attachment">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0l4-4m-4 4l-4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>


<!-- Enhanced Modal -->
<div id="documentModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-3xl w-full max-w-5xl max-h-[92vh] overflow-hidden shadow-2xl">
        <div class="px-8 py-5 border-b flex justify-between items-center bg-gray-50">
            <h3 id="modalTitle" class="text-2xl font-semibold"></h3>
            <button onclick="closeModal()" class="text-4xl text-gray-400 hover:text-gray-600">×</button>
        </div>
        <div id="modalBody" class="p-8 overflow-auto" style="max-height: calc(92vh - 85px);"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

<style>
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
</style>

<script>
// DataTables
$(document).ready(function() {
    const documentTable = $('#documentTable').DataTable({
        pageLength: 25,
        order: [[5, 'desc']],
        dom: '<"flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4"lf>rt<"flex flex-col md:flex-row md:items-center md:justify-between gap-3 mt-4"ip>',
        language: {
            search: '',
            searchPlaceholder: 'Search documents'
        }
    });

    $('#priorityFilter').on('change', function() {
        documentTable.column(7).search(this.value ? '^' + this.value + '$' : '', true, false).draw();
    });

    $('#statusFilter').on('change', function() {
        documentTable.column(4).search(this.value ? '^' + this.value + '$' : '', true, false).draw();
    });
});

// Simple & Reliable Chip System
let recipientsSelected = [];
let ccSelected = [];
const allUsers = <?= json_encode($users ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

function userLabel(user) {
    return `${user.firstName || ''} ${user.lastName || ''}`.trim();
}

function userMeta(user) {
    return [user.position, user.department, user.email].filter(Boolean).join(' • ');
}

// Render chips
function renderChips(type) {
    const isRecipients = type === 'recipients';
    const selected = isRecipients ? recipientsSelected : ccSelected;
    const tagsId = isRecipients ? 'recipients-tags' : 'cc-tags';
    const hiddenId = isRecipients ? 'recipients-hidden' : 'cc-hidden';
    const countId = isRecipients ? 'recipients-count' : 'cc-count';

    const container = document.getElementById(tagsId);
    container.innerHTML = '';

    selected.forEach(user => {
        const chip = document.createElement('div');
        chip.className = "inline-flex items-center gap-2 bg-blue-50 text-blue-800 border border-blue-100 px-3 py-1.5 rounded-xl text-sm";

        const name = document.createElement('span');
        name.textContent = userLabel(user);
        chip.appendChild(name);

        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = "text-blue-500 hover:text-red-600 font-bold leading-none";
        remove.setAttribute('aria-label', `Remove ${userLabel(user)}`);
        remove.textContent = '×';
        remove.onclick = () => removeChip(user.id, type);
        chip.appendChild(remove);

        container.appendChild(chip);
    });

    document.getElementById(hiddenId).value = selected.map(u => u.id).join(',');
    document.getElementById(countId).textContent = `${selected.length} selected`;
}

// Remove chip
window.removeChip = function(id, type) {
    if (type === 'recipients') {
        recipientsSelected = recipientsSelected.filter(u => u.id != id);
        renderChips('recipients');
    } else {
        ccSelected = ccSelected.filter(u => u.id != id);
        renderChips('cc');
    }
};

// Initialize field
function initField(type) {
    const prefix = type === 'recipients' ? 'recipients' : 'cc';
    const input = document.getElementById(prefix + '-input');
    const suggestions = document.getElementById(prefix + '-suggestions');

    function hideSuggestions() {
        suggestions.innerHTML = '';
        suggestions.classList.add('hidden');
    }

    input.addEventListener('input', function() {
        const term = this.value.toLowerCase().trim();
        hideSuggestions();

        if (term.length < 1) return;

        const current = type === 'recipients' ? recipientsSelected : ccSelected;
        const opposite = type === 'recipients' ? ccSelected : recipientsSelected;
        const selectedIds = new Set([...current, ...opposite].map(user => String(user.id)));

        const filtered = allUsers.filter(user => {
            if (selectedIds.has(String(user.id))) return false;

            const haystack = [
                user.firstName,
                user.lastName,
                user.position,
                user.department,
                user.email
            ].filter(Boolean).join(' ').toLowerCase();

            return haystack.includes(term);
        }).slice(0, 12);

        if (filtered.length > 0) {
            suggestions.classList.remove('hidden');
            filtered.forEach(user => {
                const div = document.createElement('div');
                div.className = "px-4 py-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0";

                const name = document.createElement('div');
                name.className = "text-sm font-semibold text-gray-900";
                name.textContent = userLabel(user);
                div.appendChild(name);

                const meta = document.createElement('div');
                meta.className = "text-xs text-gray-500 mt-0.5";
                meta.textContent = userMeta(user) || 'No details available';
                div.appendChild(meta);

                div.onclick = () => {
                    const arr = type === 'recipients' ? recipientsSelected : ccSelected;
                    if (!arr.find(u => u.id == user.id)) {
                        arr.push(user);
                        renderChips(type);
                    }
                    input.value = '';
                    hideSuggestions();
                };
                suggestions.appendChild(div);
            });
        } else {
            suggestions.classList.remove('hidden');
            const empty = document.createElement('div');
            empty.className = "px-4 py-3 text-sm text-gray-500";
            empty.textContent = "No matching active users";
            suggestions.appendChild(empty);
        }
    });

    input.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && this.value === '') {
            const arr = type === 'recipients' ? recipientsSelected : ccSelected;
            arr.pop();
            renderChips(type);
        }
    });

    document.addEventListener('click', function(e) {
        if (!document.getElementById(prefix + '-container').contains(e.target)) {
            hideSuggestions();
        }
    });
}

// Initialize
initField('recipients');
initField('cc');

// FINAL SUBMIT SAFETY
document.getElementById('circulationForm').addEventListener('submit', function() {
    renderChips('recipients');
    renderChips('cc');
});

// Description
document.getElementById('circulationForm').addEventListener('submit', function() {
    const desc = document.getElementById('description');
    if (desc) document.getElementById('description-hidden').value = desc.innerHTML;
});

function resetForm() {
    document.getElementById('circulationForm').reset();
    document.getElementById('description').innerHTML = '';
    recipientsSelected = [];
    ccSelected = [];
    document.getElementById('recipients-tags').innerHTML = '';
    document.getElementById('cc-tags').innerHTML = '';
    document.getElementById('recipients-hidden').value = '';
    document.getElementById('cc-hidden').value = '';
    document.getElementById('recipients-count').textContent = '0 selected';
    document.getElementById('cc-count').textContent = '0 selected';
}


// Enhanced View Document
function viewDocument(id) {
    const modal = document.getElementById('documentModal');
    const body = document.getElementById('modalBody');
    const title = document.getElementById('modalTitle');

    title.textContent = 'Loading...';
    body.innerHTML = `<div class="flex justify-center py-20"><div class="animate-spin rounded-full h-12 w-12 border-b-4 border-blue-600"></div></div>`;
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

function downloadDocument(id) {
    event.stopImmediatePropagation();
    window.location.href = `index.php?controller=correspondence&action=download&id=${id}`;
}
</script>
