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
  <div class="mx-auto max-w-6xl px-4 py-8">
    <div class="mb-8 overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-[0_18px_60px_rgba(15,23,42,0.08)]">
      <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-slate-50 px-6 py-5">
        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-blue-600">Account Center</p>
        <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">Profile & Security</h2>
        <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-500">
          Keep your avatar current and manage your password from one calm, easy-to-scan screen.
        </p>
      </div>

      <div class="p-6 lg:p-7">
        <?php if (!empty($error)): ?>
          <div class="mb-5 rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <form action="index.php?controller=Auth&action=updateProfile" method="post" enctype="multipart/form-data" class="space-y-6">
          <div class="grid grid-cols-1 lg:grid-cols-[360px_minmax(0,1fr)] gap-6">
            <aside class="space-y-4">
              <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
                <div class="bg-gradient-to-br from-sky-600 via-sky-500 to-cyan-500 px-6 py-6 text-white">
                  <div class="flex items-center gap-4">
                    <img id="avatarPreview"
                      src="<?=BASE_URL ?>uploads/profile/<?= htmlspecialchars($avatarFile) ?>"
                       
                         class="h-20 w-20 rounded-full border border-white/30 object-cover shadow-lg"
                         alt="Profile avatar">
                    <div class="min-w-0">
                      <p class="truncate text-xl font-semibold"><?= htmlspecialchars($fullName) ?></p>
                      <p class="truncate text-sm text-sky-50/90"><?= htmlspecialchars($user['position'] ?? 'User') ?></p>
                      <span class="mt-2 inline-flex rounded-full border border-white/25 bg-white/15 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-white">
                        <?= htmlspecialchars($roleLabel) ?>
                      </span>
                    </div>
                  </div>
                </div>

                <div class="space-y-4 p-5">
                  <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                      <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Username</p>
                      <p class="mt-1 truncate font-semibold text-slate-900"><?= htmlspecialchars($user['username'] ?? '—') ?></p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                      <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">User ID</p>
                      <p class="mt-1 font-semibold text-slate-900">#<?= htmlspecialchars($user['id'] ?? '—') ?></p>
                    </div>
                  </div>

                  <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-center transition hover:border-sky-300 hover:bg-sky-50/60" id="avatarDropZone">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white shadow-sm">
                      <?= $profileIcon ?? '◌' ?>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-slate-900">Replace profile image</p>
                    <p class="mt-1 text-xs leading-5 text-slate-500">PNG, JPG, or WEBP. Max 2MB.</p>
                    <p id="avatarName" class="mt-3 text-sm font-medium text-sky-700"></p>
                    <input type="file" name="profile_picture" id="avatarInput" accept="image/jpeg,image/png,image/webp" class="hidden">
                  </div>
                </div>
              </div>

              <div class="rounded-[28px] border border-slate-200 bg-slate-50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Account Notes</p>
                <ul class="mt-4 space-y-3 text-sm text-slate-600">
                  <li class="flex gap-3">
                    <span class="mt-1 h-2 w-2 rounded-full bg-blue-500"></span>
                    Changing your password requires your current password.
                  </li>
                  <li class="flex gap-3">
                    <span class="mt-1 h-2 w-2 rounded-full bg-emerald-500"></span>
                    Updating the avatar is optional and can be done alone.
                  </li>
                  <li class="flex gap-3">
                    <span class="mt-1 h-2 w-2 rounded-full bg-amber-500"></span>
                    The account role and username stay read-only here.
                  </li>
                </ul>
              </div>
            </aside>

            <section class="space-y-5">
              <div class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Identity</p>
                <h3 class="mt-1 text-lg font-semibold text-slate-900">Profile snapshot</h3>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">First name</label>
                    <input type="text" value="<?= htmlspecialchars($user['firstName'] ?? '') ?>" readonly
                           class="w-full h-11 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 outline-none">
                  </div>
                  <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Last name</label>
                    <input type="text" value="<?= htmlspecialchars($user['lastName'] ?? '') ?>" readonly
                           class="w-full h-11 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 outline-none">
                  </div>
                  <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Position</label>
                    <input type="text" value="<?= htmlspecialchars($user['position'] ?? '') ?>" readonly
                           class="w-full h-11 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 outline-none">
                  </div>
                  <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Access level</label>
                    <input type="text" value="<?= htmlspecialchars($roleLabel) ?>" readonly
                           class="w-full h-11 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 outline-none">
                  </div>
                </div>
              </div>

              <div class="rounded-[28px] border border-slate-200 bg-slate-50/70 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Security</p>
                <h3 class="mt-1 text-lg font-semibold text-slate-900">Change password</h3>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div class="md:col-span-3">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Current password</label>
                    <input type="password" name="current_password" autocomplete="current-password"
                           class="w-full h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                  </div>
                  <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">New password</label>
                    <input type="password" name="new_password" autocomplete="new-password"
                           class="w-full h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                  </div>
                  <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Confirm password</label>
                    <input type="password" name="confirm_password" autocomplete="new-password"
                           class="w-full h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                  </div>
                  <div class="rounded-2xl border border-sky-100 bg-sky-50 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-sky-700">Tip</p>
                    <p class="mt-1 text-sm leading-6 text-sky-900/80">
                      Leave the password fields blank if you only want to update your avatar.
                    </p>
                  </div>
                </div>
              </div>

              <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="index.php?controller=Auth&action=dashboard"
                   class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                  Back to Dashboard
                </a>
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-2xl bg-sky-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-sky-700">
                  Save Profile Changes
                </button>
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
