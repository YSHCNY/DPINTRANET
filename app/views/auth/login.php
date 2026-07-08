


<div class="min-h-screen bg-slate-100 px-4 py-6 sm:px-6 lg:px-8">
  <div class="mx-auto flex min-h-[calc(100vh-3rem)] w-full max-w-5xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm md:flex-row">

    <div class="flex items-center justify-center bg-slate-900 p-6 md:w-2/5 md:p-8 lg:p-10">
      <div class="relative w-full overflow-hidden rounded-xl border border-white/10 bg-slate-950/70 p-6 sm:p-7">
        <img src="<?= BASE_URL ?>uploads/banner/Frame5.png" alt="" class="absolute inset-0 h-full w-full object-cover opacity-20">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-900/90 via-slate-900/80 to-slate-800/90"></div>

        <div class="relative">
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/15 bg-white/10">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 7.5A2.25 2.25 0 0 1 6.75 5.25h10.5a2.25 2.25 0 0 1 2.25 2.25v9A2.25 2.25 0 0 1 17.25 18.75H6.75A2.25 2.25 0 0 1 4.5 16.5v-9Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9h7.5M8.25 12h7.5M8.25 15h4.5" />
              </svg>
            </div>
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-400">DPINTRANET</p>
              <h1 class="text-base font-semibold text-white">Integrated Enterprise Portal</h1>
            </div>
          </div>

          <p class="mt-5 text-sm leading-6 text-slate-300">
            Coordinate correspondence, repository work, fleet movement, room scheduling, and service notifications from one secure workspace.
          </p>

          <div class="mt-6 space-y-2">
            <?php $modules = ['Correspondence', 'Repository', 'Fleet Management', 'Room Scheduling', 'Notifications']; ?>
            <?php foreach ($modules as $module): ?>
              <div class="flex items-center gap-2 rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-200">
                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white/10 text-[11px] text-white">✓</span>
                <span><?= htmlspecialchars($module) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="flex w-full flex-col justify-between bg-white p-6 sm:p-8 md:w-3/5 md:p-10 lg:p-12">
      <div>
        <?php if (isset($_GET['wc']) && $_GET['wc'] === 'signedOut'): ?>
          <div id="flash"
            class="mb-6 flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-900 px-4 py-3 text-sm text-white shadow-sm opacity-0 translate-y-2 transition-all duration-500 ease-out">
            <svg xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
              class="h-5 w-5 flex-shrink-0 animate-bounce"
              id="flashIcon">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
            </svg>
            <span class="font-medium">Logged out successfully.</span>
          </div>

          <script>
            const flash = document.getElementById('flash');
            const icon = document.getElementById('flashIcon');

            requestAnimationFrame(() => {
              flash.classList.remove('opacity-0', 'translate-y-2');
            });

            setTimeout(() => {
              icon.classList.remove('animate-bounce');
              icon.classList.add('animate-pulse');
            }, 700);

            setTimeout(() => {
              flash.classList.add('opacity-0', 'translate-y-2');
              setTimeout(() => flash.remove(), 500);
            }, 3000);
          </script>
        <?php endif; ?>

        <div class="flex items-center gap-3">
          <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-2">
            <img src="<?= BASE_URL ?>uploads/logo/official.png" alt="DPINTRANET logo" class="h-full w-full object-contain">
          </div>
          <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Authorized access</p>
            <h2 class="text-base font-semibold text-slate-900">Welcome back</h2>
          </div>
        </div>

        <p class="mt-3 text-sm leading-6 text-slate-500">
          Sign in to continue to your secure workspace.
        </p>

        <?php if (!empty($error)): ?>
          <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-600">
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="" class="mt-6 space-y-4">
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-600">Username</label>
            <input type="text" name="username" required
              placeholder="Enter username"
              class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 transition focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200"
            >
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-600">Password</label>
            <input type="password" name="password" required
              placeholder="Enter password"
              class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 transition focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200"
            >
          </div>

          <button type="submit"
            class="h-11 w-full rounded-xl bg-slate-900 px-4 text-sm font-medium text-white transition hover:bg-slate-800">
            Login
          </button>
        </form>

        <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4">
          <a href="index.php?controller=StandardPortal&action=login"
            class="flex items-center justify-between gap-3 text-sm font-medium text-slate-700 transition hover:text-slate-900">
            <span>
              <span class="block">Standard User Portal</span>
              <span class="mt-1 block text-xs font-normal text-slate-500">Receive assigned correspondence and updates.</span>
            </span>
            <span class="text-base leading-none">→</span>
          </a>
        </div>
      </div>

      <div class="mt-8 border-t border-slate-200 pt-4 text-[11px] text-slate-500">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
          <span>System version 1.0</span>
          <span>© 2026 DPINTRANET</span>
        </div>
        <p class="mt-2 leading-5">Authorized personnel only. All login activities are monitored.</p>
      </div>
    </div>
  </div>
</div>
