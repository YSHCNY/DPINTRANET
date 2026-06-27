<?php
  function dashboardStatusBadgeClass($status) {
    $value = strtolower(trim((string)($status ?? '')));
    if ($value === 'draft' || $value === 'inprogress' || $value === 'processing') {
      return 'border border-amber-100 bg-amber-50 text-amber-700';
    }
    if ($value === 'sent' || $value === 'received' || $value === 'completed' || $value === 'approved') {
      return 'border border-emerald-100 bg-emerald-50 text-emerald-700';
    }
    if ($value === 'urgent' || $value === 'high' || $value === 'priority') {
      return 'border border-rose-100 bg-rose-50 text-rose-700';
    }
    return 'border border-slate-200 bg-slate-100 text-slate-700';
  }

  function dashboardFileIcon($filename) {
    $extension = strtolower(pathinfo((string)($filename ?? ''), PATHINFO_EXTENSION));
    switch ($extension) {
      case 'pdf':
        return '📄';
      case 'doc':
      case 'docx':
      case 'txt':
        return '📝';
      case 'xls':
      case 'xlsx':
      case 'csv':
        return '📊';
      case 'jpg':
      case 'jpeg':
      case 'png':
      case 'gif':
      case 'webp':
        return '🖼️';
      case 'zip':
      case 'rar':
        return '🗜️';
      default:
        return '📁';
    }
  }

  function dashboardFileTypeLabel($filename) {
    $extension = strtolower(pathinfo((string)($filename ?? ''), PATHINFO_EXTENSION));
    if ($extension === '') {
      return 'File';
    }
    return strtoupper($extension);
  }

  function dashboardRelativeTime($value) {
    if (empty($value)) {
      return 'Recently updated';
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
      return 'Recently updated';
    }

    $diff = time() - $timestamp;
    if ($diff < 60) {
      return 'Just now';
    }
    if ($diff < 3600) {
      return floor($diff / 60) . ' min ago';
    }
    if ($diff < 86400) {
      return floor($diff / 3600) . ' hr ago';
    }

    $todayStart = strtotime('today');
    if ($timestamp >= $todayStart) {
      return 'Today, ' . date('g:i A', $timestamp);
    }

    return date('M j', $timestamp);
  }

  $totalRecipients = max(1, $correspondenceMetrics['total_recipients'] ?? 0);
  $receivedRecipients = $correspondenceMetrics['received_recipients'] ?? 0;
  $responseRate = round(($receivedRecipients / $totalRecipients) * 100);
  $pendingActions = (int)($correspondenceMetrics['pending_recipients'] ?? 0);
  $activeDrafts = (int)($correspondenceMetrics['draft_documents'] ?? 0);
  $openFiles = (int)($fileMetrics['total'] ?? 0);
  $recentFilesCount = (int)($fileMetrics['recent30Days'] ?? 0);
  $activitySummary = $pendingActions > 0
    ? 'Follow up on ' . $pendingActions . ' pending response' . ($pendingActions === 1 ? '' : 's') . ' and keep the queue moving.'
    : 'Everything is on track for today.';
  $totalBookings = (int)($carMetrics['scheduledBookings'] ?? 0) + (int)($roomMetrics['scheduledBookings'] ?? 0);
  $activeVehicles = (int)($carMetrics['activeVehicles'] ?? 0);
  $activeRooms = (int)($roomMetrics['activeRooms'] ?? 0);
  $circulationBody = $pendingActions > 0
    ? 'Follow up on ' . $pendingActions . ' pending response' . ($pendingActions === 1 ? '' : 's') . ' and ' . $activeDrafts . ' active draft' . ($activeDrafts === 1 ? '' : 's') . ' in motion.'
    : 'The circulation queue is clear and ready for the next release.';
  $filesBody = $recentFilesCount > 0
    ? $recentFilesCount . ' file' . ($recentFilesCount === 1 ? '' : 's') . ' uploaded or updated in the last 30 days.'
    : 'No new uploads were recorded in the last 30 days.';
  $roomTodayCount = (int)($roomMetrics['scheduledBookings'] ?? 0);
  $vehicleTodayCount = (int)($carMetrics['scheduledBookings'] ?? 0);
  $roomBody = $roomTodayCount > 0
    ? $roomTodayCount . ' meeting' . ($roomTodayCount === 1 ? '' : 's') . ' scheduled today.'
    : 'No room meetings are scheduled today.';
  $vehicleBody = $vehicleTodayCount > 0
    ? $vehicleTodayCount . ' booking' . ($vehicleTodayCount === 1 ? '' : 's') . ' scheduled today.'
    : 'No vehicle bookings are scheduled today.';
  $correspondenceStatus = $pendingActions > 0 ? 'Needs follow-up' : 'On track';
  $vehicleStatus = ((int)($carMetrics['scheduledBookings'] ?? 0) > 0) ? 'Bookings active' : 'Quiet day';
  $roomStatus = ((int)($roomMetrics['scheduledBookings'] ?? 0) > 0) ? 'Bookings active' : 'Quiet day';
  $filesStatus = $recentFilesCount > 0 ? 'Recently updated' : 'No recent activity';
?>
<div class="mx-auto flex max-w-7xl flex-col gap-4">
  <section class="rounded-[22px] border border-slate-200/80 bg-white p-4 shadow-[0_10px_30px_-20px_rgba(15,23,42,0.22)] sm:p-5">
    <div class="grid gap-4 xl:grid-cols-[1.08fr_0.92fr] xl:items-start">
      <div class="space-y-3">
        <div class="space-y-1.5">
          <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-emerald-700">Dashboard</p>
          <div class="space-y-1">
            <h1 class="text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl">Welcome back, <?= htmlspecialchars($firstName) ?>.</h1>
            <p class="max-w-2xl text-sm leading-5 text-slate-600">A calm overview of the work that needs attention, the modules in motion, and the next best action.</p>
          </div>
        </div>

        <div class="space-y-2">
          <div class="flex items-center gap-2">
            <span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
            <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-slate-500">Quick focus</p>
          </div>
          <div class="flex flex-wrap items-center gap-2" role="tablist" aria-label="Dashboard focus tabs">
            <button type="button" class="cta-tab active rounded-full border border-slate-200 bg-emerald-900 px-2.5 py-1.5 text-sm font-semibold text-white shadow-sm transition-all duration-300" data-cta-trigger="circulation" data-cta-label="Circulation" data-cta-title="Keep the circulation queue moving" data-cta-body="<?= htmlspecialchars($circulationBody) ?>" data-cta-meta="Pending responses and active drafts" data-cta-badge="Priority queue" aria-selected="true"><span class="mr-2 inline-flex h-2 w-2 rounded-full bg-emerald-300"></span>Circulation</button>
            <button type="button" class="cta-tab rounded-full border border-slate-200 bg-white px-2.5 py-1.5 text-sm font-semibold text-slate-700 transition-all duration-300 hover:bg-slate-50" data-cta-trigger="files" data-cta-label="Files" data-cta-title="Latest repository activity" data-cta-body="<?= htmlspecialchars($filesBody) ?>" data-cta-meta="Recent uploads and updates" data-cta-badge="Recent uploads" aria-selected="false"><span class="mr-2 inline-flex h-2 w-2 rounded-full bg-sky-400"></span>Files</button>
            <button type="button" class="cta-tab rounded-full border border-slate-200 bg-white px-2.5 py-1.5 text-sm font-semibold text-slate-700 transition-all duration-300 hover:bg-slate-50" data-cta-trigger="room" data-cta-label="Room" data-cta-title="Today’s room schedule" data-cta-body="<?= htmlspecialchars($roomBody) ?>" data-cta-meta="Meetings booked for today" data-cta-badge="Meetings" aria-selected="false"><span class="mr-2 inline-flex h-2 w-2 rounded-full bg-violet-400"></span>Room</button>
            <button type="button" class="cta-tab rounded-full border border-slate-200 bg-white px-2.5 py-1.5 text-sm font-semibold text-slate-700 transition-all duration-300 hover:bg-slate-50" data-cta-trigger="vehicle" data-cta-label="Vehicle" data-cta-title="Today’s vehicle bookings" data-cta-body="<?= htmlspecialchars($vehicleBody) ?>" data-cta-meta="Vehicle commitments for today" data-cta-badge="Bookings" aria-selected="false"><span class="mr-2 inline-flex h-2 w-2 rounded-full bg-amber-400"></span>Vehicle</button>
          </div>
        </div>

        <div id="focusTodayCard" class="rounded-[16px] border border-slate-200/80 bg-slate-50/90 p-3 transition-all duration-300 sm:p-3.5">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="min-w-0">
              <div class="flex flex-wrap items-center gap-2">
                <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500">What’s latest</p>
                <span id="focusTodayMeta" class="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-600">Pending responses and active drafts</span>
              </div>
              <p id="focusTodayLabel" class="mt-2 text-[10px] font-semibold uppercase tracking-[0.2em] text-emerald-700">Circulation</p>
              <p id="focusTodayTitle" class="mt-2 text-base font-semibold text-slate-900">Keep the circulation queue moving</p>
              <p id="focusTodayBody" class="mt-2 text-sm leading-5 text-slate-600"><?= htmlspecialchars($circulationBody) ?></p>
            </div>
            <div id="focusTodayBadge" class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-emerald-700">Priority queue</div>
          </div>
        </div>
      </div>

      <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-1">
        <div class="rounded-[16px] border border-slate-200 bg-slate-50/80 p-3">
          <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-emerald-700">Response rate</p>
          <p class="mt-1.5 text-xl font-semibold text-slate-900"><?= $responseRate ?>%</p>
          <p class="mt-1 text-sm text-slate-500">Recipients who have already responded</p>
        </div>
        <div class="rounded-[16px] border border-slate-200 bg-slate-900 p-3 text-white">
          <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-300">Pending actions</p>
          <p class="mt-1.5 text-xl font-semibold"><?= $pendingActions ?></p>
          <p class="mt-1 text-sm text-slate-300">Open follow-ups waiting for your input</p>
        </div>
        <div class="rounded-[16px] border border-slate-200 bg-white p-3 sm:col-span-2 xl:col-span-1">
          <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500">Active drafts</p>
          <p class="mt-1.5 text-xl font-semibold text-slate-900"><?= $activeDrafts ?></p>
          <p class="mt-1 text-sm text-slate-500">Documents still moving through review</p>
        </div>
      </div>
    </div>
  </section>

  <section class="rounded-[22px] border border-slate-200/80 bg-white p-4 shadow-[0_10px_30px_-20px_rgba(15,23,42,0.16)] sm:p-5">
    <div class="flex flex-wrap items-center justify-between gap-2">
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500">Operational summary</p>
        <h2 class="mt-1 text-base font-semibold text-slate-900">Modules at a glance</h2>
      </div>
      <a href="index.php?controller=correspondence&action=correspondence" class="text-sm font-semibold text-emerald-700 transition hover:text-emerald-900">Open correspondence</a>
    </div>

    <div class="mt-3 grid gap-2.5 sm:grid-cols-2 xl:grid-cols-4">
      <article class="rounded-[16px] border border-slate-200/70 bg-slate-50/80 p-3">
        <div class="flex items-center justify-between gap-3">
          <p class="text-sm font-semibold text-slate-900">Correspondence</p>
          <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-emerald-700"><?= htmlspecialchars($correspondenceStatus) ?></span>
        </div>
        <p class="mt-2 text-xl font-semibold text-slate-900"><?= $responseRate ?>%</p>
        <p class="mt-1 text-sm text-slate-500">Response completion across recipients</p>
        <p class="mt-2 text-sm text-slate-600"><?= $pendingActions ?> pending follow-up<?= $pendingActions === 1 ? '' : 's' ?></p>
      </article>

      <article class="rounded-[16px] border border-slate-200/70 bg-slate-50/80 p-3">
        <div class="flex items-center justify-between gap-3">
          <p class="text-sm font-semibold text-slate-900">Vehicle booking</p>
          <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-600"><?= htmlspecialchars($vehicleStatus) ?></span>
        </div>
        <p class="mt-2 text-xl font-semibold text-slate-900"><?= (int)($carMetrics['activeVehicles'] ?? 0) ?></p>
        <p class="mt-1 text-sm text-slate-500">Active vehicles in the fleet</p>
        <p class="mt-2 text-sm text-slate-600"><?= (int)($carMetrics['scheduledBookings'] ?? 0) ?> scheduled booking<?= ((int)($carMetrics['scheduledBookings'] ?? 0) === 1) ? '' : 's' ?></p>
      </article>

      <article class="rounded-[16px] border border-slate-200/70 bg-slate-50/80 p-3">
        <div class="flex items-center justify-between gap-3">
          <p class="text-sm font-semibold text-slate-900">Room booking</p>
          <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-600"><?= htmlspecialchars($roomStatus) ?></span>
        </div>
        <p class="mt-2 text-xl font-semibold text-slate-900"><?= (int)($roomMetrics['activeRooms'] ?? 0) ?></p>
        <p class="mt-1 text-sm text-slate-500">Active rooms available for booking</p>
        <p class="mt-2 text-sm text-slate-600"><?= (int)($roomMetrics['scheduledBookings'] ?? 0) ?> scheduled booking<?= ((int)($roomMetrics['scheduledBookings'] ?? 0) === 1) ? '' : 's' ?></p>
      </article>

      <article class="rounded-[16px] border border-slate-200/70 bg-slate-50/80 p-3">
        <div class="flex items-center justify-between gap-3">
          <p class="text-sm font-semibold text-slate-900">File repository</p>
          <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-600"><?= htmlspecialchars($filesStatus) ?></span>
        </div>
        <p class="mt-2 text-xl font-semibold text-slate-900"><?= $openFiles ?></p>
        <p class="mt-1 text-sm text-slate-500">Total files in the repository</p>
        <p class="mt-2 text-sm text-slate-600"><?= $recentFilesCount ?> added or updated in the last 30 days</p>
      </article>
    </div>
  </section>

  <section class="rounded-2xl border border-slate-200/60 bg-white p-4 shadow-sm sm:p-4">
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
      <div class="space-y-1">
        <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500">Schedule overview</p>
        <h2 class="text-[15px] font-medium text-slate-900">Bookings calendar</h2>
      </div>
      <div class="flex flex-wrap items-center gap-2">
        <div class="inline-flex items-center gap-2 rounded-full border border-slate-200/60 bg-slate-50 px-4 py-2 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-600">
          <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
          <span>Vehicle</span>
          <span class="inline-flex h-2 w-2 rounded-full bg-violet-500"></span>
          <span>Room</span>
        </div>
        <div class="inline-flex rounded-xl border border-slate-200/60 bg-slate-50 p-2">
          <button type="button" data-calendar-filter="all" class="h-8 rounded-xl border border-transparent bg-slate-900 px-4 text-[11px] font-medium text-white transition hover:bg-slate-800">All</button>
          <button type="button" data-calendar-filter="car" class="h-8 rounded-xl border border-transparent bg-slate-50 px-4 text-[11px] font-medium text-slate-700 transition hover:bg-slate-100">Vehicle</button>
          <button type="button" data-calendar-filter="room" class="h-8 rounded-xl border border-transparent bg-slate-50 px-4 text-[11px] font-medium text-slate-700 transition hover:bg-slate-100">Room</button>
        </div>
      </div>
    </div>

    <div class="mt-4 grid gap-4 grid-cols-1 xl:grid-cols-[1.3fr_0.7fr]">
      <div class="dashboard-calendar-shell rounded-xl border border-slate-200/60 bg-white p-2 shadow-sm relative" id="dashboardCalendar">
        <div id="calendarEmptyState" class="absolute inset-0 m-4 flex items-center justify-center rounded-lg border-dashed border-slate-200 bg-slate-50/80 p-4 text-sm text-slate-500 hidden">No scheduled bookings for the selected range.</div>
      </div>

      <aside class="rounded-xl border border-slate-200/60 bg-slate-50/80 p-4 shadow-sm xl:max-w-[420px]">
        <div class="flex items-start justify-between gap-4">
          <div>
            <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500">Today and next</p>
            <h3 class="mt-1 text-[15px] font-medium text-slate-900">Schedule focus</h3>
          </div>
          <span class="rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-[10px] font-semibold uppercase tracking-[0.16em] text-emerald-700">Live</span>
        </div>

        <div class="mt-4 text-[12px] text-slate-700">
          <div class="space-y-4">
            <div class="flex items-center justify-between gap-2">
              <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">Today</p>
              <span id="todayBookingsCount" class="rounded-full bg-slate-100 px-4 py-2 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-600">0</span>
            </div>
            <div id="todayBookingsList" class="space-y-4"></div>
          </div>

          <div class="mt-4 border-t border-slate-200/60 pt-4 space-y-4">
            <div class="flex items-center justify-between gap-2">
              <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">Upcoming</p>
              <span class="rounded-full bg-slate-100 px-4 py-2 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-600">Next</span>
            </div>
            <div id="upcomingReservationsList" class="space-y-4"></div>
          </div>

          <div class="mt-4 border-t border-slate-200/60 pt-4">
            <div class="flex items-center gap-4 text-[11px] text-slate-500">
              <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
              <span>Vehicle</span>
              <span class="inline-flex h-2 w-2 rounded-full bg-violet-500"></span>
              <span>Room</span>
            </div>
          </div>
        </div>
      </aside>
    </div>

    <div class="mt-4 grid gap-4 rounded-xl border border-slate-200/60 bg-slate-50/80 p-4 sm:grid-cols-4">
      <div class="flex items-center justify-between gap-2 rounded-xl border border-slate-200/60 bg-white/80 px-4 py-4">
        <span class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">Bookings</span>
        <span id="summaryTotalBookings" class="text-[13px] font-medium text-slate-900"><?= $totalBookings ?></span>
      </div>
      <div class="flex items-center justify-between gap-2 rounded-xl border border-slate-200/60 bg-white/80 px-4 py-4">
        <span class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">Vehicles</span>
        <span id="summaryActiveVehicles" class="text-[13px] font-medium text-slate-900"><?= $activeVehicles ?></span>
      </div>
      <div class="flex items-center justify-between gap-2 rounded-xl border border-slate-200/60 bg-white/80 px-4 py-4">
        <span class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">Rooms</span>
        <span id="summaryActiveRooms" class="text-[13px] font-medium text-slate-900"><?= $activeRooms ?></span>
      </div>
      <div class="flex items-center justify-between gap-2 rounded-xl border border-slate-200/60 bg-white/80 px-4 py-4">
        <span class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">Conflicts</span>
        <span id="summaryConflicts" class="text-[13px] font-medium text-slate-900">0</span>
      </div>
    </div>

    <div class="mt-4 text-xs text-slate-400">Only scheduled room and vehicle bookings are included.</div>
  </section>

    <div id="dashboardEventModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 py-6">
      <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>
      <div class="relative w-full max-w-2xl overflow-hidden rounded-[24px] bg-white p-5 shadow-2xl">
        <div class="flex items-start justify-between gap-4">
          <div>
            <p id="dashboardEventSource" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Booking type</p>
            <h3 id="dashboardEventTitle" class="mt-2 text-xl font-semibold text-slate-900">Event details</h3>
          </div>
          <button type="button" id="dashboardEventCloseBtn" class="text-slate-500 transition hover:text-slate-900">✕</button>
        </div>
        <div class="mt-5 grid gap-4 sm:grid-cols-2">
          <div class="space-y-3 rounded-2xl bg-slate-50 p-3">
            <div>
              <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Status</p>
              <p id="dashboardEventStatus" class="mt-2 text-sm font-semibold text-slate-900"></p>
            </div>
            <div>
              <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Start</p>
              <p id="dashboardEventStart" class="mt-2 text-sm text-slate-700"></p>
            </div>
            <div>
              <p class="text-xs uppercase tracking-[0.18em] text-slate-500">End</p>
              <p id="dashboardEventEnd" class="mt-2 text-sm text-slate-700"></p>
            </div>
          </div>
          <div class="space-y-3 rounded-2xl bg-slate-50 p-3">
            <div>
              <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Location</p>
              <p id="dashboardEventLocation" class="mt-2 text-sm text-slate-700"></p>
            </div>
            <div>
              <p class="text-xs uppercase tracking-[0.18em] text-slate-500">People</p>
              <p id="dashboardEventPeople" class="mt-2 text-sm text-slate-700"></p>
            </div>
            <div>
              <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Notes</p>
              <p id="dashboardEventNotes" class="mt-2 text-sm text-slate-700"></p>
            </div>
          </div>
        </div>
        <div class="mt-5 flex flex-wrap items-center gap-3">
          <a id="dashboardEventViewLink" href="#" target="_blank" class="rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">Open booking page</a>
          <button type="button" id="dashboardEventDismissBtn" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Close</button>
        </div>
      </div>
    </div>
  </section>

  <style>
    .dashboard-calendar-shell {
      min-height: min(56vh, 620px);
    }

    .dashboard-calendar-shell .fc-toolbar {
      flex-wrap: wrap;
      gap: 0.5rem;
      margin-bottom: 0.5rem;
    }

    .dashboard-calendar-shell .fc-toolbar-chunk {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
    }

    .dashboard-calendar-shell .fc-button {
      padding: 0.5rem 1rem;
      font-size: 0.75rem;
      height: 2rem;
      border-radius: 0.5rem;
      border-color: #e2e8f0;
      background: #fff;
      color: #0f172a;
      box-shadow: none;
    }

    .dashboard-calendar-shell .fc-button-primary:not(:disabled).fc-button-active,
    .dashboard-calendar-shell .fc-button-primary:not(:disabled):hover {
      background: #059669;
      border-color: #059669;
      color: #fff;
    }

    .dashboard-calendar-shell .fc-scrollgrid {
      border-color: #eff2f7;
    }

    .dashboard-calendar-shell .fc-theme-standard td,
    .dashboard-calendar-shell .fc-theme-standard th {
      border-color: #eff2f7;
    }

    .dashboard-calendar-shell .fc-daygrid-day-frame {
      min-height: 80px;
      padding: 0.5rem;
    }

    .dashboard-calendar-shell .fc-daygrid-day-top {
      padding: 0.5rem 0.5rem 0;
    }

    .dashboard-calendar-shell .fc-daygrid-day-number {
      color: #475569;
      font-size: 0.75rem;
      padding: 0.25rem 0.5rem;
    }

    .dashboard-calendar-shell .fc-day-today {
      background-color: rgba(16, 185, 129, 0.08) !important;
    }

    .dashboard-calendar-shell .fc-day-today .fc-daygrid-day-number {
      color: #047857;
      font-weight: 600;
    }

    .dashboard-calendar-shell .fc-event {
      border: none;
      box-shadow: none;
      font-size: 0.75rem;
      padding: 0.25rem 0.5rem;
      border-radius: 0.5rem;
      margin-bottom: 0.25rem;
      opacity: 0.95;
    }

    .dashboard-calendar-shell .fc-event-main-frame {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .dashboard-calendar-shell .fc-event-title {
      font-weight: 500;
      letter-spacing: 0.01em;
    }

    .dashboard-calendar-shell .fc-more-link {
      color: #059669;
      font-size: 0.75rem;
      text-decoration: none;
    }

    @media (max-width: 640px) {
      .dashboard-calendar-shell {
        min-height: 420px;
      }

      .dashboard-calendar-shell .fc-toolbar-title {
        font-size: 0.92rem;
        width: 100%;
        text-align: left;
      }

      .dashboard-calendar-shell .fc-header-toolbar {
        margin-bottom: 0.5rem;
      }
    }
  </style>

  <style>
    @keyframes ctaPulse {
      0% { transform: translateY(0) scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.16); }
      50% { transform: translateY(-1px) scale(1.01); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0.04); }
      100% { transform: translateY(0) scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    .cta-tab {
      position: relative;
      overflow: hidden;
    }

    .cta-tab::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.18), transparent);
      transform: translateX(-120%);
      transition: transform 0.5s ease;
    }

    .cta-tab:hover::after {
      transform: translateX(120%);
    }

    .cta-tab.active {
      background-color: #0f172a;
      color: #fff;
      border-color: #0f172a;
      box-shadow: 0 10px 30px -12px rgba(15, 23, 42, 0.35);
    }

    #focusTodayCard.is-updating {
      animation: ctaPulse 0.65s ease;
    }
  </style>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
  <script>
    (function () {
      const calendarEndpoint = 'index.php?controller=Auth&action=dashboardEvents';
      const dashboardCalendarEl = document.getElementById('dashboardCalendar');
      const ctaButtons = Array.from(document.querySelectorAll('[data-cta-trigger]'));
      const focusTodayCard = document.getElementById('focusTodayCard');
      const focusTodayLabel = document.getElementById('focusTodayLabel');
      const focusTodayTitle = document.getElementById('focusTodayTitle');
      const focusTodayBody = document.getElementById('focusTodayBody');
      const focusTodayMeta = document.getElementById('focusTodayMeta');
      const focusTodayBadge = document.getElementById('focusTodayBadge');
      const filterButtons = Array.from(document.querySelectorAll('[data-calendar-filter]'));
      const todayBookingsCount = document.getElementById('todayBookingsCount');
      const todayBookingsList = document.getElementById('todayBookingsList');
      const upcomingReservationsList = document.getElementById('upcomingReservationsList');
      const summaryTotalBookings = document.getElementById('summaryTotalBookings');
      const summaryActiveVehicles = document.getElementById('summaryActiveVehicles');
      const summaryActiveRooms = document.getElementById('summaryActiveRooms');
      const summaryConflicts = document.getElementById('summaryConflicts');
      const emptyState = document.getElementById('calendarEmptyState');
      const eventModal = document.getElementById('dashboardEventModal');
      const eventCloseBtn = document.getElementById('dashboardEventCloseBtn');
      const eventDismissBtn = document.getElementById('dashboardEventDismissBtn');
      const eventSourceEl = document.getElementById('dashboardEventSource');
      const eventTitleEl = document.getElementById('dashboardEventTitle');
      const eventStatusEl = document.getElementById('dashboardEventStatus');
      const eventStartEl = document.getElementById('dashboardEventStart');
      const eventEndEl = document.getElementById('dashboardEventEnd');
      const eventLocationEl = document.getElementById('dashboardEventLocation');
      const eventPeopleEl = document.getElementById('dashboardEventPeople');
      const eventNotesEl = document.getElementById('dashboardEventNotes');
      const eventViewLink = document.getElementById('dashboardEventViewLink');

      let activeFilter = 'all';
      let calendar;

      function setActiveCta(trigger) {
        ctaButtons.forEach(btn => {
          const isActive = btn.dataset.ctaTrigger === trigger;
          btn.classList.toggle('active', isActive);
          btn.classList.toggle('bg-emerald-900', isActive);
          btn.classList.toggle('text-white', isActive);
          btn.classList.toggle('bg-white', !isActive);
          btn.classList.toggle('text-slate-700', !isActive);
          btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        const selected = ctaButtons.find(btn => btn.dataset.ctaTrigger === trigger);
        if (!selected) return;

        focusTodayLabel.textContent = selected.dataset.ctaLabel || 'Focus';
        focusTodayTitle.textContent = selected.dataset.ctaTitle || 'Latest update';
        focusTodayBody.textContent = selected.dataset.ctaBody || 'No updates available.';
        focusTodayMeta.textContent = selected.dataset.ctaMeta || 'Live update';
        focusTodayBadge.textContent = selected.dataset.ctaBadge || 'Update';
        focusTodayCard.classList.remove('is-updating');
        void focusTodayCard.offsetWidth;
        focusTodayCard.classList.add('is-updating');
      }

      function formatDateTime(value) {
        if (!value) return '—';
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return '—';
        return new Intl.DateTimeFormat('en-US', {
          month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit'
        }).format(date);
      }

      function normalizeEventDate(value) {
        if (!value) return null;
        if (value instanceof Date) {
          return Number.isNaN(value.getTime()) ? null : value;
        }
        const normalized = String(value).trim().replace(' ', 'T');
        const date = new Date(normalized);
        return Number.isNaN(date.getTime()) ? null : date;
      }

      function normalizeIsoDate(value) {
        if (!value) return value;
        const text = String(value).trim();
        return text.includes(' ') ? text.replace(' ', 'T') : text;
      }

      function formatCompactDate(value) {
        if (!value) return '—';
        const date = normalizeEventDate(value);
        if (!date) return '—';
        return new Intl.DateTimeFormat('en-US', {
          month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit'
        }).format(date);
      }

      function renderScheduleSidebar(events) {
        const safeEvents = Array.isArray(events) ? events : [];
        const today = new Date();
        const startOfToday = new Date(today.getFullYear(), today.getMonth(), today.getDate());
        const endOfToday = new Date(startOfToday);
        endOfToday.setDate(endOfToday.getDate() + 1);

        const todayEvents = safeEvents
          .map(event => ({
            ...event,
            start: normalizeIsoDate(event.start),
            end: normalizeIsoDate(event.end || event.start),
            _parsedStart: normalizeEventDate(event.start),
            _parsedEnd: normalizeEventDate(event.end || event.start),
          }))
          .filter(event => event._parsedStart && event._parsedStart >= startOfToday && event._parsedStart < endOfToday)
          .sort((a, b) => a._parsedStart - b._parsedStart)
          .slice(0, 4);

        const upcomingEvents = safeEvents
          .map(event => ({
            ...event,
            start: normalizeIsoDate(event.start),
            end: normalizeIsoDate(event.end || event.start),
            _parsedStart: normalizeEventDate(event.start),
            _parsedEnd: normalizeEventDate(event.end || event.start),
          }))
          .filter(event => event._parsedStart && event._parsedStart >= endOfToday)
          .sort((a, b) => a._parsedStart - b._parsedStart)
          .slice(0, 4);

        const conflicts = safeEvents.reduce((count, event) => {
          const groupKey = (event.extendedProps && event.extendedProps.booking_type) ? String(event.extendedProps.booking_type).toLowerCase() : 'booking';
          return count;
        }, 0);

        const grouped = safeEvents.reduce((acc, event) => {
          const groupKey = (event.extendedProps && event.extendedProps.booking_type) ? String(event.extendedProps.booking_type).toLowerCase() : 'booking';
          if (!acc[groupKey]) acc[groupKey] = [];
          acc[groupKey].push(event);
          return acc;
        }, {});

        let overlapCount = 0;
        Object.values(grouped).forEach(eventsByType => {
          const sorted = [...eventsByType].sort((a, b) => new Date(a.start) - new Date(b.start));
          for (let index = 1; index < sorted.length; index += 1) {
            const previous = sorted[index - 1];
            const current = sorted[index];
            const previousEnd = previous.end ? new Date(previous.end) : new Date(previous.start);
            const currentStart = current.start ? new Date(current.start) : null;
            if (currentStart && currentStart < previousEnd) {
              overlapCount += 1;
            }
          }
        });

        summaryTotalBookings.textContent = safeEvents.length;
        summaryActiveVehicles.textContent = document.getElementById('summaryActiveVehicles').textContent || '0';
        summaryActiveRooms.textContent = document.getElementById('summaryActiveRooms').textContent || '0';
        summaryConflicts.textContent = overlapCount;
        todayBookingsCount.textContent = todayEvents.length;

        const renderList = (listEl, items, emptyLabel) => {
          if (!items.length) {
            listEl.innerHTML = `<div class="rounded-lg border border-dashed border-slate-200 bg-slate-50 px-2.5 py-2 text-xs text-slate-500">${emptyLabel}</div>`;
            return;
          }

          listEl.innerHTML = items.map(item => {
            const title = item.title || 'Booking';
            const timeLabel = formatCompactDate(item.start);
            const type = (item.extendedProps && item.extendedProps.booking_type) ? item.extendedProps.booking_type : 'Booking';
            const indicator = type.toLowerCase().includes('car') ? 'bg-emerald-500' : 'bg-violet-500';
            return `
              <div class="flex items-start gap-2 border-b border-slate-200/60 pb-2 last:border-0">
                <span class="mt-1 inline-flex h-2 w-2 shrink-0 rounded-full ${indicator}"></span>
                <div class="min-w-0">
                  <p class="truncate text-[13px] font-medium text-slate-900">${title}</p>
                  <p class="mt-1 text-[11px] text-slate-500">${timeLabel}</p>
                </div>
              </div>
            `;
          }).join('');
        };

        renderList(todayBookingsList, todayEvents, 'No bookings today.');
        renderList(upcomingReservationsList, upcomingEvents, 'No upcoming bookings.');
      }

      function setActiveFilter(type) {
        activeFilter = type;
        filterButtons.forEach(btn => {
          const isActive = btn.dataset.calendarFilter === type;
          btn.classList.toggle('bg-slate-900', isActive);
          btn.classList.toggle('text-white', isActive);
          btn.classList.toggle('bg-slate-50', !isActive);
          btn.classList.toggle('text-slate-700', !isActive);
        });
        if (calendar) {
          calendar.refetchEvents();
        }
      }

      function formatDateTime(value) {
        if (!value) return '—';
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return '—';
        return new Intl.DateTimeFormat('en-US', {
          month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit'
        }).format(date);
      }

      function openModal() {
        eventModal.classList.remove('hidden');
        eventModal.classList.add('flex');
        document.body.style.overflow = 'hidden';
      }

      function closeModal() {
        eventModal.classList.add('hidden');
        eventModal.classList.remove('flex');
        document.body.style.overflow = '';
      }

      function renderEventDetail(event) {
        const props = event.extendedProps || {};
        eventSourceEl.textContent = props.booking_type || props.source || 'Booking';
        eventTitleEl.textContent = event.title || 'Booking details';
        eventStatusEl.textContent = props.status ? props.status.charAt(0).toUpperCase() + props.status.slice(1) : 'Scheduled';
        eventStartEl.textContent = formatDateTime(event.start) || formatDateTime(props.departure_expected || props.start);
        eventEndEl.textContent = formatDateTime(event.end) || formatDateTime(props.return_expected || props.end);
        eventLocationEl.textContent = props.room_name ? `${props.room_name} (${props.room_code || 'Room'})` : (props.vehicle_name ? `${props.vehicle_name} (${props.plate_number || 'Vehicle'})` : 'N/A');
        eventPeopleEl.textContent = props.attendees ? `${props.attendees} attendee(s)` : (props.passengers ? `${props.passengers} passenger(s)` : 'N/A');
        eventNotesEl.textContent = props.remarks || props.purpose || 'No additional notes';
        eventViewLink.href = props.module_route || '#';
      }

      function initializeCalendar() {
        calendar = new FullCalendar.Calendar(dashboardCalendarEl, {
          initialView: 'dayGridMonth',
          themeSystem: 'standard',
          buttonText: {
            today: 'Today',
            month: 'Month',
            week: 'Week',
            day: 'Day'
          },
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
          },
          height: 'auto',
          contentHeight: 'auto',
          aspectRatio: 1.4,
          fixedWeekCount: false,
          dayMaxEvents: 2,
          eventDisplay: 'block',
          eventDidMount(info) {
            const bookingType = (info.event.extendedProps && info.event.extendedProps.booking_type) ? String(info.event.extendedProps.booking_type).toLowerCase() : '';
            const color = bookingType.includes('car') ? '#14b8a6' : '#8b5cf6';
            info.el.style.backgroundColor = color;
            info.el.style.borderColor = color;
            info.el.style.color = '#fff';
            info.el.style.boxShadow = '0 8px 20px -10px rgba(15, 23, 42, 0.25)';
          },
          eventContent(arg) {
            return { html: `<span class="inline-flex items-center gap-1"><span>${arg.event.title}</span></span>` };
          },
          events(fetchInfo, successCallback, failureCallback) {
            const params = new URLSearchParams({
              start: fetchInfo.startStr,
              end: fetchInfo.endStr,
              type: activeFilter,
            });
            fetch(`${calendarEndpoint}&${params.toString()}`, {
              headers: {'X-Requested-With': 'XMLHttpRequest'}
            })
              .then(response => response.json())
              .then(data => {
                const rawEvents = Array.isArray(data.events) ? data.events : [];
                const events = rawEvents.map(event => ({
                  ...event,
                  start: normalizeIsoDate(event.start),
                  end: normalizeIsoDate(event.end || event.start),
                }));
                emptyState.classList.toggle('hidden', events.length > 0);
                renderScheduleSidebar(events);
                successCallback(events);
              })
              .catch(() => {
                emptyState.classList.remove('hidden');
                failureCallback();
              });
          },
          eventClick(info) {
            info.jsEvent.preventDefault();
            renderEventDetail(info.event);
            openModal();
          },
        });

        calendar.render();
      }

      ctaButtons.forEach(btn => {
        btn.addEventListener('click', () => setActiveCta(btn.dataset.ctaTrigger));
      });

      filterButtons.forEach(btn => {
        btn.addEventListener('click', () => setActiveFilter(btn.dataset.calendarFilter));
      });

      eventCloseBtn.addEventListener('click', closeModal);
      eventDismissBtn.addEventListener('click', closeModal);
      eventModal.addEventListener('click', (event) => {
        if (event.target === eventModal) {
          closeModal();
        }
      });

      setActiveCta('circulation');
      setActiveFilter('all');
      initializeCalendar();
    })();
  </script>

  <div class="grid gap-4 lg:grid-cols-2">
    <!-- Recent Correspondence Section -->
    <section class="rounded-[22px] border border-slate-200/80 bg-gradient-to-br from-white via-white to-slate-50/40 p-5 shadow-[0_10px_30px_-20px_rgba(15,23,42,0.16)]">
      <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="space-y-1">
          <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500">Circulation pipeline</p>
          <h2 class="text-base font-semibold text-slate-900">Active documents</h2>
        </div>
        <div class="flex flex-wrap items-center justify-end gap-2">
          <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-emerald-700">
            <span class="inline-flex h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
            <?= is_array($recentDocuments) ? count($recentDocuments) : 0 ?> documents
          </span>
        </div>
      </div>
      <div class="mt-4 space-y-2.5">
        <?php if (empty($recentDocuments)): ?>
          <div class="rounded-[16px] border border-dashed border-slate-200/80 bg-slate-50/60 px-4 py-8 text-center">
            <svg class="mx-auto h-12 w-12 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="text-sm text-slate-500">No recent documents</p>
          </div>
        <?php else: ?>
          <?php foreach (array_slice($recentDocuments, 0, 6) as $doc): ?>
            <?php
              $docStatus = !empty($doc['is_draft']) ? 'Draft' : ($doc['status'] ?? 'Pending');
              $docStatusClass = dashboardStatusBadgeClass($docStatus);
              $pendingCount = (int)($doc['pending_count'] ?? 0);
              $receivedCount = (int)($doc['received_count'] ?? 0);
              $recipientSummary = $pendingCount > 0 || $receivedCount > 0 ? $pendingCount . ' Pending • ' . $receivedCount . ' Received' : 'No activity yet';
              $statusBgClass = (strpos($docStatusClass, 'emerald') !== false) ? 'bg-emerald-50/80' : (strpos($docStatusClass, 'amber') !== false ? 'bg-amber-50/80' : 'bg-slate-50/80');
            ?>
            <a href="index.php?controller=correspondence&action=show&id=<?= (int)$doc['id'] ?>" class="group flex items-start gap-3 rounded-[14px] border border-slate-200/60 bg-white px-3.5 py-3 transition duration-200 hover:border-slate-300 hover:shadow-[0_8px_16px_-2px_rgba(15,23,42,0.08)]">
              <div class="shrink-0 pt-0.5">
                <svg class="h-5 w-5 text-slate-600 group-hover:text-emerald-700 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-2">
                  <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-slate-900 group-hover:text-emerald-700 transition" title="<?= htmlspecialchars($doc['title'] ?? 'Untitled') ?>"><?= htmlspecialchars($doc['title'] ?? 'Untitled') ?></p>
                    <p class="mt-1.5 truncate text-xs leading-relaxed text-slate-500" title="#<?= htmlspecialchars($doc['tracking_id'] ?? '—') ?> • <?= htmlspecialchars($doc['type'] ?? 'General') ?>">#<?= htmlspecialchars($doc['tracking_id'] ?? '—') ?> • <?= htmlspecialchars($doc['type'] ?? 'General') ?> • <?= date('M j, Y', strtotime($doc['created_at'] ?? 'now')) ?></p>
                  </div>
                  <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] whitespace-nowrap <?= $docStatusClass ?>"><?= htmlspecialchars($docStatus) ?></span>
                </div>
                <p class="mt-2 text-[11px] font-medium text-slate-600"><?= htmlspecialchars($recipientSummary) ?></p>
              </div>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      <a href="index.php?controller=correspondence&action=correspondence" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-emerald-700 transition hover:text-emerald-900">
        <span>View all documents</span>
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </a>
    </section>

    <!-- Latest Files Section -->
    <section class="rounded-[22px] border border-slate-200/80 bg-gradient-to-br from-white via-white to-slate-50/40 p-5 shadow-[0_10px_30px_-20px_rgba(15,23,42,0.16)]">
      <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="space-y-1">
          <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500">Repository activity</p>
          <h2 class="text-base font-semibold text-slate-900">Recently updated</h2>
        </div>
        <div class="flex flex-wrap items-center justify-end gap-2">
          <span class="inline-flex items-center gap-1.5 rounded-full bg-sky-50 px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-sky-700">
            <span class="inline-flex h-1.5 w-1.5 rounded-full bg-sky-500"></span>
            <?= is_array($latestFiles) ? count($latestFiles) : 0 ?> files
          </span>
        </div>
      </div>
      <div class="mt-4 space-y-2.5">
        <?php if (empty($latestFiles)): ?>
          <div class="rounded-[16px] border border-dashed border-slate-200/80 bg-slate-50/60 px-4 py-8 text-center">
            <svg class="mx-auto h-12 w-12 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 19H9a6 6 0 016-6v0a6 6 0 016 6v0z" />
            </svg>
            <p class="text-sm text-slate-500">No recent file uploads</p>
          </div>
        <?php else: ?>
          <?php foreach (array_slice($latestFiles, 0, 6) as $file): ?>
            <?php
              $uploaderName = trim(($file['firstName'] ?? '') . ' ' . ($file['lastName'] ?? '')) ?: 'Unknown';
              $filename = (string)($file['filename'] ?? 'Untitled file');
              $relativeTime = dashboardRelativeTime($file['uploadedat'] ?? null);
              $specialState = strtolower((string)($file['status'] ?? ''));
              $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
              
              // File type styling
              $fileTypeConfig = [
                'pdf' => ['icon' => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z', 'color' => 'text-rose-600', 'bg' => 'bg-rose-50'],
                'doc' => ['icon' => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50'],
                'docx' => ['icon' => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50'],
                'xls' => ['icon' => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50'],
                'xlsx' => ['icon' => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50'],
                'jpg' => ['icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z', 'color' => 'text-amber-600', 'bg' => 'bg-amber-50'],
                'png' => ['icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z', 'color' => 'text-amber-600', 'bg' => 'bg-amber-50'],
                'zip' => ['icon' => 'M12 4.354a4 4 0 110 5.292M15 19H9', 'color' => 'text-slate-600', 'bg' => 'bg-slate-100'],
              ];
              $config = $fileTypeConfig[$extension] ?? ['icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color' => 'text-slate-600', 'bg' => 'bg-slate-100'];
              $statusBg = in_array($specialState, ['processing', 'failed', 'review'], true) ? 'border border-amber-100 bg-amber-50 text-amber-700' : '';
            ?>
            <a href="index.php?controller=Files&action=files" class="group flex items-start gap-3 rounded-[14px] border border-slate-200/60 bg-white px-3.5 py-3 transition duration-200 hover:border-slate-300 hover:shadow-[0_8px_16px_-2px_rgba(15,23,42,0.08)]">
              <div class="shrink-0 flex h-10 w-10 items-center justify-center rounded-xl <?= $config['bg'] ?>">
                <svg class="h-5 w-5 <?= $config['color'] ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="<?= $config['icon'] ?>" />
                </svg>
              </div>
              <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-2">
                  <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-slate-900 group-hover:text-emerald-700 transition" title="<?= htmlspecialchars($filename) ?>"><?= htmlspecialchars($filename) ?></p>
                    <p class="mt-1.5 truncate text-xs text-slate-500"><?= htmlspecialchars($file['category'] ?? 'Uncategorized') ?> • <?= strtoupper($extension ?: 'file') ?></p>
                  </div>
                  <span class="shrink-0 text-xs font-medium text-slate-400 whitespace-nowrap"><?= htmlspecialchars($relativeTime) ?></span>
                </div>
                <div class="mt-2 flex items-center justify-between gap-2">
                  <p class="text-[11px] text-slate-600 truncate">Uploaded by <span class="font-medium text-slate-900"><?= htmlspecialchars($uploaderName) ?></span></p>
                  <?php if ($statusBg !== ''): ?>
                    <span class="ml-1 shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.16em] <?= $statusBg ?>"><?= htmlspecialchars(ucfirst($specialState)) ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      <a href="index.php?controller=Files&action=files" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-sky-700 transition hover:text-sky-900">
        <span>View repository</span>
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </a>
    </section>
  </div>
</div>
