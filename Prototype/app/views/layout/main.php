<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php
        $currentController = $_GET['controller'] ?? '';
        $currentAction = $_GET['action'] ?? '';

        if ($currentController === 'Auth' && $currentAction === 'dashboard') {
            $documentTitle = 'Dashboard';
        } elseif ($currentController === 'Files' && $currentAction === 'files') {
            $documentTitle = 'Files Management';
        } elseif ($currentController === 'Auth' && $currentAction === 'users') {
            $documentTitle = 'Core Users';
        } elseif ($currentController === 'Auth' && $currentAction === 'profile') {
            $documentTitle = 'Profile & Security';
        } elseif ($currentController === 'Syslogs' && $currentAction === 'syslogs') {
            $documentTitle = 'System Logs';
        } elseif ($currentController === 'correspondence' && $currentAction === 'correspondence') {
            $documentTitle = 'Correspondence Management';
        } elseif ($currentController === 'StandardUsers' && $currentAction === 'index') {
            $documentTitle = 'Standard Users';
        } elseif ($currentController === 'CarBookings' && $currentAction === 'calendar') {
            $documentTitle = 'Car Bookings';
        } else {
            $documentTitle = 'FMS Portal';
        }
    ?>
    <title><?= htmlspecialchars($documentTitle) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>

    <!-- Global design tokens for uniform design kit -->
    <style>
      :root{
        --text: #0f1724; /* primary text: softer deep slate */
        --bg-page: #f7f8fb; /* page background: subtle off-white */
        --card: #ffffff;
        --surface-1: #f5f7f9; /* subtle surface */
        --surface-2: #e9edf0;
        --primary: #346656; /* muted green primary */
        --secondary: #111827; /* deep accent / headings */
        --accent: #4b7a58; /* accent (hover, highlights) */
        --muted: #6b7280;
        --success: #059669;
        --danger: #dc2626;
        --info: #2563eb;
        --warning: #f59e0b;
        --shadow-sm: 0 6px 18px rgba(15,23,42,0.05);
        --shadow-md: 0 10px 30px rgba(15,23,42,0.06);
        --radius-lg: 10px;
      }

      body { background: var(--bg-page); color: var(--text); font-family: Inter, ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale; }

      /* Buttons & controls: subtle, rounded, low-contrast */
      .btn-primary { background: var(--primary); color: #fff; border-radius: 8px; padding: 8px 14px; font-weight:600; box-shadow: var(--shadow-sm); border: none; }
      .btn-ghost { background: transparent; border: 1px solid var(--surface-2); color: var(--secondary); padding: 8px 12px; border-radius: 8px; }
      .card-header { background: transparent; border-bottom:1px solid var(--surface-2); }

      /* Form controls: neutral, spacious, consistent */
      input[type="text"], input[type="email"], input[type="date"], input[type="search"], select, textarea, .file { 
        border: 1px solid var(--surface-2); 
        background: var(--card);
        padding: 0.625rem 0.75rem;
        border-radius: 10px;
        outline: none;
        color: var(--secondary);
        transition: box-shadow 120ms ease, border-color 120ms ease, transform 120ms ease;
      }
      input:focus, select:focus, textarea:focus { box-shadow: 0 6px 18px rgba(52,102,86,0.06); border-color: var(--accent); }

      /* Reduce noisy gradients and heavy visuals */
      .bg-gradient-to-r, .bg-gradient-to-b { background: none !important; }

      .app-toast { display:flex; gap:12px; align-items:center; min-width:220px; max-width:520px; padding:12px 14px; border-radius:12px; color:var(--text); box-shadow:var(--shadow-md); font-weight:700; background:var(--card); border:1px solid var(--surface-2); }
      .app-toast-success { border-left:4px solid var(--success); background: linear-gradient(90deg, rgba(5,150,105,0.06), var(--card)); }
      .app-toast-error { border-left:4px solid var(--danger); background: linear-gradient(90deg, rgba(220,38,38,0.06), var(--card)); }
      .app-toast-info { border-left:4px solid var(--info); background: linear-gradient(90deg, rgba(37,99,235,0.06), var(--card)); }
      .app-toast-warning { border-left:4px solid var(--warning); background: linear-gradient(90deg, rgba(245,158,11,0.06), var(--card)); }
      .app-toast-progress { height:3px; background:var(--surface-2); border-radius:4px; overflow:hidden; margin-top:8px; }
      .app-toast-success .app-toast-progress > i { background:var(--success); }
      .app-toast-error .app-toast-progress > i { background:var(--danger); }
      .app-toast-info .app-toast-progress > i { background:var(--info); }
      .app-toast-warning .app-toast-progress > i { background:var(--warning); }
      /* Scoped theme mapping: use when page root has class 'theme-palette' */
      .theme-palette { background: var(--bg-page); color: var(--text); }
      .theme-palette .text-slate-900 { color: var(--secondary) !important; }
      .theme-palette .text-slate-700, .theme-palette .text-slate-600 { color: var(--secondary) !important; }
      .theme-palette .text-slate-500 { color: var(--muted) !important; }
      .theme-palette .bg-sky-600, .theme-palette .bg-sky-700 { background: var(--primary) !important; color: #fff !important; }
      .theme-palette .bg-sky-50 { background: rgba(70,124,77,0.06) !important; }
      .theme-palette .bg-emerald-50 { background: rgba(70,124,77,0.06) !important; }
      .theme-palette .bg-emerald-500, .theme-palette .bg-blue-500 { background: var(--primary) !important; color: #fff !important; }
      .theme-palette .text-emerald-700 { color: var(--primary) !important; }
      .theme-palette .border-slate-200 { border-color: var(--surface-2) !important; }
      .theme-palette .bg-slate-50 { background: var(--bg-card) !important; }
      .theme-palette .hover\:bg-slate-50:hover { background: rgba(0,0,0,0.02) !important; }

      /* Header and sidebar specific tokens */
      .theme-palette .header-bg { background: var(--secondary) !important; }
      .theme-palette .header-text { color: var(--bg-card) !important; }
      .theme-palette .profile-btn { background: transparent; }
      .theme-palette .profile-menu { border-radius: 18px; overflow: hidden; background: var(--bg-card); border: 1px solid var(--surface-2); box-shadow: 0 20px 60px rgba(4,3,22,0.08); }
      .theme-palette .profile-menu-header { border-bottom: 1px solid var(--surface-2); background: linear-gradient(90deg, rgba(255,255,255,0.02), var(--bg-card)); }
      .theme-palette .profile-heading { color: var(--secondary); }
      .theme-palette .profile-muted { color: var(--muted); }
      .theme-palette .role-badge { background: rgba(112,147,61,0.06); border: 1px solid rgba(112,147,61,0.12); color: var(--primary); }

      .theme-palette #sidebar.sidebar-bg { background: var(--bg-card); }
      .theme-palette #sidebar.sidebar-border { border-right: 1px solid var(--surface-2); }
      .theme-palette #sidebar.sidebar-text { color: var(--secondary); }
      .theme-palette .brand-card { background: linear-gradient(180deg, rgba(255,255,255,0.5), var(--bg-card)); border: 1px solid var(--surface-2); }
      .theme-palette .sidebar-heading { color: var(--secondary); }
      .theme-palette .sidebar-muted { color: var(--muted); }
      .theme-palette .sidebar-active { background: var(--primary) !important; color: #fff !important; box-shadow: var(--shadow-sm); }
    </style>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/colreorder/1.6.2/css/colReorder.dataTables.min.css">

    <!-- jQuery and DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/colreorder/1.6.2/js/dataTables.colReorder.min.js"></script>
    <!-- ColReorderWithResize plugin -->
    <script src="https://cdn.jsdelivr.net/gh/akottr/ColReorderWithResize/ColReorderWithResize.js"></script>
    <!-- Tailwind overrides for DataTables -->
    <link rel="stylesheet" href="../../assets/css/datatables-tailwind.css">

</head>


<body class=" bg-light">
  <body class="theme-palette bg-light">
<?php require __DIR__ . '/../partials/icons.php'; ?>
  <!-- Sidebar -->
  <?php require __DIR__ . '/../partials/sidebar.php'; ?>

  <!-- Page Wrapper -->
  <div class="ml-0 md:ml-80 min-h-screen flex flex-col transition-all">

    <!-- Header -->
    <?php require __DIR__ . '/../partials/header.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 p-6">

      <!-- Flash message -->
        <?php if (isset($_SESSION['message'])): ?>
            <div id="flash" class="mb-4 px-4 py-3 rounded-md text-white
                        <?= $_SESSION['msg_type'] === 'success' ? 'bg-green-500' : 'bg-red-500' ?>">
                <?= $_SESSION['message'] ?>
            </div>
            <script>
                setTimeout(() => {
                    document.getElementById("flash").remove();
                }, 3000);
            </script>
            <?php 
                unset($_SESSION['message']);
                unset($_SESSION['msg_type']);
            ?>
        <?php endif; ?>



          <?php if (isset($_GET['wc']) && $_GET['wc'] === 'welcome'): ?>
                <div id="flash"
                  class="mb-6 flex items-center gap-3 px-5 py-4 rounded-xl text-white shadow-lg transition-all duration-500 ease-out opacity-0 translate-y-2 bg-gradient-to-r from-[#111827] to-[#222222]">


              <!-- Icon -->
          <svg id="flashIcon"
          class="w-5 h-5 flex-shrink-0 animate-bounce"
          fill="none" stroke="currentColor" stroke-width="2"
          viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round"
          d="M5 13l4 4L19 7"/>
          </svg>

              <span class="text-md font-medium">
                Welcome back, <?= htmlspecialchars($_SESSION['firstName']) ?>!
              </span>
            </div>


            <script>
              const flash = document.getElementById('flash');


              // animate in
              requestAnimationFrame(() => {
                flash.classList.remove('opacity-0', 'translate-y-2');
              });


              // fade out smoothly
              setTimeout(() => {
                flash.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => flash.remove(), 500);
              }, 3000);
            </script>
          <?php endif; ?>

        
      <?= $content ?>
    </main>

  </div>

</body>
</html>
