<?php require __DIR__ . '/../partials/icons.php'; ?>
<?php
$user = $user ?? [];
$fullName = trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? '')) ?: 'User';
$avatarFile = $user['profile_picture'] ?? ($_SESSION['profile_picture'] ?? 'default.png');
// $avatarSrc = BASE_URL . 'uploads/profile/' . htmlspecialchars($avatarFile);
$roleLabels = [
  0 => 'Super Admin',
  1 => 'Admin',
  4 => 'PROJECT MANAGER (PM)',
  5 => 'DEPUTY PROJECT MANAGER (DPM)',
  2 => 'Encoder',
  6 => 'User / GRP Head',
  3 => 'Viewer',
];
$roleLabel = $roleLabels[(int)($user['userLevel'] ?? 3)] ?? 'Viewer';
?>
<!-- make sure to check spelling -->
<div class="min-h-screen bg-gray-50">
  <style>
    .profile-form-label {
      display: block;
      margin-bottom: 8px;
      font-size: 11px;
      font-weight: 600;
      line-height: 1.2;
      text-transform: uppercase;
      letter-spacing: 0.2em;
      color: #64748b;
    }

    .profile-form-group {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .profile-form-control {
      width: 100%;
      min-height: 44px;
      box-sizing: border-box;
      border: 1px solid #e2e8f0;
      border-radius: 16px;
      background-color: #f8fafc;
      padding: 0 16px;
      font-size: 14px;
      line-height: 1.4;
      color: #0f172a;
      outline: none;
      transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
    }

    .profile-form-control:focus {
      border-color: #38bdf8;
      box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.12);
      background-color: #ffffff;
    }

    .profile-form-control[readonly] {
      cursor: default;
      color: #334155;
    }
  </style>
  <?php require_once __DIR__ . '/../../Services/CsrfService.php'; ?>
  <meta name="csrf-token" content="<?= \App\Services\CsrfService::token() ?>">
  <div class="mx-auto max-w-6xl px-4 py-6 sm:px-5 sm:py-6">
    <div class="mb-6 overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-[0_18px_60px_rgba(15,23,42,0.08)]">
      <div class="flex flex-col gap-3 border-b border-slate-200 bg-white px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
        <div class="min-w-0">
          <div class="flex items-center gap-2">
            <span class="h-2.5 w-2.5 rounded-full bg-sky-600"></span>
            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Account Center</p>
          </div>
          <h2 class="mt-2 text-xl font-semibold tracking-tight text-slate-900">Profile & Security</h2>
          <p class="mt-1 max-w-2xl text-sm leading-5 text-slate-500">
            Keep your account details current and secure.
          </p>
        </div>
        <a href="index.php?controller=Auth&action=dashboard"
           class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
          Back to Dashboard
        </a>
      </div>

      <div class="p-5 lg:p-6">
        <?php if (!empty($error)): ?>
          <div class="mb-4 rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <form action="index.php?controller=Auth&action=updateProfile" method="post" enctype="multipart/form-data" class="space-y-5">
          <div class="grid grid-cols-1 lg:grid-cols-[320px_minmax(0,1fr)] gap-5">
            <aside class="space-y-3">
              <div class="overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-sm">
                <div class="bg-gradient-to-br from-sky-600 via-sky-500 to-cyan-500 px-4 py-4 text-white">
                  <div class="flex items-center gap-3">
                    <img id="avatarPreview"
                      src="<?=BASE_URL ?>uploads/profile/<?= htmlspecialchars($avatarFile) ?>"
                         class="h-20 w-20 rounded-full border border-white/30 object-cover shadow-sm"
                         alt="Profile avatar">
                    <div class="min-w-0">
                      <p class="truncate text-lg font-semibold leading-6 text-white"><?= htmlspecialchars($fullName) ?></p>
                      <p class="mt-1 truncate text-sm text-sky-50/90"><?= htmlspecialchars($user['position'] ?? 'User') ?></p>
                      <span class="mt-2 inline-flex rounded-full border border-white/20 bg-white/10 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-sky-50">
                        <?= htmlspecialchars($roleLabel) ?>
                      </span>
                    </div>
                  </div>
                </div>

                <div class="space-y-3 p-4">
                  <div class="flex flex-wrap items-center gap-3 text-sm text-slate-600">
                    <div class="min-w-0">
                      <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">Username</p>
                      <p class="mt-1 truncate font-semibold text-slate-900"><?= htmlspecialchars($user['username'] ?? '—') ?></p>
                    </div>
                    <div class="min-w-0">
                      <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">User ID</p>
                      <p class="mt-1 font-semibold text-slate-900">#<?= htmlspecialchars($user['id'] ?? '—') ?></p>
                    </div>
                  </div>

                  <div class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-3 text-center transition hover:border-sky-300 hover:bg-sky-50/60" id="avatarDropZone">
                    <p class="text-sm font-semibold text-slate-900">Change Profile Image</p>
                    <p class="mt-1 text-xs leading-5 text-slate-500">PNG, JPG, or WEBP • Max 2MB</p>
                    <p id="avatarName" class="mt-2 text-sm font-medium text-sky-700"></p>
                    <input type="file" name="profile_picture" id="avatarInput" accept="image/jpeg,image/png,image/webp" class="hidden">
                  </div>
                </div>
              </div>

              <div class="rounded-[24px] border border-slate-200 bg-slate-50/80 p-4 shadow-sm">
                <div class="flex items-center justify-between gap-2">
                  <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Security Tips</p>
                  <span class="rounded-full border border-slate-200 bg-white px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">Guidance</span>
                </div>
                <ul class="mt-3 space-y-2.5 text-sm text-slate-600">
                  <li class="flex gap-2.5 rounded-xl bg-white/70 px-3 py-2.5">
                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-sky-500"></span>
                    <span>Use your current password before changing it.</span>
                  </li>
                  <li class="flex gap-2.5 rounded-xl bg-white/70 px-3 py-2.5">
                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                    Update your avatar anytime; it is optional and separate from account security.
                  </li>
                  <li class="flex gap-2.5 rounded-xl bg-white/70 px-3 py-2.5">
                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-amber-500"></span>
                    Role and username stay read-only here for account integrity.
                  </li>
                </ul>
              </div>
            </aside>

            <section class="space-y-5">
              <div class="rounded-[24px] border border-slate-200/80 bg-white p-4 shadow-sm">
                <div class="border-b border-slate-200/80 pb-3">
                  <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Identity</p>
                  <h3 class="mt-1 text-lg font-semibold text-slate-900">Profile snapshot</h3>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                  <div class="profile-form-group">
                    <label class="profile-form-label">First name</label>
                    <input type="text" value="<?= htmlspecialchars($user['firstName'] ?? '') ?>" readonly
                           class="profile-form-control">
                  </div>
                  <div class="profile-form-group">
                    <label class="profile-form-label">Last name</label>
                    <input type="text" value="<?= htmlspecialchars($user['lastName'] ?? '') ?>" readonly
                           class="profile-form-control">
                  </div>
                  <div class="profile-form-group">
                    <label class="profile-form-label">Position</label>
                    <input type="text" value="<?= htmlspecialchars($user['position'] ?? '') ?>" readonly
                           class="profile-form-control">
                  </div>
                  <div class="profile-form-group">
                    <label class="profile-form-label">Access level</label>
                    <input type="text" value="<?= htmlspecialchars($roleLabel) ?>" readonly
                           class="profile-form-control">
                  </div>
                </div>
              </div>

              <div class="rounded-[24px] border border-slate-200/80 bg-white p-4 shadow-sm">
                <div class="border-b border-slate-200/80 pb-3">
                  <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Security</p>
                  <h3 class="mt-1 text-lg font-semibold text-slate-900">Change password</h3>
                </div>

                <div class="mt-4 space-y-4">
                  <div class="profile-form-group">
                    <label class="profile-form-label">Current password</label>
                    <input type="password" name="current_password" autocomplete="current-password"
                           class="profile-form-control">
                  </div>

                  <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="profile-form-group">
                      <label class="profile-form-label">New password</label>
                      <input type="password" name="new_password" autocomplete="new-password"
                             class="profile-form-control">
                    </div>
                    <div class="profile-form-group">
                      <label class="profile-form-label">Confirm password</label>
                      <input type="password" name="confirm_password" autocomplete="new-password"
                             class="profile-form-control">
                    </div>
                  </div>

                  <div class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-3">
                    <div class="flex items-center justify-between gap-3">
                      <span class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Password strength</span>
                      <span class="text-xs font-medium text-slate-500">Recommended</span>
                    </div>
                    <div class="mt-2 flex gap-1.5">
                      <span class="h-1.5 flex-1 rounded-full bg-slate-200"></span>
                      <span class="h-1.5 flex-1 rounded-full bg-slate-200"></span>
                      <span class="h-1.5 flex-1 rounded-full bg-slate-200"></span>
                    </div>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Use at least 8 characters with a mix of letters, numbers, and symbols.</p>
                  </div>

                  <div class="rounded-2xl border border-slate-200/70 bg-white px-3 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Security note</p>
                    <p class="mt-1 text-sm leading-6 text-slate-500">Leave the password fields blank if you only want to update your avatar.</p>
                  </div>
                </div>
              </div>

              <div id="trustedDevicesBox" class="rounded-[24px] border border-slate-200/80 bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                  <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Trusted Devices</p>
                    <h3 class="mt-1 text-lg font-semibold text-slate-900">Remembered browsers</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Manage browsers you've opted to trust for 30 days. Removing a device forces that browser to re-verify with 2FA.</p>
                  </div>
                  <button type="button" id="removeOthersBtn" class="inline-flex shrink-0 items-center justify-center rounded-2xl border border-rose-100 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">Remove All Other Devices</button>
                </div>

                <div class="mt-3">
                  <table id="trustedDevicesTable" class="w-full table-auto border-separate border-spacing-0 text-sm">
                    <thead>
                      <tr class="border-b border-slate-200/80 text-left text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">
                        <th class="w-[34%] px-3 py-3">Device</th>
                        <th class="w-[16%] min-w-[110px] px-3 py-3">Registered</th>
                        <th class="w-[16%] min-w-[110px] px-3 py-3">Last used</th>
                        <th class="w-[16%] min-w-[110px] px-3 py-3">Expires</th>
                        <th class="w-[18%] min-w-[120px] px-3 py-3 text-right">Action</th>
                      </tr>
                    </thead>
                    <tbody id="trustedDevicesTbody">
                      <tr><td colspan="5" class="px-3 py-6 text-center text-slate-400">Loading...</td></tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <div class="mt-8 flex justify-end border-t border-slate-200/80 pt-5">
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                  <a href="index.php?controller=Auth&action=dashboard"
                     class="inline-flex min-h-11 items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Back to Dashboard
                  </a>
                  <button type="submit"
                          class="inline-flex min-h-11 items-center justify-center rounded-2xl bg-sky-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
                    Save Profile Changes
                  </button>
                </div>
              </div>
            </section>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
const avatarInput = document.getElementById('avatarInput');
const avatarPreview = document.getElementById('avatarPreview');
const avatarName = document.getElementById('avatarName');
const avatarDropZone = document.getElementById('avatarDropZone');

if (avatarInput && avatarPreview) {
  const setAvatar = (file) => {
    if (!file) return;
    avatarPreview.src = URL.createObjectURL(file);
    if (avatarName) {
      avatarName.textContent = `Selected: ${file.name}`;
    }
  };

  avatarDropZone?.addEventListener('click', () => avatarInput.click());

  avatarInput.addEventListener('change', () => {
    setAvatar(avatarInput.files?.[0]);
  });

  avatarDropZone?.addEventListener('dragover', (event) => {
    event.preventDefault();
    avatarDropZone.classList.add('border-sky-400', 'bg-sky-50');
  });

  avatarDropZone?.addEventListener('dragleave', () => {
    avatarDropZone.classList.remove('border-sky-400', 'bg-sky-50');
  });

  avatarDropZone?.addEventListener('drop', (event) => {
    event.preventDefault();
    avatarDropZone.classList.remove('border-sky-400', 'bg-sky-50');
    if (event.dataTransfer.files.length) {
      avatarInput.files = event.dataTransfer.files;
      setAvatar(event.dataTransfer.files[0]);
    }
  });
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const tbody = document.getElementById('trustedDevicesTbody');
  const removeOthersBtn = document.getElementById('removeOthersBtn');
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  function formatDate(value) {
    if (!value) {
      return '—';
    }
    const date = new Date(value.replace(' ', 'T'));
    return Number.isNaN(date.getTime()) ? '—' : date.toLocaleString();
  }

  function escapeHtml(s) {
    if (s === null || s === undefined) return '';
    return String(s).replace(/[&<>"']/g, function(m) { return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[m]; });
  }

  if (!tbody) {
    console.error('Trusted devices table body not found');
    return;
  }

  async function loadDevices() {
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
      console.debug('Trusted devices response:', data);
      if (!data.success) {
        throw new Error(data.message || 'Failed to load');
      }

      const devices = Array.isArray(data.data && data.data.devices ? data.data.devices : []) ? data.data.devices : [];
      console.debug('Trusted devices count:', devices.length);

      if (devices.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="py-6 text-center text-slate-400">No trusted devices found.</td></tr>';
        return;
      }

      tbody.innerHTML = '';
      devices.forEach((d) => {
        const tr = document.createElement('tr');
        tr.className = 'border-t border-slate-200/70 transition-colors hover:bg-slate-50/70';

        const deviceLabel = d.device_name ? d.device_name : '—';
        const browser = d.browser_name || '—';
        const os = d.operating_system || '—';
        const created = formatDate(d.created_at);
        const lastUsed = formatDate(d.last_used_at);
        const expires = formatDate(d.expires_at);

        function renderDateTimeCell(value) {
          if (!value || value === '—') {
            return '<div class="text-sm leading-5 text-slate-600">—</div>';
          }

          const parts = String(value).split(' ');
          const datePart = parts[0] || '—';
          const timePart = parts.slice(1).join(' ') || '';

          return `
            <div class="text-sm leading-5 text-slate-600">
              <div class="break-words">${escapeHtml(datePart)}</div>
              ${timePart ? `<div class="mt-0.5 text-xs text-slate-500 break-words">${escapeHtml(timePart)}</div>` : ''}
            </div>
          `;
        }

        const isCurrent = d.is_current ? '<div class="mt-1 inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">Current device</div>' : '';

        tr.innerHTML = `
          <td class="px-3 py-3.5 align-top">
            <div class="min-w-0">
              <div class="break-words font-medium text-slate-900">${escapeHtml(deviceLabel)}</div>
              <div class="mt-1 break-words text-xs text-slate-500">${escapeHtml(browser)} • ${escapeHtml(os)}</div>
              ${isCurrent}
            </div>
          </td>
          <td class="px-3 py-3.5 align-top">
            ${renderDateTimeCell(created)}
          </td>
          <td class="px-3 py-3.5 align-top">
            ${renderDateTimeCell(lastUsed)}
          </td>
          <td class="px-3 py-3.5 align-top">
            ${renderDateTimeCell(expires)}
          </td>
          <td class="min-w-[120px] px-3 py-3.5 align-top text-right">
            <button type="button" data-id="${d.public_id}" class="revokeBtn inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">Remove</button>
          </td>
        `;

        tbody.appendChild(tr);
      });

      tbody.querySelectorAll('.revokeBtn').forEach(btn => {
        btn.addEventListener('click', async () => {
          const id = btn.getAttribute('data-id');
          if (!confirm('Remove this trusted device? This will force sign-in from that browser.')) return;

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
  });

  loadDevices();
});
</script>
