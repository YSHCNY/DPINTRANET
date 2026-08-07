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

<div class="space-y-6">
    <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-2">
                <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-emerald-600">Profile</p>
                <h1 class="text-xl font-semibold text-slate-900 lg:text-2xl">Profile Settings</h1>
                <p class="max-w-2xl text-sm leading-6 text-slate-500">Update your profile details, password, and profile image securely.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="index.php?controller=StandardPortal&action=dashboard" class="inline-flex h-11 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Back to dashboard</a>
            </div>
        </div>
    </div>

    <form id="profileSettingsForm" action="index.php?controller=StandardPortal&action=updateProfileSettings" method="post" enctype="multipart/form-data" class="grid gap-6 xl:grid-cols-[320px_minmax(0,1fr)]">
        <?php require_once __DIR__ . '/../../Services/CsrfService.php'; ?>
        <meta name="csrf-token" content="<?= \App\Services\CsrfService::token() ?>">
        <aside class="space-y-6 rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <div class="space-y-5 text-center">
                <div class="relative mx-auto h-24 w-24 overflow-hidden rounded-full border border-slate-200 bg-slate-100 shadow-sm">
                    <?php if ($avatar !== ''): ?>
                        <img id="avatarPreview" src="<?= $avatarUrl ?>" alt="Avatar" class="h-full w-full object-cover" />
                    <?php else: ?>
                        <div id="avatarPlaceholder" class="flex h-full w-full items-center justify-center bg-slate-200 text-3xl font-semibold text-slate-600"><?= $initials ?></div>
                    <?php endif; ?>
                </div>
                <div class="space-y-2">
                    <p class="text-xl font-semibold text-slate-900"><?= htmlspecialchars($fullName ?: ($user['username'] ?? '')) ?></p>
                    <p class="text-sm text-slate-500">@<?= htmlspecialchars($user['username'] ?? '') ?></p>
                </div>
                <div class="flex flex-wrap justify-center gap-2 text-sm">
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-600"><?= htmlspecialchars($user['role'] ?? 'User') ?></span>
                    <?php if (!empty($user['department'])): ?><span class="inline-flex items-center rounded-full bg-slate-50 px-3 py-1 text-[11px] font-semibold text-slate-700 border border-slate-200"><?= htmlspecialchars($user['department']) ?></span><?php endif; ?>
                    <?php if (!empty($user['position'])): ?><span class="inline-flex items-center rounded-full bg-slate-50 px-3 py-1 text-[11px] font-semibold text-slate-700 border border-slate-200"><?= htmlspecialchars($user['position']) ?></span><?php endif; ?>
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.15em] <?= $statusLabel === 'Active' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' ?>"><?= $statusLabel ?></span>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200/80 bg-slate-50 p-5">
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

        <div class="space-y-6">
            <section class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <div class="mb-6 flex flex-col gap-2">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.26em] text-slate-500">Personal information</p>
                        <h2 class="mt-2 text-xl font-semibold text-slate-900">Basic details</h2>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label for="firstName" class="block text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">First name</label>
                        <input id="firstName" name="firstName" type="text" value="<?= $getValue('firstName') ?>" class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        <?php if (!empty($errors['firstName'])): ?><p class="mt-1 text-sm text-rose-600"><?= htmlspecialchars($errors['firstName']) ?></p><?php endif; ?>
                    </div>
                    <div class="space-y-2">
                        <label for="middleName" class="block text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Middle name</label>
                        <input id="middleName" name="middleName" type="text" value="<?= $getValue('middleName') ?>" class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        <?php if (!empty($errors['middleName'])): ?><p class="mt-1 text-sm text-rose-600"><?= htmlspecialchars($errors['middleName']) ?></p><?php endif; ?>
                    </div>
                    <div class="space-y-2">
                        <label for="lastName" class="block text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Last name</label>
                        <input id="lastName" name="lastName" type="text" value="<?= $getValue('lastName') ?>" class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        <?php if (!empty($errors['lastName'])): ?><p class="mt-1 text-sm text-rose-600"><?= htmlspecialchars($errors['lastName']) ?></p><?php endif; ?>
                    </div>
                    <div class="space-y-2">
                        <label for="email" class="block text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Email address</label>
                        <input id="email" name="email" type="email" value="<?= $getValue('email') ?>" class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        <?php if (!empty($errors['email'])): ?><p class="mt-1 text-sm text-rose-600"><?= htmlspecialchars($errors['email']) ?></p><?php endif; ?>
                    </div>
                    <div class="space-y-2 sm:col-span-2">
                        <label for="phone" class="block text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Phone number</label>
                        <input id="phone" name="phone" type="tel" value="<?= $getValue('phone') ?>" class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        <?php if (!empty($errors['phone'])): ?><p class="mt-1 text-sm text-rose-600"><?= htmlspecialchars($errors['phone']) ?></p><?php endif; ?>
                    </div>
                </div>
            </section>

                        <section id="trustedDevicesBox" class="rounded-xl border border-slate-200 bg-white p-5 shadow-[0_1px_2px_rgba(15,23,42,0.04)]">
                                <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                        <div>
                                                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Account security</p>
                                                <h2 class="mt-2 text-2xl font-semibold text-slate-900">Trusted devices</h2>
                                                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Review the browsers and devices you've trusted for secure access to the portal.</p>
                                        </div>

                                        <button type="button" id="removeOthersBtn" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-rose-700 transition duration-150 hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-200">
                                            <svg class="mr-2 h-4 w-4 text-rose-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/><path d="M11 14v4"/><path d="M13 14v4"/></svg>
                                            Remove other devices
                                        </button>
                                </div>

                                <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 px-4 py-4">
                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Current device</p>
                                            <p id="trustedSummaryCurrent" class="mt-1 text-lg font-semibold text-slate-900">Loading…</p>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-3">
                                            <div class="rounded-lg border border-slate-200 bg-white px-3 py-2">
                                                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Trusted devices</p>
                                                <p id="trustedSummaryTotal" class="mt-1 text-sm font-semibold text-slate-700">0</p>
                                            </div>
                                            <div class="rounded-lg border border-slate-200 bg-white px-3 py-2">
                                                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Last used</p>
                                                <p id="trustedSummaryLastActivity" class="mt-1 text-sm font-semibold text-slate-700">Loading…</p>
                                            </div>
                                            <div class="rounded-lg border border-slate-200 bg-white px-3 py-2">
                                                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Trusted until</p>
                                                <p id="trustedSummaryTrustedUntil" class="mt-1 text-sm font-semibold text-slate-700">Loading…</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-8 overflow-hidden rounded-xl border border-slate-200 bg-white">
                                    <div class="overflow-hidden">
                                        <table id="trustedDevicesTable" class="w-full min-w-full table-fixed text-xs">
                                            <thead class="bg-slate-50 text-left text-[11px] uppercase tracking-[0.2em] text-slate-500">
                                                <tr class="hidden sm:table-row">
                                                    <th class="px-3 py-3 text-left align-top break-words">Device</th>
                                                    <th class="px-3 py-3 text-left align-top break-words">Registered</th>
                                                    <th class="px-3 py-3 text-left align-top break-words">Last used</th>
                                                    <th class="px-3 py-3 text-left align-top break-words">Status</th>
                                                    <th class="px-3 py-3 text-left align-top break-words">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="trustedDevicesTbody">
                                                <tr class="animate-pulse">
                                                    <td colspan="7" class="px-3 py-10">
                                                        <div class="space-y-4">
                                                            <div class="h-4 w-1/3 rounded-full bg-slate-200"></div>
                                                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                                                <div class="h-10 rounded-2xl bg-slate-200"></div>
                                                                <div class="h-10 rounded-2xl bg-slate-200"></div>
                                                                <div class="h-10 rounded-2xl bg-slate-200"></div>
                                                                <div class="h-10 rounded-2xl bg-slate-200"></div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                        </section>

                        <section class="mt-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-900 text-white">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2L4 5v6c0 5 3 9 8 11 5-2 8-6 8-11V5l-8-3z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">Security tips</p>
                                    <p class="mt-1 text-sm text-slate-500">Keep your portal access locked down with these best practices.</p>
                                </div>
                            </div>
                            <ul class="mt-4 space-y-3 text-sm text-slate-600">
                                <li class="flex gap-3"><span class="mt-0.5 inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>Only trust personal devices.</li>
                                <li class="flex gap-3"><span class="mt-0.5 inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>Remove devices you no longer use.</li>
                                <li class="flex gap-3"><span class="mt-0.5 inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>Never trust shared or public computers.</li>
                                <li class="flex gap-3"><span class="mt-0.5 inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>Remove unknown devices immediately.</li>
                            </ul>
                        </section>

                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const tbody = document.getElementById('trustedDevicesTbody');
                            const removeOthersBtn = document.getElementById('removeOthersBtn');
                            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                            const summaryTotal = document.getElementById('trustedSummaryTotal');
                            const summaryCurrent = document.getElementById('trustedSummaryCurrent');
                            const summaryLastActivity = document.getElementById('trustedSummaryLastActivity');
                            const summaryTrustedUntil = document.getElementById('trustedSummaryTrustedUntil');

                            function formatDisplayDate(value) {
                                if (!value) return '—';
                                const date = new Date(value.replace(' ', 'T'));
                                if (Number.isNaN(date.getTime())) return '—';

                                const now = new Date();
                                const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                                const target = new Date(date.getFullYear(), date.getMonth(), date.getDate());
                                const diff = Math.round((target - today) / 86400000);

                                const timePart = date.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' }).replace(/:00\s*$/, '');
                                if (diff === 0) return `Today, ${timePart}`;
                                if (diff === -1) return `Yesterday, ${timePart}`;
                                return date.toLocaleDateString([], { month: 'short', day: 'numeric', year: 'numeric' });
                            }

                            function formatExpiration(value) {
                                if (!value) return '—';
                                const expires = new Date(value.replace(' ', 'T'));
                                if (Number.isNaN(expires.getTime())) return '—';

                                const now = new Date();
                                const diffMs = expires.getTime() - now.getTime();
                                const diffDays = Math.ceil(diffMs / 86400000);
                                if (diffMs < 0) return 'Expired';
                                if (diffDays === 0) return 'Expires today';
                                return `Expires in ${diffDays} day${diffDays === 1 ? '' : 's'}`;
                            }

                            function statusBadge(device) {
                                const base = 'inline-flex rounded-full px-3 py-1 text-xs font-semibold';
                                if (device.revoked_at || device.is_revoked || device.revoked) {
                                    return `<span class="${base} bg-rose-100 text-rose-700 border border-rose-200">Revoked</span>`;
                                }
                                if (device.is_current) {
                                    return `<span class="${base} bg-emerald-100 text-emerald-700 border border-emerald-200">Active</span>`;
                                }
                                return `<span class="${base} bg-slate-100 text-slate-700 border border-slate-200">Trusted</span>`;
                            }

                            function deviceTypeIcon(deviceName) {
                                const name = (deviceName || '').toLowerCase();
                                const isMobile = /mobile|iphone|ipad|android|phone|tablet/i.test(name);
                                if (isMobile) {
                                    return `<span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-900 text-white"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="7" y="3" width="10" height="18" rx="2"/><path d="M11 4h2"/></svg></span>`;
                                }
                                return `<span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-900 text-white"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 5v14"/><path d="M17 5v14"/></svg></span>`;
                            }

                            function escapeHtml(s) {
                                if (s === null || s === undefined) return '';
                                return String(s).replace(/[&<>"']/g, function(m) { return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[m]; });
                            }

                            function renderLoading() {
                                tbody.innerHTML = `
                                    <tr class="animate-pulse">
                                        <td colspan="7" class="px-4 py-10">
                                            <div class="space-y-4">
                                                <div class="h-4 w-1/3 rounded-full bg-slate-200"></div>
                                                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                                    <div class="h-10 rounded-2xl bg-slate-200"></div>
                                                    <div class="h-10 rounded-2xl bg-slate-200"></div>
                                                    <div class="h-10 rounded-2xl bg-slate-200"></div>
                                                    <div class="h-10 rounded-2xl bg-slate-200"></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>`;
                            }

                            function renderEmptyState() {
                                tbody.innerHTML = `
                                    <tr>
                                        <td colspan="7" class="px-4 py-12">
                                            <div class="flex flex-col items-center justify-center gap-4 rounded-3xl border border-dashed border-slate-200 bg-white p-10 text-center text-slate-600">
                                                <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-slate-500">
                                                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1.5L3 5v6.5c0 5.5 3.5 10 9 11 5.5-1 9-5.5 9-11V5l-8-3z"/><path d="M12 7v5"/><path d="M12 16h.01"/></svg>
                                                </div>
                                                <div class="max-w-md space-y-2">
                                                    <p class="text-lg font-semibold text-slate-900">No Trusted Devices</p>
                                                    <p class="text-sm text-slate-500">You haven't trusted any browsers yet.</p>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>`;
                            }

                            function updateSummary(devices) {
                                summaryTotal.textContent = String(devices.length);
                                const currentDevice = devices.find(d => d.is_current) || devices[0] || {};
                                summaryCurrent.textContent = currentDevice.device_name || currentDevice.browser_name || 'Current device';
                                summaryLastActivity.textContent = formatDisplayDate(currentDevice.last_used_at || currentDevice.created_at);
                                summaryTrustedUntil.textContent = formatExpiration(currentDevice.expires_at);
                            }

                            async function loadDevices() {
                                renderLoading();
                                try {
                                    const res = await fetch('index.php?controller=TrustedDevices&action=listAjax', {
                                        credentials: 'same-origin',
                                        headers: {
                                            'X-CSRF-Token': csrf,
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'Accept': 'application/json',
                                        },
                                    });
                                    const data = await res.json();

                                    if (!data.success) {
                                        throw new Error(data.message || 'Failed to load');
                                    }

                                    const devices = Array.isArray(data.data && data.data.devices ? data.data.devices : []) ? data.data.devices : [];
                                    console.debug('Trusted devices count:', devices.length);
                                    if (devices.length === 0) {
                                        renderEmptyState();
                                        updateSummary([]);
                                        return;
                                    }

                                    updateSummary(devices);
                                    tbody.innerHTML = '';
                                    devices.forEach((d) => {
                                        const tr = document.createElement('tr');
                                        tr.className = 'grid gap-4 border-t bg-white px-4 py-4 sm:table-row sm:grid-none sm:px-0 sm:py-0';

                                        const deviceName = d.device_name || 'Unknown device';
                                        const browser = d.browser_name || 'Unknown browser';
                                        const os = d.operating_system || 'Unknown OS';
                                        const created = formatDisplayDate(d.created_at);
                                        const lastUsed = formatDisplayDate(d.last_used_at);
                                        const expiresText = formatExpiration(d.expires_at);
                                        const statusChip = statusBadge(d);
                                        const icon = deviceTypeIcon(deviceName);

                                        tr.innerHTML = `
                                            <td class="py-3 px-3 sm:px-2">
                                                <div class="flex items-start gap-3">
                                                    <div class="shrink-0">${icon}</div>
                                                    <div class="min-w-0">
                                                        <p class="text-sm font-semibold text-slate-900">${escapeHtml(deviceName)}</p>
                                                        <p class="mt-1 text-xs text-slate-500">${escapeHtml(browser)} · ${escapeHtml(os)}</p>
                                                    </div>
                                                </div>
                                            </td>
                                  
                                            <td class="p-2 sm:px-2">
                                                <div class="sm:hidden text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400">Registered</div>
                                                <div class="text-xs text-slate-700 break-words">${escapeHtml(created)}</div>
                                            </td>
                                            <td class="p-2 sm:px-2">
                                                <div class="sm:hidden text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400">Last Used</div>
                                                <div class="text-xs text-slate-700 break-words">${escapeHtml(lastUsed)}</div>
                                            </td>
                                            <td class="p-2 sm:px-2">
                                                <div class="sm:hidden text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400">Status</div>
                                                <div>${statusChip}</div>
                                            </td>
                                            <td class="p-2 sm:px-2">
                                                <div class="sm:hidden text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400">Action</div>
                                                <button type="button" data-id="${d.public_id}" class="revokeBtn inline-flex h-9 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition duration-150 hover:border-rose-300 hover:bg-rose-50 hover:text-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-200">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/><path d="M11 14v4"/><path d="M13 14v4"/></svg>
                                                    <span>Remove</span>
                                                </button>
                                            </td>
                                        `;

                                        tbody.appendChild(tr);
                                    });

                                    tbody.querySelectorAll('.revokeBtn').forEach(btn => {
                                        btn.addEventListener('click', async () => {
                                            const id = btn.getAttribute('data-id');
                                            if (!confirm('Remove this trusted device? This will force sign-in from that browser.')) return;
                                            btn.disabled = true;
                                            btn.classList.add('opacity-60', 'cursor-not-allowed');

                                            const fd = new FormData();
                                            fd.append('public_id', id);

                                            const r = await fetch('index.php?controller=TrustedDevices&action=revokeAjax', {
                                                method: 'POST',
                                                credentials: 'same-origin',
                                                headers: {
                                                    'X-CSRF-Token': csrf,
                                                    'X-Requested-With': 'XMLHttpRequest',
                                                    'Accept': 'application/json',
                                                },
                                                body: fd,
                                            });
                                            const jr = await r.json();
                                            if (jr.success) {
                                                loadDevices();
                                            } else {
                                                console.error(jr);
                                                alert(jr.message || 'Could not remove device');
                                                btn.disabled = false;
                                                btn.classList.remove('opacity-60', 'cursor-not-allowed');
                                            }
                                        });
                                    });

                                } catch (err) {
                                    console.error('Trusted devices load failed', err);
                                    tbody.innerHTML = '<tr><td colspan="7" class="py-6 text-center text-rose-600">Unable to load trusted devices.</td></tr>';
                                }
                            }

                            removeOthersBtn?.addEventListener('click', async () => {
                                if (!confirm('Remove all other trusted devices? Your current browser will remain trusted.')) return;
                                removeOthersBtn.disabled = true;
                                removeOthersBtn.classList.add('opacity-60', 'cursor-not-allowed');
                                const fd = new FormData();
                                const res = await fetch('index.php?controller=TrustedDevices&action=revokeOthersAjax', {
                                    method: 'POST',
                                    credentials: 'same-origin',
                                    headers: {
                                        'X-CSRF-Token': csrf,
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'Accept': 'application/json',
                                    },
                                    body: fd,
                                });
                                const j = await res.json();
                                if (j.success) {
                                    loadDevices();
                                } else {
                                    console.error(j);
                                    alert(j.message || 'Failed to remove other devices');
                                }
                                removeOthersBtn.disabled = false;
                                removeOthersBtn.classList.remove('opacity-60', 'cursor-not-allowed');
                            });

                            loadDevices();
                        });
                        </script>

            

            <section class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <div class="mb-6">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Security</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-900">Change password</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-500">Update your password securely and keep your account protected with stronger authentication.</p>
                </div>

                <div class="grid gap-4 lg:grid-cols-3">
                    <div class="space-y-3">
                        <label for="current_password" class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Current password</label>
                        <div class="relative">
                            <input id="current_password" name="current_password" type="password" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 pr-14 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                            <button type="button" class="password-toggle absolute right-3 top-1/2 -translate-y-1/2 inline-flex h-9 items-center justify-center rounded-full border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-100">Show</button>
                        </div>
                        <p class="text-xs leading-5 text-slate-500">Confirm your current password before making changes.</p>
                        <?php if (!empty($errors['current_password'])): ?><p class="mt-1 text-sm text-rose-600"><?= htmlspecialchars($errors['current_password']) ?></p><?php endif; ?>
                    </div>
                    <div class="space-y-3">
                        <label for="new_password" class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">New password</label>
                        <div class="relative">
                            <input id="new_password" name="new_password" type="password" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 pr-14 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                            <button type="button" class="password-toggle absolute right-3 top-1/2 -translate-y-1/2 inline-flex h-9 items-center justify-center rounded-full border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-100">Show</button>
                        </div>
                        <p class="text-xs leading-5 text-slate-500">Use at least 8 characters with letters, numbers, and symbols.</p>
                        <?php if (!empty($errors['new_password'])): ?><p class="mt-1 text-sm text-rose-600"><?= htmlspecialchars($errors['new_password']) ?></p><?php endif; ?>
                    </div>
                    <div class="space-y-3">
                        <label for="confirm_password" class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Confirm password</label>
                        <div class="relative">
                            <input id="confirm_password" name="confirm_password" type="password" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 pr-14 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                            <button type="button" class="password-toggle absolute right-3 top-1/2 -translate-y-1/2 inline-flex h-9 items-center justify-center rounded-full border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-100">Show</button>
                        </div>
                        <p class="text-xs leading-5 text-slate-500">Re-enter your new password to verify it matches.</p>
                        <?php if (!empty($errors['confirm_password'])): ?><p class="mt-1 text-sm text-rose-600"><?= htmlspecialchars($errors['confirm_password']) ?></p><?php endif; ?>
                    </div>
                </div>

      

                <div id="passwordStrength" class="mt-4 text-sm font-medium text-slate-500">Password strength: <span class="font-semibold text-slate-700">None</span></div>
            </section>

            <section class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <div class="mb-6">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">PIN Code</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-900">Change PIN</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-500">Change your PIN separately from your password for an additional layer of user access protection.</p>
                </div>

                <div class="grid gap-4 lg:grid-cols-3">
                    <div class="space-y-3">
                        <label for="current_pin" class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Current PIN</label>
                        <div class="relative">
                            <input id="current_pin" name="current_pin" type="password" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 pr-14 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                            <button type="button" class="password-toggle absolute right-3 top-1/2 -translate-y-1/2 inline-flex h-9 items-center justify-center rounded-full border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-100">Show</button>
                        </div>
                        <p class="text-xs leading-5 text-slate-500">Enter the PIN currently assigned to your portal account.</p>
                        <?php if (!empty($errors['current_pin'])): ?><p class="mt-1 text-sm text-rose-600"><?= htmlspecialchars($errors['current_pin']) ?></p><?php endif; ?>
                    </div>
                    <div class="space-y-3">
                        <label for="new_pin" class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">New PIN</label>
                        <div class="relative">
                            <input id="new_pin" name="new_pin" type="password" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 pr-14 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                            <button type="button" class="password-toggle absolute right-3 top-1/2 -translate-y-1/2 inline-flex h-9 items-center justify-center rounded-full border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-100">Show</button>
                        </div>
                        <p class="text-xs leading-5 text-slate-500">Pick a PIN that is unique and not used with your password.</p>
                        <?php if (!empty($errors['new_pin'])): ?><p class="mt-1 text-sm text-rose-600"><?= htmlspecialchars($errors['new_pin']) ?></p><?php endif; ?>
                    </div>
                    <div class="space-y-3">
                        <label for="confirm_pin" class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Confirm PIN</label>
                        <div class="relative">
                            <input id="confirm_pin" name="confirm_pin" type="password" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 pr-14 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                            <button type="button" class="password-toggle absolute right-3 top-1/2 -translate-y-1/2 inline-flex h-9 items-center justify-center rounded-full border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-100">Show</button>
                        </div>
                        <p class="text-xs leading-5 text-slate-500">Re-enter the new PIN to confirm the change.</p>
                        <?php if (!empty($errors['confirm_pin'])): ?><p class="mt-1 text-sm text-rose-600"><?= htmlspecialchars($errors['confirm_pin']) ?></p><?php endif; ?>
                    </div>
                </div>

          
            </section>


             <section class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Profile Picture</p>
                        <h2 class="mt-1 text-lg font-semibold text-slate-900">Avatar</h2>
                    </div>
                </div>

                <div class="space-y-4">
                    <div id="avatarDropZone" class="group relative overflow-hidden rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center transition duration-200 hover:border-slate-400 hover:bg-slate-100 focus-within:border-emerald-300 focus-within:bg-white">
                        <input id="avatarInput" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" class="absolute inset-0 h-full w-full cursor-pointer opacity-0" />
                        <div class="space-y-4">
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-emerald-600 shadow-sm transition duration-200 group-hover:scale-105">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 16V8m0 0l-3 3m3-3l3 3m6 0V8m0 0l-3 3m3-3l3 3M7 12h10"/></svg>
                            </div>
                            <div class="space-y-2">
                                <p class="text-sm font-semibold text-slate-900">Drag & drop or click to upload an avatar</p>
                                <p class="text-xs text-slate-500">Supported file types: JPG, PNG, WEBP. Max 3MB.</p>
                                <p id="avatarFileName" class="text-xs font-medium text-slate-500">Click anywhere to browse files.</p>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($errors['avatar'])): ?><p class="text-sm text-rose-600"><?= htmlspecialchars($errors['avatar']) ?></p><?php endif; ?>
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="button" id="removeAvatarButton" class="inline-flex h-11 items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">Remove avatar</button>
                        <p class="text-xs text-slate-500">Preview updates before saving.</p>
                    </div>
                </div>
        </section>

            <div class="sticky bottom-0 z-20 rounded-3xl border border-slate-200/80 bg-white/95 px-5 py-4 shadow-sm backdrop-blur-md lg:sticky lg:bottom-4 lg:px-6 lg:py-5">
                <div class="mx-auto flex w-full max-w-6xl flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="text-sm text-slate-500">Make sure you save changes before leaving.</div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <button type="button" class="hidden rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-200 lg:inline-flex">Discard</button>
                        <button id="saveProfileButton" type="submit" class="inline-flex h-12 items-center justify-center rounded-2xl bg-emerald-700 px-6 text-sm font-semibold text-white shadow-sm shadow-emerald-100 transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-200 disabled:cursor-not-allowed disabled:opacity-60">
                            <span id="saveButtonText">Save Changes</span>
                        </button>
                    </div>
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
    const avatarFileName = document.getElementById('avatarFileName');
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

    const updateAvatarFileName = (file) => {
        if (!avatarFileName) return;
        avatarFileName.textContent = file ? `Selected file: ${file.name}` : 'Click anywhere to browse files.';
    };

    avatarInput.addEventListener('change', () => {
        const file = avatarInput.files[0];
        updateAvatarFileName(file);
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
