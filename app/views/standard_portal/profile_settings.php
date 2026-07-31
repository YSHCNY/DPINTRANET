<?php require __DIR__ . '/partials.php'; ?>
<?php portalHeader('Profile Settings'); ?>
<?php portalFlash(); ?>
<?php
$user = $user ?? [];
$errors = $errors ?? [];
$old = $old ?? [];

$getValue = function ($key, $fallback = '') use ($user, $old) {
    if (isset($old[$key])) {
        return htmlspecialchars($old[$key]);
    }
    if (isset($user[$key])) {
        return htmlspecialchars($user[$key]);
    }
    return htmlspecialchars($fallback);
};

$avatar = trim((string)($user['avatar'] ?? ''));
$avatarUrl = $avatar !== ''
    ? BASE_URL . 'uploads/standard_users/' . rawurlencode($avatar)
    : BASE_URL . 'uploads/standard_users/default.png';

$fullName = trim(($user['firstName'] ?? '') . ' ' . ($user['middleName'] ?? '') . ' ' . ($user['lastName'] ?? ''));
$fullName = preg_replace('/\s+/', ' ', $fullName);
$initials = strtoupper(substr(trim(($user['firstName'] ?? '')), 0, 1) . substr(trim(($user['lastName'] ?? '')), 0, 1));
$statusLabel = strtolower(trim((string)($user['status'] ?? 'inactive'))) === 'active' ? 'Active' : 'Inactive';
$portalAccess = !empty($user['is_portal_user']) ? 'Enabled' : 'Disabled';
$createdAt = !empty($user['created_at']) ? date('M d, Y g:i A', strtotime($user['created_at'])) : 'N/A';
$updatedAt = !empty($user['updated_at']) ? date('M d, Y g:i A', strtotime($user['updated_at'])) : 'N/A';
?>

<div class="space-y-4">
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-2">
                <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-emerald-600">Profile</p>
                <h1 class="text-2xl font-semibold text-slate-900">Profile Settings</h1>
                <p class="max-w-2xl text-sm leading-6 text-slate-500">Update your profile details, password, and profile image securely.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="index.php?controller=StandardPortal&action=dashboard" class="inline-flex h-10 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Back to dashboard</a>
            </div>
        </div>
    </div>

    <form id="profileSettingsForm" action="index.php?controller=StandardPortal&action=updateProfileSettings" method="post" enctype="multipart/form-data" class="grid gap-4 xl:grid-cols-[320px_minmax(0,1fr)]">
        <aside class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="space-y-4 text-center">
                <div class="relative mx-auto h-28 w-28 rounded-full bg-slate-100 shadow-sm overflow-hidden">
                    <?php if ($avatar !== ''): ?>
                        <img id="avatarPreview" src="<?= $avatarUrl ?>" alt="Avatar" class="h-full w-full object-cover" />
                    <?php else: ?>
                        <div id="avatarPlaceholder" class="flex h-full w-full items-center justify-center bg-slate-200 text-3xl font-semibold text-slate-600"><?= $initials ?></div>
                    <?php endif; ?>
                </div>
                <div class="space-y-1">
                    <p class="text-lg font-semibold text-slate-900"><?= htmlspecialchars($fullName ?: ($user['username'] ?? '')) ?></p>
                    <p class="text-sm text-slate-500">@<?= htmlspecialchars($user['username'] ?? '') ?></p>
                </div>
                <div class="space-y-2">
                    <p class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate-600"><?= htmlspecialchars($user['role'] ?? 'User') ?></p>
                    <div class="grid gap-2 text-sm text-slate-600">
                        <p><span class="font-semibold text-slate-900">Department:</span> <?= htmlspecialchars($user['department'] ?? '—') ?></p>
                        <p><span class="font-semibold text-slate-900">Position:</span> <?= htmlspecialchars($user['position'] ?? '—') ?></p>
                        <p><span class="font-semibold text-slate-900">Status:</span>
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.15em] <?= $statusLabel === 'Active' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' ?>"><?= $statusLabel ?></span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Account details</p>
                <div class="grid gap-3 text-sm text-slate-700">
                    <div class="space-y-1">
                        <p class="font-semibold text-slate-900">Username</p>
                        <p class="text-slate-500"><?= htmlspecialchars($user['username'] ?? '—') ?></p>
                    </div>
                    <div class="space-y-1">
                        <p class="font-semibold text-slate-900">Role</p>
                        <p class="text-slate-500"><?= htmlspecialchars($user['role'] ?? '—') ?></p>
                    </div>
                    <div class="space-y-1">
                        <p class="font-semibold text-slate-900">Department</p>
                        <p class="text-slate-500"><?= htmlspecialchars($user['department'] ?? '—') ?></p>
                    </div>
                    <div class="space-y-1">
                        <p class="font-semibold text-slate-900">Position</p>
                        <p class="text-slate-500"><?= htmlspecialchars($user['position'] ?? '—') ?></p>
                    </div>
                    <div class="space-y-1">
                        <p class="font-semibold text-slate-900">Portal access</p>
                        <p class="text-slate-500"><?= htmlspecialchars($portalAccess) ?></p>
                    </div>
                    <div class="space-y-1">
                        <p class="font-semibold text-slate-900">Created</p>
                        <p class="text-slate-500"><?= htmlspecialchars($createdAt) ?></p>
                    </div>
                    <div class="space-y-1">
                        <p class="font-semibold text-slate-900">Last updated</p>
                        <p class="text-slate-500"><?= htmlspecialchars($updatedAt) ?></p>
                    </div>
                </div>
            </div>
        </aside>

        <div class="space-y-4">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Personal Information</p>
                        <h2 class="mt-1 text-lg font-semibold text-slate-900">Basic details</h2>
                    </div>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    <div>
                        <label for="firstName" class="mb-2 block text-sm font-semibold text-slate-700">First Name</label>
                        <input id="firstName" name="firstName" type="text" value="<?= $getValue('firstName') ?>" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        <?php if (!empty($errors['firstName'])): ?><p class="mt-2 text-sm text-rose-600"><?= htmlspecialchars($errors['firstName']) ?></p><?php endif; ?>
                    </div>
                    <div>
                        <label for="middleName" class="mb-2 block text-sm font-semibold text-slate-700">Middle Name</label>
                        <input id="middleName" name="middleName" type="text" value="<?= $getValue('middleName') ?>" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        <?php if (!empty($errors['middleName'])): ?><p class="mt-2 text-sm text-rose-600"><?= htmlspecialchars($errors['middleName']) ?></p><?php endif; ?>
                    </div>
                    <div>
                        <label for="lastName" class="mb-2 block text-sm font-semibold text-slate-700">Last Name</label>
                        <input id="lastName" name="lastName" type="text" value="<?= $getValue('lastName') ?>" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        <?php if (!empty($errors['lastName'])): ?><p class="mt-2 text-sm text-rose-600"><?= htmlspecialchars($errors['lastName']) ?></p><?php endif; ?>
                    </div>
                    <div>
                        <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email Address</label>
                        <input id="email" name="email" type="email" value="<?= $getValue('email') ?>" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        <?php if (!empty($errors['email'])): ?><p class="mt-2 text-sm text-rose-600"><?= htmlspecialchars($errors['email']) ?></p><?php endif; ?>
                    </div>
                    <div>
                        <label for="phone" class="mb-2 block text-sm font-semibold text-slate-700">Phone Number</label>
                        <input id="phone" name="phone" type="tel" value="<?= $getValue('phone') ?>" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        <?php if (!empty($errors['phone'])): ?><p class="mt-2 text-sm text-rose-600"><?= htmlspecialchars($errors['phone']) ?></p><?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Account Information</p>
                        <h2 class="mt-1 text-lg font-semibold text-slate-900">Read only</h2>
                    </div>
                </div>

                <?php $maskedPin = !empty($user['pin_code']) ? str_repeat('*', max(4, strlen($user['pin_code']))) : '—'; ?>
                <div class="grid gap-4 lg:grid-cols-2">
                    <?php foreach (['username' => 'Username', 'role' => 'Role', 'department' => 'Department', 'position' => 'Position', 'status' => 'Account Status', 'is_portal_user' => 'Portal Access', 'created_at' => 'Created At', 'updated_at' => 'Last Updated'] as $field => $label): ?>
                        <div>
                            <p class="text-sm font-semibold text-slate-700"><?= $label ?></p>
                            <p class="mt-1 text-sm text-slate-500"><?= htmlspecialchars($field === 'is_portal_user' ? ($user[$field] ? 'Enabled' : 'Disabled') : ($user[$field] ?? '—')) ?></p>
                        </div>
                    <?php endforeach; ?>
                    <div>
                        <p class="text-sm font-semibold text-slate-700">ID</p>
                        <p class="mt-1 text-sm text-slate-500"><?= htmlspecialchars($user['id'] ?? '—') ?></p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-700">PIN Code</p>
                        <p class="mt-1 text-sm text-slate-500"><?= htmlspecialchars($maskedPin) ?></p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Security</p>
                        <h2 class="mt-1 text-lg font-semibold text-slate-900">Change password</h2>
                    </div>
                </div>

                <div class="grid gap-4 lg:grid-cols-3">
                    <div>
                        <label for="current_password" class="mb-2 block text-sm font-semibold text-slate-700">Current Password</label>
                        <div class="relative">
                            <input id="current_password" name="current_password" type="password" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 pr-12 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                            <button type="button" class="password-toggle absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm">Show</button>
                        </div>
                        <?php if (!empty($errors['current_password'])): ?><p class="mt-2 text-sm text-rose-600"><?= htmlspecialchars($errors['current_password']) ?></p><?php endif; ?>
                    </div>
                    <div>
                        <label for="new_password" class="mb-2 block text-sm font-semibold text-slate-700">New Password</label>
                        <div class="relative">
                            <input id="new_password" name="new_password" type="password" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 pr-12 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                            <button type="button" class="password-toggle absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm">Show</button>
                        </div>
                        <?php if (!empty($errors['new_password'])): ?><p class="mt-2 text-sm text-rose-600"><?= htmlspecialchars($errors['new_password']) ?></p><?php endif; ?>
                    </div>
                    <div>
                        <label for="confirm_password" class="mb-2 block text-sm font-semibold text-slate-700">Confirm Password</label>
                        <div class="relative">
                            <input id="confirm_password" name="confirm_password" type="password" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 pr-12 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                            <button type="button" class="password-toggle absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm">Show</button>
                        </div>
                        <?php if (!empty($errors['confirm_password'])): ?><p class="mt-2 text-sm text-rose-600"><?= htmlspecialchars($errors['confirm_password']) ?></p><?php endif; ?>
                    </div>
                </div>
                <div class="mt-4">
                    <div id="passwordStrength" class="text-sm font-medium text-slate-500">Password strength: <span class="font-semibold text-slate-700">None</span></div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">PIN Code</p>
                        <h2 class="mt-1 text-lg font-semibold text-slate-900">Change PIN</h2>
                    </div>
                </div>

                <div class="grid gap-4 lg:grid-cols-3">
                    <div>
                        <label for="current_pin" class="mb-2 block text-sm font-semibold text-slate-700">Current PIN</label>
                        <div class="relative">
                            <input id="current_pin" name="current_pin" type="password" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 pr-12 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                            <button type="button" class="password-toggle absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm">Show</button>
                        </div>
                        <?php if (!empty($errors['current_pin'])): ?><p class="mt-2 text-sm text-rose-600"><?= htmlspecialchars($errors['current_pin']) ?></p><?php endif; ?>
                    </div>
                    <div>
                        <label for="new_pin" class="mb-2 block text-sm font-semibold text-slate-700">New PIN</label>
                        <div class="relative">
                            <input id="new_pin" name="new_pin" type="password" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 pr-12 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                            <button type="button" class="password-toggle absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm">Show</button>
                        </div>
                        <?php if (!empty($errors['new_pin'])): ?><p class="mt-2 text-sm text-rose-600"><?= htmlspecialchars($errors['new_pin']) ?></p><?php endif; ?>
                    </div>
                    <div>
                        <label for="confirm_pin" class="mb-2 block text-sm font-semibold text-slate-700">Confirm PIN</label>
                        <div class="relative">
                            <input id="confirm_pin" name="confirm_pin" type="password" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 pr-12 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                            <button type="button" class="password-toggle absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm">Show</button>
                        </div>
                        <?php if (!empty($errors['confirm_pin'])): ?><p class="mt-2 text-sm text-rose-600"><?= htmlspecialchars($errors['confirm_pin']) ?></p><?php endif; ?>
                    </div>
                </div>
                <p class="mt-4 text-sm text-slate-500">Your new PIN must be different from your password.</p>
            </section>
                <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Profile Picture</p>
                        <h2 class="mt-1 text-lg font-semibold text-slate-900">Avatar</h2>
                    </div>
                </div>

                <div class="space-y-4">
                    <div id="avatarDropZone" class="group relative overflow-hidden rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center transition hover:border-emerald-300 hover:bg-white">
                        <input id="avatarInput" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" class="absolute inset-0 h-full w-full cursor-pointer opacity-0" />
                        <div class="space-y-3">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-3xl bg-white text-emerald-600 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V8m0 0l-3 3m3-3l3 3m6 0V8m0 0l-3 3m3-3l3 3M7 12h10"/></svg>
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-semibold text-slate-900">Upload or drag and drop a new avatar</p>
                                <p class="text-sm text-slate-500">JPG, PNG, WEBP up to 3MB. Existing avatar will be replaced.</p>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($errors['avatar'])): ?><p class="text-sm text-rose-600"><?= htmlspecialchars($errors['avatar']) ?></p><?php endif; ?>
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="button" id="removeAvatarButton" class="inline-flex h-11 items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Remove avatar</button>
                        <p class="text-sm text-slate-500">Preview updates before saving.</p>
                    </div>
                </div>
            </section>

            <div class="sticky bottom-0 z-10 rounded-3xl border border-slate-200 bg-white/95 p-4 shadow-xl shadow-slate-100 backdrop-blur-md lg:static lg:border-transparent lg:bg-transparent lg:p-0 lg:shadow-none">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="text-sm text-slate-500">Make sure you save changes before leaving.</div>
                    <button id="saveProfileButton" type="submit" class="inline-flex h-11 items-center justify-center rounded-2xl bg-emerald-600 px-5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60">
                        <span id="saveButtonText">Save Changes</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
(function() {
    const form = document.getElementById('profileSettingsForm');
    const saveButton = document.getElementById('saveProfileButton');
    const saveButtonText = document.getElementById('saveButtonText');
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');
    const avatarPlaceholder = document.getElementById('avatarPlaceholder');
    const avatarDropZone = document.getElementById('avatarDropZone');
    const removeAvatarButton = document.getElementById('removeAvatarButton');
    const currentPassword = document.getElementById('current_password');
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    const passwordStrength = document.getElementById('passwordStrength');
    const passwordToggles = document.querySelectorAll('.password-toggle');
    let initialFormData = new FormData(form);

    const getPasswordStrengthLabel = (value) => {
        if (!value) return 'None';
        if (value.length < 8) return 'Too weak';
        if (/[A-Z]/.test(value) && /[0-9]/.test(value) && /[^A-Za-z0-9]/.test(value)) return 'Strong';
        if ((/[A-Z]/.test(value) && /[0-9]/.test(value)) || (/[0-9]/.test(value) && /[^A-Za-z0-9]/.test(value))) return 'Medium';
        return 'Weak';
    };

    const updatePasswordStrength = () => {
        const label = getPasswordStrengthLabel(newPassword.value);
        const strengthColor = {
            'None': 'text-slate-700',
            'Too weak': 'text-rose-600',
            'Weak': 'text-amber-600',
            'Medium': 'text-emerald-600',
            'Strong': 'text-emerald-800',
        };
        passwordStrength.innerHTML = 'Password strength: <span class="font-semibold ' + strengthColor[label] + '">' + label + '</span>';
    };

    newPassword.addEventListener('input', updatePasswordStrength);

    passwordToggles.forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const input = toggle.closest('div').querySelector('input');
            if (!input) return;
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            toggle.textContent = isPassword ? 'Hide' : 'Show';
        });
    });

    const handleDragState = (active) => {
        if (!avatarDropZone) return;
        avatarDropZone.classList.toggle('border-emerald-300', active);
        avatarDropZone.classList.toggle('bg-white', active);
    };

    if (avatarDropZone) {
        avatarDropZone.addEventListener('dragover', (event) => {
            event.preventDefault();
            handleDragState(true);
        });
        avatarDropZone.addEventListener('dragleave', () => handleDragState(false));
        avatarDropZone.addEventListener('drop', (event) => {
            event.preventDefault();
            handleDragState(false);
            const file = event.dataTransfer.files[0];
            if (file) {
                avatarInput.files = event.dataTransfer.files;
                loadAvatarPreview(file);
            }
        });
    }

    avatarInput.addEventListener('change', () => {
        const file = avatarInput.files[0];
        if (file) {
            loadAvatarPreview(file);
        }
    });

    removeAvatarButton.addEventListener('click', () => {
        if (avatarPreview) {
            avatarPreview.src = '<?= BASE_URL ?>uploads/standard_users/default.png';
        }
        if (avatarPlaceholder) {
            avatarPlaceholder.textContent = '<?= $initials ?>';
        }
        form.insertAdjacentHTML('beforeend', '<input type="hidden" name="remove_avatar" value="1" />');
    });

    const loadAvatarPreview = (file) => {
        if (!file.type.match(/^image\/(jpeg|png|webp)$/)) return;
        const reader = new FileReader();
        reader.onload = (event) => {
            if (avatarPlaceholder) {
                avatarPlaceholder.style.display = 'none';
            }
            if (avatarPreview) {
                avatarPreview.src = event.target.result;
            }
        };
        reader.readAsDataURL(file);
    };

    form.addEventListener('submit', () => {
        saveButton.disabled = true;
        saveButtonText.textContent = 'Saving...';
    });

    window.addEventListener('beforeunload', (event) => {
        const currentData = new FormData(form);
        for (const [key, value] of currentData.entries()) {
            if (!initialFormData.has(key) || initialFormData.get(key) !== value) {
                event.preventDefault();
                event.returnValue = '';
                return '';
            }
        }
    });
})();
</script>

<?php portalFooter(); ?>
