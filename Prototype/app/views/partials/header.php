<?php
$currentController = $_GET['controller'] ?? '';
$currentAction = $_GET['action'] ?? '';
$currentLevel = (int)($_SESSION['user_level'] ?? 3);
$roleLabels = [
  0 => 'Super Admin',
  1 => 'Admin',
  2 => 'Editor',
  3 => 'Viewer',
];
$avatarFile = $_SESSION['profile_picture'] ?? 'default.png';
$avatarSrc = '../app/assets/profiles/' . $avatarFile;
$fullName = trim(($_SESSION['firstName'] ?? '') . ' ' . ($_SESSION['lastName'] ?? '')) ?: 'Guest';
$position = $_SESSION['position'] ?? 'User';

if ($currentController == 'Auth' && $currentAction == 'dashboard') {
  $pageTitle = 'Dashboard';
} elseif ($currentController == 'Files' && $currentAction == 'files') {
  $pageTitle = 'Files Management';
} elseif ($currentController == 'Auth' && $currentAction == 'users') {
  $pageTitle = 'Core Users';
} elseif ($currentController == 'Auth' && $currentAction == 'profile') {
  $pageTitle = 'Profile & Security';
} elseif ($currentController == 'Syslogs' && $currentAction == 'syslogs') {
  $pageTitle = 'System Logs';
} elseif ($currentController == 'correspondence' && $currentAction == 'correspondence') {
  $pageTitle = 'Correspondence Management';
} elseif ($currentController == 'StandardUsers' && $currentAction == 'index') {
  $pageTitle = 'Standard Users';
} else {
  $pageTitle = 'FMS Portal';
}
?>

<nav class="sticky top-0 z-40 flex items-center justify-between bg-sky-700 px-6 py-3 backdrop-blur-xl">
  <div class="flex min-w-0 items-center gap-4">
    <button id="sidebarToggle" class="md:hidden text-2xl text-stone-100 focus:outline-none" type="button">
      &#9776;
    </button>
    <span class="max-w-[52vw] truncate text-base font-semibold text-stone-100 sm:max-w-[56vw] lg:max-w-[32rem]"><?= htmlspecialchars($pageTitle) ?></span>
  </div>

  <div class="flex items-center gap-3">
    <!-- <div class="hidden text-right sm:block">
      <span class="block font-medium text-stone-50"><?= htmlspecialchars($fullName) ?></span>
      <span class="text-sm text-sky-100/80"><?= htmlspecialchars($position) ?></span>
    </div> -->

    <div class="relative ml-2">
      <button id="profileMenuButton" type="button"
              class="flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-2 py-1.5 text-left shadow-sm transition hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/40">
        <img src="<?= htmlspecialchars($avatarSrc) ?>"
             class="size-9 rounded-full object-cover ring-2 ring-white/35"
             alt="User avatar">
        <div class="hidden pr-1 sm:block">
          <p class="text-sm font-semibold leading-4 text-white"><?= htmlspecialchars($fullName) ?></p>
          <p class="text-[11px] uppercase tracking-[0.18em] text-sky-100/80"><?= htmlspecialchars($roleLabels[$currentLevel] ?? 'Viewer') ?></p>
        </div>
      </button>

      <div id="profileMenu"
           class="absolute right-0 mt-3 hidden w-72 overflow-hidden rounded-[22px] border border-slate-200 bg-white shadow-[0_24px_70px_rgba(15,23,42,0.18)]">
        <div class="border-b border-slate-100 bg-gradient-to-r from-sky-50 to-white px-4 py-4">
          <div class="flex items-center gap-3">
            <img src="<?= htmlspecialchars($avatarSrc) ?>" class="size-12 rounded-full object-cover ring-2 ring-sky-100" alt="User avatar">
            <div class="min-w-0">
              <p class="truncate text-sm font-semibold text-slate-900"><?= htmlspecialchars($fullName) ?></p>
              <p class="truncate text-xs text-slate-500"><?= htmlspecialchars($position) ?></p>
              <span class="mt-1 inline-flex rounded-full border border-sky-100 bg-sky-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.18em] text-sky-700">
                <?= htmlspecialchars($roleLabels[$currentLevel] ?? 'Viewer') ?>
              </span>
            </div>
          </div>
        </div>

        <div class="p-2">
          <a href="index.php?controller=Auth&action=profile"
             class="flex items-start gap-3 rounded-2xl px-3 py-3 text-sm text-slate-700 transition hover:bg-slate-50">
            <span class="mt-0.5 inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-sky-50 text-sky-700">
              <?= $profileIcon ?? '◌' ?>
            </span>
            <span>
              <span class="block font-semibold text-slate-900">Profile & Security</span>
              <span class="block text-xs leading-5 text-slate-500">Update avatar, password, and account details.</span>
            </span>
          </a>

          <a href="index.php?controller=Auth&action=logout&wc=signedOut"
             class="mt-1 flex items-start gap-3 rounded-2xl px-3 py-3 text-sm text-slate-700 transition hover:bg-rose-50">
            <span class="mt-0.5 inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-rose-50 text-rose-700">
              <?= $signOutIcon ?? '↩' ?>
            </span>
            <span>
              <span class="block font-semibold text-slate-900">Sign out</span>
              <span class="block text-xs leading-5 text-slate-500">End your session securely.</span>
            </span>
          </a>
        </div>
      </div>
    </div>
  </div>
</nav>

<script>
  const sidebar = document.getElementById('sidebar');
  const btn = document.getElementById('sidebarToggle');
  if (btn) {
    btn.addEventListener('click', () => {
      sidebar.classList.toggle('-translate-x-full');
    });
  }

  const profileButton = document.getElementById('profileMenuButton');
  const profileMenu = document.getElementById('profileMenu');

  if (profileButton && profileMenu) {
    const closeProfileMenu = () => profileMenu.classList.add('hidden');
    const toggleProfileMenu = () => profileMenu.classList.toggle('hidden');

    profileButton.addEventListener('click', (event) => {
      event.stopPropagation();
      toggleProfileMenu();
    });

    profileMenu.addEventListener('click', (event) => {
      event.stopPropagation();
    });

    document.addEventListener('click', closeProfileMenu);
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        closeProfileMenu();
      }
    });
  }
</script>
