<?php
$currentController = $_GET['controller'] ?? '';
$currentAction = $_GET['action'] ?? '';
$currentUserLevel = (int)($_SESSION['user_level'] ?? 3);
$isSuperAdmin = $currentUserLevel === 0;
$isAdmin = $currentUserLevel === 1;
$fullName = trim(($_SESSION['firstName'] ?? '') . ' ' . ($_SESSION['lastName'] ?? '')) ?: 'Guest';
$position = $_SESSION['position'] ?? 'User';
$avatarFile = $_SESSION['profile_picture'] ?? 'default.png';
$avatarSrc = '../app/assets/profiles/' . $avatarFile;

function sidebarActiveClass(bool $active): string
{
    return $active
        ? 'bg-sky-600 text-white shadow-sm'
        : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900';
}
?>

<nav id="sidebar"
     class="fixed inset-y-0 left-0 z-30 flex h-screen w-80 -translate-x-full flex-col border-r border-slate-200 bg-white/90 shadow-[0_24px_80px_rgba(15,23,42,0.10)] backdrop-blur-2xl transition-transform duration-300 ease-out md:translate-x-0">

  <div class="border-b border-slate-200/80 px-6 py-6">
    <div class="flex items-center gap-3 rounded-[24px] border border-slate-200 bg-gradient-to-br from-slate-50 to-white px-4 py-4 shadow-sm">
      <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <img src="../app/assets/logo/brand2.png" class="h-9 w-auto object-contain" alt="Brand">
      </div>
      <div class="min-w-0">
        <p class="truncate text-sm font-semibold tracking-wide text-slate-900">DPEARP Portal</p>
        <p class="truncate text-xs text-slate-500">Intranet</p>
      </div>
    </div>
  </div>

  <div class="flex-1 overflow-y-auto px-4 py-5">
    <div class="mb-6 px-2">
      <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-400">Navigation</p>
      <p class="mt-1 text-xs leading-5 text-slate-500">Keep the main tools close and the interface quiet.</p>
    </div>

    <ul class="space-y-2">
      <li>
        <a href="index.php?controller=Auth&action=dashboard"
           class="group flex items-center gap-4 rounded-2xl px-4 py-3.5 text-sm font-medium transition <?= sidebarActiveClass($currentController == 'Auth' && $currentAction == 'dashboard') ?>">
          <span class="flex h-11 w-11 items-center justify-center rounded-2xl <?= ($currentController == 'Auth' && $currentAction == 'dashboard') ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-600 group-hover:bg-white' ?>">
            <?= $dashboardIcon ?>
          </span>
          <span class="min-w-0">
            <span class="block">Dashboard</span>
            <span class="block text-xs font-normal opacity-70">Overview and activity</span>
          </span>
        </a>
      </li>

      <li>
        <a href="index.php?controller=Files&action=files"
           class="group flex items-center gap-4 rounded-2xl px-4 py-3.5 text-sm font-medium transition <?= sidebarActiveClass($currentController == 'Files' && $currentAction == 'files') ?>">
          <span class="flex h-11 w-11 items-center justify-center rounded-2xl <?= ($currentController == 'Files' && $currentAction == 'files') ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-600 group-hover:bg-white' ?>">
            <?= $fileIcon ?>
          </span>
          <span class="min-w-0">
            <span class="block">Files</span>
            <span class="block text-xs font-normal opacity-70">Upload and repository</span>
          </span>
        </a>
      </li>

      <li>
        <a href="index.php?controller=correspondence&action=correspondence"
           class="group flex items-center gap-4 rounded-2xl px-4 py-3.5 text-sm font-medium transition <?= sidebarActiveClass($currentController == 'correspondence' && $currentAction == 'correspondence') ?>">
          <span class="flex h-11 w-11 items-center justify-center rounded-2xl <?= ($currentController == 'correspondence' && $currentAction == 'correspondence') ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-600 group-hover:bg-white' ?>">
            <?= $correspondenceIcon ?>
          </span>
          <span class="min-w-0">
            <span class="block">Correspondence</span>
            <span class="block text-xs font-normal opacity-70">Circulations and history</span>
          </span>
        </a>
      </li>
    </ul>

    <?php if ($isSuperAdmin || $isAdmin): ?>
      <div class="mt-8 px-2">
        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-400">Administration</p>
      </div>

      <ul class="mt-3 space-y-2">
        <?php if ($isSuperAdmin): ?>
          <li>
            <a href="index.php?controller=Auth&action=users"
               class="group flex items-center gap-4 rounded-2xl px-4 py-3.5 text-sm font-medium transition <?= sidebarActiveClass($currentController == 'Auth' && $currentAction == 'users') ?>">
              <span class="flex h-11 w-11 items-center justify-center rounded-2xl <?= ($currentController == 'Auth' && $currentAction == 'users') ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-600 group-hover:bg-white' ?>">
                <?= $usersIcon ?>
              </span>
              <span class="min-w-0">
                <span class="block">Core Users</span>
                <span class="block text-xs font-normal opacity-70">Full system accounts</span>
              </span>
            </a>
          </li>

          <li>
            <a href="index.php?controller=Syslogs&action=syslogs"
               class="group flex items-center gap-4 rounded-2xl px-4 py-3.5 text-sm font-medium transition <?= sidebarActiveClass($currentController == 'Syslogs' && $currentAction == 'syslogs') ?>">
              <span class="flex h-11 w-11 items-center justify-center rounded-2xl <?= ($currentController == 'Syslogs' && $currentAction == 'syslogs') ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-600 group-hover:bg-white' ?>">
                <?= $clipBoardIcon ?>
              </span>
              <span class="min-w-0">
                <span class="block">System Logs</span>
                <span class="block text-xs font-normal opacity-70">Audit trail and events</span>
              </span>
            </a>
          </li>
        <?php endif; ?>

        <li>
          <a href="index.php?controller=StandardUsers&action=index"
             class="group flex items-center gap-4 rounded-2xl px-4 py-3.5 text-sm font-medium transition <?= sidebarActiveClass($currentController == 'StandardUsers' && $currentAction == 'index') ?>">
            <span class="flex h-11 w-11 items-center justify-center rounded-2xl <?= ($currentController == 'StandardUsers' && $currentAction == 'index') ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-600 group-hover:bg-white' ?>">
              <?= $usersIcon ?>
            </span>
            <span class="min-w-0">
              <span class="block">Standard Users</span>
              <span class="block text-xs font-normal opacity-70">Portal accounts and access</span>
            </span>
          </a>
        </li>
      </ul>
    <?php endif; ?>
  </div>
</nav>
