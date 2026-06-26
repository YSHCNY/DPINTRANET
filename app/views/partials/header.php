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

// Notifications
$unreadCount = 0;
$notifications = [];
if (!empty($_SESSION['id'])) {
  require_once '../app/models/Notification.php';
  $notifModel = new NotificationModel();
  $unreadCount = $notifModel->getUnreadCount((int)$_SESSION['id']);
  $notifications = $notifModel->getForUser((int)$_SESSION['id'], 8);
}

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

<nav class="sticky top-0 z-40 flex items-center justify-between bg-emerald-700 px-3 py-2 backdrop-blur-sm border-b border-emerald-600">
  <style>
    /* 8-grid responsive header */
    .header-title { max-width: calc(100vw - 100px); }
    @media (max-width: 768px) {
      .header-title { max-width: calc(100vw - 140px); }
      .header-user-info { display: none; }
    }
  </style>

  <div class="flex min-w-0 items-center gap-2">
    <button id="sidebarToggle" class="md:hidden inline-flex items-center justify-center h-8 w-8 rounded-lg hover:bg-emerald-600 text-white focus:outline-none transition" type="button">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>
    </button>
    <span class="header-title truncate text-sm font-semibold text-white"><?= htmlspecialchars($pageTitle) ?></span>
  </div>

  <div class="flex items-center gap-2">
    <!-- Notifications -->
    <div class="relative">
      <button id="notifButton" class="relative inline-flex items-center justify-center h-8 w-8 rounded-lg hover:bg-emerald-600 text-white focus:outline-none transition" type="button">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6z"/></svg>
        <?php if ($unreadCount > 0): ?>
          <span class="absolute -top-1 -right-1 inline-flex items-center justify-center rounded-full bg-red-500 text-white text-[9px] font-bold h-5 w-5"><?= min($unreadCount, 9) ?><?= $unreadCount > 9 ? '+' : '' ?></span>
        <?php endif; ?>
      </button>

      <div id="notifMenu" class="absolute right-0 mt-2 hidden w-80 max-w-[90vw] overflow-hidden rounded-lg bg-white shadow-xl">
        <div class="px-3 py-2 border-b border-slate-200 bg-slate-50">
          <div class="flex items-center justify-between">
            <div class="text-xs font-bold uppercase tracking-wide text-slate-700">Notifications</div>
            <div class="text-xs font-medium text-slate-500"><?= $unreadCount ?> new</div>
          </div>
        </div>
        <div class="max-h-80 overflow-y-auto">
          <?php if (empty($notifications)): ?>
            <div class="p-3 text-xs text-slate-500 text-center">No notifications</div>
          <?php else: ?>
            <?php foreach ($notifications as $n): ?>
              <a href="index.php?controller=Notifications&action=view&id=<?= (int)$n['id'] ?>" class="block px-3 py-2 hover:bg-slate-50 border-b border-slate-100 transition">
                <div class="text-xs text-slate-800 line-clamp-2"><?= htmlspecialchars($n['message']) ?></div>
                <div class="text-[11px] text-slate-500 mt-1"><?= date('M d g:i A', strtotime($n['created_at'])) ?></div>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Profile Menu -->
    <div class="relative">
      <button id="profileMenuButton" type="button"
        class="flex items-center gap-2 rounded-lg hover:bg-emerald-600 text-white px-2 py-1 text-left transition focus:outline-none">
        <img src="<?= htmlspecialchars($avatarSrc) ?>"
             class="h-8 w-8 rounded-full object-cover ring-2 ring-white/30"
             alt="User avatar">
        <div class="header-user-info hidden pr-1 sm:block min-w-0">
          <p class="text-sm font-medium leading-4 text-white truncate"><?= htmlspecialchars($fullName) ?></p>
          <p class="text-[10px] uppercase tracking-wide text-emerald-100"><?= htmlspecialchars($roleLabels[$currentLevel] ?? 'Viewer') ?></p>
        </div>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-200" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
      </button>

      <div id="profileMenu" class="absolute right-0 mt-2 hidden w-72 overflow-hidden rounded-lg bg-white shadow-xl">
        <!-- Profile Header -->
        <div class="px-3 py-2 bg-emerald-700 text-white">
          <div class="flex items-center gap-2">
            <img src="<?= htmlspecialchars($avatarSrc) ?>" class="h-10 w-10 rounded-lg object-cover ring-2 ring-white/30" alt="User avatar">
            <div class="min-w-0 flex-1">
              <p class="text-sm font-semibold text-white truncate"><?= htmlspecialchars($fullName) ?></p>
              <p class="text-xs text-emerald-100 truncate"><?= htmlspecialchars($position) ?></p>
            </div>
          </div>
          <div class="mt-2 inline-flex rounded-md bg-emerald-600 text-white px-2 py-1 text-[10px] font-bold uppercase tracking-wide">
            <?= htmlspecialchars($roleLabels[$currentLevel] ?? 'Viewer') ?>
          </div>
        </div>

        <!-- Menu Items -->
        <div class="border-t border-slate-200">
          <a href="index.php?controller=Auth&action=profile"
             class="flex items-center gap-2 px-3 py-2 hover:bg-slate-50 border-b border-slate-100 transition text-sm">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-emerald-100 text-emerald-700 flex-shrink-0 text-sm">
              <?= $profileIcon ?? '⚙' ?>
            </span>
            <span class="min-w-0">
              <span class="block text-sm font-medium text-slate-900">Profile & Security</span>
              <span class="block text-xs text-slate-500">Account settings</span>
            </span>
          </a>

          <a href="index.php?controller=Auth&action=logout&wc=signedOut"
             class="flex items-center gap-2 px-3 py-2 hover:bg-slate-50 transition text-sm">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-red-100 text-red-700 flex-shrink-0 text-sm">
              <?= $signOutIcon ?? '↩' ?>
            </span>
            <span class="min-w-0">
              <span class="block text-sm font-medium text-slate-900">Sign Out</span>
              <span class="block text-xs text-slate-500">End session</span>
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

  const notifButton = document.getElementById('notifButton');
  const notifMenu = document.getElementById('notifMenu');
  if (notifButton && notifMenu) {
    notifButton.addEventListener('click', (e) => {
      e.stopPropagation();
      notifMenu.classList.toggle('hidden');
      profileMenu.classList.add('hidden');
    });
    document.addEventListener('click', () => notifMenu.classList.add('hidden'));
  }
</script>
