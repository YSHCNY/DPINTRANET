<?php
$currentController = $_GET['controller'] ?? '';
$currentAction = $_GET['action'] ?? '';
$currentLevel = (int)($_SESSION['user_level'] ?? 3);
$roleLabels = [
  0 => 'Super Admin',
  1 => 'Admin',
  4 => 'PROJECT MANAGER (PM)',
  5 => 'DEPUTY PROJECT MANAGER (DPM)',
  2 => 'Encoder',
  6 => 'User / GRP Head',
  3 => 'Viewer',
];
// Ensure a display role variable exists to avoid undefined variable warnings
$displayRole = $displayRole ?? ($roleLabels[$currentLevel] ?? 'Viewer');
$avatarFile = $_SESSION['profile_picture'] ?? 'default.png';
$avatarSrc = BASE_URL . 'uploads/assets/profiles/' . $avatarFile;
$fullName = trim(($_SESSION['firstName'] ?? '') . ' ' . ($_SESSION['lastName'] ?? '')) ?: 'Guest';
$position = $_SESSION['position'] ?? 'User';

// Notifications
$unreadCount = 0;
$notifications = [];
  if (!empty($_SESSION['id'])) {
  require_once '../app/models/Notification.php';
  require_once '../app/models/correspondence.php';
  $notifModel = new NotificationModel();
  $correspondenceModel = new CorrespondenceModel();
  $rawNotifications = $notifModel->getForUser((int)$_SESSION['id'], 8);

  // Exclude portal-only notifications from the admin dropdown
  $notifications = [];
  foreach ($rawNotifications as $n) {
    $module = strtolower(trim((string)($n['module'] ?? '')));
    if ($module === 'standard_portal') continue;
    $notifications[] = $n;
  }

  $unreadCount = 0;
  foreach ($notifications as $n) {
    if (empty($n['is_read']) && !empty($n['url'])) {
      $unreadCount++;
    }
  }
}

function buildNotificationLink(array $notification): string {
  $notificationId = (int)($notification['id'] ?? 0);
  if ($notificationId > 0) {
    return 'index.php?controller=AdminNotifications&action=view&id=' . $notificationId;
  }

  return 'index.php?controller=AdminNotifications&action=view';
}

function notificationActionText(array $notification): string {
  $message = trim((string)($notification['message'] ?? ''));
  $message = preg_replace('/^(Your document has been|Your booking has been|You have a)\s+/i', '', $message);
  $clean = preg_replace('/\s+/',' ', $message);

  if (preg_match('/^(.*?)[:\.\-]\s*(.+)$/', $clean, $matches)) {
    $primary = trim($matches[1]);
    $secondary = trim($matches[2]);
  } elseif (preg_match('/^(.*?)\.\s+(.+)$/', $clean, $matches)) {
    $primary = trim($matches[1]);
    $secondary = trim($matches[2]);
  } else {
    $primary = trim($clean);
    $secondary = '';
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

  if (preg_match('/^(.*?)[\.:\-]\s*(.+)$/', $clean, $matches)) {
    return trim($matches[2]);
  }
  if (preg_match('/^(.*?)\.\s+(.+)$/', $clean, $matches)) {
    return trim($matches[2]);
  }

  if (strlen($clean) > 60) {
    return substr($clean, 0, 60) . '...';
  }

  return $clean;
}

function notificationTimeLabel(string $createdAt): string {
  $timestamp = strtotime($createdAt);
  if ($timestamp === false) {
    return '';
  }

  $now = time();
  $diff = $now - $timestamp;

  if ($diff < 60) {
    return 'Just now';
  }
  if ($diff < 3600) {
    return floor($diff / 60) . ' min ago';
  }
  if (date('Y-m-d', $timestamp) === date('Y-m-d', $now)) {
    return floor($diff / 3600) . ' hr ago';
  }
  if (date('Y-m-d', $timestamp) === date('Y-m-d', strtotime('-1 day', $now))) {
    return 'Yesterday';
  }

  return date('M j', $timestamp);
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
    'car_bookings' => 'Fleet',
    'room_bookings' => 'Meeting Rooms',
    'files' => 'System',
    'notifications' => 'System',
    'auth' => 'System',
    'default' => 'System',
  ];

  return $map[strtolower($module)] ?? $map['default'];
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

function notificationAccentClass(string $message): string {
  $text = strtolower($message);
  if (str_contains($text, 'approved')) {
    return 'bg-emerald-500';
  }
  if (str_contains($text, 'rejected') || str_contains($text, 'cancelled')) {
    return 'bg-rose-500';
  }
  if (str_contains($text, 'returned') || str_contains($text, 'revision')) {
    return 'bg-amber-500';
  }
  if (str_contains($text, 'received') || str_contains($text, 'finalized') || str_contains($text, 'ready')) {
    return 'bg-sky-500';
  }
  return 'bg-slate-400';
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

  $fallback = trim((string)($notification['context'] ?? ''));
  if ($fallback !== '') {
    return $fallback;
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
  $module = strtolower(trim((string)($notification['module'] ?? '')));
  $entityId = (int)($notification['entity_id'] ?? 0);

  $total = isset($notification['recipients_total']) ? (int)$notification['recipients_total'] : 0;
  $received = isset($notification['recipients_received']) ? (int)$notification['recipients_received'] : 0;

  if ($total > 0) {
    return [
      'received' => $received,
      'total' => $total,
      'bar' => '',
    ];
  }

  if ($module === 'correspondence' && $entityId > 0) {
    try {
      $correspondenceModel = new CorrespondenceModel();
      $progress = $correspondenceModel->getRecipientProgressForDocument($entityId);
      if ($progress !== null) {
        return $progress;
      }
    } catch (Throwable $e) {
      // Fall back to no progress data.
    }
  }

  return null;
}

if ($currentController == 'Auth' && $currentAction == 'dashboard') {
  $pageTitle = 'Dashboard';
} elseif ($currentController == 'Files' && $currentAction == 'files') {
  $pageTitle = 'Files Management';
} elseif ($currentController == 'Auth' && $currentAction == 'users') {
  $pageTitle = 'Core Users';
} elseif ($currentController == 'Auth' && $currentAction == 'profile') {
  $pageTitle = 'Profile & Security';
} elseif ($currentController == 'Syslogs' && $currentAction == 'syslogs') {
  $pageTitle = 'System Logs';
} elseif ($currentController == 'correspondence' && $currentAction == 'correspondence') {
  $pageTitle = 'Correspondence Management';
} elseif ($currentController == 'StandardUsers' && $currentAction == 'index') {
  $pageTitle = 'Standard Users';
} else {
  $pageTitle = 'FMS Portal';
}
?>

<nav class="sticky top-0 z-40 flex items-center justify-between bg-emerald-700 px-3 py-2 border-b border-emerald-600">
  <style>
    /* 8-grid responsive header */
    .header-title { max-width: calc(100vw - 100px); }
    @media (max-width: 768px) {
      .header-title { max-width: calc(100vw - 140px); }
      .header-user-info { display: none; }
    }
  </style>

  <div class="flex min-w-0 items-center gap-2">
    <button id="sidebarToggle" class="md:hidden inline-flex items-center justify-center h-8 w-8 rounded-lg hover:bg-emerald-600 text-white focus:outline-none transition" type="button">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>
    </button>
    <span class="header-title truncate text-sm font-semibold text-white"><?= htmlspecialchars($pageTitle) ?></span>
  </div>

  <div class="flex items-center gap-2">
    <!-- Notifications -->
    <div class="relative">
      <button id="notifButton" class="relative inline-flex items-center justify-center h-8 w-8 rounded-lg hover:bg-emerald-600 text-white focus:outline-none transition" type="button">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6z"/></svg>
        <?php if ($unreadCount > 0): ?>
          <span class="absolute -top-1 -right-1 inline-flex items-center justify-center rounded-full bg-sky-500 text-white text-[9px] font-semibold h-5 w-5"><?= htmlspecialchars(formatUnreadBadgeCount($unreadCount)) ?></span>
        <?php endif; ?>
      </button>

      <div id="notifMenu" class="absolute right-0 mt-2 hidden min-w-[400px] max-w-[420px] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="sticky top-0 z-10 border-b border-slate-200 bg-white px-4 py-3">
          <div class="flex items-center justify-between gap-3">
            <div>
              <div class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-700">Notifications</div>
              <div class="mt-1 text-[11px] font-medium text-slate-500 unread-count-label"><?= htmlspecialchars($unreadCount) ?> Unread</div>
            </div>
            <button type="button" id="markAllReadButton" class="text-xs font-medium text-slate-500 transition hover:text-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 rounded">
              Mark all as read
            </button>
          </div>
          <div class="mt-3 flex gap-2 overflow-x-auto pb-2">
            <?php $filters = ['all' => 'All', 'unread' => 'Unread', 'documents' => 'Documents', 'fleet' => 'Fleet', 'rooms' => 'Meeting Rooms', 'system' => 'System']; ?>
            <?php foreach ($filters as $key => $label): ?>
              <button type="button" data-filter="<?= htmlspecialchars($key) ?>" class="notification-filter-tab whitespace-nowrap rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] font-medium text-slate-600 transition hover:border-slate-300 hover:text-slate-900"><?= htmlspecialchars($label) ?></button>
            <?php endforeach; ?>
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
            foreach ($notifications as $n) {
              $groups[notificationGroupLabel($n['created_at'])][] = $n;
            }
          ?>
            <?php foreach ($groups as $section => $items): ?>
              <?php if (empty($items)) continue; ?>
              <div class="px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500 bg-slate-50"><?= htmlspecialchars($section) ?></div>
              <div class="divide-y divide-slate-100">
                <?php foreach ($items as $n): ?>
                  <?php $action = notificationActionText($n); ?>
                  <?php $time = notificationTimeLabel($n['created_at']); ?>
                  <?php $moduleLabel = notificationModuleLabel((string)($n['module'] ?? '')); ?>
                  <?php $isUnread = empty($n['is_read']); ?>
                  <?php $mainTitle = notificationMainTitleText($n); ?>
                  <?php $subtitle = notificationSubtitleText($n); ?>
                  <?php $detail = notificationDetailText($n); ?>
                  <?php $progress = notificationProgressData($n); ?>
                  <a href="<?= htmlspecialchars(buildNotificationLink($n)) ?>" class="notification-item group flex items-start gap-3  border border-slate-200 bg-white px-4 py-4 hover:bg-slate-50 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                    <div class="min-w-0 flex-1">
                      <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2 min-w-0">
                          <?php if ($isUnread): ?>
                            <span class="inline-flex h-2 w-2 rounded-full bg-sky-500"></span>
                          <?php endif; ?>
                          <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                            <?= htmlspecialchars(notificationStatusBadgeText($n)) ?>
                          </span>
                        </div>
                        <span role="module-label" class=" rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[9px] font-semibold uppercase tracking-[0.12em] text-slate-600"><?= htmlspecialchars($moduleLabel) ?></span>
                      </div>

                      <div class="mt-2 flex flex-col gap-1">
                        <p class="truncate text-sm <?= $isUnread ? 'font-semibold text-slate-900' : 'font-normal text-slate-500' ?>"><?= htmlspecialchars($mainTitle) ?></p>
                        <?php if ($subtitle !== ''): ?>
                          <p class="text-xs text-slate-500"><?= htmlspecialchars($subtitle) ?></p>
                        <?php endif; ?>
                        <?php if ($detail !== ''): ?>
                          <p class="text-xs text-slate-600"><?= htmlspecialchars($detail) ?></p>
                        <?php endif; ?>
                        <?php if ($progress !== null): ?>
                          <div class="flex flex-col gap-1">
                            <?php if ((int)$progress['total'] === 1): ?>
                              <p class="text-xs font-medium text-emerald-600">Recipient acknowledged</p>
                            <?php else: ?>
                              <p class="text-xs font-medium text-slate-600">
                                <?= htmlspecialchars($progress['received'] . ' of ' . $progress['total'] . ' recipients acknowledged') ?>
                              </p>
                              <div class="h-1 w-full overflow-hidden rounded-full bg-slate-200">
                                <div class="h-full rounded-full bg-emerald-500" style="width: <?= htmlspecialchars((string)round((($progress['received'] / max(1, (int)$progress['total'])) * 100), 2)) ?>%"></div>
                              </div>
                            <?php endif; ?>
                          </div>
                        <?php endif; ?>
                      </div>

                      <div class="mt-2 flex items-center justify-between gap-2">
                        <p class="text-xs text-slate-400"><?= htmlspecialchars($time) ?></p>
                        <!-- <span role="module-label" class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[9px] font-semibold uppercase tracking-[0.12em] text-slate-600"><?= htmlspecialchars($moduleLabel) ?></span> -->
                      </div>
                    </div>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Profile Menu -->
    <div class="relative">
      <button id="profileMenuButton" type="button"
        class="flex items-center gap-2 rounded-lg hover:bg-emerald-600 text-white px-2 py-1 text-left transition focus:outline-none">
        
        <img src="<?=BASE_URL ?>uploads/profile/<?= $avatarFile ?>"

             class="h-8 w-8 rounded-full object-cover ring-2 ring-white/30"
             alt="User avatar">
        <div class="header-user-info hidden pr-1 sm:block min-w-0">
          <p class="text-sm font-medium leading-4 text-white truncate"><?= htmlspecialchars($fullName) ?></p>
          <p class="text-[10px] uppercase tracking-wide text-emerald-100"><?= htmlspecialchars($roleLabels[$currentLevel] ?? 'Viewer') ?></p>
        </div>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-200" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
      </button>

      <div id="profileMenu" class="absolute right-0 mt-2 hidden w-72 overflow-hidden rounded-lg bg-white shadow-xl">
        <!-- Profile Header -->
        <div class="px-3 py-2 bg-emerald-700 text-white">
          <div class="flex items-center gap-2">
             <img src="<?=BASE_URL ?>uploads/profile/<?= htmlspecialchars($avatarFile) ?>" class="h-10 w-10 rounded-lg object-cover ring-2 ring-white/30" alt="User avatar">
            <div class="min-w-0 flex-1">
              <p class="text-sm font-semibold text-white truncate"><?= htmlspecialchars($fullName) ?></p>
              <p class="text-xs text-emerald-100 truncate"><?= htmlspecialchars($position) ?></p>
            </div>
          </div>
          <div class="mt-2 inline-flex rounded-md bg-emerald-600 text-white px-2 py-1 text-[10px] font-bold uppercase tracking-wide">
            <?= htmlspecialchars($roleLabels[$currentLevel] ?? 'Viewer') ?>
          </div>
        </div>

        <!-- Menu Items -->
        <div class="border-t border-slate-200">
          <a href="index.php?controller=Auth&action=profile"
             class="flex items-center gap-2 px-3 py-2 hover:bg-slate-50 border-b border-slate-100 transition text-sm">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-emerald-100 text-emerald-700 flex-shrink-0 text-sm">
              <?= $profileIcon ?? '⚙' ?>
            </span>
            <span class="min-w-0">
              <span class="block text-sm font-medium text-slate-900">Profile & Security</span>
              <span class="block text-xs text-slate-500">Account settings</span>
            </span>
          </a>

          <a href="index.php?controller=Auth&action=logout&wc=signedOut"
             class="flex items-center gap-2 px-3 py-2 hover:bg-slate-50 transition text-sm">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-red-100 text-red-700 flex-shrink-0 text-sm">
              <?= $signOutIcon ?? '↩' ?>
            </span>
            <span class="min-w-0">
              <span class="block text-sm font-medium text-slate-900">Sign Out</span>
              <span class="block text-xs text-slate-500">End session</span>
            </span>
          </a>
        </div>
      </div>
    </div>
  </div>
</nav>

<style>
  #sidebar.sidebar-open .sidebar-header-text,
  #sidebar:hover .sidebar-header-text {
    max-width: 11rem !important;
    opacity: 1 !important;
  }
  #sidebar.sidebar-open .sidebar-section-label,
  #sidebar:hover .sidebar-section-label {
    max-height: 1.5rem !important;
    opacity: 1 !important;
  }
  #sidebar.sidebar-open .sidebar-item-label,
  #sidebar:hover .sidebar-item-label {
    max-width: 16rem !important;
    opacity: 1 !important;
    transform: translateX(0) !important;
  }
</style>

<script>
  const sidebar = document.getElementById('sidebar');
  const btn = document.getElementById('sidebarToggle');
  const sidebarBackdrop = document.getElementById('sidebarBackdrop');
  if (btn && sidebar && sidebarBackdrop) {
    const toggleSidebar = () => {
      sidebar.classList.toggle('-translate-x-full');
      sidebar.classList.toggle('sidebar-open');
      const isOpen = !sidebar.classList.contains('-translate-x-full');
      sidebarBackdrop.classList.toggle('opacity-0', !isOpen);
      sidebarBackdrop.classList.toggle('pointer-events-none', !isOpen);
      sidebarBackdrop.classList.toggle('opacity-100', isOpen);
    };

    btn.addEventListener('click', () => {
      toggleSidebar();
    });

    sidebarBackdrop.addEventListener('click', () => {
      toggleSidebar();
    });
  }

  const profileButton = document.getElementById('profileMenuButton');
  const profileMenu = document.getElementById('profileMenu');

  if (profileButton && profileMenu) {
    const closeProfileMenu = () => profileMenu.classList.add('hidden');
    const toggleProfileMenu = () => profileMenu.classList.toggle('hidden');

    profileButton.addEventListener('click', (event) => {
      event.stopPropagation();
      toggleProfileMenu();
    });

    profileMenu.addEventListener('click', (event) => {
      event.stopPropagation();
    });

    document.addEventListener('click', closeProfileMenu);
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        closeProfileMenu();
      }
    });
  }

  const notifButton = document.getElementById('notifButton');
  const notifMenu = document.getElementById('notifMenu');
  const markAllReadButton = document.getElementById('markAllReadButton');
  const filterTabs = notifMenu ? notifMenu.querySelectorAll('.notification-filter-tab') : [];
  const notificationItems = notifMenu ? notifMenu.querySelectorAll('.notification-item') : [];

  if (notifButton && notifMenu) {
    notifButton.addEventListener('click', (e) => {
      e.stopPropagation();
      notifMenu.classList.toggle('hidden');
      if (profileMenu) {
        profileMenu.classList.add('hidden');
      }
    });
    document.addEventListener('click', () => notifMenu.classList.add('hidden'));

    notifMenu.addEventListener('click', (event) => {
      event.stopPropagation();
    });
  }

  if (markAllReadButton) {
    markAllReadButton.addEventListener('click', async (e) => {
      e.preventDefault();
      e.stopPropagation();

      try {
        const response = await fetch('index.php?controller=AdminNotifications&action=markAllRead', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });
        const payload = await response.json();
        if (payload.success) {
          const badge = notifButton.querySelector('span');
          if (badge) badge.remove();
          const unreadLabel = notifMenu.querySelector('.unread-count-label');
          if (unreadLabel) {
            unreadLabel.textContent = '0 Unread';
          }
          notificationItems.forEach((item) => {
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

  if (filterTabs.length > 0) {
    filterTabs.forEach((button) => {
      button.addEventListener('click', () => {
        const filter = button.getAttribute('data-filter');
        filterTabs.forEach((tab) => tab.classList.remove('border-slate-900', 'text-slate-900', 'bg-slate-100'));
        button.classList.add('border-slate-900', 'text-slate-900', 'bg-slate-100');

        notificationItems.forEach((item) => {
          const moduleLabel = item.querySelector('span[role="module-label"]');
          const moduleText = moduleLabel ? moduleLabel.textContent.trim().toLowerCase() : '';
          const isUnread = item.querySelector('.inline-flex.h-2.w-2.rounded-full.bg-sky-500') !== null;
          let show = true;

          if (filter === 'unread') {
            show = isUnread;
          } else if (filter === 'documents') {
            show = moduleText === 'documents';
          } else if (filter === 'fleet') {
            show = moduleText === 'fleet';
          } else if (filter === 'rooms') {
            show = moduleText === 'meeting rooms';
          } else if (filter === 'system') {
            show = moduleText === 'system';
          }

          item.classList.toggle('hidden', !show);
        });
      });
    });
    const firstTab = filterTabs[0];
    if (firstTab) {
      firstTab.classList.add('border-slate-900', 'text-slate-900', 'bg-slate-100');
    }
  }
</script>
