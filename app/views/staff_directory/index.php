<?php
function escape($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function staffImageUrl($image) {
    if (!empty($image)) {
        return BASE_URL . 'uploads/staff_directory/' . rawurlencode($image);
    }

    return BASE_URL . 'app/assets/profiles/default.png';
}

function staffStatusBadge($status) {
    $value = strtolower(trim((string)($status ?? 'active')));
    $label = ucfirst(str_replace(['-', '_'], ' ', $value ?: 'active'));

    switch ($value) {
        case 'active':
            return ['label' => $label, 'pill' => 'bg-emerald-50 text-emerald-700', 'dot' => 'bg-emerald-700'];
        case 'on_leave':
            return ['label' => $label, 'pill' => 'bg-amber-50 text-amber-700', 'dot' => 'bg-amber-700'];
        case 'inactive':
            return ['label' => $label, 'pill' => 'bg-rose-50 text-rose-700', 'dot' => 'bg-rose-700'];
        case 'contract':
            return ['label' => $label, 'pill' => 'bg-violet-50 text-violet-700', 'dot' => 'bg-violet-700'];
        default:
            return ['label' => $label, 'pill' => 'bg-slate-100 text-slate-700', 'dot' => 'bg-slate-700'];
    }


}
?>
<div class="min-h-screen theme-palette">
    <div class="max-w-[1400px] mx-auto px-3 py-6 text-xs  sm:text-xs md:text-sm correspondence-ui">
<div class="space-y-4 p-2 ">

        <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 p-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.26em] text-blue-600">
                        Staff Directory
                    </p>
                     <h2 class=" text-xl font-semibold tracking-tight text-slate-900">Enterprise team directory</h2>
                    <p class=" max-w-2xl text-sm leading-6 text-slate-500">
                        Find team members fast, preview details, and keep the directory ready for action.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-700">
                        <span class="h-2 w-2 rounded-full bg-slate-900"></span>
                        Total Staff <?= escape($metrics['totalStaff']) ?>
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-700">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Active <?= escape($metrics['activeStaff']) ?>
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-700">
                        <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                        Latest deployment <?= escape($metrics['recentDeployment']) ?>
                    </span>
                </div>
            </div>

            <div class="border-t border-slate-200 p-4">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end">
                    <div class="flex flex-1 flex-col gap-3 md:flex-row md:items-end">
                        <div class="w-full md:w-40">
                            <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">Department</label>
                            <select id="departmentFilter" class="h-10 w-full rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="">All departments</option>
                                <?php foreach ($departments as $department): ?>
                                    <option value="<?= escape($department) ?>"><?= escape($department) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="w-full md:w-40">
                            <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">Position</label>
                            <select id="positionFilter" class="h-10 w-full rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="">All positions</option>
                                <?php foreach ($positions as $position): ?>
                                    <option value="<?= escape($position) ?>"><?= escape($position) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="w-full md:w-40">
                            <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">Status</label>
                            <select id="statusFilter" class="h-10 w-full rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="">All statuses</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="on_leave">On Leave</option>
                                <option value="contract">Contract</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div class="w-full md:max-w-sm">
                            <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">Search</label>
                            <label class="flex h-9 w-full items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-500 focus-within:border-slate-400 focus-within:ring-2 focus-within:ring-slate-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-4.35-4.35m1.85-5.15a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/>
                                </svg>
                                <input id="staffSearchInput" type="search" placeholder="Search staff" class="w-full border-0 bg-transparent p-0 text-sm text-slate-700 outline-none placeholder:text-slate-400">
                            </label>
                        </div>
                    </div>

                    <a href="index.php?controller=StaffDirectory&action=create" class="inline-flex h-9 items-center whitespace-nowrap rounded-lg bg-slate-950 px-4 text-sm font-semibold text-white transition hover:bg-slate-800">
                        Add new staff
                    </a>
                </div>
            </div>
        </div>

    <div class="grid gap-4 xl:grid-cols-[1.85fr_0.95fr]">
        <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">

          <div class="flex flex-col mb-4">
    <p class="text-base font-semibold text-slate-900">Team roster</p>
    <p class="mt-1 text-sm text-slate-500">
        Select a staff member to preview their profile and available actions.
    </p>
</div>

            <div class="mt-2 overflow-x-auto rounded-xl">
                            <div class="flex flex-col gap-3 border-t border-slate-100 p-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <label for="pageLengthSelect" class="font-medium text-slate-600">Show</label>
                    <select id="pageLengthSelect" class="h-8 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-sm text-slate-900 outline-none focus:border-slate-400">
                        <option value="10">10</option>
                        <option value="20" selected>20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>entries</span>

                </div>
            </div>
                <table id="staffDirectoryTable" class="w-full text-sm text-slate-900">
            

                    <thead class="bg-white text-left text-xs uppercase tracking-[0.24em] text-slate-500">
                        <tr>
                            <th class="px-3 py-3">Staff</th>
                            <th class="px-3 py-3">Position</th>
                            <th class="px-3 py-3">Deployment</th>
                            <th class="px-3 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <?php foreach ($staffEntries as $staff): ?>
                            <?php $fullName = trim(sprintf('%s, %s', $staff['lastName'] ?? '', $staff['firstName'] ?? '')); ?>
                            <tr class="hover:bg-slate-50 transition-colors" data-staff='<?= htmlspecialchars(json_encode([
                                'id' => $staff['staff_id'],
                                'name' => $fullName,
                                'department' => $staff['department'],
                                'position' => $staff['position'],
                                'email' => $staff['email'],
                                'contact' => $staff['contact_number'],
                                'deployment' => !empty($staff['deployment_date']) ? date('M j, Y', strtotime($staff['deployment_date'])) : '—',
                                'status' => $staff['status'] ?? 'active',
                                'image' => staffImageUrl($staff['image'] ?? null),
                            ]), ENT_QUOTES, 'UTF-8') ?>'>
                                <td class="px-3 py-3 align-top">
                                    <div class="flex items-center gap-3">
                                        <img src="<?= staffImageUrl($staff['image'] ?? null) ?>" alt="<?= escape($fullName) ?>" class="h-9 w-9 rounded-md object-cover border border-slate-200 bg-slate-100">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900"><?= escape($fullName) ?></p>
                                            <p class="text-xs text-slate-500"><?= escape($staff['staff_id']) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3 align-top text-sm text-slate-700"><?= escape($staff['position']) ?></td>
                                <td class="px-3 py-3 align-top text-sm text-slate-700"><?= !empty($staff['deployment_date']) ? date('M j, Y', strtotime($staff['deployment_date'])) : '—' ?></td>
                                <td class="px-3 py-3 align-top text-sm text-slate-700">
                                    <?php $statusBadge = staffStatusBadge($staff['status'] ?? 'active'); ?>
                                    <span class="inline-flex items-center gap-2 rounded-full px-2.5 py-1 text-xs font-medium <?= escape($statusBadge['pill']) ?>">
                                        <span class="h-1.5 w-1.5 rounded-full <?= escape($statusBadge['dot']) ?>"></span>
                                        <?= escape($statusBadge['label']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        
        </section>

        <aside class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div id="emptySnapshot" class="space-y-4 transition-opacity duration-200 ease-out">
                <div class="rounded-xl bg-white p-4">
                    <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Quick snapshot</p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="space-y-1">
                            <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Total staff</p>
                            <p class="text-base font-semibold text-slate-900"><?= escape($metrics['totalStaff']) ?></p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Active staff</p>
                            <p class="text-base font-semibold text-slate-900"><?= escape($metrics['activeStaff']) ?></p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Departments</p>
                            <p class="text-base font-semibold text-slate-900"><?= escape($metrics['departmentCount']) ?></p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Latest deployment</p>
                            <p class="text-base font-semibold text-slate-900"><?= escape($metrics['recentDeployment']) ?></p>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="mb-3 text-sm font-semibold text-slate-900">Search tips</p>
                    <ul class="grid gap-2 text-xs text-slate-500">
                        <li class="flex items-start gap-2">
                            <span class="mt-1 h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                            Click a row to preview details.
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-1 h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                            Filter by department or position.
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-1 h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                            Search name, email, or ID.
                        </li>
                    </ul>
                </div>
            </div>

            <div id="staffPreview" class="hidden space-y-4 transition-opacity duration-200 ease-out">
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-4">
                        <img id="previewImage" src="<?= staffImageUrl(null) ?>" alt="Selected staff avatar" class="h-16 w-16 rounded-xl object-cover border border-slate-200 bg-slate-100">
                        <div>
                            <p id="previewName" class="text-base font-semibold text-slate-900"></p>
                            <p id="previewStaffId" class="text-xs text-slate-500"></p>
                            <p id="previewPosition" class="mt-2 text-sm text-slate-700"></p>
                        </div>
                    </div>
                    <div class="mt-4 grid gap-3 text-sm text-slate-700">
                        <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                            <span class="text-xs font-medium text-slate-500">Department</span>
                            <span id="previewDepartment" class="text-sm font-semibold text-slate-900"></span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                            <span class="text-xs font-medium text-slate-500">Email</span>
                            <span id="previewEmail" class="text-sm font-semibold text-slate-900"></span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                            <span class="text-xs font-medium text-slate-500">Contact</span>
                            <span id="previewContact" class="text-sm font-semibold text-slate-900"></span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                            <span class="text-xs font-medium text-slate-500">Deployment</span>
                            <span id="previewDeployment" class="text-sm font-semibold text-slate-900"></span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                            <span class="text-xs font-medium text-slate-500">Status</span>
                            <span id="previewStatus" class="inline-flex items-center gap-2 rounded-full px-2.5 py-1 text-xs font-semibold"><span class="h-1.5 w-1.5 rounded-full"></span><span>Active</span></span>
                        </div>
                    </div>
                </div>
                <div class="grid gap-3">
                    <button id="previewViewButton" type="button" disabled class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-50 opacity-50 cursor-not-allowed">View full details</button>
                    <button id="previewEditButton" type="button" disabled class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-50 opacity-50 cursor-not-allowed">Edit</button>
                    <button id="previewDeleteButton" type="button" disabled class="h-9 rounded-lg border border-red-200 bg-red-50 px-3 text-sm font-semibold text-red-700 transition hover:bg-red-100 opacity-50 cursor-not-allowed">Delete</button>
                    <button id="clearSelection" type="button" class="h-9 rounded-lg bg-slate-950 px-3 text-sm font-semibold text-white transition hover:bg-slate-800">Cancel selection</button>
                </div>
            </div>
        </aside>
    </div>
</div>

</div>
</div>

<style>
    #staffDirectoryTable_wrapper .dataTables_info,
    #staffDirectoryTable_wrapper .dataTables_paginate {
        color: #475569;
        font-size: 0.875rem;
    }

    #staffDirectoryTable_wrapper .dataTables_paginate {
        margin-top: 0;
    }

    #staffDirectoryTable_wrapper .dataTables_paginate .paginate_button {
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        padding: 0.35rem 0.7rem;
        margin-left: 0.25rem;
        color: #334155;
        background: white;
    }

    #staffDirectoryTable_wrapper .dataTables_paginate .paginate_button.current,
    #staffDirectoryTable_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #0f172a;
    }

    #staffDirectoryTable {
        width: 100%;
        table-layout: auto;
    }

    #staffDirectoryTable th,
    #staffDirectoryTable td {
        vertical-align: middle;
        word-break: break-word;
    }

    #staffDirectoryTable th.sorting:after,
    #staffDirectoryTable th.sorting_asc:after,
    #staffDirectoryTable th.sorting_desc:after {
        opacity: 0.25;
        font-size: 0.65rem;
        right: 0.6rem;
    }

    #staffDirectoryTable tbody tr:hover {
        cursor: pointer;
    }

    .selected-row {
        background-color: #f1f5f9 !important;
    }
</style>

<script>
    $(document).ready(function() {
        const table = $('#staffDirectoryTable').DataTable({
            pageLength: 20,
            order: [[2, 'desc']],
            dom: 'rt<"mt-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between"ip>',
            language: {
                emptyTable: 'No staff found.'
            }
        });

        $('#staffSearchInput').on('input', function() {
            table.search($(this).val()).draw();
        });

        $('#pageLengthSelect').on('change', function() {
            table.page.len(Number($(this).val())).draw();
        });

        function parseStaffData(row) {
            const data = row.attr('data-staff');
            if (!data) {
                return null;
            }

            try {
                return JSON.parse(data);
            } catch (error) {
                console.error('Failed to parse staff preview data:', error, data);
                return null;
            }
        }

        let selectedStaffId = null;

        function getStatusBadgeClasses(status) {
            const value = (status || 'active').toString().toLowerCase();

            switch (value) {
                case 'active':
                    return ['bg-emerald-50 text-emerald-700', 'bg-emerald-700'];
                case 'inactive':
                case 'disabled':
                    return ['bg-slate-100 text-slate-700', 'bg-slate-700'];
                case 'pending':
                case 'draft':
                    return ['bg-amber-50 text-amber-700', 'bg-amber-700'];
                case 'on leave':
                case 'leave':
                case 'on-leave':
                case 'on_leave':
                    return ['bg-sky-50 text-sky-700', 'bg-sky-700'];
                case 'contract':
                    return ['bg-violet-50 text-violet-700', 'bg-violet-700'];
                case 'suspended':
                case 'blocked':
                    return ['bg-rose-50 text-rose-700', 'bg-rose-700'];
                case 'resigned':
                case 'retired':
                    return ['bg-violet-50 text-violet-700', 'bg-violet-700'];
                default:
                    return ['bg-slate-100 text-slate-700', 'bg-slate-700'];
            }
        }

        function togglePreview(data) {
            const $emptySnapshot = $('#emptySnapshot');
            const $staffPreview = $('#staffPreview');
            const viewButton = $('#previewViewButton');
            const editButton = $('#previewEditButton');
            const deleteButton = $('#previewDeleteButton');

            if (!data) {
                selectedStaffId = null;
                $emptySnapshot.removeClass('hidden');
                $staffPreview.addClass('hidden');
                viewButton.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
                editButton.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
                deleteButton.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
                return;
            }

            selectedStaffId = data.id;
            $emptySnapshot.addClass('hidden');
            $staffPreview.removeClass('hidden');
            viewButton.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
            editButton.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
            deleteButton.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');

            $('#previewImage').attr('src', data.image || '<?= staffImageUrl(null) ?>');
            $('#previewName').text(data.name || 'N / A');
            $('#previewPosition').text(data.position || 'No position');
            $('#previewStaffId').text(data.id || 'N / A');
            $('#previewDepartment').text(data.department || 'No department');
            $('#previewEmail').text(data.email || 'No email');
            $('#previewContact').text(data.contact || 'No phone');
            $('#previewDeployment').text(data.deployment || 'No deployment date');
            const [pillClass, dotClass] = getStatusBadgeClasses(data.status);
            const statusText = (data.status || 'Active').toString();
            const $previewStatus = $('#previewStatus');
            $previewStatus.removeClass().addClass(`inline-flex items-center gap-2 rounded-full px-2.5 py-1 text-xs font-semibold ${pillClass}`);
            $previewStatus.find('span').first().removeClass().addClass(`h-1.5 w-1.5 rounded-full ${dotClass}`);
            $previewStatus.find('span').last().text(statusText);
        }

        $('#staffDirectoryTable tbody').on('click', 'tr', function() {
            const $row = $(this);
            const isSelected = $row.hasClass('selected-row');
            $('#staffDirectoryTable tbody tr').removeClass('selected-row');

            if (isSelected) {
                togglePreview(null);
                return;
            }

            $row.addClass('selected-row');
            togglePreview(parseStaffData($row));
        });

        $('#clearSelection').on('click', function() {
            $('#staffDirectoryTable tbody tr').removeClass('selected-row');
            togglePreview(null);
        });

        $('#previewViewButton').on('click', function() {
            if (!selectedStaffId) {
                return;
            }
            window.location.href = `index.php?controller=StaffDirectory&action=edit&id=${encodeURIComponent(selectedStaffId)}`;
        });

        $('#previewEditButton').on('click', function() {
            if (!selectedStaffId) {
                return;
            }
            window.location.href = `index.php?controller=StaffDirectory&action=edit&id=${encodeURIComponent(selectedStaffId)}`;
        });

        $('#previewDeleteButton').on('click', function() {
            if (!selectedStaffId) {
                return;
            }
            const confirmDelete = confirm('Delete this staff profile? This action cannot be undone.');
            if (confirmDelete) {
                window.location.href = `index.php?controller=StaffDirectory&action=delete&id=${encodeURIComponent(selectedStaffId)}`;
            }
        });

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            const department = $('#departmentFilter').val();
            const position = $('#positionFilter').val();
            const status = $('#statusFilter').val();
            const row = table.row(dataIndex).node();
            const staffData = parseStaffData($(row));

            if (!staffData) {
                return true;
            }

            const departmentMatches = department === '' || staffData.department === department;
            const positionMatches = position === '' || staffData.position === position;
            const statusMatches = status === '' || (staffData.status || 'active').toString().toLowerCase() === status;
            return departmentMatches && positionMatches && statusMatches;
        });

        $('#departmentFilter, #positionFilter, #statusFilter').on('change', function() {
            $('#staffDirectoryTable tbody tr').removeClass('selected-row');
            togglePreview(null);
            table.draw();
        });
    });
</script>
