<?php
$message = $message ?? null;
$msgType = $msgType ?? 'info';
$remainingAttempts = $remainingAttempts ?? 5;
?>
<div class="min-h-screen bg-slate-50 px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto flex min-h-[calc(100vh-4rem)] max-w-md items-center justify-center">
        <div class="w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5 sm:px-8">
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Standard User Portal</p>
                <h1 class="mt-1 text-xl font-semibold text-slate-900">Verify code</h1>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Enter the 6-digit verification code sent to your registered email address.
                </p>
            </div>

            <form method="POST" action="index.php?controller=StandardPortal&action=verifyOtp" class="space-y-4 p-6 sm:px-8 sm:py-7">
                <?php if (!empty($message)): ?>
                    <div class="rounded-lg border <?= ($msgType === 'success') ? 'border-emerald-200 bg-emerald-50 text-emerald-600' : (($msgType === 'error') ? 'border-rose-200 bg-rose-50 text-rose-600' : 'border-slate-200 bg-slate-50 text-slate-600') ?> px-4 py-3 text-sm font-medium">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-700">Verification Code</label>
                    <input type="text" name="otp" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required class="h-11 w-full rounded-lg border border-slate-200 px-3 text-center text-base tracking-[0.3em] text-slate-800 transition focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200">
                </div>

                <div class="flex items-center justify-between text-sm text-slate-500">
                    <span>Remaining attempts: <?= (int) $remainingAttempts ?></span>
                    <span id="resendTimer" class="font-medium text-slate-600">Resend available in 60s</span>
                </div>

                <a id="resendLink" href="index.php?controller=StandardPortal&action=resendOtp" class="inline-flex text-sm font-medium text-slate-600 transition hover:text-slate-900 disabled:pointer-events-none disabled:opacity-50">Resend code</a>

                <button type="submit" class="h-10 w-full rounded-lg bg-slate-900 text-sm font-medium text-white transition hover:bg-slate-800">
                    Verify
                </button>
            </form>

            <div class="border-t border-slate-200 px-6 py-4 text-[11px] text-slate-500 sm:px-8">
                <div class="flex items-center justify-between gap-3">
                    <span>Standard User Portal</span>
                    <span>System version 1.0</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
  (function () {
    const timerEl = document.getElementById('resendTimer');
    const linkEl = document.getElementById('resendLink');
    if (!timerEl || !linkEl) return;

    let secondsLeft = 60;
    const updateTimer = () => {
      if (secondsLeft <= 0) {
        timerEl.textContent = 'Ready to resend';
        linkEl.classList.remove('pointer-events-none', 'opacity-50');
        return;
      }

      timerEl.textContent = `Resend available in ${secondsLeft}s`;
      linkEl.classList.add('pointer-events-none', 'opacity-50');
      secondsLeft -= 1;
      window.setTimeout(updateTimer, 1000);
    };

    updateTimer();
  })();
</script>
