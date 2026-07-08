<div class="min-h-screen bg-slate-50 px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto flex min-h-[calc(100vh-4rem)] max-w-lg items-center justify-center">
        <div class="w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5 sm:px-8">
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Standard User Portal</p>
                <h1 class="mt-1 text-xl font-semibold text-slate-900">Sign in</h1>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Access the correspondence portal to receive assigned items, participate in document discussion threads, and review your correspondence history.
                </p>

                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-medium text-slate-600">
                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                        Assigned Correspondence
                    </span>
                    <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-medium text-slate-600">
                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                        Document Threads
                    </span>
                    <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-medium text-slate-600">
                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                        History
                    </span>
                </div>
            </div>

            <form method="POST" action="index.php?controller=StandardPortal&action=login" class="space-y-4 p-6 sm:px-8 sm:py-7">
                <?php if (!empty($flashMessage)): ?>
                    <div class="rounded-lg border <?= ($flashType ?? 'info') === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-600' : (($flashType ?? 'info') === 'error' ? 'border-rose-200 bg-rose-50 text-rose-600' : 'border-slate-200 bg-slate-50 text-slate-600') ?> px-4 py-3 text-sm font-medium">
                        <?= htmlspecialchars($flashMessage) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-600">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-700">Username</label>
                    <input type="text" name="username" required autofocus
                           class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm text-slate-800 transition focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200">
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-700">Password</label>
                    <input type="password" name="password" required
                           class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm text-slate-800 transition focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    <a href="index.php?controller=StandardPortal&action=forgotPassword" class="mt-2 inline-flex text-sm text-slate-500 transition hover:text-slate-700">Forgot password?</a>
                </div>

                <button type="submit" class="h-11 w-full rounded-xl bg-slate-900 text-sm font-medium text-white transition hover:bg-slate-800">
                    Sign in
                </button>

                <p class="text-center text-[11px] leading-5 text-slate-500">
                    Only authorized users can access assigned correspondence and related document activity.
                </p>
            </form>

            <div class="border-t border-slate-200 px-6 py-4 text-[11px] text-slate-500 sm:px-8">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <span>Standard User Portal</span>
                    <span>System version 1.0</span>
                </div>
                <p class="mt-1">© 2026 DPINTRANET</p>
            </div>
        </div>
    </div>
</div>
