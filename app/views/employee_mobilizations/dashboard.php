<?php
require_once __DIR__ . '/../../models/StaffDirectoryModel.php';
require_once __DIR__ . '/../../Services/EmployeeMobilizationService.php';
require_once __DIR__ . '/../../core/Database.php';
$staffDirectoryModel = new StaffDirectoryModel();
$employees = $staffDirectoryModel->getAllStaff();
$employeesJson = json_encode(array_map(static function ($employee) {
  return [
    'id' => (string)($employee['id'] ?? ''),
    'staff_id' => (string)($employee['staff_id'] ?? ''),
    'firstName' => (string)($employee['firstName'] ?? ''),
    'lastName' => (string)($employee['lastName'] ?? ''),
    'position' => (string)($employee['position'] ?? ''),
    'department' => (string)($employee['department'] ?? ''),
  ];
}, $employees), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$mobilizationService = new \App\Services\EmployeeMobilizationService();
$mobilizedThisMonthCount = count($mobilizationService->getMobilizedThisMonth());
$demobilizedThisMonthCount = count($mobilizationService->getDemobilizedThisMonth());
$todayMovements = $mobilizationService->getTodayMovements();
$mobilizationsTodayCount = count(array_filter($todayMovements, static function (array $movement) {
  return isset($movement['movement_type']) && $movement['movement_type'] === 'Mobilization';
}));
$demobilizationsTodayCount = count(array_filter($todayMovements, static function (array $movement) {
  return isset($movement['movement_type']) && $movement['movement_type'] === 'Demobilization';
}));
$db = Database::connect();
$latestMovements = [];
$stmt = $db->prepare("SELECT id, employee_id, movement_type, movement_date FROM employee_mobilizations WHERE movement_type IN ('Mobilization', 'Demobilization') ORDER BY movement_date DESC, id DESC LIMIT 10");
$stmt->execute();
$latestMovements = $stmt->fetchAll(PDO::FETCH_ASSOC);

$employeeLookup = [];
$employeeIds = [];
foreach ($latestMovements as $movement) {
  if (!empty($movement['employee_id'])) {
    $employeeIds[] = (int)$movement['employee_id'];
  }
}

if ($employeeIds !== []) {
  $placeholders = implode(',', array_fill(0, count($employeeIds), '?'));
  $employeeStmt = $db->prepare("SELECT id, firstName, lastName, department FROM staff_directory WHERE id IN ({$placeholders})");
  $employeeStmt->execute($employeeIds);
  foreach ($employeeStmt->fetchAll(PDO::FETCH_ASSOC) as $employee) {
    $employeeLookup[(int)($employee['id'] ?? 0)] = $employee;
  }
}

$mobilizationRows = [];
$demobilizationRows = [];
foreach ($latestMovements as $movement) {
  $employeeId = isset($movement['employee_id']) ? (int)$movement['employee_id'] : 0;
  $employee = $employeeLookup[$employeeId] ?? [];
  $employeeName = trim((string)($employee['firstName'] ?? '') . ' ' . (string)($employee['lastName'] ?? ''));
  if ($employeeName === '') {
    $employeeName = 'Employee #' . $employeeId;
  }
  $department = (string)($employee['department'] ?? 'Department not set');
  $movementDate = (string)($movement['movement_date'] ?? '');
  $formattedTime = '';
  if ($movementDate !== '') {
    $dateObj = DateTime::createFromFormat('Y-m-d', $movementDate);
    if ($dateObj instanceof DateTime) {
      $formattedTime = $dateObj->format('F j, Y');
    } else {
      $formattedTime = $movementDate;
    }
  }
  $row = ['name' => $employeeName, 'department' => $department, 'time' => $formattedTime];

  if ((string)($movement['movement_type'] ?? '') === 'Demobilization') {
    $demobilizationRows[] = $row;
  } else {
    $mobilizationRows[] = $row;
  }
}

$mobilizationRows = array_slice($mobilizationRows, 0, 5);
$demobilizationRows = array_slice($demobilizationRows, 0, 5);

$placeholderMovementCards = [
  [
    'title' => 'Latest Mobilizations',
    'subtitle' => 'Most recent entries',
    'accent' => 'emerald',
    'groupLabel' => 'Mobilization',
    'rows' => $mobilizationRows,
  ],
  [
    'title' => 'Latest Demobilizations',
    'subtitle' => 'Most recent entries',
    'accent' => 'rose',
    'groupLabel' => 'Demobilization',
    'rows' => $demobilizationRows,
  ],
];

$mobilizationCalendarEvents = [];
foreach ($calendarEventsByDay as $dayKey => $events) {
  foreach ($events as $event) {
    $fullName = trim((string)($event['employee_name'] ?? 'Employee'));
    if ($fullName === '') {
      $fullName = 'Employee';
    }
    $shortName = explode(' ', $fullName)[0] ?: 'Employee';
    $movementType = (string)($event['movement_type'] ?? 'Mobilization');
    $backgroundColor = $movementType === 'Demobilization' ? '#FECACA' : '#DCFCE7';
    $borderColor = $movementType === 'Demobilization' ? '#FCA5A5' : '#86EFAC';
    $textColor = $movementType === 'Demobilization' ? '#B91C1C' : '#166534';

    $mobilizationCalendarEvents[] = [
      'id' => $event['id'] ?? null,
      'title' => $shortName,
      'start' => $dayKey,
      'allDay' => true,
      'backgroundColor' => $backgroundColor,
      'borderColor' => $borderColor,
      'textColor' => $textColor,
      'extendedProps' => [
        'movement_id' => $event['id'] ?? null,
        'employee_id' => $event['employee_id'] ?? null,
        'employee_name' => $fullName,
        'movement_type' => $movementType,
        'remarks' => $event['remarks'] ?? '',
      ],
    ];
  }
}
?>
<div class="max-w-[1400px] mx-auto px-3 py-6">
  <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
    <div class="flex flex-col gap-2 lg:flex-row lg:items-end lg:justify-between">
      <div class="space-y-2">
        <div class="flex items-center gap-2 text-xs font-medium uppercase tracking-[0.2em] text-slate-500">
          <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
          Mobilization Dashboard
        </div>
        <div>
          <h1 class="text-base font-semibold text-slate-900">Employee movement tracking</h1>
          <p class="text-sm text-slate-500">A streamlined overview for mobilizations, demobilizations, and day-to-day activity.</p>
        </div>
      </div>
      <div class="flex flex-wrap items-center gap-2">
       
        <button id="openMovementModalBtn" type="button" class="inline-flex h-8 items-center justify-center rounded-lg bg-slate-900 px-3 text-sm font-medium text-white transition hover:bg-slate-800">New Movement</button>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1  mt-2 gap-3 md:grid-cols-2 xl:grid-cols-4">
    <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
      <div class="space-y-3">
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Mobilizations Today</p>
          <p class="mt-1 text-3xl font-semibold tracking-tight text-slate-900 leading-none"><?= htmlspecialchars((string)($mobilizationsTodayCount ?? 0)) ?></p>
        </div>
        <p class="text-sm text-slate-400">Total mobilizations scheduled for today.</p>
      </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
      <div class="space-y-3">
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Demobilizations Today</p>
          <p class="mt-1 text-3xl font-semibold tracking-tight text-slate-900 leading-none"><?= htmlspecialchars((string)($demobilizationsTodayCount ?? 0)) ?></p>
        </div>
        <p class="text-sm text-slate-400">Total demobilizations scheduled for today.</p>
      </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
      <div class="space-y-3">
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Mobilizations This Month</p>
          <p class="mt-1 text-3xl font-semibold tracking-tight text-slate-900 leading-none"><?= htmlspecialchars((string)($mobilizedThisMonthCount ?? 0)) ?></p>
        </div>
        <p class="text-sm text-slate-400">Total mobilizations for the current month.</p>
      </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
      <div class="space-y-3">
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Demobilizations This Month</p>
          <p class="mt-1 text-3xl font-semibold tracking-tight text-slate-900 leading-none"><?= htmlspecialchars((string)($demobilizedThisMonthCount ?? 0)) ?></p>
        </div>
        <p class="text-sm text-slate-400">Total demobilizations for the current month.</p>
      </div>
    </div>
  </div>

  <div class="mt-2 grid grid-cols-1 gap-3 lg:grid-cols-2">
    <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
      <div class="flex items-center justify-between gap-2 border-b border-slate-200 pb-3">
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Today's Workforce Movement</p>
          <h2 class="mt-1 text-lg font-semibold text-slate-900">Mobilizations Today</h2>
        </div>
      </div>
      <div class="mt-3 space-y-2">
        <?php foreach (array_slice($mobilizationRows, 0, 5) as $row): ?>
          <?php
            $initials = implode('', array_filter(array_map(static fn ($part) => $part !== '' ? strtoupper($part[0]) : '', explode(' ', $row['name'])), fn ($char) => $char !== '')) ?: 'E';
          ?>
          <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-3">
            <div class="flex min-w-0 items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-sm font-semibold text-white"><?= htmlspecialchars($initials) ?></div>
              <div class="min-w-0">
                <div class="truncate text-sm font-semibold text-slate-900"><?= htmlspecialchars($row['name']) ?></div>
                <?php if ($row['department'] !== '' && $row['department'] !== 'Department not set'): ?>
                  <div class="truncate text-xs text-slate-500"><?= htmlspecialchars($row['department']) ?></div>
                <?php endif; ?>
              </div>
            </div>
            <div class="shrink-0 text-xs font-medium text-slate-600"><?= htmlspecialchars($row['time']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
      <?php if (count($mobilizationRows) > 5): ?>
        <div class="mt-3 border-t border-slate-200 pt-3 text-right">
          <a href="#" class="text-sm font-medium text-slate-700 transition hover:text-slate-900">View All</a>
        </div>
      <?php endif; ?>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
      <div class="flex items-center justify-between gap-2 border-b border-slate-200 pb-3">
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Today's Workforce Movement</p>
          <h2 class="mt-1 text-lg font-semibold text-slate-900">Demobilizations Today</h2>
        </div>
      </div>
      <div class="mt-3 space-y-2">
        <?php foreach (array_slice($demobilizationRows, 0, 5) as $row): ?>
          <?php
            $initials = implode('', array_filter(array_map(static fn ($part) => $part !== '' ? strtoupper($part[0]) : '', explode(' ', $row['name'])), fn ($char) => $char !== '')) ?: 'E';
          ?>
          <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-3">
            <div class="flex min-w-0 items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-sm font-semibold text-white"><?= htmlspecialchars($initials) ?></div>
              <div class="min-w-0">
                <div class="truncate text-sm font-semibold text-slate-900"><?= htmlspecialchars($row['name']) ?></div>
                <?php if ($row['department'] !== '' && $row['department'] !== 'Department not set'): ?>
                  <div class="truncate text-xs text-slate-500"><?= htmlspecialchars($row['department']) ?></div>
                <?php endif; ?>
              </div>
            </div>
            <div class="shrink-0 text-xs font-medium text-slate-600"><?= htmlspecialchars($row['time']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
      <?php if (count($demobilizationRows) > 5): ?>
        <div class="mt-3 border-t border-slate-200 pt-3 text-right">
          <a href="#" class="text-sm font-medium text-slate-700 transition hover:text-slate-900">View All</a>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="grid grid-cols-1 gap-4 mt-4 xl:grid-cols-[minmax(0,1.6fr)_minmax(320px,0.8fr)]">
    <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
      <div class="flex items-center justify-between gap-2 border-b border-slate-200 pb-3">
        <div>
          <h2 class="text-base font-semibold text-slate-900">Monthly calendar</h2>
          <p class="text-sm text-slate-500">Placeholder view for planned movements and events.</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-600"><?= htmlspecialchars((string)($monthLabel ?? '')) ?></div>
      </div>

      <div class="mt-3 grid grid-cols-7 gap-2 text-center text-xs font-medium uppercase tracking-[0.2em] text-slate-500">
        <div>Sun</div>
        <div>Mon</div>
        <div>Tue</div>
        <div>Wed</div>
        <div>Thu</div>
        <div>Fri</div>
        <div>Sat</div>
      </div>

      <div class="mt-3">
        <div id="mobilizationCalendar" class="min-h-[440px]"></div>
      </div>
    </div>

    <aside class="space-y-3">
      <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
        <div class="flex items-center justify-between gap-2">
          <div>
            <h2 id="mobilizationActivityHeading" class="text-base font-semibold text-slate-900">Upcoming Movements</h2>
            <p id="mobilizationActivitySubtitle" class="mt-2 text-sm text-slate-500">Showing scheduled mobilizations and demobilizations.</p>
          </div>
        
        </div>

        <div id="mobilizationActivityList" class="mt-3 space-y-3">
          <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5 text-sm text-slate-500">Loading movements...</div>
        </div>
      </div>

    </aside>
  </div>
</div>

<div id="movementModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="movementModalTitle">
  <div id="movementModalBackdrop" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
  <div class="relative mx-auto my-4 w-[95vw] max-w-2xl rounded-2xl border border-slate-200 bg-white shadow-xl">
    <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
      <div>
        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Movement</p>
        <h2 id="movementModalTitle" class="text-lg font-semibold text-slate-900">Create movement</h2>
      </div>
      <button type="button" id="closeMovementModalBtn" class="rounded-lg border border-slate-200 p-2 text-slate-600 transition hover:bg-slate-50" aria-label="Close">✕</button>
    </div>

    <form id="movementForm" class="space-y-3 px-4 py-3">
      <div id="movementFormAlert" class="hidden rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700"></div>
      <input type="hidden" id="movementId" name="movement_id" value="">

      <div class="grid gap-3 md:grid-cols-2">
        <div class="md:col-span-2">
          <label class="mb-1 block text-sm font-medium text-slate-700">Employee</label>
          <div class="relative" id="employeePickerRoot">
            <button id="movementEmployeeToggle" type="button" role="combobox" aria-expanded="false" aria-controls="movementEmployeeDropdown" aria-haspopup="listbox" class="flex w-full items-center justify-between rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-left shadow-sm transition hover:border-slate-300">
              <div id="movementEmployeeTriggerText" class="min-w-0 flex-1">
                <div class="flex min-w-0 flex-col">
                  <span class="truncate text-sm font-medium text-slate-700">Select employee...</span>
                  <span class="mt-0.5 truncate text-xs text-slate-400">Search by name or employee ID</span>
                </div>
              </div>
              <svg xmlns="http://www.w3.org/2000/svg" class="ml-3 h-4 w-4 flex-shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <input type="hidden" id="movementEmployee" name="employee_id" value="">
            <div id="movementEmployeeDropdown" class="absolute left-0 right-0 z-20 mt-2 hidden rounded-xl border border-slate-200 bg-white shadow-sm transition-all duration-150 ease-out">
              <div class="sticky top-0 border-b border-slate-200 bg-white p-2">
                <div class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-1.5">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                  <input id="movementEmployeeSearch" type="text" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="movementEmployeeList" aria-haspopup="listbox" placeholder="Search employee..." class="w-full border-0 bg-transparent p-0 text-sm text-slate-700 outline-none" />
                </div>
              </div>
              <div id="movementEmployeeList" role="listbox" class="max-h-80 overflow-y-auto py-1"></div>
            </div>
          </div>
          <div id="error-movementEmployee" class="mt-1 hidden text-sm text-rose-600"></div>
        </div>

        <div>
          <label for="movementType" class="mb-1 block text-sm font-medium text-slate-700">Movement Type</label>
          <select id="movementType" name="movement_type" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-slate-400">
            <option value="Mobilization">Mobilization</option>
            <option value="Demobilization">Demobilization</option>
          </select>
          <div id="error-movementType" class="mt-1 hidden text-sm text-rose-600"></div>
        </div>

        <div>
          <label for="movementDate" class="mb-1 block text-sm font-medium text-slate-700">Movement Date</label>
          <input id="movementDate" name="movement_date" type="date" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-slate-400">
          <div id="error-movementDate" class="mt-1 hidden text-sm text-rose-600"></div>
        </div>

        <div class="md:col-span-2">
          <label for="movementRemarks" class="mb-1 block text-sm font-medium text-slate-700">Remarks</label>
          <textarea id="movementRemarks" name="remarks" rows="3" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-slate-400" placeholder="Optional notes"></textarea>
          <div id="error-movementRemarks" class="mt-1 hidden text-sm text-rose-600"></div>
        </div>
      </div>

      <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-3">
        <button type="button" id="cancelMovementModalBtn" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">Cancel</button>
        <button type="submit" id="movementSaveBtn" class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white transition hover:bg-slate-800">Save movement</button>
      </div>
    </form>
  </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<style>
  .fc .fc-toolbar-title { font-size: 1rem; font-weight: 600; }
  .fc .fc-button-primary {
    background: #0f172a !important;
    border-color: #0f172a !important;
    color: #ffffff !important;
  }
  .fc .fc-button-primary:not(:disabled).fc-button-active,
  .fc .fc-button-primary:not(:disabled):hover {
    background: #111827 !important;
    border-color: #111827 !important;
  }
  .fc .fc-daygrid-event {
    border-radius: 9999px;
    padding: 0.25rem 0.5rem;
    font-size: 0.69rem;
    line-height: 1.2;
  }
  .fc .fc-daygrid-event-harness {
    margin-bottom: 0.2rem;
  }
  .fc .fc-daygrid-day-number {
    font-weight: 600;
  }
</style>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const mobilizationCalendarEl = document.getElementById('mobilizationCalendar');
    const mobilizationCalendarEvents = <?= json_encode($mobilizationCalendarEvents ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const activityList = document.getElementById('mobilizationActivityList');
    const openMovementModalBtn = document.getElementById('openMovementModalBtn');
    const movementModal = document.getElementById('movementModal');
    const movementModalBackdrop = document.getElementById('movementModalBackdrop');
    const closeMovementModalBtn = document.getElementById('closeMovementModalBtn');
    const cancelMovementModalBtn = document.getElementById('cancelMovementModalBtn');
    const movementForm = document.getElementById('movementForm');
    const movementModalTitle = document.getElementById('movementModalTitle');
    const movementIdInput = document.getElementById('movementId');
    const movementEmployeeInput = document.getElementById('movementEmployee');
    const movementEmployeeToggle = document.getElementById('movementEmployeeToggle');
    const movementEmployeeTriggerText = document.getElementById('movementEmployeeTriggerText');
    const movementEmployeeSearchInput = document.getElementById('movementEmployeeSearch');
    const movementEmployeeDropdown = document.getElementById('movementEmployeeDropdown');
    const movementEmployeeList = document.getElementById('movementEmployeeList');
    const employeePickerRoot = document.getElementById('employeePickerRoot');
    const movementTypeInput = document.getElementById('movementType');
    const movementDateInput = document.getElementById('movementDate');
    const movementRemarksInput = document.getElementById('movementRemarks');
    const movementSaveBtn = document.getElementById('movementSaveBtn');
    const movementFormAlert = document.getElementById('movementFormAlert');
    const activityHeading = document.getElementById('mobilizationActivityHeading');
    const activitySubtitle = document.getElementById('mobilizationActivitySubtitle');
    const createdBy = <?= (int)($_SESSION['id'] ?? 0) ?>;
    const employeeOptions = <?= $employeesJson ?>;
    let selectedEmployee = null;
    let activeOptionIndex = -1;
    let searchTimer = null;
    let selectedDayCell = null;

    function clearFieldErrors() {
      document.querySelectorAll('[id^="error-"]').forEach(function (node) {
        node.textContent = '';
        node.classList.add('hidden');
      });
      if (movementFormAlert) {
        movementFormAlert.classList.add('hidden');
        movementFormAlert.textContent = '';
      }
    }

    function showFieldError(fieldId, message) {
      const errorNode = document.getElementById('error-' + fieldId);
      if (errorNode) {
        errorNode.textContent = message;
        errorNode.classList.remove('hidden');
      }
    }

    function escapeText(value) {
      return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    }

    function highlightMatch(value, query) {
      const text = String(value ?? '');
      const normalizedQuery = String(query ?? '').trim();
      if (!normalizedQuery) {
        return escapeText(text);
      }
      const lowerText = text.toLowerCase();
      const lowerQuery = normalizedQuery.toLowerCase();
      const matchIndex = lowerText.indexOf(lowerQuery);
      if (matchIndex === -1) {
        return escapeText(text);
      }
      return escapeText(text.slice(0, matchIndex)) + '<mark class="rounded bg-slate-100 px-0.5 text-slate-900">' + escapeText(text.slice(matchIndex, matchIndex + normalizedQuery.length)) + '</mark>' + escapeText(text.slice(matchIndex + normalizedQuery.length));
    }

    function getEmployeeById(employeeId) {
      return employeeOptions.find(function (employee) {
        return String(employee.id || employee.staff_id) === String(employeeId);
      }) || null;
    }

    function formatDisplayDate(dateString) {
      const date = new Date(dateString + 'T00:00:00');
      if (Number.isNaN(date.getTime())) {
        return dateString;
      }
      return date.toLocaleDateString(undefined, { weekday: 'long', month: 'short', day: 'numeric' });
    }

    function setSelectedCalendarDay(cell) {
      if (selectedDayCell) {
        selectedDayCell.classList.remove('border-slate-900', 'bg-slate-100');
        selectedDayCell.classList.add('border-slate-200', 'bg-slate-50/70');
      }
      selectedDayCell = cell;
      if (selectedDayCell) {
        selectedDayCell.classList.add('border-slate-900', 'bg-slate-100');
        selectedDayCell.classList.remove('border-slate-200', 'bg-slate-50/70');
      }
    }

    function updateActivityPanelHeading(date) {
      if (!activityHeading || !activitySubtitle) {
        return;
      }
      activityHeading.textContent = 'Movements for ' + formatDisplayDate(date);
      activitySubtitle.textContent = 'Showing mobilizations and demobilizations for ' + formatDisplayDate(date) + '.';
    }

    function updateTriggerContent(employee) {
      const selectedName = employee ? ((employee.firstName || '') + ' ' + (employee.lastName || '')).trim() : '';
      const selectedMeta = employee ? [employee.id || employee.staff_id || '', employee.position || ''].filter(Boolean).join(' • ') : '';
      const selectedDepartment = employee ? (employee.department || '') : '';

      movementEmployeeTriggerText.innerHTML = selectedName ? [
        '<div class="flex min-w-0 flex-col text-left">',
        '<span class="truncate text-sm font-semibold text-slate-900">' + escapeText(selectedName) + '</span>',
        '<span class="mt-0.5 truncate text-xs text-slate-500">' + escapeText(selectedMeta) + '</span>',
        (selectedDepartment ? '<span class="mt-0.5 truncate text-xs text-slate-400">' + escapeText(selectedDepartment) + '</span>' : ''),
        '</div>'
      ].join('') : [
        '<div class="flex min-w-0 flex-col text-left">',
        '<span class="truncate text-sm font-medium text-slate-700">Select employee...</span>',
        '<span class="mt-0.5 truncate text-xs text-slate-400">Search by name or employee ID</span>',
        '</div>'
      ].join('');
    }

    function setSelectedEmployee(employee) {
      selectedEmployee = employee || null;
      movementEmployeeInput.value = employee ? (employee.id || employee.staff_id || '') : '';
      movementEmployeeSearchInput.value = '';
      updateTriggerContent(employee);
    }

    function closeEmployeePicker() {
      movementEmployeeDropdown.classList.add('hidden');
      movementEmployeeDropdown.setAttribute('aria-expanded', 'false');
      movementEmployeeToggle.setAttribute('aria-expanded', 'false');
      movementEmployeeSearchInput.setAttribute('aria-expanded', 'false');
      activeOptionIndex = -1;
    }

    function openEmployeePicker() {
      movementEmployeeDropdown.classList.remove('hidden');
      movementEmployeeDropdown.setAttribute('aria-expanded', 'true');
      movementEmployeeToggle.setAttribute('aria-expanded', 'true');
      movementEmployeeSearchInput.setAttribute('aria-expanded', 'true');
      movementEmployeeSearchInput.focus();
      renderEmployeeResults(movementEmployeeSearchInput.value);
    }

    function renderEmployeeResults(query) {
      const trimmedQuery = String(query ?? '').trim();
      movementEmployeeList.innerHTML = '';

      movementEmployeeDropdown.classList.remove('hidden');
      movementEmployeeDropdown.setAttribute('aria-expanded', 'true');
      movementEmployeeToggle.setAttribute('aria-expanded', 'true');
      movementEmployeeSearchInput.setAttribute('aria-expanded', 'true');

      window.clearTimeout(searchTimer);
      searchTimer = window.setTimeout(function () {
        const filtered = employeeOptions.filter(function (employee) {
          if (!trimmedQuery) {
            return true;
          }
          const haystack = [employee.firstName, employee.lastName, employee.id, employee.staff_id, employee.position].join(' ').toLowerCase();
          return haystack.indexOf(trimmedQuery.toLowerCase()) !== -1;
        }).slice(0, 20);

        if (!filtered.length) {
          movementEmployeeList.innerHTML = '<div class="px-3 py-3 text-sm text-slate-500"><p class="font-medium text-slate-700">No employees found</p><p class="mt-1 text-xs text-slate-400">Try another employee name or employee ID.</p></div>';
          return;
        }

        movementEmployeeList.innerHTML = filtered.map(function (employee) {
          const isSelected = selectedEmployee && String(selectedEmployee.id || selectedEmployee.staff_id) === String(employee.id || employee.staff_id);
          const label = (employee.firstName || '') + ' ' + (employee.lastName || '');
          return '<button type="button" class="employee-picker-option flex w-full items-start gap-3 border-b border-slate-100 px-3 py-2 text-left transition hover:bg-slate-50 ' + (isSelected ? 'bg-emerald-50 border-emerald-200' : '') + '" role="option" aria-selected="' + (isSelected ? 'true' : 'false') + '" data-employee-id="' + escapeText(employee.id || employee.staff_id) + '">' +
            '<div class="mt-0.5 flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 bg-white text-sm font-semibold text-slate-700">' + escapeText((employee.firstName || 'E').charAt(0).toUpperCase()) + '</div>' +
            '<div class="min-w-0 flex-1">' +
            '<div class="flex items-center justify-between gap-2">' +
            '<p class="truncate text-sm font-semibold text-slate-900">' + highlightMatch(label, trimmedQuery) + '</p>' + (isSelected ? '<span class="text-[11px] font-medium text-emerald-700">✓</span>' : '') +
            '</div>' +
            '<p class="mt-1 text-xs text-slate-500">' + highlightMatch((employee.id || employee.staff_id || '') + (employee.position ? ' • ' + employee.position : ''), trimmedQuery) + '</p>' +
            '<p class="mt-1 text-xs text-slate-400">' + escapeText(employee.department || '') + '</p>' +
            '</div>' +
            '</button>';
        }).join('');
        activeOptionIndex = -1;
        movementEmployeeList.querySelectorAll('.employee-picker-option').forEach(function (optionButton) {
          optionButton.addEventListener('click', function (event) {
            event.preventDefault();
            const employeeId = optionButton.getAttribute('data-employee-id');
            const employee = getEmployeeById(employeeId);
            if (employee) {
              setSelectedEmployee(employee);
              closeEmployeePicker();
            }
          });
        });
      }, 120);
    }

    function updateActiveOption(direction) {
      const optionButtons = Array.from(movementEmployeeList.querySelectorAll('.employee-picker-option'));
      if (!optionButtons.length) {
        return;
      }
      if (activeOptionIndex < 0) {
        activeOptionIndex = direction > 0 ? 0 : optionButtons.length - 1;
      } else {
        activeOptionIndex = Math.max(0, Math.min(optionButtons.length - 1, activeOptionIndex + direction));
      }
      optionButtons.forEach(function (button, index) {
        button.classList.toggle('bg-slate-50', index === activeOptionIndex);
        button.classList.toggle('border-slate-200', index === activeOptionIndex);
      });
      optionButtons[activeOptionIndex].scrollIntoView({ block: 'nearest' });
    }

    function openMovementModal(mode, movement) {
      clearFieldErrors();
      movementForm.reset();
      movementIdInput.value = '';
      movementModalTitle.textContent = mode === 'edit' ? 'Edit movement' : 'Create movement';
      movementDateInput.value = new Date().toISOString().split('T')[0];
      movementModal.classList.remove('hidden');
      setSelectedEmployee(null);
      closeEmployeePicker();

      if (mode === 'edit' && movement) {
        movementIdInput.value = movement.id || '';
        const employee = getEmployeeById(movement.employee_id || '');
        if (employee) {
          setSelectedEmployee(employee);
        } else {
          movementEmployeeInput.value = movement.employee_id || '';
          updateTriggerContent(null);
        }
        movementTypeInput.value = movement.movement_type || 'Mobilization';
        movementDateInput.value = movement.movement_date || '';
        movementRemarksInput.value = movement.remarks || '';
      }
    }

    function closeMovementModal() {
      movementModal.classList.add('hidden');
      movementForm.reset();
      clearFieldErrors();
      movementIdInput.value = '';
      movementEmployeeInput.value = '';
      movementEmployeeSearchInput.value = '';
      setSelectedEmployee(null);
      closeEmployeePicker();
    }

    function getMovementBadgeClasses(movementType) {
      return movementType === 'Demobilization'
        ? 'bg-rose-100 text-rose-700'
        : 'bg-emerald-100 text-emerald-700';
    }

    function renderActivityItem(item) {
      const name = item.employee_name || 'Unknown employee';
      const department = item.employee_department || item.department || 'Department not set';
      const movementType = item.movement_type || 'Movement';
      const scheduleDate = item.movement_date || 'TBD';
      const movementId = item.id || '';
      const employeeId = item.employee_id || '';
      const remarks = item.remarks || '';
      const badgeClasses = getMovementBadgeClasses(movementType);

      return '<div class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm" data-movement-id="' + escapeText(movementId) + '">' +
        '<div class="flex items-start justify-between gap-2">' +
        '<div class="min-w-0">' +
        '<p class="text-sm font-semibold text-slate-900">' + escapeText(name) + '</p>' +
        '<p class="mt-1 text-xs text-slate-500">' + escapeText(department) + '</p>' +
        '</div>' +
        '<span class="rounded-full border px-2 py-1 text-[11px] font-medium ' + badgeClasses + '">' + escapeText(movementType) + '</span>' +
        '</div>' +
        '<div class="mt-3 flex items-center justify-between gap-2 text-xs text-slate-500">' +
        '<span class="font-medium text-slate-600">' + escapeText(scheduleDate) + '</span>' +
        '<button type="button" class="edit-movement-trigger rounded-md border border-slate-200 bg-white px-2.5 py-1.5 text-[11px] font-medium text-slate-600 transition hover:bg-slate-50" data-movement-id="' + escapeText(movementId) + '" data-employee-id="' + escapeText(employeeId) + '" data-movement-type="' + escapeText(movementType) + '" data-movement-date="' + escapeText(scheduleDate) + '" data-remarks="' + escapeText(remarks) + '">Edit</button>' +
        '</div>' +
        '</div>';
    }

    function getActivityGroups(items) {
      const normalizedItems = Array.isArray(items) ? items : [];
      const today = new Date();
      const tomorrow = new Date(today);
      tomorrow.setDate(today.getDate() + 1);

      const formatDateKey = function (date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return year + '-' + month + '-' + day;
      };

      const todayKey = formatDateKey(today);
      const tomorrowKey = formatDateKey(tomorrow);
      const groups = {
        today: [],
        tomorrow: [],
        upcoming: []
      };

      normalizedItems.forEach(function (item) {
        const movementDate = String(item.movement_date || '').slice(0, 10);
        if (movementDate === todayKey) {
          groups.today.push(item);
        } else if (movementDate === tomorrowKey) {
          groups.tomorrow.push(item);
        } else if (movementDate > tomorrowKey) {
          groups.upcoming.push(item);
        } else {
          groups.upcoming.push(item);
        }
      });

      return groups;
    }

    function renderActivityPanel(items) {
      const groups = getActivityGroups(items);
      const sections = [
        { key: 'today', title: 'Today', items: groups.today },
        { key: 'tomorrow', title: 'Tomorrow', items: groups.tomorrow },
        { key: 'upcoming', title: 'This Week', items: groups.upcoming }
      ];

      const content = sections.map(function (section) {
        if (!section.items.length) {
          return '';
        }

        const renderedItems = section.items.map(function (item) {
          return renderActivityItem(item);
        }).join('');

        return '<div class="space-y-2">' +
          '<div class="flex items-center justify-between">' +
          '<h3 class="text-sm font-semibold text-slate-800">' + escapeText(section.title) + '</h3>' +
          '<span class="rounded-full bg-slate-100 px-2 py-1 text-[11px] font-medium text-slate-600">' + section.items.length + '</span>' +
          '</div>' +
          '<div class="space-y-2">' + renderedItems + '</div>' +
          '</div>';
      }).join('');

      if (!content) {
        activityList.innerHTML = '<div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm text-slate-500">No movements available.</div>';
        return;
      }

      activityList.innerHTML = content;
    }

    function refreshActivityPanel() {
      if (!activityList) {
        return;
      }

      activityList.innerHTML = '<div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm text-slate-500">Loading activity...</div>';

      Promise.all([
        fetch('index.php?controller=EmployeeMobilization&action=today', {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        }),
        fetch('index.php?controller=EmployeeMobilization&action=upcoming', {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        })
      ])
        .then(function (responses) {
          return Promise.all(responses.map(function (response) {
            return response.json();
          }));
        })
        .then(function (payloads) {
          const todayPayload = payloads[0] || {};
          const upcomingPayload = payloads[1] || {};
          const todayItems = Array.isArray(todayPayload.movements) ? todayPayload.movements : [];
          const upcomingItems = Array.isArray(upcomingPayload.movements) ? upcomingPayload.movements : [];
          const combinedItems = [];
          const seenIds = new Set();

          [todayItems, upcomingItems].forEach(function (items) {
            items.forEach(function (item) {
              const itemId = item.id || (item.movement_date || '') + '|' + (item.employee_id || '') + '|' + (item.movement_type || '');
              if (seenIds.has(itemId)) {
                return;
              }
              seenIds.add(itemId);
              combinedItems.push(item);
            });
          });

          renderActivityPanel(combinedItems);
        })
        .catch(function () {
          activityList.innerHTML = '<div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm text-slate-500">Unable to load activity.</div>';
        });
    }

    if (openMovementModalBtn) {
      openMovementModalBtn.addEventListener('click', function () {
        openMovementModal('create', null);
      });
    }

    [closeMovementModalBtn, cancelMovementModalBtn, movementModalBackdrop].forEach(function (element) {
      if (element) {
        element.addEventListener('click', function () {
          closeMovementModal();
        });
      }
    });

    movementEmployeeToggle.addEventListener('click', function () {
      if (movementEmployeeDropdown.classList.contains('hidden')) {
        openEmployeePicker();
      } else {
        closeEmployeePicker();
      }
    });

    movementEmployeeSearchInput.addEventListener('focus', function () {
      openEmployeePicker();
    });

    movementEmployeeSearchInput.addEventListener('input', function () {
      renderEmployeeResults(this.value);
    });

    movementEmployeeSearchInput.addEventListener('keydown', function (event) {
      const optionButtons = Array.from(movementEmployeeList.querySelectorAll('.employee-picker-option'));
      if (!optionButtons.length) {
        return;
      }
      if (event.key === 'ArrowDown') {
        event.preventDefault();
        updateActiveOption(1);
      } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        updateActiveOption(-1);
      } else if (event.key === 'Enter') {
        event.preventDefault();
        if (activeOptionIndex >= 0 && optionButtons[activeOptionIndex]) {
          optionButtons[activeOptionIndex].click();
        }
      } else if (event.key === 'Escape') {
        event.preventDefault();
        closeEmployeePicker();
      }
    });

    document.addEventListener('click', function (event) {
      if (!employeePickerRoot.contains(event.target)) {
        closeEmployeePicker();
      }
    });

    if (activityList) {
      activityList.addEventListener('click', function (event) {
        const button = event.target.closest('.edit-movement-trigger');
        if (!button) {
          return;
        }

        const movement = {
          id: button.getAttribute('data-movement-id') || '',
          employee_id: button.getAttribute('data-employee-id') || '',
          movement_type: button.getAttribute('data-movement-type') || 'Mobilization',
          movement_date: button.getAttribute('data-movement-date') || '',
          remarks: button.getAttribute('data-remarks') || ''
        };
        openMovementModal('edit', movement);
      });
    }

    if (movementForm) {
      movementForm.addEventListener('submit', function (event) {
        event.preventDefault();
        clearFieldErrors();

        const employeeId = movementEmployeeInput.value.trim();
        const movementType = movementTypeInput.value.trim();
        const movementDate = movementDateInput.value.trim();
        const remarks = movementRemarksInput.value.trim();
        let hasErrors = false;

        if (!employeeId) {
          showFieldError('movementEmployee', 'Please select an employee.');
          hasErrors = true;
        }

        if (!movementType) {
          showFieldError('movementType', 'Please select a movement type.');
          hasErrors = true;
        }

        if (!movementDate) {
          showFieldError('movementDate', 'Please choose a movement date.');
          hasErrors = true;
        }

        if (hasErrors) {
          return;
        }

        movementSaveBtn.disabled = true;
        movementSaveBtn.textContent = 'Saving...';

        const action = movementType === 'Demobilization' ? 'scheduleDemobilization' : 'scheduleMobilization';
        const params = new URLSearchParams({
          employee_id: employeeId,
          movement_date: movementDate,
          remarks: remarks,
          created_by: createdBy,
          movement_id: movementIdInput.value || ''
        });

        fetch('index.php?controller=EmployeeMobilization&action=' + action, {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
          },
          body: params.toString()
        })
          .then(function (response) {
            return response.json();
          })
          .then(function (payload) {
            if (!payload || !payload.success) {
              const message = payload && payload.message ? payload.message : 'Unable to save movement.';
              movementFormAlert.textContent = message;
              movementFormAlert.classList.remove('hidden');
              return;
            }

            closeMovementModal();
            refreshActivityPanel();
          })
          .catch(function () {
            movementFormAlert.textContent = 'Unable to save movement right now.';
            movementFormAlert.classList.remove('hidden');
          })
          .finally(function () {
            movementSaveBtn.disabled = false;
            movementSaveBtn.textContent = 'Save movement';
          });
      });
    }

    if (!activityList) {
      refreshActivityPanel();
      return;
    }

    refreshActivityPanel();

    if (mobilizationCalendarEl && typeof FullCalendar !== 'undefined') {
      const mobilizationCalendar = new FullCalendar.Calendar(mobilizationCalendarEl, {
        initialView: 'dayGridMonth',
        themeSystem: 'standard',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth'
        },
        dayMaxEvents: 2,
        events: mobilizationCalendarEvents,
        eventDisplay: 'block',
        eventContent: function (arg) {
          return { html: '<div class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-current"></span><span class="fc-event-title">' + escapeText(arg.event.title) + '</span></div>' };
        },
        eventClick: function (info) {
          const event = info.event;
          const props = event.extendedProps || {};
          const movement = {
            id: props.movement_id || event.id,
            employee_id: props.employee_id || '',
            movement_type: props.movement_type || '',
            movement_date: event.startStr || '',
            remarks: props.remarks || ''
          };
          openMovementModal('edit', movement);
        }
      });
      mobilizationCalendar.render();
    }
  });
</script>
