<?php
// Room Bookings Calendar
// Requires: RoomBookingsController@calendar
$roomCount = is_array($rooms ?? []) ? count($rooms) : 0;
$roomActiveCount = 0;
$roomInactiveCount = 0;
if (!empty($rooms) && is_array($rooms)) {
    foreach ($rooms as $room) {
        if (isset($room['status']) && $room['status'] === 'active') {
            $roomActiveCount++;
        } else {
            $roomInactiveCount++;
        }
    }
}
?>
<!-- HEADER -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
  <div class="max-w-[1500px] mx-auto px-4 py-6">
    <div class="mb-6 overflow-hidden rounded-[22px] border border-slate-200/80 bg-gradient-to-br from-white via-white to-slate-50/40 shadow-[0_10px_30px_-20px_rgba(15,23,42,0.16)]">
      <div class="border-b border-slate-200/60 bg-gradient-to-r from-slate-50/80 to-slate-50/60 px-6 py-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div class="max-w-2xl space-y-1">
            <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-violet-700">Room scheduling</p>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Meeting rooms calendar</h1>
            <p class="mt-1 text-sm text-slate-600">Schedule room resources with real-time availability, capacity checks, and conflict prevention.</p>
          </div>
          <div class="flex flex-wrap items-center gap-2">
            <button type="button" id="openRoomBookingModalBtn" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition duration-200 hover:border-slate-300 hover:shadow-md">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span>New booking</span>
            </button>
            <button type="button" id="openRoomCreateModalBtn" class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition duration-200 hover:border-slate-300 hover:shadow-md">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              <span>Manage rooms</span>
            </button>
          </div>
        </div>
      </div>
      <div class="p-6 lg:p-6">
        <div class="grid gap-5 xl:grid-cols-[1.3fr_0.7fr]">
          <div class="rounded-[16px] border border-slate-200/60 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-200/60 bg-gradient-to-r from-slate-50/40 to-slate-50/20 px-5 py-4">
              <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2 rounded-lg border border-slate-200/60 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm">
                  <label class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500">View:</label>
                  <select id="calendarViewSelect" class="bg-transparent text-sm text-slate-900 font-semibold focus:outline-none cursor-pointer">
                    <option value="dayGridMonth">Month</option>
                    <option value="timeGridWeek">Week</option>
                    <option value="timeGridDay">Day</option>
                  </select>
                </div>
                <div class="flex items-center gap-2 rounded-lg border border-slate-200/60 bg-white px-3 py-2 shadow-sm">
                  <label class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500">Room:</label>
                  <select id="roomFilter" class="bg-transparent text-sm text-slate-900 font-semibold focus:outline-none cursor-pointer">
                    <option value="">All</option>
                    <?php foreach (($rooms ?? []) as $room): ?>
                      <option value="<?= (int)$room['id'] ?>"><?= htmlspecialchars($room['room_name'] . ' (' . $room['room_code'] . ')') ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            <div id="roomBookingCalendar" class="min-h-[520px] p-4 sm:p-5"></div>
          </div>

          <aside class="rounded-[16px] border border-slate-200/60 bg-slate-50/80 p-5 shadow-sm">
            <div class="space-y-4">
              <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500">Room resources</p>
                <h2 class="mt-1 text-[15px] font-medium text-slate-900">Summary</h2>
              </div>
              <div class="grid grid-cols-2 gap-3">
                <div class="rounded-[12px] border border-slate-200/60 bg-white/80 p-3 shadow-sm">
                  <p class="text-[10px] uppercase tracking-[0.2em] text-slate-500">Total Rooms</p>
                  <p id="roomSummaryTotal" class="mt-2.5 text-2xl font-semibold text-slate-900"><?= $roomCount ?></p>
                </div>
                <div class="rounded-[12px] border border-slate-200/60 bg-white/80 p-3 shadow-sm">
                  <p class="text-[10px] uppercase tracking-[0.2em] text-slate-500">Available</p>
                  <p id="roomSummaryAvailable" class="mt-2.5 text-2xl font-semibold text-emerald-700">0</p>
                </div>
                <div class="rounded-[12px] border border-slate-200/60 bg-white/80 p-3 shadow-sm">
                  <p class="text-[10px] uppercase tracking-[0.2em] text-slate-500">In use</p>
                  <p id="roomSummaryOccupied" class="mt-2.5 text-2xl font-semibold text-violet-700">0</p>
                </div>
                <div class="rounded-[12px] border border-slate-200/60 bg-white/80 p-3 shadow-sm">
                  <p class="text-[10px] uppercase tracking-[0.2em] text-slate-500">Upcoming</p>
                  <p id="roomSummaryBookings" class="mt-2.5 text-2xl font-semibold text-slate-900">0</p>
                </div>
              </div>
              <div class="pt-2 border-t border-slate-200/60">
                <p id="roomInventoryStatusBadge" class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-emerald-700">
                  <span class="inline-flex h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                  Open for booking
                </p>
              </div>
            </div>
          </aside>
        </div>

        <div class="mt-6 space-y-4">
          <div class="space-y-1">
            <h2 class="text-base font-semibold text-slate-900">Meeting rooms</h2>
            <p class="text-sm text-slate-600">Real-time room status and upcoming reservations</p>
          </div>
          <div id="roomCardsContainer" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div id="roomBookingModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="roomBookingModalTitle">
  <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" id="roomBookingModalBackdrop"></div>
  <div class="relative mx-auto my-6 w-[95vw] max-w-4xl modal-panel">
    <div class="modal-header card-header">
      <div>
        <h2 id="roomBookingModalTitle" class="text-lg font-semibold text-slate-900">Room Booking Schedule</h2>
        <p class="text-sm text-slate-500 mt-1">Create or edit a room booking schedule with capacity and conflict checks.</p>
      </div>
      <button type="button" id="closeRoomBookingModalBtn" class="modal-close" aria-label="Close"><span class="text-lg">✕</span></button>
    </div>
    <form id="roomBookingForm" class="modal-body" method="POST" action="">
      <input type="hidden" name="id" id="roomBookingId" value="">
      <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
          <label class="text-sm font-semibold text-slate-700 block mb-2">Booking Date <span class="text-red-500">*</span></label>
          <input type="date" name="date_trip" id="roomDateTrip" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none transition" required />
          <p class="text-xs text-slate-500 mt-1">Choose the booking date.</p>
        </div>
        <div>
          <label class="text-sm font-semibold text-slate-700 block mb-2">Requested Date <span class="text-red-500">*</span></label>
          <input type="date" name="date_requested" id="roomDateRequested" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none transition" required />
          <p class="text-xs text-slate-500 mt-1">When was this room requested?</p>
        </div>
        <div class="sm:col-span-2">
          <label class="text-sm font-semibold text-slate-700 block mb-2">Booking Purpose <span class="text-red-500">*</span></label>
          <input type="text" name="purpose" id="roomPurpose" placeholder="Meeting, workshop, training" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none transition" required />
        </div>
        <div>
          <label class="text-sm font-semibold text-slate-700 block mb-2">Attendees <span class="text-red-500">*</span></label>
          <input type="number" name="attendees" id="roomAttendees" min="1" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none transition" required />
          <p class="text-xs text-slate-500 mt-1">Number of people expected in the room.</p>
        </div>
        <div></div>
        <div>
          <label class="text-sm font-semibold text-slate-700 block mb-2">Start Date & Time <span class="text-red-500">*</span></label>
          <input type="datetime-local" name="departure_expected" id="roomDepartureExpected" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none transition" required />
          <p class="text-xs text-slate-500 mt-1">Select when the booking begins.</p>
        </div>
        <div>
          <label class="text-sm font-semibold text-slate-700 block mb-2">End Date & Time <span class="text-red-500">*</span></label>
          <input type="datetime-local" name="return_expected" id="roomReturnExpected" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none transition" required />
          <p class="text-xs text-slate-500 mt-1">Select when the booking ends.</p>
        </div>
        <div>
          <label class="text-sm font-semibold text-slate-700 block mb-2">Room <span class="text-red-500">*</span></label>
          <select name="room_id" id="roomId" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none transition" required>
            <option value="">-- Select a room --</option>
            <?php foreach (($rooms ?? []) as $room): ?>
              <option value="<?= (int)$room['id'] ?>"><?= htmlspecialchars($room['room_name'] . ' (' . $room['room_code'] . ') / ' . $room['capacity'] . ' pax') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="sm:col-span-2">
          <label class="text-sm font-semibold text-slate-700 block mb-2">Special Instructions</label>
          <textarea name="special_instructions" id="roomSpecialInstructions" rows="3" placeholder="Any equipment, AV needs, or setup requests" class="w-full resize-none rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none transition"></textarea>
        </div>
        <div class="sm:col-span-2">
          <label class="text-sm font-semibold text-slate-700 block mb-2">Remarks</label>
          <textarea name="remarks" id="roomRemarks" rows="3" placeholder="Additional notes" class="w-full resize-none rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none transition"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" id="cancelRoomBookingModalBtn" class="btn-ghost">Cancel</button>
        <button type="button" id="deleteRoomBookingBtn" class="btn-danger hidden">Delete</button>
        <button type="submit" id="saveRoomBookingBtn" class="btn-primary">Save Schedule</button>
      </div>
    </form>
  </div>
</div>

<div id="roomCreateModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="roomCreateModalTitle">
  <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" id="roomCreateModalBackdrop"></div>
  <div class="relative mx-auto my-6 w-[95vw] max-w-2xl modal-panel">
    <div class="modal-header card-header">
      <div>
        <h2 id="roomCreateModalTitle" class="text-lg font-semibold text-slate-900">Add Room Resource</h2>
        <p class="text-sm text-slate-500 mt-1">Create a room that can be reserved in the booking schedule.</p>
      </div>
      <button type="button" id="closeRoomCreateModalBtn" class="modal-close" aria-label="Close"><span class="text-lg">✕</span></button>
    </div>
    <form id="roomCreateForm" class="modal-body" method="POST" action="">
      <input type="hidden" name="id" id="roomEditId" value="">
      <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
          <label class="text-sm font-semibold text-slate-700 block mb-2">Room Name <span class="text-red-500">*</span></label>
          <input type="text" name="room_name" id="roomName" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none transition" required />
        </div>
        <div>
          <label class="text-sm font-semibold text-slate-700 block mb-2">Room Code <span class="text-red-500">*</span></label>
          <input type="text" name="room_code" id="roomCode" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none transition" required />
        </div>
        <div>
          <label class="text-sm font-semibold text-slate-700 block mb-2">Capacity <span class="text-red-500">*</span></label>
          <input type="number" name="capacity" id="roomCapacity" min="1" value="1" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none transition" required />
        </div>
        <div>
          <label class="text-sm font-semibold text-slate-700 block mb-2">Status</label>
          <select name="status" id="roomStatus" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none transition">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-ghost" id="cancelRoomCreateModalBtn">Cancel</button>
        <button type="submit" class="btn-primary" id="roomCreateSubmitBtn">Create Room</button>
      </div>
    </form>
  </div>
</div>

<div id="roomHistoryModal" class="fixed inset-0 z-[60] hidden" role="dialog" aria-modal="true" aria-labelledby="roomHistoryModalTitle">
  <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" id="roomHistoryModalBackdrop"></div>
  <div class="relative mx-auto my-6 w-[95vw] max-w-3xl max-h-[85vh] overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-2xl">
    <div class="border-b border-slate-200 px-5 py-4">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500" id="roomHistoryModalSubtitle">Room schedule</p>
          <h2 id="roomHistoryModalTitle" class="mt-1 text-lg font-semibold text-slate-900">Room history</h2>
        </div>
        <div class="flex items-center gap-2">
          <button type="button" id="editRoomDetailsBtn" class="btn-ghost">Edit room</button>
          <button type="button" id="deleteRoomDetailsBtn" class="btn-danger">Delete room</button>
          <button type="button" id="closeRoomHistoryModalBtn" class="modal-close" aria-label="Close"><span class="text-lg">✕</span></button>
        </div>
      </div>
      <div class="mt-3 flex flex-wrap gap-2">
        <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700"><span class="h-2 w-2 rounded-full bg-amber-500"></span>Upcoming</span>
        <span class="inline-flex items-center gap-2 rounded-full bg-sky-50 px-2.5 py-1 text-[11px] font-semibold text-sky-700"><span class="h-2 w-2 rounded-full bg-sky-500"></span>Ongoing</span>
        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>Finished meeting</span>
      </div>
    </div>
    <div class="max-h-[70vh] overflow-y-auto px-5 py-4">
      <div id="roomHistoryModalContent" class="space-y-4"></div>
    </div>
  </div>
</div>

<div id="roomBookingDetailModal" class="fixed inset-0 z-[70] hidden" role="dialog" aria-modal="true" aria-labelledby="roomBookingDetailModalTitle">
  <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" id="roomBookingDetailModalBackdrop"></div>
  <div class="relative mx-auto my-6 w-[95vw] max-w-2xl rounded-[24px] border border-slate-200 bg-white shadow-2xl">
    <div class="border-b border-slate-200 px-5 py-4">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500" id="roomBookingDetailModalSubtitle">Booking details</p>
          <h2 id="roomBookingDetailModalTitle" class="mt-1 text-lg font-semibold text-slate-900">Meeting details</h2>
        </div>
        <button type="button" id="closeRoomBookingDetailModalBtn" class="modal-close" aria-label="Close"><span class="text-lg">✕</span></button>
      </div>
      <div class="mt-3" id="roomBookingDetailStatusWrap"></div>
    </div>
    <div class="px-5 py-4">
      <div id="roomBookingDetailContent" class="space-y-4"></div>
      <div class="mt-5 flex flex-wrap justify-end gap-2 border-t border-slate-200 pt-4">
        <button type="button" id="closeRoomBookingDetailActionBtn" class="btn-ghost">Close</button>
        <button type="button" id="editRoomBookingDetailBtn" class="btn-primary">Edit</button>
        <button type="button" id="deleteRoomBookingDetailBtn" class="btn-danger">Delete</button>
      </div>
    </div>
  </div>
</div>

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

  /* Modal styling */
  .modal-panel { border-radius:16px; overflow:hidden; background:#ffffff; box-shadow: 0 20px 60px rgba(2,6,23,0.12); }
  .modal-header { padding:20px; border-bottom:1px solid #eef2f7; display:flex; align-items:flex-start; justify-content:space-between; gap:16px; }
  .modal-body { padding:20px; max-height: calc(100vh - 240px); overflow:auto; }
  .modal-footer { padding:18px 20px; border-top:1px solid #f1f5f9; display:flex; gap:10px; justify-content:flex-end; align-items:center; }
  .modal-close { display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:10px; background:transparent; color:#64748b; border:none; cursor:pointer; transition: all 0.2s ease; }
  .modal-close:hover { background:rgba(15,23,42,0.06); color:#0f172a; }
  
  /* Button styling */
  .btn-primary { background: #059669; color: #fff; padding:10px 16px; border-radius:10px; border:none; font-weight:600; box-shadow: 0 4px 12px rgba(5,150,105,0.15); display:inline-flex; gap:8px; align-items:center; transition: all 0.2s ease; cursor:pointer; }
  .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 16px rgba(5,150,105,0.2); background: #047857; }
  .btn-ghost { background:transparent; border-radius:10px; padding:8px 12px; border:1px solid transparent; color:var(--muted); cursor:pointer; transition: all 0.2s ease; }
  .btn-ghost:hover { background:rgba(15,23,42,0.04); color:var(--text); }
  .btn-danger { background:#fff; border:1px solid rgba(220,38,38,0.12); color:#dc2626; border-radius:10px; padding:8px 12px; cursor:pointer; transition: all 0.2s ease; }
  .btn-danger:hover { background:rgba(220,38,38,0.05); border-color:rgba(220,38,38,0.2); }

  /* Toast notifications */
  .app-toast { position: fixed; bottom: 24px; right: 24px; z-index: 99999; display:flex; gap:12px; align-items:center; min-width:220px; max-width:520px; padding:14px 16px; border-radius:14px; color:#0f172a; box-shadow:0 20px 50px rgba(15,23,42,0.12); background:#ffffff; border:1px solid rgba(148,163,184,0.18); }
  .app-toast .toast-icon { width:24px; height:24px; display:grid; place-items:center; flex:0 0 24px; font-weight:700; }
  .app-toast .toast-message { flex:1; font-size:13px; line-height:1.35; }
  .app-toast .toast-close { margin-left:12px; background:transparent; border:none; color:#64748b; cursor:pointer; padding:6px; border-radius:10px; font-weight:700; }
  .app-toast-success { border-left:4px solid #16a34a; background:linear-gradient(90deg, rgba(22,163,74,0.1), #ffffff); }
  .app-toast-error { border-left:4px solid #dc2626; background:linear-gradient(90deg, rgba(220,38,38,0.1), #ffffff); }
  .app-toast-info { border-left:4px solid #2563eb; background:linear-gradient(90deg, rgba(37,99,235,0.1), #ffffff); }
  .app-toast-warning { border-left:4px solid #f59e0b; background:linear-gradient(90deg, rgba(245,158,11,0.1), #ffffff); }
  .app-toast-progress { height:4px; border-radius:999px; overflow:hidden; margin-top:10px; }
  .app-toast-progress > i { display:block; width:100%; height:100%; transform-origin:left center; animation:toastProgress 4s linear forwards; }
  .app-toast-success .app-toast-progress > i { background:#16a34a; }
  .app-toast-error .app-toast-progress > i { background:#dc2626; }
  .app-toast-info .app-toast-progress > i { background:#2563eb; }
  .app-toast-warning .app-toast-progress > i { background:#f59e0b; }
  @keyframes toastProgress { from { transform: scaleX(1); } to { transform: scaleX(0); } }
</style>

<script>
(function () {
  'use strict';

  const roomBookingModal = document.getElementById('roomBookingModal');
  const roomBookingForm = document.getElementById('roomBookingForm');
  const roomBookingId = document.getElementById('roomBookingId');
  const roomBookingCalendar = document.getElementById('roomBookingCalendar');
  const roomFilter = document.getElementById('roomFilter');
  const calendarViewSelect = document.getElementById('calendarViewSelect');
  const openRoomBookingModalBtn = document.getElementById('openRoomBookingModalBtn');
  const closeRoomBookingModalBtn = document.getElementById('closeRoomBookingModalBtn');
  const cancelRoomBookingModalBtn = document.getElementById('cancelRoomBookingModalBtn');
  const roomBookingModalBackdrop = document.getElementById('roomBookingModalBackdrop');
  const deleteRoomBookingBtn = document.getElementById('deleteRoomBookingBtn');
  const refreshRoomsBtn = document.getElementById('refreshRoomsBtn');
  const roomCardsContainer = document.getElementById('roomCardsContainer');
  const roomSummaryTotal = document.getElementById('roomSummaryTotal');
  const roomSummaryAvailable = document.getElementById('roomSummaryAvailable');
  const roomSummaryOccupied = document.getElementById('roomSummaryOccupied');
  const roomSummaryBookings = document.getElementById('roomSummaryBookings');
  const roomInventoryStatusBadge = document.getElementById('roomInventoryStatusBadge');
  const roomHistoryModal = document.getElementById('roomHistoryModal');
  const roomHistoryModalBackdrop = document.getElementById('roomHistoryModalBackdrop');
  const closeRoomHistoryModalBtn = document.getElementById('closeRoomHistoryModalBtn');
  const roomHistoryModalContent = document.getElementById('roomHistoryModalContent');
  const roomHistoryModalTitle = document.getElementById('roomHistoryModalTitle');
  const roomHistoryModalSubtitle = document.getElementById('roomHistoryModalSubtitle');
  const editRoomDetailsBtn = document.getElementById('editRoomDetailsBtn');
  const deleteRoomDetailsBtn = document.getElementById('deleteRoomDetailsBtn');
  const roomBookingDetailModal = document.getElementById('roomBookingDetailModal');
  const roomBookingDetailModalBackdrop = document.getElementById('roomBookingDetailModalBackdrop');
  const closeRoomBookingDetailModalBtn = document.getElementById('closeRoomBookingDetailModalBtn');
  const closeRoomBookingDetailActionBtn = document.getElementById('closeRoomBookingDetailActionBtn');
  const editRoomBookingDetailBtn = document.getElementById('editRoomBookingDetailBtn');
  const deleteRoomBookingDetailBtn = document.getElementById('deleteRoomBookingDetailBtn');
  const roomBookingDetailContent = document.getElementById('roomBookingDetailContent');
  const roomBookingDetailStatusWrap = document.getElementById('roomBookingDetailStatusWrap');
  const roomBookingDetailModalTitle = document.getElementById('roomBookingDetailModalTitle');
  const roomBookingDetailModalSubtitle = document.getElementById('roomBookingDetailModalSubtitle');

  const roomData = <?= json_encode($rooms ?? []) ?>;

  // Use server-side BASE_URL to make controller AJAX paths reliable.
  const baseUrl = <?= json_encode(rtrim(BASE_URL, '/') . '/') ?>;
  const listUrl = 'index.php?controller=RoomBookings&action=list';
  const createBookingUrl = 'index.php?controller=RoomBookings&action=create';
  const getBookingUrl = 'index.php?controller=RoomBookings&action=get';
  const updateBookingUrl = 'index.php?controller=RoomBookings&action=update';
  const deleteBookingUrl = 'index.php?controller=RoomBookings&action=delete';
  const roomHistoryUrl = 'index.php?controller=RoomBookings&action=roomHistory';
  const roomListUrl = 'index.php?controller=RoomBookings&action=rooms';
  const createRoomUrl = 'index.php?controller=RoomBookings&action=createRoom';
  const updateRoomUrl = 'index.php?controller=RoomBookings&action=updateRoom';
  const deleteRoomUrl = 'index.php?controller=RoomBookings&action=deleteRoom';

  const roomCreateModal = document.getElementById('roomCreateModal');
  const roomCreateModalBackdrop = document.getElementById('roomCreateModalBackdrop');
  const openRoomCreateModalBtn = document.getElementById('openRoomCreateModalBtn');
  const closeRoomCreateModalBtn = document.getElementById('closeRoomCreateModalBtn');
  const cancelRoomCreateModalBtn = document.getElementById('cancelRoomCreateModalBtn');
  const roomCreateForm = document.getElementById('roomCreateForm');
  const roomNameInput = document.getElementById('roomName');
  const roomCodeInput = document.getElementById('roomCode');
  const roomCapacityInput = document.getElementById('roomCapacity');
  const roomStatusInput = document.getElementById('roomStatus');
  const roomEditIdInput = document.getElementById('roomEditId');
  const roomCreateSubmitBtn = document.getElementById('roomCreateSubmitBtn');
  const roomCreateModalTitle = document.getElementById('roomCreateModalTitle');

  let dynamicRoomData = Array.isArray(roomData) ? [...roomData] : [];
  let activeRoomSelection = null;

  const formFields = {
    dateTrip: document.getElementById('roomDateTrip'),
    dateRequested: document.getElementById('roomDateRequested'),
    purpose: document.getElementById('roomPurpose'),
    attendees: document.getElementById('roomAttendees'),
    departureExpected: document.getElementById('roomDepartureExpected'),
    returnExpected: document.getElementById('roomReturnExpected'),
    roomId: document.getElementById('roomId'),
    specialInstructions: document.getElementById('roomSpecialInstructions'),
    remarks: document.getElementById('roomRemarks'),
  };

  const notifications = [];
  const roomHistoryCache = new Map();
  let activeBookingDetail = null;

  function openModal(modalEl) {
    if (!modalEl) return;
    modalEl.classList.remove('hidden');
    modalEl.classList.add('block');
    document.body.style.overflow = 'hidden';
  }

  function closeModal(modalEl) {
    if (!modalEl) return;
    modalEl.classList.add('hidden');
    modalEl.classList.remove('block');
    document.body.style.overflow = '';
  }

  function parseJsonResponse(response) {
    if (!response.ok) {
      const ct = (response.headers.get('content-type') || '').toLowerCase();
      if (ct.includes('application/json')) {
        return response.json().then(json => { throw new Error(json.message || 'Request failed'); });
      }
      return response.text().then(text => { throw new Error(text || 'Request failed'); });
    }
    return response.json();
  }

  function showNotification(message, type = 'info') {
    const toastType = (type === 'success') ? 'app-toast-success' : (type === 'error') ? 'app-toast-error' : (type === 'warning') ? 'app-toast-warning' : 'app-toast-info';
    const iconText = type === 'success' ? '✔' : type === 'error' ? '✕' : type === 'warning' ? '!' : 'ℹ';
    const toast = document.createElement('div');
    toast.className = `app-toast fixed bottom-6 right-6 z-[99999] ${toastType}`;

    const icon = document.createElement('div');
    icon.className = 'toast-icon';
    icon.textContent = iconText;

    const msg = document.createElement('div');
    msg.className = 'toast-message';
    msg.textContent = message;

    const closeBtn = document.createElement('button');
    closeBtn.className = 'toast-close';
    closeBtn.setAttribute('type', 'button');
    closeBtn.setAttribute('aria-label', 'Dismiss notification');
    closeBtn.textContent = '✕';
    closeBtn.addEventListener('click', () => {
      if (toast.parentNode) toast.parentNode.removeChild(toast);
    });

    const progressWrap = document.createElement('div');
    progressWrap.className = 'app-toast-progress';
    const progress = document.createElement('i');
    progressWrap.appendChild(progress);

    toast.appendChild(icon);
    toast.appendChild(msg);
    toast.appendChild(closeBtn);
    toast.appendChild(progressWrap);

    document.body.appendChild(toast);
    window.setTimeout(() => {
      if (toast.parentNode) toast.parentNode.removeChild(toast);
    }, 4200);
  }

  function getVisibleRooms() {
    return Array.isArray(dynamicRoomData) && dynamicRoomData.length ? dynamicRoomData : (Array.isArray(roomData) ? roomData : []);
  }

  function updateRoomSummary() {
    const rooms = getVisibleRooms();
    const activeRooms = rooms.filter(room => String(room.status || '').toLowerCase() === 'active').length;
    const now = new Date();

    let occupiedNow = 0;
    let upcomingBookings = 0;

    rooms.forEach(room => {
      if (String(room.status || '').toLowerCase() !== 'active') {
        return;
      }

      const roomBookings = roomHistoryCache.get(String(room.id)) || roomHistoryCache.get(Number(room.id)) || [];
      const hasCurrentBooking = roomBookings.some(booking => {
        const start = new Date(String(booking.departure_expected).replace(' ', 'T'));
        const end = new Date(String(booking.return_expected).replace(' ', 'T'));
        if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime())) {
          return false;
        }
        return now >= start && now <= end;
      });

      if (hasCurrentBooking) {
        occupiedNow += 1;
      }
    });

    Array.from(roomHistoryCache.values()).flat().forEach(booking => {
      const status = getRoomBookingStatus(booking.departure_expected, booking.return_expected);
      if (status.key === 'upcoming') {
        upcomingBookings += 1;
      }
    });

    const availableNow = Math.max(0, activeRooms - occupiedNow);

    if (roomSummaryTotal) roomSummaryTotal.textContent = rooms.length;
    if (roomSummaryAvailable) roomSummaryAvailable.textContent = availableNow;
    if (roomSummaryOccupied) roomSummaryOccupied.textContent = occupiedNow;
    if (roomSummaryBookings) roomSummaryBookings.textContent = upcomingBookings;

    if (roomInventoryStatusBadge) {
      const badgeClasses = availableNow === 0 && activeRooms > 0
        ? 'inline-flex rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700'
        : occupiedNow > 0
          ? 'inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700'
          : 'inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700';
      roomInventoryStatusBadge.className = badgeClasses;
      roomInventoryStatusBadge.textContent = availableNow === 0 && activeRooms > 0
        ? 'Fully booked'
        : occupiedNow > 0
          ? 'Partially occupied'
          : 'Open for booking';
    }
  }

  function refreshRoomList() {
    fetch(roomListUrl, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
      .then(parseJsonResponse)
      .then(data => {
        if (!data.success || !Array.isArray(data.rooms)) {
          throw new Error(data.message || 'Unable to refresh rooms');
        }
        dynamicRoomData = data.rooms;
        updateRoomOptions();
        updateRoomSummary();
      })
      .catch(err => {
        console.error('Failed to load rooms:', err);
      });
  }

  function updateRoomOptions() {
    if (!roomFilter || !formFields.roomId) return;
    roomFilter.innerHTML = '<option value="">All</option>' + dynamicRoomData.map(room => {
      return `<option value="${room.id}">${escapeHtml(room.room_name)} (${escapeHtml(room.room_code)})</option>`;
    }).join('');
    formFields.roomId.innerHTML = '<option value="">-- Select a room --</option>' + dynamicRoomData.map(room => {
      return `<option value="${room.id}">${escapeHtml(room.room_name)} (${escapeHtml(room.room_code)}) / ${room.capacity} pax</option>`;
    }).join('');
  }

  function resetRoomCreateForm() {
    if (roomCreateForm) roomCreateForm.reset();
    if (roomStatusInput) roomStatusInput.value = 'active';
    if (roomEditIdInput) roomEditIdInput.value = '';
    if (roomCreateModalTitle) roomCreateModalTitle.textContent = 'Add Room Resource';
    if (roomCreateSubmitBtn) roomCreateSubmitBtn.textContent = 'Create Room';
  }

  function fillRoomCreateForm(room) {
    if (!room) return;
    if (roomEditIdInput) roomEditIdInput.value = room.id || '';
    if (roomNameInput) roomNameInput.value = room.room_name || '';
    if (roomCodeInput) roomCodeInput.value = room.room_code || '';
    if (roomCapacityInput) roomCapacityInput.value = room.capacity || 1;
    if (roomStatusInput) roomStatusInput.value = room.status || 'active';
    if (roomCreateModalTitle) roomCreateModalTitle.textContent = 'Edit Room Resource';
    if (roomCreateSubmitBtn) roomCreateSubmitBtn.textContent = 'Save Room';
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

  function setRoomBookingDefaults(startValue = null, endValue = null) {
    const now = new Date();
    const start = startValue ? new Date(startValue) : new Date(now.getFullYear(), now.getMonth(), now.getDate(), 9, 0);
    const end = endValue ? new Date(endValue) : new Date(start.getTime() + 60 * 60 * 1000);

    if (formFields.dateTrip) formFields.dateTrip.value = toISODate(start);
    if (formFields.dateRequested) formFields.dateRequested.value = toISODate(now);
    if (formFields.departureExpected) formFields.departureExpected.value = toISODateTime(start);
    if (formFields.returnExpected) formFields.returnExpected.value = toISODateTime(end);
    if (formFields.purpose) formFields.purpose.value = '';
    if (formFields.attendees) formFields.attendees.value = '';
    if (formFields.roomId) formFields.roomId.value = '';
    if (formFields.specialInstructions) formFields.specialInstructions.value = '';
    if (formFields.remarks) formFields.remarks.value = '';
    if (deleteRoomBookingBtn) deleteRoomBookingBtn.classList.add('hidden');
  }

  function resetRoomBookingForm() {
    roomBookingId.value = '';
    document.getElementById('roomBookingModalTitle').innerText = 'Create New Room Booking';
    Object.values(formFields).forEach(el => { if (el) el.value = ''; });
    if (deleteRoomBookingBtn) deleteRoomBookingBtn.classList.add('hidden');
    setRoomBookingDefaults();
  }

  function fillRoomBookingForm(data) {
    roomBookingId.value = data.id || '';
    if (formFields.dateTrip) formFields.dateTrip.value = data.date_trip || '';
    if (formFields.dateRequested) formFields.dateRequested.value = data.date_requested || '';
    if (formFields.purpose) formFields.purpose.value = data.purpose || '';
    if (formFields.attendees) formFields.attendees.value = data.attendees || '';
    if (formFields.departureExpected) formFields.departureExpected.value = data.departure_expected ? data.departure_expected.replace(' ', 'T').slice(0,16) : '';
    if (formFields.returnExpected) formFields.returnExpected.value = data.return_expected ? data.return_expected.replace(' ', 'T').slice(0,16) : '';
    if (formFields.roomId) formFields.roomId.value = data.room_id || '';
    if (formFields.specialInstructions) formFields.specialInstructions.value = data.special_instructions || '';
    if (formFields.remarks) formFields.remarks.value = data.remarks || '';
    document.getElementById('roomBookingModalTitle').innerText = 'Edit Room Booking';
    if (deleteRoomBookingBtn) deleteRoomBookingBtn.classList.remove('hidden');
  }

  function getRequestUrlWithParams(url, params) {
    const search = new URLSearchParams(params);
    return `${url}&${search.toString()}`;
  }

  function getRoomMeta(roomId) {
    const id = Number(roomId);
    return (dynamicRoomData.find(room => Number(room.id) === id) || roomData.find(room => Number(room.id) === id) || null);
  }

function formatBookingDateTime(value) {
    if (!value) return '—';

    const date = value instanceof Date
        ? value
        : new Date(String(value).replace(' ', 'T'));

    if (isNaN(date.getTime())) return '—';

    const now = new Date();

    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const tomorrow = new Date(today);
    tomorrow.setDate(today.getDate() + 1);

    const target = new Date(date.getFullYear(), date.getMonth(), date.getDate());

    const time = date.toLocaleTimeString([], {
        hour: 'numeric',
        minute: '2-digit'
    });

    if (target.getTime() === today.getTime()) {
        return `Today • ${time}`;
    }

    if (target.getTime() === tomorrow.getTime()) {
        return `Tomorrow • ${time}`;
    }

    return `${date.toLocaleDateString([], {
        weekday: 'short',
        month: 'short',
        day: 'numeric'
    })} • ${time}`;
}

  function openBookingDetailModal(booking) {
    if (!booking || !booking.id) return;
    activeBookingDetail = booking;
    const status = getRoomBookingStatus(booking.departure_expected, booking.return_expected);
    const roomMeta = getRoomMeta(booking.room_id);
    if (roomBookingDetailModalTitle) {
      roomBookingDetailModalTitle.textContent = booking.purpose || 'Room booking';
    }
    if (roomBookingDetailModalSubtitle) {
      roomBookingDetailModalSubtitle.textContent = roomMeta ? `${roomMeta.room_name} · ${roomMeta.room_code}` : 'Room booking';
    }
    if (roomBookingDetailStatusWrap) {
      roomBookingDetailStatusWrap.innerHTML = `
        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-semibold ${status.badgeClass}">
          <span class="h-2 w-2 rounded-full ${status.dotClass}"></span>
          ${escapeHtml(status.label)}
        </span>
      `;
    }
    if (roomBookingDetailContent) {
      roomBookingDetailContent.innerHTML = `
        <div class="rounded-[20px] border border-slate-200 bg-slate-50 p-4">
          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Room</p>
              <p class="mt-2 text-sm font-semibold text-slate-900">${escapeHtml(roomMeta ? `${roomMeta.room_name} (${roomMeta.room_code})` : '—')}</p>
            </div>
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Capacity</p>
              <p class="mt-2 text-sm font-semibold text-slate-900">${escapeHtml(roomMeta && roomMeta.capacity ? `${roomMeta.capacity} pax` : '—')}</p>
            </div>
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Start</p>
              <p class="mt-2 text-sm font-semibold text-slate-900">${escapeHtml(formatBookingDateTime(booking.departure_expected))}</p>
            </div>
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">End</p>
              <p class="mt-2 text-sm font-semibold text-slate-900">${escapeHtml(formatBookingDateTime(booking.return_expected))}</p>
            </div>
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Attendees</p>
              <p class="mt-2 text-sm font-semibold text-slate-900">${escapeHtml(booking.attendees || 0)} pax</p>
            </div>
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Requested</p>
              <p class="mt-2 text-sm font-semibold text-slate-900">${escapeHtml(booking.date_requested || booking.date_trip || '—')}</p>
            </div>
          </div>
          <div class="mt-4 space-y-3">
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Special instructions</p>
              <p class="mt-2 text-sm text-slate-600">${escapeHtml(booking.special_instructions || 'No special instructions.')}</p>
            </div>
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Remarks</p>
              <p class="mt-2 text-sm text-slate-600">${escapeHtml(booking.remarks || 'No remarks.')}</p>
            </div>
          </div>
        </div>
      `;
    }
    openModal(roomBookingDetailModal);
  }

  function openBookingEditModal(booking) {
    const id = booking && booking.id ? booking.id : activeBookingDetail && activeBookingDetail.id ? activeBookingDetail.id : null;
    if (!id) return;
    fetch(`${getBookingUrl}&id=${encodeURIComponent(id)}`, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
      .then(parseJsonResponse)
      .then(data => {
        if (!data.success) throw new Error(data.message || 'Unable to load booking');
        fillRoomBookingForm(data.booking || {});
        closeModal(roomBookingDetailModal);
        openModal(roomBookingModal);
      })
      .catch(err => {
        console.error(err);
        showNotification('Unable to load booking details.', 'error');
      });
  }

  function deleteBookingById(id) {
    if (!id || !window.confirm('Delete this booking?')) return;
    const fd = new FormData();
    fd.append('id', id);
    fetch(deleteBookingUrl, { method: 'POST', body: fd, headers: {'X-Requested-With': 'XMLHttpRequest'} })
      .then(parseJsonResponse)
      .then(data => {
        if (!data.success) {
          showNotification(data.message || 'Unable to delete booking', 'error');
          return;
        }
        closeModal(roomBookingDetailModal);
        resetRoomBookingForm();
        closeModal(roomBookingModal);
        calendar.refetchEvents();
        refreshRoomList();
        loadRoomCards();
        refreshRoomHistoryForActiveSelection();
        showNotification('Booking deleted', 'info');
      })
      .catch(err => showNotification(err.message || 'Network error deleting booking', 'error'));
  }

  const calendar = new FullCalendar.Calendar(roomBookingCalendar, {
    initialView: 'dayGridMonth',
    themeSystem: 'standard',
    selectable: true,
    selectMirror: true,
    editable: true,
    eventResizableFromStart: true,
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    events(fetchInfo, successCallback, failureCallback) {
      let params = {start: fetchInfo.startStr, end: fetchInfo.endStr};
      if (roomFilter && roomFilter.value) {
        params.room_id = roomFilter.value;
      }
      fetch(getRequestUrlWithParams(listUrl, params), {headers: {'X-Requested-With': 'XMLHttpRequest'}})
        .then(parseJsonResponse)
        .then(data => {
          successCallback(data.events || []);
        })
        .catch(error => {
          console.error('Failed to load calendar events', error);
          failureCallback(error);
        });
    },
    select(info) {
      if (!info.start) return;
      const start = info.start;
      const end = info.end || new Date(start.getTime() + 60 * 60 * 1000);
      setRoomBookingDefaults(start.toISOString(), end.toISOString());
      openModal(roomBookingModal);
      calendar.unselect();
    },
    eventClick(info) {
      info.jsEvent.preventDefault();
      fetch(`${getBookingUrl}&id=${encodeURIComponent(info.event.id)}`, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
        .then(parseJsonResponse)
        .then(data => {
          if (!data.success) throw new Error(data.message || 'Unable to load booking');
          openBookingDetailModal(data.booking || {});
        })
        .catch(err => {
          console.error(err);
          showNotification('Unable to load booking details.', 'error');
        });
    },
    eventDrop(info) {
      const event = info.event;
      const id = event.id;
      if (!id) {
        info.revert();
        return;
      }
      const start = event.start;
      const end = event.end || new Date(start.getTime() + 60 * 60 * 1000);
      const payload = new FormData();
      payload.append('id', id);
      payload.append('departure_expected', toISODateTime(start));
      payload.append('return_expected', toISODateTime(end));
      fetch(updateBookingUrl, {
        method: 'POST',
        body: payload,
        headers: {'X-Requested-With': 'XMLHttpRequest'}
      })
      .then(parseJsonResponse)
      .then(data => {
        if (!data.success) {
          showNotification(data.message || 'Unable to move booking', 'error');
          info.revert();
          return;
        }
        showNotification('Booking moved successfully', 'success');
      })
      .catch(err => {
        console.error(err);
        showNotification('Network error moving booking', 'error');
        info.revert();
      });
    },
    eventResize(info) {
      const event = info.event;
      const id = event.id;
      if (!id) {
        info.revert();
        return;
      }
      const start = event.start;
      const end = event.end || new Date(start.getTime() + 60 * 60 * 1000);
      const payload = new FormData();
      payload.append('id', id);
      payload.append('departure_expected', toISODateTime(start));
      payload.append('return_expected', toISODateTime(end));
      fetch(updateBookingUrl, {
        method: 'POST',
        body: payload,
        headers: {'X-Requested-With': 'XMLHttpRequest'}
      })
      .then(parseJsonResponse)
      .then(data => {
        if (!data.success) {
          showNotification(data.message || 'Unable to resize booking', 'error');
          info.revert();
          return;
        }
        showNotification('Booking duration updated', 'success');
      })
      .catch(err => {
        console.error(err);
        showNotification('Network error resizing booking', 'error');
        info.revert();
      });
    }
  });

  calendar.render();

  if (calendarViewSelect) {
    calendarViewSelect.addEventListener('change', () => {
      calendar.changeView(calendarViewSelect.value);
    });
  }

  if (roomFilter) {
    roomFilter.addEventListener('change', () => {
      calendar.refetchEvents();
    });
  }

  function getRoomBookingStatus(departureExpected, returnExpected) {
    const now = new Date();
    const start = new Date(String(departureExpected).replace(' ', 'T'));
    const end = new Date(String(returnExpected).replace(' ', 'T'));
    if (isNaN(start) || isNaN(end)) {
      return { key: 'pending', label: 'Pending', badgeClass: 'bg-slate-100 text-slate-700', dotClass: 'bg-slate-400' };
    }
    if (now < start) {
      return { key: 'upcoming', label: 'Upcoming', badgeClass: 'bg-amber-50 text-amber-700', dotClass: 'bg-amber-500' };
    }
    if (now > end) {
      return { key: 'finished', label: 'Finished meeting', badgeClass: 'bg-emerald-50 text-emerald-700', dotClass: 'bg-emerald-500' };
    }
    return { key: 'ongoing', label: 'Ongoing', badgeClass: 'bg-sky-50 text-sky-700', dotClass: 'bg-sky-500' };
  }

  function renderRoomPreview(roomId, bookings) {
    const previewEl = document.getElementById(`room-card-preview-${roomId}`);
    const moreEl = document.getElementById(`room-card-more-${roomId}`);
    if (!previewEl || !moreEl) return;

    const upcoming = bookings.filter(b => getRoomBookingStatus(b.departure_expected, b.return_expected).key === 'upcoming');
    previewEl.innerHTML = '';
    if (upcoming.length === 0) {
      previewEl.innerHTML = '<div class="text-sm text-slate-500">No upcoming reservations</div>';
      moreEl.textContent = '';
      return;
    }

    const limit = 2;
    const shown = upcoming.slice(0, limit);
    shown.forEach(b => previewEl.appendChild(createBookingSummaryItem(b, true)));
    const extra = upcoming.length - shown.length;
    moreEl.textContent = extra > 0 ? `+${extra} more upcoming` : 'Click to view full history';
  }

  function renderRoomHistoryModal(room, bookings) {
    const list = Array.isArray(bookings) ? bookings : [];
    const sortByStart = (a, b) => new Date(String(a.departure_expected).replace(' ', 'T')) - new Date(String(b.departure_expected).replace(' ', 'T'));
    const sortByEnd = (a, b) => new Date(String(b.departure_expected).replace(' ', 'T')) - new Date(String(a.departure_expected).replace(' ', 'T'));

    const ongoing = list.filter(b => getRoomBookingStatus(b.departure_expected, b.return_expected).key === 'ongoing').sort(sortByStart);
    const upcoming = list.filter(b => getRoomBookingStatus(b.departure_expected, b.return_expected).key === 'upcoming').sort(sortByStart);
    const finished = list.filter(b => getRoomBookingStatus(b.departure_expected, b.return_expected).key === 'finished').sort(sortByEnd);
    const history = finished.slice().sort(sortByEnd);

    if (roomHistoryModalTitle) {
      roomHistoryModalTitle.textContent = `${room.room_name} · ${room.room_code}`;
    }
    if (roomHistoryModalSubtitle) {
      roomHistoryModalSubtitle.textContent = `${room.capacity} pax • ${String(room.status || 'active').toLowerCase() === 'active' ? 'Active room' : 'Inactive room'}`;
    }
    if (roomHistoryModalContent) {
      roomHistoryModalContent.innerHTML = '';

      const summary = document.createElement('div');
      summary.className = 'mb-4 flex flex-wrap gap-2';
      summary.innerHTML = `
        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-700">${list.length} total bookings</span>
        <span class="rounded-full bg-sky-50 px-2.5 py-1 text-[11px] font-semibold text-sky-700">${ongoing.length} ongoing</span>
        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700">${upcoming.length} upcoming</span>
        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">${finished.length} finished</span>
      `;
      roomHistoryModalContent.appendChild(summary);

      const renderSection = (title, items, badgeClass, emptyText) => {
        const section = document.createElement('div');
        section.className = 'rounded-[20px] border border-slate-200 bg-slate-50 p-4';
        const header = document.createElement('div');
        header.className = 'mb-3 flex items-center justify-between gap-2';
        header.innerHTML = `
          <div>
            <h3 class="text-sm font-semibold text-slate-900">${escapeHtml(title)}</h3>
            <p class="text-xs text-slate-500">${items.length} item${items.length === 1 ? '' : 's'}</p>
          </div>
          <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold ${badgeClass}">${escapeHtml(title)}</span>
        `;
        section.appendChild(header);

        if (items.length === 0) {
          const empty = document.createElement('div');
          empty.className = 'rounded-2xl border border-dashed border-slate-200 bg-white px-3 py-4 text-sm text-slate-500';
          empty.textContent = emptyText;
          section.appendChild(empty);
        } else {
          const listWrap = document.createElement('div');
          listWrap.className = 'space-y-2';
          items.forEach(item => listWrap.appendChild(createBookingSummaryItem(item, false)));
          section.appendChild(listWrap);
        }

        roomHistoryModalContent.appendChild(section);
      };

      renderSection('Ongoing', ongoing, 'bg-sky-50 text-sky-700', 'No ongoing meetings.');
      renderSection('Upcoming', upcoming, 'bg-amber-50 text-amber-700', 'No upcoming meetings.');
      renderSection('Finished meetings', finished, 'bg-emerald-50 text-emerald-700', 'No finished meetings.');
      renderSection('History', history, 'bg-slate-100 text-slate-700', 'No booking history.');
    }
  }

  function refreshRoomHistoryForActiveSelection() {
    if (!activeRoomSelection || !activeRoomSelection.id) return;
    if (!roomHistoryModal || roomHistoryModal.classList.contains('hidden')) return;

    if (roomHistoryModalContent) {
      roomHistoryModalContent.innerHTML = '<div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-500">Refreshing room schedule…</div>';
    }

    fetch(`${roomHistoryUrl}&room_id=${encodeURIComponent(activeRoomSelection.id)}`, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
      .then(parseJsonResponse)
      .then(data => {
        if (!data.success) throw new Error(data.message || 'Unable to load room history');
        const bookings = data.bookings || [];
        roomHistoryCache.set(activeRoomSelection.id, bookings);
        renderRoomHistoryModal(activeRoomSelection, bookings);
      })
      .catch(err => {
        showNotification(err.message || 'Failed to load room history', 'error');
      });
  }

  function openRoomHistoryModal(room) {
    activeRoomSelection = room;
    openModal(roomHistoryModal);
    if (roomHistoryCache.has(room.id)) {
      renderRoomHistoryModal(room, roomHistoryCache.get(room.id));
      return;
    }

    if (roomHistoryModalContent) {
      roomHistoryModalContent.innerHTML = '<div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-500">Loading room schedule…</div>';
    }

    fetch(`${roomHistoryUrl}&room_id=${encodeURIComponent(room.id)}`, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
      .then(parseJsonResponse)
      .then(data => {
        if (!data.success) throw new Error(data.message || 'Unable to load room history');
        const bookings = data.bookings || [];
        roomHistoryCache.set(room.id, bookings);
        renderRoomHistoryModal(room, bookings);
        updateRoomSummary();
      })
      .catch(err => {
        showNotification(err.message || 'Failed to load room history', 'error');
      });
  }

  function getRoomAvailabilityState(bookings) {
    const list = Array.isArray(bookings) ? bookings : [];
    const now = new Date();
    const currentBookings = list.filter(booking => {
      const start = new Date(String(booking.departure_expected).replace(' ', 'T'));
      const end = new Date(String(booking.return_expected).replace(' ', 'T'));
      if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime())) {
        return false;
      }
      return now >= start && now <= end;
    });
    const currentBooking = currentBookings.sort((a, b) => new Date(String(a.departure_expected).replace(' ', 'T')) - new Date(String(b.departure_expected).replace(' ', 'T')))[0] || null;
    const isOccupied = Boolean(currentBooking);
    const nextAvailability = isOccupied && currentBooking.return_expected ? new Date(String(currentBooking.return_expected).replace(' ', 'T')) : null;
    return { isOccupied, currentBooking, nextAvailability };
  }

  function renderRoomCardAvailability(room, bookings) {
    const statusBadgeEl = document.getElementById(`room-card-status-${room.id}`);
    const availabilityDetailEl = document.getElementById(`room-card-availability-${room.id}`);
    if (!statusBadgeEl || !availabilityDetailEl) return;

    const isActive = String(room.status || '').toLowerCase() === 'active';
    const state = getRoomAvailabilityState(bookings);
    const isOccupied = state.isOccupied;
    const isUnavailable = !isActive || isOccupied;
    const badgeClass = isUnavailable
      ? 'inline-flex rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700'
      : 'inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700';
    const badgeLabel = !isActive ? 'Unavailable' : (isOccupied ? 'Occupied' : 'Available');
    const detailsHtml = !isActive
      ? '<div class="rounded-xl border border-rose-100 bg-rose-50 px-3 py-2 text-xs font-medium text-rose-700">Inactive room · unavailable</div>'
      : (isOccupied && state.nextAvailability
        ? `<div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600"><span class="font-semibold text-slate-700">Next availability:</span> ${escapeHtml(formatBookingDateTime(state.nextAvailability))}</div>`
        : '<div class="rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700">Available now</div>');

    statusBadgeEl.className = badgeClass;
    statusBadgeEl.textContent = badgeLabel;
    availabilityDetailEl.innerHTML = detailsHtml;
  }

  function buildRoomCard(room, bookings = []) {
    const card = document.createElement('div');
    const isActive = String(room.status || '').toLowerCase() === 'active';
    const status = isActive ? 'Active' : 'Inactive';
    const cardClasses = isActive
      ? 'rounded-2xl border border-slate-200 bg-white p-4 shadow-sm cursor-pointer hover:shadow-lg transition'
      : 'rounded-2xl border border-slate-300 bg-slate-100 p-4 shadow-sm cursor-pointer opacity-80 hover:shadow-lg transition';
    card.className = cardClasses;
    card.setAttribute('role', 'button');
    card.setAttribute('tabindex', '0');
    card.dataset.roomId = String(room.id);
    const badge = isActive
      ? `<span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">${status}</span>`
      : `<span class="inline-flex rounded-full bg-slate-300 px-2.5 py-1 text-xs font-semibold text-slate-700">${status}</span>`;
    const mutedText = isActive ? 'text-slate-900' : 'text-slate-500';
    const mutedMeta = isActive ? 'text-slate-500' : 'text-slate-400';
    card.innerHTML = `
      <div class="flex items-center justify-between gap-3">
        <div>
          <h3 class="text-base font-semibold ${mutedText}">${escapeHtml(room.room_name)}</h3>
          <p class="text-sm ${mutedMeta}">${escapeHtml(room.room_code)} · ${escapeHtml(room.capacity)} pax</p>
        </div>
        <div>${badge}</div>
      </div>
      <div class="mt-4 space-y-3">
        <div class="flex items-center justify-between gap-2">
          <span class="text-xs uppercase tracking-[0.24em] ${mutedMeta}">Current status</span>
          <span id="room-card-status-${room.id}" class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Available</span>
        </div>
        <div id="room-card-availability-${room.id}" class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2 text-xs font-medium text-emerald-700">Checking availability…</div>
        <div class="text-xs uppercase tracking-[0.24em] ${mutedMeta}">Upcoming reservations</div>
        <div class="space-y-3" id="room-card-preview-${room.id}"><div class="text-sm ${mutedMeta}">Loading...</div></div>
        <div class="text-xs ${mutedMeta}" id="room-card-more-${room.id}"></div>
        <div class="mt-3 flex items-center justify-between border-t border-slate-200 pt-3 text-sm ${mutedMeta}">
          <span>Open room schedule</span>
          <span class="rounded-full bg-slate-200 px-2.5 py-1 text-[11px] font-semibold text-slate-700">View</span>
        </div>
      </div>`;
    renderRoomCardAvailability(room, bookings);
    card.addEventListener('click', () => openRoomHistoryModal(room));
    card.addEventListener('keydown', event => {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        openRoomHistoryModal(room);
      }
    });
    return card;
  }

  function createBookingSummaryItem(booking, compact = true) {
    const container = document.createElement('div');
    container.className = 'w-full rounded-2xl border border-slate-200 bg-white p-3 text-left shadow-sm';
    const start = new Date(String(booking.departure_expected).replace(' ', 'T'));
    const end = new Date(String(booking.return_expected).replace(' ', 'T'));
    const status = getRoomBookingStatus(booking.departure_expected, booking.return_expected);
    const formattedRange = `${start.toLocaleString([], {month:'short', day:'numeric', hour:'2-digit', minute:'2-digit'})} – ${end.toLocaleString([], {hour:'2-digit', minute:'2-digit'})}`;
    container.innerHTML = `
      <div class="flex items-start justify-between gap-2">
        <div class="min-w-0">
          <div class="flex items-center gap-2">
            <span class="block truncate text-sm font-semibold text-slate-900">${escapeHtml(booking.purpose || 'Booking')}</span>
          </div>
          <div class="mt-1 text-xs text-slate-500">${escapeHtml(formattedRange)}</div>
        </div>
        <span class="inline-flex items-center gap-1 whitespace-nowrap rounded-full px-2.5 py-1 text-[11px] font-semibold ${status.badgeClass}">
          <span class="h-2 w-2 rounded-full ${status.dotClass}"></span>
          ${escapeHtml(status.label)}
        </span>
      </div>
      <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-500">
        <span class="rounded-full bg-slate-50 px-2 py-1">${escapeHtml(booking.attendees || 0)} pax</span>
        ${compact ? '' : `<span class="rounded-full bg-slate-50 px-2 py-1">${escapeHtml(booking.room_name || booking.room_code || '')}</span>`}
      </div>
    `;
    return container;
  }

  function escapeHtml(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function loadRoomCards() {
    if (!roomCardsContainer) return;
    roomCardsContainer.innerHTML = '';
    updateRoomSummary();
    const roomsToRender = getVisibleRooms();
    if (!roomsToRender.length) {
      updateRoomSummary();
      return;
    }

    let pendingRequests = roomsToRender.length;
    roomsToRender.forEach(room => {
      const card = buildRoomCard(room);
      roomCardsContainer.appendChild(card);
      fetch(`${roomHistoryUrl}&room_id=${encodeURIComponent(room.id)}`, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
        .then(parseJsonResponse)
        .then(data => {
          const bookings = data.bookings || [];
          roomHistoryCache.set(room.id, bookings);
          renderRoomCardAvailability(room, bookings);
          renderRoomPreview(room.id, bookings);
          updateRoomSummary();
        })
        .catch(() => {
          const previewEl = document.getElementById(`room-card-preview-${room.id}`);
          if (previewEl) {
            previewEl.innerHTML = '<div class="text-sm text-slate-500">Unable to load reservations</div>';
          }
          updateRoomSummary();
        })
        .finally(() => {
          pendingRequests -= 1;
          if (pendingRequests === 0) {
            updateRoomSummary();
          }
        });
    });
  }

  if (openRoomBookingModalBtn) {
    openRoomBookingModalBtn.addEventListener('click', () => {
      resetRoomBookingForm();
      openModal(roomBookingModal);
    });
  }

  if (closeRoomBookingModalBtn) {
    closeRoomBookingModalBtn.addEventListener('click', () => {
      resetRoomBookingForm();
      closeModal(roomBookingModal);
    });
  }

  if (cancelRoomBookingModalBtn) {
    cancelRoomBookingModalBtn.addEventListener('click', () => {
      resetRoomBookingForm();
      closeModal(roomBookingModal);
    });
  }

  if (roomBookingModalBackdrop) {
    roomBookingModalBackdrop.addEventListener('click', () => {
      resetRoomBookingForm();
      closeModal(roomBookingModal);
    });
  }

  roomBookingForm.addEventListener('submit', function (event) {
    event.preventDefault();

    const formData = new FormData(roomBookingForm);
    const isUpdate = Boolean(roomBookingId.value);
    const url = isUpdate ? updateBookingUrl : createBookingUrl;

    fetch(url, {
      method: 'POST',
      body: formData,
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(parseJsonResponse)
    .then(data => {
      if (!data.success) {
        showNotification(data.message || 'Unable to save booking', 'error');
        return;
      }
      resetRoomBookingForm();
      closeModal(roomBookingModal);
      calendar.refetchEvents();
      refreshRoomList();
      loadRoomCards();
      refreshRoomHistoryForActiveSelection();
      showNotification('Booking saved successfully', 'success');
    })
    .catch(err => {
      showNotification(err.message || 'Network error saving booking', 'error');
    });
  });

  if (deleteRoomBookingBtn) {
    deleteRoomBookingBtn.addEventListener('click', () => {
      deleteBookingById(roomBookingId.value);
    });
  }

  if (closeRoomBookingDetailModalBtn) {
    closeRoomBookingDetailModalBtn.addEventListener('click', () => closeModal(roomBookingDetailModal));
  }

  if (closeRoomBookingDetailActionBtn) {
    closeRoomBookingDetailActionBtn.addEventListener('click', () => closeModal(roomBookingDetailModal));
  }

  if (roomBookingDetailModalBackdrop) {
    roomBookingDetailModalBackdrop.addEventListener('click', () => closeModal(roomBookingDetailModal));
  }

  if (editRoomBookingDetailBtn) {
    editRoomBookingDetailBtn.addEventListener('click', () => openBookingEditModal(activeBookingDetail));
  }

  if (deleteRoomBookingDetailBtn) {
    deleteRoomBookingDetailBtn.addEventListener('click', () => deleteBookingById(activeBookingDetail && activeBookingDetail.id));
  }

  if (refreshRoomsBtn) {
    refreshRoomsBtn.addEventListener('click', () => {
      calendar.refetchEvents();
      refreshRoomList();
      loadRoomCards();
    });
  }

  if (openRoomCreateModalBtn) {
    openRoomCreateModalBtn.addEventListener('click', () => {
      resetRoomCreateForm();
      openModal(roomCreateModal);
    });
  }

  if (editRoomDetailsBtn) {
    editRoomDetailsBtn.addEventListener('click', () => {
      if (activeRoomSelection) {
        fillRoomCreateForm(activeRoomSelection);
        closeModal(roomHistoryModal);
        openModal(roomCreateModal);
      }
    });
  }

  if (deleteRoomDetailsBtn) {
    deleteRoomDetailsBtn.addEventListener('click', () => {
      if (!activeRoomSelection || !activeRoomSelection.id) return;
      if (!window.confirm(`Delete room ${activeRoomSelection.room_name || 'this room'}?`)) return;
      const fd = new FormData();
      fd.append('id', activeRoomSelection.id);
      fetch(deleteRoomUrl, { method: 'POST', body: fd, headers: {'X-Requested-With': 'XMLHttpRequest'} })
        .then(parseJsonResponse)
        .then(data => {
          if (!data.success) {
            showNotification(data.message || 'Unable to delete room', 'error');
            return;
          }
          closeModal(roomHistoryModal);
          refreshRoomList();
          loadRoomCards();
          updateRoomSummary();
          showNotification('Room deleted successfully', 'info');
        })
        .catch(err => showNotification(err.message || 'Network error deleting room', 'error'));
    });
  }

  if (roomCreateModalBackdrop) {
    roomCreateModalBackdrop.addEventListener('click', () => {
      resetRoomCreateForm();
      closeModal(roomCreateModal);
    });
  }

  if (closeRoomCreateModalBtn) {
    closeRoomCreateModalBtn.addEventListener('click', () => {
      resetRoomCreateForm();
      closeModal(roomCreateModal);
    });
  }

  if (cancelRoomCreateModalBtn) {
    cancelRoomCreateModalBtn.addEventListener('click', () => {
      resetRoomCreateForm();
      closeModal(roomCreateModal);
    });
  }

  if (roomHistoryModalBackdrop) {
    roomHistoryModalBackdrop.addEventListener('click', () => closeModal(roomHistoryModal));
  }

  if (closeRoomHistoryModalBtn) {
    closeRoomHistoryModalBtn.addEventListener('click', () => closeModal(roomHistoryModal));
  }

  if (roomCreateForm) {
    roomCreateForm.addEventListener('submit', function (event) {
      event.preventDefault();
      const formData = new FormData(roomCreateForm);
      const isEdit = Boolean(roomEditIdInput && roomEditIdInput.value);
      const url = isEdit ? updateRoomUrl : createRoomUrl;
      fetch(url, {
        method: 'POST',
        body: formData,
        headers: {'X-Requested-With': 'XMLHttpRequest'}
      })
      .then(parseJsonResponse)
      .then(data => {
        if (!data.success) {
          showNotification(data.message || (isEdit ? 'Unable to update room' : 'Unable to create room'), 'error');
          return;
        }
        showNotification(isEdit ? 'Room updated successfully' : 'Room created successfully', 'success');
        resetRoomCreateForm();
        closeModal(roomCreateModal);
        refreshRoomList();
        loadRoomCards();
        updateRoomSummary();
      })
      .catch(err => {
        showNotification(err.message || (isEdit ? 'Network error updating room' : 'Network error creating room'), 'error');
      });
    });
  }

  setRoomBookingDefaults();
  refreshRoomList();
  loadRoomCards();
})();
</script>
