<?php
$message = $message ?? null;
$msgType = $msgType ?? 'info';
?>
<div class="min-h-screen bg-slate-50 px-4 py-8 sm:px-6 lg:px-8">
  <div class="mx-auto flex min-h-[calc(100vh-4rem)] max-w-md items-center justify-center">
    <div class="w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
      <div class="border-b border-slate-200 px-6 py-5 sm:px-8">
        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">DPINTRANET</p>
        <h1 class="mt-1 text-xl font-semibold text-slate-900">Reset password</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">Choose a new password for your account.</p>
      </div>

      <form method="POST" action="index.php?controller=Auth&action=resetPassword" class="space-y-4 p-6 sm:px-8 sm:py-7">
        <?php if (!empty($message)): ?>
          <div class="rounded-lg border <?= ($msgType === 'success') ? 'border-emerald-200 bg-emerald-50 text-emerald-600' : (($msgType === 'error') ? 'border-rose-200 bg-rose-50 text-rose-600' : 'border-slate-200 bg-slate-50 text-slate-600') ?> px-4 py-3 text-sm font-medium">
            <?= htmlspecialchars($message) ?>
          </div>
        <?php endif; ?>

        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700">New Password</label>
          <input type="password" name="new_password" required class="h-10 w-full rounded-lg border border-slate-200 px-3 text-sm text-slate-800 transition focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200">
        </div>

        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700">Confirm Password</label>
          <input type="password" name="confirm_password" required class="h-10 w-full rounded-lg border border-slate-200 px-3 text-sm text-slate-800 transition focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200">
        </div>

        <button type="submit" class="h-10 w-full rounded-lg bg-slate-900 text-sm font-medium text-white transition hover:bg-slate-800">Reset Password</button>
      </form>
    </div>
  </div>
</div>
