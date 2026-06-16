<?php
// Car Bookings Calendar (Core Portal)
// Requires: CarBookingsController@calendar
?>

<div class="space-y-4">
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div class="min-w-0">
      <h1 class="text-[15px] font-semibold text-slate-900">Car bookings</h1>
      <p class="text-xs text-slate-500">Drag “New schedule” into the calendar or create via quick form.</p>
    </div>

        <div class="flex items-center gap-2">
          <div class="hidden sm:block">
            <div id="external-events" class="flex flex-col gap-2">
              <div class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs shadow-sm">
                <span class="text-slate-600">⟡</span>
                <span class="text-slate-700 font-medium">New schedule</span>
              </div>
            </div>
          </div>

          <button
            type="button"
            id="openVehicleModalBtn"
            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition">
            <span aria-hidden>＋</span>
            <span>Add vehicle</span>
          </button>

          <button
            type="button"
            id="openDriversModalBtn"
            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition">
            <span aria-hidden>👤</span>
            <span>Manage drivers</span>
          </button>
        </div>

  </div>

  <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-2 px-1 pb-2">
      <div class="flex items-center gap-2">
        <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-2 py-1">
          <label class="text-[11px] text-slate-500">View</label>
          <select
            id="calendarViewSelect"
            class="bg-transparent text-xs text-slate-800 focus:outline-none">
            <option value="dayGridMonth">Month</option>
            <option value="timeGridWeek">Week</option>
            <option value="timeGridDay">Day</option>
          </select>
        </div>

        <button
          type="button"
          id="refreshBtn"
          class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition">
          Refresh
        </button>
      </div>

      <div class="flex items-center gap-2">
        <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-2 py-1">
          <label class="text-[11px] text-slate-500">Vehicle</label>
          <select id="vehicleFilter" class="bg-transparent text-xs text-slate-800 focus:outline-none">
            <option value="">All</option>
            <?php foreach (($vehicles ?? []) as $v): ?>
              <option value="<?= (int)$v['id'] ?>"><?= htmlspecialchars($v['vehicle_name'] . ' (' . $v['plate_number'] . ')') ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-2 py-1">
          <label class="text-[11px] text-slate-500">Driver</label>
          <select id="driverFilter" class="bg-transparent text-xs text-slate-800 focus:outline-none">
            <option value="">All</option>
            <?php foreach (($drivers ?? []) as $d): ?>
              <option value="<?= (int)$d['id'] ?>"><?= htmlspecialchars($d['driver_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>

    <div id="carBookingCalendar" class="min-h-[520px]"></div>
  </div>
</div>

<!-- Booking Modal -->
<div id="bookingModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-slate-900/30 backdrop-blur-sm" id="bookingModalBackdrop"></div>

  <div class="relative mx-auto my-6 w-[95vw] max-w-3xl rounded-2xl border border-slate-200 bg-white shadow-[0_30px_120px_rgba(2,6,23,0.35)]">
    <div class="flex items-start justify-between gap-3 border-b border-slate-200 px-5 py-4">
      <div>
        <h2 class="text-sm font-semibold text-slate-900">Create booking</h2>
        <p class="text-xs text-slate-500">Minimal inputs, fast submission.</p>
      </div>
      <button type="button" id="closeBookingModalBtn" class="rounded-xl p-2 hover:bg-slate-100 transition" aria-label="Close">
        ✕
      </button>
    </div>

    <form id="bookingForm" class="px-5 py-4" method="POST" action="">
      <input type="hidden" name="id" id="bookingId" value="">

      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div>
          <label class="text-[11px] font-medium text-slate-600">Date of trip</label>
          <input type="date" name="date_trip" id="dateTrip" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sky-200" required />
        </div>

        <div>
          <label class="text-[11px] font-medium text-slate-600">Date requested</label>
          <input type="date" name="date_requested" id="dateRequested" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sky-200" required />
        </div>

        <div class="sm:col-span-2">
          <label class="text-[11px] font-medium text-slate-600">Destinations</label>
          <input type="text" name="destinations" id="destinations" placeholder="e.g., City A, City B" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sky-200" required />
        </div>

        <div class="sm:col-span-2">
          <label class="text-[11px] font-medium text-slate-600">Purpose</label>
          <input type="text" name="purpose" id="purpose" placeholder="Short purpose" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sky-200" required />
        </div>

        <div>
          <label class="text-[11px] font-medium text-slate-600">Passengers</label>
          <input type="number" name="passengers" id="passengers" min="1" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sky-200" required />
        </div>

        <div>
          <label class="text-[11px] font-medium text-slate-600">Departure (expected)</label>
          <input type="date" name="departure_expected" id="departureExpected" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sky-200" required />
        </div>

        <div>
          <label class="text-[11px] font-medium text-slate-600">Return (expected)</label>
          <input type="date" name="return_expected" id="returnExpected" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sky-200" required />
        </div>

        <div>
          <label class="text-[11px] font-medium text-slate-600">Assigned vehicle</label>
          <select name="vehicle_id" id="vehicleId" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sky-200" required>
            <option value="">Select</option>
            <?php foreach (($vehicles ?? []) as $v): ?>
              <option value="<?= (int)$v['id'] ?>"><?= htmlspecialchars($v['vehicle_name'] . ' - ' . $v['plate_number']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="text-[11px] font-medium text-slate-600">Assigned driver</label>
          <select name="driver_id" id="driverId" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sky-200" required>
            <option value="">Select</option>
            <?php foreach (($drivers ?? []) as $d): ?>
              <option value="<?= (int)$d['id'] ?>"><?= htmlspecialchars($d['driver_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="sm:col-span-2">
          <label class="text-[11px] font-medium text-slate-600">Special instructions</label>
          <textarea name="special_instructions" id="specialInstructions" rows="2" class="mt-1 w-full resize-y rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sky-200"></textarea>
        </div>

        <div class="sm:col-span-2">
          <label class="text-[11px] font-medium text-slate-600">Remarks</label>
          <textarea name="remarks" id="remarks" rows="2" class="mt-1 w-full resize-y rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sky-200"></textarea>
        </div>
      </div>

      <div class="mt-4 flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div class="text-xs text-slate-500">Fields are stored globally and visible for future tracking.</div>
        <div class="flex gap-2">
          <button type="button" id="cancelBookingModalBtn" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 transition">Cancel</button>
          <button type="submit" class="rounded-xl bg-sky-700 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-800 transition">Save booking</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Vehicle Modal -->
<div id="vehicleModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-slate-900/30 backdrop-blur-sm" id="vehicleModalBackdrop"></div>
  <div class="relative mx-auto my-6 w-[95vw] max-w-3xl rounded-2xl border border-slate-200 bg-white shadow-[0_30px_120px_rgba(2,6,23,0.35)]">
    <div class="flex items-start justify-between gap-3 border-b border-slate-200 px-5 py-4">
      <div>
        <h2 class="text-sm font-semibold text-slate-900">Manage vehicles</h2>
        <p class="text-xs text-slate-500">Add/edit and activate/deactivate.</p>
      </div>
      <button type="button" id="closeVehicleModalBtn" class="rounded-xl p-2 hover:bg-slate-100 transition" aria-label="Close">
        ✕
      </button>
    </div>

    <div class="px-5 py-4">
      <form id="vehicleForm" class="grid grid-cols-1 gap-3 sm:grid-cols-3 items-end" method="POST" action="">
        <input type="hidden" name="id" id="vehicleIdInput" value="">

        <div class="sm:col-span-2">
          <label class="text-[11px] font-medium text-slate-600">Plate number</label>
          <input type="text" name="plate_number" id="plateNumber" placeholder="e.g., ABC-123" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sky-200" required />
        </div>

        <div>
          <label class="text-[11px] font-medium text-slate-600">Capacity</label>
          <input type="number" name="capacity" id="capacity" min="1" placeholder="Seats" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sky-200" required />
        </div>

        <div class="sm:col-span-2">
          <label class="text-[11px] font-medium text-slate-600">Vehicle name</label>
          <input type="text" name="vehicle_name" id="vehicleName" placeholder="e.g., Toyota Hiace" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sky-200" required />
        </div>

        <div>
          <label class="text-[11px] font-medium text-slate-600">Status</label>
          <select name="status" id="vehicleStatus" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sky-200" required>
            <option value="active" selected>Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>

        <div class="sm:col-span-3 flex items-center gap-2 justify-end">
          <button type="button" id="resetVehicleFormBtn" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 transition">Reset</button>
          <button type="submit" class="rounded-xl bg-sky-700 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-800 transition">Save vehicle</button>
        </div>
      </form>

      <div class="mt-5 rounded-2xl border border-slate-200 overflow-hidden">
        <div class="bg-slate-50 px-4 py-3 flex items-center justify-between gap-2">
          <div class="text-xs font-semibold text-slate-700">Vehicles</div>
          <div class="flex items-center gap-2">
            <select id="vehiclesListStatusFilter" class="bg-white text-xs text-slate-800 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-200">
              <option value="">All</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
        <div class="max-h-[320px] overflow-auto">
          <table class="w-full text-left text-xs">
            <thead class="sticky top-0 bg-white border-b border-slate-200">
              <tr>
                <th class="px-4 py-3 font-semibold text-slate-600">Vehicle</th>
                <th class="px-4 py-3 font-semibold text-slate-600">Status</th>
                <th class="px-4 py-3 font-semibold text-slate-600 text-right">Actions</th>
              </tr>
            </thead>
            <tbody id="vehiclesTableBody">
              <tr>
                <td colspan="3" class="px-4 py-4 text-slate-500">Loading…</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>


<!-- Drivers Modal -->
<div id="driversModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-slate-900/30 backdrop-blur-sm" id="driversModalBackdrop"></div>
  <div class="relative mx-auto my-6 w-[95vw] max-w-3xl rounded-2xl border border-slate-200 bg-white shadow-[0_30px_120px_rgba(2,6,23,0.35)]">
    <div class="flex items-start justify-between gap-3 border-b border-slate-200 px-5 py-4">
      <div>
        <h2 class="text-sm font-semibold text-slate-900">Manage drivers</h2>
        <p class="text-xs text-slate-500">Add/edit and activate/deactivate.</p>
      </div>
      <button type="button" id="closeDriversModalBtn" class="rounded-xl p-2 hover:bg-slate-100 transition" aria-label="Close">
        ✕
      </button>
    </div>

    <div class="px-5 py-4">
      <form id="driverForm" class="grid grid-cols-1 gap-3 sm:grid-cols-3 items-end">
        <input type="hidden" name="id" id="driverId">

        <div class="sm:col-span-2">
          <label class="text-[11px] font-medium text-slate-600">Driver name</label>
          <input type="text" name="driver_name" id="driverNameInput" placeholder="e.g., John Doe" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sky-200" required />
        </div>

        <div>
          <label class="text-[11px] font-medium text-slate-600">Status</label>
          <select name="status" id="driverStatusInput" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-sky-200" required>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>

        <div class="sm:col-span-3 flex items-center gap-2 justify-end">
          <button type="button" id="resetDriverFormBtn" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 transition">Reset</button>
          <button type="submit" class="rounded-xl bg-sky-700 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-800 transition">Save driver</button>
        </div>
      </form>

      <div class="mt-5 rounded-2xl border border-slate-200 overflow-hidden">
        <div class="bg-slate-50 px-4 py-3 flex items-center justify-between gap-2">
          <div class="text-xs font-semibold text-slate-700">Drivers</div>
          <div class="flex items-center gap-2">
            <select id="driversListStatusFilter" class="bg-white text-xs text-slate-800 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-200">
              <option value="">All</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
        <div class="max-h-[320px] overflow-auto">
          <table class="w-full text-left text-xs">
            <thead class="sticky top-0 bg-white border-b border-slate-200">
              <tr>
                <th class="px-4 py-3 font-semibold text-slate-600">Name</th>
                <th class="px-4 py-3 font-semibold text-slate-600">Status</th>
                <th class="px-4 py-3 font-semibold text-slate-600 text-right">Actions</th>
              </tr>
            </thead>
            <tbody id="driversTableBody">
              <tr>
                <td colspan="3" class="px-4 py-4 text-slate-500">Loading…</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- External drag setup + FullCalendar -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
  (function () {

    const calendarEl = document.getElementById('carBookingCalendar');
    if (!calendarEl) return;

    // External “New schedule” draggable
    const externalEventsEl = document.getElementById('external-events');
    const newScheduleEl = externalEventsEl ? externalEventsEl.querySelector('.inline-flex') : null;

    let selectedDropDate = null;

    const bookingModalEl = document.getElementById('bookingModal');
    const bookingForm = document.getElementById('bookingForm');
    const bookingId = document.getElementById('bookingId');

    function openModal(modalEl) {
      modalEl.classList.remove('hidden');
      modalEl.classList.add('block');
      document.body.style.overflow = 'hidden';
    }

    function closeModal(modalEl) {
      modalEl.classList.add('hidden');
      modalEl.classList.remove('block');
      document.body.style.overflow = '';
    }

    const closeBookingModalBtn = document.getElementById('closeBookingModalBtn');
    const cancelBookingModalBtn = document.getElementById('cancelBookingModalBtn');
    const bookingModalBackdrop = document.getElementById('bookingModalBackdrop');

    function closeBooking() { closeModal(bookingModalEl); }

    closeBookingModalBtn.addEventListener('click', closeBooking);
    cancelBookingModalBtn.addEventListener('click', closeBooking);
    bookingModalBackdrop.addEventListener('click', closeBooking);

    function setBookingDefaults(dateStr) {
      const today = new Date();
      const d = dateStr ? new Date(dateStr) : today;

      const toISODate = (dt) => {
        const y = dt.getFullYear();
        const m = String(dt.getMonth() + 1).padStart(2, '0');
        const day = String(dt.getDate()).padStart(2, '0');
        return `${y}-${m}-${day}`;
      };

      document.getElementById('dateTrip').value = toISODate(d);
      document.getElementById('dateRequested').value = toISODate(today);
      document.getElementById('departureExpected').value = toISODate(d);
      const ret = new Date(d);
      ret.setDate(ret.getDate() + 1);
      document.getElementById('returnExpected').value = toISODate(ret);

      bookingId.value = '';
    }

    // Vehicle modal
    const vehicleModalEl = document.getElementById('vehicleModal');
    const openVehicleModalBtn = document.getElementById('openVehicleModalBtn');
    const closeVehicleModalBtn = document.getElementById('closeVehicleModalBtn');
    const resetVehicleFormBtn = document.getElementById('resetVehicleFormBtn');
    const vehicleModalBackdrop = document.getElementById('vehicleModalBackdrop');

    const vehicleIdInputEl = document.getElementById('vehicleIdInput');
    const plateNumberInputEl = document.getElementById('plateNumber');
    const vehicleNameInputEl = document.getElementById('vehicleName');
    const capacityInputEl = document.getElementById('capacity');
    const vehicleStatusInputEl = document.getElementById('vehicleStatus');

    const vehiclesModalForm = document.getElementById('vehicleForm');
    const vehiclesTableBodyEl = document.getElementById('vehiclesTableBody');
    const vehiclesListStatusFilterEl = document.getElementById('vehiclesListStatusFilter');

    function closeVehicle() { closeModal(vehicleModalEl); }

    if (openVehicleModalBtn) {
      openVehicleModalBtn.addEventListener('click', () => {
        openModal(vehicleModalEl);
        loadVehiclesTable();
      });
    }

    if (closeVehicleModalBtn) closeVehicleModalBtn.addEventListener('click', closeVehicle);
    if (vehicleModalBackdrop) vehicleModalBackdrop.addEventListener('click', closeVehicle);


    // JS endpoints (should match controller routes)
    const listUrl = 'index.php?controller=CarBookings&action=list';
    const createBookingUrl = 'index.php?controller=CarBookings&action=create';
    const createVehicleUrl = 'index.php?controller=Vehicles&action=create';
    const updateVehicleUrl = 'index.php?controller=Vehicles&action=update';
    const deleteVehicleUrl = 'index.php?controller=Vehicles&action=delete';
    const vehicleOptionsUrl = 'index.php?controller=Vehicles&action=listAjax';
    const vehicleListAllUrl = 'index.php?controller=Vehicles&action=listAllAjax';


    const driversListUrl = 'index.php?controller=Drivers&action=listAjax';
    const createDriverUrl = 'index.php?controller=Drivers&action=create';
    const updateDriverUrl = 'index.php?controller=Drivers&action=update';
    const deleteDriverUrl = 'index.php?controller=Drivers&action=delete';

    const vehicleFilter = document.getElementById('vehicleFilter');
    const driverFilter = document.getElementById('driverFilter');
    const refreshBtn = document.getElementById('refreshBtn');

    const calendarViewSelect = document.getElementById('calendarViewSelect');

    // Drivers modal
    const driversModalEl = document.getElementById('driversModal');
    const openDriversModalBtn = document.getElementById('openDriversModalBtn');
    const closeDriversModalBtn = document.getElementById('closeDriversModalBtn');
    const driversModalBackdrop = document.getElementById('driversModalBackdrop');

    const driverForm = document.getElementById('driverForm');
    const driverIdEl = document.getElementById('driverId');
    const driverNameInputEl = document.getElementById('driverNameInput');
    const driverStatusInputEl = document.getElementById('driverStatusInput');
    const resetDriverFormBtn = document.getElementById('resetDriverFormBtn');

    const driversTableBodyEl = document.getElementById('driversTableBody');
    const driversListStatusFilterEl = document.getElementById('driversListStatusFilter');

    function setDriversModalFormFromRow(row) {
      driverIdEl.value = row ? String(row.id) : '';
      driverNameInputEl.value = row ? row.driver_name : '';
      driverStatusInputEl.value = row ? row.status : 'active';
    }

    function renderDriversTable(rows) {

      if (!driversTableBodyEl) return;
      if (!rows || rows.length === 0) {
        driversTableBodyEl.innerHTML = '<tr><td colspan="3" class="px-4 py-4 text-slate-500">No drivers found</td></tr>';
        return;
      }

      driversTableBodyEl.innerHTML = '';
      rows.forEach(row => {
        const tr = document.createElement('tr');
        tr.className = 'border-b border-slate-100';
        const statusLabel = row.status === 'inactive' ? 'Inactive' : 'Active';

        tr.innerHTML = `
          <td class="px-4 py-3">
            <div class="font-medium text-slate-800">${escapeHtml(row.driver_name)}</div>
          </td>
          <td class="px-4 py-3">
            <span class="inline-flex items-center rounded-full px-2 py-1 text-[11px] ${row.status === 'active' ? 'bg-emerald-50 text-emerald-800' : 'bg-slate-100 text-slate-700'}">
              ${statusLabel}
            </span>
          </td>
          <td class="px-4 py-3 text-right">
            <div class="inline-flex items-center gap-2">
              <button type="button" class="driver-edit-btn rounded-xl border border-slate-200 bg-white px-3 py-1 text-[11px] font-medium text-slate-700 hover:bg-slate-50 transition" data-id="${row.id}">Edit</button>
              <button type="button" class="driver-delete-btn rounded-xl bg-rose-50 px-3 py-1 text-[11px] font-semibold text-rose-700 hover:bg-rose-100 transition" data-id="${row.id}">Disable</button>
            </div>
          </td>
        `;

        driversTableBodyEl.appendChild(tr);
      });

      driversTableBodyEl.querySelectorAll('.driver-edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const id = parseInt(btn.getAttribute('data-id'), 10);
          const row = rows.find(r => Number(r.id) === id);
          setDriversModalFormFromRow(row);
        });
      });

      driversTableBodyEl.querySelectorAll('.driver-delete-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const id = parseInt(btn.getAttribute('data-id'), 10);
          const row = rows.find(r => Number(r.id) === id);
          const name = row ? row.driver_name : 'this driver';
          if (!confirm(`Disable driver '${name}'?`)) return;

          const fd = new FormData();
          fd.append('id', id);

          fetch(deleteDriverUrl, {
            method: 'POST',
            body: fd,
            headers: {'X-Requested-With': 'XMLHttpRequest'}
          })
          .then(r => r.json())
          .then(resp => {
            if (!resp || !resp.success) {
              alert(resp && resp.message ? resp.message : 'Failed to disable driver');
              return;
            }
            // Reload table + refresh booking driver dropdown
            loadDriversTable();
            refreshDriverFilterDropdown();
            refreshBookingDriverSelect();
            setDriversModalFormFromRow(null);
          })
          .catch(() => alert('Network error while disabling driver'));
        });
      });
    }

    function escapeHtml(str) {
      return String(str)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '<')
        .replaceAll('>', '>')
        .replaceAll('"', '"')
        .replaceAll("'", '&#039;');
    }

    function loadDriversTable() {

      if (!driversTableBodyEl) return;
      const filterStatus = driversListStatusFilterEl ? driversListStatusFilterEl.value : '';
      driversTableBodyEl.innerHTML = '<tr><td colspan="3" class="px-4 py-4 text-slate-500">Loading…</td></tr>';

      const url = driversListUrl + (filterStatus ? ('&status=' + encodeURIComponent(filterStatus)) : '') + '&t=' + Date.now();
      fetch(url, {
        method: 'GET',
        headers: {'X-Requested-With': 'XMLHttpRequest'}
      })
      .then(r => r.json())
      .then(data => {
        const drivers = data && Array.isArray(data.drivers) ? data.drivers : [];
        renderDriversTable(drivers);
      })
      .catch(() => {
        driversTableBodyEl.innerHTML = '<tr><td colspan="3" class="px-4 py-4 text-slate-500">Failed to load</td></tr>';
      });
    }

    function refreshDriverFilterDropdown() {
      if (!driverFilter) return;
      fetch(driversListUrl + '&t=' + Date.now(), {
        method: 'GET',
        headers: {'X-Requested-With': 'XMLHttpRequest'}
      })
      .then(r => r.json())
      .then(data => {
        const drivers = data && Array.isArray(data.drivers) ? data.drivers : [];
        driverFilter.innerHTML = '<option value="">All</option>';
        drivers.filter(d => d.status === 'active').forEach(d => {
          const opt = document.createElement('option');
          opt.value = d.id;
          opt.textContent = d.driver_name;
          driverFilter.appendChild(opt);
        });
      })
      .catch(() => {});
    }

    function refreshBookingDriverSelect() {
      // Booking modal uses the same "driverId" <select>
      const bookingDriverSelect = document.getElementById('driverId');
      if (!bookingDriverSelect) return;

      fetch(driversListUrl + '&t=' + Date.now(), {
        method: 'GET',
        headers: {'X-Requested-With': 'XMLHttpRequest'}
      })
      .then(r => r.json())
      .then(data => {
        const drivers = data && Array.isArray(data.drivers) ? data.drivers : [];
        const activeDrivers = drivers.filter(d => d.status === 'active');
        const keep = bookingDriverSelect.value;

        bookingDriverSelect.innerHTML = '<option value="">Select</option>';
        activeDrivers.forEach(d => {
          const opt = document.createElement('option');
          opt.value = d.id;
          opt.textContent = d.driver_name;
          bookingDriverSelect.appendChild(opt);
        });

        if (keep && activeDrivers.some(d => String(d.id) === String(keep))) {
          bookingDriverSelect.value = keep;
        }
      })
      .catch(() => {});
    }

    // Vehicles modal helpers
    function escapeHtml(str) {
      return String(str)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '<')
        .replaceAll('>', '>')
        .replaceAll('"', '"')
        .replaceAll("'", '&#039;');
    }

    function setVehicleModalFormFromRow(row) {
      if (!vehiclesModalForm || !vehicleIdInputEl) return;
      vehicleIdInputEl.value = row ? String(row.id) : '';
      plateNumberInputEl.value = row ? row.plate_number : '';
      vehicleNameInputEl.value = row ? row.vehicle_name : '';
      capacityInputEl.value = row ? row.capacity : '';
      vehicleStatusInputEl.value = row ? row.status : 'active';
    }

    function renderVehiclesTable(rows) {
      if (!vehiclesTableBodyEl) return;
      if (!rows || rows.length === 0) {
        vehiclesTableBodyEl.innerHTML = '<tr><td colspan="3" class="px-4 py-4 text-slate-500">No vehicles found</td></tr>';
        return;
      }

      vehiclesTableBodyEl.innerHTML = '';
      rows.forEach(row => {
        const tr = document.createElement('tr');
        tr.className = 'border-b border-slate-100';
        const statusLabel = row.status === 'inactive' ? 'Inactive' : 'Active';

        tr.innerHTML = `
          <td class="px-4 py-3">
            <div class="font-medium text-slate-800">${escapeHtml(row.vehicle_name)}</div>
            <div class="text-[11px] text-slate-500">${escapeHtml(row.plate_number)}</div>
          </td>
          <td class="px-4 py-3">
            <span class="inline-flex items-center rounded-full px-2 py-1 text-[11px] ${row.status === 'active' ? 'bg-emerald-50 text-emerald-800' : 'bg-slate-100 text-slate-700'}">
              ${statusLabel}
            </span>
          </td>
          <td class="px-4 py-3 text-right">
            <div class="inline-flex items-center gap-2">
              <button type="button" class="vehicle-edit-btn rounded-xl border border-slate-200 bg-white px-3 py-1 text-[11px] font-medium text-slate-700 hover:bg-slate-50 transition" data-id="${row.id}">Edit</button>
              <button type="button" class="vehicle-delete-btn rounded-xl bg-rose-50 px-3 py-1 text-[11px] font-semibold text-rose-700 hover:bg-rose-100 transition" data-id="${row.id}">Disable</button>
            </div>
          </td>
        `;

        vehiclesTableBodyEl.appendChild(tr);
      });

      vehiclesTableBodyEl.querySelectorAll('.vehicle-edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const id = parseInt(btn.getAttribute('data-id'), 10);
          const row = rows.find(r => Number(r.id) === id);
          setVehicleModalFormFromRow(row);
        });
      });

      vehiclesTableBodyEl.querySelectorAll('.vehicle-delete-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const id = parseInt(btn.getAttribute('data-id'), 10);
          const row = rows.find(r => Number(r.id) === id);
          const label = row ? `${row.vehicle_name} (${row.plate_number})` : 'this vehicle';
          if (!confirm(`Disable vehicle '${label}'?`)) return;

          const fd = new FormData();
          fd.append('id', id);

          fetch(deleteVehicleUrl, {
            method: 'POST',
            body: fd,
            headers: {'X-Requested-With': 'XMLHttpRequest'}
          })
          .then(r => r.json())
          .then(resp => {
            if (!resp || !resp.success) {
              alert(resp && resp.message ? resp.message : 'Failed to disable vehicle');
              return;
            }
            loadVehiclesTable();
            refreshVehicleFilterDropdown();
            refreshBookingVehicleSelect();
            setVehicleModalFormFromRow(null);
          })
          .catch(() => alert('Network error while disabling vehicle'));
        });
      });
    }

    function loadVehiclesTable() {
      if (!vehiclesTableBodyEl) return;
      const filterStatus = vehiclesListStatusFilterEl ? vehiclesListStatusFilterEl.value : '';
      vehiclesTableBodyEl.innerHTML = '<tr><td colspan="3" class="px-4 py-4 text-slate-500">Loading…</td></tr>';

      const url = vehicleListAllUrl + (filterStatus ? ('&status=' + encodeURIComponent(filterStatus)) : '') + '&t=' + Date.now();

      fetch(url, {
        method: 'GET',
        headers: {'X-Requested-With': 'XMLHttpRequest'}
      })
      .then(r => r.json())
      .then(data => {
        const vehicles = data && Array.isArray(data.vehicles) ? data.vehicles : [];
        renderVehiclesTable(vehicles);
      })
      .catch(() => {
        vehiclesTableBodyEl.innerHTML = '<tr><td colspan="3" class="px-4 py-4 text-slate-500">Failed to load</td></tr>';
      });
    }

    function refreshVehicleFilterDropdown() {
      // vehicleFilter in top toolbar
      if (!vehicleFilter) return;
      fetch(vehicleOptionsUrl + '&t=' + Date.now(), {
        method: 'GET',
        headers: {'X-Requested-With': 'XMLHttpRequest'}
      })
      .then(r => r.json())
      .then(data => {
        const vehicles = data && Array.isArray(data.vehicles) ? data.vehicles : [];
        const keep = vehicleFilter.value;

        vehicleFilter.innerHTML = '<option value="">All</option>';
        vehicles.forEach(v => {
          const opt = document.createElement('option');
          opt.value = v.id;
          opt.textContent = `${v.vehicle_name} (${v.plate_number})`;
          vehicleFilter.appendChild(opt);
        });

        if (keep && vehicles.some(v => String(v.id) === String(keep))) {
          vehicleFilter.value = keep;
        }
      })
      .catch(() => {});
    }

    function refreshBookingVehicleSelect() {
      // Booking modal uses the same "vehicleId" <select>
      const bookingVehicleSelect = document.getElementById('vehicleId');
      if (!bookingVehicleSelect) return;

      fetch(vehicleOptionsUrl + '&t=' + Date.now(), {
        method: 'GET',
        headers: {'X-Requested-With': 'XMLHttpRequest'}
      })
      .then(r => r.json())
      .then(data => {
        const vehicles = data && Array.isArray(data.vehicles) ? data.vehicles : [];
        const keep = bookingVehicleSelect.value;

        bookingVehicleSelect.innerHTML = '<option value="">Select</option>';
        vehicles.forEach(v => {
          const opt = document.createElement('option');
          opt.value = v.id;
          opt.textContent = `${v.vehicle_name} - ${v.plate_number}`;
          bookingVehicleSelect.appendChild(opt);
        });

        if (keep && vehicles.some(v => String(v.id) === String(keep))) {
          bookingVehicleSelect.value = keep;
        }
      })
      .catch(() => {});
    }


    if (openDriversModalBtn) {
      openDriversModalBtn.addEventListener('click', () => {
        openModal(driversModalEl);
        loadDriversTable();
      });
    }
    if (closeDriversModalBtn) closeDriversModalBtn.addEventListener('click', () => closeModal(driversModalEl));
    if (driversModalBackdrop) driversModalBackdrop.addEventListener('click', () => closeModal(driversModalEl));
    if (resetDriverFormBtn) {
      resetDriverFormBtn.addEventListener('click', () => setDriversModalFormFromRow(null));
    }
    if (driversListStatusFilterEl) {
      driversListStatusFilterEl.addEventListener('change', () => loadDriversTable());
    }

    if (driverForm) {
      driverForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const id = driverIdEl.value ? parseInt(driverIdEl.value, 10) : null;
        const fd = new FormData();
        fd.append('driver_name', driverNameInputEl.value);
        fd.append('status', driverStatusInputEl.value);

        if (id) {
          fd.append('id', id);
          fetch(updateDriverUrl, {
            method: 'POST',
            body: fd,
            headers: {'X-Requested-With': 'XMLHttpRequest'}
          })
          .then(r => r.json())
          .then(resp => {
            if (!resp || !resp.success) {
              alert(resp && resp.message ? resp.message : 'Failed to update driver');
              return;
            }
            closeModal(driversModalEl);
            loadDriversTable();
            refreshDriverFilterDropdown();
            refreshBookingDriverSelect();
            setDriversModalFormFromRow(null);
          })
          .catch(() => alert('Network error while updating driver'));
        } else {
          fetch(createDriverUrl, {
            method: 'POST',
            body: fd,
            headers: {'X-Requested-With': 'XMLHttpRequest'}
          })
          .then(r => r.json())
          .then(resp => {
            if (!resp || !resp.success) {
              alert(resp && resp.message ? resp.message : 'Failed to create driver');
              return;
            }
            closeModal(driversModalEl);
            loadDriversTable();
            refreshDriverFilterDropdown();
            refreshBookingDriverSelect();
            setDriversModalFormFromRow(null);
          })
          .catch(() => alert('Network error while creating driver'));
        }
      });
    }


    const calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      height: 'auto',
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
      },
      selectable: true,
      editable: false,
      droppable: true,
      eventTimeFormat: {
        hour: '2-digit',
        minute: '2-digit',
        meridiem: false
      },
      eventDisplay: 'block',
      eventBackgroundColor: '#0c4a6e',
      eventBorderColor: '#0c4a6e',
      eventTextColor: '#ffffff',

      eventSources: [
        {
          events: function (fetchInfo, successCallback, failureCallback) {
            const payload = new URLSearchParams({
              start: fetchInfo.startStr,
              end: fetchInfo.endStr,
              vehicle_id: vehicleFilter.value || '',
              driver_id: driverFilter.value || ''
            });

            fetch(listUrl + '&' + payload.toString(), {
              method: 'GET',
              headers: {'X-Requested-With': 'XMLHttpRequest'}
            })
              .then(r => r.json())
              .then(data => {
                if (!data || !Array.isArray(data.events)) {
                  successCallback([]);
                  return;
                }
                successCallback(data.events);
              })
              .catch(() => failureCallback());
          }
        }
      ],

      dateClick: function (info) {
        selectedDropDate = info.dateStr;
        setBookingDefaults(info.dateStr);
        openModal(bookingModalEl);
      },

      drop: function (info) {
        // Only supports external “New schedule” drop
        if (!newScheduleEl) return;
        if (!info.date) return;

        selectedDropDate = info.dateStr;
        setBookingDefaults(info.dateStr);
        openModal(bookingModalEl);
      }
    });

    calendar.render();

    // External drag init
    if (newScheduleEl) {
      new FullCalendar.Draggable(externalEventsEl, {
        itemSelector: '.inline-flex',
        eventData: function () {
          return {
            title: 'New schedule',
            allDay: true
          };
        }
      });
    }

    calendarViewSelect.addEventListener('change', function () {
      const val = this.value;
      calendar.changeView(val);
    });

    refreshBtn.addEventListener('click', function () {
      calendar.refetchEvents();
    });

    vehicleFilter.addEventListener('change', () => calendar.refetchEvents());
    driverFilter.addEventListener('change', () => calendar.refetchEvents());

    // Booking form submit (AJAX)
    bookingForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const formData = new FormData(bookingForm);

      fetch(createBookingUrl, {
        method: 'POST',
        body: formData,
        headers: {'X-Requested-With': 'XMLHttpRequest'}
      })
      .then(r => r.json())
      .then(resp => {
        if (!resp || !resp.success) {
          alert(resp && resp.message ? resp.message : 'Failed to save booking');
          return;
        }
        closeBooking();
        calendar.refetchEvents();
      })
      .catch(() => alert('Network error while saving booking'));
    });

    // Vehicle form submit (AJAX) - create or update
    const vehicleForm = document.getElementById('vehicleForm');
    if (vehicleForm) {
      vehicleForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(vehicleForm);
        const id = vehicleIdInputEl && vehicleIdInputEl.value ? parseInt(vehicleIdInputEl.value, 10) : null;

        const url = id ? updateVehicleUrl : createVehicleUrl;

        fetch(url, {
          method: 'POST',
          body: formData,
          headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
        .then(r => r.json())
        .then(resp => {
          if (!resp || !resp.success) {
            alert(resp && resp.message ? resp.message : 'Failed to save vehicle');
            return;
          }

          closeVehicle();

          // Refresh both dropdowns + table
          loadVehiclesTable();
          refreshVehicleFilterDropdown();
          refreshBookingVehicleSelect();
          calendar.refetchEvents();

          setVehicleModalFormFromRow(null);
        })
        .catch(() => alert('Network error while saving vehicle'));
      });
    }

    if (resetVehicleFormBtn) {
      resetVehicleFormBtn.addEventListener('click', () => setVehicleModalFormFromRow(null));
    }


  })();
</script>

