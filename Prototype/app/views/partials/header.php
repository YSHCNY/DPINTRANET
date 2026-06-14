<?php
$user_name = $_SESSION['user']['first_name'] ?? 'Guest';
$user_image = $_SESSION['user']['profile_image'] ?? 'default.png';


$currentController = $_GET['controller'] ?? '';
$currentAction = $_GET['action'] ?? '';

if ($currentController == 'Auth' && $currentAction == 'dashboard') {
  $pageTitle = 'Dashboard';
} elseif ($currentController == 'Files' && $currentAction == 'files') {
  $pageTitle = 'Files Management';
} elseif ($currentController == 'Auth' && $currentAction == 'users') {
  $pageTitle = 'Core Users';
} elseif ($currentController == 'Syslogs' && $currentAction == 'syslogs') {
  $pageTitle = 'System Logs';
} else {
  $pageTitle = 'DALTON BYPASS - FMS';
}
?>
 
<nav class="sticky top-0 z-40
            bg-sky-700 backdrop-blur-xl
           
            px-6 py-3
            flex justify-between items-center">

  <div class="flex items-center gap-4">
    <button id="sidebarToggle"
            class="md:hidden text-2xl text-stone-100 focus:outline-none">
      &#9776;
    </button>
    <span class="text-lg font-semibold text-stone-100"> <?= $pageTitle  ?></span>
  </div>

  <div class="flex items-center gap-3">
    <div class = 'text-right'>
    <span class="font-medium text-stone-50"><?= $_SESSION['firstName'] . ' ' . $_SESSION['lastName']?></span> <br>
    <span class="font-normal text-sky-500"><?= $_SESSION['position'] ?></span>
</div>

   

      <div class="relative ml-3 group">
      <!-- Avatar button -->
      <button
      class="flex rounded-full focus:outline-none focus:ring-2 focus:ring-white/40">
      <img src=".././app/assets/profiles/<?= $_SESSION['profile_picture'] ?>"
      class="size-9 rounded-full object-cover border border-white/20 shadow-sm"
      alt="User menu">
      </button>


      <!-- Dropdown -->
      <div
      class="absolute right-0 mt-2 w-48
      origin-top-right rounded-xl
      bg-sky-800 py-1 shadow-lg
      ring-1 ring-black/10
      opacity-0 scale-95 pointer-events-none
      transition-all duration-150 ease-out
      group-focus-within:opacity-100
      group-focus-within:scale-100
      group-focus-within:pointer-events-auto">


      <a href="#"
      class="block px-4 py-2 text-sm text-gray-300 hover:bg-white/5 focus:bg-white/5">
      Your profile
      </a>


      <a href="#"
      class="block px-4 py-2 text-sm text-gray-300 hover:bg-white/5 focus:bg-white/5">
      Settings
      </a>


      <a href="index.php?controller=Auth&action=logout&wc=signedOut"
      class="block px-4 py-2 text-sm text-gray-300 hover:bg-white/5 focus:bg-white/5">
      Sign out
      </a>
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
</script>
