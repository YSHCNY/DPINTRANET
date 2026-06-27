<div class="space-y-6">
  <div class="grid gap-6 xl:grid-cols-[2fr_1fr]">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
      <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div>
          <p class="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-700">Dashboard</p>
          <h1 class="mt-2 text-3xl font-semibold text-slate-900">Welcome back, <?= htmlspecialchars($firstName) ?>.</h1>
          <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Your correspondence analytics, fast actions, and module health at a glance.</p>
        </div>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-1">
          <div class="rounded-2xl bg-emerald-50 p-4 text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Response rate</p>
            <?php
              $totalRecipients = max(1, $correspondenceMetrics['total_recipients'] ?? 0);
              $receivedRecipients = $correspondenceMetrics['received_recipients'] ?? 0;
              $responseRate = round(($receivedRecipients / $totalRecipients) * 100);
            ?>
            <p class="mt-3 text-3xl font-semibold text-slate-900"><?= $responseRate ?>%</p>
            <p class="text-xs text-slate-500">Received from all recipients</p>
          </div>
          <div class="rounded-2xl bg-slate-900 p-4 text-center text-white">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-200">Active drafts</p>
            <p class="mt-3 text-3xl font-semibold"><?= $correspondenceMetrics['draft_documents'] ?? 0 ?></p>
            <p class="text-xs text-slate-300">Drafts waiting to finalize</p>
          </div>
          <div class="rounded-2xl bg-slate-50 p-4 text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Open actions</p>
            <p class="mt-3 text-3xl font-semibold text-slate-900"><?= $correspondenceMetrics['pending_recipients'] ?? 0 ?></p>
            <p class="text-xs text-slate-500">Pending recipient responses</p>
          </div>
        </div>
      </div>
    </section>

    <section class="space-y-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
      <div class="flex items-center justify-between gap-3">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Summary</p>
          <h2 class="text-xl font-semibold text-slate-900">Module health</h2>
        </div>
        <div class="hidden sm:flex items-center gap-3">
          <a href="index.php?controller=correspondence&action=correspondence" class="rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-100 transition">View correspondence</a>
        </div>
      </div>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
          <p class="text-sm font-semibold text-slate-700">Vehicle schedule</p>
          <p class="mt-4 text-3xl font-semibold text-slate-900"><?= $carMetrics['activeVehicles'] ?? 0 ?></p>
          <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Active fleet</p>
          <div class="mt-4 rounded-2xl bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Scheduled bookings</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900"><?= $carMetrics['scheduledBookings'] ?? 0 ?></p>
          </div>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
          <p class="text-sm font-semibold text-slate-700">Room schedule</p>
          <p class="mt-4 text-3xl font-semibold text-slate-900"><?= $roomMetrics['activeRooms'] ?? 0 ?></p>
          <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Active rooms</p>
          <div class="mt-4 rounded-2xl bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Scheduled bookings</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900"><?= $roomMetrics['scheduledBookings'] ?? 0 ?></p>
          </div>
        </div>
      </div>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
          <p class="text-sm font-semibold text-slate-700">Library</p>
          <p class="mt-4 text-3xl font-semibold text-slate-900"><?= $fileMetrics['total'] ?? 0 ?></p>
          <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Total uploads</p>
          <p class="mt-4 text-sm text-slate-500">Last 30 days: <span class="font-semibold text-slate-900"><?= $fileMetrics['recent30Days'] ?? 0 ?></span></p>
        </div>
      </div>
    </section>
  </div>

  <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Schedule overview</p>
        <h2 class="mt-2 text-xl font-semibold text-slate-900">Unified bookings calendar</h2>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">See room and car bookings together. Filter by type and switch between month, week, and day views.</p>
      </div>
      <div class="flex flex-wrap items-center gap-2">
        <button type="button" data-calendar-filter="all" class="calendar-filter-btn rounded-full border border-slate-200 bg-slate-900 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-slate-800">All</button>
        <button type="button" data-calendar-filter="car" class="calendar-filter-btn rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">Cars</button>
        <button type="button" data-calendar-filter="room" class="calendar-filter-btn rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">Rooms</button>
      </div>
    </div>

    <div class="mt-6 min-h-[520px] rounded-3xl border border-slate-100 bg-slate-50 p-3" id="dashboardCalendar"></div>
    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-sm text-slate-500">
      <p>Only scheduled room and vehicle bookings are included.</p>
      <p id="calendarEmptyState" class="hidden">No bookings found for the selected date range.</p>
    </div>

    <div id="dashboardEventModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 py-6">
      <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>
      <div class="relative w-full max-w-2xl overflow-hidden rounded-3xl bg-white p-6 shadow-2xl">
        <div class="flex items-start justify-between gap-4">
          <div>
            <p id="dashboardEventSource" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Booking type</p>
            <h3 id="dashboardEventTitle" class="mt-2 text-xl font-semibold text-slate-900">Event details</h3>
          </div>
          <button type="button" id="dashboardEventCloseBtn" class="text-slate-500 transition hover:text-slate-900">✕</button>
        </div>
        <div class="mt-6 grid gap-4 sm:grid-cols-2">
          <div class="space-y-3 rounded-3xl bg-slate-50 p-4">
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
          <div class="space-y-3 rounded-3xl bg-slate-50 p-4">
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
        <div class="mt-6 flex flex-wrap items-center gap-3">
          <a id="dashboardEventViewLink" href="#" target="_blank" class="rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">Open booking page</a>
          <button type="button" id="dashboardEventDismissBtn" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Close</button>
        </div>
      </div>
    </div>
  </section>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
  <script>
    (function () {
      const calendarEndpoint = 'index.php?controller=Auth&action=dashboardEvents';
      const dashboardCalendarEl = document.getElementById('dashboardCalendar');
      const filterButtons = Array.from(document.querySelectorAll('[data-calendar-filter]'));
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
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
          },
          height: 'auto',
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
                const events = Array.isArray(data.events) ? data.events : [];
                emptyState.classList.toggle('hidden', events.length > 0);
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

      setActiveFilter('all');
      initializeCalendar();
    })();
  </script>

  <div class="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
      <div class="flex items-center justify-between gap-4">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Correspondence</p>
          <h2 class="text-xl font-semibold text-slate-900">Recent documents</h2>
        </div>
        <a href="index.php?controller=correspondence&action=correspondence" class="text-sm font-semibold text-emerald-700 hover:text-emerald-900">View all</a>
      </div>
      <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200">
        <div class="grid grid-cols-12 gap-4 bg-slate-100 px-4 py-3 text-xs uppercase tracking-[0.18em] text-slate-500">
          <span class="col-span-3">Tracking ID</span>
          <span class="col-span-4">Title</span>
          <span class="col-span-2">Status</span>
          <span class="col-span-3">Recipients</span>
        </div>

        
        <?php if (empty($recentDocuments)): ?>
          <div class="px-4 py-6 text-center text-sm text-slate-500">No recent documents yet.</div>
        <?php else: ?>
          <?php foreach ($recentDocuments as $doc): ?>
            <a href="index.php?controller=correspondence&action=show&id=<?= (int)$doc['id'] ?>" class="grid grid-cols-12 gap-4 border-b border-slate-200 px-4 py-4 transition hover:bg-slate-50">
              <div class="col-span-3 min-w-0">
                <p class="text-sm font-semibold text-slate-900 truncate"><?= htmlspecialchars($doc['tracking_id'] ?? '—') ?></p>
                <p class="text-xs text-slate-500"><?= date('M d, Y', strtotime($doc['created_at'] ?? 'now')) ?></p>
              </div>
              <div class="col-span-4 min-w-0">
                <p class="text-sm font-semibold text-slate-900 truncate"><?= htmlspecialchars($doc['title'] ?? 'Untitled') ?></p>
                <p class="mt-1 text-xs text-slate-500"><?= htmlspecialchars($doc['type'] ?? 'N/A') ?> • <?= htmlspecialchars($doc['priority'] ?? 'Medium') ?></p>
              </div>
              <div class="col-span-2 flex items-center gap-2">
                <span class="rounded-full px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.15em] <?= (strtolower($doc['status']) === 'draft' || !empty($doc['is_draft'])) ? 'bg-amber-100 text-amber-700' : (strtolower($doc['status']) === 'inprogress' ? 'bg-sky-100 text-sky-700' : 'bg-slate-100 text-slate-700') ?>">
                  <?= htmlspecialchars(!empty($doc['is_draft']) ? 'Draft' : $doc['status']) ?>
                </span>
              </div>
              <div class="col-span-3 grid gap-2 text-sm">
                <div class="flex items-center justify-between text-slate-600">
                  <span>Received</span>
                  <span class="font-semibold text-slate-900"><?= (int)($doc['received_count'] ?? 0) ?></span>
                </div>
                <div class="flex items-center justify-between text-slate-600">
                  <span>Pending</span>
                  <span class="font-semibold text-slate-900"><?= (int)($doc['pending_count'] ?? 0) ?></span>
                </div>
              </div>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
      <div class="flex items-center justify-between gap-4">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Official File Repository</p>
          <h2 class="text-xl font-semibold text-slate-900">Latest updated files</h2>
        </div>
        <a href="index.php?controller=Files&action=files" class="text-sm font-semibold text-emerald-700 hover:text-emerald-900">View repository</a>
      </div>
      <div class="mt-6 space-y-3">
        <?php if (empty($latestFiles)): ?>
          <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-6 text-center text-sm text-slate-500">No recent file updates yet.</div>
        <?php else: ?>
          <?php foreach ($latestFiles as $file): ?>
            <?php
              $uploaderName = trim(($file['firstName'] ?? '') . ' ' . ($file['lastName'] ?? '')) ?: 'Unknown uploader';
              $uploadedAt = !empty($file['uploadedat']) ? date('M d, Y \a\t g:i A', strtotime($file['uploadedat'])) : 'Unknown date';
            ?>
            <a href="index.php?controller=Files&action=files" class="block rounded-3xl border border-slate-200 bg-slate-50 p-4 transition hover:border-slate-300 hover:bg-slate-100">
              <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                  <p class="text-sm font-semibold text-slate-900 truncate"><?= htmlspecialchars($file['filename'] ?? 'Untitled file') ?></p>
                  <p class="mt-1 text-xs text-slate-500 truncate"><?= htmlspecialchars($file['desc'] ?? 'No description provided') ?></p>
                </div>
                <div class="space-y-1 text-right text-xs text-slate-500 sm:text-left">
                  <p><?= htmlspecialchars($file['category'] ?? 'Uncategorized') ?></p>
                  <p><?= htmlspecialchars($uploaderName) ?></p>
                </div>
              </div>
              <div class="mt-3 flex items-center justify-between gap-3 text-xs text-slate-500">
                <span><?= htmlspecialchars($uploadedAt) ?></span>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-600">
                  Updated
                </span>
              </div>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>
  </div>
</div>
