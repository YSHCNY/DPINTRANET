<?php
$currentController = $_GET['controller'] ?? '';
$currentAction = $_GET['action'] ?? '';
$currentUserLevel = (int)($_SESSION['user_level'] ?? 3);
$isSuperAdmin = $currentUserLevel === 0;
$isAdmin = in_array($currentUserLevel, [1, 4, 5], true);
$fullName = trim(($_SESSION['firstName'] ?? '') . ' ' . ($_SESSION['lastName'] ?? '')) ?: 'Guest';
$position = $_SESSION['position'] ?? 'User';
$avatarFile = $_SESSION['profile_picture'] ?? 'default.png';
$avatarSrc = '../app/assets/profiles/' . $avatarFile;

function sidebarActiveClass(bool $active): string
{
    return $active
        ? 'bg-green-900 text-white'
        : 'text-slate-700 hover:bg-slate-100';
}
?>

<nav id="sidebar"
  class="fixed inset-y-0 left-0 z-40 flex h-screen w-full max-w-full flex-col overflow-hidden bg-white border-r border-slate-200 shadow-sm transition-transform duration-300 ease-in-out -translate-x-full md:translate-x-0 md:w-20 md:max-w-none hover:md:w-64 group peer">

  <!-- Header / Brand: compact icon-only by default -->
  <div class="flex h-14 items-center justify-center gap-2 border-b border-slate-200 px-3 transition-all duration-300 ease-in-out group-hover:justify-start">
    <div class="flex h-8 w-8 items-center justify-center rounded-2xl bg-slate-100">
      <img src="<?= BASE_URL ?>uploads/logo/official.png" class="h-6 w-auto object-contain" alt="Brand">
    </div>
    <div class="sidebar-header-text flex min-w-0 flex-1 flex-col overflow-hidden max-w-0 opacity-0 transition-all duration-300 ease-in-out group-hover:max-w-[11rem] group-hover:opacity-100">
      <p class="truncate text-xs font-bold text-slate-900 leading-tight">DPEARP</p>
      <p class="truncate text-xs text-slate-500">Portal</p>
    </div>
  </div>

  <!-- Main Content: scrollable -->
  <div class="flex-1 overflow-y-auto">
    <!-- Navigation Section -->
    <div class="px-2 py-2">
        <p class="px-2 text-xs font-bold uppercase tracking-wider text-slate-500 sidebar-section-label mb-2 max-h-0 overflow-hidden opacity-0 transition-all duration-300 ease-in-out group-hover:max-h-6 group-hover:opacity-100">Menu</p>
      
        <ul class="space-y-1">
        <li>
            <a href="index.php?controller=Auth&action=dashboard"
               class="flex h-10 w-full items-center justify-start gap-3 rounded-2xl px-3 transition-all duration-300 ease-in-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white <?= sidebarActiveClass($currentController == 'Auth' && $currentAction == 'dashboard') ?>">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg flex-shrink-0 sidebar-icon-bg <?= ($currentController == 'Auth' && $currentAction == 'dashboard') ? 'bg-green-900 text-white' : 'bg-slate-100 text-slate-600' ?>">
              <?= $dashboardIcon ?>
            </span>
            <span class="sidebar-item-label flex min-w-0 overflow-hidden whitespace-nowrap text-ellipsis text-sm font-medium opacity-0 max-w-0 -translate-x-1 transition-all duration-300 ease-in-out group-hover:max-w-[16rem] group-hover:opacity-100 group-hover:translate-x-0">
              Dashboard
            </span>
          </a>
        </li>

        <li>
          <a href="index.php?controller=Files&action=files"
             class="flex h-10 w-full items-center justify-start gap-3 rounded-2xl px-3 transition-all duration-300 ease-in-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white <?= sidebarActiveClass($currentController == 'Files' && $currentAction == 'files') ?>">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg flex-shrink-0 sidebar-icon-bg <?= ($currentController == 'Files' && $currentAction == 'files') ? 'bg-green-900 text-white' : 'bg-slate-100 text-slate-600' ?>">
              <?= $fileIcon ?>
            </span>
            <span class="sidebar-item-label flex min-w-0 overflow-hidden whitespace-nowrap text-ellipsis text-sm font-medium opacity-0 max-w-0 -translate-x-1 transition-all duration-300 ease-in-out group-hover:max-w-[16rem] group-hover:opacity-100 group-hover:translate-x-0">
              Library
            </span>
          </a>
        </li>

        <li>
          <a href="index.php?controller=correspondence&action=correspondence"
             class="flex h-10 w-full items-center justify-start gap-3 rounded-2xl px-3 transition-all duration-300 ease-in-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white <?= sidebarActiveClass($currentController == 'correspondence' && $currentAction == 'correspondence') ?>">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg flex-shrink-0 sidebar-icon-bg <?= ($currentController == 'correspondence' && $currentAction == 'correspondence') ? 'bg-green-900 text-white' : 'bg-slate-100 text-slate-600' ?>">
              <?= $correspondenceIcon ?>
            </span>
            <span class="sidebar-item-label flex min-w-0 overflow-hidden whitespace-nowrap text-ellipsis text-sm font-medium opacity-0 max-w-0 -translate-x-1 transition-all duration-300 ease-in-out group-hover:max-w-[16rem] group-hover:opacity-100 group-hover:translate-x-0">
              Correspondence
            </span>
          </a>
        </li>

        <li>
          <a href="index.php?controller=CarBookings&action=calendar"
             class="flex h-10 w-full items-center justify-start gap-3 rounded-2xl px-3 transition-all duration-300 ease-in-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white <?= sidebarActiveClass($currentController == 'CarBookings' && $currentAction == 'calendar') ?>">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg flex-shrink-0 sidebar-icon-bg <?= ($currentController == 'CarBookings' && $currentAction == 'calendar') ? 'bg-green-900 text-white' : 'bg-slate-100 text-slate-600' ?>">
              <?= $vehicleIcons ?>
            </span>
            <span class="sidebar-item-label flex min-w-0 overflow-hidden whitespace-nowrap text-ellipsis text-sm font-medium opacity-0 max-w-0 -translate-x-1 transition-all duration-300 ease-in-out group-hover:max-w-[16rem] group-hover:opacity-100 group-hover:translate-x-0">
              Vehicle Schedule
            </span>
          </a>
        </li>

        <li>
          <a href="index.php?controller=RoomBookings&action=calendar"
             class="flex h-10 w-full items-center justify-start gap-3 rounded-2xl px-3 transition-all duration-300 ease-in-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white <?= sidebarActiveClass($currentController == 'RoomBookings' && $currentAction == 'calendar') ?>">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg flex-shrink-0 sidebar-icon-bg <?= ($currentController == 'RoomBookings' && $currentAction == 'calendar') ? 'bg-green-900 text-white' : 'bg-slate-100 text-slate-600' ?>">
              <?= $roomIcon ?>
            </span>
            <span class="sidebar-item-label flex min-w-0 overflow-hidden whitespace-nowrap text-ellipsis text-sm font-medium opacity-0 max-w-0 -translate-x-1 transition-all duration-300 ease-in-out group-hover:max-w-[16rem] group-hover:opacity-100 group-hover:translate-x-0">
              Room Schedule
            </span>
          </a>
        </li>
        <li>
          <a href="index.php?controller=StaffDirectory&action=index"
             class="flex h-10 w-full items-center justify-start gap-3 rounded-2xl px-3 transition-all duration-300 ease-in-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white <?= sidebarActiveClass($currentController == 'StaffDirectory' && $currentAction == 'index') ?>">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg flex-shrink-0 sidebar-icon-bg <?= ($currentController == 'StaffDirectory' && $currentAction == 'index') ? 'bg-green-900 text-white' : 'bg-slate-100 text-slate-600' ?>">
              <?= $usersIcon ?>
            </span>
            <span class="sidebar-item-label flex min-w-0 overflow-hidden whitespace-nowrap text-ellipsis text-sm font-medium opacity-0 max-w-0 -translate-x-1 transition-all duration-300 ease-in-out group-hover:max-w-[16rem] group-hover:opacity-100 group-hover:translate-x-0">
              Staff Directory
            </span>
          </a>
        </li>
      </ul>
    </div>

    <!-- Administration Section -->
    <?php if ($isSuperAdmin || $isAdmin): ?>
      <div class="border-t border-slate-200 px-2 py-2">
        <p class="px-2 text-xs font-bold uppercase tracking-wider text-slate-500 sidebar-section-label mb-2 max-h-0 overflow-hidden opacity-0 transition-all duration-300 ease-in-out group-hover:max-h-6 group-hover:opacity-100">Admin</p>
        
        <ul class="space-y-1">
          <?php if ($isSuperAdmin): ?>
            <li>
              <a href="index.php?controller=Auth&action=users"
                 class="flex h-10 w-full items-center justify-start gap-3 rounded-2xl px-3 transition-all duration-300 ease-in-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white <?= sidebarActiveClass($currentController == 'Auth' && $currentAction == 'users') ?>">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg flex-shrink-0 sidebar-icon-bg <?= ($currentController == 'Auth' && $currentAction == 'users') ? 'bg-green-900 text-white' : 'bg-slate-100 text-slate-600' ?>">
                  <?= $usersIcon ?>
                </span>
                <span class="sidebar-item-label flex min-w-0 overflow-hidden whitespace-nowrap text-ellipsis text-sm font-medium opacity-0 max-w-0 -translate-x-1 transition-all duration-300 ease-in-out group-hover:max-w-[16rem] group-hover:opacity-100 group-hover:translate-x-0">
                  Core Users
                </span>
              </a>
            </li>

            <li>
              <a href="index.php?controller=Syslogs&action=syslogs"
                 class="flex h-10 w-full items-center justify-start gap-3 rounded-2xl px-3 transition-all duration-300 ease-in-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white <?= sidebarActiveClass($currentController == 'Syslogs' && $currentAction == 'syslogs') ?>">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg flex-shrink-0 sidebar-icon-bg <?= ($currentController == 'Syslogs' && $currentAction == 'syslogs') ? 'bg-green-900 text-white' : 'bg-slate-100 text-slate-600' ?>">
                  <?= $clipBoardIcon ?>
                </span>
                <span class="sidebar-item-label flex min-w-0 overflow-hidden whitespace-nowrap text-ellipsis text-sm font-medium opacity-0 max-w-0 -translate-x-1 transition-all duration-300 ease-in-out group-hover:max-w-[16rem] group-hover:opacity-100 group-hover:translate-x-0">
                  System Logs
                </span>
              </a>
            </li>
          <?php endif; ?>

          <li>
            <a href="index.php?controller=StandardUsers&action=index"
              class="flex h-10 w-full items-center justify-start gap-3 rounded-2xl px-3 transition-all duration-300 ease-in-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white <?= sidebarActiveClass($currentController == 'StandardUsers' && $currentAction == 'index') ?>">
              <span class="flex h-8 w-8 items-center justify-center rounded-lg flex-shrink-0 sidebar-icon-bg <?= ($currentController == 'StandardUsers' && $currentAction == 'index') ? 'bg-green-900 text-white' : 'bg-slate-100 text-slate-600' ?>">
                <?= $usersIcon ?>
              </span>
              <span class="sidebar-item-label flex min-w-0 overflow-hidden whitespace-nowrap text-ellipsis text-sm font-medium opacity-0 max-w-0 -translate-x-1 transition-all duration-300 ease-in-out group-hover:max-w-[16rem] group-hover:opacity-100 group-hover:translate-x-0">
                Std Users
              </span>
            </a>
          </li>
          <!-- <li>
            <a href="index.php?controller=StaffDirectory&action=index"
               class="flex h-10 w-full items-center justify-start gap-3 rounded-2xl px-3 transition-all duration-300 ease-in-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white <?= sidebarActiveClass($currentController == 'StaffDirectory' && $currentAction == 'index') ?>">
              <span class="flex h-8 w-8 items-center justify-center rounded-lg flex-shrink-0 sidebar-icon-bg <?= ($currentController == 'StaffDirectory' && $currentAction == 'index') ? 'bg-green-900 text-white' : 'bg-slate-100 text-slate-600' ?>">
                <?= $usersIcon ?>
              </span>
              <span class="flex min-w-0 overflow-hidden whitespace-nowrap text-ellipsis text-sm font-medium opacity-0 max-w-0 -translate-x-1 transition-all duration-300 ease-in-out group-hover:max-w-[16rem] group-hover:opacity-100 group-hover:translate-x-0">
                Staff Directory
              </span>
            </a>
          </li> -->
        </ul>
      </div>
    <?php endif; ?>
  </div>
</nav>
