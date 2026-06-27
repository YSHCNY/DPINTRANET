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
        } elseif ($currentController === 'RoomBookings' && $currentAction === 'calendar') {
            $documentTitle = 'Room Bookings';
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
      /* :root{
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
      } */

      
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
  <div class="ml-0 md:ml-[17.5rem] min-h-screen flex flex-col transition-all">

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
