<?php
$currentController = $_GET['controller'] ?? '';
$currentAction = $_GET['action'] ?? '';
?>

<nav id="sidebar"
     class="fixed top-0 left-0 h-screen w-72
            bg-white/30 backdrop-blur-xl
            border-r-2 border-stone-50
            shadow
            flex flex-col z-30 ">

  <div class="px-5 py-4 ">

 <div class="w-44 my-2 mx-auto">
  <img src=".././app/assets/logo/brand2.png"
       class="w-full h-auto">
</div>
  </div>

  <ul class="flex-1 px-3 py-4 space-y-1 text-gray-700">
    <li class="text-xs uppercase text-gray-400 px-3 mb-2">Navigation</li>

    <li>
      <a href="index.php?controller=Auth&action=dashboard"
         class="flex items-center gap-3 px-3 py-2.5 rounded-xl  
          <?= ($currentController == 'Auth' && $currentAction == 'dashboard') ? 'bg-sky-600 text-white' : 'text-gray-700 hover:bg-gray-200 transition' ?>">
      
        <?= $dashboardIcon ?>
        Dashboard
      </a>
    </li>

    <li>
      <a href="index.php?controller=Files&action=files"
         class="flex items-center gap-3 px-3 py-2.5 rounded-xl  
         <?= ($currentController == 'Files' && $currentAction == 'files') ? 'bg-sky-600 text-white' : 'text-gray-700 hover:bg-gray-200 transition' ?>">
        <?= $fileIcon ?>
        Files
      </a>
    </li>


        <li>
      <a href="index.php?controller=correspondence&action=correspondence"
         class="flex items-center gap-3 px-3 py-2.5 rounded-xl  
         <?= ($currentController == 'correspondence' && $currentAction == 'correspondence') ? 'bg-sky-600 text-white' : 'text-gray-700 hover:bg-gray-200 transition' ?>">
        <?= $correspondenceIcon ?>
        Correspondence
      </a>
    </li>

    <!-- <li>
      <a href="index.php?controller=Contracts&action=contracts"
         class="flex items-center gap-3 px-3 py-2.5 rounded-xl  
         <?= ($currentController == 'Contracts' && $currentAction == 'contracts') ? 'bg-sky-600 text-white' : 'text-gray-700 hover:bg-gray-200 transition' ?>">
        <?= $contractsIcon ?>
        Contracts
      </a>
    </li> -->

    <?php if(in_array((string)($_SESSION['user_level'] ?? ''), ['0', '1'], true)){ ?>
    <li class="text-xs uppercase text-gray-400 px-3 mt-5 mb-2">More</li>

	    <li>
	      <a href="index.php?controller=Auth&action=users"
	         class="flex items-center gap-3 px-3 py-2.5 rounded-xl 
	         <?= ($currentController == 'Auth' && $currentAction == 'users') ? 'bg-sky-600 text-white' : 'text-gray-700 hover:bg-gray-200 transition' ?>">
	        <?= $usersIcon ?>
	        Core Users
	      </a>
	    </li>

	    <li>
	      <a href="index.php?controller=StandardUsers&action=index"
	         class="flex items-center gap-3 px-3 py-2.5 rounded-xl 
	         <?= ($currentController == 'StandardUsers') ? 'bg-sky-600 text-white' : 'text-gray-700 hover:bg-gray-200 transition' ?>">
	        <?= $usersIcon ?>
	        Standard Users
	      </a>
	    </li>

	    <li>
	      <a href="index.php?controller=Syslogs&action=syslogs"
         class="flex items-center gap-3 px-3 py-2.5 rounded-xl 
         <?= ($currentController == 'Auth' && $currentAction == 'syslogs') ? 'bg-sky-600 text-white' : 'text-gray-700 hover:bg-gray-200 transition' ?>">
        <?= $clipBoardIcon ?>
        System Logs
      </a>
    </li>


    
    <?php } ?>

  </ul>

  <!-- <div class="p-4 border-t border-gray-200">
    <a href="index.php?controller=Auth&action=logout"
       class="flex items-center justify-center gap-2 py-2.5 rounded-xl
              border border-gray-300 hover:bg-gray-100 transition">
      <i class="bi bi-box-arrow-right"></i> Logout
    </a>
  </div> -->

</nav>

