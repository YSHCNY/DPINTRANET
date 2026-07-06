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

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
  <div class="max-w-[1500px] mx-auto px-4 py-6">
    <div class="mb-6 overflow-hidden rounded-[22px] border border-slate-200/80 bg-gradient-to-br from-white via-white to-slate-50/40 shadow-[0_10px_30px_-20px_rgba(15,23,42,0.16)]">
      <div class="border-b border-slate-200/60 bg-gradient-to-r from-slate-50/80 to-slate-50/60 px-6 py-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div class="max-w-2xl space-y-1">
            <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-emerald-700">Fleet management</p>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Vehicle bookings calendar</h1>
            <p class="mt-1 text-sm text-slate-600">Schedule vehicles, manage drivers, and review booking history across your fleet.</p>
          </div>
          <div class="flex flex-wrap items-center gap-2">
            <button type="button" id="openVehicleModalBtn" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition duration-200 hover:border-slate-300 hover:shadow-md">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
              <span>Vehicles</span>
            </button>
            <button type="button" id="openDriversModalBtn" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition duration-200 hover:border-slate-300 hover:shadow-md">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 19H9a6 6 0 016-6v0a6 6 0 016 6v0z" />
              </svg>
              <span>Drivers</span>
            </button>
          </div>
        </div>
      </div>
      <div class="p-6 lg:p-6">
        <div class="grid gap-5 xl:grid-cols-[1.3fr_0.7fr]">
          <div class="rounded-[16px] border border-slate-200/60 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-200/60 bg-gradient-to-r from-slate-50/40 to-slate-50/20 px-5 py-4">
              <div class="flex flex-wrap items-center justify-between gap-3">
                <div id="external-events" class="hidden sm:inline-flex items-center gap-2 rounded-full border border-emerald-200/60 bg-emerald-50/40 px-3 py-2 text-sm font-medium text-emerald-700 shadow-sm">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                  </svg>
                  <span>Drag to create booking</span>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                  <div class="flex items-center gap-2 rounded-lg border border-slate-200/60 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm">
                    <label class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500">View:</label>
                    <select id="calendarViewSelect" class="bg-transparent text-sm text-slate-900 font-semibold focus:outline-none cursor-pointer">
                      <option value="dayGridMonth">Month</option>
                      <option value="timeGridWeek">Week</option>
                      <option value="timeGridDay">Day</option>
                    </select>
                  </div>
                  <div class="flex items-center gap-2 rounded-lg border border-slate-200/60 bg-white px-3 py-2 shadow-sm">
                    <label class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500">Vehicle:</label>
                    <select id="vehicleFilter" class="bg-transparent text-sm text-slate-900 font-semibold focus:outline-none cursor-pointer">
                      <option value="">All</option>
                      <?php foreach (($vehicles ?? []) as $v): ?>
                        <option value="<?= (int)$v['id'] ?>"><?= htmlspecialchars($v['vehicle_name'] . ' (' . $v['plate_number'] . ')') ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="flex items-center gap-2 rounded-lg border border-slate-200/60 bg-white px-3 py-2 shadow-sm">
                    <label class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500">Driver:</label>
                    <select id="driverFilter" class="bg-transparent text-sm text-slate-900 font-semibold focus:outline-none cursor-pointer">
                      <option value="">All</option>
                      <?php foreach (($drivers ?? []) as $d): ?>
                        <option value="<?= (int)$d['id'] ?>"><?= htmlspecialchars($d['driver_name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>
            </div>
            <div id="carBookingCalendar" class="min-h-[520px] p-4 sm:p-5"></div>
          </div>

          <aside class="rounded-[16px] border border-slate-200/60 bg-slate-50/80 p-5 shadow-sm">
            <div class="space-y-4">
              <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500">Fleet overview</p>
                <h2 class="mt-1 text-[15px] font-medium text-slate-900">Summary</h2>
              </div>
              <div class="grid grid-cols-2 gap-3">
                <div class="rounded-[12px] border border-slate-200/60 bg-white/80 p-3 shadow-sm">
                  <p class="text-[10px] uppercase tracking-[0.2em] text-slate-500">Total Fleet</p>
                  <p id="totalFleetCount" class="mt-2.5 text-2xl font-semibold text-slate-900"><?= count($vehicles ?? []) ?></p>
                </div>
                <div class="rounded-[12px] border border-slate-200/60 bg-white/80 p-3 shadow-sm">
                  <p class="text-[10px] uppercase tracking-[0.2em] text-slate-500">On Trip</p>
                  <p id="onTripCount" class="mt-2.5 text-2xl font-semibold text-emerald-700">0</p>
                </div>
                <div class="rounded-[12px] border border-slate-200/60 bg-white/80 p-3 shadow-sm">
                  <p class="text-[10px] uppercase tracking-[0.2em] text-slate-500">Ready</p>
                  <p id="readyBookingCount" class="mt-2.5 text-2xl font-semibold text-emerald-700">0</p>
                </div>
                <div class="rounded-[12px] border border-slate-200/60 bg-white/80 p-3 shadow-sm">
                  <p class="text-[10px] uppercase tracking-[0.2em] text-slate-500">Drivers</p>
                  <p id="driverAvailableCount" class="mt-2.5 text-2xl font-semibold text-slate-900">0</p>
                </div>
              </div>
              <div class="pt-2 border-t border-slate-200/60">
                <p id="fleetStatusBadge" class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-emerald-700">
                  <span class="inline-flex h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                  Live availability
                </p>
              </div>
            </div>
          </aside>
        </div>
        <div class="mt-6 space-y-4">
          <div class="space-y-1">
            <h2 class="text-base font-semibold text-slate-900">Fleet vehicles</h2>
            <p class="text-sm text-slate-600">Real-time vehicle status and recent bookings</p>
          </div>
          <div id="vehicleCardsContainer" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4"></div>
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

  <div class="relative  bg-white mx-auto my-6 w-[95vw] max-w-4xl modal-panel">
    <!-- Modal Header -->
    <div class="modal-header card-header">
      <div>
        <h2 class="text-lg font-semibold text-slate-900" id="bookingModalTitle">Create New Booking</h2>

        <p class="text-sm text-slate-500 mt-1">Fill in the details below to schedule a vehicle.</p>
      </div>
      <button type="button" id="closeBookingModalBtn" class="modal-close" aria-label="Close">
        <span class="text-lg">✕</span>
      </button>
    </div>

    <!-- Modal Content -->
    <form id="bookingForm" class="modal-body" method="POST" action="">
      <input type="hidden" name="id" id="bookingId" value="">

      <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
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
              <option value="<?= (int)$v['id'] ?>"><?= htmlspecialchars($v['vehicle_name'] . ' (' . $v['plate_number'] . ')') ?></option>
            <?php endforeach; ?>
          </select>
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
      <div class="modal-footer">
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
  <div id="vehicleModal" class="fixed inset-0 z-50 hidden " role="dialog" aria-modal="true" aria-labelledby="vehicleModalTitle">
  <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" id="vehicleModalBackdrop"></div>
  <div class="relative  bg-white mx-auto my-6 w-[95vw] max-w-4xl modal-panel">
    <div class="modal-header card-header">
      <div>
        <h2 id="vehicleModalTitle" class="text-lg font-semibold text-slate-900">Manage Vehicles</h2>
        <p class="text-sm text-slate-500 mt-1">Add new vehicles or modify existing ones</p>
      </div>
      <button type="button" id="closeVehicleModalBtn" class="modal-close" aria-label="Close">
        <span class="text-lg">✕</span>
      </button>
    </div>

    <div class="modal-body">
      <!-- Add Vehicle Form -->
      <div class="vehicle-form-card rounded-lg border border-surface-2 p-4 bg-white mb-4">
        <form id="vehicleForm" class="space-y-4">
          <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold text-slate-900">Add / Edit Vehicle</h3>
            <p class="text-xs text-slate-500">Keep fields concise — plate number and capacity are required.</p>
          </div>
        <input type="hidden" name="id" id="vehicleIdInput" value="">

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 items-end">
          <div class="sm:col-span-2">
            <label class="text-xs font-semibold text-slate-700 block mb-2">Plate Number <span class="text-red-500">*</span></label>
            <input type="text" name="plate_number" id="plateNumber" placeholder="e.g., ABC-1234" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none transition" required />
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-700 block mb-2">Capacity <span class="text-red-500">*</span></label>
            <input type="number" name="capacity" id="capacity" min="1" placeholder="Seats" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none transition" required />
          </div>

          <div class="sm:col-span-2">
            <label class="text-xs font-semibold text-slate-700 block mb-2">Vehicle Name <span class="text-red-500">*</span></label>
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
        <div class="px-5 py-3 flex items-center justify-between gap-3 card-header">
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
  <div id="driversModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="driversModalTitle">
  <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" id="driversModalBackdrop"></div>
  <div class="relative  bg-white mx-auto my-6 w-[95vw] max-w-3xl modal-panel">
    <div class="modal-header card-header">
      <div>
        <h2 id="driversModalTitle" class="text-lg font-semibold text-slate-900">Manage Drivers</h2>
        <p class="text-sm text-slate-500 mt-1">Add new drivers or modify existing ones</p>
      </div>
        <button type="button" id="closeDriversModalBtn" class="modal-close" aria-label="Close">
          <span class="text-lg">✕</span>
        </button>
    </div>

    <div class="modal-body">
      <!-- Add Driver Form -->
      <div class="driver-form-card rounded-lg border border-surface-2 p-4 bg-white mb-4">
        <form id="driverForm" class="space-y-4">
          <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold text-slate-900">Add / Edit Driver</h3>
            <p class="text-xs text-slate-500">Provide the driver's full name and status.</p>
          </div>
          <input type="hidden" name="id" id="driverModalId">

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 items-end">
            <div class="sm:col-span-2">
              <label class="text-xs font-semibold text-slate-700 block mb-2">Driver Name <span class="text-red-500">*</span></label>
              <input type="text" name="driver_name" id="driverNameInput" placeholder="e.g., John Doe" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none transition" required />
            </div>

            <div>
              <label class="text-xs font-semibold text-slate-700 block mb-2">Status <span class="text-red-500">*</span></label>
              <select name="status" id="driverStatusInput" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none transition" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>

            <div class="sm:col-span-3 flex items-center gap-2 justify-end">
              <button type="button" id="resetDriverFormBtn" class="btn-ghost">Reset</button>
              <button type="submit" class="btn-primary">Save Driver</button>
            </div>
          </div>
        </form>
      </div>

      <!-- Drivers Table -->
      <div class="rounded-lg border border-slate-200 overflow-hidden drivers-table">
        <div class="px-5 py-3 flex items-center justify-between gap-3 card-header">
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
        <div class="max-h-[320px] overflow-auto">
          <table class="w-full text-left text-sm">
            <thead class="sticky top-0 bg-surface-1 border-b border-slate-200">
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
</style>


  <!-- Vehicle Details Modal (shows history and clickable bookings) -->
  <div id="vehicleDetailsModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="vehicleDetailsModalTitle">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" id="vehicleDetailsModalBackdrop"></div>
    <div class="relative  bg-white mx-auto my-6 w-[95vw] max-w-3xl modal-panel">
      <div class="modal-header card-header">
        <div>
          <h2 id="vehicleDetailsModalTitle" class="text-lg font-semibold text-slate-900">Vehicle Details</h2>
          <p class="text-sm text-slate-500 mt-1">Recent bookings — click an item to view full booking details</p>
        </div>
        <button type="button" id="closeVehicleDetailsModalBtn" class="modal-close" aria-label="Close">
          <span class="text-lg">✕</span>
        </button>
      </div>

      <div class="modal-body" id="vehicleDetailsContent">
        <div class="text-sm text-slate-500">Loading...</div>
      </div>
    </div>
  </div>

<script>
(function () {
  'use strict';

  const calendarEl = document.getElementById('carBookingCalendar');
  const hasCalendar = Boolean(calendarEl);

  // ==================== CONSTANTS ====================
  // Force the server-defined BASE_URL so AJAX endpoints resolve to the app entrypoint.
  const baseUrl = <?= json_encode(defined('BASE_URL') ? rtrim(BASE_URL, '/') . '/' : '/') ?>;

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
  const totalFleetCountEl = document.getElementById('totalFleetCount');
  const onTripCountEl = document.getElementById('onTripCount');
  const readyBookingCountEl = document.getElementById('readyBookingCount');
  const driverAvailableCountEl = document.getElementById('driverAvailableCount');
  const fleetStatusBadgeEl = document.getElementById('fleetStatusBadge');

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

    const tripDate = document.getElementById('dateTrip') ? document.getElementById('dateTrip').value : formData.get('date_trip');
    const departureExpected = document.getElementById('departureExpected') ? document.getElementById('departureExpected').value : formData.get('departure_expected');
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
      const vehicle_id = document.getElementById('vehicleId') ? document.getElementById('vehicleId').value : formData.get('vehicle_id');
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
        ? `<span class="driver-badge">Active</span>`
        : `<span class="driver-badge inactive">Inactive</span>`;

      tr.innerHTML = `
        <td class="px-5 py-3">
          <div class="font-medium text-slate-800">${escapeHtml(row.driver_name)}</div>
        </td>
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
          fd.append('id', id);
          fd.append('driver_name', row.driver_name || '');
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

    // request all vehicles (we'll show inactive ones as greyed/flagged)
    fetch(vehicleListAllUrl + '&t=' + Date.now(), {
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
      const vehicles = (data && Array.isArray(data.vehicles) ? data.vehicles : []);

      if (vehicles.length === 0) {
        vehicleCardsContainer.innerHTML = '<div class="col-span-full text-center py-8 text-slate-500">No vehicles available</div>';
        refreshFleetSnapshot(vehicles);
        return;
      }

      // display all vehicles; inactive vehicles will be shown but visually de-emphasized
      vehicles.forEach(vehicle => {
        const card = createVehicleCard(vehicle);
        vehicleCardsContainer.appendChild(card);
      });
      refreshFleetSnapshot(vehicles);
    })
    .catch(err => {
      console.error('Error loading vehicle cards:', err);
      vehicleCardsContainer.innerHTML = '<div class="col-span-full text-center py-8 text-rose-500">Failed to load vehicles</div>';
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
    const availableDrivers = Math.max(0, activeDrivers.length - ongoingDriverIds.size);

    if (totalFleetCountEl) totalFleetCountEl.textContent = String(vehicles.length);
    if (onTripCountEl) onTripCountEl.textContent = String(onTrip);
    if (readyBookingCountEl) readyBookingCountEl.textContent = String(readyBooking);
    if (driverAvailableCountEl) driverAvailableCountEl.textContent = String(availableDrivers);

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
        updateFleetSnapshot(vehData, driverData, bookingEvents);
      })
      .catch(err => {
        console.error('Failed to refresh fleet snapshot:', err);
        updateFleetSnapshot(vehicles, [], []);
      });
  }

  function createVehicleCard(vehicle) {
    const card = document.createElement('div');
    card.className = 'rounded-lg border border-slate-200 bg-white shadow-sm hover:shadow-md transition overflow-hidden hover:border-slate-300';
    // Map status to color/shape following human-intuitive mapping
    const rawStatus = (vehicle.status || 'info').toString().toLowerCase();
    let badgeClasses = 'inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold';
    let badgeLabel = rawStatus.toUpperCase();
    if (rawStatus === 'active' || rawStatus === 'finished') {
      badgeClasses += ' text-emerald-700 bg-emerald-50 border border-emerald-100';
    } else if (rawStatus === 'cancelled' || rawStatus === 'canceled' || rawStatus === 'disabled') {
      badgeClasses += ' text-rose-700 bg-rose-50 border border-rose-100';
    } else if (rawStatus === 'warning' || rawStatus === 'pending') {
      badgeClasses += ' text-amber-800 bg-amber-100 border border-amber-200';
    } else if (rawStatus === 'info' || rawStatus === 'overall' || rawStatus === 'available') {
      badgeClasses += ' text-sky-700 bg-sky-50 border border-sky-100';
      badgeLabel = 'INFO';
    } else {
      // default to info/blue
      badgeClasses += ' text-sky-700 bg-sky-50 border border-sky-100';
    }

    const badgeHtml = `<span class="${badgeClasses}">${escapeHtml(badgeLabel)}</span>`;

    // Prefer full URL returned by server (image_url). Fall back to building URL from baseUrl.
    const filename = vehicle.image_filename || vehicle.image || null;
    const imgSrc = vehicle.image_url ? vehicle.image_url : (filename ? (baseUrl + 'uploads/vehicle/' + filename) : (baseUrl + 'app/assets/img/vehicle-placeholder.jpg'));

    const isDisabled = rawStatus !== 'active';
    if (isDisabled) {
      card.className += ' opacity-60 filter grayscale';
    }

    card.innerHTML = `
      <div class="relative h-36 bg-slate-100">
        <img src="${escapeHtml(imgSrc)}" alt="${escapeHtml(vehicle.vehicle_name)}" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
        <div class="absolute left-4 bottom-3 text-white">
          <h3 class="text-sm font-semibold">${escapeHtml(vehicle.vehicle_name)}</h3>
          <p class="text-xs opacity-80">${escapeHtml(vehicle.plate_number)}</p>
        </div>
        <div class="absolute right-3 top-3">
          ${badgeHtml}
        </div>
      </div>

      <div class="px-4 py-4">
        <div class="flex items-center justify-between mb-4">
          <div>
            <span class="text-xs font-medium text-slate-600">Capacity</span>
            <div class="text-sm font-semibold text-slate-800">${vehicle.capacity || '—'} seats</div>
          </div>
          <div class="text-right">
            <span class="text-xs text-slate-500">Plate</span>
            <div class="text-sm font-medium text-slate-900">${escapeHtml(vehicle.plate_number || '—')}</div>
          </div>
        </div>

        <div class="mb-4" id="loading-${vehicle.id}">
          <p class="text-xs text-slate-500 text-center py-2">Loading bookings...</p>
        </div>
        <div id="bookings-${vehicle.id}" class="space-y-2 hidden"></div>

        <button type="button" class="w-full mt-4 rounded-2xl border border-slate-200 bg-gradient-to-r from-white via-slate-50 to-white px-3 py-2 text-sm font-semibold text-slate-700 hover:from-slate-50 hover:to-white transition view-vehicle-btn" data-vehicle-id="${vehicle.id}">
          View Details
        </button>
      </div>
    `;

    if (isDisabled) {
      const flag = document.createElement('div');
      flag.className = 'absolute right-3 top-3';
      flag.innerHTML = '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-100">Disabled</span>';
      card.appendChild(flag);
    }

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

          const computedStatus = getBookingStatus(it.departure_expected || it.start_at, it.return_expected || it.end_at);
          const serverStatus = (it.status || it.booking_status || '').toLowerCase();
          let badgeHtml = '';
          if (serverStatus === 'cancelled' || serverStatus === 'canceled') {
            badgeHtml = '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold text-rose-700 bg-rose-50">CANCELLED</span>';
          } else if (computedStatus === 'finished') {
            badgeHtml = '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold text-emerald-700 bg-emerald-50">FINISHED</span>';
          }

          item.innerHTML = `<div class="flex items-center justify-between"><div class="flex-1"><div class="font-medium">${escapeHtml(it.purpose || 'Trip')}</div><div class="text-xs text-slate-500">${escapeHtml(formatLocalDisplay(it.departure_expected || it.start_at || ''))} → ${escapeHtml(formatLocalDisplay(it.return_expected || it.end_at || ''))}</div></div><div class="flex flex-col items-end gap-1"><div class="text-xs text-slate-400">View</div>${badgeHtml}</div></div>`;
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
        document.getElementById('dateTrip').value = toLocalDateValue(b.date_trip || b.start_at || '');
        document.getElementById('dateRequested').value = toLocalDateValue(b.date_requested || '');
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

    if (code === 'vehicle_conflict') return 'This booking can’t be saved: the selected vehicle is already booked for that time period.';
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
        case 'status': return 'Status conflict';
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

      // pending/approved conflict: if existing booking is 'approved' and new booking is pending, it's a stronger conflict
      if (evStatus && status && evStatus !== status) {
        conflicts.push({ type: 'status', event: ev });
        continue;
      }

      // Note: do not treat generic time overlap as a conflict when vehicle and driver differ.
      // Only vehicle/driver/combined/status/duplicate/recurring conflicts are reported.
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
          document.getElementById('dateTrip').value = toLocalDateValue(b.date_trip || b.start_at || '');
          document.getElementById('dateRequested').value = toLocalDateValue(b.date_requested || '');
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
