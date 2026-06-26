<div class="min-h-screen bg-gray-50 flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200">
                <p class="text-xs font-semibold uppercase text-blue-600">Standard User Portal</p>
                <h1 class="text-2xl font-semibold text-gray-900 mt-1">Sign in</h1>
                <p class="text-sm text-gray-500 mt-1">Access assigned correspondence and receive documents.</p>
            </div>

            <form method="POST" action="index.php?controller=StandardPortal&action=login" class="p-6 space-y-4">
                <?php if (!empty($error)): ?>
                    <div class="rounded-lg border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Username</label>
                    <input type="text" name="username" required autofocus
                           class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Password</label>
                    <input type="password" name="password" required
                           class="w-full h-10 border border-gray-300 rounded-lg px-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>

                <button type="submit" class="w-full h-10 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">
                    Sign in
                </button>
            </form>
        </div>
    </div>
</div>
