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
  class="fixed inset-y-0 left-0 z-30 flex h-screen w-80 -translate-x-full flex-col bg-white border-r border-slate-200 shadow-sm transition-all duration-300 ease-out md:translate-x-0 sidebar-transition">

  <style>
    /* Modern CRM 8-grid responsive sizing */
    :root { 
      --sidebar-expanded-width: 17.5rem;    /* 280px - compact expanded */
      --sidebar-collapsed-width: 4.5rem;    /* 72px - compact collapsed */
    }
    
    #sidebar.sidebar-transition {
      width: var(--sidebar-expanded-width);
    }
    
    #sidebar.sidebar-collapsed {
      width: var(--sidebar-collapsed-width);
    }

    /* Smooth text transitions on collapse */
    #sidebar.sidebar-collapsed .sidebar-expanded {
      opacity: 0;
      transform: translateX(-4px);
      pointer-events: none;
      transition: opacity 200ms ease, transform 200ms ease;
    }
    
    #sidebar:not(.sidebar-collapsed) .sidebar-collapsed-only {
      opacity: 0;
      pointer-events: none;
    }

    /* Text utilities */
    #sidebar .sidebar-truncate { 
      white-space: nowrap; 
      overflow: hidden; 
      text-overflow: ellipsis;
    }

    /* Responsive breakpoints */
    @media (max-width: 768px) {
      #sidebar { width: 100%; --sidebar-expanded-width: 100%; }
      #sidebar.sidebar-collapsed { width: 4.5rem; }
      .sidebar-label-mobile { display: none; }
      #sidebar.sidebar-collapsed .sidebar-label-mobile { display: inline; }
    }

    /* Hover effects */
    #sidebar a:not(.active) span.sidebar-icon-bg {
      transition: all 150ms ease;
    }
    /* #sidebar a:hover:not(.active) span.sidebar-icon-bg {
      background-color: rgb(32, 92, 18);
    } */
  </style>

  <!-- Header / Brand: compact 8px grid -->
  <div class="border-b border-slate-200 px-3 py-2">
    <div class="flex items-center gap-2 rounded-lg px-2 py-1.5">
      <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 flex-shrink-0">
        <img src="<?= BASE_URL ?>uploads/logo/official.png" class="h-6 w-auto object-contain" alt="Brand">
      </div>
      <div class="min-w-0 sidebar-expanded">
        <p class="sidebar-truncate text-xs font-bold text-slate-900 leading-tight">DPEARP</p>
        <p class="sidebar-truncate text-[10px] text-slate-500">Portal</p>
      </div>
    </div>
  </div>

  <!-- Main Content: scrollable -->
  <div class="flex-1 overflow-y-auto">
    <!-- Navigation Section -->
    <div class="px-2 py-3">
      <p class="px-2 text-[10px] font-bold uppercase tracking-wider text-slate-500 sidebar-expanded mb-2">Menu</p>
      
      <ul class="space-y-1">
        <li>
          <a href="index.php?controller=Auth&action=dashboard"
             class="group flex items-center gap-2 rounded-lg px-2 py-2 text-sm font-medium transition-colors <?= sidebarActiveClass($currentController == 'Auth' && $currentAction == 'dashboard') ?>">
            <span class="flex h-8 w-8 items-center justify-center rounded-md flex-shrink-0 sidebar-icon-bg <?= ($currentController == 'Auth' && $currentAction == 'dashboard') ? 'bg-green-900 text-white' : 'bg-slate-100 text-slate-600' ?>">
              <?= $dashboardIcon ?>
            </span>
            <span class="min-w-0 sidebar-expanded leading-tight">
              <span class="text-sm font-medium">Dashboard</span>
            </span>
          </a>
        </li>

        <li>
          <a href="index.php?controller=Files&action=files"
             class="group flex items-center gap-2 rounded-lg px-2 py-2 text-sm font-medium transition-colors <?= sidebarActiveClass($currentController == 'Files' && $currentAction == 'files') ?>">
            <span class="flex h-8 w-8 items-center justify-center rounded-md flex-shrink-0 sidebar-icon-bg <?= ($currentController == 'Files' && $currentAction == 'files') ? 'bg-green-900 text-white' : 'bg-slate-100 text-slate-600' ?>">
              <?= $fileIcon ?>
            </span>
            <span class="min-w-0 sidebar-expanded leading-tight">
              <span class="text-sm font-medium">Library</span>
            </span>
          </a>
        </li>

        <li>
          <a href="index.php?controller=correspondence&action=correspondence"
             class="group flex items-center gap-2 rounded-lg px-2 py-2 text-sm font-medium transition-colors <?= sidebarActiveClass($currentController == 'correspondence' && $currentAction == 'correspondence') ?>">
            <span class="flex h-8 w-8 items-center justify-center rounded-md flex-shrink-0 sidebar-icon-bg <?= ($currentController == 'correspondence' && $currentAction == 'correspondence') ? 'bg-green-900 text-white' : 'bg-slate-100 text-slate-600' ?>">
              <?= $correspondenceIcon ?>
            </span>
            <span class="min-w-0 sidebar-expanded leading-tight">
              <span class="text-sm font-medium">Correspondence</span>
            </span>
          </a>
        </li>

        <li>
          <a href="index.php?controller=CarBookings&action=calendar"
             class="group flex items-center gap-2 rounded-lg px-2 py-2 text-sm font-medium transition-colors <?= sidebarActiveClass($currentController == 'CarBookings' && $currentAction == 'calendar') ?>">
            <span class="flex h-8 w-8 items-center justify-center rounded-md flex-shrink-0 sidebar-icon-bg <?= ($currentController == 'CarBookings' && $currentAction == 'calendar') ? 'bg-green-900 text-white' : 'bg-slate-100 text-slate-600' ?>">
              <?= $vehicleIcons ?>
            </span>
            <span class="min-w-0 sidebar-expanded leading-tight">
              <span class="text-sm font-medium">Vehicle Schedule</span>
            </span>
          </a>
        </li>

        <li>
          <a href="index.php?controller=RoomBookings&action=calendar"
             class="group flex items-center gap-2 rounded-lg px-2 py-2 text-sm font-medium transition-colors <?= sidebarActiveClass($currentController == 'RoomBookings' && $currentAction == 'calendar') ?>">
            <span class="flex h-8 w-8 items-center justify-center rounded-md flex-shrink-0 sidebar-icon-bg <?= ($currentController == 'RoomBookings' && $currentAction == 'calendar') ? 'bg-green-900 text-white' : 'bg-slate-100 text-slate-600' ?>">
              <?= $roomIcon ?>
            </span>
            <span class="min-w-0 sidebar-expanded leading-tight">
              <span class="text-sm font-medium">Room Schedule</span>
            </span>
          </a>
        </li>
      </ul>
    </div>

    <!-- Administration Section -->
    <?php if ($isSuperAdmin || $isAdmin): ?>
      <div class="border-t border-slate-200 px-2 py-3">
        <p class="px-2 text-[10px] font-bold uppercase tracking-wider text-slate-500 sidebar-expanded mb-2">Admin</p>
        
        <ul class="space-y-1">
          <?php if ($isSuperAdmin): ?>
            <li>
              <a href="index.php?controller=Auth&action=users"
                 class="group flex items-center gap-2 rounded-lg px-2 py-2 text-sm font-medium transition-colors <?= sidebarActiveClass($currentController == 'Auth' && $currentAction == 'users') ?>">
                <span class="flex h-8 w-8 items-center justify-center rounded-md flex-shrink-0 sidebar-icon-bg <?= ($currentController == 'Auth' && $currentAction == 'users') ? 'bg-green-900 text-white' : 'bg-slate-100 text-slate-600' ?>">
                  <?= $usersIcon ?>
                </span>
                <span class="min-w-0 sidebar-expanded leading-tight">
                  <span class="text-sm font-medium">Core Users</span>
                </span>
              </a>
            </li>

            <li>
              <a href="index.php?controller=Syslogs&action=syslogs"
                 class="group flex items-center gap-2 rounded-lg px-2 py-2 text-sm font-medium transition-colors <?= sidebarActiveClass($currentController == 'Syslogs' && $currentAction == 'syslogs') ?>">
                <span class="flex h-8 w-8 items-center justify-center rounded-md flex-shrink-0 sidebar-icon-bg <?= ($currentController == 'Syslogs' && $currentAction == 'syslogs') ? 'bg-green-900 text-white' : 'bg-slate-100 text-slate-600' ?>">
                  <?= $clipBoardIcon ?>
                </span>
                <span class="min-w-0 sidebar-expanded leading-tight">
                  <span class="text-sm font-medium">System Logs</span>
                </span>
              </a>
            </li>
          <?php endif; ?>

          <li>
            <a href="index.php?controller=StandardUsers&action=index"
               class="group flex items-center gap-2 rounded-lg px-2 py-2 text-sm font-medium transition-colors <?= sidebarActiveClass($currentController == 'StandardUsers' && $currentAction == 'index') ?>">
              <span class="flex h-8 w-8 items-center justify-center rounded-md flex-shrink-0 sidebar-icon-bg <?= ($currentController == 'StandardUsers' && $currentAction == 'index') ? 'bg-green-900 text-white' : 'bg-slate-100 text-slate-600' ?>">
                <?= $usersIcon ?>
              </span>
              <span class="min-w-0 sidebar-expanded leading-tight">
                <span class="text-sm font-medium">Std Users</span>
              </span>
            </a>
          </li>
        </ul>
      </div>
    <?php endif; ?>
  </div>
</nav>
