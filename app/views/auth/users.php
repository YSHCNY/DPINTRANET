<?php
$isEdit = !empty($editingUser);
$formAction = $isEdit
    ? 'index.php?controller=Auth&action=update&id=' . (int)$editingUser['id']
    : 'index.php?controller=Auth&action=register';
$accessLevels = $accessLevels ?? [1 => 'Admin', 2 => 'Encoder', 3 => 'Viewer'];

function coreUserValue($editingUser, $key, $default = '') {
    return htmlspecialchars($editingUser[$key] ?? $default);
}

function coreUserLevelLabel($level) {
    $labels = [
        0 => 'Super Admin',
        1 => 'Admin',
        2 => 'Encoder',
        3 => 'Viewer',
    ];

    return $labels[(int)$level] ?? 'Viewer';
}

function coreUserLevelClass($level) {
    return match ((int)$level) {
        0 => 'bg-purple-50 text-purple-700 border-purple-100',
        1 => 'bg-green-50 text-green-700 border-green-100',
        2 => 'bg-blue-50 text-blue-700 border-blue-100',
        default => 'bg-gray-50 text-gray-700 border-gray-100',
    };
}
?>

<div class="space-y-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-600">Core Users</p>
                <h2 class="text-xl font-semibold text-gray-900 mt-0.5"><?= $isEdit ? 'Edit Core User' : 'Create Core User' ?></h2>
            </div>

            <?php if ($isEdit): ?>
                <a href="index.php?controller=Auth&action=users"
                   class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Cancel Edit
                </a>
            <?php endif; ?>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mx-5 mt-5 rounded-lg border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="<?= $formAction ?>" method="POST" enctype="multipart/form-data" class="p-5">
            <div class="grid grid-cols-1 xl:grid-cols-[220px_minmax(0,1fr)] gap-6">
                <div class="space-y-4">
                    <div class="border border-gray-200 rounded-lg bg-gray-50 p-4">
                        <img id="coreAvatarPreview"
                             src="<?= BASE_URL . '/uploads/profile/' . htmlspecialchars((!empty(trim($editingUser['profile_picture'] ?? '')) ? trim($editingUser['profile_picture']) : 'default.png')) ?>"
                             class="w-24 h-24 rounded-full object-cover border border-gray-200 bg-white mx-auto"
                             alt="Core user avatar">

                        <label class="block text-xs font-semibold text-gray-700 mt-4 mb-1.5">Profile Picture</label>
                        <input type="file" name="profile_picture" id="coreAvatarInput" accept="image/jpeg,image/png,image/webp"
                               class="w-full text-sm file:mr-3 file:border-0 file:bg-white file:text-gray-700 file:rounded-md file:px-3 file:py-1.5 hover:file:bg-gray-100">
                        <p class="text-xs text-gray-500 mt-2">JPG, PNG, or WEBP. Max 2MB.</p>
                    </div>

                    <div class="rounded-lg border border-blue-100 bg-blue-50 px-3 py-3">
                        <p class="text-sm font-semibold text-blue-900">Restricted Module</p>
                        <p class="text-xs text-blue-700 mt-1">Only Super Admin can manage Core Users after the first Super Admin is created.</p>
                    </div>
                </div>

                <div class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">First Name <span class="text-red-500">*</span></label>
                            <input type="text" name="firstName" required value="<?= coreUserValue($editingUser, 'firstName') ?>"
                                   class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" name="lastName" required value="<?= coreUserValue($editingUser, 'lastName') ?>"
                                   class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Position <span class="text-red-500">*</span></label>
                            <input type="text" name="position" required value="<?= coreUserValue($editingUser, 'position') ?>"
                                   class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Username <span class="text-red-500">*</span></label>
                            <input type="text" name="username" required value="<?= coreUserValue($editingUser, 'username') ?>"
                                   class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Email</label>
                            <input type="email" name="email" value="<?= coreUserValue($editingUser, 'email') ?>"
                                   class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Password <?= $isEdit ? '<span class="text-gray-400">(optional)</span>' : '<span class="text-red-500">*</span>' ?></label>
                            <input type="password" name="password" <?= $isEdit ? '' : 'required' ?>
                                   class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Confirm Password <?= $isEdit ? '<span class="text-gray-400">(optional)</span>' : '<span class="text-red-500">*</span>' ?></label>
                            <input type="password" name="confirm_password" <?= $isEdit ? '' : 'required' ?>
                                   class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Access Level</label>
                            <?php $selectedLevel = (int)($editingUser['userLevel'] ?? 3); ?>
                            <select name="user_level" class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm bg-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <?php foreach ($accessLevels as $level => $label): ?>
                                    <option value="<?= (int)$level ?>" <?= $selectedLevel === (int)$level ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
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
                            <?= $isEdit ? 'Save Changes' : 'Create Core User' ?>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-600">Directory</p>
                <h2 class="text-xl font-semibold text-gray-900 mt-0.5">Core Users</h2>
            </div>
            <span class="inline-flex w-fit items-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-medium text-gray-600">
                <?= count($users) ?> user(s)
            </span>
        </div>

        <div class="p-5 overflow-x-auto">
            <table id="coreUsersTable" class="w-full text-sm">
                <thead class="bg-gray-50 border-y border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">User</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Username</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Position</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500">Access</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-blue-50/60 transition-colors">
                            <td class="px-4 py-3 min-w-[230px]">
                                <div class="flex items-center gap-3">
                                    <img src="<?= BASE_URL . '/uploads/profile/' . htmlspecialchars((!empty(trim($user['profile_picture'] ?? '')) ? trim($user['profile_picture']) : 'default.png')) ?>" class="w-10 h-10 rounded-full object-cover border border-gray-200" alt="Profile">
                                    <div>
                                        <p class="font-semibold text-gray-900">
                                            <?= htmlspecialchars(trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? ''))) ?>
                                        </p>
                                        <p class="text-xs text-gray-500">ID #<?= htmlspecialchars($user['id']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-mono text-gray-700"><?= htmlspecialchars($user['username']) ?></td>
                            <td class="px-4 py-3 text-gray-700"><?= htmlspecialchars($user['email'] ?? '') ?></td>
                            <td class="px-4 py-3 text-gray-700"><?= htmlspecialchars($user['position']) ?></td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex rounded-md border px-2.5 py-1 text-xs font-semibold <?= coreUserLevelClass($user['userLevel']) ?>">
                                    <?= htmlspecialchars(coreUserLevelLabel($user['userLevel'])) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-center gap-2">
                                    <a href="index.php?controller=Auth&action=edit&id=<?= (int)$user['id'] ?>"
                                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-blue-100 bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white transition"
                                       title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.651-1.651a1.875 1.875 0 112.652 2.652L9.75 16.903 6 18l1.097-3.75L16.862 4.487z" />
                                        </svg>
                                    </a>
                                    <a href="index.php?controller=Auth&action=delete&id=<?= (int)$user['id'] ?>"
                                       onclick="return confirm('Delete this core user?')"
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
    #coreUsersTable_wrapper .dataTables_length label,
    #coreUsersTable_wrapper .dataTables_filter label,
    #coreUsersTable_wrapper .dataTables_info {
        color: #4b5563;
        font-size: 0.875rem;
    }

    #coreUsersTable_wrapper .dataTables_filter input,
    #coreUsersTable_wrapper .dataTables_length select {
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        color: var(--secondary);
        font-size: 0.875rem;
        min-height: 2.5rem;
        outline: none;
    }

    #coreUsersTable_wrapper .dataTables_filter input {
        margin-left: 0;
        padding: 0 0.75rem;
        width: min(100%, 260px);
    }
</style>

<script>
$(document).ready(function() {
    $('#coreUsersTable').DataTable({
        pageLength: 25,
        order: [[3, 'asc'], [0, 'asc']],
        dom: '<"flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4"lf>rt<"flex flex-col md:flex-row md:items-center md:justify-between gap-3 mt-4"ip>',
        language: {
            search: '',
            searchPlaceholder: 'Search core users'
        }
    });
});

document.getElementById('coreAvatarInput')?.addEventListener('change', function() {
    const file = this.files?.[0];
    if (!file) return;

    document.getElementById('coreAvatarPreview').src = URL.createObjectURL(file);
});
</script>
