


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

        <?php if (!empty($_SESSION['session_expired_message'])): ?>
          <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-700">
            <?= htmlspecialchars($_SESSION['session_expired_message']) ?> Please sign in again to continue.
          </div>
        <?php endif; ?>

        <?php if (!empty($showExpiredSessionModal) || !empty($_SESSION['session_expired_message'])): ?>
          <div id="expiredSessionModal" class="<?= !empty($showExpiredSessionModal) ? '' : 'hidden' ?> fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 px-4">
            <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
              <div class="flex items-center justify-center rounded-full bg-amber-100 p-3 text-amber-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.01M10.489 3.606l-8.16 14.04A1.5 1.5 0 0 0 3.66 20.25h16.68a1.5 1.5 0 0 0 1.33-2.304l-8.16-14.04a1.5 1.5 0 0 0-2.66 0Z" />
                </svg>
              </div>
              <h3 class="mt-4 text-center text-lg font-semibold text-slate-900">Session expired</h3>
              <p class="mt-2 text-center text-sm leading-6 text-slate-600">You were automatically logged out due to inactivity. Please sign in again to continue.</p>
              <p id="recoverCountdownText" class="mt-3 text-center text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Recover session available</p>
              <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-center">
                <a id="loginAgainLink" href="index.php?controller=Auth&action=login&dismissExpired=1" class="inline-flex h-11 items-center justify-center rounded-xl bg-slate-900 px-4 text-sm font-semibold text-white transition hover:bg-slate-800">Log in again</a>
                <a id="recoverSessionLink" href="index.php?controller=Auth&action=login&recover=1" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-400">Recover session</a>
              </div>
              <script>
                (() => {
                  const modal = document.getElementById('expiredSessionModal');
                  const loginAgainLink = document.getElementById('loginAgainLink');
                  const recoverLink = document.getElementById('recoverSessionLink');
                  const countdownText = document.getElementById('recoverCountdownText');
                  if (!modal || !recoverLink) return;

                  if (modal.dataset.timerInitialized === 'true') return;
                  modal.dataset.timerInitialized = 'true';

                  const timeoutMs = 5 * 60 * 1000;
                  const expiredText = 'RECOVER SESSION EXPIRED';
                  const storageKey = 'dpintranet-recover-session-timer';
                  const expiredStateKey = 'dpintranet-recover-session-expired';
                  const shouldAutoShow = modal.classList.contains('hidden') === false || <?= !empty($showExpiredSessionModal) || !empty($_SESSION['session_expired_message']) ? 'true' : 'false' ?>;

                  if (shouldAutoShow) {
                    modal.classList.remove('hidden');
                  }

                  let expired = sessionStorage.getItem(expiredStateKey) === '1';
                  let countdownTimer = null;
                  let deadline = parseInt(sessionStorage.getItem(storageKey) || '0', 10);

                  if (!deadline || deadline <= Date.now()) {
                    sessionStorage.removeItem(expiredStateKey);
                    expired = false;
                    deadline = Date.now() + timeoutMs;
                    sessionStorage.setItem(storageKey, String(deadline));
                  }

                  if (loginAgainLink) {
                    loginAgainLink.addEventListener('click', (event) => {
                      event.preventDefault();
                      sessionStorage.removeItem(storageKey);
                      sessionStorage.removeItem(expiredStateKey);
                      if (countdownTimer) {
                        window.clearInterval(countdownTimer);
                        countdownTimer = null;
                      }
                      modal.classList.add('hidden');
                      window.location.href = loginAgainLink.getAttribute('href');
                    });
                  }

                  const updateState = () => {
                    const remainingMs = Math.max(0, deadline - Date.now());
                    const remainingSeconds = Math.max(0, Math.ceil(remainingMs / 1000));

                    if (expired) {
                      recoverLink.textContent = expiredText;
                      recoverLink.classList.add('pointer-events-none', 'opacity-60', 'cursor-not-allowed');
                      recoverLink.setAttribute('aria-disabled', 'true');
                      recoverLink.removeAttribute('href');
                      if (countdownText) {
                        countdownText.textContent = 'Recover session expired';
                      }
                      return;
                    }

                    if (countdownText) {
                      countdownText.textContent = `Recover session available for ${String(remainingSeconds).padStart(2, '0')}s`;
                    }

                    recoverLink.textContent = 'Recover session';
                    recoverLink.classList.remove('pointer-events-none', 'opacity-60', 'cursor-not-allowed');
                    recoverLink.setAttribute('aria-disabled', 'false');
                    recoverLink.setAttribute('href', 'index.php?controller=Auth&action=login&recover=1');
                  };

                  const tick = () => {
                    if (expired) return;

                    if (Date.now() >= deadline) {
                      expired = true;
                      sessionStorage.setItem(expiredStateKey, '1');
                      updateState();
                      sessionStorage.removeItem(storageKey);
                      return;
                    }

                    updateState();
                  };

                  tick();
                  countdownTimer = window.setInterval(tick, 1000);
                })();
              </script>
            </div>
          </div>
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

        <div id="loginAlert" class="mt-5 hidden rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-600"></div>

        <form id="loginForm" method="POST" action="" class="mt-6 space-y-4">
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-600">Username</label>
            <input type="text" name="username" id="username" required
              placeholder="Enter username"
              class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 transition focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200"
            >
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-600">Password</label>
            <input type="password" name="password" id="password" required
              placeholder="Enter password"
              class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 transition focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200"
            >
          </div>

          <button id="loginSubmit" type="submit"
            class="h-11 w-full rounded-xl bg-slate-900 px-4 text-sm font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-400">
            Login
          </button>
        </form>

        <script>
          (function () {
            const form = document.getElementById('loginForm');
            const alertBox = document.getElementById('loginAlert');
            const submitButton = document.getElementById('loginSubmit');
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');
            let countdownTimer = null;
            let currentState = {
              success: false,
              message: '',
              remainingAttempts: null,
              isLocked: false,
              lockExpiresInSeconds: 0,
            };

            function clearCountdown() {
              if (countdownTimer) {
                clearInterval(countdownTimer);
                countdownTimer = null;
              }
            }

            function formatAttemptsMessage(remainingAttempts) {
              if (remainingAttempts === null || remainingAttempts === undefined || remainingAttempts <= 0) {
                return '';
              }

              const noun = remainingAttempts === 1 ? 'attempt' : 'attempts';
              return `${remainingAttempts} login ${noun} remaining before a temporary lockout.`;
            }

            function formatCountdown(seconds) {
              const safeSeconds = Math.max(0, Number(seconds || 0));
              const minutes = Math.floor(safeSeconds / 60);
              const remainderSeconds = safeSeconds % 60;
              return `${String(minutes).padStart(2, '0')}:${String(remainderSeconds).padStart(2, '0')}`;
            }

            function render(state) {
              currentState = { ...currentState, ...state };
              const message = currentState.message || '';
              const isLocked = Boolean(currentState.isLocked);
              const remainingAttempts = currentState.remainingAttempts;
              const lockSeconds = Math.max(0, Number(currentState.lockExpiresInSeconds || 0));

              if (currentState.success) {
                clearCountdown();
                alertBox.classList.add('hidden');
                alertBox.textContent = '';
                submitButton.disabled = true;
                submitButton.textContent = 'Signing in...';
                return;
              }

              if (isLocked && lockSeconds > 0) {
                clearCountdown();
                alertBox.classList.remove('hidden');
                alertBox.classList.remove('border-rose-200', 'bg-rose-50', 'text-rose-600');
                alertBox.classList.add('border-amber-200', 'bg-amber-50', 'text-amber-700');
                alertBox.textContent = `Login temporarily blocked. Please try again in ${lockSeconds} seconds.`;
                submitButton.disabled = true;
                submitButton.textContent = `Try Again in ${formatCountdown(lockSeconds)}`;
                let remaining = lockSeconds;
                countdownTimer = setInterval(() => {
                  remaining = Math.max(0, remaining - 1);
                  submitButton.textContent = `Try Again in ${formatCountdown(remaining)}`;
                  alertBox.textContent = `Login temporarily blocked. Please try again in ${remaining} seconds.`;

                  if (remaining <= 0) {
                    clearCountdown();
                    render({ success: false, message: '', remainingAttempts: null, isLocked: false, lockExpiresInSeconds: 0 });
                  }
                }, 1000);
                return;
              }

              if (message && !isLocked) {
                clearCountdown();
                const attemptsMessage = formatAttemptsMessage(remainingAttempts);
                const combinedMessage = attemptsMessage ? `${message}\n${attemptsMessage}` : message;
                alertBox.classList.remove('hidden');
                alertBox.classList.remove('border-amber-200', 'bg-amber-50', 'text-amber-700');
                alertBox.classList.add('border-rose-200', 'bg-rose-50', 'text-rose-600');
                alertBox.textContent = combinedMessage;
                submitButton.disabled = false;
                submitButton.textContent = 'Login';
                return;
              }

              clearCountdown();
              alertBox.classList.add('hidden');
              alertBox.textContent = '';
              submitButton.disabled = false;
              submitButton.textContent = 'Login';
            }

            form.addEventListener('submit', function (event) {
              event.preventDefault();
              const payload = new FormData(form);
              render({ success: true, message: '', remainingAttempts: null, isLocked: false, lockExpiresInSeconds: 0 });

              fetch(window.location.href, {
                method: 'POST',
                headers: {
                  'X-Requested-With': 'XMLHttpRequest',
                  'Accept': 'application/json'
                },
                body: payload
              })
                .then((response) => response.json())
                .then((data) => {
                  if (data && data.success) {
                    window.location.href = data.redirectUrl || window.location.href;
                    return;
                  }

                  render({
                    success: false,
                    message: data && data.message ? data.message : '',
                    remainingAttempts: data && data.remainingAttempts !== undefined ? data.remainingAttempts : null,
                    isLocked: Boolean(data && data.isLocked),
                    lockExpiresInSeconds: data && data.lockExpiresInSeconds !== undefined ? data.lockExpiresInSeconds : 0,
                  });
                })
                .catch(() => {
                  render({ success: false, message: 'Unable to sign in right now.', remainingAttempts: null, isLocked: false, lockExpiresInSeconds: 0 });
                });
            });

            usernameInput.addEventListener('input', () => {
              if (currentState.success) {
                render({ success: false, message: '', remainingAttempts: null, isLocked: false, lockExpiresInSeconds: 0 });
              }
            });

            passwordInput.addEventListener('input', () => {
              if (currentState.success) {
                render({ success: false, message: '', remainingAttempts: null, isLocked: false, lockExpiresInSeconds: 0 });
              }
            });
          })();
        </script>

        <div class="mt-4 text-sm text-slate-500">
          <a href="index.php?controller=Auth&action=forgotPassword" class="font-medium text-slate-600 transition hover:text-slate-900">Forgot password?</a>
        </div>

        <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4">
          <div class="space-y-3">
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


         <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4">
          <div class="space-y-3">
            <a href="index.php?controller=DigitalMonitoring&action=index"
              class="flex items-center justify-between gap-3 rounded-lg text-sm font-medium text-slate-700 transition hover:text-slate-900">
              <span>
                <span class="block">Digital Monitoring</span>
                <span class="mt-1 block text-xs font-normal text-slate-500">View current vehicles, rooms, and workforce activity.</span>
              </span>
              <span class="text-base leading-none">→</span>
            </a>
      </div>

          
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
