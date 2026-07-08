<?php
$flashMessage = $_SESSION['portal_message'] ?? null;
$flashType = $_SESSION['portal_msg_type'] ?? 'info';
unset($_SESSION['portal_message'], $_SESSION['portal_msg_type']);
?>
<div class="min-h-screen bg-slate-50 px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto flex min-h-[calc(100vh-4rem)] max-w-md items-center justify-center">
        <div class="w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5 sm:px-8">
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Standard User Portal</p>
                <h1 class="mt-1 text-xl font-semibold text-slate-900">Forgot password</h1>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Enter your username or employee ID and the registered email address to receive a verification code.
                </p>
            </div>

            <form method="POST" action="index.php?controller=StandardPortal&action=forgotPassword" class="space-y-4 p-6 sm:px-8 sm:py-7">
                <?php if (!empty($flashMessage)): ?>
                    <div class="rounded-lg border <?= ($flashType === 'success') ? 'border-emerald-200 bg-emerald-50 text-emerald-600' : (($flashType === 'error') ? 'border-rose-200 bg-rose-50 text-rose-600' : 'border-slate-200 bg-slate-50 text-slate-600') ?> px-4 py-3 text-sm font-medium">
                        <?= htmlspecialchars($flashMessage) ?>
                    </div>
                <?php endif; ?>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-700">Username or Employee ID</label>
                    <input type="text" name="identifier" required class="h-10 w-full rounded-lg border border-slate-200 px-3 text-sm text-slate-800 transition focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200">
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-700">Registered Email</label>
                    <input type="email" name="email" required class="h-10 w-full rounded-lg border border-slate-200 px-3 text-sm text-slate-800 transition focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200">
                </div>

                <button type="submit" class="h-10 w-full rounded-lg bg-slate-900 text-sm font-medium text-white transition hover:bg-slate-800">
                    Send Verification Code
                </button>

                <a href="index.php?controller=StandardPortal&action=login" class="inline-flex text-sm text-slate-500 transition hover:text-slate-700">
                    ← Back to Login
                </a>
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
