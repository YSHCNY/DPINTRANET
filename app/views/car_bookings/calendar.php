<?php
// Car Bookings Calendar with Vehicle History Cards
// Requires: CarBookingsController@calendar
$vehicleActiveCount = 0;
$vehicleInactiveCount = 0;
if (!empty($vehicles) && is_array($vehicles)) {
  foreach ($vehicles as $v) {
    if (isset($v['status']) && $v['status'] === 'active') {
      $vehicleActiveCount++;
    } else {
      $vehicleInactiveCount++;
    }
  }
}
$driverCount = is_array($drivers ?? []) ? count($drivers) : 0;
?>

<div class="min-h-screen theme-palette fleet-module">
    <div class="mx-auto max-w-[1380px] px-2 py-2 text-xs sm:text-xs md:text-sm lg:px-3 lg:py-3 xl:px-4 xl:py-4">
    
    <div class="">

      
      <div class="space-y-2 lg:space-y-3">
        <div class="mb-2 rounded-xl border border-slate-200 bg-white p-2 shadow-sm lg:mb-3 lg:p-3">
        <div class="flex flex-col gap-2 xl:flex-row xl:items-start xl:justify-between">
          <div class="min-w-0 space-y-1 lg:space-y-1.5">
            <p class="text-[10px] font-semibold uppercase tracking-[0.25em] text-slate-500">Fleet Management</p>
            <h1 class="text-base font-semibold tracking-tight text-slate-900">Vehicle booking calendar</h1>
            <p class="max-w-2xl text-xs text-slate-500 lg:text-sm">Schedule vehicles, assign drivers and monitor fleet availability.</p>
          </div>
          <div class="flex flex-wrap items-center gap-2">
            <div id="fleetStatusBadge" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-emerald-50 px-2 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700 lg:px-2.5 lg:py-1.5">
              <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
              Live availability
            </div>
            <button type="button" id="refreshBtn" class="inline-flex h-8 items-center gap-2 rounded-lg border border-slate-200 bg-white px-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 lg:h-9">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h5M20 20v-5h-5M5 14a7 7 0 0011.9 2.1L20 20m0-8a7 7 0 00-11.9-2.1L4 4" />
              </svg>
              <span>Refresh</span>
            </button>
            <button type="button" id="newBookingBtn" class="inline-flex h-8 items-center gap-2 rounded-lg bg-slate-900 px-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 lg:h-9">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              <span>New Booking</span>
            </button>
          </div>
        </div>

        <div id="fleetSnapshotSection" class="grid gap-1.5 lg:gap-2 sm:grid-cols-2 xl:grid-cols-4">
          <div class="rounded-xl border border-slate-200 bg-white p-2 shadow-sm lg:p-3">
            <p class="text-[10px] uppercase tracking-[0.2em] text-slate-500">Available Now</p>
            <p id="totalFleetCount" class="mt-1.5 text-base font-semibold text-emerald-700 lg:text-lg">0</p>
          </div>
          <div class="rounded-xl border border-slate-200 bg-white p-2 shadow-sm lg:p-3">
            <p class="text-[10px] uppercase tracking-[0.2em] text-slate-500">Booked Today</p>
            <p id="onTripCount" class="mt-1.5 text-base font-semibold text-slate-900 lg:text-lg">0</p>
          </div>
          <div class="rounded-xl border border-slate-200 bg-white p-2 shadow-sm lg:p-3">
            <p class="text-[10px] uppercase tracking-[0.2em] text-slate-500">Returning Soon</p>
            <p id="readyBookingCount" class="mt-1.5 text-base font-semibold text-slate-900 lg:text-lg">0</p>
          </div>
          <div class="rounded-xl border border-slate-200 bg-white p-2 shadow-sm lg:p-3">
            <p class="text-[10px] uppercase tracking-[0.2em] text-slate-500">Under Maintenance</p>
            <p id="driverAvailableCount" class="mt-1.5 text-base font-semibold text-slate-900 lg:text-lg">0</p>
          </div>
        </div>

</div>





        <div class="rounded-xl border border-slate-200 bg-white p-2 shadow-sm lg:p-3">
          <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <p class="text-[10px] uppercase tracking-[0.2em] text-slate-500">Filters</p>
              <h2 class="text-sm font-semibold text-slate-900">Refine fleet view</h2>
            </div>
          </div>
          <div class="mt-2 grid gap-1.5 lg:mt-3 lg:gap-2 sm:grid-cols-2 lg:grid-cols-4">
            <div class="relative">
              <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-4.35-4.35m1.85-5.15a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
                </svg>
              </span>
              <input id="fleetSearchInput" type="search" placeholder="Search vehicles" class="h-8 w-full rounded-lg border border-slate-200 bg-white pl-10 pr-4 text-sm text-slate-700 outline-none transition focus:border-slate-400" />
            </div>
            <div class="flex h-8 items-center gap-2 rounded-lg border border-slate-200 bg-white px-2.5">
              <label class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500" for="vehicleFilter">Vehicle</label>
              <select id="vehicleFilter" class="bg-transparent text-sm font-semibold text-slate-900 focus:outline-none cursor-pointer">
                <option value="">All</option>
                <?php foreach (($vehicles ?? []) as $v): ?>
                  <option value="<?= (int)$v['id'] ?>"><?= htmlspecialchars($v['vehicle_name'] . ' (' . $v['plate_number'] . ')') ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="flex h-8 items-center gap-2 rounded-lg border border-slate-200 bg-white px-2.5">
              <label class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500" for="driverFilter">Driver</label>
              <select id="driverFilter" class="bg-transparent text-sm font-semibold text-slate-900 focus:outline-none cursor-pointer">
                <option value="">All</option>
                <?php foreach (($drivers ?? []) as $d): ?>
                  <option value="<?= (int)$d['id'] ?>"><?= htmlspecialchars($d['driver_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="flex h-8 items-center gap-2 rounded-lg border border-slate-200 bg-white px-2.5 text-sm font-semibold text-slate-500">
              <span>Status</span>
            </div>
          </div>
        </div>

        <section class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm lg:p-4">
          <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <p class="text-[10px] uppercase tracking-[0.2em] text-slate-500">Availability</p>
              <h2 class="text-base font-semibold text-slate-900">Vehicle availability dashboard</h2>
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <div class="inline-flex h-9 overflow-hidden rounded-full border border-slate-200 bg-white">
                <button id="fleetTabVehiclesBtn" type="button" class="px-4 text-sm font-semibold text-slate-900 bg-slate-100">Vehicles (0)</button>
                <button id="fleetTabDriversBtn" type="button" class="border-l border-slate-200 px-4 text-sm font-semibold text-slate-600 hover:text-slate-900">Drivers (0)</button>
              </div>
              <div class="relative">
                <button id="fleetAddNewBtn" type="button" aria-expanded="false" class="inline-flex h-9 items-center gap-2 rounded-lg border border-slate-200 bg-slate-900 px-4 text-sm font-semibold text-white transition hover:bg-slate-800">
                  Add New
                  <span class="text-xs">▾</span>
                </button>
                <div id="fleetAddNewMenu" class="absolute right-0 z-10 mt-2 hidden w-48 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                  <button id="openVehicleModalBtn" type="button" class="w-full rounded-none border-b border-slate-200 px-4 py-2 text-left text-sm text-slate-900 hover:bg-slate-50">New Vehicle</button>
                  <button id="openDriversModalBtn" type="button" class="w-full rounded-none px-4 py-2 text-left text-sm text-slate-900 hover:bg-slate-50">New Driver</button>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-2 flex flex-wrap items-center gap-1.5 text-xs text-slate-500 lg:mt-3">
            <span id="fleetGalleryMeta">0 Vehicles • Page 1 of 1</span>
          </div>

          <div id="vehicleCardsContainer" class="mt-2 grid gap-1.5 lg:mt-3 lg:gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"></div>

          <div id="fleetPaginationControls" class="mt-2 flex flex-col gap-1.5 rounded-xl border border-slate-200 bg-white px-2.5 py-2 shadow-sm sm:flex-row sm:items-center sm:justify-between lg:mt-3 lg:gap-2 lg:px-3 lg:py-2.5">
            <div id="fleetPaginationInfo" class="text-sm text-slate-600">Showing 0–0 of 0 results</div>
            <div class="flex flex-wrap items-center gap-1.5">
              <div id="fleetPageNumbers" class="hidden flex flex-wrap items-center gap-1"></div>
              <button id="fleetPrevPageBtn" type="button" class="inline-flex h-8 items-center gap-2 rounded-lg border border-slate-200 bg-white px-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:pointer-events-none disabled:opacity-40">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Previous
              </button>
              <button id="fleetNextPageBtn" type="button" class="inline-flex h-8 items-center gap-2 rounded-lg border border-slate-200 bg-white px-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:pointer-events-none disabled:opacity-40">
                Next
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
              </button>
            </div>
          </div>
        </section>

        <div class="grid gap-2 calendar-split-panel">
          <div class="rounded-xl border border-slate-200 bg-white p-2 shadow-sm lg:p-3">
            <div class="mb-2 flex items-center justify-between gap-2 lg:mb-3">
              <div>
                <p class="text-[10px] uppercase tracking-[0.2em] text-slate-500">Calendar</p>
                <h2 class="text-base font-semibold text-slate-900">Fleet scheduling timeline</h2>
              </div>
            </div>
            <div id="carBookingCalendar" class=""></div>
          </div>

          <aside class="rounded-xl border border-slate-200 bg-white p-2 shadow-sm lg:p-3">
            <div class="space-y-2 lg:space-y-3">
              <div id="scheduleFocusPanel" class="rounded-xl  bg-white shadow-sm lg:p-3">
                <div class="flex items-start justify-between gap-2 lg:gap-3">
                  <div>
                    <p class="text-[10px] uppercase tracking-[0.24em] text-slate-500">Schedule focus</p>
                    <h3 class="mt-1 text-base font-semibold text-slate-900">Schedule Focus</h3>
                  </div>
                  <div class="rounded-full border border-emerald-200 bg-emerald-100 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-600">Live</div>
                </div>

                <div class="space-y-2 border-t border-slate-100 pt-2.5 lg:space-y-3 lg:pt-3">
                  <div>
                    <p class="text-[10px] uppercase tracking-[0.24em] text-slate-500">Today</p>
                    <div id="scheduleFocusToday" class="mt-2.5 space-y-1 text-sm text-slate-700">
                      <div class="flex items-center gap-2 text-slate-700">
                        <span id="scheduleFocusTodayDot" class="inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                        <span id="scheduleFocusTodayTitle" class="font-medium"></span>
                      </div>
                      <div id="scheduleFocusTodayMeta" class="text-sm text-slate-500"></div>
                    </div>
                  </div>

                  <div>
                    <p class="text-[10px] uppercase tracking-[0.24em] text-slate-500">Upcoming</p>
                    <div id="scheduleFocusUpcoming" class="mt-2.5 space-y-1 text-sm text-slate-700">
                      <div class="flex items-center gap-2 text-slate-700">
                        <span id="scheduleFocusUpcomingDot" class="inline-flex h-2 w-2 rounded-full bg-slate-400"></span>
                        <span id="scheduleFocusUpcomingTitle" class="font-medium"></span>
                      </div>
                      <div id="scheduleFocusUpcomingMeta" class="text-sm text-slate-500"></div>
                    </div>
                  </div>
                </div>
              </div>

              <div id="bookingDetailsWrapper" class="hidden rounded-lg  p-2 lg:p-3">
                <div class="flex items-start justify-between gap-2">
                  <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500">Booking details</p>
                    <h3 class="mt-0.5 text-sm font-medium text-slate-900">Selected booking</h3>
                  </div>
                  <div id="bookingPreviewStatus" class="rounded-full border border-slate-200 bg-white px-2 py-1 text-[10px] font-medium text-slate-600">No selection</div>
                </div>
                <div id="bookingPreviewEmpty" class="mt-2.5 text-sm text-slate-500">
                  Select a booked slot on the calendar to inspect, edit, cancel, or remove it.
                </div>
                <form id="bookingPreviewForm" class="mt-3 hidden space-y-2 lg:space-y-3">
                  <input type="hidden" name="id" id="previewBookingId" value="">
                  <div class="grid gap-3 md:grid-cols-2">
                    <div class="md:col-span-2">
                      <label class="mb-1 block text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500" for="previewPurpose">Purpose</label>
                      <input type="text" id="previewPurpose" name="purpose" class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none focus:border-slate-400" required>
                    </div>
                    <div class="md:col-span-2">
                      <label class="mb-1 block text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500" for="previewDestinations">Destinations</label>
                      <input type="text" id="previewDestinations" name="destinations" class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none focus:border-slate-400" required>
                    </div>
                    <div>
                      <label class="mb-1 block text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500" for="previewDateTrip">Trip date</label>
                      <input type="date" id="previewDateTrip" name="date_trip" class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none focus:border-slate-400" required>
                    </div>
                    <div>
                      <label class="mb-1 block text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500" for="previewDateRequested">Requested</label>
                      <input type="date" id="previewDateRequested" name="date_requested" class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none focus:border-slate-400" required>
                    </div>
                    <div>
                      <label class="mb-1 block text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500" for="previewDepartureExpected">Departure</label>
                      <input type="datetime-local" id="previewDepartureExpected" name="departure_expected" class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none focus:border-slate-400" required>
                    </div>
                    <div>
                      <label class="mb-1 block text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500" for="previewReturnExpected">Return</label>
                      <input type="datetime-local" id="previewReturnExpected" name="return_expected" class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none focus:border-slate-400" required>
                    </div>
                    <div>
                      <label class="mb-1 block text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500" for="previewPassengers">Passengers</label>
                      <input type="number" id="previewPassengers" name="passengers" min="1" class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none focus:border-slate-400" required>
                    </div>
                    <div>
                      <label class="mb-1 block text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500" for="previewVehicleId">Vehicle</label>
                      <select id="previewVehicleId" name="vehicle_id" class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none focus:border-slate-400" required>
                        <option value="">Select a vehicle</option>
                        <?php foreach (($vehicles ?? []) as $v): ?>
                          <option value="<?= (int)$v['id'] ?>"><?= htmlspecialchars($v['vehicle_name'] . ' (' . $v['plate_number'] . ')') ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div>
                      <label class="mb-1 block text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500" for="previewDriverId">Driver</label>
                      <select id="previewDriverId" name="driver_id" class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none focus:border-slate-400" required>
                        <option value="">Select a driver</option>
                        <?php foreach (($drivers ?? []) as $d): ?>
                          <option value="<?= (int)$d['id'] ?>"><?= htmlspecialchars($d['driver_name']) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="md:col-span-2">
                      <label class="mb-1 block text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500" for="previewSpecialInstructions">Special instructions</label>
                      <textarea id="previewSpecialInstructions" name="special_instructions" rows="2" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 outline-none focus:border-slate-400"></textarea>
                    </div>
                    <div class="md:col-span-2">
                      <label class="mb-1 block text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500" for="previewRemarks">Remarks</label>
                      <textarea id="previewRemarks" name="remarks" rows="2" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 outline-none focus:border-slate-400"></textarea>
                    </div>
                  </div>
                 <div class="flex flex-col gap-2 border-t border-slate-200 pt-3">
                    <button
                        type="submit"
                        class="inline-flex h-9 w-full items-center justify-center rounded-lg bg-slate-900 px-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                        Save changes
                    </button>

                    <button
                        type="button"
                        id="previewCancelViewBtn"
                        class="inline-flex h-9 w-full items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Cancel view
                    </button>

                    <button
                        type="button"
                        id="previewCancelBtn"
                        class="inline-flex h-9 w-full items-center justify-center rounded-lg border border-rose-200 bg-rose-50 px-3 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">
                        Cancel booking
                    </button>
                </div>
                </form>
              </div>
            </div>
          </aside>
        </div>
      </div>
    </div>
  </div>
</div>


  <!-- Calendar Container -->
  <!-- <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden"> -->
    <!-- Toolbar -->
    <!-- <div class="border-b border-slate-100 px-6 py-4 card-header">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="inline-block">
            <div id="external-events" class="hidden sm:inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-200 bg-white text-sm shadow-sm">
              <span class="text-slate-400">⟡</span>
              <span class="text-slate-600 font-medium">Drag "New booking" to create</span>
            </div>
          </div>
        </div> -->

        <div class="flex items-center gap-2 flex-wrap">
      
          
          </div>

          <button type="button" id="refreshBtn" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">

          </button>

     
        </div>
      </div>
    </div>

    <!-- Calendar -->
    <!-- <div id="carBookingCalendar" class="min-h-[600px] p-6"></div> -->
  </div>

  <!-- Vehicle Cards Section -->
  <!-- <div>
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
</div> -->

<!-- ============================================
     BOOKING MODAL
     ============================================ -->
<div id="bookingModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="bookingModalTitle">
  <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" id="bookingModalBackdrop"></div>

  <div class="relative mx-auto my-6 w-[95vw] max-w-4xl modal-panel">
    <!-- Modal Header -->
    <div class="modal-header card-header px-4 py-4 lg:px-5 lg:py-5">
      <div>
        <h2 class="text-lg font-semibold text-slate-900" id="bookingModalTitle">Create New Booking</h2>

        <p class="mt-1 text-sm text-slate-500">Fill in the details below to schedule a vehicle.</p>
      </div>
      <button type="button" id="closeBookingModalBtn" class="modal-close" aria-label="Close">
        <span class="text-lg">✕</span>
      </button>
    </div>

    <!-- Modal Content -->
    <form id="bookingForm" class="modal-body p-4 lg:p-5" method="POST" action="">
      <input type="hidden" name="id" id="bookingId" value="">

      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:gap-4">
        <!-- Date of Trip -->
        <div>
          <label class="text-sm font-semibold text-slate-700 block mb-2">Date of Trip <span class="text-red-500">*</span></label>
          <input type="date" name="date_trip" id="dateTrip" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none transition" required />
          <p class="text-xs text-slate-500 mt-1">Select the trip date.</p>
        </div>

        <!-- Date Requested -->
        <div>
          <label class="text-sm font-semibold text-slate-700 block mb-2">Date Requested <span class="text-red-500">*</span></label>
          <input type="date" name="date_requested" id="dateRequested" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none transition" required />
          <p class="text-xs text-slate-500 mt-1">When was this booking requested?</p>
        </div>

        <!-- Destinations -->
        <div class="sm:col-span-2">
          <label class="text-sm font-semibold text-slate-700 block mb-2">Destinations <span class="text-red-500">*</span></label>
          <input type="text" name="destinations" id="destinations" placeholder="e.g., Downtown to Airport via City Hall" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none transition" required />
        </div>

        <!-- Purpose -->
        <div class="sm:col-span-2">
          <label class="text-sm font-semibold text-slate-700 block mb-2">Purpose <span class="text-red-500">*</span></label>
          <input type="text" name="purpose" id="purpose" placeholder="e.g., Business meeting, Client transport" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none transition" required />
        </div>

        <!-- Passengers -->
        <div>
          <label class="text-sm font-semibold text-slate-700 block mb-2">Number of Passengers <span class="text-red-500">*</span></label>
          <input type="number" name="passengers" id="passengers" min="1" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none transition" required />
        </div>

        <!-- Empty space -->
        <div></div>

        <!-- Departure Expected -->
        <div>
          <label class="text-sm font-semibold text-slate-700 block mb-2">Expected Departure <span class="text-red-500">*</span></label>
          <input type="datetime-local" name="departure_expected" id="departureExpected" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none transition" required />
          <p class="text-xs text-slate-500 mt-1">Expected departure date and time.</p>
        </div>

        <!-- Return Expected -->
        <div>
          <label class="text-sm font-semibold text-slate-700 block mb-2">Expected Return <span class="text-red-500">*</span></label>
          <input type="datetime-local" name="return_expected" id="returnExpected" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none transition" required />
          <p class="text-xs text-slate-500 mt-1">Expected return date and time.</p>
        </div>

        <!-- Vehicle Selection -->
        <div>
          <label class="text-sm font-semibold text-slate-700 block mb-2">Assigned Vehicle <span class="text-red-500">*</span></label>
          <select name="vehicle_id" id="vehicleId" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none transition" required>
            <option value="">-- Select a vehicle --</option>
            <?php foreach (($vehicles ?? []) as $v): ?>
              <option value="<?= (int)$v['id'] ?>" data-plate="<?= htmlspecialchars((string)$v['plate_number']) ?>"><?= htmlspecialchars($v['vehicle_name'] . ' (' . $v['plate_number'] . ')') ?></option>
            <?php endforeach; ?>
          </select>
          <div class="mt-2 flex flex-col gap-1">
            <div id="bookingVehicleRestrictionStatus" class="inline-flex w-fit items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-600"></div>
            <div id="bookingVehicleRestrictionLabel" class="text-[11px] font-semibold text-slate-600"></div>
            <div id="bookingVehicleRestrictionQuickNote" class="text-[11px] text-slate-500"></div>
          </div>
        </div>

        <!-- Driver Selection -->
        <div>
          <label class="text-sm font-semibold text-slate-700 block mb-2">Assigned Driver <span class="text-red-500">*</span></label>
          <select name="driver_id" id="driverId" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none transition" required>
            <option value="">-- Select a driver --</option>
            <?php foreach (($drivers ?? []) as $d): ?>
              <option value="<?= (int)$d['id'] ?>"><?= htmlspecialchars($d['driver_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Special Instructions -->
        <div class="sm:col-span-2">
          <label class="text-sm font-semibold text-slate-700 block mb-2">Special Instructions</label>
          <textarea name="special_instructions" id="specialInstructions" rows="3" placeholder="Any special requirements? (e.g., wheelchair accessible, extra luggage space)" class="w-full resize-none rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none transition"></textarea>
        </div>

        <!-- Remarks -->
        <div class="sm:col-span-2">
          <label class="text-sm font-semibold text-slate-700 block mb-2">Remarks</label>
          <textarea name="remarks" id="remarks" rows="3" placeholder="Additional notes about this booking..." class="w-full resize-none rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none transition"></textarea>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="modal-footer px-4 py-3 lg:px-5 lg:py-4">
        <button type="button" id="cancelBookingModalBtn" class="btn-ghost">Cancel</button>
        <button type="button" id="deleteBookingBtn" class="btn-danger hidden">Delete</button>
        <button type="submit" id="saveBookingBtn" class="btn-primary">
          <span>✓</span>
          <span>Save Booking</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ============================================
     VEHICLE MODAL
     ============================================ -->
  <div id="vehicleModal" class="fixed inset-0 z-50 hidden bg-white" role="dialog" aria-modal="true" aria-labelledby="vehicleModalTitle">
  <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" id="vehicleModalBackdrop"></div>
  <div class="relative mx-auto my-6 w-[95vw] max-w-6xl modal-panel">
    <div class="modal-header card-header px-5 py-4 lg:px-6 lg:py-5">
      <div>
        <h2 id="vehicleModalTitle" class="text-lg font-semibold text-slate-900">Manage Vehicles</h2>
        <p class="mt-1 text-sm text-slate-500">Add new vehicles or modify existing ones</p>
      </div>
      <button type="button" id="closeVehicleModalBtn" class="modal-close" aria-label="Close">
        <span class="text-lg">✕</span>
      </button>
    </div>

    <div class="modal-body p-5 lg:p-6">
      <!-- Add Vehicle Form -->
      <div class="vehicle-form-card mb-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm lg:mb-5 lg:p-5">
        <form id="vehicleForm" class="space-y-4 lg:space-y-5">
          <div class="flex flex-col gap-2 border-b border-slate-100 pb-3 sm:flex-row sm:items-center sm:justify-between">
            <h3 class="text-base font-semibold text-slate-900">Add / Edit Vehicle</h3>
            <p class="text-xs text-slate-500">Keep fields concise — plate number and capacity are required.</p>
          </div>
        <input type="hidden" name="id" id="vehicleIdInput" value="">

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 items-end">
          <div class="sm:col-span-2">
            <label class="mb-2 block text-xs font-semibold text-slate-700">Plate Number <span class="text-red-500">*</span></label>
            <input type="text" name="plate_number" id="plateNumber" placeholder="e.g., ABC-1234" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none transition" required />
          </div>

          <div>
            <label class="mb-2 block text-xs font-semibold text-slate-700">Capacity <span class="text-red-500">*</span></label>
            <input type="number" name="capacity" id="capacity" min="1" placeholder="Seats" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none transition" required />
          </div>

          <div class="sm:col-span-2">
            <label class="mb-2 block text-xs font-semibold text-slate-700">Vehicle Name <span class="text-red-500">*</span></label>
            <input type="text" name="vehicle_name" id="vehicleName" placeholder="e.g., Toyota Hiace Van" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none transition" required />
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-700 block mb-2">Status <span class="text-red-500">*</span></label>
            <select name="status" id="vehicleStatus" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none transition" required>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>

          <div class="sm:col-span-3 flex items-center gap-2 justify-end">
            <button type="button" id="resetVehicleFormBtn" class="btn-ghost">Reset</button>
            <button type="submit" class="btn-primary">Save Vehicle</button>
          </div>
        </div>
        </form>
      </div>
      <script>
        (function(){
          const form = document.getElementById('vehicleForm');
          if (!form) return;

          // build preview + file input block
          const block = document.createElement('div');
          block.className = 'mb-4 grid grid-cols-1 sm:grid-cols-3 gap-4 items-start';

          const previewWrap = document.createElement('div');
          previewWrap.className = 'sm:col-span-1 flex items-center';
          previewWrap.innerHTML = `
            <div class="w-28 h-20 rounded-md border border-slate-200 overflow-hidden bg-surface-1 flex items-center justify-center">
              <img id="vehicleImagePreview" src="" alt="" class="w-full h-full object-cover hidden" />
              <div id="vehicleImagePlaceholder" class="text-xs text-slate-400">No image</div>
            </div>
          `;

          const inputWrap = document.createElement('div');
          inputWrap.className = 'sm:col-span-2';
          inputWrap.innerHTML = `
            <label class="text-xs font-semibold text-slate-700 block mb-2">Vehicle Image</label>
            <div class="flex items-center gap-3">
              <input type="file" name="vehicle_image" id="vehicleImage" accept="image/*" class="text-sm" />
              <button type="button" id="vehicleImageClear" class="rounded border border-slate-200 bg-white px-3 py-1 text-xs text-slate-700">Clear</button>
            </div>
            <p class="text-xs text-slate-500 mt-1">Optional: upload a photo (jpg, png, gif, webp). Max 2MB.</p>
          `;

          block.appendChild(previewWrap);
          block.appendChild(inputWrap);
          form.insertBefore(block, form.firstChild);

          const fileInput = document.getElementById('vehicleImage');
          const previewImg = document.getElementById('vehicleImagePreview');
          const placeholder = document.getElementById('vehicleImagePlaceholder');
          const clearBtn = document.getElementById('vehicleImageClear');

          function showPreviewFile(file) {
            if (!file) { previewImg.src = ''; previewImg.classList.add('hidden'); placeholder.classList.remove('hidden'); return; }
            const reader = new FileReader();
            reader.onload = function(e) {
              previewImg.src = e.target.result;
              previewImg.classList.remove('hidden');
              placeholder.classList.add('hidden');
            };
            reader.readAsDataURL(file);
          }

          fileInput.addEventListener('change', function () {
            const f = this.files && this.files[0] ? this.files[0] : null;
            if (f) {
              // basic client-side checks
              const allowed = ['image/jpeg','image/png','image/gif','image/webp'];
              if (!allowed.includes(f.type)) {
                alert('Unsupported image type. Allowed: jpg, png, gif, webp');
                this.value = '';
                showPreviewFile(null);
                return;
              }
              if (f.size > 2 * 1024 * 1024) {
                alert('Image too large (max 2MB)');
                this.value = '';
                showPreviewFile(null);
                return;
              }
            }
            showPreviewFile(f);
          });

          clearBtn.addEventListener('click', function () {
            fileInput.value = '';
            showPreviewFile(null);
          });

          // expose a helper for the edit flow
          window._setVehicleImagePreview = function (url) {
            if (!url) { showPreviewFile(null); return; }
            previewImg.src = url; previewImg.classList.remove('hidden'); placeholder.classList.add('hidden');
          };
        })();
      </script>

      <!-- Vehicles Table -->
      <div class="rounded-lg border border-slate-200 overflow-hidden vehicles-table">
        <div class="flex items-center justify-between gap-3 px-3 py-2 card-header lg:px-4 lg:py-3">
          <div>
            <h3 class="text-sm font-semibold text-slate-900">Vehicles</h3>
            <p class="text-xs text-slate-500">Manage fleet — edit details or toggle availability.</p>
          </div>
          <select id="vehiclesListStatusFilter" class="bg-white text-xs text-slate-700 border border-slate-200 rounded-lg px-3 py-1.5 focus:outline-none transition">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div class="max-h-[320px] overflow-auto">
          <table class="w-full text-left text-sm">
            <thead class="sticky top-0 bg-surface-1 border-b border-slate-200">
              <tr>
                <th class="px-3 py-2 font-semibold text-slate-700 lg:px-4 lg:py-3">Vehicle</th>
                <th class="px-3 py-2 font-semibold text-slate-700 lg:px-4 lg:py-3">Plate</th>
                <th class="px-3 py-2 font-semibold text-slate-700 lg:px-4 lg:py-3">Capacity</th>
                <th class="px-3 py-2 font-semibold text-slate-700 lg:px-4 lg:py-3">Status</th>
                <th class="px-3 py-2 font-semibold text-slate-700 text-right lg:px-4 lg:py-3">Actions</th>
              </tr>
            </thead>
            <tbody id="vehiclesTableBody">
              <tr>
                <td colspan="5" class="px-3 py-4 text-center text-slate-500 lg:px-4">Loading vehicles...</td>
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
  <div id="driversModal" class="fixed inset-0 z-50 hidden bg-white" role="dialog" aria-modal="true" aria-labelledby="driversModalTitle">
  <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" id="driversModalBackdrop"></div>
  <div class="relative mx-auto my-6 w-[95vw] max-w-6xl modal-panel">
    <div class="modal-header card-header px-5 py-4 lg:px-6 lg:py-5">
      <div>
        <h2 id="driversModalTitle" class="text-lg font-semibold text-slate-900">Manage Drivers</h2>
        <p class="mt-1 text-sm text-slate-500">Add new drivers or modify existing ones</p>
      </div>
        <button type="button" id="closeDriversModalBtn" class="modal-close" aria-label="Close">
          <span class="text-lg">✕</span>
        </button>
    </div>

    <div class="modal-body p-5 lg:p-6">
      <!-- Add Driver Form -->
      <div class="driver-form-card mb-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm lg:mb-5 lg:p-5">
        <form id="driverForm" class="space-y-4 lg:space-y-5">
          <div class="flex flex-col gap-2 border-b border-slate-100 pb-3 sm:flex-row sm:items-center sm:justify-between">
            <h3 class="text-base font-semibold text-slate-900">Add / Edit Driver</h3>
            <p class="text-xs text-slate-500">Provide the driver's full name and status.</p>
          </div>
          <input type="hidden" name="id" id="driverModalId">

          <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3 items-end lg:gap-4">
            <div>
              <label class="text-xs font-semibold text-slate-700 block mb-2">Employee ID <span class="text-red-500">*</span></label>
              <input type="text" name="employee_id" id="employeeIdInput" placeholder="e.g., EMP-001" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none transition" required />
            </div>

            <div>
              <label class="text-xs font-semibold text-slate-700 block mb-2">First Name <span class="text-red-500">*</span></label>
              <input type="text" name="first_name" id="firstNameInput" placeholder="e.g., John" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none transition" required />
            </div>

            <div>
              <label class="text-xs font-semibold text-slate-700 block mb-2">Last Name <span class="text-red-500">*</span></label>
              <input type="text" name="last_name" id="lastNameInput" placeholder="e.g., Doe" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none transition" required />
            </div>

            <div>
              <label class="text-xs font-semibold text-slate-700 block mb-2">Mobile Number</label>
              <input type="text" name="mobile_number" id="mobileNumberInput" placeholder="e.g., 08012345678" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none transition" />
            </div>

            <div>
              <label class="text-xs font-semibold text-slate-700 block mb-2">Email</label>
              <input type="email" name="email" id="emailInput" placeholder="e.g., john@example.com" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none transition" />
            </div>

            <div>
              <label class="text-xs font-semibold text-slate-700 block mb-2">License Number <span class="text-red-500">*</span></label>
              <input type="text" name="license_number" id="licenseNumberInput" placeholder="e.g., ABC12345" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none transition" required />
            </div>

            <div>
              <label class="text-xs font-semibold text-slate-700 block mb-2">License Class <span class="text-red-500">*</span></label>
              <input type="text" name="license_class" id="licenseClassInput" placeholder="e.g., B" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none transition" required />
            </div>

            <div>
              <label class="text-xs font-semibold text-slate-700 block mb-2">License Expiry <span class="text-red-500">*</span></label>
              <input type="date" name="license_expiry" id="licenseExpiryInput" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none transition" required />
            </div>

            <div>
              <label class="text-xs font-semibold text-slate-700 block mb-2">Status <span class="text-red-500">*</span></label>
              <select name="status" id="driverStatusInput" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none transition" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>

            <input type="hidden" name="driver_name" id="driverNameInput" value="" />

            <div class="sm:col-span-3 flex items-center gap-2 justify-end">
              <button type="button" id="resetDriverFormBtn" class="btn-ghost">Reset</button>
              <button type="submit" class="btn-primary">Save Driver</button>
            </div>
          </div>
        </form>
      </div>

      <!-- Drivers Table -->
      <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm drivers-table">
        <div class="flex items-center justify-between gap-3 px-4 py-3 card-header lg:px-5 lg:py-4">
          <div>
            <h3 class="text-sm font-semibold text-slate-900">Drivers</h3>
            <p class="text-xs text-slate-500">Manage driver records and availability.</p>
          </div>
          <select id="driversListStatusFilter" class="bg-white text-xs text-slate-700 border border-slate-200 rounded-lg px-3 py-1.5 focus:outline-none transition">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div class="max-h-[420px] overflow-auto">
          <table class="w-full text-left text-sm">
            <thead class="sticky top-0 bg-surface-1 border-b border-slate-200">
              <tr>
                <th class="px-3 py-2 font-semibold text-slate-700 lg:px-4 lg:py-3">Full Name</th>
                <th class="px-3 py-2 font-semibold text-slate-700 lg:px-4 lg:py-3">Employee ID</th>
                <th class="px-3 py-2 font-semibold text-slate-700 lg:px-4 lg:py-3">License Number</th>
                <th class="px-3 py-2 font-semibold text-slate-700 lg:px-4 lg:py-3">License Class</th>
                <th class="px-3 py-2 font-semibold text-slate-700 lg:px-4 lg:py-3">License Expiry</th>
                <th class="px-3 py-2 font-semibold text-slate-700 lg:px-4 lg:py-3">Status</th>
                <th class="px-3 py-2 font-semibold text-slate-700 text-right lg:px-4 lg:py-3">Actions</th>
              </tr>
            </thead>
            <tbody id="driversTableBody">
              <tr>
                <td colspan="7" class="px-3 py-4 text-center text-slate-500 lg:px-4">Loading drivers...</td>
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

<!-- Premium CRM Calendar Styling -->
<style>
  :root {
    --text: #0f172a;
    --bg-page: #fbfbfe;
    --bg-card: #ffffff;
    --surface-1: #f6f7f8;
    --surface-2: #eef0f2;
    --primary: #059669;
    --secondary: #22242a;
    --accent: #888888;
    --muted: #64748b;
    --shadow-1: 0 8px 24px rgba(4, 3, 22, 0.06);
    --shadow-2: 0 12px 36px rgba(4, 3, 22, 0.08);
  }

  /* FullCalendar base styling */
  .fc {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  }

  .fc .fc-toolbar {
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    padding: 0;
    border: none;
  }

  .fc .fc-toolbar-chunk {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
  }

  .fc .fc-button {
    padding: 0.5rem 1rem;
    font-size: 0.75rem;
    height: 2rem;
    border-radius: 0.5rem;
    border-color: rgba(15, 23, 42, 0.1);
    background: #ffffff;
    color: #0f172a;
    box-shadow: none;
    font-weight: 600;
    transition: all 0.2s ease;
  }

  .fc .fc-button:hover {
    background: #f1f5f9;
    border-color: rgba(15, 23, 42, 0.15);
    transform: translateY(-1px);
  }

  .fc .fc-button-primary:not(:disabled).fc-button-active,
  .fc .fc-button-primary:not(:disabled):hover {
    background: #059669;
    border-color: #047857;
    color: #ffffff;
  }

  .fc .fc-button-primary {
    background: #f1f5f9;
    border-color: rgba(15, 23, 42, 0.1);
    color: #0f172a;
  }

  .fc .fc-scrollgrid {
    border-color: #f1f5f9;
  }

  .fc .fc-theme-standard td,
  .fc .fc-theme-standard th {
    border-color: #f1f5f9;
  }

  .fc .fc-daygrid-day-frame {
    min-height: 80px;
    padding: 0.5rem;
  }

  .fc .fc-daygrid-day-top {
    padding: 0.5rem 0.5rem 0;
  }

  .fc .fc-daygrid-day-number {
    color: #64748b;
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
  }

  .fc .fc-day-today {
    background-color: rgba(16, 185, 129, 0.08) !important;
  }

  .fc .fc-day-today .fc-daygrid-day-number {
    color: #047857;
    font-weight: 600;
  }

  .fc .fc-event {
    border: none;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
    font-size: 0.75rem;
    padding: 0.35rem 0.5rem;
    border-radius: 0.5rem;
    margin-bottom: 0.25rem;
    opacity: 0.95;
    transition: all 0.2s ease;
  }

  .fc .fc-event:hover {
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.12);
    transform: translateY(-1px);
  }

  .fc .fc-event-main-frame {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .fc .fc-event-title {
    font-weight: 600;
    letter-spacing: 0.01em;
  }

  .fc .fc-more-link {
    color: #059669;
    font-size: 0.75rem;
    text-decoration: none;
    font-weight: 600;
  }

  .fc .fc-more-link:hover {
    text-decoration: underline;
  }

  /* Toolbar title styling */
  .fc .fc-toolbar-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #0f172a;
  }

  @media (max-width: 640px) {
    .fc {
      font-size: 0.85rem;
    }

    .fc .fc-toolbar-title {
      font-size: 1.2rem;
    }

    .fc .fc-daygrid-day-frame {
      min-height: 70px;
    }
  }


  /* Toast styles */
  .app-toast {
    display: flex;
    gap: 12px;
    align-items: center;
    min-width: 220px;
    max-width: 520px;
    padding: 12px 14px;
    border-radius: 12px;
    color: var(--text);
    box-shadow: var(--shadow-2);
    font-weight: 700;
    background: var(--bg-card);
    border: 1px solid rgba(15, 23, 42, 0.08);
  }

  .app-toast .toast-icon {
    width: 20px;
    height: 20px;
    flex: 0 0 20px;
    opacity: 0.95;
  }

  .app-toast .toast-message {
    flex: 1;
    font-size: 13px;
    line-height: 1.15;
    color: var(--text);
  }

  .app-toast .toast-close {
    margin-left: 8px;
    background: transparent;
    border: none;
    color: var(--muted);
    cursor: pointer;
    padding: 6px;
    border-radius: 8px;
    font-weight: 700;
  }

  .app-toast-success {
    border-left: 4px solid #10b981;
    background: linear-gradient(90deg, rgba(16, 185, 129, 0.06), var(--bg-card));
  }

  .app-toast-error {
    border-left: 4px solid #dc2626;
    background: linear-gradient(90deg, rgba(220, 38, 38, 0.06), var(--bg-card));
  }

  .app-toast-info {
    border-left: 4px solid #2563eb;
    background: linear-gradient(90deg, rgba(37, 99, 235, 0.06), var(--bg-card));
  }

  .app-toast-warning {
    border-left: 4px solid #f59e0b;
    background: linear-gradient(90deg, rgba(245, 158, 11, 0.06), var(--bg-card));
  }

  /* Modal styling */
  .modal-panel {
    border-radius: 16px;
    overflow: hidden;
    background: var(--bg-card);
    box-shadow: 0 20px 60px rgba(2, 6, 23, 0.12);
  }

  .modal-header {
    padding: 20px;
    border-bottom: 1px solid #eef2f7;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
  }

  .modal-body {
    padding: 20px;
    max-height: calc(100vh - 240px);
    overflow: auto;
  }

  .modal-footer {
    padding: 18px 20px;
    border-top: 1px solid #f1f5f9;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    align-items: center;
  }

  .modal-close {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: transparent;
    color: #64748b;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .modal-close:hover {
    background: rgba(15, 23, 42, 0.06);
    color: #0f172a;
  }

  /* Button styling */
  .btn-primary {
    background: #059669;
    color: #fff;
    padding: 10px 16px;
    border-radius: 10px;
    border: none;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(5, 150, 105, 0.15);
    display: inline-flex;
    gap: 8px;
    align-items: center;
    transition: all 0.2s ease;
    cursor: pointer;
  }

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(5, 150, 105, 0.2);
    background: #047857;
  }

  .btn-ghost {
    background: transparent;
    border-radius: 10px;
    padding: 8px 12px;
    border: 1px solid transparent;
    color: var(--muted);
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .btn-ghost:hover {
    background: rgba(15, 23, 42, 0.04);
    color: var(--text);
  }

  .btn-danger {
    background: #fff;
    border: 1px solid rgba(220, 38, 38, 0.12);
    color: #dc2626;
    border-radius: 10px;
    padding: 8px 12px;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .btn-danger:hover {
    background: rgba(220, 38, 38, 0.05);
    border-color: rgba(220, 38, 38, 0.2);
  }

  .btn-icon {
    background: transparent;
    border: 1px solid rgba(15, 23, 42, 0.1);
    color: var(--text);
    padding: 6px 8px;
    border-radius: 8px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .btn-icon:hover {
    background: #f1f5f9;
    border-color: rgba(15, 23, 42, 0.15);
  }

  .btn-icon-danger {
    color: #b91c1c;
    border-color: rgba(185, 28, 28, 0.1);
  }

  /* Form fields */
  select:focus,
  input:focus,
  textarea:focus {
    box-shadow: 0 8px 30px rgba(2, 6, 23, 0.08);
    border-color: rgba(15, 23, 42, 0.15);
    outline: none;
  }

  /* Table styling */
  .vehicles-table thead th {
    background: transparent;
    color: var(--muted);
    font-weight: 700;
    text-transform: none;
  }

  .vehicles-table tbody tr {
    transition: background 0.12s ease, transform 0.08s ease;
  }

  .vehicles-table tbody tr:hover {
    background: #fbfcfd;
    transform: translateY(-1px);
  }

  .vehicle-badge {
    display: inline-block;
    padding: 6px 8px;
    border-radius: 999px;
    font-size: 12px;
    color: var(--text);
    background: rgba(15, 23, 39, 0.03);
    border: 1px solid rgba(15, 23, 39, 0.04);
  }

  .vehicle-badge.inactive {
    color: #64748b;
    background: rgba(99, 102, 241, 0.03);
  }

  table.w-full td,
  table.w-full th {
    border-bottom: 1px solid rgba(15, 23, 42, 0.03);
  }

  .vehicle-form-card input,
  .vehicle-form-card select,
  .vehicle-form-card textarea {
    background: #ffffff;
  }

  .drivers-table thead th {
    background: transparent;
    color: var(--muted);
    font-weight: 700;
    text-transform: none;
  }

  .drivers-table tbody tr {
    transition: background 0.12s ease, transform 0.08s ease;
  }

  .drivers-table tbody tr:hover {
    background: #fbfcfd;
    transform: translateY(-1px);
  }

  .driver-badge {
    display: inline-block;
    padding: 6px 8px;
    border-radius: 999px;
    font-size: 12px;
    color: var(--text);
    background: rgba(15, 23, 39, 0.03);
    border: 1px solid rgba(15, 23, 39, 0.04);
  }

  .card-header {
    background: transparent;
    border-bottom: 1px solid rgba(15, 23, 42, 0.06);
  }

  /* Fleet module compact spacing overrides */
  .fleet-module .rounded-xl,
  .fleet-module .rounded-lg,
  .fleet-module .card-header,
  .fleet-module .modal-body,
  .fleet-module .modal-footer,
  .fleet-module .modal-header {
    padding: 0.75rem !important;
  }

  .fleet-module [class*="p-4"] { padding: 0.75rem !important; }
  .fleet-module [class*="p-3"] { padding: 0.5rem !important; }
  .fleet-module [class*="p-2"] { padding: 0.375rem !important; }
  .fleet-module [class*="px-5"],
  .fleet-module [class*="py-5"] { padding: 0.75rem !important; }
  .fleet-module [class*="px-4"],
  .fleet-module [class*="py-4"] { padding: 0.65rem !important; }
  .fleet-module [class*="px-3"],
  .fleet-module [class*="py-3"] { padding: 0.5rem !important; }

  .fleet-module [class*="gap-4"] { gap: 0.75rem !important; }
  .fleet-module [class*="gap-3"] { gap: 0.5rem !important; }
  .fleet-module [class*="gap-2"] { gap: 0.375rem !important; }
  .fleet-module [class*="gap-1"] { gap: 0.25rem !important; }
  .fleet-module [class*="space-y-4"] > * + * { margin-top: 0.75rem !important; }
  .fleet-module [class*="space-y-3"] > * + * { margin-top: 0.5rem !important; }
  .fleet-module [class*="space-y-2"] > * + * { margin-top: 0.375rem !important; }

  .fleet-module [class*="mt-4"] { margin-top: 0.75rem !important; }
  .fleet-module [class*="mb-4"] { margin-bottom: 0.75rem !important; }
  .fleet-module [class*="mt-3"] { margin-top: 0.5rem !important; }
  .fleet-module [class*="mb-3"] { margin-bottom: 0.5rem !important; }
  .fleet-module [class*="mt-2"] { margin-top: 0.375rem !important; }
  .fleet-module [class*="mb-2"] { margin-bottom: 0.375rem !important; }

  .fleet-module [class*="h-9"] { height: 2rem !important; }
  .fleet-module [class*="h-10"] { height: 2.25rem !important; }

  .fleet-module [class*="px-4"] { padding-left: 0.65rem !important; padding-right: 0.65rem !important; }
  .fleet-module [class*="text-sm"] { font-size: 0.875rem; }

  .fleet-module #vehicleCardsContainer {
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)) !important;
  }

  .fleet-module .calendar-split-panel {
    grid-template-columns: minmax(240px, 2.2fr) minmax(160px, 1fr);
    align-items: start;
  }

  .fleet-module .calendar-split-panel > * {
    min-width: 0;
    width: 100%;
  }

  .fleet-module .calendar-split-panel > div,
  .fleet-module .calendar-split-panel > aside {
    max-width: 100%;
  }

  .fleet-module #fleetPaginationControls {
    padding: 0.75rem !important;
  }

  .fleet-module #fleetGalleryMeta,
  .fleet-module #fleetPaginationInfo {
    font-size: 0.85rem !important;
  }

  @media (max-width: 920px) {
    .fleet-module .calendar-split-panel {
      grid-template-columns: minmax(240px, 1.7fr) minmax(200px, 0.9fr);
    }
  }

  @media (max-width: 760px) {
    .fleet-module .calendar-split-panel {
      grid-template-columns: minmax(220px, 1.6fr) minmax(180px, 1fr);
    }
  }

  @media (max-width: 1280px) {
    .fleet-module .xl\:grid-cols-4 {
      grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    }

    .fleet-module .xl\:grid-cols-3 {
      grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }
  }

  @media (max-width: 1024px) {
    .fleet-module .xl\:grid-cols-4,
    .fleet-module .lg\:grid-cols-3,
    .fleet-module .sm\:grid-cols-2 {
      grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
    }

    .fleet-module .flex-wrap {
      gap: 0.5rem !important;
    }
  }
</style>


  <!-- Vehicle Details Modal (shows history and clickable bookings) -->
  <div id="vehicleDetailsModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="vehicleDetailsModalTitle">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" id="vehicleDetailsModalBackdrop"></div>
    <div class="relative mx-auto my-6 w-[95vw] max-w-5xl modal-panel">
      <div class="modal-header card-header px-4 py-4 lg:px-5 lg:py-5">
        <div>
          <h2 id="vehicleDetailsModalTitle" class="text-base font-semibold text-slate-900">Vehicle Booking Timeline</h2>
          <p id="vehicleDetailsModalSubtitle" class="mt-1 text-sm text-slate-500">Recent booking activity and upcoming commitments for this vehicle.</p>
          <div id="vehicleDetailsMeta" class="mt-2 flex flex-wrap gap-2"></div>
          <div id="vehicleDetailsActions" class="mt-3 flex flex-wrap gap-2"></div>
        </div>
        <button type="button" id="closeVehicleDetailsModalBtn" class="modal-close" aria-label="Close">
          <span class="text-lg">✕</span>
        </button>
      </div>

      <div class="modal-body p-4 lg:p-5" id="vehicleDetailsContent">
        <div class="text-sm text-slate-500">Loading booking timeline...</div>
      </div>
    </div>
  </div>

    <!-- Fleet card action confirmation modal -->
    <div id="fleetCardActionModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="fleetCardActionModalTitle">
      <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" id="fleetCardActionBackdrop"></div>
      <div class="relative mx-auto my-6 w-[95vw] max-w-md modal-panel">
        <div class="modal-header card-header px-4 py-4 lg:px-5 lg:py-5">
          <div>
            <h2 id="fleetCardActionModalTitle" class="text-lg font-semibold text-slate-900">Confirm action</h2>
            <p id="fleetCardActionModalSubtitle" class="mt-1 text-sm text-slate-500">Confirm the action before it is applied.</p>
          </div>
          <button type="button" id="fleetCardActionCloseBtn" class="modal-close" aria-label="Close">
            <span class="text-lg">✕</span>
          </button>
        </div>
        <div class="modal-body p-4 lg:p-5">
          <p id="fleetCardActionMessage" class="text-sm text-slate-600"></p>
          <div class="mt-4 flex items-center justify-end gap-2 lg:gap-3">
            <button type="button" id="fleetCardActionCancelBtn" class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Cancel</button>
            <button type="button" id="fleetCardActionConfirmBtn" class="inline-flex h-9 items-center justify-center rounded-lg bg-rose-600 px-4 text-sm font-semibold text-white transition hover:bg-rose-500">Confirm</button>
          </div>
        </div>
      </div>
    </div>

<script>
(function () {
  'use strict';

  const calendarEl = document.getElementById('carBookingCalendar');
  const hasCalendar = Boolean(calendarEl);

  // ==================== CONSTANTS ====================
  // Use the server-side BASE_URL if available, but fall back to client path detection when needed.
  const baseUrl = (() => {
    const serverUrl = <?= json_encode(defined('BASE_URL') ? rtrim(BASE_URL, '/') . '/' : '') ?>;
    if (serverUrl && serverUrl !== './') {
      return serverUrl;
    }
    const path = window.location.pathname;
    if (path.endsWith('index.php')) {
      return path.substring(0, path.lastIndexOf('/') + 1) || '/';
    }
    if (path.endsWith('/')) {
      return path;
    }
    return path.substring(0, path.lastIndexOf('/') + 1) + '/';
  })();

  const listUrl = 'index.php?controller=CarBookings&action=list';
  const createBookingUrl = 'index.php?controller=CarBookings&action=create';
  const vehicleHistoryUrl = 'index.php?controller=CarBookings&action=vehicleHistory';
  const getBookingUrl = 'index.php?controller=CarBookings&action=get';
  const updateBookingUrl = 'index.php?controller=CarBookings&action=update';
  const deleteBookingUrl = 'index.php?controller=CarBookings&action=delete';
  const createVehicleUrl = 'index.php?controller=Vehicles&action=create';
  const updateVehicleUrl = 'index.php?controller=Vehicles&action=update';
  const deleteVehicleUrl = 'index.php?controller=Vehicles&action=delete';
  const vehicleOptionsUrl = 'index.php?controller=Vehicles&action=listAjax';
  const vehicleListAllUrl = 'index.php?controller=Vehicles&action=listAllAjax';
  const driversListUrl = 'index.php?controller=Drivers&action=listAjax';
  const createDriverUrl = 'index.php?controller=Drivers&action=create';
  const updateDriverUrl = 'index.php?controller=Drivers&action=update';
  const deleteDriverUrl = 'index.php?controller=Drivers&action=delete';

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
  const totalFleetCountEl = document.getElementById('totalFleetCount');
  const onTripCountEl = document.getElementById('onTripCount');
  const readyBookingCountEl = document.getElementById('readyBookingCount');
  const driverAvailableCountEl = document.getElementById('driverAvailableCount');
  const fleetStatusBadgeEl = document.getElementById('fleetStatusBadge');

  const externalEventsEl = document.getElementById('external-events');
  const vehicleCardsContainer = document.getElementById('vehicleCardsContainer');
  const fleetTabVehiclesBtn = document.getElementById('fleetTabVehiclesBtn');
  const fleetTabDriversBtn = document.getElementById('fleetTabDriversBtn');
  const fleetSearchInput = document.getElementById('fleetSearchInput');
  const fleetAddNewBtn = document.getElementById('fleetAddNewBtn');
  const fleetAddNewMenu = document.getElementById('fleetAddNewMenu');
  const fleetGalleryMetaEl = document.getElementById('fleetGalleryMeta');
  const fleetPaginationControlsEl = document.getElementById('fleetPaginationControls');
  const fleetPaginationInfoEl = document.getElementById('fleetPaginationInfo');
  const fleetPageNumbersEl = document.getElementById('fleetPageNumbers');
  const fleetPrevPageBtn = document.getElementById('fleetPrevPageBtn');
  const fleetNextPageBtn = document.getElementById('fleetNextPageBtn');
  const bookingPreviewForm = document.getElementById('bookingPreviewForm');
  const bookingPreviewEmptyEl = document.getElementById('bookingPreviewEmpty');
  const bookingPreviewStatusEl = document.getElementById('bookingPreviewStatus');
  const fleetSnapshotSectionEl = document.getElementById('fleetSnapshotSection');
  const previewBookingIdEl = document.getElementById('previewBookingId');
  const previewPurposeEl = document.getElementById('previewPurpose');
  const previewDestinationsEl = document.getElementById('previewDestinations');
  const previewDateTripEl = document.getElementById('previewDateTrip');
  const previewDateRequestedEl = document.getElementById('previewDateRequested');
  const previewDepartureExpectedEl = document.getElementById('previewDepartureExpected');
  const previewReturnExpectedEl = document.getElementById('previewReturnExpected');
  const previewPassengersEl = document.getElementById('previewPassengers');
  const previewVehicleIdEl = document.getElementById('previewVehicleId');
  const previewDriverIdEl = document.getElementById('previewDriverId');
  const previewSpecialInstructionsEl = document.getElementById('previewSpecialInstructions');
  const previewRemarksEl = document.getElementById('previewRemarks');
  const previewCancelViewBtn = document.getElementById('previewCancelViewBtn');
  const previewCancelBtn = document.getElementById('previewCancelBtn');
  const vehicleDetailsModalEl = document.getElementById('vehicleDetailsModal');
  const vehicleDetailsContentEl = document.getElementById('vehicleDetailsContent');
  const vehicleDetailsMetaEl = document.getElementById('vehicleDetailsMeta');
  const closeVehicleDetailsModalBtn = document.getElementById('closeVehicleDetailsModalBtn');
  const vehicleDetailsModalBackdrop = document.getElementById('vehicleDetailsModalBackdrop');
  const fleetCardActionModalEl = document.getElementById('fleetCardActionModal');
  const fleetCardActionBackdrop = document.getElementById('fleetCardActionBackdrop');
  const fleetCardActionCloseBtn = document.getElementById('fleetCardActionCloseBtn');
  const fleetCardActionMessageEl = document.getElementById('fleetCardActionMessage');
  const fleetCardActionConfirmBtn = document.getElementById('fleetCardActionConfirmBtn');
  const fleetCardActionCancelBtn = document.getElementById('fleetCardActionCancelBtn');

  // ==================== UTILITY FUNCTIONS ====================
  function escapeHtml(str) {
    return String(str)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function formatMmdaTimeInput(value) {
    if (!value) return '';
    const text = String(value).trim();
    if (!text) return '';
    const match = text.match(/^(\d{4}-\d{2}-\d{2})[T ](\d{2}:\d{2})(?::\d{2})?$/);
    if (match) {
      return `${match[1]}T${match[2]}`;
    }
    return text;
  }

  function isVehicleCodingClient(plateNumber, bookingDate, bookingStartTime) {
    const plate = String(plateNumber || '').trim();
    const datePart = String(bookingDate || '').trim();
    const timePart = String(bookingStartTime || '').trim();
    if (!plate || !datePart || !timePart) {
      return { coding: false, reason: '', windowHours: false };
    }

    const matches = plate.match(/(\d)(?!.*\d)/);
    const lastDigit = matches && matches[1] ? Number(matches[1]) : null;
    if (lastDigit === null) {
      return { coding: false, reason: '', windowHours: false };
    }

    const day = new Date(datePart + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase();
    const dayMap = {
      monday: [1, 2],
      tuesday: [3, 4],
      wednesday: [5, 6],
      thursday: [7, 8],
      friday: [9, 0]
    };

    if (!dayMap[day]) {
      return { coding: false, reason: '', windowHours: false };
    }
    if (!dayMap[day].includes(lastDigit)) {
      return { coding: false, reason: '', windowHours: false };
    }

    const timeValue = timePart.match(/^(\d{1,2}):(\d{2})$/);
    if (!timeValue) {
      return { coding: false, reason: '', windowHours: false };
    }
    const hours = Number(timeValue[1]);
    const minutes = Number(timeValue[2]);
    const totalMinutes = hours * 60 + minutes;
    const morningStart = 7 * 60;
    const morningEnd = 10 * 60;
    const eveningStart = 17 * 60;
    const eveningEnd = 20 * 60;
    const windowStart = 10 * 60 + 1;
    const windowEnd = 16 * 60 + 59;

    if ((totalMinutes >= morningStart && totalMinutes <= morningEnd) || (totalMinutes >= eveningStart && totalMinutes <= eveningEnd)) {
      return { coding: true, reason: 'MMDA Number Coding', windowHours: false };
    }
    if (totalMinutes >= windowStart && totalMinutes <= windowEnd) {
      return { coding: false, reason: '', windowHours: true };
    }
    return { coding: false, reason: '', windowHours: false };
  }

  function getMmdaBookingStatus(plateNumber, bookingDate, bookingStartTime) {
    if (!plateNumber) {
      return { coding: false, reason: '', windowHours: false };
    }
    return isVehicleCodingClient(plateNumber, bookingDate, bookingStartTime);
  }

  function getVehiclePlateFromOption(optionValue) {
    if (!optionValue) return '';
    const select = document.getElementById('vehicleId');
    if (!select) return '';
    const option = Array.from(select.options).find(item => String(item.value) === String(optionValue));
    return option ? option.getAttribute('data-plate') || '' : '';
  }

  function updateBookingVehicleRestriction() {
    const select = document.getElementById('vehicleId');
    const dateInput = document.getElementById('dateTrip');
    const timeInput = document.getElementById('departureExpected');
    const statusEl = document.getElementById('bookingVehicleRestrictionStatus');
    const labelEl = document.getElementById('bookingVehicleRestrictionLabel');
    const quickNoteEl = document.getElementById('bookingVehicleRestrictionQuickNote');
    if (!select || !dateInput || !timeInput) return;

    const selectedValue = select.value;
    const selectedOption = Array.from(select.options).find(option => String(option.value) === String(selectedValue));
    const plateNumber = selectedOption ? (selectedOption.getAttribute('data-plate') || '') : '';
    const bookingDate = dateInput.value;
    const bookingStartTime = formatMmdaTimeInput(timeInput.value);
    const result = getMmdaBookingStatus(plateNumber, bookingDate, bookingStartTime);

    if (selectedValue) {
      select.classList.toggle('border-rose-400', result.coding);
      select.classList.toggle('bg-rose-50', result.coding);
      select.classList.toggle('text-rose-700', result.coding);
      select.classList.toggle('border-slate-200', !result.coding);
      select.classList.toggle('bg-white', !result.coding);
      select.classList.toggle('text-slate-700', !result.coding);
    } else {
      select.classList.remove('border-rose-400', 'bg-rose-50', 'text-rose-700');
      select.classList.add('border-slate-200', 'bg-white', 'text-slate-700');
    }

    if (statusEl) {
      statusEl.className = result.coding
        ? 'inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-rose-700'
        : 'inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-600';
      statusEl.textContent = result.coding ? 'MMDA Coding' : (result.windowHours ? 'Available (Window Hours)' : '');
    }

    if (labelEl) {
      labelEl.textContent = result.coding ? 'MMDA Coding' : (result.windowHours ? 'Available (Window Hours)' : '');
      labelEl.className = result.coding
        ? 'text-[11px] font-semibold text-rose-600'
        : 'text-[11px] font-semibold text-emerald-600';
    }

    if (quickNoteEl) {
      quickNoteEl.textContent = result.coding ? 'This vehicle is affected by MMDA Number Coding during the selected booking time.' : (result.windowHours ? 'Window hours are available for this plate.' : '');
      quickNoteEl.className = result.coding
        ? 'text-[11px] text-rose-600'
        : 'text-[11px] text-slate-500';
    }

    if (selectedValue) {
      const options = Array.from(select.options);
      options.forEach(option => {
        const optionPlate = option.getAttribute('data-plate') || '';
        const optionResult = getMmdaBookingStatus(optionPlate, bookingDate, bookingStartTime);
        option.classList.toggle('opacity-70', optionResult.coding);
      });
    }
  }

  const fleetCardActionState = {
    itemType: null,
    itemId: null,
    itemName: '',
    action: null,
    itemData: null
  };

  function showFleetCardActionModal({ itemType, itemId, itemName, action, itemData }) {
    fleetCardActionState.itemType = itemType;
    fleetCardActionState.itemId = itemId;
    fleetCardActionState.itemName = itemName;
    fleetCardActionState.action = action;
    fleetCardActionState.itemData = itemData;
    const label = action === 'disable' ? 'Disable' : 'Enable';
    const subject = itemType === 'drivers' || itemType === 'driver' ? 'driver' : 'vehicle';
    fleetCardActionMessageEl.textContent = `${label} ${subject} '${itemName}'? This action will update its availability for new bookings.`;
    openModal(fleetCardActionModalEl);
  }

  function hideFleetCardActionModal() {
    if (!fleetCardActionModalEl) return;
    closeModal(fleetCardActionModalEl);
    fleetCardActionState.itemType = null;
    fleetCardActionState.itemId = null;
    fleetCardActionState.itemName = '';
    fleetCardActionState.action = null;
    fleetCardActionState.itemData = null;
  }

  function performFleetCardAction() {
    const { itemType, itemId, itemName, action, itemData } = fleetCardActionState;
    if (!itemType || !itemId || !action || !itemData) return;
    const isVehicle = itemType === 'vehicles' || itemType === 'vehicle';
    const deleteUrl = isVehicle ? deleteVehicleUrl : deleteDriverUrl;
    const updateUrl = isVehicle ? updateVehicleUrl : updateDriverUrl;
    const isDisable = action === 'disable';
    const fd = new FormData();
    fd.append('id', itemId);

    const successLabel = isDisable ? 'disabled' : 'enabled';
    const errorLabel = isDisable ? 'disable' : 'enable';
    const refreshList = isVehicle ? loadVehiclesTable : loadDriversTable;
    const refreshSelect = isVehicle ? refreshBookingVehicleSelect : refreshBookingDriverSelect;
    const notifications = {
      vehicle: { disabled: 'Vehicle disabled', enabled: 'Vehicle enabled' },
      driver: { disabled: 'Driver disabled', enabled: 'Driver enabled' }
    };

    if (!isDisable) {
      if (isVehicle) {
        fd.append('plate_number', itemData.plate_number || '');
        fd.append('vehicle_name', itemData.vehicle_name || '');
        fd.append('capacity', itemData.capacity || 1);
        fd.append('status', 'active');
      } else {
        const fullName = [itemData.first_name, itemData.last_name].filter(Boolean).join(' ').trim() || itemData.driver_name || '';
        fd.append('driver_name', fullName);
        fd.append('employee_id', itemData.employee_id || itemData.employeeId || '');
        fd.append('first_name', itemData.first_name || '');
        fd.append('last_name', itemData.last_name || '');
        fd.append('mobile_number', itemData.mobile_number || itemData.mobileNumber || '');
        fd.append('email', itemData.email || '');
        fd.append('license_number', itemData.license_number || '');
        fd.append('license_class', itemData.license_class || '');
        fd.append('license_expiry', itemData.license_expiry || '');
        fd.append('status', 'active');
      }
    }

    const endpoint = isDisable ? deleteUrl : updateUrl;
    fetch(endpoint, { method: 'POST', body: fd, headers: {'X-Requested-With': 'XMLHttpRequest'} })
      .then(parseJsonResponse)
      .then(resp => {
        if (!resp || !resp.success) {
          alert(resp && resp.message ? resp.message : `Failed to ${errorLabel} ${isVehicle ? 'vehicle' : 'driver'}`);
          return;
        }
        hideFleetCardActionModal();
        refreshList();
        refreshSelect();
        loadVehicleCards();
        showNotification(isDisable ? notifications[isVehicle ? 'vehicle' : 'driver'].disabled : notifications[isVehicle ? 'vehicle' : 'driver'].enabled, isDisable ? 'info' : 'success');
      })
      .catch(err => {
        console.error(`Fleet action failed: ${err.message || err}`);
        alert(err && err.message ? err.message : `Network error while trying to ${errorLabel} ${isVehicle ? 'vehicle' : 'driver'}`);
      });
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

  function toLocalDateValue(dtStr) {
    if (!dtStr) return '';
    const s = String(dtStr).replace(' ', 'T');
    const d = new Date(s);
    if (!isNaN(d.getTime())) return toISODate(d);
    const m = String(dtStr).match(/^(\d{4}-\d{2}-\d{2})/);
    return m ? m[1] : '';
  }

  function toLocalTimeValue(dtStr) {
    if (!dtStr) return '';
    const s = String(dtStr).trim();
    const timeOnly = s.match(/^(\d{2}:\d{2})(:\d{2})?$/);
    if (timeOnly) return timeOnly[1];
    const d = new Date(s.replace(' ', 'T'));
    if (!isNaN(d.getTime())) {
      return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
    }
    return '';
  }

  function combineDateAndTime(dateStr, timeStr) {
    if (!dateStr || !timeStr) return '';
    const datePart = String(dateStr).trim();
    const timePart = String(timeStr).trim();
    if (!/^\d{4}-\d{2}-\d{2}$/.test(datePart) || !/^\d{2}:\d{2}$/.test(timePart)) return '';
    return `${datePart}T${timePart}`;
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
    const tripDate = dateStr ? new Date(dateStr + 'T00:00:00') : today;
    const defaultDate = toISODate(tripDate);
    const defaultStart = new Date(tripDate.getFullYear(), tripDate.getMonth(), tripDate.getDate(), 9, 0);
    const defaultEnd = new Date(tripDate.getFullYear(), tripDate.getMonth(), tripDate.getDate(), 17, 0);

    document.getElementById('dateTrip').value = defaultDate;
    document.getElementById('dateRequested').value = toISODate(today);
    document.getElementById('departureExpected').value = toISODateTime(defaultStart);
    document.getElementById('returnExpected').value = toISODateTime(defaultEnd);

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
    updateBookingVehicleRestriction();

    const saveBtn = document.getElementById('saveBookingBtn');
    if (saveBtn) saveBtn.style.display = '';

    // Delete hidden by default
    const delBtn = document.getElementById('deleteBookingBtn');
    if (delBtn) delBtn.classList.add('hidden');
  }

  const newBookingBtn = document.getElementById('newBookingBtn');
  if (newBookingBtn) {
    newBookingBtn.addEventListener('click', () => {
      resetBookingModalForm();
      setBookingDefaults('');
      openModal(bookingModalEl);
    });
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
      vehicleDetailsContentEl.innerHTML = '<div class="text-sm text-slate-500">Loading booking timeline...</div>';
    }
    if (vehicleDetailsMetaEl) {
      vehicleDetailsMetaEl.innerHTML = '';
    }
  }

  // vehicle details modal handlers
  if (closeVehicleDetailsModalBtn) closeVehicleDetailsModalBtn.addEventListener('click', () => { resetVehicleDetailsModal(); closeModal(vehicleDetailsModalEl); });
  if (vehicleDetailsModalBackdrop) vehicleDetailsModalBackdrop.addEventListener('click', () => { resetVehicleDetailsModal(); closeModal(vehicleDetailsModalEl); });


  const vehicleIdSelect = document.getElementById('vehicleId');
  const dateTripInput = document.getElementById('dateTrip');
  const departureExpectedInput = document.getElementById('departureExpected');

  if (vehicleIdSelect) {
    vehicleIdSelect.addEventListener('change', updateBookingVehicleRestriction);
  }
  if (dateTripInput) {
    dateTripInput.addEventListener('change', updateBookingVehicleRestriction);
  }
  if (departureExpectedInput) {
    departureExpectedInput.addEventListener('change', updateBookingVehicleRestriction);
  }

  bookingForm.addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(bookingForm);
    const isUpdate = bookingId && bookingId.value;
    const url = isUpdate ? updateBookingUrl : createBookingUrl;

    const tripDate = document.getElementById('dateTrip') ? document.getElementById('dateTrip').value : formData.get('date_trip');
    const departureExpected = document.getElementById('departureExpected') ? document.getElementById('departureExpected').value : formData.get('departure_expected');
    const vehicleIdValue = document.getElementById('vehicleId') ? document.getElementById('vehicleId').value : formData.get('vehicle_id');
    const selectedVehicleOption = document.getElementById('vehicleId') ? Array.from(document.getElementById('vehicleId').options).find(option => String(option.value) === String(vehicleIdValue)) : null;
    const vehiclePlate = selectedVehicleOption ? (selectedVehicleOption.getAttribute('data-plate') || '') : '';
    const mmdaCheck = getMmdaBookingStatus(vehiclePlate, tripDate, formatMmdaTimeInput(departureExpected));
    if (mmdaCheck.coding) {
      showNotification('This vehicle cannot be booked because it is affected by MMDA Number Coding during the selected schedule.', 'error');
      return;
    }
    const returnExpected = document.getElementById('returnExpected') ? document.getElementById('returnExpected').value : formData.get('return_expected');
    if (!departureExpected || !returnExpected) {
      showNotification('Please select both expected departure and return date/time.', 'error');
      return;
    }
    formData.set('departure_expected', departureExpected);
    formData.set('return_expected', returnExpected);
    if (tripDate) formData.set('date_trip', tripDate);

    // Client-side conflict check to provide immediate, actionable feedback
    try {
      const vehicle_id = vehicleIdValue;
      const driver_id = document.getElementById('driverId') ? document.getElementById('driverId').value : formData.get('driver_id');
      const start = departureExpected;
      const end = returnExpected;
      const excludeId = isUpdate ? bookingId.value : null;
      const bufferMinutes = bookingForm.dataset.bufferMinutes ? parseInt(bookingForm.dataset.bufferMinutes, 10) : 15;
      const requesterEl = bookingForm.querySelector('[name="requester_id"]');
      const requesterId = requesterEl ? requesterEl.value : (window.currentUserId || null);
      const statusEl = bookingForm.querySelector('[name="status"]');
      const status = statusEl ? statusEl.value : 'pending';

      const conflicts = checkBookingConflicts({ vehicle_id, driver_id, start, end, excludeId, bufferMinutes, requesterId, status, isResize: false });
      if (conflicts && conflicts.length > 0) {
        const types = Array.from(new Set(conflicts.map(c => c.type)));
        showNotification(buildConflictMessage(types), 'error');
        highlightConflictFields(types);
        return;
      }
    } catch (err) {
      console.error('Conflict check failed:', err);
    }

    fetch(url, {
      method: 'POST',
      body: formData,
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(r => {
      // Try to parse JSON body when present, regardless of status code
      const ct = (r.headers.get('content-type') || '').toLowerCase();
      if (ct.includes('application/json')) return r.json();
      return r.text().then(t => { throw new Error('Server returned non-JSON response: ' + String(t).slice(0,200)); });
    })
    .then(resp => {
      if (!resp || !resp.success) {
        // server returned a JSON error payload
        const friendly = friendlyBookingError(resp);
        console.error('Booking save error:', friendly, resp);
        // highlight fields when server indicates specific conflict
        if (resp && resp.error_code) {
          if (resp.error_code === 'vehicle_conflict') highlightConflictFields(['vehicle']);
          if (resp.error_code === 'driver_conflict') highlightConflictFields(['driver']);
          if (resp.error_code === 'vehicle_driver_conflict' || resp.error_code === 'vehicle_and_driver_conflict') highlightConflictFields(['vehicle_driver']);
        }
        showNotification(friendly, 'error');
        return;
      }
      resetBookingModalForm();
      closeModal(bookingModalEl);
      if (calendar) calendar.refetchEvents();
      loadVehicleCards();
      showNotification(isUpdate ? 'Booking updated successfully!' : 'Booking created successfully!', 'success');
    })
    .catch(err => {
      console.error('Network/parsing error:', err);
      // If the error message contains a known server error_code string, try to show friendly message
      const msg = (err && err.message) ? String(err.message) : '';
      if (msg.includes('vehicle_conflict')) {
        highlightConflictFields(['vehicle']);
        showNotification('Vehicle conflict', 'error');
        return;
      }
      if (msg.includes('driver_conflict')) {
        highlightConflictFields(['driver']);
        showNotification('Driver conflict', 'error');
        return;
      }
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
  const openVehicleModalQuickBtn = document.getElementById('openVehicleModalQuickBtn');
  const closeVehicleModalBtn = document.getElementById('closeVehicleModalBtn');
  const vehicleModalBackdrop = document.getElementById('vehicleModalBackdrop');

  function setVehicleModalFormFromRow(row) {
    vehicleIdInputEl.value = row ? String(row.id) : '';
    plateNumberInputEl.value = row ? row.plate_number : '';
    vehicleNameInputEl.value = row ? row.vehicle_name : '';
    capacityInputEl.value = row ? row.capacity : '';
    vehicleStatusInputEl.value = row ? row.status : 'active';
    // Reset file input and preview
    const fileInput = document.getElementById('vehicleImage');
    if (fileInput) fileInput.value = '';
    if (row) {
      // prefer server-provided full URL when available, otherwise build from baseUrl
      const fname = row.image_filename || row.image || null;
      const url = row.image_url ? row.image_url : (fname ? (baseUrl + 'uploads/vehicle/' + fname) : null);
      if (window._setVehicleImagePreview) window._setVehicleImagePreview(url);
    } else {
      if (window._setVehicleImagePreview) window._setVehicleImagePreview(null);
    }
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
        ? `<span class="vehicle-badge">Active</span>`
        : `<span class="vehicle-badge inactive">Inactive</span>`;

      const actionLabel = row.status === 'active' ? 'Disable' : 'Enable';
      const actionClass = row.status === 'active' ? 'btn-icon btn-icon-danger' : 'btn-icon';

      tr.innerHTML = `
        <td class="px-5 py-3">
          <div class="font-medium text-slate-800">${escapeHtml(row.vehicle_name)}</div>
        </td>
        <td class="px-5 py-3 text-slate-700">${escapeHtml(row.plate_number)}</td>
        <td class="px-5 py-3 text-slate-700">${row.capacity} seats</td>
        <td class="px-5 py-3">${statusBadge}</td>
        <td class="px-5 py-3 text-right">
          <div class="inline-flex items-center gap-2">
            <button type="button" class="vehicle-edit-btn btn-icon" data-id="${row.id}">Edit</button>
            <button type="button" class="vehicle-delete-btn ${actionClass}" data-id="${row.id}">${actionLabel}</button>
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
        const isActive = row && row.status === 'active';

        if (isActive) {
          // disable flow (existing endpoint)
          if (!confirm(`Disable vehicle '${label}'? It will not be available for new bookings.`)) return;
          const fd = new FormData(); fd.append('id', id);
          fetch(deleteVehicleUrl, { method: 'POST', body: fd, headers: {'X-Requested-With': 'XMLHttpRequest'} })
            .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
            .then(resp => {
              if (!resp || !resp.success) { alert(resp && resp.message ? resp.message : 'Failed to disable vehicle'); return; }
              loadVehiclesTable(); refreshVehicleFilterDropdown(); refreshBookingVehicleSelect(); setVehicleModalFormFromRow(null);
              showNotification('Vehicle disabled', 'info');
            })
            .catch(() => alert('Network error while disabling vehicle'));
        } else {
          // enable flow: use update endpoint (requires full payload)
          if (!confirm(`Enable vehicle '${label}'? It will be available for new bookings.`)) return;
          const fd = new FormData();
          fd.append('id', id);
          fd.append('plate_number', row.plate_number || '');
          fd.append('vehicle_name', row.vehicle_name || '');
          fd.append('capacity', row.capacity || 1);
          fd.append('status', 'active');

          fetch(updateVehicleUrl, { method: 'POST', body: fd, headers: {'X-Requested-With': 'XMLHttpRequest'} })
            .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
            .then(resp => {
              if (!resp || !resp.success) { alert(resp && resp.message ? resp.message : 'Failed to enable vehicle'); return; }
              loadVehiclesTable(); refreshVehicleFilterDropdown(); refreshBookingVehicleSelect(); setVehicleModalFormFromRow(null);
              showNotification('Vehicle enabled', 'success');
            })
            .catch(() => alert('Network error while enabling vehicle'));
        }
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

  if (openVehicleModalBtn) {
    openVehicleModalBtn.addEventListener('click', () => {
      openModal(vehicleModalEl);
      loadVehiclesTable();
    });
  }
  if (openVehicleModalQuickBtn) {
    openVehicleModalQuickBtn.addEventListener('click', () => {
      openModal(vehicleModalEl);
      loadVehiclesTable();
    });
  }
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
    // attach selected image if present
    const imageEl = document.getElementById('vehicleImage');
    if (imageEl && imageEl.files && imageEl.files[0]) {
      fd.append('vehicle_image', imageEl.files[0]);
    }
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
  const employeeIdInputEl = document.getElementById('employeeIdInput');
  const firstNameInputEl = document.getElementById('firstNameInput');
  const lastNameInputEl = document.getElementById('lastNameInput');
  const mobileNumberInputEl = document.getElementById('mobileNumberInput');
  const emailInputEl = document.getElementById('emailInput');
  const licenseNumberInputEl = document.getElementById('licenseNumberInput');
  const licenseClassInputEl = document.getElementById('licenseClassInput');
  const licenseExpiryInputEl = document.getElementById('licenseExpiryInput');
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
    driverNameInputEl.value = row ? row.driver_name || '' : '';
    employeeIdInputEl.value = row ? row.employee_id || '' : '';
    firstNameInputEl.value = row ? row.first_name || '' : '';
    lastNameInputEl.value = row ? row.last_name || '' : '';
    mobileNumberInputEl.value = row ? row.mobile_number || '' : '';
    emailInputEl.value = row ? row.email || '' : '';
    licenseNumberInputEl.value = row ? row.license_number || '' : '';
    licenseClassInputEl.value = row ? row.license_class || '' : '';
    licenseExpiryInputEl.value = row ? row.license_expiry || '' : '';
    driverStatusInputEl.value = row ? row.status : 'active';
  }

  function renderDriversTable(rows) {
    if (!rows || rows.length === 0) {
      driversTableBodyEl.innerHTML = '<tr><td colspan="7" class="px-5 py-8 text-center text-slate-500">No drivers found</td></tr>';
      return;
    }

    driversTableBodyEl.innerHTML = '';
    rows.forEach(row => {
      const tr = document.createElement('tr');
      tr.className = 'border-b border-slate-100 hover:bg-slate-50 transition';
      const statusBadge = row.status === 'active'
        ? `<span class="driver-badge">Active</span>`
        : `<span class="driver-badge inactive">Inactive</span>`;
      const fullName = [row.first_name, row.last_name].filter(Boolean).join(' ').trim() || row.driver_name || '—';
      const employeeId = row.employee_id || row.employeeId || '';
      const licenseNumber = row.license_number || '';
      const licenseClass = row.license_class || '';
      const licenseExpiry = row.license_expiry || '';

      tr.innerHTML = `
        <td class="px-5 py-3">
          <div class="font-medium text-slate-800">${escapeHtml(fullName)}</div>
        </td>
        <td class="px-5 py-3">${escapeHtml(employeeId)}</td>
        <td class="px-5 py-3">${escapeHtml(licenseNumber)}</td>
        <td class="px-5 py-3">${escapeHtml(licenseClass)}</td>
        <td class="px-5 py-3">${escapeHtml(licenseExpiry)}</td>
        <td class="px-5 py-3">${statusBadge}</td>
        <td class="px-5 py-3 text-right">
          <div class="inline-flex items-center gap-2">
            <button type="button" class="driver-edit-btn btn-icon" data-id="${row.id}">Edit</button>
            <button type="button" class="driver-delete-btn ${row.status === 'active' ? 'btn-icon btn-icon-danger' : 'btn-icon'}" data-id="${row.id}">${row.status === 'active' ? 'Disable' : 'Enable'}</button>
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
        const isActive = row && row.status === 'active';

        if (isActive) {
          if (!confirm(`Disable driver '${name}'? They will not be available for new bookings.`)) return;
          const fd = new FormData(); fd.append('id', id);
          fetch(deleteDriverUrl, { method: 'POST', body: fd, headers: {'X-Requested-With': 'XMLHttpRequest'} })
            .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
            .then(resp => {
              if (!resp || !resp.success) { alert(resp && resp.message ? resp.message : 'Failed to disable driver'); return; }
              loadDriversTable(); refreshDriverFilterDropdown(); refreshBookingDriverSelect(); setDriversModalFormFromRow(null);
              showNotification('Driver disabled', 'info');
            })
            .catch(() => alert('Network error while disabling driver'));
        } else {
          if (!confirm(`Enable driver '${name}'? They will be available for new bookings.`)) return;
          const fd = new FormData();
          const fullName = [row.first_name, row.last_name].filter(Boolean).join(' ').trim() || row.driver_name || '';
          fd.append('id', id);
          fd.append('driver_name', fullName);
          fd.append('employee_id', row.employee_id || row.employeeId || '');
          fd.append('first_name', row.first_name || '');
          fd.append('last_name', row.last_name || '');
          fd.append('mobile_number', row.mobile_number || row.mobileNumber || '');
          fd.append('email', row.email || '');
          fd.append('license_number', row.license_number || '');
          fd.append('license_class', row.license_class || '');
          fd.append('license_expiry', row.license_expiry || '');
          fd.append('status', 'active');
          fetch(updateDriverUrl, { method: 'POST', body: fd, headers: {'X-Requested-With': 'XMLHttpRequest'} })
            .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
            .then(resp => {
              if (!resp || !resp.success) { alert(resp && resp.message ? resp.message : 'Failed to enable driver'); return; }
              loadDriversTable(); refreshDriverFilterDropdown(); refreshBookingDriverSelect(); setDriversModalFormFromRow(null);
              showNotification('Driver enabled', 'success');
            })
            .catch(() => alert('Network error while enabling driver'));
        }
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
  fd.append('employee_id', employeeIdInputEl.value);
  fd.append('first_name', firstNameInputEl.value);
  fd.append('last_name', lastNameInputEl.value);
  fd.append('mobile_number', mobileNumberInputEl.value);
  fd.append('email', emailInputEl.value);
  fd.append('license_number', licenseNumberInputEl.value);
  fd.append('license_class', licenseClassInputEl.value);
  fd.append('license_expiry', licenseExpiryInputEl.value);
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

    vehicleCardsContainer.innerHTML = '<div class="col-span-full rounded-xl border border-slate-200 bg-slate-50 p-6 text-center text-slate-500">Loading fleet...</div>';

    const vehiclesRequest = fetch(vehicleListAllUrl + '&t=' + Date.now(), {
      method: 'GET',
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    }).then(parseJsonResponse).then(data => Array.isArray(data.vehicles) ? data.vehicles : []);

    const driversRequest = fetch(driversListUrl + '&t=' + Date.now(), {
      method: 'GET',
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    }).then(parseJsonResponse).then(data => Array.isArray(data.drivers) ? data.drivers : []);

    const range = getTodayRange();
    const bookingsRequest = fetch(listUrl + '&' + new URLSearchParams({ start: range.start, end: range.end }).toString() + '&t=' + Date.now(), {
      method: 'GET',
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    }).then(parseJsonResponse).then(data => Array.isArray(data.events) ? data.events : []);

    Promise.all([vehiclesRequest, driversRequest, bookingsRequest])
      .then(([vehicles, drivers, bookings]) => {
        fleetGalleryState.vehicles = vehicles;
        fleetGalleryState.drivers = drivers;
        fleetGalleryState.bookings = bookings;
        renderFleetGalleryCards();
        refreshFleetSnapshot(vehicles);
      })
      .catch(err => {
        console.error('Error loading fleet gallery:', err);
        vehicleCardsContainer.innerHTML = '<div class="col-span-full text-center py-8 text-rose-500">Failed to load fleet gallery</div>';
        updateFleetSnapshot([], [], []);
      });
  }

  function getTodayRange() {
    const now = new Date();
    const start = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0);
    const end = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59);
    return { start: start.toISOString(), end: end.toISOString() };
  }

  function normalizeBookingEvent(event) {
    const props = event.extendedProps || {};
    return {
      vehicle_id: props.vehicle_id || props.vehicleId || props.vehicle || null,
      driver_id: props.driver_id || props.driverId || props.driver || null,
      vehicle_name: props.vehicle_name || props.vehicleName || props.vehicle || null,
      purpose: props.purpose || props.trip_name || props.title || null,
      destination: props.destination || props.destinations || props.trip_destination || null,
      status: props.status || props.booking_status || null,
      start: event.start || event.startStr || props.departure_expected || props.start_at || null,
      end: event.end || event.endStr || props.return_expected || props.end_at || null,
    };
  }

  function updateFleetSnapshot(vehicles = [], drivers = [], events = []) {
    const activeVehicles = vehicles.filter(v => String(v.status || '').toLowerCase() === 'active');
    const activeDrivers = drivers.filter(d => String(d.status || '').toLowerCase() === 'active');

    const ongoingEvents = (events || []).map(ev => normalizeBookingEvent(ev)).filter(item => {
      return getBookingStatus(item.start, item.end) === 'ongoing';
    });

    const ongoingVehicleIds = new Set(ongoingEvents.map(ev => String(ev.vehicle_id)).filter(Boolean));
    const ongoingDriverIds = new Set(ongoingEvents.map(ev => String(ev.driver_id)).filter(Boolean));

    const onTrip = activeVehicles.filter(v => ongoingVehicleIds.has(String(v.id))).length;
    const readyBooking = Math.max(0, activeVehicles.length - onTrip);
    const bookedToday = (events || []).length;
    const now = new Date();
    const returningSoon = ongoingEvents.filter(ev => {
      const end = new Date(String(ev.end).replace(' ', 'T'));
      const minutesUntilEnd = (end.getTime() - now.getTime()) / 60000;
      return minutesUntilEnd > 0 && minutesUntilEnd <= 60;
    }).length;
    const underMaintenance = vehicles.filter(v => {
      const status = String(v.status || '').toLowerCase();
      return status === 'inactive' || status === 'maintenance';
    }).length;

    if (totalFleetCountEl) totalFleetCountEl.textContent = String(readyBooking);
    if (onTripCountEl) onTripCountEl.textContent = String(bookedToday);
    if (readyBookingCountEl) readyBookingCountEl.textContent = String(returningSoon);
    if (driverAvailableCountEl) driverAvailableCountEl.textContent = String(underMaintenance);

    if (fleetStatusBadgeEl) {
      if (readyBooking === 0) {
        fleetStatusBadgeEl.className = 'inline-flex rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700';
        fleetStatusBadgeEl.textContent = 'No vehicle available';
      } else if (onTrip > 0) {
        fleetStatusBadgeEl.className = 'inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700';
        fleetStatusBadgeEl.textContent = 'Partially booked';
      } else {
        fleetStatusBadgeEl.className = 'inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700';
        fleetStatusBadgeEl.textContent = 'Live availability';
      }
    }
  }

  function refreshFleetSnapshot(vehicles = []) {
    const range = getTodayRange();
    const bookingParams = new URLSearchParams({ start: range.start, end: range.end });

    const vehiclePromise = Promise.resolve(vehicles);
    const driverPromise = fetch(driversListUrl + '&t=' + Date.now(), {
      method: 'GET', headers: {'X-Requested-With': 'XMLHttpRequest'}
    }).then(parseJsonResponse).then(data => Array.isArray(data.drivers) ? data.drivers : []);
    const eventsPromise = fetch(listUrl + '&' + bookingParams.toString(), {
      method: 'GET', headers: {'X-Requested-With': 'XMLHttpRequest'}
    }).then(parseJsonResponse).then(data => Array.isArray(data.events) ? data.events : []);

    Promise.all([vehiclePromise, driverPromise, eventsPromise])
      .then(([vehData, driverData, bookingEvents]) => {
        fleetGalleryState.bookings = bookingEvents;
        updateFleetSnapshot(vehData, driverData, bookingEvents);
      })
      .catch(err => {
        console.error('Failed to refresh fleet snapshot:', err);
        updateFleetSnapshot(vehicles, [], []);
      });
  }

  function createVehicleCard(vehicle) {
    const card = document.createElement('div');
    const rawStatus = (vehicle.status || 'active').toString().toLowerCase();
    const mmdaState = getMmdaBookingStatus(vehicle.plate_number || '', toISODate(new Date()), '09:00');
    const isMmdaRestricted = Boolean(mmdaState.coding);
    const mmdaLabel = isMmdaRestricted ? 'MMDA Coding Today' : (mmdaState.windowHours ? 'Available (Window Hours)' : '');
    const statusLabel = rawStatus === 'active' ? 'Available' : 'Unavailable';
    const statusDot = rawStatus === 'active' ? 'bg-emerald-600' : 'bg-slate-400';
    const vehicleName = vehicle.vehicle_name || 'Vehicle';
    const plate = vehicle.plate_number || 'No plate';
    const seats = vehicle.capacity ? `${vehicle.capacity} seats` : null;
    const metadataText = [plate, seats].filter(Boolean).join(' • ');

    card.dataset.vehicleStatus = rawStatus;
    card.dataset.vehicleName = vehicleName;
    card.dataset.vehiclePlate = vehicle.plate_number || '';
    card.dataset.vehicleCapacity = String(vehicle.capacity || 1);
    card.className = 'flex h-full flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md';
    if (rawStatus !== 'active') {
      card.className += ' opacity-80 filter grayscale';
    }
    card.setAttribute('data-mmda-restricted', isMmdaRestricted ? '1' : '0');
    card.setAttribute('title', mmdaLabel ? 'This vehicle is affected by MMDA Number Coding today.' : '');

    const filename = vehicle.image_filename || vehicle.image || null;
    const imgSrc = vehicle.image_url ? vehicle.image_url : (filename ? (baseUrl + 'uploads/vehicle/' + filename) : null);

    card.innerHTML = `
      <div class="relative overflow-hidden rounded-t-xl bg-slate-100 flex-shrink-0">
        <div class="aspect-[5/3] w-full overflow-hidden rounded-t-xl bg-slate-100">
          ${imgSrc ? `
            <img src="${escapeHtml(imgSrc)}" alt="${escapeHtml(vehicleName)}" class="h-full w-full object-cover object-center" />
          ` : `
            <div class="flex h-full w-full items-center justify-center bg-slate-100 text-slate-400">
              <span class="text-sm font-semibold">No image available</span>
            </div>
          `}
        </div>
        <div id="vehicle-status-badge-${vehicle.id}" class="absolute right-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-white/95 px-2 py-1 text-[10px] font-medium text-slate-700 shadow-sm ring-1 ring-slate-200 backdrop-blur">
          <span id="vehicle-status-dot-${vehicle.id}" class="inline-flex h-1.5 w-1.5 rounded-full ${statusDot}"></span>
          <span id="vehicle-status-text-${vehicle.id}" class="leading-none">${escapeHtml(statusLabel)}</span>
        </div>
      </div>
      <div class="flex flex-1 flex-col gap-3 p-3 lg:p-4">
        <div class="space-y-2">
          <div class="space-y-1">
            <h3 class="truncate text-base font-semibold text-slate-900">${escapeHtml(vehicleName)}</h3>
            <p class="truncate text-sm text-slate-500">${escapeHtml(metadataText)}</p>
            ${isMmdaRestricted ? `<div class="inline-flex w-fit items-center rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-rose-700">MMDA Coding</div>` : (mmdaLabel ? `<div class="inline-flex w-fit items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-emerald-700">${escapeHtml(mmdaLabel)}</div>` : '')}
          </div>
          <div id="bookings-${vehicle.id}" class="min-h-[1.5rem] text-base font-semibold text-slate-900">Loading availability…</div>
          <div id="next-trip-${vehicle.id}" class="hidden pt-3 border-t border-slate-100">
            <div class="space-y-1 pt-2 text-sm text-slate-600">
              <div class="truncate font-semibold text-slate-900">Next trip</div>
              <div id="next-trip-title-${vehicle.id}" class="truncate text-sm font-medium text-slate-900"></div>
              <div id="next-trip-time-${vehicle.id}" class="truncate text-xs leading-5 text-slate-500"></div>
            </div>
          </div>
        </div>
        <div class="mt-auto">
          <button type="button" class="h-9 w-full rounded-lg border border-slate-900 bg-slate-900 px-3 text-sm font-semibold text-white transition hover:bg-slate-800 view-vehicle-btn" data-vehicle-id="${vehicle.id}">View Schedule</button>
        </div>
        <div id="loading-${vehicle.id}" class="text-xs text-slate-400" style="display:none;"></div>
      </div>
    `;

    loadVehicleUpcoming(vehicle.id, card);
    card.querySelector('.view-vehicle-btn').addEventListener('click', () => {
      showVehicleDetails(vehicle.id, card);
    });
    const vehicleActionBtn = card.querySelector('.fleet-card-action-btn');
    if (vehicleActionBtn) {
      vehicleActionBtn.addEventListener('click', () => {
        showFleetCardActionModal({
          itemType: 'vehicles',
          itemId: vehicle.id,
          itemName: vehicleName,
          action: vehicleActionBtn.getAttribute('data-action'),
          itemData: vehicle
        });
      });
    }

    return card;
  }

  function getDriverOperationalState(driver, bookings = []) {
    const rawStatus = String(driver.status || 'active').toLowerCase();
    if (rawStatus === 'inactive') {
      return {
        state: 'Inactive',
        badgeClass: 'bg-slate-100 text-slate-700 border-slate-200',
        booking: null
      };
    }

    const matchingBookings = (bookings || [])
      .map(normalizeBookingEvent)
      .filter(item => String(item.driver_id || '') === String(driver.id || ''));

    const ongoingBooking = matchingBookings.find(item => getBookingStatus(item.start, item.end) === 'ongoing');
    if (ongoingBooking) {
      return {
        state: 'On Trip',
        badgeClass: 'bg-amber-50 text-amber-700 border-amber-200',
        booking: ongoingBooking
      };
    }

    const upcomingBooking = matchingBookings
      .filter(item => getBookingStatus(item.start, item.end) === 'pending')
      .sort((a, b) => new Date(a.start || 0).getTime() - new Date(b.start || 0).getTime())[0];

    if (upcomingBooking) {
      return {
        state: 'Reserved for Upcoming Trip',
        badgeClass: 'bg-sky-50 text-sky-700 border-sky-200',
        booking: upcomingBooking
      };
    }

    return {
      state: 'Available',
      badgeClass: 'bg-emerald-50 text-emerald-700 border-emerald-200',
      booking: null
    };
  }

  function getDriverProfileData(driver, bookings = []) {
    const normalizedBookings = (bookings || [])
      .map((booking) => normalizeBookingEvent(booking))
      .filter((item) => String(item.driver_id || '') === String(driver.id || ''));

    const operationalState = getDriverOperationalState(driver, bookings);
    const currentBooking = operationalState.booking || null;
    const upcomingBooking = normalizedBookings
      .filter((item) => getBookingStatus(item.start, item.end) === 'pending')
      .sort((a, b) => new Date(a.start || 0).getTime() - new Date(b.start || 0).getTime())[0] || null;

    const historyRows = normalizedBookings
      .filter((item) => getBookingStatus(item.start, item.end) === 'finished' || getTimelineBookingStatus(item) === 'cancelled')
      .sort((a, b) => new Date(b.start || 0).getTime() - new Date(a.start || 0).getTime());

    const completedTrips = historyRows.filter((item) => getBookingStatus(item.start, item.end) === 'finished').length;
    const cancelledTrips = historyRows.filter((item) => getTimelineBookingStatus(item) === 'cancelled').length;
    const lastTripDate = historyRows[0] ? historyRows[0].start : null;

    return {
      operationalState,
      currentBooking,
      upcomingBooking,
      recentTrips: historyRows.slice(0, 5),
      stats: {
        totalTrips: normalizedBookings.length,
        completedTrips,
        cancelledTrips,
        lastTripDate
      }
    };
  }

  function renderDriverProfileModal(driver) {
    if (!vehicleDetailsContentEl || !vehicleDetailsModalEl) return;

    const profile = getDriverProfileData(driver, fleetGalleryState.bookings);
    const driverName = driver.driver_name || [driver.first_name, driver.last_name].filter(Boolean).join(' ').trim() || 'Driver';
    const employeeId = driver.employee_id || driver.employeeId || '—';
    const mobileNumber = driver.mobile_number || driver.mobileNumber || '—';
    const email = driver.email || '—';
    const licenseNumber = driver.license_number || '—';
    const licenseClass = driver.license_class || '—';
    const licenseExpiry = driver.license_expiry || '—';
    const rawStatus = String(driver.status || 'active').toLowerCase();
    const statusLabel = profile.operationalState.state;
    const statusClass = profile.operationalState.badgeClass;

    const initials = (driverName || 'D').split(/\s+/).filter(Boolean).slice(0, 2).map((part) => part.charAt(0)).join('').toUpperCase() || 'D';
    const licenseExpiryDate = licenseExpiry !== '—' ? new Date(String(licenseExpiry).replace(' ', 'T')) : null;
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    let licenseStatusLabel = 'Pending';
    let licenseStatusClass = 'border-slate-200 bg-slate-50 text-slate-700';
    if (licenseExpiryDate && !Number.isNaN(licenseExpiryDate.getTime())) {
      const diffDays = Math.ceil((licenseExpiryDate - today) / (1000 * 60 * 60 * 24));
      if (diffDays < 0) {
        licenseStatusLabel = 'Expired';
        licenseStatusClass = 'border-rose-200 bg-rose-50 text-rose-700';
      } else if (diffDays <= 30) {
        licenseStatusLabel = 'Expiring Soon';
        licenseStatusClass = 'border-amber-200 bg-amber-50 text-amber-700';
      } else {
        licenseStatusLabel = 'Valid';
        licenseStatusClass = 'border-emerald-200 bg-emerald-50 text-emerald-700';
      }
    }

    const profileAvatar = driver.image_url || driver.image_filename ?
      (driver.image_url ? driver.image_url : baseUrl + 'uploads/driver/' + driver.image_filename)
      : null;

    const currentAssignmentHtml = profile.currentBooking ? `
      <div class="space-y-2 rounded-xl border border-slate-200 bg-slate-50 p-3">
        <div class="flex items-center justify-between gap-2">
          <span class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Current Status</span>
          <span class="rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700">${escapeHtml(statusLabel)}</span>
        </div>
        <div class="space-y-1 text-sm text-slate-700">
          <div><span class="font-semibold text-slate-900">Assigned Vehicle</span> • ${escapeHtml(profile.currentBooking.vehicle_name || 'Assigned vehicle')}</div>
          <div><span class="font-semibold text-slate-900">Booking Start</span> • ${escapeHtml(formatAvailabilityDateTime(profile.currentBooking.start) || '—')}</div>
          <div><span class="font-semibold text-slate-900">Booking End</span> • ${escapeHtml(formatAvailabilityDateTime(profile.currentBooking.end) || '—')}</div>
        </div>
      </div>
    ` : `
      <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500">
        Driver is currently available for assignment.
      </div>
    `;

    const upcomingAssignmentHtml = profile.upcomingBooking ? `
      <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700">
        <div class="space-y-1">
          <div class="font-semibold text-slate-900">${escapeHtml(profile.upcomingBooking.vehicle_name || 'Assigned vehicle')}</div>
          <div>${escapeHtml(formatAvailabilityDateTime(profile.upcomingBooking.start) || '—')}</div>
          <div class="text-slate-500">${escapeHtml(formatAvailabilityDateTime(profile.upcomingBooking.end) || '—')}</div>
        </div>
      </div>
    ` : `
      <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500">
        No upcoming bookings.
      </div>
    `;

    const recentTripsHtml = profile.recentTrips.length ? `
      <div class="overflow-hidden rounded-xl border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-xs">
          <thead class="bg-slate-50 text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">
            <tr>
              <th class="px-3 py-2 text-left">Date</th>
              <th class="px-3 py-2 text-left">Vehicle</th>
              <th class="px-3 py-2 text-left">Destination</th>
              <th class="px-3 py-2 text-left">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 bg-white">
            ${profile.recentTrips.map((item) => {
              const rowStatus = getTimelineBookingStatus(item) === 'cancelled' ? 'Cancelled' : 'Completed';
              return `
                <tr>
                  <td class="px-3 py-2 text-slate-700">${escapeHtml(formatAvailabilityDateTime(item.start) || '—')}</td>
                  <td class="px-3 py-2 text-slate-700">${escapeHtml(item.vehicle_name || '—')}</td>
                  <td class="px-3 py-2 text-slate-700">${escapeHtml(item.destination || item.purpose || '—')}</td>
                  <td class="px-3 py-2">
                    <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">${escapeHtml(rowStatus)}</span>
                  </td>
                </tr>
              `;
            }).join('')}
          </tbody>
        </table>
      </div>
    ` : `
      <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500">
        No recent trip history is available.
      </div>
    `;

    const metaContent = [
      ['Status', statusLabel],
      ['Employee ID', employeeId],
      ['License', licenseStatusLabel]
    ];

    if (vehicleDetailsMetaEl) {
      vehicleDetailsMetaEl.innerHTML = metaContent.map(([label, value]) => `
        <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-600">
          <span class="text-slate-400">${escapeHtml(label)}</span>
          <span class="rounded-full bg-slate-100 px-1.5 py-0.5 text-[10px] text-slate-700">${escapeHtml(String(value))}</span>
        </span>
      `).join('');
    }

    const subtitleEl = document.getElementById('vehicleDetailsModalSubtitle');
    if (subtitleEl) {
      subtitleEl.textContent = 'Operational overview, assignment status, and recent trip performance.';
    }

    const titleEl = document.getElementById('vehicleDetailsModalTitle');
    if (titleEl) {
      titleEl.textContent = 'Driver Fleet Profile';
    }

    vehicleDetailsContentEl.innerHTML = `
      <div class="space-y-3">
        <section class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-start gap-3">
              ${profileAvatar ? `<img src="${escapeHtml(profileAvatar)}" alt="${escapeHtml(driverName)}" class="h-12 w-12 rounded-full object-cover ring-2 ring-slate-200" />` : `<div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-200 text-sm font-semibold text-slate-700 ring-2 ring-white">${escapeHtml(initials)}</div>`}
              <div>
                <div class="flex flex-wrap items-center gap-2">
                  <h3 class="text-sm font-semibold text-slate-900">${escapeHtml(driverName)}</h3>
                  <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[10px] font-semibold ${escapeHtml(statusClass)}">
                    <span class="h-1.5 w-1.5 rounded-full ${rawStatus === 'inactive' ? 'bg-slate-400' : rawStatus === 'active' ? 'bg-emerald-600' : 'bg-amber-600'}"></span>
                    ${escapeHtml(statusLabel)}
                  </span>
                </div>
                <div class="mt-1 text-[11px] font-medium uppercase tracking-[0.2em] text-slate-500">Employee ID • ${escapeHtml(employeeId)}</div>
              </div>
            </div>
            <div class="flex flex-wrap gap-2">
              <button type="button" id="driverProfileEditBtn" class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-[11px] font-semibold text-slate-700 transition hover:bg-slate-50">Edit Driver</button>
              <button type="button" id="driverProfileActionBtn" class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-[11px] font-semibold text-slate-700 transition hover:bg-slate-50">${rawStatus === 'active' ? 'Disable Driver' : 'Enable Driver'}</button>
            </div>
          </div>
        </section>

        <div class="grid gap-3 lg:grid-cols-2">
          <section class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
            <h4 class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Driver Information</h4>
            <div class="mt-2 space-y-2 text-sm text-slate-700">
              <div class="flex items-center justify-between gap-2"><span class="text-slate-500">Employee ID</span><span class="font-medium text-slate-900">${escapeHtml(employeeId)}</span></div>
              <div class="flex items-center justify-between gap-2"><span class="text-slate-500">Mobile Number</span><span class="font-medium text-slate-900">${escapeHtml(mobileNumber)}</span></div>
              <div class="flex items-center justify-between gap-2"><span class="text-slate-500">Email</span><span class="font-medium text-slate-900">${escapeHtml(email)}</span></div>
            </div>
          </section>
          <section class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
            <h4 class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">License Information</h4>
            <div class="mt-2 space-y-2 text-sm text-slate-700">
              <div class="flex items-center justify-between gap-2"><span class="text-slate-500">License Number</span><span class="font-medium text-slate-900">${escapeHtml(licenseNumber)}</span></div>
              <div class="flex items-center justify-between gap-2"><span class="text-slate-500">License Class</span><span class="font-medium text-slate-900">${escapeHtml(licenseClass)}</span></div>
              <div class="flex items-center justify-between gap-2"><span class="text-slate-500">License Expiry</span><span class="font-medium text-slate-900">${escapeHtml(licenseExpiry)}</span></div>
              <div class="mt-2"><span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-semibold ${escapeHtml(licenseStatusClass)}">${escapeHtml(licenseStatusLabel)}</span></div>
            </div>
          </section>
        </div>

        <div class="grid gap-3 lg:grid-cols-2">
          <section class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
            <h4 class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Current Assignment</h4>
            <div class="mt-2">${currentAssignmentHtml}</div>
          </section>
          <section class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
            <h4 class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Upcoming Booking</h4>
            <div class="mt-2">${upcomingAssignmentHtml}</div>
          </section>
        </div>

        <section class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
          <h4 class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Quick Statistics</h4>
          <div class="mt-3 grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-2.5">
              <div class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500">Total Trips</div>
              <div class="mt-1 text-lg font-semibold text-slate-900">${escapeHtml(String(profile.stats.totalTrips))}</div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-2.5">
              <div class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500">Completed Trips</div>
              <div class="mt-1 text-lg font-semibold text-slate-900">${escapeHtml(String(profile.stats.completedTrips))}</div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-2.5">
              <div class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500">Cancelled Trips</div>
              <div class="mt-1 text-lg font-semibold text-slate-900">${escapeHtml(String(profile.stats.cancelledTrips))}</div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-2.5">
              <div class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500">Last Trip Date</div>
              <div class="mt-1 text-sm font-semibold text-slate-900">${escapeHtml(profile.stats.lastTripDate ? formatAvailabilityDateTime(profile.stats.lastTripDate) : '—')}</div>
            </div>
          </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
          <h4 class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Recent Trips</h4>
          <div class="mt-2">${recentTripsHtml}</div>
        </section>
      </div>
    `;

    const editBtn = document.getElementById('driverProfileEditBtn');
    if (editBtn) {
      editBtn.addEventListener('click', () => {
        setDriversModalFormFromRow(driver);
        openModal(driversModalEl);
        closeModal(vehicleDetailsModalEl);
      });
    }

    const actionBtn = document.getElementById('driverProfileActionBtn');
    if (actionBtn) {
      actionBtn.addEventListener('click', () => {
        showFleetCardActionModal({
          itemType: 'drivers',
          itemId: driver.id,
          itemName: driverName,
          action: rawStatus === 'active' ? 'disable' : 'enable',
          itemData: driver
        });
      });
    }
  }

  function createDriverCard(driver) {
    const card = document.createElement('div');
    const bookingContext = getDriverOperationalState(driver, fleetGalleryState.bookings);
    const rawStatus = (driver.status || 'active').toString().toLowerCase();
    const driverName = driver.driver_name || 'Driver';
    const employee = driver.employee_id || 'N/A';
    const driverInitials = (driverName || 'D').split(/\s+/).filter(Boolean).slice(0, 2).map(part => part.charAt(0)).join('').toUpperCase() || 'D';
    const licenseClass = driver.license_class || 'N/A';
    const licenseExpiry = driver.license_expiry || 'N/A';
    const licenseStatus = (() => {
      if (!licenseExpiry || licenseExpiry === 'N/A') {
        return { label: 'Pending', tone: 'slate', dot: 'bg-slate-400' };
      }
      const expiryDate = new Date(licenseExpiry);
      if (Number.isNaN(expiryDate.getTime())) {
        return { label: 'Pending', tone: 'slate', dot: 'bg-slate-400' };
      }
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      const diffDays = Math.ceil((expiryDate - today) / (1000 * 60 * 60 * 24));
      if (diffDays < 0) {
        return { label: 'Expired', tone: 'rose', dot: 'bg-rose-500' };
      }
      if (diffDays <= 30) {
        return { label: 'Expiring Soon', tone: 'amber', dot: 'bg-amber-500' };
      }
      return { label: 'Valid', tone: 'emerald', dot: 'bg-emerald-500' };
    })();
    const licenseToneClass = licenseStatus.tone === 'rose'
      ? 'bg-rose-50 text-rose-700 border-rose-200'
      : licenseStatus.tone === 'amber'
        ? 'bg-amber-50 text-amber-700 border-amber-200'
        : licenseStatus.tone === 'emerald'
          ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
          : 'bg-slate-50 text-slate-700 border-slate-200';
    const availabilityState = bookingContext.state === 'Reserved for Upcoming Trip' ? 'Reserved' : bookingContext.state;
    const availabilityClass = bookingContext.badgeClass;

    card.className = 'flex h-full flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-white transition-colors duration-200 hover:border-slate-300';

    card.innerHTML = `
      <div class="flex items-start justify-between gap-3 p-4">
        <div class="flex items-center gap-3 min-w-0">
          <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-sm font-semibold text-slate-700">${escapeHtml(driverInitials)}</div>
          <div class="min-w-0">
            <h3 class="truncate text-sm font-semibold text-slate-900">${escapeHtml(driverName)}</h3>
            <p class="truncate text-xs text-slate-500">ID ${escapeHtml(employee)}</p>
          </div>
        </div>
        <div class="whitespace-nowrap rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] ${escapeHtml(availabilityClass)}">${escapeHtml(availabilityState)}</div>
      </div>
      <div class="space-y-3 px-4 pb-4">
        <div class="grid gap-2 text-sm text-slate-600 sm:grid-cols-2">
          <div class="truncate">
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">License class</p>
            <p class="truncate font-medium text-slate-900">${escapeHtml(licenseClass)}</p>
          </div>
          <div class="truncate">
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Expiry</p>
            <p class="truncate font-medium text-slate-900">${escapeHtml(licenseExpiry)}</p>
          </div>
        </div>
        <div class="flex items-center justify-between text-[12px] text-slate-500">
          <span class="inline-flex items-center gap-2">
            <span class="h-2.5 w-2.5 rounded-full ${escapeHtml(licenseStatus.dot)}"></span>
            ${escapeHtml(licenseStatus.label)}
          </span>
          <span class="hidden text-slate-400 sm:inline">Driver</span>
        </div>
        <div class="flex gap-2">
          <button type="button" class="flex-1 h-9 rounded-lg bg-slate-900 px-3 text-sm font-semibold text-white transition hover:bg-slate-800 view-vehicle-btn" data-vehicle-id="${driver.id}">View Details</button>
          <button type="button" class="flex-1 h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 fleet-card-action-btn" data-action="${rawStatus === 'active' ? 'disable' : 'enable'}">${rawStatus === 'active' ? 'Disable' : 'Enable'}</button>
        </div>
      </div>
    `;

    const driverActionBtn = card.querySelector('.fleet-card-action-btn');
    if (driverActionBtn) {
      driverActionBtn.addEventListener('click', () => {
        showFleetCardActionModal({
          itemType: 'drivers',
          itemId: driver.id,
          itemName: driverName,
          action: driverActionBtn.getAttribute('data-action'),
          itemData: driver
        });
      });
    }

    const detailBtn = card.querySelector('.view-vehicle-btn');
    if (detailBtn) {
      detailBtn.addEventListener('click', () => {
        renderDriverProfileModal(driver);
        openModal(vehicleDetailsModalEl);
      });
    }

    return card;
  }

  if (fleetCardActionCloseBtn) {
    fleetCardActionCloseBtn.addEventListener('click', hideFleetCardActionModal);
  }
  if (fleetCardActionCancelBtn) {
    fleetCardActionCancelBtn.addEventListener('click', hideFleetCardActionModal);
  }
  if (fleetCardActionBackdrop) {
    fleetCardActionBackdrop.addEventListener('click', hideFleetCardActionModal);
  }
  if (fleetCardActionConfirmBtn) {
    fleetCardActionConfirmBtn.addEventListener('click', performFleetCardAction);
  }

  function normalizeFleetSearchTerm(value) {
    return String(value || '').trim().toLowerCase();
  }

  function renderFleetGalleryCards() {
    if (!vehicleCardsContainer) return;
    const term = normalizeFleetSearchTerm(fleetGalleryState.searchTerm);
    const activeTab = fleetGalleryState.activeTab;
    const pageSize = fleetGalleryState.pageSize || 8;

    if (fleetSearchInput) {
      fleetSearchInput.placeholder = activeTab === 'drivers' ? 'Search drivers' : 'Search vehicles';
    }

    let items = activeTab === 'drivers'
      ? fleetGalleryState.drivers
      : fleetGalleryState.vehicles;

    const filtered = items.filter(item => {
      if (activeTab === 'drivers') {
        return [item.driver_name, item.status, item.license_number, item.contact]
          .filter(Boolean)
          .some(value => normalizeFleetSearchTerm(value).includes(term));
      }
      return [item.vehicle_name, item.plate_number, item.status, item.make, item.model]
        .filter(Boolean)
        .some(value => normalizeFleetSearchTerm(value).includes(term));
    });

    const totalItems = filtered.length;
    const totalPages = Math.max(1, Math.ceil(totalItems / pageSize));
    fleetGalleryState.currentPage = Math.min(Math.max(1, fleetGalleryState.currentPage), totalPages);

    const startIndex = (fleetGalleryState.currentPage - 1) * pageSize;
    const pageItems = filtered.slice(startIndex, startIndex + pageSize);

    vehicleCardsContainer.innerHTML = '';
    if (pageItems.length === 0) {
      vehicleCardsContainer.innerHTML = `<div class="col-span-full rounded-xl border border-slate-200 bg-slate-50 p-6 text-center text-slate-500">No ${activeTab} found</div>`;
    } else {
      pageItems.forEach(item => {
        const card = activeTab === 'drivers' ? createDriverCard(item) : createVehicleCard(item);
        vehicleCardsContainer.appendChild(card);
      });
    }

      const startResult = totalItems === 0 ? 0 : startIndex + 1;
    const endResult = totalItems === 0 ? 0 : startIndex + pageItems.length;
    if (fleetPaginationControlsEl) {
      fleetPaginationInfoEl.textContent = totalItems === 0
        ? `No ${activeTab} found`
        : `Showing ${startResult}–${endResult} of ${totalItems} results`;
      fleetPaginationControlsEl.classList.toggle('hidden', totalPages <= 1);
    }

    if (fleetGalleryMetaEl) {
      fleetGalleryMetaEl.textContent = `${totalItems} ${activeTab === 'drivers' ? 'Drivers' : 'Vehicles'} • Page ${fleetGalleryState.currentPage} of ${totalPages}`;
    }

    if (fleetTabVehiclesBtn) {
      fleetTabVehiclesBtn.textContent = `Vehicles (${fleetGalleryState.vehicles.length})`;
    }
    if (fleetTabDriversBtn) {
      fleetTabDriversBtn.textContent = `Drivers (${fleetGalleryState.drivers.length})`;
    }

    updateFleetPageNumbers(totalPages);

    if (fleetPrevPageBtn) {
      fleetPrevPageBtn.disabled = fleetGalleryState.currentPage <= 1;
    }
    if (fleetNextPageBtn) {
      fleetNextPageBtn.disabled = fleetGalleryState.currentPage >= totalPages;
    }

    updateFleetGalleryTabStyles();
  }

  function updateFleetPageNumbers(totalPages) {
    if (!fleetPageNumbersEl) return;
    fleetPageNumbersEl.innerHTML = '';
    if (totalPages <= 1) {
      fleetPageNumbersEl.classList.add('hidden');
      return;
    }
    fleetPageNumbersEl.classList.remove('hidden');
    const buttons = [];
    if (totalPages <= 7) {
      for (let i = 1; i <= totalPages; i += 1) buttons.push(i);
    } else {
      const left = Math.max(1, fleetGalleryState.currentPage - 2);
      const right = Math.min(totalPages, fleetGalleryState.currentPage + 2);
      if (left > 1) buttons.push(1);
      if (left > 2) buttons.push('...');
      for (let i = left; i <= right; i += 1) buttons.push(i);
      if (right < totalPages - 1) buttons.push('...');
      if (right < totalPages) buttons.push(totalPages);
    }

    buttons.forEach(value => {
      if (value === '...') {
        const ellipsis = document.createElement('span');
        ellipsis.className = 'px-2 text-xs text-slate-400';
        ellipsis.textContent = '…';
        fleetPageNumbersEl.appendChild(ellipsis);
        return;
      }
      const pageButton = document.createElement('button');
      pageButton.type = 'button';
      pageButton.textContent = String(value);
      pageButton.className = value === fleetGalleryState.currentPage
        ? 'inline-flex h-9 items-center justify-center rounded-lg bg-slate-900 px-3 text-sm font-semibold text-white'
        : 'inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 hover:bg-slate-50';
      if (value === fleetGalleryState.currentPage) {
        pageButton.disabled = true;
      }
      pageButton.addEventListener('click', () => {
        fleetGalleryState.currentPage = value;
        renderFleetGalleryCards();
      });
      fleetPageNumbersEl.appendChild(pageButton);
    });
  }

  function updateFleetGalleryTabStyles() {
    if (!fleetTabVehiclesBtn || !fleetTabDriversBtn) return;
    const active = fleetGalleryState.activeTab;
    fleetTabVehiclesBtn.classList.toggle('bg-slate-100', active === 'vehicles');
    fleetTabVehiclesBtn.classList.toggle('text-slate-900', active === 'vehicles');
    fleetTabVehiclesBtn.classList.toggle('text-slate-600', active !== 'vehicles');
    fleetTabDriversBtn.classList.toggle('bg-slate-100', active === 'drivers');
    fleetTabDriversBtn.classList.toggle('text-slate-900', active === 'drivers');
    fleetTabDriversBtn.classList.toggle('text-slate-600', active !== 'drivers');
  }

  let fleetGalleryState = {
    vehicles: [],
    drivers: [],
    bookings: [],
    activeTab: 'vehicles',
    searchTerm: '',
    currentPage: 1,
    pageSize: 8
  };

  if (fleetTabVehiclesBtn) {
    fleetTabVehiclesBtn.addEventListener('click', () => {
      fleetGalleryState.activeTab = 'vehicles';
      fleetGalleryState.currentPage = 1;
      renderFleetGalleryCards();
    });
  }

  if (fleetTabDriversBtn) {
    fleetTabDriversBtn.addEventListener('click', () => {
      fleetGalleryState.activeTab = 'drivers';
      fleetGalleryState.currentPage = 1;
      renderFleetGalleryCards();
    });
  }

  if (fleetSearchInput) {
    fleetSearchInput.addEventListener('input', event => {
      fleetGalleryState.searchTerm = event.target.value || '';
      fleetGalleryState.currentPage = 1;
      renderFleetGalleryCards();
    });
  }

  if (fleetAddNewBtn && fleetAddNewMenu) {
    fleetAddNewBtn.addEventListener('click', event => {
      event.stopPropagation();
      const expanded = fleetAddNewBtn.getAttribute('aria-expanded') === 'true';
      fleetAddNewBtn.setAttribute('aria-expanded', String(!expanded));
      fleetAddNewMenu.classList.toggle('hidden');
    });

    document.addEventListener('click', event => {
      if (!fleetAddNewMenu.contains(event.target) && !fleetAddNewBtn.contains(event.target)) {
        fleetAddNewMenu.classList.add('hidden');
        fleetAddNewBtn.setAttribute('aria-expanded', 'false');
      }
    });
  }

  if (fleetPrevPageBtn) {
    fleetPrevPageBtn.addEventListener('click', () => {
      if (fleetGalleryState.currentPage > 1) {
        fleetGalleryState.currentPage -= 1;
        renderFleetGalleryCards();
      }
    });
  }

  if (fleetNextPageBtn) {
    fleetNextPageBtn.addEventListener('click', () => {
      fleetGalleryState.currentPage += 1;
      renderFleetGalleryCards();
    });
  }

  // Initialize initial gallery state
  renderFleetGalleryCards();

  function formatAvailabilityDateTime(dtStr) {
    if (!dtStr) return '';
    const value = String(dtStr).replace(' ', 'T');
    const dateValue = new Date(value);
    if (isNaN(dateValue.getTime())) return '';

    const today = new Date();
    const todayStart = new Date(today.getFullYear(), today.getMonth(), today.getDate());
    const tomorrowStart = new Date(today.getFullYear(), today.getMonth(), today.getDate() + 1);
    const dateStart = new Date(dateValue.getFullYear(), dateValue.getMonth(), dateValue.getDate());
    const dayLabel = dateStart.getTime() === todayStart.getTime()
      ? 'Today'
      : dateStart.getTime() === tomorrowStart.getTime()
        ? 'Tomorrow'
        : dateValue.toLocaleDateString([], { month: 'short', day: 'numeric' });
    const timeLabel = dateValue.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
    return `${dayLabel} • ${timeLabel}`;
  }

  function formatAvailabilityTime(dtStr, forceDate = false) {
    if (!dtStr) return '';
    const value = String(dtStr).replace(' ', 'T');
    const dateValue = new Date(value);
    if (isNaN(dateValue.getTime())) return '';

    const today = new Date();
    const todayStart = new Date(today.getFullYear(), today.getMonth(), today.getDate());
    const dateStart = new Date(dateValue.getFullYear(), dateValue.getMonth(), dateValue.getDate());
    const timeLabel = dateValue.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });

    if (dateStart.getTime() === todayStart.getTime()) {
      return `Today • ${timeLabel}`;
    }

    if (forceDate) {
      return `${dateValue.toLocaleDateString([], { month: 'short', day: 'numeric' })} • ${timeLabel}`;
    }

    return timeLabel;
  }

  function formatBookingRange(startStr, endStr) {
    if (!startStr) return '';
    const start = new Date(String(startStr).replace(' ', 'T'));
    if (isNaN(start.getTime())) return '';

    const end = endStr ? new Date(String(endStr).replace(' ', 'T')) : null;
    const sameDay = end && end.getFullYear() === start.getFullYear() && end.getMonth() === start.getMonth() && end.getDate() === start.getDate();

    const startLabel = `${start.toLocaleDateString([], { month: 'short', day: 'numeric' })} • ${start.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' })}`;
    if (sameDay && end) {
      return `${startLabel} – ${end.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' })}`;
    }

    if (end && !isNaN(end.getTime())) {
      return `${startLabel} – ${end.toLocaleDateString([], { month: 'short', day: 'numeric' })} • ${end.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' })}`;
    }

    return startLabel;
  }

  function buildVehicleAvailabilitySummary(upcoming, currentStatus = '') {
    if (currentStatus === 'maintenance') {
      return 'Maintenance';
    }
    if (currentStatus === 'inactive') {
      return 'Disabled';
    }

    const ongoing = upcoming.find(b => getBookingStatus(b.departure_expected, b.return_expected) === 'ongoing');
    if (ongoing) {
      const endAt = ongoing.return_expected || ongoing.end_at || '';
      if (!endAt) {
        return 'On Trip';
      }
      const endDate = new Date(String(endAt).replace(' ', 'T'));
      const today = new Date();
      const todayStart = new Date(today.getFullYear(), today.getMonth(), today.getDate());
      const endDayStart = new Date(endDate.getFullYear(), endDate.getMonth(), endDate.getDate());
      const returnTime = endDayStart.getTime() === todayStart.getTime()
        ? formatAvailabilityTime(endAt, false)
        : formatAvailabilityTime(endAt, true);
      return returnTime ? `On Trip • Returns ${returnTime}` : 'On Trip';
    }

    const pendingBookings = upcoming
      .filter(b => getBookingStatus(b.departure_expected, b.return_expected) === 'pending')
      .sort((a, b) => {
        const aTime = new Date(a.departure_expected || a.start_at || 0).getTime();
        const bTime = new Date(b.departure_expected || b.start_at || 0).getTime();
        return aTime - bTime;
      });

    if (!pendingBookings.length) {
      return 'Available Now';
    }

    const nextStart = pendingBookings[0].departure_expected || pendingBookings[0].start_at || '';
    const nextStartDate = new Date(String(nextStart).replace(' ', 'T'));
    const today = new Date();
    const todayStart = new Date(today.getFullYear(), today.getMonth(), today.getDate());
    const nextDayStart = new Date(nextStartDate.getFullYear(), nextStartDate.getMonth(), nextStartDate.getDate());
    const startLabel = nextDayStart.getTime() === todayStart.getTime()
      ? formatAvailabilityTime(nextStart, false)
      : formatAvailabilityTime(nextStart, true);

    return startLabel ? `Reserved • Starts ${startLabel}` : 'Reserved';
  }

  function loadVehicleUpcoming(vehicleId, card) {
    const loadingEl = card.querySelector(`#loading-${vehicleId}`);
    const bookingsEl = card.querySelector(`#bookings-${vehicleId}`);
    const badgeEl = card.querySelector(`#vehicle-status-badge-${vehicleId}`);
    const dotEl = card.querySelector(`#vehicle-status-dot-${vehicleId}`);

    fetch(vehicleHistoryUrl + '&vehicle_id=' + vehicleId + '&t=' + Date.now(), {
      method: 'GET',
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(parseJsonResponse)
    .then(data => {
      if (loadingEl) loadingEl.style.display = 'none';

      const rows = (data && Array.isArray(data.bookings)) ? data.bookings : [];
      const upcoming = rows.filter(b => {
        const st = getBookingStatus(b.departure_expected, b.return_expected);
        return st === 'pending' || st === 'ongoing';
      });

      const currentStatus = card.dataset.vehicleStatus;
      const isUnavailable = currentStatus === 'inactive' || currentStatus === 'maintenance';
      const ongoingBooking = upcoming.find(b => getBookingStatus(b.departure_expected, b.return_expected) === 'ongoing');
      const pendingBookings = upcoming
        .filter(b => getBookingStatus(b.departure_expected, b.return_expected) === 'pending')
        .sort((a, b) => {
          const aTime = new Date(a.departure_expected || a.start_at || 0).getTime();
          const bTime = new Date(b.departure_expected || b.start_at || 0).getTime();
          return aTime - bTime;
        });

      if (bookingsEl) {
        bookingsEl.textContent = buildVehicleAvailabilitySummary(upcoming, currentStatus);
        bookingsEl.classList.remove('hidden');
      }

      const nextTripEl = card.querySelector(`#next-trip-${vehicleId}`);
      const nextTripTitleEl = card.querySelector(`#next-trip-title-${vehicleId}`);
      const nextTripTimeEl = card.querySelector(`#next-trip-time-${vehicleId}`);
      if (!isUnavailable && pendingBookings.length && nextTripEl && nextTripTitleEl && nextTripTimeEl) {
        const next = pendingBookings[0];
        const start = next.departure_expected || next.start_at || '';
        const end = next.return_expected || next.end_at || '';
        nextTripTitleEl.textContent = escapeHtml(next.purpose || next.title || next.trip_name || 'Next trip');
        nextTripTimeEl.textContent = formatBookingRange(start, end);
        nextTripEl.classList.remove('hidden');
      } else if (nextTripEl) {
        nextTripEl.classList.add('hidden');
      }

      if (badgeEl) {
        const statusTextEl = card.querySelector(`#vehicle-status-text-${vehicleId}`);
        const badgeText = currentStatus === 'maintenance'
          ? 'Maintenance'
          : currentStatus === 'inactive'
            ? 'Disabled'
            : ongoingBooking
              ? 'On Trip'
              : 'Available';
        if (statusTextEl) {
          statusTextEl.textContent = badgeText;
        }
        if (dotEl) {
          const dotColor = badgeText === 'Maintenance'
            ? 'bg-amber-600'
            : badgeText === 'Disabled'
              ? 'bg-slate-400'
              : badgeText === 'On Trip'
                ? 'bg-blue-600'
                : 'bg-emerald-600';
          dotEl.className = `inline-flex h-1.5 w-1.5 rounded-full ${dotColor}`;
        }
      }
    })
    .catch(err => {
      console.error('Error loading upcoming bookings:', err);
      if (loadingEl) {
        loadingEl.style.display = 'block';
        loadingEl.innerHTML = '<span class="text-xs text-rose-500">Failed to load availability</span>';
      }
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
  function getTimelineBookingStatus(booking) {
    const serverStatus = String(booking.status || booking.booking_status || '').toLowerCase();
    if (serverStatus === 'cancelled' || serverStatus === 'canceled') {
      return 'cancelled';
    }
    return getBookingStatus(booking.departure_expected || booking.start_at || '', booking.return_expected || booking.end_at || '');
  }

  function getTimelineBookingTitle(booking) {
    return booking.purpose || booking.title || booking.trip_name || 'Booking';
  }

  function getTimelineScheduleText(booking) {
    const start = formatLocalDisplay(booking.departure_expected || booking.start_at || '');
    const end = formatLocalDisplay(booking.return_expected || booking.end_at || '');
    if (start && end) {
      return `${start} → ${end}`;
    }
    return start || end || 'Schedule to be confirmed';
  }

  function getTimelineDriverText(booking) {
    return booking.driver_name || booking.driver || booking.assigned_driver || 'Driver pending';
  }

  function getTimelineDestinationText(booking) {
    return booking.destinations || booking.destination || booking.trip_destination || '';
  }

  function sortTimelineBookings(a, b) {
    const aTime = new Date(a.departure_expected || a.start_at || a.return_expected || a.end_at || '').getTime();
    const bTime = new Date(b.departure_expected || b.start_at || b.return_expected || b.end_at || '').getTime();
    const aValue = Number.isFinite(aTime) ? aTime : 0;
    const bValue = Number.isFinite(bTime) ? bTime : 0;
    if (aValue === bValue) {
      return Number(b.id || 0) - Number(a.id || 0);
    }
    return bValue - aValue;
  }

  function buildTimelineCard(booking, onSelect) {
    const card = document.createElement('button');
    card.type = 'button';
    card.className = 'w-full rounded-xl border border-slate-200 bg-white p-4 text-left shadow-sm transition hover:border-slate-300 hover:bg-slate-50';

    const status = getTimelineBookingStatus(booking);
    const statusLabel = status === 'pending' ? 'Upcoming' : status === 'ongoing' ? 'Ongoing' : status === 'finished' ? 'Completed' : status === 'cancelled' ? 'Cancelled' : 'Scheduled';
    const badgeClass = getStatusBadgeClass(status);
    const driverText = getTimelineDriverText(booking);
    const destinationText = getTimelineDestinationText(booking);

    card.innerHTML = `
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
          <div class="flex items-center gap-2">
            <p class="truncate text-sm font-semibold text-slate-900">${escapeHtml(getTimelineBookingTitle(booking))}</p>
            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[10px] font-semibold ${badgeClass}">${escapeHtml(statusLabel)}</span>
          </div>
          <p class="mt-1 text-xs text-slate-500">${escapeHtml(getTimelineScheduleText(booking))}</p>
          <div class="mt-2 flex flex-wrap gap-2 text-[11px] text-slate-500">
            <span class="inline-flex items-center gap-1"><span class="font-medium text-slate-600">Driver</span>${escapeHtml(driverText)}</span>
            ${destinationText ? `<span class="inline-flex items-center gap-1"><span class="font-medium text-slate-600">To</span>${escapeHtml(destinationText)}</span>` : ''}
          </div>
        </div>
        <div class="flex flex-shrink-0 flex-col items-end gap-2 text-xs font-medium text-slate-600">
          <span class="text-[11px] text-slate-400">View</span>
        <span class="text-[11px] font-semibold text-slate-700">  //// </span>
        </div>
      </div>
    `;

    card.addEventListener('click', (event) => {
      event.preventDefault();
      onSelect(booking);
    });

    return card;
  }

  function renderTimelineSections(rows, options = {}) {
    const { showAll = false, maxVisible = 4 } = options;
    const container = vehicleDetailsContentEl;
    if (!container) return;

    const sortedRows = [...rows].sort(sortTimelineBookings);
    const upcomingRows = sortedRows.filter((booking) => getTimelineBookingStatus(booking) === 'pending');
    const ongoingRows = sortedRows.filter((booking) => getTimelineBookingStatus(booking) === 'ongoing');
    const pastRows = sortedRows.filter((booking) => {
      const status = getTimelineBookingStatus(booking);
      return status === 'finished' || status === 'cancelled';
    });

    const allSections = [
      { key: 'upcoming', title: 'Upcoming', items: upcomingRows, emptyMessage: 'No upcoming bookings are scheduled for this vehicle at the moment.' },
      ...(ongoingRows.length > 0 ? [{ key: 'ongoing', title: 'Ongoing', items: ongoingRows, emptyMessage: '' }] : []),
      { key: 'past', title: 'Past Bookings', items: pastRows, emptyMessage: 'No completed booking history is available yet.' }
    ];

    const totalItems = sortedRows.length;
    const shouldShowToggle = totalItems > maxVisible;

    const renderSection = (section) => {
      const sectionEl = document.createElement('section');
      sectionEl.className = 'space-y-3';

      const header = document.createElement('div');
      header.className = 'flex items-center justify-between gap-2';
      const titleEl = document.createElement('h3');
      titleEl.className = 'text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500';
      titleEl.textContent = section.title;
      const countEl = document.createElement('span');
      countEl.className = 'rounded-full border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-[10px] font-semibold text-slate-600';
      countEl.textContent = String(section.items.length);
      header.appendChild(titleEl);
      header.appendChild(countEl);
      sectionEl.appendChild(header);

      if (!section.items.length) {
        const emptyEl = document.createElement('div');
        emptyEl.className = 'rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500';
        emptyEl.textContent = section.emptyMessage;
        sectionEl.appendChild(emptyEl);
        return sectionEl;
      }

      const listEl = document.createElement('div');
      listEl.className = 'space-y-2';
      const visibleItems = showAll ? section.items : section.items.slice(0, maxVisible);
      visibleItems.forEach((booking) => {
        listEl.appendChild(buildTimelineCard(booking, (selectedBooking) => {
          loadBookingReadOnly(selectedBooking.id);
          closeModal(vehicleDetailsModalEl);
        }));
      });
      sectionEl.appendChild(listEl);
      return sectionEl;
    };

    container.innerHTML = '';
    const wrapper = document.createElement('div');
    wrapper.className = 'space-y-5';

    allSections.forEach((section) => {
      wrapper.appendChild(renderSection(section));
    });

    if (shouldShowToggle) {
      const toggleEl = document.createElement('button');
      toggleEl.type = 'button';
      toggleEl.className = 'text-sm font-semibold text-slate-600 transition hover:text-slate-900';
      toggleEl.textContent = showAll ? 'Show fewer entries ←' : 'View Full Booking History →';
      toggleEl.addEventListener('click', () => {
        renderTimelineSections(rows, { showAll: !showAll, maxVisible });
      });
      wrapper.appendChild(toggleEl);
    }

    container.appendChild(wrapper);
  }

  function showVehicleDetails(vehicleId, card) {
    // open vehicle details modal and populate with clickable bookings
    if (!vehicleDetailsContentEl || !vehicleDetailsModalEl) return;
    vehicleDetailsContentEl.innerHTML = '<div class="text-sm text-slate-500">Loading booking timeline...</div>';
    if (vehicleDetailsMetaEl) {
      vehicleDetailsMetaEl.innerHTML = '';
    }
    openModal(vehicleDetailsModalEl);

    fetch(vehicleHistoryUrl + '&vehicle_id=' + vehicleId + '&t=' + Date.now(), {
      method: 'GET',
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(parseJsonResponse)
    .then(data => {
      const rows = (data && Array.isArray(data.bookings)) ? data.bookings : [];
      const counts = {
        total: rows.length,
        upcoming: rows.filter((booking) => getTimelineBookingStatus(booking) === 'pending').length,
        ongoing: rows.filter((booking) => getTimelineBookingStatus(booking) === 'ongoing').length,
        completed: rows.filter((booking) => getTimelineBookingStatus(booking) === 'finished').length
      };

      if (vehicleDetailsMetaEl) {
        const metaItems = [
          ['Total Bookings', counts.total],
          ['Upcoming', counts.upcoming],
          ['Ongoing', counts.ongoing],
          ['Completed', counts.completed]
        ];
        vehicleDetailsMetaEl.innerHTML = metaItems.map(([label, value]) => `
          <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-600">
            <span class="text-slate-400">${escapeHtml(label)}</span>
            <span class="rounded-full bg-slate-100 px-1.5 py-0.5 text-[10px] text-slate-700">${escapeHtml(String(value))}</span>
          </span>
        `).join('');
      }

      const detailsActionsEl = document.getElementById('vehicleDetailsActions');
      if (detailsActionsEl) {
        const cardName = card ? card.dataset.vehicleName : null;
        const cardStatus = card ? card.dataset.vehicleStatus : 'active';
        const action = cardStatus === 'active' ? 'disable' : 'enable';
        const label = action === 'disable' ? 'Disable' : 'Enable';
        const detailsBtn = document.createElement('button');
        detailsBtn.type = 'button';
        detailsBtn.className = 'h-9 rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 transition hover:bg-slate-50';
        detailsBtn.textContent = label;
        detailsBtn.addEventListener('click', () => {
          showFleetCardActionModal({
            itemType: 'vehicles',
            itemId: vehicleId,
            itemName: cardName || 'Vehicle',
            action: action,
            itemData: {
              id: vehicleId,
              vehicle_name: cardName || 'Vehicle',
              plate_number: card ? card.dataset.vehiclePlate : '',
              capacity: parseInt(card ? card.dataset.vehicleCapacity : '', 10) || 1,
              status: cardStatus
            }
          });
        });
        detailsActionsEl.innerHTML = '';
        detailsActionsEl.appendChild(detailsBtn);
      }

      renderTimelineSections(rows, { showAll: false, maxVisible: 4 });
    })
    .catch(err => {
      console.error('Error loading history details:', err);
      if (vehicleDetailsContentEl) vehicleDetailsContentEl.innerHTML = '<p class="text-xs text-rose-500 text-center py-1">Failed to load booking timeline</p>';
    });
  }

  function setBookingPreviewStatus(label, tone = 'neutral') {
    if (!bookingPreviewStatusEl) return;
    const toneClasses = {
      neutral: 'border-slate-200 bg-white text-slate-600',
      pending: 'border-amber-200 bg-amber-50 text-amber-700',
      ongoing: 'border-blue-200 bg-blue-50 text-blue-700',
      finished: 'border-emerald-200 bg-emerald-50 text-emerald-700',
      cancelled: 'border-rose-200 bg-rose-50 text-rose-700'
    };
    bookingPreviewStatusEl.className = `rounded-full border px-2.5 py-1 text-[10px] font-medium ${toneClasses[tone] || toneClasses.neutral}`;
    bookingPreviewStatusEl.textContent = label;
  }

  function toggleFleetSnapshotVisibility(show) {
    if (fleetSnapshotSectionEl) {
      fleetSnapshotSectionEl.classList.toggle('hidden', !show);
    }
  }

  function formatScheduleDateTime(date) {
    if (!date || isNaN(date.getTime())) return '';
    const dateLabel = date.toLocaleDateString([], { month: 'short', day: 'numeric' });
    const timeLabel = date.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
    return `${dateLabel} • ${timeLabel}`;
  }

  function getNearestScheduleFocusItems() {
    if (!calendar) return { today: null, upcoming: null };
    const now = new Date();
    const todayStart = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0, 0);
    const todayEnd = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59, 999);

    const items = calendar.getEvents()
      .map(ev => {
        const start = ev.start ? new Date(ev.start) : null;
        if (!start || isNaN(start.getTime())) return null;
        const ext = ev.extendedProps || {};
        const title = String(ev.title || ext.purpose || ext.title || ext.trip_name || 'Scheduled trip').trim();
        const vehicleName = String(ext.vehicle_name || ext.vehicleName || '').trim();
        const plate = String(ext.plate_number || ext.plateNumber || '').trim();
        const vehicleLabel = vehicleName && plate ? `${vehicleName} (${plate})` : vehicleName || plate || 'Vehicle schedule';
        return { title, vehicleLabel, start };
      })
      .filter(Boolean)
      .sort((a, b) => a.start - b.start);

    const todayItem = items.find(item => item.start >= todayStart && item.start <= todayEnd);
    const upcomingItem = items.find(item => item.start > todayEnd);
    return { today: todayItem || null, upcoming: upcomingItem || null };
  }

  function renderScheduleFocusPanel() {
    if (!scheduleFocusPanel) return;
    const todayTitleEl = document.getElementById('scheduleFocusTodayTitle');
    const todayMetaEl = document.getElementById('scheduleFocusTodayMeta');
    const todayDotEl = document.getElementById('scheduleFocusTodayDot');
    const upcomingTitleEl = document.getElementById('scheduleFocusUpcomingTitle');
    const upcomingMetaEl = document.getElementById('scheduleFocusUpcomingMeta');
    const upcomingDotEl = document.getElementById('scheduleFocusUpcomingDot');

    const { today, upcoming } = getNearestScheduleFocusItems();

    if (today) {
      todayTitleEl.textContent = `${today.title} — ${today.vehicleLabel}`;
      todayMetaEl.textContent = formatScheduleDateTime(today.start);
      todayDotEl.className = 'inline-flex h-2 w-2 rounded-full bg-emerald-500';
    } else {
      todayTitleEl.textContent = 'No scheduled trips today.';
      todayMetaEl.textContent = '';
      todayDotEl.className = 'inline-flex h-2 w-2 rounded-full bg-slate-200';
    }

    if (upcoming) {
      upcomingTitleEl.textContent = `${upcoming.title} — ${upcoming.vehicleLabel}`;
      upcomingMetaEl.textContent = formatScheduleDateTime(upcoming.start);
      upcomingDotEl.className = 'inline-flex h-2 w-2 rounded-full bg-slate-500';
    } else {
      upcomingTitleEl.textContent = 'No upcoming bookings.';
      upcomingMetaEl.textContent = '';
      upcomingDotEl.className = 'inline-flex h-2 w-2 rounded-full bg-slate-200';
    }
  }

  function resetBookingPreviewPanel() {
    if (bookingPreviewForm) bookingPreviewForm.classList.add('hidden');
    if (bookingDetailsWrapper) bookingDetailsWrapper.classList.add('hidden');
    if (scheduleFocusPanel) scheduleFocusPanel.classList.remove('hidden');
    if (bookingPreviewEmptyEl) bookingPreviewEmptyEl.classList.remove('hidden');
    toggleFleetSnapshotVisibility(true);
    if (previewBookingIdEl) previewBookingIdEl.value = '';
    if (previewPurposeEl) previewPurposeEl.value = '';
    if (previewDestinationsEl) previewDestinationsEl.value = '';
    if (previewDateTripEl) previewDateTripEl.value = '';
    if (previewDateRequestedEl) previewDateRequestedEl.value = '';
    if (previewDepartureExpectedEl) previewDepartureExpectedEl.value = '';
    if (previewReturnExpectedEl) previewReturnExpectedEl.value = '';
    if (previewPassengersEl) previewPassengersEl.value = '';
    if (previewVehicleIdEl) previewVehicleIdEl.value = '';
    if (previewDriverIdEl) previewDriverIdEl.value = '';
    if (previewSpecialInstructionsEl) previewSpecialInstructionsEl.value = '';
    if (previewRemarksEl) previewRemarksEl.value = '';
    setBookingPreviewStatus('No selection', 'neutral');
    renderScheduleFocusPanel();
  }

  function populateBookingPreview(booking) {
    if (!booking) return;
    if (scheduleFocusPanel) scheduleFocusPanel.classList.add('hidden');
    if (bookingDetailsWrapper) bookingDetailsWrapper.classList.remove('hidden');
    if (previewBookingIdEl) previewBookingIdEl.value = String(booking.id || '');
    if (previewPurposeEl) previewPurposeEl.value = booking.purpose || '';
    if (previewDestinationsEl) previewDestinationsEl.value = booking.destinations || '';
    if (previewDateTripEl) previewDateTripEl.value = toLocalDateValue(booking.date_trip || booking.start_at || '');
    if (previewDateRequestedEl) previewDateRequestedEl.value = toLocalDateValue(booking.date_requested || '');
    if (previewDepartureExpectedEl) previewDepartureExpectedEl.value = toLocalInputValue(booking.start_at || booking.departure_expected || '');
    if (previewReturnExpectedEl) previewReturnExpectedEl.value = toLocalInputValue(booking.end_at || booking.return_expected || '');
    if (previewPassengersEl) previewPassengersEl.value = booking.passengers || booking.seat_count || '';
    if (previewVehicleIdEl) previewVehicleIdEl.value = booking.vehicle_id || '';
    if (previewDriverIdEl) previewDriverIdEl.value = booking.driver_id || '';
    if (previewSpecialInstructionsEl) previewSpecialInstructionsEl.value = booking.special_instructions || '';
    if (previewRemarksEl) previewRemarksEl.value = booking.remarks || booking.notes || '';

    const serverStatus = String(booking.status || '').toLowerCase();
    const departureExpected = booking.departure_expected || booking.start_at || '';
    const returnExpected = booking.return_expected || booking.end_at || '';
    const computedStatus = serverStatus === 'cancelled' || serverStatus === 'canceled'
      ? 'cancelled'
      : getBookingStatus(departureExpected, returnExpected);

    if (bookingPreviewEmptyEl) bookingPreviewEmptyEl.classList.add('hidden');
    if (bookingPreviewForm) bookingPreviewForm.classList.remove('hidden');
    toggleFleetSnapshotVisibility(false);

    const tone = computedStatus === 'pending' ? 'pending' : computedStatus === 'ongoing' ? 'ongoing' : computedStatus === 'finished' ? 'finished' : computedStatus === 'cancelled' ? 'cancelled' : 'neutral';
    setBookingPreviewStatus(computedStatus.toUpperCase(), tone);
  }

  function loadBookingPreview(id) {
    if (!id) return;
    const url = getBookingUrl + '&id=' + encodeURIComponent(id) + '&t=' + Date.now();
    fetch(url, { method: 'GET', headers: {'X-Requested-With': 'XMLHttpRequest'} })
      .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(data => {
        if (!data || !data.success || !data.booking) {
          showNotification('Unable to load booking preview', 'error');
          return;
        }
        populateBookingPreview(data.booking);
      })
      .catch(err => {
        console.error('Failed to load booking preview:', err);
        showNotification('Failed to load booking preview', 'error');
      });
  }

  function loadBookingReadOnly(id) {
    loadBookingPreview(id);
  }

  if (bookingPreviewForm) {
    bookingPreviewForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const id = previewBookingIdEl && previewBookingIdEl.value ? previewBookingIdEl.value : '';
      if (!id) return;

      const formData = new FormData(bookingPreviewForm);
      formData.set('id', id);
      fetch(updateBookingUrl, {
        method: 'POST',
        body: formData,
        headers: {'X-Requested-With': 'XMLHttpRequest'}
      })
      .then(r => {
        const ct = (r.headers.get('content-type') || '').toLowerCase();
        if (ct.includes('application/json')) return r.json();
        return r.text().then(t => { throw new Error('Server returned non-JSON response: ' + String(t).slice(0,200)); });
      })
      .then(data => {
        if (!data || !data.success) {
          const friendly = friendlyBookingError(data);
          throw new Error(friendly);
        }
        showNotification('Booking updated', 'success');
        loadBookingPreview(id);
        refetchCalendarEvents();
        loadVehicleCards();
      })
      .catch(err => {
        console.error('Failed to update booking from preview:', err);
        showNotification(err.message || 'Failed to update booking', 'error');
      });
    });
  }

  if (previewCancelViewBtn) {
    previewCancelViewBtn.addEventListener('click', function () {
      resetBookingPreviewPanel();
    });
  }

  if (previewCancelBtn) {
    previewCancelBtn.addEventListener('click', function () {
      const id = previewBookingIdEl && previewBookingIdEl.value ? previewBookingIdEl.value : '';
      if (!id) return;
      const body = new URLSearchParams({ id });
      fetch(deleteBookingUrl, {
        method: 'POST',
        body,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/x-www-form-urlencoded'
        }
      })
      .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(data => {
        if (!data || !data.success) {
          throw new Error((data && data.message) ? data.message : 'Unable to cancel booking');
        }
        showNotification('Booking cancelled', 'warning');
        resetBookingPreviewPanel();
        refetchCalendarEvents();
        loadVehicleCards();
      })
      .catch(err => {
        console.error('Failed to cancel booking from preview:', err);
        showNotification(err.message || 'Failed to cancel booking', 'error');
      });
    });
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
    // Modern toast with icon, close button and progress bar
    const container = document.createElement('div');
    container.setAttribute('role','status');
    let toastClass = 'app-toast-info';
    if (type === 'success') toastClass = 'app-toast-success';
    else if (type === 'error') toastClass = 'app-toast-error';
    else if (type === 'warning') toastClass = 'app-toast-warning';
    container.className = `app-toast fixed bottom-6 right-6 z-[9999] ${toastClass}`;

    const icon = document.createElement('div'); icon.className = 'toast-icon';
    if (type === 'success') {
      icon.innerHTML = '<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 10l3 3 9-9" stroke="rgba(255,255,255,0.95)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    } else if (type === 'error') {
      icon.innerHTML = '<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 6l8 8M14 6l-8 8" stroke="rgba(255,255,255,0.95)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    } else if (type === 'warning') {
      icon.innerHTML = '<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 3.5v6" stroke="rgba(255,255,255,0.95)" stroke-width="2" stroke-linecap="round"/><path d="M10 15.2a.8.8 0 100-1.6.8.8 0 000 1.6z" fill="rgba(255,255,255,0.95)"/></svg>';
    } else {
      icon.innerHTML = '<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 3v8" stroke="rgba(255,255,255,0.95)" stroke-width="2" stroke-linecap="round"/><circle cx="10" cy="15" r="1" fill="rgba(255,255,255,0.95)"/></svg>';
    }

    const msg = document.createElement('div'); msg.className = 'toast-message'; msg.textContent = message;
    const closeBtn = document.createElement('button'); closeBtn.className = 'toast-close'; closeBtn.setAttribute('aria-label','Dismiss'); closeBtn.innerHTML = '✕';

    const progressWrap = document.createElement('div'); progressWrap.className = 'app-toast-progress';
    const progressBar = document.createElement('i'); progressBar.style.width = '100%'; progressWrap.appendChild(progressBar);

    container.appendChild(icon); container.appendChild(msg); container.appendChild(closeBtn);
    container.appendChild(progressWrap);
    document.body.appendChild(container);

    let duration = 3500;
    let start = Date.now();
    const tick = () => {
      const elapsed = Date.now() - start;
      const pct = Math.max(0, 1 - elapsed / duration);
      progressBar.style.width = (pct * 100) + '%';
      if (pct <= 0) { container.remove(); clearInterval(iv); }
    };
    const iv = setInterval(tick, 50);
    closeBtn.addEventListener('click', () => { clearInterval(iv); container.remove(); });
    // remove tab focus outline but keep accessible focus
    closeBtn.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') closeBtn.click(); });
    // initial tick
    tick();
  }

  function friendlyBookingError(resp) {
    const code = resp && resp.error_code ? String(resp.error_code) : null;

    if (code === 'vehicle_conflict') {
      if (resp && resp.message && /mmda/i.test(resp.message)) {
        return resp.message;
      }
      return 'This booking can’t be saved: the selected vehicle is already booked for that time period.';
    }
    if (code === 'driver_conflict') return 'This booking can’t be saved: the selected driver is already booked for that time period.';
    if (code === 'past_departure') return 'This booking can’t be saved: the departure time is in the past (including today earlier than now).';

    // Fallback: use server message if present
    if (resp && resp.message) return resp.message;
    return 'Unable to save booking. Please check your details and try again.';
  }

  // Build a short, user-friendly conflict message from detected types
  function buildConflictMessage(types) {
    if (!types || types.length === 0) return 'Scheduling conflict';
    const t = Array.from(new Set(types));
    if (t.length === 1) {
      switch (t[0]) {
        case 'vehicle': return 'Vehicle conflict';
        case 'driver': return 'Driver conflict';
        case 'vehicle_driver': return 'Vehicle+Driver conflict';
        case 'duplicate': return 'Duplicate booking';
        case 'requester_duplicate': return 'Duplicate requester booking';
        case 'start_end_invalid': return 'Invalid times';
        case 'past': return 'Past date';
        case 'buffer': return 'Buffer time conflict';
        case 'extension': return 'Extension conflict';
        case 'recurring': return 'Recurring booking conflict';
        case 'time': return 'Time conflict';
        default: return 'Scheduling conflict';
      }
    }
    // multiple types -> short combined label
    const mapping = { vehicle: 'Vehicle', driver: 'Driver', vehicle_driver: 'Vehicle+Driver', time: 'Time' };
    const label = t.map(x => mapping[x] || x).join(' & ');
    return `${label} conflict`;
  }

  // Briefly highlight vehicle/driver form fields when conflicts occur
  function highlightConflictFields(types) {
    if (!types || types.length === 0) return;
    const elVehicle = document.getElementById('vehicleId');
    const elDriver = document.getElementById('driverId');
    const tset = new Set(types);

    const apply = (el) => {
      if (!el) return;
      const prevBorder = el.style.border;
      const prevBox = el.style.boxShadow;
      el.style.border = '2px solid rgba(220,38,38,1)';
      el.style.boxShadow = '0 0 0 4px rgba(254,226,226,0.6)';
      setTimeout(() => { el.style.border = prevBorder || ''; el.style.boxShadow = prevBox || ''; }, 2500);
    };

    if (tset.has('vehicle') || tset.has('vehicle_driver')) apply(elVehicle);
    if (tset.has('driver') || tset.has('vehicle_driver')) apply(elDriver);
    if (tset.has('requester_duplicate') && elVehicle) apply(elVehicle);
  }

  // Check for local conflicts using events already loaded in the calendar
  // Accepts options: {vehicle_id, driver_id, start, end, excludeId, bufferMinutes, requesterId, status, isResize}
  // Returns an array of conflict objects {type: 'vehicle'|'driver'|'vehicle_driver'|'time'|'duplicate'|'buffer'|'status'|'recurring'|'requester_duplicate', event}
  function checkBookingConflicts(opts) {
    const { vehicle_id, driver_id, start, end, excludeId, bufferMinutes = 15, requesterId = null, status = 'pending', isResize = false } = (opts || {});
    const conflicts = [];
    if (!calendar) return conflicts;
    const s = new Date(start);
    const e = new Date(end);
    if (isNaN(s.getTime()) || isNaN(e.getTime())) return conflicts;

    // simple validations
    const now = new Date();
    if (s >= e) {
      conflicts.push({ type: 'start_end_invalid' });
      return conflicts;
    }
    if (s.getTime() < now.getTime()) {
      conflicts.push({ type: 'past' });
      // continue to find other conflicts as well
    }

    const bufMs = Number(bufferMinutes) * 60000;
    const events = calendar.getEvents();
    for (let i = 0; i < events.length; i++) {
      const ev = events[i];
      // skip the event being edited/moved
      if (excludeId && String(ev.id) === String(excludeId)) continue;

      const evStart = ev.start ? new Date(ev.start) : null;
      const evEnd = ev.end ? new Date(ev.end) : (evStart ? new Date(evStart) : null);
      if (!evStart || !evEnd) continue;

      // consider buffer: expand existing event by buffer on both ends
      const evStartAdj = new Date(evStart.getTime() - bufMs);
      const evEndAdj = new Date(evEnd.getTime() + bufMs);

      // overlap test with buffer: start < evEndAdj && end > evStartAdj
      if (!(s < evEndAdj && e > evStartAdj)) continue;

      // extendedProps may use different keys
      const evVehicle = ev.extendedProps ? (ev.extendedProps.vehicle_id ?? ev.extendedProps.vehicleId ?? null) : null;
      const evDriver = ev.extendedProps ? (ev.extendedProps.driver_id ?? ev.extendedProps.driverId ?? null) : null;
      const evStatus = ev.extendedProps ? (ev.extendedProps.status ?? ev.extendedProps.booking_status ?? null) : null;
      const evRequester = ev.extendedProps ? (ev.extendedProps.requester_id ?? ev.extendedProps.requesterId ?? null) : null;
      const isRecurring = ev.extendedProps && (ev.extendedProps.rrule || ev.extendedProps.recurring || ev.extendedProps.recurrence);

      const vehicleMatch = vehicle_id && evVehicle && String(evVehicle) === String(vehicle_id);
      const driverMatch = driver_id && evDriver && String(evDriver) === String(driver_id);

      // recurring event conflict
      if (isRecurring) {
        conflicts.push({ type: 'recurring', event: ev });
        continue;
      }

      // exact duplicate detection
      if (vehicleMatch && driverMatch && evStart.getTime() === s.getTime() && evEnd.getTime() === e.getTime()) {
        // same vehicle, driver and same times
        // requester check if available
        if (requesterId && evRequester && String(evRequester) === String(requesterId)) {
          conflicts.push({ type: 'requester_duplicate', event: ev });
        } else {
          conflicts.push({ type: 'duplicate', event: ev });
        }
        continue;
      }

      // vehicle + driver both busy
      if (vehicleMatch && driverMatch) {
        conflicts.push({ type: 'vehicle_driver', event: ev });
        continue;
      }

      // vehicle busy
      if (vehicleMatch) {
        conflicts.push({ type: 'vehicle', event: ev });
        continue;
      }

      // driver busy
      if (driverMatch) {
        conflicts.push({ type: 'driver', event: ev });
        continue;
      }

      // Note: do not treat generic time overlap as a conflict when vehicle and driver differ.
      // Only vehicle/driver/combined/duplicate/recurring conflicts are reported.
    }
    return conflicts;
  }

  // ==================== CALENDAR ====================
  let calendar = null;
  if (hasCalendar) {
    calendar = new FullCalendar.Calendar(calendarEl, {
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

    eventsSet: function () {
      renderScheduleFocusPanel();
    },

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

      const statusFromEvent = event.extendedProps && event.extendedProps.status ? String(event.extendedProps.status) : null;
      const departureExpected = event.extendedProps ? (event.extendedProps.departure_expected || event.extendedProps.start_at) : null;
      const returnExpected = event.extendedProps ? (event.extendedProps.return_expected || event.extendedProps.end_at) : null;
      const computedStatus = statusFromEvent || getBookingStatus(departureExpected, returnExpected);
      const tone = computedStatus === 'pending' ? 'pending' : computedStatus === 'ongoing' ? 'ongoing' : computedStatus === 'finished' ? 'finished' : computedStatus === 'cancelled' ? 'cancelled' : 'neutral';
      setBookingPreviewStatus(String(computedStatus).toUpperCase(), tone);
      loadBookingPreview(id);
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

        // Run client-side conflict check before sending update
        try {
          const vehicle_id = ev.extendedProps ? (ev.extendedProps.vehicle_id || ev.extendedProps.vehicleId) : null;
          const driver_id = ev.extendedProps ? (ev.extendedProps.driver_id || ev.extendedProps.driverId) : null;
          const status = ev.extendedProps ? (ev.extendedProps.status || 'pending') : 'pending';
          const bufferMinutes = bookingForm && bookingForm.dataset && bookingForm.dataset.bufferMinutes ? parseInt(bookingForm.dataset.bufferMinutes, 10) : 15;
          const conflicts = checkBookingConflicts({ vehicle_id, driver_id, start: newStart, end: newEnd, excludeId: id, bufferMinutes, status });
          if (conflicts && conflicts.length > 0) {
            const types = Array.from(new Set(conflicts.map(c => c.type)));
            showNotification(buildConflictMessage(types), 'error');
            highlightConflictFields(types);
            info.revert();
            return;
          }
        } catch (err) {
          console.error('Conflict check failed (drop):', err);
        }

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

        // Client-side conflict check before update
        try {
          const vehicle_id = ev.extendedProps ? (ev.extendedProps.vehicle_id || ev.extendedProps.vehicleId) : null;
          const driver_id = ev.extendedProps ? (ev.extendedProps.driver_id || ev.extendedProps.driverId) : null;
          const status = ev.extendedProps ? (ev.extendedProps.status || 'pending') : 'pending';
          const bufferMinutes = bookingForm && bookingForm.dataset && bookingForm.dataset.bufferMinutes ? parseInt(bookingForm.dataset.bufferMinutes, 10) : 15;
          const conflicts = checkBookingConflicts({ vehicle_id, driver_id, start: newStart, end: newEnd, excludeId: id, bufferMinutes, status, isResize: true });
          if (conflicts && conflicts.length > 0) {
            // for resize operations treat conflicts as extension conflicts for clarity
            showNotification(buildConflictMessage(['extension']), 'error');
            // highlight vehicle/driver if applicable (map extension to existing conflict types)
            const types = Array.from(new Set(conflicts.map(c => c.type)));
            highlightConflictFields(types);
            info.revert();
            return;
          }
        } catch (err) {
          console.error('Conflict check failed (resize):', err);
        }

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
  }

  const refetchCalendarEvents = () => {
    if (calendar) calendar.refetchEvents();
  };

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

  if (calendarViewSelect && calendar) {
    calendarViewSelect.addEventListener('change', function () {
      calendar.changeView(this.value);
    });
  }

  refreshBtn.addEventListener('click', () => {
    refetchCalendarEvents();
    loadVehicleCards();
    showNotification('Calendar refreshed', 'info');
  });

  const quickRefreshBtn = document.getElementById('quickRefreshBtn');
  const openDriversQuickBtn = document.getElementById('openDriversQuickBtn');

  if (quickRefreshBtn) {
    quickRefreshBtn.addEventListener('click', () => {
      refetchCalendarEvents();
      loadVehicleCards();
      showNotification('Fleet and calendar refreshed', 'success');
    });
  }

  if (openDriversQuickBtn) {
    openDriversQuickBtn.addEventListener('click', () => {
      if (openDriversModalBtn) {
        setDriversModalFormFromRow(null);
        loadDriversTable();
        openModal(driversModalEl);
      }
    });
  }

  vehicleFilter.addEventListener('change', () => refetchCalendarEvents());
  driverFilter.addEventListener('change', () => refetchCalendarEvents());

  // ==================== INITIALIZATION ====================
  loadVehicleCards();
})();
</script>
    </div>
  </div>
</div>
