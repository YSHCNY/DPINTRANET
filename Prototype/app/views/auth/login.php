


<div class = 'bg-gray-100'>



<div class="min-h-screen flex items-center justify-center bg-gray-100 px-4">

  <div class="w-full max-w-4xl flex flex-col md:flex-row bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-200">


    <!-- Left Image Panel -->
    <div class="hidden md:block md:w-1/2 bg-blue-600 flex items-center justify-center">
      <img src=".././app/assets/img/Frame5.png" alt="Login Image" class="object-cover h-full w-full">
    </div>

    <!-- Login Form -->
    <div class="w-full md:w-1/2 p-8 md:p-12">
   
          
      <?php if (isset($_GET['wc']) && $_GET['wc'] === 'signedOut'): ?>
      <div id="flash"
      class="   mb-6 flex items-center gap-3
      px-5 py-4 rounded-2xl
      bg-gradient-to-r from-sky-500 to-blue-500
      text-white shadow-lg
      opacity-0 translate-y-2
      transition-all duration-500 ease-out">


      <!-- Animated Icon -->
      <svg xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
      stroke-width="1.5"
      stroke="currentColor"
      class="w-5 h-5 flex-shrink-0 animate-bounce"
      id="flashIcon">
      <path stroke-linecap="round" stroke-linejoin="round"
      d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
      </svg>


      <span class="text-md font-semibold">
      Logged Out!
      </span>
      </div>


      <script>
      const flash = document.getElementById('flash');
      const icon = document.getElementById('flashIcon');


      // Animate in
      requestAnimationFrame(() => {
      flash.classList.remove('opacity-0', 'translate-y-2');
      });


      // Stop bounce after entrance for polish
      setTimeout(() => {
      icon.classList.remove('animate-bounce');
      icon.classList.add('animate-pulse');
      }, 700);


      // Fade out smoothly
      setTimeout(() => {
      flash.classList.add('opacity-0', 'translate-y-2');
      setTimeout(() => flash.remove(), 500);
      }, 3000);
      </script>
      <?php endif; ?>


      <div class="w-1/3  flex my-8">
      <img src=".././app/assets/logo/symbol.png" alt="Login Image" class="object-cover h-1/2 w-1/2">
    </div>


      <h3 class="text-2xl font-semibold text-gray-800 mb-2">
       Enter your credentials
      </h3>
      <p class=" text-gray-500 mb-6">
        Login to your account
      </p>

      <?php if (!empty($error)): ?>
        <div class="mb-4 rounded-xl bg-red-50 border border-red-200 text-red-600 px-4 py-3 text-center">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>



      <form method="POST" action="" class="space-y-5">

        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Username</label>
          <input type="text" name="username" required
            placeholder="Enter username"
            class="w-full rounded-xl border border-gray-300
                   px-4 py-3 text-gray-800
                   focus:outline-none focus:ring-2 focus:ring-blue-500
                   transition"
          >
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Password</label>
          <input type="password" name="password" required
            placeholder="Enter password"
            class="w-full rounded-xl border border-gray-300
                   px-4 py-3 text-gray-800
                   focus:outline-none focus:ring-2 focus:ring-blue-500
                   transition"
          >
        </div>

        <button type="submit"
          class="w-full rounded-xl bg-sky-700 px-4 py-3 font-medium
                 text-white hover:bg-sky-800 transition">
          Login
        </button>

	      </form>

	      <div class="mt-6 border-t border-gray-200 pt-5">
	        <a href="index.php?controller=StandardPortal&action=login"
	           class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-semibold text-gray-700 transition hover:border-blue-100 hover:bg-blue-50 hover:text-blue-700">
	          <span>
	            <span class="block">Standard User Portal</span>
	            <span class="block text-xs font-normal text-gray-500">Receive assigned correspondence</span>
	          </span>
	          <span class="text-lg leading-none">&rsaquo;</span>
	        </a>
	      </div>

	      <!-- Optional register link -->
      <!--
      <p class="text-center text-gray-500 mt-6 text-sm">
        Don't have an account? 
        <a href="index.php?controller=Auth&action=register" class="text-blue-600 font-medium hover:underline">
          Register here
        </a>
      </p>
      -->

    </div>
  </div>

</div>


</div>
