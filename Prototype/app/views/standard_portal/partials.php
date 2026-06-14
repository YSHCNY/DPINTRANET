<?php
function portalAvatar() {
    $avatar = $_SESSION['standard_user_avatar'] ?? null;
    return !empty($avatar)
        ? '../app/uploads/standard_users/' . htmlspecialchars($avatar)
        : '../app/assets/profiles/default.png';
}

function portalFlash() {
    if (!isset($_SESSION['portal_message'])) {
        return;
    }

    $class = ($_SESSION['portal_msg_type'] ?? '') === 'success'
        ? 'bg-green-50 border-green-100 text-green-700'
        : 'bg-red-50 border-red-100 text-red-700';

    echo '<div class="mb-5 rounded-lg border px-4 py-3 text-sm font-medium ' . $class . '">'
        . htmlspecialchars($_SESSION['portal_message']) .
        '</div>';

    unset($_SESSION['portal_message'], $_SESSION['portal_msg_type']);
}

function portalHeader($title) {
    $name = $_SESSION['standard_user_name'] ?? 'Standard User';
    $position = $_SESSION['standard_user_position'] ?? '';
    ?>
    <div class="min-h-screen bg-gray-50">
        <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
            <div class="max-w-7xl mx-auto px-4 py-3 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase text-blue-600">Standard User Portal</p>
                    <h1 class="text-xl font-semibold text-gray-900"><?= htmlspecialchars($title) ?></h1>
                </div>

                <div class="flex items-center justify-between md:justify-end gap-4">
                    <nav class="flex items-center gap-2 text-sm">
                        <a href="index.php?controller=StandardPortal&action=dashboard" class="px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">Dashboard</a>
                        <a href="index.php?controller=StandardPortal&action=inbox" class="px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">Correspondence</a>
                    </nav>
                    <div class="flex items-center gap-3 border-l border-gray-200 pl-4">
                        <img src="<?= portalAvatar() ?>" class="w-9 h-9 rounded-full object-cover border border-gray-200" alt="Avatar">
                        <div class="hidden sm:block leading-tight">
                            <p class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($name) ?></p>
                            <p class="text-xs text-gray-500"><?= htmlspecialchars($position) ?></p>
                        </div>
                        <a href="index.php?controller=StandardPortal&action=logout" class="text-sm font-semibold text-red-600 hover:text-red-700">Logout</a>
                    </div>
                </div>
            </div>
        </header>
        <main class="max-w-7xl mx-auto px-4 py-6">
    <?php
}

function portalFooter() {
    echo '</main></div>';
}

function portalStatusBadge($status) {
    $status = $status ?: 'Pending';
    $class = $status === 'Received'
        ? 'bg-green-50 text-green-700 border-green-100'
        : 'bg-amber-50 text-amber-700 border-amber-100';

    return '<span class="inline-flex rounded-md border px-2.5 py-1 text-xs font-semibold ' . $class . '">' . htmlspecialchars($status) . '</span>';
}
?>
