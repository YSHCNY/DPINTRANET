<?php
// Car Bookings Calendar with Vehicle History Cards
// Requires: CarBookingsController@calendar
?>

<div class="space-y-6">
  <!-- Header Section -->
  <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Vehicle Bookings</h1>
      <p class="text-sm text-slate-500 mt-1">Manage bookings, vehicles, and drivers. Drag events to create new bookings.</p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
      <button type="button" id="openVehicleModalBtn" class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-100 transition shadow-sm">
        <span>🚗</span>
        <span>Add Vehicle</span>
      </button>
      <button type="button" id="openDriversModalBtn" class="inline-flex items-center gap-2 rounded-lg border border-sky-200 bg-sky-50 px-4 py-2 text-sm font-medium text-sky-700 hover:bg-sky-100 transition shadow-sm">
        <span>👤</span>
        <span>Manage Drivers</span>
      </button>
    </div>
  </div>

  <!-- Calendar Container -->
  <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <!-- Toolbar -->
    <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50 to-slate-100 px-6 py-4">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="inline-block">
            <div id="external-events" class="hidden sm:inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-200 bg-white text-sm shadow-sm">
              <span class="text-slate-400">⟡</span>
              <span class="text-slate-600 font-medium">Drag "New booking" to create</span>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
          <div class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 shadow-sm">
            <label class="text-xs font-medium text-slate-600">View:</label>
            <select id="calendarViewSelect" class="bg-transparent text-sm text-slate-700 font-medium focus:outline-none cursor-pointer">
              <option value="dayGridMonth">Month</option>
              <option value="timeGridWeek">Week</option>
              <option value="timeGridDay">Day</option>
            </select>
          </div>

          <button type="button" id="refreshBtn" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition shadow-sm">
            <span>🔄</span>
            <span>Refresh</span>
          </button>

          <div class="hidden sm:flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 shadow-sm">
            <label class="text-xs font-medium text-slate-600">Vehicle:</label>
            <select id="vehicleFilter" class="bg-transparent text-sm text-slate-700 font-medium focus:outline-none cursor-pointer">
              <option value="">All</option>
              <?php foreach (($vehicles ?? []) as $v): ?>
                <option value="<?= (int)$v['id'] ?>"><?= htmlspecialchars($v['vehicle_name'] . ' (' . $v['plate_number'] . ')') ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="hidden sm:flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 shadow-sm">
            <label class="text-xs font-medium text-slate-600">Driver:</label>
            <select id="driverFilter" class="bg-transparent text-sm text-slate-700 font-medium focus:outline-none cursor-pointer">
              <option value="">All</option>
              <?php foreach (($drivers ?? []) as $d): ?>
                <option value="<?= (int)$d['id'] ?>"><?= htmlspecialchars($d['driver_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Calendar -->
    <div id="carBookingCalendar" class="min-h-[600px] p-6"></div>
  </div>

  <!-- Vehicle Cards Section -->
  <div>
    <div class="mb-4">
      <h2 class="text-xl font-bold text-slate-900">Vehicle Fleet Summary</h2>
      <p class="text-sm text-slate-500 mt-1">Recent bookings and vehicle status</p>
    </div>

    <div id="vehicleCardsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm animate-pulse">
        <div class="h-6 bg-slate-200 rounded w-3/4 mb-4"></div>
        <div class="space-y-2">
          <div class="h-4 bg-slate-100 rounded w-1/2"></div>
          <div class="h-4 bg-slate-100 rounded w-2/3"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ============================================
     BOOKING MODAL
     ============================================ -->
<div id="bookingModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" id="bookingModalBackdrop"></div>

  <div class="relative mx-auto my-6 w-[95vw] max-w-4xl rounded-xl border border-slate-200 bg-white shadow-2xl overflow-hidden">
    <!-- Modal Header -->
    <div class="flex items-start justify-between gap-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-6 py-5">
      <div>
        <h2 class="text-lg font-semibold text-slate-900" id="bookingModalTitle">Create New Booking</h2>

        <p class="text-sm text-slate-500 mt-1">Fill in the details below to schedule a vehicle.</p>
      </div>
      <button type="button" id="closeBookingModalBtn" class="rounded-lg p-2 hover:bg-slate-100 transition" aria-label="Close">
        <span class="text-xl text-slate-400">✕</span>
      </button>
    </div>

    <!-- Modal Content -->
    <form id="bookingForm" class="px-6 py-6 max-h-[calc(100vh-200px)] overflow-y-auto" method="POST" action="">
      <input type="hidden" name="id" id="bookingId" value="">

      <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <!-- Date of Trip -->
        <div>
          <label class="text-sm font-semibold text-slate-700 block mb-2">Date of Trip <span class="text-red-500">*</span></label>
          <input type="datetime-local" name="date_trip" id="dateTrip" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-300 transition" required />
          <p class="text-xs text-slate-500 mt-1">When will the trip occur?</p>
        </div>

        <!-- Date Requested -->
        <div>
          <label class="text-sm font-semibold text-slate-700 block mb-2">Date Requested <span class="text-red-500">*</span></label>
          <input type="datetime-local" name="date_requested" id="dateRequested" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-300 transition" required />
          <p class="text-xs text-slate-500 mt-1">When was this booking requested?</p>
        </div>

        <!-- Destinations -->
        <div class="sm:col-span-2">
          <label class="text-sm font-semibold text-slate-700 block mb-2">Destinations <span class="text-red-500">*</span></label>
          <input type="text" name="destinations" id="destinations" placeholder="e.g., Downtown to Airport via City Hall" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-300 transition" required />
        </div>

        <!-- Purpose -->
        <div class="sm:col-span-2">
          <label class="text-sm font-semibold text-slate-700 block mb-2">Purpose <span class="text-red-500">*</span></label>
          <input type="text" name="purpose" id="purpose" placeholder="e.g., Business meeting, Client transport" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-300 transition" required />
        </div>

        <!-- Passengers -->
        <div>
          <label class="text-sm font-semibold text-slate-700 block mb-2">Number of Passengers <span class="text-red-500">*</span></label>
          <input type="number" name="passengers" id="passengers" min="1" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-300 transition" required />
        </div>

        <!-- Empty space -->
        <div></div>

        <!-- Departure Expected -->
        <div>
          <label class="text-sm font-semibold text-slate-700 block mb-2">Expected Departure <span class="text-red-500">*</span></label>
          <input type="datetime-local" name="departure_expected" id="departureExpected" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-300 transition" required />
          <p class="text-xs text-slate-500 mt-1">Cannot be in the past</p>
        </div>

        <!-- Return Expected -->
        <div>
          <label class="text-sm font-semibold text-slate-700 block mb-2">Expected Return <span class="text-red-500">*</span></label>
          <input type="datetime-local" name="return_expected" id="returnExpected" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-300 transition" required />
          <p class="text-xs text-slate-500 mt-1">Must be after departure</p>
        </div>

        <!-- Vehicle Selection -->
        <div>
          <label class="text-sm font-semibold text-slate-700 block mb-2">Assigned Vehicle <span class="text-red-500">*</span></label>
          <select name="vehicle_id" id="vehicleId" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-300 transition" required>
            <option value="">-- Select a vehicle --</option>
            <?php foreach (($vehicles ?? []) as $v): ?>
              <option value="<?= (int)$v['id'] ?>"><?= htmlspecialchars($v['vehicle_name'] . ' (' . $v['plate_number'] . ')') ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Driver Selection -->
        <div>
          <label class="text-sm font-semibold text-slate-700 block mb-2">Assigned Driver <span class="text-red-500">*</span></label>
          <select name="driver_id" id="driverId" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-300 transition" required>
            <option value="">-- Select a driver --</option>
            <?php foreach (($drivers ?? []) as $d): ?>
              <option value="<?= (int)$d['id'] ?>"><?= htmlspecialchars($d['driver_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Special Instructions -->
        <div class="sm:col-span-2">
          <label class="text-sm font-semibold text-slate-700 block mb-2">Special Instructions</label>
          <textarea name="special_instructions" id="specialInstructions" rows="3" placeholder="Any special requirements? (e.g., wheelchair accessible, extra luggage space)" class="w-full resize-none rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-300 transition"></textarea>
        </div>

        <!-- Remarks -->
        <div class="sm:col-span-2">
          <label class="text-sm font-semibold text-slate-700 block mb-2">Remarks</label>
          <textarea name="remarks" id="remarks" rows="3" placeholder="Additional notes about this booking..." class="w-full resize-none rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-300 transition"></textarea>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end border-t border-slate-100 pt-6">
        <button type="button" id="cancelBookingModalBtn" class="rounded-lg border border-slate-200 bg-white px-6 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">Cancel</button>
        <button type="button" id="deleteBookingBtn" class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100 transition hidden">Delete</button>
        <button type="submit" id="saveBookingBtn" class="rounded-lg bg-gradient-to-r from-sky-600 to-sky-700 px-6 py-2 text-sm font-semibold text-white hover:from-sky-700 hover:to-sky-800 transition shadow-md">
          <span>✓</span> Save Booking
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ============================================
     VEHICLE MODAL
     ============================================ -->
<div id="vehicleModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" id="vehicleModalBackdrop"></div>
  <div class="relative mx-auto my-6 w-[95vw] max-w-4xl rounded-xl border border-slate-200 bg-white shadow-2xl overflow-hidden">
    <div class="flex items-start justify-between gap-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-6 py-5">
      <div>
        <h2 class="text-lg font-semibold text-slate-900">Manage Vehicles</h2>
        <p class="text-sm text-slate-500 mt-1">Add new vehicles or modify existing ones</p>
      </div>
      <button type="button" id="closeVehicleModalBtn" class="rounded-lg p-2 hover:bg-slate-100 transition" aria-label="Close">
        <span class="text-xl text-slate-400">✕</span>
      </button>
    </div>

    <div class="px-6 py-6">
      <!-- Add Vehicle Form -->
      <form id="vehicleForm" class="mb-8 p-5 rounded-lg border border-slate-200 bg-gradient-to-br from-emerald-50 to-emerald-100/30">
        <h3 class="text-sm font-semibold text-slate-900 mb-4">Add / Edit Vehicle</h3>
        <input type="hidden" name="id" id="vehicleIdInput" value="">

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 items-end">
          <div class="sm:col-span-2">
            <label class="text-xs font-semibold text-slate-700 block mb-2">Plate Number <span class="text-red-500">*</span></label>
            <input type="text" name="plate_number" id="plateNumber" placeholder="e.g., ABC-1234" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300 transition" required />
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-700 block mb-2">Capacity <span class="text-red-500">*</span></label>
            <input type="number" name="capacity" id="capacity" min="1" placeholder="Seats" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300 transition" required />
          </div>

          <div class="sm:col-span-2">
            <label class="text-xs font-semibold text-slate-700 block mb-2">Vehicle Name <span class="text-red-500">*</span></label>
            <input type="text" name="vehicle_name" id="vehicleName" placeholder="e.g., Toyota Hiace Van" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300 transition" required />
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-700 block mb-2">Status <span class="text-red-500">*</span></label>
            <select name="status" id="vehicleStatus" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300 transition" required>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>

          <div class="sm:col-span-3 flex items-center gap-2 justify-end">
            <button type="button" id="resetVehicleFormBtn" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">Reset</button>
            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition">Save Vehicle</button>
          </div>
        </div>
      </form>

      <!-- Vehicles Table -->
      <div class="rounded-lg border border-slate-200 overflow-hidden">
        <div class="bg-gradient-to-r from-slate-50 to-slate-100 px-5 py-3 flex items-center justify-between gap-3">
          <h3 class="text-sm font-semibold text-slate-900">Active Vehicles</h3>
          <select id="vehiclesListStatusFilter" class="bg-white text-xs text-slate-700 border border-slate-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-sky-300 transition">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div class="max-h-[320px] overflow-auto">
          <table class="w-full text-left text-sm">
            <thead class="sticky top-0 bg-white border-b border-slate-200">
              <tr>
                <th class="px-5 py-3 font-semibold text-slate-700">Vehicle</th>
                <th class="px-5 py-3 font-semibold text-slate-700">Plate</th>
                <th class="px-5 py-3 font-semibold text-slate-700">Capacity</th>
                <th class="px-5 py-3 font-semibold text-slate-700">Status</th>
                <th class="px-5 py-3 font-semibold text-slate-700 text-right">Actions</th>
              </tr>
            </thead>
            <tbody id="vehiclesTableBody">
              <tr>
                <td colspan="5" class="px-5 py-6 text-center text-slate-500">Loading vehicles...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ============================================
     DRIVERS MODAL
     ============================================ -->
<div id="driversModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" id="driversModalBackdrop"></div>
  <div class="relative mx-auto my-6 w-[95vw] max-w-4xl rounded-xl border border-slate-200 bg-white shadow-2xl overflow-hidden">
    <div class="flex items-start justify-between gap-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-6 py-5">
      <div>
        <h2 class="text-lg font-semibold text-slate-900">Manage Drivers</h2>
        <p class="text-sm text-slate-500 mt-1">Add new drivers or modify existing ones</p>
      </div>
      <button type="button" id="closeDriversModalBtn" class="rounded-lg p-2 hover:bg-slate-100 transition" aria-label="Close">
        <span class="text-xl text-slate-400">✕</span>
      </button>
    </div>

    <div class="px-6 py-6">
      <!-- Add Driver Form -->
      <form id="driverForm" class="mb-8 p-5 rounded-lg border border-slate-200 bg-gradient-to-br from-sky-50 to-sky-100/30">
        <h3 class="text-sm font-semibold text-slate-900 mb-4">Add / Edit Driver</h3>
        <input type="hidden" name="id" id="driverModalId">

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 items-end">
          <div class="sm:col-span-2">
            <label class="text-xs font-semibold text-slate-700 block mb-2">Driver Name <span class="text-red-500">*</span></label>
            <input type="text" name="driver_name" id="driverNameInput" placeholder="e.g., John Doe" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-300 transition" required />
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-700 block mb-2">Status <span class="text-red-500">*</span></label>
            <select name="status" id="driverStatusInput" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-300 transition" required>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>

          <div class="sm:col-span-3 flex items-center gap-2 justify-end">
            <button type="button" id="resetDriverFormBtn" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">Reset</button>
            <button type="submit" class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700 transition">Save Driver</button>
          </div>
        </div>
      </form>

      <!-- Drivers Table -->
      <div class="rounded-lg border border-slate-200 overflow-hidden">
        <div class="bg-gradient-to-r from-slate-50 to-slate-100 px-5 py-3 flex items-center justify-between gap-3">
          <h3 class="text-sm font-semibold text-slate-900">Active Drivers</h3>
          <select id="driversListStatusFilter" class="bg-white text-xs text-slate-700 border border-slate-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-sky-300 transition">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div class="max-h-[320px] overflow-auto">
          <table class="w-full text-left text-sm">
            <thead class="sticky top-0 bg-white border-b border-slate-200">
              <tr>
                <th class="px-5 py-3 font-semibold text-slate-700">Name</th>
                <th class="px-5 py-3 font-semibold text-slate-700">Status</th>
                <th class="px-5 py-3 font-semibold text-slate-700 text-right">Actions</th>
              </tr>
            </thead>
            <tbody id="driversTableBody">
              <tr>
                <td colspan="3" class="px-5 py-6 text-center text-slate-500">Loading drivers...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- FullCalendar Dependencies -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

  <!-- Vehicle Details Modal (shows history and clickable bookings) -->
  <div id="vehicleDetailsModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" id="vehicleDetailsModalBackdrop"></div>
    <div class="relative mx-auto my-6 w-[95vw] max-w-3xl rounded-xl border border-slate-200 bg-white shadow-2xl overflow-hidden">
      <div class="flex items-center justify-between gap-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-6 py-4">
        <div>
          <h2 class="text-lg font-semibold text-slate-900">Vehicle Details</h2>
          <p class="text-sm text-slate-500 mt-1">Recent bookings — click an item to view full booking details</p>
        </div>
        <button type="button" id="closeVehicleDetailsModalBtn" class="rounded-lg p-2 hover:bg-slate-100 transition" aria-label="Close">
          <span class="text-xl text-slate-400">✕</span>
        </button>
      </div>

      <div class="px-6 py-4 max-h-[70vh] overflow-auto" id="vehicleDetailsContent">
        <div class="text-sm text-slate-500">Loading...</div>
      </div>
    </div>
  </div>

<script>
(function () {
  'use strict';

  const calendarEl = document.getElementById('carBookingCalendar');
  if (!calendarEl) return;

  // ==================== CONSTANTS ====================
  // Build the base URL dynamically to handle any routing setup
  const getBaseUrl = () => {
    const path = window.location.pathname;
    const parts = path.split('/');
    // Find 'Public' in the path and construct the base
    const publicIndex = parts.indexOf('Public');
    if (publicIndex !== -1) {
      return parts.slice(0, publicIndex + 1).join('/') + '/';
    }
    // Fallback to just using root-relative path
    return '/Public/';
  };
  
  const baseUrl = getBaseUrl();
  
  const listUrl = baseUrl + 'index.php?controller=CarBookings&action=list';
  const createBookingUrl = baseUrl + 'index.php?controller=CarBookings&action=create';
  const vehicleHistoryUrl = baseUrl + 'index.php?controller=CarBookings&action=vehicleHistory';
  const getBookingUrl = baseUrl + 'index.php?controller=CarBookings&action=get';
  const updateBookingUrl = baseUrl + 'index.php?controller=CarBookings&action=update';
  const deleteBookingUrl = baseUrl + 'index.php?controller=CarBookings&action=delete';
  const createVehicleUrl = baseUrl + 'index.php?controller=Vehicles&action=create';
  const updateVehicleUrl = baseUrl + 'index.php?controller=Vehicles&action=update';
  const deleteVehicleUrl = baseUrl + 'index.php?controller=Vehicles&action=delete';
  const vehicleOptionsUrl = baseUrl + 'index.php?controller=Vehicles&action=listAjax';
  const vehicleListAllUrl = baseUrl + 'index.php?controller=Vehicles&action=listAllAjax';
  const driversListUrl = baseUrl + 'index.php?controller=Drivers&action=listAjax';
  const createDriverUrl = baseUrl + 'index.php?controller=Drivers&action=create';
  const updateDriverUrl = baseUrl + 'index.php?controller=Drivers&action=update';
  const deleteDriverUrl = baseUrl + 'index.php?controller=Drivers&action=delete';

  // ==================== DOM ELEMENTS ====================
  const bookingModalEl = document.getElementById('bookingModal');
  const bookingForm = document.getElementById('bookingForm');
  const bookingId = document.getElementById('bookingId');
  const vehicleModalEl = document.getElementById('vehicleModal');
  const driversModalEl = document.getElementById('driversModal');
  const vehicleFilter = document.getElementById('vehicleFilter');
  const driverFilter = document.getElementById('driverFilter');
  const calendarViewSelect = document.getElementById('calendarViewSelect');
  const refreshBtn = document.getElementById('refreshBtn');

  const externalEventsEl = document.getElementById('external-events');
  const vehicleCardsContainer = document.getElementById('vehicleCardsContainer');
  const vehicleDetailsModalEl = document.getElementById('vehicleDetailsModal');
  const vehicleDetailsContentEl = document.getElementById('vehicleDetailsContent');
  const closeVehicleDetailsModalBtn = document.getElementById('closeVehicleDetailsModalBtn');
  const vehicleDetailsModalBackdrop = document.getElementById('vehicleDetailsModalBackdrop');

  // ==================== UTILITY FUNCTIONS ====================
  function escapeHtml(str) {
    return String(str)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

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

  function toISODate(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
  }

  function toISODateTime(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    const hh = String(date.getHours()).padStart(2, '0');
    const mm = String(date.getMinutes()).padStart(2, '0');
    return `${y}-${m}-${d}T${hh}:${mm}`;
  }

  function toLocalInputValue(dtStr) {
    if (!dtStr) return '';
    // normalize space to T, truncate seconds if present
    let s = String(dtStr).replace(' ', 'T');
    if (s.length > 16) s = s.substring(0,16);
    return s;
  }

  // Safely parse JSON responses and provide helpful logging when server returns HTML
  function parseJsonResponse(response) {
    if (!response.ok) {
      const ct = (response.headers.get('content-type') || '').toLowerCase();
      if (ct.includes('application/json')) {
        return response.json().then(j => { 
          const msg = j && j.message ? j.message : JSON.stringify(j);
          throw new Error('HTTP ' + response.status + ': ' + msg);
        });
      }
      return response.text().then(t => { throw new Error('HTTP ' + response.status + (t ? (': ' + t) : '')); });
    }
    const ct = (response.headers.get('content-type') || '').toLowerCase();
    if (!ct.includes('application/json')) {
      return response.text().then(t => { console.error('Expected JSON, got:', t); throw new Error('Server returned non-JSON response'); });
    }
    return response.json();
  }

  function formatLocalDisplay(dtStr) {
    if (!dtStr) return '';
    const s = String(dtStr).replace(' ', 'T');
    const d = new Date(s);
    if (isNaN(d.getTime())) return dtStr;
    return d.toLocaleString([], { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
  }

  function getBookingStatus(departureDate, returnDate) {
    const now = new Date();
    const start = new Date(departureDate);
    const end = new Date(returnDate);
    if (isNaN(start.getTime()) || isNaN(end.getTime())) return 'pending';
    if (now < start) return 'pending';
    if (now > end) return 'finished';
    return 'ongoing';
  }

  function getStatusBadgeClass(status) {
    switch (status) {
      case 'pending':
        return 'bg-amber-50 text-amber-700 border-amber-200';
      case 'ongoing':
        return 'bg-blue-50 text-blue-700 border-blue-200';
      case 'finished':
        return 'bg-emerald-50 text-emerald-700 border-emerald-200';
      default:
        return 'bg-slate-50 text-slate-700 border-slate-200';
    }
  }

  function getStatusIcon(status) {
    switch (status) {
      case 'pending':
        return '⏱️';
      case 'ongoing':
        return '🚗';
      case 'finished':
        return '✓';
      default:
        return '•';
    }
  }

  // ==================== BOOKING MODAL ====================
  function setBookingDefaults(dateStr) {
    const today = new Date();
    // default times: departure 09:00, return 17:00
    const d = dateStr ? new Date(dateStr + 'T09:00:00') : today;

    document.getElementById('dateTrip').value = toISODateTime(d);
    document.getElementById('dateRequested').value = toISODateTime(today);
    document.getElementById('departureExpected').value = toISODateTime(d);
    const ret = new Date(d);
    ret.setHours(17,0,0,0);
    document.getElementById('returnExpected').value = toISODateTime(ret);

    bookingId.value = '';
    const deleteBookingBtn = document.getElementById('deleteBookingBtn');
    if (deleteBookingBtn) deleteBookingBtn.classList.add('hidden');
  }

  function resetBookingModalForm() {
    // Clear all field values
    bookingId.value = '';

    // Restore create-mode modal title
    const titleEl = document.getElementById('bookingModalTitle');
    if (titleEl) titleEl.textContent = 'Create New Booking';


    // Clear schedule status badge (prevents previous-clicked status showing during create)
    const statusLine = document.getElementById('bookingScheduleStatusLine');
    if (statusLine) statusLine.remove();


    const fields = [
      'dateTrip',
      'dateRequested',
      'destinations',
      'purpose',
      'passengers',
      'departureExpected',
      'returnExpected',
      'vehicleId',
      'driverId',
      'specialInstructions',
      'remarks',
    ];

    fields.forEach(id => {
      const el = document.getElementById(id);
      if (el) el.value = '';
    });

    // Restore editable state + default button visibility
    restoreBookingModalEditable();

    const saveBtn = document.getElementById('saveBookingBtn');
    if (saveBtn) saveBtn.style.display = '';

    // Delete hidden by default
    const delBtn = document.getElementById('deleteBookingBtn');
    if (delBtn) delBtn.classList.add('hidden');
  }

  document.getElementById('closeBookingModalBtn').addEventListener('click', () => {
    resetBookingModalForm();
    closeModal(bookingModalEl);
  });
  document.getElementById('cancelBookingModalBtn').addEventListener('click', () => {
    resetBookingModalForm();
    closeModal(bookingModalEl);
  });
  document.getElementById('bookingModalBackdrop').addEventListener('click', () => {
    resetBookingModalForm();
    closeModal(bookingModalEl);
  });


  function resetVehicleDetailsModal() {
    if (vehicleDetailsContentEl) {
      vehicleDetailsContentEl.innerHTML = '<div class="text-sm text-slate-500">Loading...</div>';
    }
  }

  // vehicle details modal handlers
  if (closeVehicleDetailsModalBtn) closeVehicleDetailsModalBtn.addEventListener('click', () => { resetVehicleDetailsModal(); closeModal(vehicleDetailsModalEl); });
  if (vehicleDetailsModalBackdrop) vehicleDetailsModalBackdrop.addEventListener('click', () => { resetVehicleDetailsModal(); closeModal(vehicleDetailsModalEl); });


  bookingForm.addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(bookingForm);
    const isUpdate = bookingId && bookingId.value;
    const url = isUpdate ? updateBookingUrl : createBookingUrl;

    fetch(url, {
      method: 'POST',
      body: formData,
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(r => {
      if (!r.ok) {
        throw new Error(`HTTP ${r.status}: ${r.statusText}`);
      }
      return r.json();
    })
    .then(resp => {
      if (!resp || !resp.success) {
        const friendly = friendlyBookingError(resp);
        console.error('Booking save error:', friendly, resp);
        showNotification(friendly, 'error');
        return;
      }
      resetBookingModalForm();
      closeModal(bookingModalEl);
      calendar.refetchEvents();
      loadVehicleCards();
      showNotification(isUpdate ? 'Booking updated successfully!' : 'Booking created successfully!', 'success');

    })
    .catch(err => {
      console.error('Network/parsing error:', err);
      showNotification('Network error while saving booking. Please try again.', 'error');
    });
  });

  // Delete booking handler
  const deleteBookingBtn = document.getElementById('deleteBookingBtn');
  deleteBookingBtn.addEventListener('click', function () {
    const id = bookingId && bookingId.value ? bookingId.value : null;
    if (!id) return;
    if (!confirm('Delete this booking? This will cancel it.')) return;

    const fd = new FormData(); fd.append('id', id);
    fetch(deleteBookingUrl, { method: 'POST', body: fd, headers: {'X-Requested-With': 'XMLHttpRequest'} })
      .then(parseJsonResponse)
      .then(resp => {
        if (!resp || !resp.success) {
          const msg = resp && resp.message ? resp.message : 'Failed to delete booking';
          showNotification(msg, 'error');
          return;
        }
        resetBookingModalForm();
        closeModal(bookingModalEl);
        calendar.refetchEvents();
        loadVehicleCards();
        showNotification('Booking deleted', 'info');

      })
      .catch(err => { console.error(err); showNotification('Network error while deleting booking', 'error'); });
  });

  // ==================== VEHICLE MODAL ====================
  const vehicleIdInputEl = document.getElementById('vehicleIdInput');
  const plateNumberInputEl = document.getElementById('plateNumber');
  const vehicleNameInputEl = document.getElementById('vehicleName');
  const capacityInputEl = document.getElementById('capacity');
  const vehicleStatusInputEl = document.getElementById('vehicleStatus');
  const vehiclesTableBodyEl = document.getElementById('vehiclesTableBody');
  const vehiclesListStatusFilterEl = document.getElementById('vehiclesListStatusFilter');
  const vehicleForm = document.getElementById('vehicleForm');
  const resetVehicleFormBtn = document.getElementById('resetVehicleFormBtn');
  const openVehicleModalBtn = document.getElementById('openVehicleModalBtn');
  const closeVehicleModalBtn = document.getElementById('closeVehicleModalBtn');
  const vehicleModalBackdrop = document.getElementById('vehicleModalBackdrop');

  function setVehicleModalFormFromRow(row) {
    vehicleIdInputEl.value = row ? String(row.id) : '';
    plateNumberInputEl.value = row ? row.plate_number : '';
    vehicleNameInputEl.value = row ? row.vehicle_name : '';
    capacityInputEl.value = row ? row.capacity : '';
    vehicleStatusInputEl.value = row ? row.status : 'active';
  }

  function renderVehiclesTable(rows) {
    if (!rows || rows.length === 0) {
      vehiclesTableBodyEl.innerHTML = '<tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">No vehicles found</td></tr>';
      return;
    }

    vehiclesTableBodyEl.innerHTML = '';
    rows.forEach(row => {
      const tr = document.createElement('tr');
      tr.className = 'border-b border-slate-100 hover:bg-slate-50 transition';
      const statusBadge = row.status === 'active' 
        ? '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>'
        : '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">Inactive</span>';

      tr.innerHTML = `
        <td class="px-5 py-3">
          <div class="font-medium text-slate-800">${escapeHtml(row.vehicle_name)}</div>
        </td>
        <td class="px-5 py-3 text-slate-700">${escapeHtml(row.plate_number)}</td>
        <td class="px-5 py-3 text-slate-700">${row.capacity} seats</td>
        <td class="px-5 py-3">${statusBadge}</td>
        <td class="px-5 py-3 text-right">
          <div class="inline-flex items-center gap-2">
            <button type="button" class="vehicle-edit-btn rounded-lg border border-sky-200 bg-sky-50 px-3 py-1.5 text-xs font-medium text-sky-700 hover:bg-sky-100 transition" data-id="${row.id}">Edit</button>
            <button type="button" class="vehicle-delete-btn rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100 transition" data-id="${row.id}">Disable</button>
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
        if (!confirm(`Disable vehicle '${label}'? It will not be available for new bookings.`)) return;

        const fd = new FormData();
        fd.append('id', id);

        fetch(deleteVehicleUrl, {
          method: 'POST',
          body: fd,
          headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
        .then(r => {
          if (!r.ok) throw new Error(`HTTP ${r.status}`);
          return r.json();
        })
        .then(resp => {
          if (!resp || !resp.success) {
            alert(resp && resp.message ? resp.message : 'Failed to disable vehicle');
            return;
          }
          loadVehiclesTable();
          refreshVehicleFilterDropdown();
          refreshBookingVehicleSelect();
          setVehicleModalFormFromRow(null);
          showNotification('Vehicle disabled successfully', 'info');
        })
        .catch(() => alert('Network error while disabling vehicle'));
      });
    });
  }

  function loadVehiclesTable() {
    const filterStatus = vehiclesListStatusFilterEl ? vehiclesListStatusFilterEl.value : '';
    vehiclesTableBodyEl.innerHTML = '<tr><td colspan="5" class="px-5 py-6 text-center text-slate-500">Loading vehicles...</td></tr>';

    const url = vehicleListAllUrl + (filterStatus ? ('&status=' + encodeURIComponent(filterStatus)) : '') + '&t=' + Date.now();

    fetch(url, {
      method: 'GET',
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(parseJsonResponse)
    .then(data => {
      const vehicles = data && Array.isArray(data.vehicles) ? data.vehicles : [];
      renderVehiclesTable(vehicles);
    })
    .catch(err => {
      console.error('Failed to load vehicles:', err);
      vehiclesTableBodyEl.innerHTML = '<tr><td colspan="5" class="px-5 py-6 text-center text-slate-500">Failed to load vehicles</td></tr>';
    });
  }

  function refreshVehicleFilterDropdown() {
    if (!vehicleFilter) return;
    fetch(vehicleOptionsUrl + '&t=' + Date.now(), {
      method: 'GET',
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(parseJsonResponse)
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
    const bookingVehicleSelect = document.getElementById('vehicleId');
    if (!bookingVehicleSelect) return;

    fetch(vehicleOptionsUrl + '&t=' + Date.now(), {
      method: 'GET',
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(parseJsonResponse)
    .then(data => {
      const vehicles = data && Array.isArray(data.vehicles) ? data.vehicles : [];
      const keep = bookingVehicleSelect.value;

      bookingVehicleSelect.innerHTML = '<option value="">-- Select a vehicle --</option>';
      vehicles.forEach(v => {
        const opt = document.createElement('option');
        opt.value = v.id;
        opt.textContent = `${v.vehicle_name} (${v.plate_number})`;
        bookingVehicleSelect.appendChild(opt);
      });

      if (keep && vehicles.some(v => String(v.id) === String(keep))) {
        bookingVehicleSelect.value = keep;
      }
    })
    .catch(err => console.error('Failed to refresh vehicle select:', err));
  }

  function resetVehicleModalForm() {
    setVehicleModalFormFromRow(null);
  }

  openVehicleModalBtn.addEventListener('click', () => {
    openModal(vehicleModalEl);
    loadVehiclesTable();
  });
  closeVehicleModalBtn.addEventListener('click', () => { resetVehicleModalForm(); closeModal(vehicleModalEl); });
  vehicleModalBackdrop.addEventListener('click', () => { resetVehicleModalForm(); closeModal(vehicleModalEl); });

  resetVehicleFormBtn.addEventListener('click', () => setVehicleModalFormFromRow(null));
  vehiclesListStatusFilterEl.addEventListener('change', () => loadVehiclesTable());

  vehicleForm.addEventListener('submit', function (e) {
    e.preventDefault();

    const id = vehicleIdInputEl && vehicleIdInputEl.value ? parseInt(vehicleIdInputEl.value, 10) : null;
    const fd = new FormData();
    fd.append('plate_number', plateNumberInputEl.value);
    fd.append('vehicle_name', vehicleNameInputEl.value);
    fd.append('capacity', capacityInputEl.value);
    fd.append('status', vehicleStatusInputEl.value);
    if (id) fd.append('id', id);

    const url = id ? updateVehicleUrl : createVehicleUrl;

    fetch(url, {
      method: 'POST',
      body: fd,
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(r => {
      if (!r.ok) {
        throw new Error(`HTTP ${r.status}: ${r.statusText}`);
      }
      return r.json();
    })
    .then(resp => {
      if (!resp || !resp.success) {
        const errorMsg = resp && resp.message ? resp.message : 'Failed to save vehicle';
        console.error('Vehicle save error:', errorMsg, resp);
        alert(errorMsg);
        return;
      }

      resetVehicleModalForm();
      closeModal(vehicleModalEl);
      loadVehiclesTable();
      refreshVehicleFilterDropdown();
      refreshBookingVehicleSelect();
      loadVehicleCards();
      calendar.refetchEvents();
      showNotification(id ? 'Vehicle updated successfully' : 'Vehicle created successfully', 'success');

    })
    .catch(err => {
      console.error('Network/parsing error:', err);
      alert('Network error while saving vehicle: ' + err.message);
    });
  });

  // ==================== DRIVERS MODAL ====================
  const driverIdEl = document.getElementById('driverModalId');
  const driverNameInputEl = document.getElementById('driverNameInput');
  const driverStatusInputEl = document.getElementById('driverStatusInput');
  const driverForm = document.getElementById('driverForm');
  const driversTableBodyEl = document.getElementById('driversTableBody');
  const driversListStatusFilterEl = document.getElementById('driversListStatusFilter');
  const resetDriverFormBtn = document.getElementById('resetDriverFormBtn');
  const openDriversModalBtn = document.getElementById('openDriversModalBtn');
  const closeDriversModalBtn = document.getElementById('closeDriversModalBtn');
  const driversModalBackdrop = document.getElementById('driversModalBackdrop');

  function setDriversModalFormFromRow(row) {
    driverIdEl.value = row ? String(row.id) : '';
    driverNameInputEl.value = row ? row.driver_name : '';
    driverStatusInputEl.value = row ? row.status : 'active';
  }

  function renderDriversTable(rows) {
    if (!rows || rows.length === 0) {
      driversTableBodyEl.innerHTML = '<tr><td colspan="3" class="px-5 py-8 text-center text-slate-500">No drivers found</td></tr>';
      return;
    }

    driversTableBodyEl.innerHTML = '';
    rows.forEach(row => {
      const tr = document.createElement('tr');
      tr.className = 'border-b border-slate-100 hover:bg-slate-50 transition';
      const statusBadge = row.status === 'active'
        ? '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>'
        : '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">Inactive</span>';

      tr.innerHTML = `
        <td class="px-5 py-3">
          <div class="font-medium text-slate-800">${escapeHtml(row.driver_name)}</div>
        </td>
        <td class="px-5 py-3">${statusBadge}</td>
        <td class="px-5 py-3 text-right">
          <div class="inline-flex items-center gap-2">
            <button type="button" class="driver-edit-btn rounded-lg border border-sky-200 bg-sky-50 px-3 py-1.5 text-xs font-medium text-sky-700 hover:bg-sky-100 transition" data-id="${row.id}">Edit</button>
            <button type="button" class="driver-delete-btn rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100 transition" data-id="${row.id}">Disable</button>
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
        if (!confirm(`Disable driver '${name}'? They will not be available for new bookings.`)) return;

        const fd = new FormData();
        fd.append('id', id);

        fetch(deleteDriverUrl, {
          method: 'POST',
          body: fd,
          headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
        .then(r => {
          if (!r.ok) throw new Error(`HTTP ${r.status}`);
          return r.json();
        })
        .then(resp => {
          if (!resp || !resp.success) {
            alert(resp && resp.message ? resp.message : 'Failed to disable driver');
            return;
          }
          loadDriversTable();
          refreshDriverFilterDropdown();
          refreshBookingDriverSelect();
          setDriversModalFormFromRow(null);
          showNotification('Driver disabled successfully', 'info');
        })
        .catch(() => alert('Network error while disabling driver'));
      });
    });
  }

  function loadDriversTable() {
    const filterStatus = driversListStatusFilterEl ? driversListStatusFilterEl.value : '';
    driversTableBodyEl.innerHTML = '<tr><td colspan="3" class="px-5 py-6 text-center text-slate-500">Loading drivers...</td></tr>';

    const url = driversListUrl + (filterStatus ? ('&status=' + encodeURIComponent(filterStatus)) : '') + '&t=' + Date.now();
    fetch(url, {
      method: 'GET',
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(r => {
      if (!r.ok) throw new Error(`HTTP ${r.status}`);
      return r.json();
    })
    .then(data => {
      const drivers = data && Array.isArray(data.drivers) ? data.drivers : [];
      renderDriversTable(drivers);
    })
    .catch(err => {
      console.error('Failed to load drivers:', err);
      driversTableBodyEl.innerHTML = '<tr><td colspan="3" class="px-5 py-6 text-center text-slate-500">Failed to load drivers</td></tr>';
    });
  }

  function refreshDriverFilterDropdown() {
    if (!driverFilter) return;
    fetch(driversListUrl + '&t=' + Date.now(), {
      method: 'GET',
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(r => {
      if (!r.ok) throw new Error(`HTTP ${r.status}`);
      return r.json();
    })
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
    .catch(err => console.error('Failed to refresh driver filter:', err));
  }

 function refreshBookingDriverSelect() {
  const bookingDriverSelect = document.getElementById('driverId');
  if (!bookingDriverSelect) return;

  fetch(driversListUrl + '&t=' + Date.now(), {
    method: 'GET',
    headers: {'X-Requested-With': 'XMLHttpRequest'}
  })
  .then(r => {
    if (!r.ok) throw new Error(`HTTP ${r.status}`);
    return r.json();
  })
  .then(data => {
    const drivers = data && Array.isArray(data.drivers) ? data.drivers : [];
    const keep = bookingDriverSelect.value; // Keeps track of current selection if needed

    bookingDriverSelect.innerHTML = '<option value="">-- Select a driver --</option>';
    
    // FIXED: Changed "items" to "drivers" and "d" to "driver"
    drivers.forEach(driver => {
      const opt = document.createElement('option');
      opt.value = driver.id;
      opt.textContent = driver.driver_name;
      bookingDriverSelect.appendChild(opt);
    });

    // Re-select the previous value if it still exists in the new list
    if (keep) {
      bookingDriverSelect.value = keep;
    }
  })
  .catch(err => console.error('Failed to refresh driver select:', err));
}

// Event Listeners & Initializers
closeDriversModalBtn.addEventListener('click', () => closeModal(driversModalEl));
driversModalBackdrop.addEventListener('click', () => closeModal(driversModalEl));
resetDriverFormBtn.addEventListener('click', () => setDriversModalFormFromRow(null));
driversListStatusFilterEl.addEventListener('change', () => loadDriversTable());

  function resetDriversModalForm() {
    setDriversModalFormFromRow(null);
  }

  // Open drivers modal button
if (openDriversModalBtn) {
  openDriversModalBtn.addEventListener('click', () => {
    setDriversModalFormFromRow(null);
    loadDriversTable();
    openModal(driversModalEl);
  });
}

// Close handlers
if (closeDriversModalBtn) closeDriversModalBtn.addEventListener('click', () => { resetDriversModalForm(); closeModal(driversModalEl); });
if (driversModalBackdrop) driversModalBackdrop.addEventListener('click', () => { resetDriversModalForm(); closeModal(driversModalEl); });

driverForm.addEventListener('submit', function (e) {

  e.preventDefault();

  const id = driverIdEl.value ? parseInt(driverIdEl.value, 10) : null;
  const fd = new FormData();
  fd.append('driver_name', driverNameInputEl.value);
  fd.append('status', driverStatusInputEl.value);

  if (id) fd.append('id', id);

  const url = id ? updateDriverUrl : createDriverUrl;

  fetch(url, {
    method: 'POST',
    body: fd,
    headers: {'X-Requested-With': 'XMLHttpRequest'}
  })
  .then(r => {
    if (!r.ok) {
      throw new Error(`HTTP ${r.status}: ${r.statusText}`);
    }
    return r.json();
  })
  .then(resp => {
    if (!resp || !resp.success) {
      const errorMsg = resp && resp.message ? resp.message : 'Failed to save driver';
      console.error('Driver save error:', errorMsg, resp);
      alert(errorMsg);
      return;
    }
    resetDriversModalForm();
    closeModal(driversModalEl);
    loadDriversTable();
    refreshDriverFilterDropdown();
    refreshBookingDriverSelect();
    showNotification(id ? 'Driver updated successfully' : 'Driver created successfully', 'success');

  })
  .catch(err => {
    console.error('Network/parsing error:', err);
    alert('Network error while saving driver: ' + err.message);
  });
});

  // ==================== VEHICLE CARDS ====================
  function loadVehicleCards() {
    if (!vehicleCardsContainer) return;

    vehicleCardsContainer.innerHTML = '';

    fetch(vehicleListAllUrl + '&status=active&t=' + Date.now(), {
      method: 'GET',
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(r => {
      if (!r.ok) throw new Error(`HTTP ${r.status}`);
      const ct = r.headers.get('content-type') || '';
      if (!ct.toLowerCase().includes('application/json')) {
        return r.text().then(txt => {
          console.error('Non-JSON response for vehicleListAllUrl:', txt);
          throw new Error('Server returned non-JSON response');
        });
      }
      return r.json();
    })
    .then(data => {
      const activeVehicles = (data && Array.isArray(data.vehicles) ? data.vehicles : []).filter(v => v.status === 'active');
      
      if (activeVehicles.length === 0) {
        vehicleCardsContainer.innerHTML = '<div class="col-span-full text-center py-8 text-slate-500">No active vehicles available</div>';
        return;
      }

      activeVehicles.slice(0, 6).forEach(vehicle => {
        const card = createVehicleCard(vehicle);
        vehicleCardsContainer.appendChild(card);
      });
    })
    .catch(err => {
      console.error('Error loading vehicle cards:', err);
      vehicleCardsContainer.innerHTML = '<div class="col-span-full text-center py-8 text-rose-500">Failed to load vehicles</div>';
    });
  }

  function createVehicleCard(vehicle) {
    const card = document.createElement('div');
    card.className = 'rounded-lg border border-slate-200 bg-white shadow-sm hover:shadow-md transition overflow-hidden hover:border-slate-300';
    card.innerHTML = `
      <div class="bg-gradient-to-r from-slate-50 to-slate-100 px-4 py-3 border-b border-slate-200">
        <h3 class="text-sm font-semibold text-slate-900">${escapeHtml(vehicle.vehicle_name)}</h3>
        <p class="text-xs text-slate-600 mt-0.5">🏷️ ${escapeHtml(vehicle.plate_number)}</p>
      </div>
      
      <div class="px-4 py-4">
        <div class="flex items-center justify-between mb-4">
          <span class="text-xs font-medium text-slate-600">Capacity</span>
          <span class="text-sm font-semibold text-sky-600">${vehicle.capacity} seats</span>
        </div>
        
        <div class="mb-4" id="loading-${vehicle.id}">
          <p class="text-xs text-slate-500 text-center py-2">Loading bookings...</p>
        </div>
        <div id="bookings-${vehicle.id}" class="space-y-2" style="display:none;"></div>
        
        <button type="button" class="w-full mt-4 rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-medium text-sky-700 hover:bg-sky-100 transition view-vehicle-btn" data-vehicle-id="${vehicle.id}">
          View Details
        </button>
      </div>
    `;

    // initially load upcoming bookings only
    loadVehicleUpcoming(vehicle.id, card);

    card.querySelector('.view-vehicle-btn').addEventListener('click', () => {
      showVehicleDetails(vehicle.id, card);
    });

    return card;
  }

  // Show only upcoming bookings (pending or ongoing) — used for cards
  function loadVehicleUpcoming(vehicleId, card) {
    const loadingEl = card.querySelector(`#loading-${vehicleId}`);
    const bookingsEl = card.querySelector(`#bookings-${vehicleId}`);

    fetch(vehicleHistoryUrl + '&vehicle_id=' + vehicleId + '&t=' + Date.now(), {
      method: 'GET',
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(parseJsonResponse)
    .then(data => {
      loadingEl.style.display = 'none';

      const rows = (data && Array.isArray(data.bookings)) ? data.bookings : [];
      // keep only upcoming (pending or ongoing)
      const upcoming = rows.filter(b => {
        const st = getBookingStatus(b.departure_expected, b.return_expected);
        return st === 'pending' || st === 'ongoing';
      });

      if (!upcoming || upcoming.length === 0) {
        bookingsEl.innerHTML = '<p class="text-xs text-slate-500 text-center py-2">No upcoming bookings</p>';
        bookingsEl.style.display = 'block';
        return;
      }

      bookingsEl.innerHTML = '';
      upcoming.slice(0,3).forEach(booking => {
        bookingsEl.appendChild(renderBookingShort(booking));
      });

      bookingsEl.style.display = 'block';
    })
    .catch(err => {
      console.error('Error loading upcoming bookings:', err);
      loadingEl.innerHTML = '<p class="text-xs text-rose-500 text-center py-1">Failed to load</p>';
    });
  }

  // Render a compact booking element for the card
  function renderBookingShort(booking) {
    const status = getBookingStatus(booking.departure_expected, booking.return_expected);
    const statusIcon = getStatusIcon(status);
    const badgeClass = getStatusBadgeClass(status);

    const bookingEl = document.createElement('div');
    bookingEl.className = `text-xs p-2 rounded border ${badgeClass}`;
    bookingEl.innerHTML = `
      <div class="flex items-start justify-between gap-2">
        <div class="flex-1">
          <p class="font-medium">${escapeHtml(booking.purpose || 'Trip')}</p>
          <p class="text-xs opacity-75 mt-0.5">${escapeHtml(formatLocalDisplay(booking.departure_expected || booking.start_at || ''))}</p>
        </div>
        <span class="flex-shrink-0">${statusIcon}</span>
      </div>
    `;
    return bookingEl;
  }

  // Show full vehicle details and history inside the card (toggle)
  function showVehicleDetails(vehicleId, card) {
    // open vehicle details modal and populate with clickable bookings
    if (!vehicleDetailsContentEl || !vehicleDetailsModalEl) return;
    vehicleDetailsContentEl.innerHTML = '<div class="text-sm text-slate-500">Loading history...</div>';
    openModal(vehicleDetailsModalEl);

    fetch(vehicleHistoryUrl + '&vehicle_id=' + vehicleId + '&t=' + Date.now(), {
      method: 'GET',
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(parseJsonResponse)
    .then(data => {
      const rows = (data && Array.isArray(data.bookings)) ? data.bookings : [];
      const upcomingRows = rows.filter(b => {
        const st = getBookingStatus(b.departure_expected, b.return_expected);
        return st === 'pending' || st === 'ongoing';
      });
      const pastRows = rows.filter(b => {
        const st = getBookingStatus(b.departure_expected, b.return_expected);
        return st === 'finished';
      });

      const buildList = (title, items) => {
        const sec = document.createElement('div');
        sec.className = 'mb-4';
        const hdr = document.createElement('div'); hdr.className = 'text-xs font-semibold text-slate-700 mb-2'; hdr.textContent = title;
        sec.appendChild(hdr);
        if (!items || items.length === 0) {
          const p = document.createElement('div'); p.className = 'text-xs text-slate-500'; p.textContent = 'None'; sec.appendChild(p); return sec;
        }
        items.forEach(it => {
          const item = document.createElement('div');
          item.className = 'p-2 rounded border mb-2 bg-white hover:bg-slate-50 cursor-pointer';
          item.innerHTML = `<div class="flex items-center justify-between"><div class="flex-1"><div class="font-medium">${escapeHtml(it.purpose || 'Trip')}</div><div class="text-xs text-slate-500">${escapeHtml(formatLocalDisplay(it.departure_expected || it.start_at || ''))} → ${escapeHtml(formatLocalDisplay(it.return_expected || it.end_at || ''))}</div></div><div class="text-xs text-slate-400">View</div></div>`;
          item.addEventListener('click', () => {
            // open booking in read-only mode
            loadBookingReadOnly(it.id);
            closeModal(vehicleDetailsModalEl);
          });
          sec.appendChild(item);
        });
        return sec;
      };

      vehicleDetailsContentEl.innerHTML = '';
      vehicleDetailsContentEl.appendChild(buildList('Upcoming', upcomingRows));
      vehicleDetailsContentEl.appendChild(buildList('Past', pastRows));
    })
    .catch(err => {
      console.error('Error loading history details:', err);
      if (vehicleDetailsContentEl) vehicleDetailsContentEl.innerHTML = '<p class="text-xs text-rose-500 text-center py-1">Failed to load history</p>';
    });
  }

  // Load booking by id into booking modal in read-only mode
  function loadBookingReadOnly(id) {
    if (!id) return;
    const url = getBookingUrl + '&id=' + encodeURIComponent(id) + '&t=' + Date.now();
    fetch(url, { method: 'GET', headers: {'X-Requested-With': 'XMLHttpRequest'} })
      .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(data => {
        if (!data || !data.success || !data.booking) {
          alert('Failed to load booking details');
          return;
        }
        const b = data.booking;
        // populate form fields
        bookingId.value = String(b.id || '');
        document.getElementById('dateTrip').value = toLocalInputValue(b.date_trip || b.start_at || '');
        document.getElementById('dateRequested').value = toLocalInputValue(b.date_requested || '');
        document.getElementById('departureExpected').value = toLocalInputValue(b.start_at || b.departure_expected || '');
        document.getElementById('returnExpected').value = toLocalInputValue(b.end_at || b.return_expected || '');
        document.getElementById('destinations').value = b.destinations || '';
        document.getElementById('purpose').value = b.purpose || '';
        document.getElementById('passengers').value = b.passengers || '';
        document.getElementById('vehicleId').value = b.vehicle_id || '';
        document.getElementById('driverId').value = b.driver_id || '';
        document.getElementById('specialInstructions').value = b.special_instructions || '';
        document.getElementById('remarks').value = b.remarks || '';

        // make form read-only
        bookingForm.querySelectorAll('input,textarea,select,button').forEach(el => {
          // keep close/cancel buttons enabled by leaving them as type=button with specific ids
          if (el.id === 'closeBookingModalBtn' || el.id === 'cancelBookingModalBtn') return;
          el.disabled = true;
        });

        // hide save & delete buttons
        const saveBtn = document.getElementById('saveBookingBtn');
        const delBtn = document.getElementById('deleteBookingBtn');
        if (saveBtn) saveBtn.style.display = 'none';
        if (delBtn) delBtn.style.display = 'none';

        // Update modal title for read-only details vs create mode
        const titleEl = document.getElementById('bookingModalTitle');
        if (titleEl) titleEl.textContent = 'Booking details';

        openModal(bookingModalEl);
      })
      .catch(err => { console.error('Failed to load booking:', err); alert('Failed to load booking details'); });
  }

  // restore booking modal to editable state when closed
  function restoreBookingModalEditable() {
    bookingForm.querySelectorAll('input,textarea,select,button').forEach(el => {
      if (el.id === 'closeBookingModalBtn' || el.id === 'cancelBookingModalBtn') return;
      el.disabled = false;
    });
    const saveBtn = document.getElementById('saveBookingBtn');
    const delBtn = document.getElementById('deleteBookingBtn');
    if (saveBtn) saveBtn.style.display = '';
    if (delBtn) delBtn.style.display = '';
  }

  // booking modal will restore editable state via close handlers above

  // ==================== NOTIFICATION ====================
  function showNotification(message, type = 'info') {
    const notif = document.createElement('div');
    notif.className = `fixed bottom-6 right-6 px-4 py-3 rounded-lg text-sm font-medium text-white shadow-lg z-[9999] ${
      type === 'success' ? 'bg-emerald-600' : type === 'error' ? 'bg-rose-600' : 'bg-sky-600'
    }`;
    notif.textContent = message;
    document.body.appendChild(notif);

    setTimeout(() => {
      notif.style.opacity = '0';
      notif.style.transition = 'opacity 0.3s ease';
      setTimeout(() => notif.remove(), 300);
    }, 3000);
  }

  function friendlyBookingError(resp) {
    const code = resp && resp.error_code ? String(resp.error_code) : null;

    if (code === 'vehicle_conflict') return 'This booking can’t be saved: the selected vehicle is already booked for that time period.';
    if (code === 'driver_conflict') return 'This booking can’t be saved: the selected driver is already booked for that time period.';
    if (code === 'past_departure') return 'This booking can’t be saved: the departure time is in the past (including today earlier than now).';

    // Fallback: use server message if present
    if (resp && resp.message) return resp.message;
    return 'Unable to save booking. Please check your details and try again.';
  }

  // ==================== CALENDAR ====================
  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    height: 'auto',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    selectable: true,
    editable: true,
    droppable: true,
    eventTimeFormat: {
      hour: '2-digit',
      minute: '2-digit',
      meridiem: false
    },
    eventDisplay: 'block',
    // Use per-event colors (provided by extendedProps/backgroundColor/borderColor)
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
          .then(r => {
            if (!r.ok) throw new Error(`HTTP ${r.status}`);
            return r.json();
          })
          .then(data => {
            if (!data || !Array.isArray(data.events)) {
              successCallback([]);
              return;
            }
            successCallback(data.events);
          })
          .catch(err => {
            console.error('Failed to load calendar events:', err);
            failureCallback();
          });
        }
      }
    ],

    dateClick: function (info) {
      setBookingDefaults(info.dateStr);
      openModal(bookingModalEl);
    },

    drop: function (info) {
      if (!info.date) return;
      
      const dateStr = info.dateStr;
      setBookingDefaults(dateStr);
      openModal(bookingModalEl);
    },

    eventClick: function (info) {
      const event = info.event;
      const id = event.id;
      if (!id) return;

      // Show status immediately from the event payload
      const statusFromEvent = event.extendedProps && event.extendedProps.status ? String(event.extendedProps.status) : null;
      const departureExpected = event.extendedProps ? (event.extendedProps.departure_expected || event.extendedProps.start_at) : null;
      const returnExpected = event.extendedProps ? (event.extendedProps.return_expected || event.extendedProps.end_at) : null;
      const computedStatus = statusFromEvent || getBookingStatus(departureExpected, returnExpected);

      // Ensure there is an info line inside the modal header/body
      let statusLine = document.getElementById('bookingScheduleStatusLine');
      if (!statusLine) {
        // Insert right under the modal title area
        const modalHeader = bookingModalEl.querySelector('h2');
        if (modalHeader) {
          statusLine = document.createElement('div');
          statusLine.id = 'bookingScheduleStatusLine';
          statusLine.className = 'mt-2 text-xs font-medium';
          modalHeader.parentElement.appendChild(statusLine);
        }
      }
      if (statusLine) {
        const badgeClass = (() => {
          switch (computedStatus) {
            case 'pending': return 'bg-amber-50 text-amber-700 border-amber-200';
            case 'ongoing': return 'bg-blue-50 text-blue-700 border-blue-200';
            case 'finished': return 'bg-emerald-50 text-emerald-700 border-emerald-200';
            default: return 'bg-slate-50 text-slate-700 border-slate-200';
          }
        })();
        statusLine.innerHTML = `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border ${badgeClass}">
          ${getStatusIcon(computedStatus)} ${computedStatus.toUpperCase()}\n        </span>`;
      }

      fetch(getBookingUrl + '&id=' + encodeURIComponent(id), { headers: {'X-Requested-With': 'XMLHttpRequest'} })
        .then(parseJsonResponse)
        .then(data => {
          if (!data || !data.success || !data.booking) {
            alert(data && data.message ? data.message : 'Unable to load booking');
            return;
          }
          const b = data.booking;
          // populate modal
          bookingId.value = b.id || '';
          document.getElementById('dateTrip').value = toLocalInputValue(b.date_trip || b.start_at || '');
          document.getElementById('dateRequested').value = toLocalInputValue(b.date_requested || '');
          document.getElementById('destinations').value = b.destinations || '';
          document.getElementById('purpose').value = b.purpose || '';
          document.getElementById('passengers').value = b.passengers || b.seat_count || '';
          document.getElementById('departureExpected').value = toLocalInputValue(b.start_at || b.departure_expected || '');
          document.getElementById('returnExpected').value = toLocalInputValue(b.end_at || b.return_expected || '');
          document.getElementById('vehicleId').value = b.vehicle_id || '';
          document.getElementById('driverId').value = b.driver_id || '';
          document.getElementById('specialInstructions').value = b.special_instructions || '';
          document.getElementById('remarks').value = b.remarks || b.notes || '';

          const deleteBookingBtn = document.getElementById('deleteBookingBtn');
          if (deleteBookingBtn) deleteBookingBtn.classList.remove('hidden');
          // Update modal title for read-only details vs create mode
          const titleEl = document.getElementById('bookingModalTitle');
          if (titleEl) titleEl.textContent = 'Booking details';

          openModal(bookingModalEl);
        })

        .catch(err => {
          console.error('Failed to load booking:', err);
          alert('Failed to load booking');
        });
    },

  eventDrop: function (info) {
        const ev = info.event;
        const id = ev.id;
        if (!id) { info.revert(); return; }

        // Prevent past dates
        const todayMidnight = new Date();
        todayMidnight.setHours(0, 0, 0, 0);
        if (ev.start && ev.start.getTime() < todayMidnight.getTime()) {
            showNotification('Cannot move booking into the past', 'error');
            info.revert();
            return;
        }

        const newStart = toISODateTime(ev.start);
        const newEnd = ev.end ? toISODateTime(ev.end) : toISODateTime(ev.start);

        const fd = new FormData();
        fd.append('id', id);
        fd.append('departure_expected', newStart);
        fd.append('return_expected', newEnd);

        fetch(updateBookingUrl, { 
            method: 'POST', 
            body: fd, 
            headers: { 'X-Requested-With': 'XMLHttpRequest' } 
        })
        .then(parseJsonResponse)
        .then(resp => {
            if (!resp || !resp.success) {
                const friendly = friendlyBookingError(resp);
                console.error('Update error (server):', friendly, resp);
                showNotification(friendly, 'error');
                info.revert();
                return;
            }
            showNotification('Booking rescheduled successfully', 'success');
            loadVehicleCards();
        })
        .catch(err => {
            console.error('Update error (network/parsing):', err);
            showNotification('Network error while rescheduling booking.', 'error');
            info.revert();
        });
    },

    eventResize: function (info) {
        const ev = info.event;
        const id = ev.id;
        if (!id) { info.revert(); return; }

        const todayMidnight = new Date();
        todayMidnight.setHours(0, 0, 0, 0);
        if (ev.start && ev.start.getTime() < todayMidnight.getTime()) {
            showNotification('Cannot resize booking into the past', 'error');
            info.revert();
            return;
        }

        const newStart = toISODateTime(ev.start);
        const newEnd = ev.end ? toISODateTime(ev.end) : toISODateTime(ev.start);

        const fd = new FormData();
        fd.append('id', id);
        fd.append('departure_expected', newStart);
        fd.append('return_expected', newEnd);

        fetch(updateBookingUrl, { 
            method: 'POST', 
            body: fd, 
            headers: { 'X-Requested-With': 'XMLHttpRequest' } 
        })
        .then(parseJsonResponse)
        .then(resp => {
            if (!resp || !resp.success) {
                const friendly = friendlyBookingError(resp);
                console.error('Resize error (server):', friendly, resp);
                showNotification(friendly, 'error');
                info.revert();
                return;
            }
            showNotification('Booking duration updated', 'success');
            loadVehicleCards();
        })
        .catch(err => {
            console.error('Resize error (network/parsing):', err);
            showNotification('Network error while updating booking.', 'error');
            info.revert();
        });
    }
});

  calendar.render();

  // External drag for new bookings
  if (externalEventsEl) {
    const newScheduleEl = externalEventsEl.querySelector('.inline-flex');
    if (newScheduleEl) {
      new FullCalendar.Draggable(externalEventsEl, {
        itemSelector: '.inline-flex',
        eventData: function () {
          return {
            title: 'New booking',
            allDay: true
          };
        }
      });
    }
  }

  calendarViewSelect.addEventListener('change', function () {
    calendar.changeView(this.value);
  });

  refreshBtn.addEventListener('click', () => {
    calendar.refetchEvents();
    loadVehicleCards();
    showNotification('Calendar refreshed', 'info');
  });

  vehicleFilter.addEventListener('change', () => calendar.refetchEvents());
  driverFilter.addEventListener('change', () => calendar.refetchEvents());

  // ==================== INITIALIZATION ====================
  loadVehicleCards();
})();
</script>
