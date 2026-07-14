<?php
$isEdit = !empty($editingUser);
$formAction = $isEdit
    ? 'index.php?controller=StandardUsers&action=update&id=' . (int)$editingUser['id']
    : 'index.php?controller=StandardUsers&action=store';
$roleOptions = $roleOptions ?? ['user'];
$statusOptions = $statusOptions ?? ['active', 'inactive'];

function fieldValue($editingUser, $key, $default = '') {
    return htmlspecialchars($editingUser[$key] ?? $default);
}

function avatarUrl($avatar) {
    return !empty($avatar)
        ? BASE_URL . 'uploads/standard_users/' . htmlspecialchars($avatar)
        : BASE_URL . 'uploads/standard_users/default.png';
        
}
?>

<div class="space-y-8">
    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] gap-6">
        <div class="space-y-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase text-blue-600">Standard Users</p>
                        <h2 class="text-xl font-semibold text-gray-900 mt-0.5"><?= $isEdit ? 'Edit Standard User' : 'Create Standard User' ?></h2>
                    </div>

                    <?php if ($isEdit): ?>
                        <a href="index.php?controller=StandardUsers&action=index"
                           class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Cancel Edit
                        </a>
                    <?php endif; ?>
                </div>

                <form action="<?= $formAction ?>" method="POST" enctype="multipart/form-data" class="p-5">
                    <div class="grid grid-cols-1 xl:grid-cols-[220px_minmax(0,1fr)] gap-6">
                        <div class="space-y-4">
                            <div class="border border-gray-200 rounded-lg bg-gray-50 p-4">
                                <img id="avatarPreview"
                                     src="<?= avatarUrl($editingUser['avatar'] ?? null) ?>"
                                     class="w-24 h-24 rounded-full object-cover border border-gray-200 bg-white mx-auto"
                                     alt="User avatar">

                                <label class="block text-xs font-semibold text-gray-700 mt-4 mb-1.5">Avatar</label>
                                <input type="file" name="avatar" id="avatarInput" accept="image/jpeg,image/png,image/webp"
                                       class="w-full text-sm file:mr-3 file:border-0 file:bg-white file:text-gray-700 file:rounded-md file:px-3 file:py-1.5 hover:file:bg-gray-100">
                                <p class="text-xs text-gray-500 mt-2">JPG, PNG, or WEBP. Max 3MB.</p>
                            </div>

                            <label class="flex items-center justify-between gap-3 border border-gray-200 rounded-lg px-3 py-2.5 bg-white">
                                <span>
                                    <span class="block text-sm font-semibold text-gray-800">Portal Access</span>
                                    <span class="block text-xs text-gray-500">Allow login from portal</span>
                                </span>
                                <input type="checkbox" name="is_portal_user" class="w-4 h-4 accent-blue-600"
                                    <?= !empty($editingUser['is_portal_user']) ? 'checked' : '' ?>>
                            </label>
                        </div>

                        <div class="space-y-5">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">First Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="firstName" required value="<?= fieldValue($editingUser, 'firstName') ?>"
                                           class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Middle Name</label>
                                    <input type="text" name="middleName" value="<?= fieldValue($editingUser, 'middleName') ?>"
                                           class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Last Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="lastName" required value="<?= fieldValue($editingUser, 'lastName') ?>"
                                           class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Username <span class="text-red-500">*</span></label>
                                    <input type="text" name="username" required value="<?= fieldValue($editingUser, 'username') ?>"
                                           class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Password <?= $isEdit ? '<span class="text-gray-400">(optional)</span>' : '<span class="text-red-500">*</span>' ?></label>
                                    <input type="password" name="password" <?= $isEdit ? '' : 'required' ?>
                                           class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">PIN Code</label>
                                    <input type="text" name="pin_code" value="<?= fieldValue($editingUser, 'pin_code') ?>"
                                           class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Email</label>
                                    <input type="email" name="email" value="<?= fieldValue($editingUser, 'email') ?>"
                                           class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Phone</label>
                                    <input type="text" name="phone" value="<?= fieldValue($editingUser, 'phone') ?>"
                                           class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Position</label>
                                    <input type="text" name="position" value="<?= fieldValue($editingUser, 'position') ?>"
                                           class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Department</label>
                                    <input type="text" name="department" value="<?= fieldValue($editingUser, 'department') ?>"
                                           class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Role</label>
                                    <?php $role = $editingUser['role'] ?? ($roleOptions[0] ?? ''); ?>
                                    <select name="role" class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                        <?php foreach ($roleOptions as $option): ?>
                                            <option value="<?= htmlspecialchars($option) ?>" <?= $role === $option ? 'selected' : '' ?>>
                                                <?= htmlspecialchars(ucwords(str_replace(['_', '-'], ' ', $option))) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Status</label>
                                    <?php $status = $editingUser['status'] ?? ($statusOptions[0] ?? 'active'); ?>
                                    <select name="status" class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                        <?php foreach ($statusOptions as $option): ?>
                                            <option value="<?= htmlspecialchars($option) ?>" <?= $status === $option ? 'selected' : '' ?>>
                                                <?= htmlspecialchars(ucwords(str_replace(['_', '-'], ' ', $option))) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-2">
                                <button type="reset" class="h-10 px-4 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                    Reset
                                </button>
                                <button type="submit" class="h-10 px-5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm">
                                    <?= $isEdit ? 'Save Changes' : 'Create Standard User' ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <form action="index.php?controller=StandardUsers&action=import" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200">
                <div>
                    <p class="text-xs font-semibold uppercase text-blue-600">Bulk Import</p>
                    <h2 class="text-xl font-semibold text-gray-900 mt-0.5">Bulk Import Standard Users</h2>
                    <p class="mt-2 text-sm text-slate-500">Import multiple portal users using the standard template. Role, Status, and Portal Access are automatically assigned during import.</p>
                </div>
            </div>
            <div class="p-5 space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <a href="<?= BASE_URL ?>uploads/stafftemplate/std_user_template.xlsx" download class="inline-flex items-center justify-center h-10 rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Download CSV Template
                    </a>
                    <a href="<?= BASE_URL ?>uploads/stafftemplate/std_user_template.xlsx" download class="inline-flex items-center justify-center h-10 rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Download Excel Template
                    </a>
                </div>

                <div>
                    <label for="bulkImportFileInput" id="bulkImportDropzone" class="group block rounded-3xl border border-slate-200 border-dashed bg-slate-50 px-4 py-10 text-center cursor-pointer transition hover:border-slate-300 hover:bg-slate-100">
                        <input id="bulkImportFileInput" name="bulk_import_file" type="file" accept=".csv,.xlsx" class="hidden">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M7 10l5-5m0 0l5 5m-5-5v12" />
                            </svg>
                        </div>
                        <p class="mt-4 text-sm font-semibold text-slate-900">Drag & drop a file here, or click to browse</p>
                        <p class="mt-1 text-xs text-slate-500">Supports .csv and .xlsx files.</p>
                    </label>
                    <p id="bulkImportFilename" class="mt-3 text-sm text-slate-500">No file selected</p>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <button id="bulkValidateButton" type="button" class="h-10 px-5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm">
                        Validate
                    </button>
                    <button id="bulkImportSubmit" type="button" disabled class="h-10 px-5 bg-slate-200 text-slate-500 text-sm font-semibold rounded-lg shadow-sm cursor-not-allowed">
                        Import
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase text-slate-500">Total Rows</p>
                        <p id="bulkTotalRows" class="mt-3 text-2xl font-semibold text-slate-900">0</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase text-slate-500">Valid Rows</p>
                        <p id="bulkValidRows" class="mt-3 text-2xl font-semibold text-slate-900">0</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase text-slate-500">Invalid Rows</p>
                        <p id="bulkInvalidRows" class="mt-3 text-2xl font-semibold text-slate-900">0</p>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-sm font-semibold text-slate-900">Validation Summary</p>
                    <p id="bulkValidationSummary" class="mt-2 text-sm text-slate-500">No validation performed yet. Use Validate to inspect the file before importing.</p>
                </div>

                <div class="overflow-x-auto">
                    <table id="bulkValidationTable" class="min-w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-500">Row</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-500">Username</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-500">Email</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-500">Field</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-500">Error</th>
                            </tr>
                        </thead>
                        <tbody id="bulkValidationBody" class="divide-y divide-slate-200 bg-white">
                            <tr>
                                <td class="px-3 py-3 text-slate-700" colspan="5">No validation results yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-600">Directory</p>
                <h2 class="text-xl font-semibold text-gray-900 mt-0.5">Standard Users</h2>
            </div>
            <span class="inline-flex w-fit items-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-medium text-gray-600">
                <?= count($users) ?> user(s)
            </span>
        </div>

        <div class="p-5 overflow-x-auto">
            <table id="standardUsersTable" class="w-full text-sm">
                <thead class="bg-gray-50 border-y border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">User</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Contact</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Department</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500">Portal</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($users as $user): ?>
                        <?php
                            $statusClass = match ($user['status'] ?? 'active') {
                                'active' => 'bg-green-50 text-green-700 border-green-100',
                                'suspended' => 'bg-amber-50 text-amber-700 border-amber-100',
                                default => 'bg-gray-50 text-gray-700 border-gray-100',
                            };
                        ?>
                        <tr class="hover:bg-blue-50/60 transition-colors">
                            <td class="px-4 py-3 min-w-[240px]">
                                <div class="flex items-center gap-3">
                                    <img src="<?= avatarUrl($user['avatar'] ?? null) ?>" class="w-10 h-10 rounded-full object-cover border border-gray-200" alt="Avatar">
                                    <div>
                                        <p class="font-semibold text-gray-900">
                                            <?= htmlspecialchars(trim(($user['firstName'] ?? '') . ' ' . ($user['middleName'] ?? '') . ' ' . ($user['lastName'] ?? ''))) ?>
                                        </p>
                                        <p class="text-xs text-gray-500 font-mono"><?= htmlspecialchars($user['username'] ?? '') ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 min-w-[210px]">
                                <p class="text-gray-800"><?= htmlspecialchars($user['email'] ?? '') ?></p>
                                <p class="text-xs text-gray-500"><?= htmlspecialchars($user['phone'] ?? '') ?></p>
                            </td>
                            <td class="px-4 py-3 min-w-[190px]">
                                <p class="text-gray-800"><?= htmlspecialchars($user['department'] ?? '') ?></p>
                                <p class="text-xs text-gray-500"><?= htmlspecialchars($user['position'] ?? '') ?><?= !empty($user['role']) ? ' • ' . htmlspecialchars($user['role']) : '' ?></p>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex rounded-md border px-2.5 py-1 text-xs font-semibold <?= !empty($user['is_portal_user']) ? 'bg-blue-50 text-blue-700 border-blue-100' : 'bg-gray-50 text-gray-600 border-gray-100' ?>">
                                    <?= !empty($user['is_portal_user']) ? 'Enabled' : 'Off' ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex rounded-md border px-2.5 py-1 text-xs font-semibold <?= $statusClass ?>">
                                    <?= htmlspecialchars(ucfirst($user['status'] ?? 'active')) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-center gap-2">
                                    <a href="index.php?controller=StandardUsers&action=edit&id=<?= (int)$user['id'] ?>"
                                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-blue-100 bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white transition"
                                       title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.651-1.651a1.875 1.875 0 112.652 2.652L9.75 16.903 6 18l1.097-3.75L16.862 4.487z" />
                                        </svg>
                                    </a>
                                    <a href="index.php?controller=StandardUsers&action=delete&id=<?= (int)$user['id'] ?>"
                                       onclick="return confirm('Delete this standard user?')"
                                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-700 hover:bg-red-600 hover:text-white transition"
                                       title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12m-9 0V5a1 1 0 011-1h4a1 1 0 011 1v2m-8 0l1 12h6l1-12" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    #standardUsersTable_wrapper .dataTables_length label,
    #standardUsersTable_wrapper .dataTables_filter label,
    #standardUsersTable_wrapper .dataTables_info {
        color: #4b5563;
        font-size: 0.875rem;
    }

    #standardUsersTable_wrapper .dataTables_filter input,
    #standardUsersTable_wrapper .dataTables_length select {
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        color: var(--secondary);
        font-size: 0.875rem;
        min-height: 2.5rem;
        outline: none;
    }

    #standardUsersTable_wrapper .dataTables_filter input {
        margin-left: 0;
        padding: 0 0.75rem;
        width: min(100%, 260px);
    }
</style>

<script>
$(document).ready(function() {
    $('#standardUsersTable').DataTable({
        pageLength: 25,
        order: [[0, 'asc']],
        dom: '<"flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4"lf>rt<"flex flex-col md:flex-row md:items-center md:justify-between gap-3 mt-4"ip>',
        language: {
            search: '',
            searchPlaceholder: 'Search standard users'
        }
    });
});

document.getElementById('avatarInput')?.addEventListener('change', function() {
    const file = this.files?.[0];
    if (!file) return;

    const preview = document.getElementById('avatarPreview');
    preview.src = URL.createObjectURL(file);
});

const bulkFileInput = document.getElementById('bulkImportFileInput');
const bulkFilename = document.getElementById('bulkImportFilename');
const bulkDropzone = document.getElementById('bulkImportDropzone');
const bulkImportSubmit = document.getElementById('bulkImportSubmit');
const bulkValidateButton = document.getElementById('bulkValidateButton');
const bulkValidationBody = document.getElementById('bulkValidationBody');
const bulkTotalRows = document.getElementById('bulkTotalRows');
const bulkValidRows = document.getElementById('bulkValidRows');
const bulkInvalidRows = document.getElementById('bulkInvalidRows');
const bulkValidationSummary = document.getElementById('bulkValidationSummary');

let lastValidationFile = null;
let lastValidationResult = null;

function updateBulkImportFilename(file) {
    if (!bulkFilename) {
        return;
    }
    bulkFilename.textContent = file ? file.name : 'No file selected';
}

function resetValidationState() {
    if (bulkTotalRows) bulkTotalRows.textContent = '0';
    if (bulkValidRows) bulkValidRows.textContent = '0';
    if (bulkInvalidRows) bulkInvalidRows.textContent = '0';
    if (bulkValidationSummary) bulkValidationSummary.textContent = 'No validation performed yet. Use Validate to inspect the file before importing.';
    if (bulkValidationBody) {
        bulkValidationBody.innerHTML = '<tr><td class="px-3 py-3 text-slate-700" colspan="5">No validation results yet.</td></tr>';
    }
    lastValidationResult = null;
}

function showToast(type, message) {
    const typeClass = type === 'success'
        ? 'bg-emerald-50 border-emerald-300 text-emerald-900'
        : type === 'error'
            ? 'bg-rose-50 border-rose-300 text-rose-900'
            : 'bg-slate-50 border-slate-300 text-slate-900';

    const toast = document.createElement('div');
    toast.className = `fixed bottom-6 right-6 z-[99999] max-w-sm rounded-2xl border px-4 py-3 shadow-lg ${typeClass}`;
    toast.style.boxShadow = '0 20px 50px rgba(15,23,42,0.12)';

    const messageEl = document.createElement('div');
    messageEl.className = 'text-sm leading-6';
    messageEl.textContent = message;

    const closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'ml-3 inline-flex h-7 w-7 items-center justify-center rounded-full text-base font-semibold text-current opacity-80 hover:opacity-100';
    closeBtn.innerHTML = '&times;';
    closeBtn.addEventListener('click', () => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    });

    toast.appendChild(messageEl);
    toast.appendChild(closeBtn);
    document.body.appendChild(toast);

    window.setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 4500);
}

function updateValidationResults(data, action) {
    const totalRows = Number(data.total_rows ?? 0);
    const validRows = Number(data.valid_rows ?? 0);
    const failedRows = Number(data.failed_count ?? 0);
    const errors = data.failed_rows ?? [];
    const successCount = Number(data.inserted_rows ?? data.success_count ?? 0);

    if (bulkTotalRows) bulkTotalRows.textContent = totalRows;
    if (bulkValidRows) bulkValidRows.textContent = validRows;
    if (bulkInvalidRows) bulkInvalidRows.textContent = failedRows;

    if (bulkValidationSummary) {
        if (action === 'import') {
            bulkValidationSummary.textContent = successCount > 0
                ? `${successCount} user${successCount === 1 ? '' : 's'} imported successfully.`
                : 'No users were imported. Fix validation issues first.';
        } else {
            bulkValidationSummary.textContent = errors.length > 0
                ? `${failedRows} invalid row${failedRows === 1 ? '' : 's'} found.`
                : `${validRows} valid row${validRows === 1 ? '' : 's'} ready to import.`;
        }
    }

    if (bulkValidationBody) {
        bulkValidationBody.innerHTML = '';

        const generalErrors = Array.isArray(data.errors) ? data.errors : [];
        const rowErrors = Array.isArray(errors) ? errors : [];
        const allErrors = rowErrors.length ? rowErrors : generalErrors;

        if (allErrors.length === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = '<td class="px-3 py-3 text-slate-700" colspan="5">No validation errors found.</td>';
            bulkValidationBody.appendChild(tr);
        } else {
            const displayRows = rowErrors.length ? rowErrors : generalErrors;
            for (const row of displayRows) {
                const rowErrorsList = Array.isArray(row.errors) ? row.errors : [row.errors];
                for (const errorText of rowErrorsList) {
                    const fieldLabel = typeof errorText === 'object' && errorText !== null && errorText.field
                        ? errorText.field
                        : 'General';
                    const errorMessage = typeof errorText === 'object' && errorText !== null && errorText.message
                        ? errorText.message
                        : String(errorText);
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-50';
                    tr.innerHTML = `
                        <td class="px-3 py-3 text-slate-700">${row.row_number ?? '-'}</td>
                        <td class="px-3 py-3 text-slate-700">${row.username ?? ''}</td>
                        <td class="px-3 py-3 text-slate-700">${row.email ?? ''}</td>
                        <td class="px-3 py-3 text-slate-700">${fieldLabel}</td>
                        <td class="px-3 py-3 text-slate-700">${errorMessage}</td>
                    `;
                    bulkValidationBody.appendChild(tr);
                }
            }
        }
    }

    if (action === 'validate') {
        lastValidationResult = data;
    }

    if (bulkImportSubmit) {
        const shouldEnable = action === 'validate' && validRows > 0;
        bulkImportSubmit.disabled = !shouldEnable;
        bulkImportSubmit.classList.toggle('bg-blue-600', shouldEnable);
        bulkImportSubmit.classList.toggle('hover:bg-blue-700', shouldEnable);
        bulkImportSubmit.classList.toggle('bg-slate-200', !shouldEnable);
        bulkImportSubmit.classList.toggle('text-slate-500', !shouldEnable);
        bulkImportSubmit.classList.toggle('cursor-not-allowed', !shouldEnable);
    }
}

async function submitBulkAction(action) {
    if (!bulkFileInput?.files?.length) {
        alert('Please select a file before validating or importing.');
        return;
    }

    const formData = new FormData();
    formData.append('bulk_import_file', bulkFileInput.files[0]);
    formData.append('bulk_action', action);

    try {
        const response = await fetch('index.php?controller=StandardUsers&action=import', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error('Unable to submit bulk import request.');
        }

        const data = await response.json();
        updateValidationResults(data, action);
        if (action === 'validate') {
            if (data.failed_count > 0) {
                showToast('error', `${data.failed_count} invalid row${data.failed_count === 1 ? '' : 's'} found during validation.`);
            } else {
                showToast('success', `${data.valid_rows} valid row${data.valid_rows === 1 ? '' : 's'} ready for import.`);
            }
            return;
        }

        if (action === 'import') {
            if (data.inserted_rows > 0) {
                showToast('success', `${data.inserted_rows} user${data.inserted_rows === 1 ? '' : 's'} imported successfully.`);
                window.setTimeout(() => window.location.reload(), 1200);
            } else {
                showToast('error', 'Import completed with no rows inserted. Please resolve validation errors first.');
            }
        }
    } catch (error) {
        console.error(error);
        showToast('error', 'An error occurred while processing the bulk import. Please try again.');
    }
}

bulkValidateButton?.addEventListener('click', function() {
    submitBulkAction('validate');
});

bulkImportSubmit?.addEventListener('click', function() {
    submitBulkAction('import');
});

bulkFileInput?.addEventListener('change', function() {
    updateBulkImportFilename(this.files?.[0]);
    resetValidationState();
});

if (bulkDropzone && bulkFileInput) {
    ['dragenter', 'dragover'].forEach(event => {
        bulkDropzone.addEventListener(event, function(event) {
            event.preventDefault();
            this.classList.add('border-blue-300', 'bg-blue-50/70');
        });
    });

    ['dragleave', 'dragend', 'drop'].forEach(event => {
        bulkDropzone.addEventListener(event, function(event) {
            event.preventDefault();
            this.classList.remove('border-blue-300', 'bg-blue-50/70');
        });
    });

    bulkDropzone.addEventListener('drop', function(event) {
        const file = event.dataTransfer?.files?.[0];
        if (!file) {
            return;
        }

        bulkFileInput.files = event.dataTransfer.files;
        updateBulkImportFilename(file);
    });
}
</script>
