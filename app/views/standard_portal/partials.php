<?php
require_once __DIR__ . '/../../models/Notification.php';
require_once __DIR__ . '/../../models/correspondence.php';

function portalAvatar() {
    $avatar = $_SESSION['standard_user_avatar'] ?? null;
    return !empty($avatar)
        ? BASE_URL . 'uploads/standard_users/' . rawurlencode($avatar)
        : BASE_URL . 'uploads/standard_users/default.png';
}

function portalNotificationsForCurrentUser(): array {
    $userId = (int)($_SESSION['standard_user_id'] ?? 0);
    if ($userId <= 0) {
        return ['notifications' => [], 'unreadCount' => 0];
    }

    $notifModel = new NotificationModel();
    $notifications = $notifModel->getForUser($userId, 8);
    $unreadCount = 0;

    foreach ($notifications as $notification) {
        if (empty($notification['is_read']) && !empty($notification['url'])) {
            $unreadCount++;
        }
    }

    return ['notifications' => $notifications, 'unreadCount' => $unreadCount];
}

function buildPortalNotificationLink(array $notification): string {
    $notificationId = (int)($notification['id'] ?? 0);
    if ($notificationId > 0) {
        return 'index.php?controller=PortalNotifications&action=view&id=' . $notificationId;
    }

    return 'index.php?controller=PortalNotifications&action=view';
}

function notificationActionText(array $notification): string {
    $message = trim((string)($notification['message'] ?? ''));
    $message = preg_replace('/^(Your document has been|Your booking has been|You have a)\s+/i', '', $message);
    $clean = preg_replace('/\s+/',' ', $message);

    if (preg_match('/^(.*?)[:\.\-]\s*(.+)$/', $clean, $matches)) {
        $primary = trim($matches[1]);
    } elseif (preg_match('/^(.*?)\.\s+(.+)$/', $clean, $matches)) {
        $primary = trim($matches[1]);
    } else {
        $primary = trim($clean);
    }

    $lower = strtolower($primary);
    $map = [
        'finalized and circulated' => 'Finalized & Circulated',
        'ready for circulation' => 'Ready for Circulation',
        'document received' => 'Document Received',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'returned for revision' => 'Returned for Revision',
        'booking approved' => 'Booking Approved',
        'booking cancelled' => 'Booking Cancelled',
        'booking rejected' => 'Booking Rejected',
    ];
    foreach ($map as $needle => $label) {
        if (str_contains($lower, $needle)) {
            return $label;
        }
    }

    return ucfirst($primary ?: 'Notification');
}

function notificationContextText(array $notification): string {
    $message = trim((string)($notification['message'] ?? ''));
    $message = preg_replace('/^(Your document has been|Your booking has been|You have a)\s+/i', '', $message);
    $clean = preg_replace('/\s+/',' ', $message);

    if (preg_match('/^(.*?)•[\.:\-]\s*(.+)$/', $clean, $matches)) {
        return trim($matches[2]);
    }
    if (preg_match('/^(.*?)•\.\s+(.+)$/', $clean, $matches)) {
        return trim($matches[2]);
    }

    if (strlen($clean) > 60) {
        return substr($clean, 0, 60) . '...';
    }

    return $clean;
}

function notificationTimeLabel(string $createdAt): string {
    try {
        $source = new DateTimeImmutable($createdAt, new DateTimeZone('UTC'));
    } catch (Throwable $e) {
        return '';
    }

    $timestamp = $source->setTimezone(new DateTimeZone(date_default_timezone_get()));
    $now = new DateTimeImmutable('now', new DateTimeZone(date_default_timezone_get()));
    $diff = $now->getTimestamp() - $timestamp->getTimestamp();

    if ($diff < 60) {
        return 'Just now';
    }
    if ($diff < 3600) {
        return floor($diff / 60) . ' min ago';
    }
    if ($timestamp->format('Y-m-d') === $now->format('Y-m-d')) {
        return floor($diff / 3600) . ' hr ago';
    }
    if ($timestamp->format('Y-m-d') === $now->modify('-1 day')->format('Y-m-d')) {
        return 'Yesterday';
    }

    return $timestamp->format('M j');
}

function notificationGroupLabel(string $createdAt): string {
    $timestamp = strtotime($createdAt);
    if ($timestamp === false) {
        return 'Earlier';
    }

    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $date = date('Y-m-d', $timestamp);

    if ($date === $today) {
        return 'Today';
    }
    if ($date === $yesterday) {
        return 'Yesterday';
    }
    return 'Earlier';
}

function notificationModuleLabel(string $module): string {
    $map = [
        'correspondence' => 'Documents',
        'standard_portal' => 'Portal',
        'notifications' => 'System',
        'files' => 'System',
        'auth' => 'System',
        'default' => 'System',
    ];

    return $map[strtolower($module)] ?? $map['default'];
}

function notificationStatusLabel(array $notification): string {
    $eventKey = strtolower(trim((string)($notification['event_key'] ?? '')));
    $message = strtolower(trim((string)($notification['message'] ?? '')));

    if (strpos($eventKey, 'reopened') !== false || strpos($eventKey, 'reopen') !== false || strpos($eventKey, 'suspended') !== false) {
        return 'Reopened';
    }
    if (strpos($eventKey, 'done') !== false || strpos($eventKey, 'closed') !== false || strpos($eventKey, 'finalized') !== false || strpos($message, 'done') !== false || strpos($message, 'finalized') !== false) {
        return 'Completed';
    }
    if (strpos($eventKey, 'circulated') !== false || strpos($message, 'circulated') !== false || strpos($message, 'received') !== false || strpos($message, 'ready') !== false) {
        return 'Circulated';
    }

    return 'Workflow';
}

function notificationStatusBadgeText(array $notification): string {
    $eventKey = strtolower(trim((string)($notification['event_key'] ?? '')));
    $message = strtolower(trim((string)($notification['message'] ?? '')));
    $title = strtolower(trim((string)($notification['title'] ?? '')));
    $text = $eventKey . ' ' . $message . ' ' . $title;

    if (str_contains($text, 'received')) {
        return 'RECEIVED';
    }

    if (str_contains($text, 'circulated') || str_contains($text, 'finalized')) {
        return 'Finalized & Circulated';
    }

    if (str_contains($text, 'draft') || str_contains($text, 'ready for circulation')) {
        return 'Ready for Circulation';
    }

    return 'Update';
}

function notificationMainTitleText(array $notification): string {
    $entityId = (int)($notification['entity_id'] ?? 0);
    if ($entityId > 0) {
        try {
            $correspondenceModel = new CorrespondenceModel();
            $document = $correspondenceModel->getById($entityId);
            if ($document) {
                $title = trim((string)($document['title'] ?? ''));
                if ($title !== '') {
                    return $title;
                }
            }
        } catch (Throwable $e) {
            // Fall back below.
        }
    }

    $candidateKeys = ['title', 'document_title', 'name', 'subject'];
    foreach ($candidateKeys as $key) {
        $value = trim((string)($notification[$key] ?? ''));
        if ($value !== '') {
            return $value;
        }
    }

    return notificationActionText($notification);
}

function notificationSubtitleText(array $notification): string {
    $entityId = (int)($notification['entity_id'] ?? 0);
    if ($entityId > 0) {
        try {
            $correspondenceModel = new CorrespondenceModel();
            $document = $correspondenceModel->getById($entityId);
            if ($document) {
                $trackingId = trim((string)($document['tracking_id'] ?? ''));
                if ($trackingId !== '') {
                    return $trackingId;
                }
            }
        } catch (Throwable $e) {
            // Fall back below.
        }
    }

    $candidateKeys = ['tracking_id', 'document_id', 'document_no', 'reference_no', 'room_name', 'room_no', 'vehicle_name', 'vehicle_no', 'booking_ref', 'booking_number', 'code', 'identifier'];
    foreach ($candidateKeys as $key) {
        $value = trim((string)($notification[$key] ?? ''));
        if ($value !== '') {
            return $value;
        }
    }

    return '';
}

function notificationDetailText(array $notification): string {
    $candidateKeys = ['detail', 'additional_detail', 'location', 'department', 'contact', 'description', 'note'];
    foreach ($candidateKeys as $key) {
        $value = trim((string)($notification[$key] ?? ''));
        if ($value !== '') {
            return $value;
        }
    }

    return '';
}

function notificationProgressData(array $notification): ?array {
    $total = isset($notification['recipients_total']) ? (int)$notification['recipients_total'] : 0;
    $received = isset($notification['recipients_received']) ? (int)$notification['recipients_received'] : 0;

    if ($total <= 0) {
        return null;
    }

    $filled = max(0, min($total, $received));
    $bar = str_repeat('█', $filled) . str_repeat('░', max(0, $total - $filled));

    return [
        'received' => $received,
        'total' => $total,
        'bar' => $bar,
    ];
}

function notificationTimestampLabel(string $createdAt): string {
    $timestamp = strtotime($createdAt);
    if ($timestamp === false) {
        return '';
    }

    $now = time();
    $date = date('Y-m-d', $timestamp);
    $today = date('Y-m-d', $now);
    $yesterday = date('Y-m-d', strtotime('-1 day', $now));

    if ($date === $today) {
        return 'Today • ' . date('g:i A', $timestamp);
    }
    if ($date === $yesterday) {
        return 'Yesterday • ' . date('g:i A', $timestamp);
    }

    $sevenDaysAgo = strtotime('-6 days', $now);
    if ($timestamp >= $sevenDaysAgo) {
        return date('D • g:i A', $timestamp);
    }

    return date('M j, Y • g:i A', $timestamp);
}

function formatUnreadBadgeCount(int $count): string {
    if ($count >= 100) {
        return '99+';
    }
    if ($count >= 10) {
        return '9+';
    }
    return (string)$count;
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
    $notificationData = portalNotificationsForCurrentUser();
    $notifications = $notificationData['notifications'];
    $unreadCount = $notificationData['unreadCount'];
    $correspondenceModel = new CorrespondenceModel();
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
                        <a href="index.php?controller=StandardPortal&action=profileSettings" class="rounded-full border border-slate-200 bg-slate-50 px-3 py-2 hover:border-emerald-200 hover:bg-emerald-50 transition">Settings</a>
                    </nav>

                    <div class="relative">
                        <button id="portalNotifButton" class="relative inline-flex items-center justify-center h-10 w-10 rounded-full border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 transition focus:outline-none" type="button" aria-label="Notifications">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6z"/></svg>
                            <?php if ($unreadCount > 0): ?>
                                <span class="absolute -top-1 -right-1 inline-flex items-center justify-center rounded-full bg-sky-500 text-white text-[9px] font-semibold h-5 w-5"><?= htmlspecialchars(formatUnreadBadgeCount($unreadCount)) ?></span>
                            <?php endif; ?>
                        </button>

                        <div id="portalNotifMenu" class="absolute right-0 mt-2 hidden min-w-[360px] max-w-[420px] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div class="sticky top-0 z-10 border-b border-slate-200 bg-white px-4 py-3">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <div class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-700">Notifications</div>
                                        <div class="mt-1 text-[11px] font-medium text-slate-500 unread-count-label"><?= htmlspecialchars($unreadCount) ?> Unread</div>
                                    </div>
                                    <button type="button" id="portalMarkAllReadButton" class="text-xs font-medium text-slate-500 transition hover:text-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 rounded">
                                        Mark all as read
                                    </button>
                                </div>
                            </div>
                            <div class="max-h-[calc(100vh-18rem)] overflow-y-auto">
                                <?php if (empty($notifications)): ?>
                                    <div class="p-6 text-center">
                                        <p class="text-sm font-semibold text-slate-900">You're all caught up</p>
                                        <p class="mt-2 text-xs text-slate-500">No unread notifications.</p>
                                    </div>
                                <?php else:
                                    $groups = ['Today' => [], 'Yesterday' => [], 'Earlier' => []];
                                    foreach ($notifications as $notification) {
                                        $groups[notificationGroupLabel($notification['created_at'])][] = $notification;
                                    }
                                ?>
                                    <?php foreach ($groups as $section => $items): ?>
                                        <?php if (empty($items)) continue; ?>
                                        <div class="px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500 bg-slate-50"><?= htmlspecialchars($section) ?></div>
                                        <div class="divide-y divide-slate-100">
                                            <?php foreach ($items as $notification): ?>
                                                <?php
                                                    $action = notificationActionText($notification);
                                                    $time = notificationTimeLabel($notification['created_at']);
                                                    $moduleLabel = notificationModuleLabel((string)($notification['module'] ?? ''));
                                                    $isUnread = empty($notification['is_read']);
                                                    $mainTitle = notificationMainTitleText($notification);
                                                    $subtitle = notificationSubtitleText($notification);
                                                    $detail = notificationDetailText($notification);
                                                    $progress = notificationProgressData($notification);
                                                ?>

                                                <a href="<?= htmlspecialchars(buildPortalNotificationLink($notification)) ?>" class="notification-item group flex items-start gap-3  border border-slate-200 bg-white px-4 py-4 hover:bg-slate-50 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                                                    <div class="min-w-0 flex-1">
                                                        <div class="flex items-center justify-between gap-3">
                                                            <div class="flex items-center gap-2 min-w-0">
                                                                <?php if ($isUnread): ?>
                                                                    <span class="inline-flex h-2 w-2 rounded-full bg-sky-500"></span>
                                                                <?php endif; ?>
                                                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                                                    <?= htmlspecialchars(notificationStatusBadgeText($notification)) ?>
                                                                </span>
                                                            </div>
                                                            <span role="module-label" class="shrink-0 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[9px] font-semibold uppercase tracking-[0.12em] text-slate-600">
                                                                <?= htmlspecialchars($moduleLabel) ?>
                                                            </span>
                                                        </div>

                                                        <div class="mt-2 flex flex-col gap-2">
                                                            <p class="truncate text-sm <?= $isUnread ? 'font-semibold text-slate-900' : 'font-normal text-slate-500' ?>">
                                                                <?= htmlspecialchars($mainTitle) ?>
                                                            </p>
                                                            <?php if ($subtitle !== ''): ?>
                                                                <p class="text-xs text-slate-500">
                                                                    <?= htmlspecialchars($subtitle) ?>
                                                                </p>
                                                            <?php endif; ?>
                                                            <?php if ($detail !== ''): ?>
                                                                <p class="text-xs text-slate-600">
                                                                    <?= htmlspecialchars($detail) ?>
                                                                </p>
                                                            <?php endif; ?>
                                                            <?php if ($progress !== null): ?>
                                                                <div class="flex flex-col gap-1">
                                                                    <div class="flex items-center justify-between gap-2">
                                                                        <span class="uppercase tracking-wide text-[10px] font-semibold text-slate-500">Recipients</span>
                                                                        <span class="text-[11px] font-semibold text-slate-700"><?= htmlspecialchars($progress['received'] . ' / ' . $progress['total']) ?></span>
                                                                    </div>
                                                                    <div class="text-[11px] text-slate-400">
                                                                        <span class="font-semibold text-slate-600"><?= htmlspecialchars($progress['bar']) ?></span>
                                                                    </div>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>

                                                        <p class="mt-3 text-xs text-slate-400">
                                                            <?= htmlspecialchars($time) ?>
                                                        </p>
                                                    </div>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

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
        <script>
            const portalNotifButton = document.getElementById('portalNotifButton');
            const portalNotifMenu = document.getElementById('portalNotifMenu');
            const portalMarkAllReadButton = document.getElementById('portalMarkAllReadButton');
            const portalFilterTabs = portalNotifMenu ? portalNotifMenu.querySelectorAll('.notification-filter-tab') : [];
            const portalNotificationItems = portalNotifMenu ? portalNotifMenu.querySelectorAll('.notification-item') : [];

            if (portalNotifButton && portalNotifMenu) {
                portalNotifButton.addEventListener('click', (event) => {
                    event.stopPropagation();
                    portalNotifMenu.classList.toggle('hidden');
                });

                portalNotifMenu.addEventListener('click', (event) => {
                    event.stopPropagation();
                });

                document.addEventListener('click', () => {
                    portalNotifMenu.classList.add('hidden');
                });
            }

            if (portalMarkAllReadButton) {
                portalMarkAllReadButton.addEventListener('click', async (e) => {
                    e.preventDefault();
                    e.stopPropagation();

                    try {
                        const response = await fetch('index.php?controller=PortalNotifications&action=markAllRead', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const payload = await response.json();
                        if (payload.success) {
                            const badge = portalNotifButton.querySelector('span');
                            if (badge) badge.remove();
                            const unreadLabel = portalNotifMenu.querySelector('.unread-count-label');
                            if (unreadLabel) {
                                unreadLabel.textContent = '0 Unread';
                            }
                            portalNotificationItems.forEach((item) => {
                                const unreadDot = item.querySelector('.inline-flex.h-2.w-2.rounded-full.bg-sky-500');
                                if (unreadDot) unreadDot.remove();
                                const title = item.querySelector('p.truncate.text-sm');
                                if (title) {
                                    title.classList.remove('font-semibold', 'text-slate-900');
                                    title.classList.add('font-normal', 'text-slate-500');
                                }
                            });
                        }
                    } catch (error) {
                        console.error('Mark all read failed', error);
                    }
                });
            }
        </script>
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
