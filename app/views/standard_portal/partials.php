<?php
function portalAvatar() {
    $avatar = $_SESSION['standard_user_avatar'] ?? null;
    return !empty($avatar)
        ? BASE_URL . 'uploads/standard_users/' . rawurlencode($avatar)
        : BASE_URL . 'uploads/standard_users/default.png';
}

function portalFlash() {
    if (!isset($_SESSION['portal_message'])) {
        return;
    }

    $class = ($_SESSION['portal_msg_type'] ?? '') === 'success'
        ? 'bg-emerald-100 border-emerald-200 text-emerald-800'
        : 'bg-rose-100 border-rose-200 text-rose-800';

    echo '<div class="mb-4 rounded-2xl border px-3 py-2 text-sm font-medium ' . $class . '">'
        . htmlspecialchars($_SESSION['portal_message']) .
        '</div>';

    unset($_SESSION['portal_message'], $_SESSION['portal_msg_type']);
}

function portalHeader($title) {
    $name = $_SESSION['standard_user_name'] ?? 'Standard User';
    $position = $_SESSION['standard_user_position'] ?? '';
    ?>
    <div class="min-h-screen bg-slate-50 text-slate-900">
        <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
            <div class="max-w-6xl mx-auto px-4 py-3 grid gap-3 md:grid-cols-[1fr_auto] items-center">
                <div class="space-y-1">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-emerald-700">Reciever user Portal</p>
                    <h1 class="text-xl font-semibold text-slate-900"><?= htmlspecialchars($title) ?></h1>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                    <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-700">
                        <a href="index.php?controller=StandardPortal&action=dashboard" class="rounded-full border border-slate-200 bg-slate-50 px-3 py-2 hover:border-emerald-200 hover:bg-emerald-50 transition">Dashboard</a>
                        <a href="index.php?controller=StandardPortal&action=inbox" class="rounded-full border border-slate-200 bg-slate-50 px-3 py-2 hover:border-emerald-200 hover:bg-emerald-50 transition">Correspondence</a>
                    </nav>

                    <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2">
                        <img src="<?= portalAvatar() ?>" class="w-9 h-9 rounded-full object-cover border border-slate-200" alt="Avatar">
                        <div class="min-w-0 hidden sm:block leading-tight">
                            <p class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($name) ?></p>
                            <p class="text-[11px] text-slate-500"><?= htmlspecialchars($position) ?></p>
                        </div>
                        <a href="index.php?controller=StandardPortal&action=logout" class="text-xs font-semibold text-rose-600 hover:text-rose-700">Logout</a>
                    </div>
                </div>
            </div>
        </header>
        <main class="max-w-6xl mx-auto px-4 py-4">
    <?php
}

function portalFooter() {
    echo '</main></div>';
}

function portalStatusBadge($status) {
    $normalized = strtolower(trim((string)$status));
    $label = $normalized !== '' ? ucfirst($normalized) : 'Pending';

    switch ($normalized) {
        case 'done':
        case 'received':
            $class = 'border-emerald-200 bg-emerald-100 text-emerald-700';
            break;
        case 'suspended':
            $class = 'border-rose-200 bg-rose-100 text-rose-700';
            break;
        case 'draft':
            $class = 'border-sky-200 bg-sky-100 text-sky-700';
            break;
        case 'inprogress':
            $class = 'border-slate-200 bg-slate-100 text-slate-700';
            break;
        case 'pending':
            $class = 'border-amber-200 bg-amber-100 text-amber-700';
            break;
        default:
            $class = 'border-amber-200 bg-amber-100 text-amber-700';
            break;
    }

    return '<span class="inline-flex rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] ' . $class . '">' . htmlspecialchars($label) . '</span>';
}
?>
