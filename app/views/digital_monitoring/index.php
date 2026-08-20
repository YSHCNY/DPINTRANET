<?php
$toneClasses = [
  'emerald' => ['dot' => 'bg-emerald-400', 'value' => 'text-emerald-300'],
  'sky' => ['dot' => 'bg-sky-400', 'value' => 'text-sky-300'],
  'amber' => ['dot' => 'bg-amber-400', 'value' => 'text-amber-300'],
];
$statusClasses = [
  'Available' => 'border-emerald-800 bg-emerald-950/60 text-emerald-300',
  'On trip' => 'border-sky-800 bg-sky-950/60 text-sky-300',
  'In use' => 'border-sky-800 bg-sky-950/60 text-sky-300',
  'Upcoming' => 'border-amber-800 bg-amber-950/60 text-amber-300',
];
$vehicleStatusClasses = [
  'Available' => 'border-emerald-800 bg-emerald-950/60 text-emerald-300',
  'On trip' => 'border-sky-800 bg-sky-950/60 text-sky-300',
  'Upcoming' => 'border-amber-800 bg-amber-950/60 text-amber-300',
];
$roomStatusClasses = [
  'Available' => 'border-green-800 bg-green-950/60 text-green-300',
  'In use' => 'border-sky-800 bg-sky-950/60 text-sky-300',
  'Upcoming' => 'border-amber-800 bg-amber-950/60 text-amber-300',
];
$maxVisibleRecords = 5;
$panels = ['vehicles' => 'Vehicles', 'rooms' => 'Rooms', 'workforce' => 'Workforce'];
$panelData = [];
$capacity = 6;
$rotationFrameSizes = ['vehicles' => 5, 'rooms' => 5, 'workforce' => 6];
foreach ($panels as $panelKey => $panelTitle) {
  $panelSection = is_array($snapshot[$panelKey] ?? null) ? $snapshot[$panelKey] : ['summary' => [], 'display' => []];
  $display = is_array($panelSection['display'] ?? null) ? $panelSection['display'] : [];
  $priority = is_array($display['priority'] ?? null) ? $display['priority'] : [];
  $secondaryRecords = array_merge(
    is_array($display['secondary'] ?? null) ? $display['secondary'] : [],
    is_array($display['overflow'] ?? null) ? $display['overflow'] : []
  );
  $customFrames = is_array($display['workforceFrames'] ?? null) ? $display['workforceFrames'] : [];
  $frameCapacity = min($capacity, $rotationFrameSizes[$panelKey] ?? $capacity);
  $frameSummaries = [];
  $pageGroups = [];
  if ($panelKey === 'workforce' && $customFrames !== []) {
    $frames = [];
    foreach ($customFrames as $customFrame) {
      $customSummary = is_array($customFrame['summary'] ?? null) ? $customFrame['summary'] : [];
      $customColumns = is_array($customFrame['columns'] ?? null) ? $customFrame['columns'] : [];
      $groupFrames = [];
      if ($customColumns !== []) {
        $columnFrames = [];
        foreach ($customColumns as $columnName => $columnRecords) {
          $columnFrames[$columnName] = array_chunk(is_array($columnRecords) ? $columnRecords : [], $frameCapacity);
        }
        $pageCount = max(array_map(static fn (array $columnPages): int => count($columnPages), $columnFrames));
        foreach ($columnFrames as $columnName => $columnPages) {
          $columnFrames[$columnName] = array_pad($columnPages, $pageCount, []);
        }
        $groupFrames = array_fill(0, $pageCount, []);
      } else {
        $customRecords = is_array($customFrame['records'] ?? null) ? $customFrame['records'] : [];
        $groupFrames = array_chunk($customRecords, $frameCapacity);
      }
      if ($groupFrames === []) {
        $groupFrames[] = [];
      }
      $pageGroups[] = ['summary' => $customSummary, 'columns' => $customColumns !== [] ? $columnFrames : [], 'frames' => $groupFrames];
      foreach ($groupFrames as $recordFrame) {
        $frames[] = $recordFrame;
        $frameSummaries[] = $customSummary;
      }
    }
  } else {
    $frames = array_chunk($priority, $frameCapacity);
  }
  if (!($panelKey === 'workforce' && $customFrames !== [])) {
    if ($frames === []) {
      $frames[] = [];
    }
    $remainingSlots = $frameCapacity - count($frames[count($frames) - 1]);
    if ($remainingSlots > 0 && $secondaryRecords !== []) {
      $frames[count($frames) - 1] = array_merge($frames[count($frames) - 1], array_slice($secondaryRecords, 0, $remainingSlots));
      $secondaryRecords = array_slice($secondaryRecords, $remainingSlots);
    }
    foreach (array_chunk($secondaryRecords, $frameCapacity) as $secondaryFrame) {
      $frames[] = $secondaryFrame;
    }
  }
  $panelData[$panelKey] = [
    'summary' => is_array($panelSection['summary'] ?? null) ? $panelSection['summary'] : [],
    'priority' => $priority,
    'frames' => $frames,
    'hasOverflow' => $panelKey === 'workforce'
      ? count($pageGroups) > 0 && array_sum(array_map(static fn (array $group): int => count($group['frames'] ?? []), $pageGroups)) > 1
      : count($frames) > 1,
    'frameSummaries' => $frameSummaries,
    'pageGroups' => $pageGroups,
  ];
}
?>
<style>
  @keyframes monitoring-status-counter-clockwise {
    from { transform: rotate(0deg); }
    to { transform: rotate(-360deg); }
  }

  .monitoring-status-dot-active {
    animation: monitoring-status-counter-clockwise 1.8s linear infinite;
  }

  .monitoring-workforce-timeline[data-density="compact"] .monitoring-workforce-event {
    padding-top: 0.55rem;
    padding-bottom: 0.55rem;
  }

  .monitoring-workforce-timeline[data-density="compact"] .monitoring-workforce-event-name {
    font-size: 1.25rem;
    line-height: 1.25;
  }

  .monitoring-workforce-timeline[data-density="compact"] .monitoring-workforce-section-title {
    padding-top: 0.65rem;
    padding-bottom: 0.65rem;
    font-size: 1rem;
  }

  .monitoring-workforce-timeline[data-density="compact"] .monitoring-workforce-date {
    margin-top: 0.9rem;
  }

  @media (max-height: 800px) {
    .monitoring-workforce-timeline .monitoring-workforce-event {
      padding-top: 0.55rem;
      padding-bottom: 0.55rem;
    }

    .monitoring-workforce-timeline .monitoring-workforce-event-name {
      font-size: 1.25rem;
      line-height: 1.25;
    }
  }

  @media (prefers-reduced-motion: reduce) {
    .monitoring-status-dot-active { animation: none; }
  }
</style>
<div class="min-h-[calc(100vh)] bg-[#0b1720] text-slate-100">
  <main class="mx-auto flex min-h-[calc(100vh-5rem)] max-w-[1800px] flex-col px-4 py-5 sm:px-6 lg:px-8 lg:py-6">
    <header class="flex flex-col justify-between gap-4 border-b border-slate-700/80 pb-5 md:flex-row md:items-end">
      <div>
        <div class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.24em] text-teal-300"><span class="h-2 w-2 rounded-full bg-teal-400"></span>Digital Operations Center</div>
        <h2 class="mt-2 text-3xl font-semibold tracking-tight text-white sm:text-4xl">Operational state</h2>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-400">Fleet, room, and workforce activity at a glance.</p>
      </div>
      <div class="flex items-start gap-5 self-start md:self-auto">
        <div id="monitoringStateLabel" class="pt-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-teal-300">Today's Operations</div>
        <div id="monitoringRefreshStatus" class="pt-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500" role="status" aria-live="polite">Live data ready</div>
        <time class="text-right" datetime="" aria-label="Current date and time">
          <span id="monitoringClock" class="block text-3xl font-semibold leading-none tracking-tight text-white tabular-nums"></span>
          <span id="monitoringDate" class="mt-1 block text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500"></span>
        </time>
      </div>
    </header>

    <section id="monitoringStateGrid" class="monitoring-state-grid mt-5 grid flex-1 grid-cols-1 gap-4 lg:grid-cols-2" aria-label="Operational monitoring sections">
      <?php foreach (['vehicles' => 'Vehicles', 'rooms' => 'Rooms', 'workforce' => 'Workforce'] as $key => $title): ?>
        <?php $section = is_array($snapshot[$key] ?? null) ? $snapshot[$key] : ['summary' => [], 'items' => []]; ?>
        <?php $availableMetric = array_values(array_filter($section['summary'], static fn (array $metric): bool => ($metric['label'] ?? '') === 'Available'))[0] ?? ['value' => 0]; ?>
        <article class="monitoring-panel <?= $key === 'workforce' ? 'hidden' : '' ?> flex h-[calc(100vh-12rem)] min-h-0 min-w-0 flex-col overflow-hidden rounded-xl border border-slate-700 bg-[#12232d] shadow-[0_14px_34px_-20px_rgba(0,0,0,0.8)]" data-monitoring-panel="<?= $key ?>" data-monitoring-state="<?= $key === 'workforce' ? 'workforce' : 'today' ?>" aria-labelledby="<?= $key ?>Heading">
          <div class="shrink-0 border-b border-slate-800 px-5 py-4">
              <div class="flex min-w-0 items-center justify-between gap-4"><div class="min-w-0"><h2 id="<?= $key ?>Heading" class="truncate p-2 text-2xl font-semibold uppercase tracking-[0.08em] text-white"><?= $title ?></h2><?php if ($key === 'workforce'): ?><p class="monitoring-workforce-state-title mt-1 px-2 text-sm font-bold uppercase tracking-[0.14em] text-teal-300">Today's Workforce Movement</p><?php endif; ?></div><?php if ($key !== 'workforce'): ?><p class="monitoring-header-summary shrink-0 text-right text-sm font-semibold uppercase tracking-[0.08em] text-slate-400 sm:text-base"><?= htmlspecialchars((string)($availableMetric['value'] ?? 0)) ?> AVAILABLE</p><?php else: ?><p class="monitoring-workforce-header-summary shrink-0 text-right text-sm font-semibold uppercase tracking-[0.08em] text-slate-300 sm:text-base"></p><?php endif; ?></div>
           <?php $summaryCount = count($section['summary']); ?>

<?php if ($key === 'workforce'): ?><div class="monitoring-summary mt-5 grid w-full <?= 
    match ($summaryCount) {
        1 => 'grid-cols-1',
        2 => 'grid-cols-2',
        3 => 'grid-cols-3',
        default => 'grid-cols-2',
    }
?> divide-x divide-slate-700">
              <?php foreach ($section['summary'] as $metric): ?>
                <?php $tone = $toneClasses[$metric['tone'] ?? 'emerald']; ?>
                  <div class="min-w-0 w-full px-3 first:pl-0 last:pr-0"><div class="flex min-w-0 items-center gap-1.5"><span class="h-2 w-2 shrink-0 rounded-full <?= $tone['dot'] ?>"></span><span class="truncate text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-400"><?= htmlspecialchars((string)($metric['label'] ?? '')) ?></span></div><p class="mt-1 text-3xl font-semibold <?= $tone['value'] ?>"><?= htmlspecialchars((string)($metric['value'] ?? 0)) ?></p></div>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </div>
          <div class="monitoring-records min-h-0 flex-1 divide-y divide-slate-700/80 overflow-hidden" aria-live="polite">
            <?php $visibleItems = array_slice(is_array($section['items']) ? $section['items'] : [], 0, $maxVisibleRecords); ?>
            <?php if (empty($visibleItems)): ?><div class="px-5 py-10 text-center text-sm text-slate-500">No current records</div><?php endif; ?>
            <?php foreach ($visibleItems as $item): ?>
              <div class="min-w-0 px-5 py-3.5">
                <?php if ($key === 'vehicles'): ?>
                  <?php $vehicleStatus = (string)($item['status'] ?? 'Available'); ?>
                  <?php $vehicleStatusLabel = match ($vehicleStatus) { 'On trip' => 'ON TRIP', 'In use' => 'IN USE', 'Upcoming' => 'UPCOMING', default => 'AVAILABLE' }; ?>
                  <div class="flex min-w-0 items-center justify-between gap-5"><div class="min-w-0"><p class="truncate text-2xl font-semibold leading-tight text-white sm:text-3xl"><?= htmlspecialchars((string)($item['vehicle_name'] ?? 'Vehicle')) ?></p><p class="mt-1 truncate text-base text-slate-300 sm:text-lg"><?= htmlspecialchars((string)($item['plate_number'] ?? 'Unassigned plate')) ?> · <?= htmlspecialchars((string)($item['driver'] ?? 'Unassigned')) ?><?= $vehicleStatus === 'Available' || empty($item['context'] ?? '') ? '' : ' · ' . htmlspecialchars((string)$item['context']) ?></p></div><div class="flex shrink-0 flex-col items-end gap-1"><p class="text-xl font-bold leading-tight tabular-nums text-teal-200 sm:text-2xl"><?= $vehicleStatus === 'Available' ? 'Ready now' : htmlspecialchars((string)($item['time_range'] ?? 'No scheduled time')) ?></p><span class="flex items-center gap-1.5 rounded-full border px-2 py-1 text-[10px] font-bold tracking-[0.08em] <?= $vehicleStatusClasses[$vehicleStatus] ?? $vehicleStatusClasses['Available'] ?>"><span class="h-2.5 w-2.5 rounded-full <?= in_array($vehicleStatus, ['On trip', 'In use'], true) ? 'border-2 border-current border-r-transparent monitoring-status-dot-active' : 'bg-current' ?>" aria-hidden="true"></span><?= $vehicleStatusLabel ?></span></div></div>
                <?php elseif ($key === 'rooms'): ?>
                  <?php $roomStatus = (string)($item['status'] ?? 'Available'); ?>
                  <?php $roomStatusLabel = match ($roomStatus) { 'On trip' => 'ON TRIP', 'In use' => 'IN USE', 'Upcoming' => 'UPCOMING', default => 'AVAILABLE' }; ?>
                  <div class="flex min-w-0 items-center justify-between gap-5"><div class="min-w-0"><p class="truncate text-2xl font-semibold leading-tight text-white sm:text-3xl"><?= htmlspecialchars((string)($item['room_name'] ?? 'Room')) ?></p><p class="mt-1 truncate text-base text-slate-300 sm:text-lg"><?= htmlspecialchars((string)($item['room_code'] ?? 'Room')) ?> · <?= htmlspecialchars((string)($item['capacity'] ?? '')) ?><?= $roomStatus === 'Available' || empty($item['context'] ?? '') ? '' : ' · ' . htmlspecialchars((string)$item['context']) ?></p></div><div class="flex shrink-0 flex-col items-end gap-1"><p class="text-xl font-bold leading-tight tabular-nums text-teal-200 sm:text-2xl"><?= $roomStatus === 'Available' ? 'Ready now' : htmlspecialchars((string)($item['time_range'] ?? 'No scheduled time')) ?></p><span class="flex items-center gap-1.5 rounded-full border px-2 py-1 text-[10px] font-bold tracking-[0.08em] <?= $roomStatusClasses[$roomStatus] ?? $roomStatusClasses['Available'] ?>"><span class="h-2.5 w-2.5 rounded-full <?= in_array($roomStatus, ['On trip', 'In use'], true) ? 'border-2 border-current border-r-transparent monitoring-status-dot-active' : 'bg-current' ?>" aria-hidden="true"></span><?= $roomStatusLabel ?></span></div></div>
                <?php else: ?>
                  <?php $workforceStatus = (string)($item['status'] ?? 'Mobilizing'); ?>
                  <div class="flex min-w-0 items-center justify-between gap-3">
                    <div class="min-w-0">
                      <p class="truncate font-semibold text-slate-100"><?= htmlspecialchars((string)($item['employee_name'] ?? 'Employee')) ?></p>
                      <p class="mt-1 truncate text-base text-slate-300"><?= htmlspecialchars((string)($item['staff_identifier'] ?? 'Unassigned ID')) ?> · <?= htmlspecialchars((string)($item['department'] ?? 'Unassigned department')) ?></p>
                    </div>
                    <span class="shrink-0 rounded-full border px-2 py-1 text-[10px] font-semibold <?= str_contains($workforceStatus, 'Demobilizing') ? 'border-amber-800 bg-amber-950/60 text-amber-300' : 'border-sky-800 bg-sky-950/60 text-sky-300' ?>"><?= htmlspecialchars(str_replace('Upcoming ', '', $workforceStatus)) ?></span>
                  </div>
                  <p class="mt-2 truncate text-xs text-slate-400"><?= htmlspecialchars((string)($item['date'] ?? 'Date unavailable')) ?></p>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </section>
  </main>
</div>

<script>
  (() => {
    const config = {
      defaultDurationMs: 15000,
      panels: {
        vehicles: { durationMs: 15000 },
        rooms: { durationMs: 15000 },
        workforce: { durationMs: 15000 },
      },
    };
    const data = <?= json_encode($panelData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const refreshConfig = {
      intervalMs: 30000,
      endpoint: 'index.php?controller=DigitalMonitoring&action=snapshotAjax',
    };
    const refreshStatus = document.getElementById('monitoringRefreshStatus');
    const clockElement = document.getElementById('monitoringClock');
    const dateElement = document.getElementById('monitoringDate');
    const clockTime = document.querySelector('time[aria-label="Current date and time"]');
    let refreshInFlight = false;
    let refreshTimer = null;
    const updateClock = () => {
      const now = new Date();
      if (clockElement) clockElement.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
      if (dateElement) dateElement.textContent = now.toLocaleDateString([], { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
      if (clockTime) clockTime.dateTime = now.toISOString();
    };
    updateClock();
    window.setInterval(updateClock, 1000);
    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[character]));
    const statusClass = (status) => ({
      Available: 'border-emerald-800 bg-emerald-950/60 text-emerald-300',
      'On trip': 'border-sky-800 bg-sky-950/60 text-sky-300',
      'In use': 'border-sky-800 bg-sky-950/60 text-sky-300',
      Upcoming: 'border-amber-800 bg-amber-950/60 text-amber-300',
      Mobilizing: 'border-sky-800 bg-sky-950/60 text-sky-300',
      Demobilizing: 'border-amber-800 bg-amber-950/60 text-amber-300',
      'Upcoming Demobilizing': 'border-amber-800 bg-amber-950/60 text-amber-300',
      'Upcoming Mobilizing': 'border-sky-800 bg-sky-950/60 text-sky-300',
    }[status] || 'border-slate-700 bg-slate-800 text-slate-300');
    const vehicleStatusClass = (status) => ({
      Available: 'border-emerald-800 bg-emerald-950/60 text-emerald-300',
      'On trip': 'border-sky-800 bg-sky-950/60 text-sky-300',
      Upcoming: 'border-amber-800 bg-amber-950/60 text-amber-300',
    }[status] || statusClass(status));
    const roomStatusClass = (status) => ({
      Available: 'border-green-800 bg-green-950/60 text-green-300',
      'In use': 'border-sky-800 bg-sky-950/60 text-sky-300',
      Upcoming: 'border-amber-800 bg-amber-950/60 text-amber-300',
    }[status] || statusClass(status));
    const compactStatus = (status) => ({ Available: 'AVAILABLE', 'On trip': 'ON TRIP', 'In use': 'IN USE', Upcoming: 'UPCOMING' }[status] || 'AVAILABLE');
    const statusIndicator = (status) => ['On trip', 'In use'].includes(status) ? 'border-2 border-current border-r-transparent monitoring-status-dot-active' : 'bg-current';
    const workforceStatusLabel = (status) => String(status || 'Mobilizing').replace('Upcoming ', '');
    const workforceDateLabel = (date) => String(date || 'Date unavailable').replace(/, [0-9]{4}$/, '');
    const availabilityMarkup = (summary) => `${escapeHtml(summary.find((metric) => metric.label === 'Available')?.value ?? 0)} AVAILABLE`;
    const markup = (key, item) => {
      if (key === 'vehicles') return `<div class="min-w-0 px-5 py-3.5"><div class="flex min-w-0 items-center justify-between gap-5"><div class="min-w-0"><p class="truncate text-2xl font-semibold leading-tight text-white sm:text-3xl">${escapeHtml(item.vehicle_name || 'Vehicle')}</p><p class="mt-1 truncate text-base text-slate-300 sm:text-lg">${escapeHtml(item.plate_number || 'Unassigned plate')} · ${escapeHtml(item.driver || 'Unassigned')}${item.status === 'Available' || !item.context ? '' : ` · ${escapeHtml(item.context)}`}</p></div><div class="flex shrink-0 flex-col items-end gap-1"><p class="text-xl font-bold leading-tight tabular-nums text-teal-200 sm:text-2xl">${item.status === 'Available' ? 'Ready now' : escapeHtml(item.time_range || 'No scheduled time')}</p><span class="flex items-center gap-1.5 rounded-full border px-2 py-1 text-[10px] font-bold tracking-[0.08em] ${vehicleStatusClass(item.status)}"><span class="h-2.5 w-2.5 rounded-full ${statusIndicator(item.status)}" aria-hidden="true"></span>${compactStatus(item.status)}</span></div></div></div>`;
      if (key === 'rooms') return `<div class="min-w-0 px-5 py-3.5"><div class="flex min-w-0 items-center justify-between gap-5"><div class="min-w-0"><p class="truncate text-2xl font-semibold leading-tight text-white sm:text-3xl">${escapeHtml(item.room_name || 'Room')}</p><p class="mt-1 truncate text-base text-slate-300 sm:text-lg">${escapeHtml(item.room_code || 'Room')} · ${escapeHtml(item.capacity || '')}${item.status === 'Available' || !item.context ? '' : ` · ${escapeHtml(item.context)}`}</p></div><div class="flex shrink-0 flex-col items-end gap-1"><p class="text-xl font-bold leading-tight tabular-nums text-teal-200 sm:text-2xl">${item.status === 'Available' ? 'Ready now' : escapeHtml(item.time_range || 'No scheduled time')}</p><span class="flex items-center gap-1.5 rounded-full border px-2 py-1 text-[10px] font-bold tracking-[0.08em] ${roomStatusClass(item.status)}"><span class="h-2.5 w-2.5 rounded-full ${statusIndicator(item.status)}" aria-hidden="true"></span>${compactStatus(item.status)}</span></div></div></div>`;
      return `<div class="min-w-0 px-5 py-4"><div class="flex min-w-0 items-center justify-between gap-3"><div class="min-w-0"><p class="truncate font-semibold text-slate-100">${escapeHtml(item.employee_name || 'Employee')}</p><p class="mt-1 truncate text-base text-slate-300">${escapeHtml(item.staff_identifier || 'Unassigned ID')} · ${escapeHtml(item.department || 'Unassigned department')}</p></div><span class="shrink-0 rounded-full border px-2 py-1 text-[10px] font-semibold ${statusClass(item.status)}">${escapeHtml(workforceStatusLabel(item.status))}</span></div><p class="mt-2 truncate text-base text-slate-300">${escapeHtml(workforceDateLabel(item.date))}</p></div>`;
    };
    const buildPanelData = (snapshot) => {
      const result = {};
      Object.keys(config.panels).forEach((key) => {
        const section = snapshot?.[key] || {};
        const display = section.display || {};
        const priority = Array.isArray(display.priority) ? display.priority : [];
        const secondary = [...(Array.isArray(display.secondary) ? display.secondary : []), ...(Array.isArray(display.overflow) ? display.overflow : [])];
        const frameCapacity = 5;
        let frames = [];
        let frameSummaries = [];
        let pageGroups = [];
        const customFrames = key === 'workforce' && Array.isArray(display.workforceFrames) ? display.workforceFrames : [];
        if (customFrames.length) {
          pageGroups = customFrames.map((frame) => {
            const records = Array.isArray(frame.records) ? frame.records : [];
            const columns = frame.columns && typeof frame.columns === 'object' ? frame.columns : null;
            if (columns) {
              const columnFrames = {};
              Object.entries(columns).forEach(([column, columnRecords]) => {
                const recordsForColumn = Array.isArray(columnRecords) ? columnRecords : [];
                const pageTotal = Math.ceil(recordsForColumn.length / frameCapacity) || 1;
                columnFrames[column] = Array.from({ length: pageTotal }, (_, index) => recordsForColumn.slice(index * frameCapacity, (index + 1) * frameCapacity));
              });
              const pageCount = Math.max(1, ...Object.values(columnFrames).map((columnPages) => columnPages.length));
              Object.keys(columnFrames).forEach((column) => { while (columnFrames[column].length < pageCount) columnFrames[column].push([]); });
              const groupFrames = Array.from({ length: pageCount }, () => []);
              return { summary: Array.isArray(frame.summary) ? frame.summary : [], frames: groupFrames, columns: columnFrames };
            }
            const groupFrames = [];
            for (let index = 0; index < records.length || index === 0; index += frameCapacity) {
              groupFrames.push(records.slice(index, index + frameCapacity));
              if (records.length === 0) break;
            }
            return { summary: Array.isArray(frame.summary) ? frame.summary : [], frames: groupFrames, columns: null };
          });
          pageGroups.forEach((group) => group.frames.forEach((frame) => {
            frames.push(frame);
            frameSummaries.push(group.summary);
          }));
        } else {
          frames = [];
          for (let index = 0; index < priority.length; index += frameCapacity) frames.push(priority.slice(index, index + frameCapacity));
          if (!frames.length) frames.push([]);
          let remaining = Math.max(0, frameCapacity - frames[frames.length - 1].length);
          if (remaining && secondary.length) {
            frames[frames.length - 1] = frames[frames.length - 1].concat(secondary.splice(0, remaining));
          }
          for (let index = 0; index < secondary.length; index += frameCapacity) frames.push(secondary.slice(index, index + frameCapacity));
        }
        const hasOverflow = key === 'workforce' && pageGroups.length
          ? pageGroups.reduce((total, group) => total + (group.frames?.length || 0), 0) > 1
          : frames.length > 1;
        result[key] = { summary: Array.isArray(section.summary) ? section.summary : [], priority, frames, frameSummaries, pageGroups, hasOverflow };
      });
      return result;
    };
    const createPanel = (key) => {
      const panel = document.querySelector(`[data-monitoring-panel="${key}"]`);
      if (!panel || !data[key]) return null;
      let panelData = data[key];
      let frameIndex = 0;
      let destroyed = false;
      const records = panel.querySelector('.monitoring-records');
      const durationMs = Number(config.panels[key]?.durationMs || config.defaultDurationMs);
      let controls = null;
      let indicator = null;
      let donut = null;
      let donutProgress = null;
      const panelPageCount = () => key === 'workforce' && Array.isArray(panelData.pageGroups) && panelData.pageGroups.length
        ? panelData.pageGroups.reduce((total, group) => total + Math.max(1, group.frames?.length || 0), 0)
        : Math.max(1, panelData.frames?.length || 0);
      const workforcePageAt = (page) => {
        let remaining = page;
        for (let groupIndex = 0; groupIndex < panelData.pageGroups.length; groupIndex++) {
          const group = panelData.pageGroups[groupIndex];
          const pageCount = Math.max(1, group.frames?.length || 0);
          if (remaining < pageCount) return { group, groupIndex, page: remaining };
          remaining -= pageCount;
        }
        const groupIndex = Math.max(0, panelData.pageGroups.length - 1);
        const group = panelData.pageGroups[groupIndex] || { summary: [], frames: [[]] };
        return { group, groupIndex, page: Math.max(0, (group.frames?.length || 1) - 1) };
      };
      const ensureControls = () => {
        if (controls) return;
        const heading = panel.querySelector('h2');
        controls = document.createElement('div');
        controls.className = key === 'workforce' ? 'monitoring-controls flex shrink-0 items-center gap-2' : 'monitoring-controls mt-0.5 flex items-center gap-1 text-[10px] text-slate-500';
        controls.innerHTML = key === 'workforce'
          ? '<span class="monitoring-frame-indicator text-xs font-semibold text-slate-400" aria-live="polite"></span><svg class="monitoring-donut h-6 w-6" viewBox="0 0 36 36" role="img" aria-label="Frame duration remaining"><circle cx="18" cy="18" r="15.5" fill="none" stroke="rgb(51 65 85)" stroke-width="3"></circle><circle class="monitoring-donut-progress" cx="18" cy="18" r="15.5" fill="none" stroke="rgb(45 212 191)" stroke-width="3" stroke-linecap="round" pathLength="100" stroke-dasharray="100 100" stroke-dashoffset="0" transform="rotate(-90 18 18)"></circle></svg>'
          : '<span class="monitoring-frame-indicator text-[10px] font-medium" aria-live="polite"></span><svg class="monitoring-donut h-4 w-4" viewBox="0 0 36 36" role="img" aria-label="Frame duration remaining"><circle cx="18" cy="18" r="15.5" fill="none" stroke="rgb(51 65 85)" stroke-width="3"></circle><circle class="monitoring-donut-progress" cx="18" cy="18" r="15.5" fill="none" stroke="rgb(45 212 191)" stroke-width="3" stroke-linecap="round" pathLength="100" stroke-dasharray="100 100" stroke-dashoffset="0" transform="rotate(-90 18 18)"></circle></svg>';
        heading.parentElement.appendChild(controls);
        indicator = controls.querySelector('.monitoring-frame-indicator');
        donut = controls.querySelector('.monitoring-donut');
        donutProgress = controls.querySelector('.monitoring-donut-progress');
      };
      const render = () => {
        const frames = Array.isArray(panelData.frames) && panelData.frames.length ? panelData.frames : [[]];
        const items = frames[frameIndex] || [];
        const frameSummary = panelData.frameSummaries?.[frameIndex] || panelData.summary;
        const workforcePage = key === 'workforce' && Array.isArray(panelData.pageGroups) && panelData.pageGroups.length ? workforcePageAt(frameIndex) : null;
        const activeGroup = workforcePage?.group || null;
        if (key === 'workforce') {
          const workforceHeaderSummary = panel.querySelector('.monitoring-workforce-header-summary');
          if (workforceHeaderSummary) workforceHeaderSummary.textContent = `${activeGroup?.summary?.[0]?.value ?? 0} MOBILIZING · ${activeGroup?.summary?.[1]?.value ?? 0} DEMOBILIZING`;
          panel.querySelector('.monitoring-summary')?.classList.add('hidden');
        } else {
          panel.querySelector('.monitoring-summary')?.classList.remove('hidden');
        }
        if (frameSummary && key !== 'workforce') {
          const headerSummary = panel.querySelector('.monitoring-header-summary');
          if (headerSummary) headerSummary.innerHTML = availabilityMarkup(frameSummary);
        }
        if (key === 'workforce' && Array.isArray(panelData.pageGroups) && panelData.pageGroups.length) {
          const groupColumnItems = (group, column, page) => {
            const columnPages = group.columns?.[column];
            if (Array.isArray(columnPages)) {
              const selectedPage = columnPages[Math.min(page, Math.max(0, columnPages.length - 1))] || [];
              if (selectedPage.length || columnPages.length <= 1) return selectedPage;
              return columnPages.findLast((columnPage) => columnPage.length > 0) || [];
            }
            if (group.columns && typeof group.columns === 'object') return [];
            const frameItems = group.frames?.[Math.min(page, Math.max(0, (group.frames?.length || 1) - 1))] || [];
            return frameItems.filter((item) => {
              const status = String(item.status || '');
              return column === 'Demobilizing'
                ? status.includes('Demobilizing')
                : status.includes('Mobilizing') && !status.includes('Demobilizing');
            });
          };
          const mobilizing = groupColumnItems(activeGroup, 'Mobilizing', workforcePage.page);
          const demobilizing = groupColumnItems(activeGroup, 'Demobilizing', workforcePage.page);
          const upcoming = workforcePage.groupIndex > 0;
          const stateTitle = panel.querySelector('.monitoring-workforce-state-title');
          if (stateTitle) stateTitle.textContent = upcoming ? 'Upcoming Workforce Movements' : "Today's Workforce Movement";
          const eventMarkup = (item) => `<div class="monitoring-workforce-event px-5 py-4"><div class="flex min-w-0 items-start justify-between gap-4"><div class="min-w-0"><p class="monitoring-workforce-event-name truncate text-xl font-semibold text-white">${escapeHtml(item.employee_name || 'Employee')}</p><p class="mt-1 truncate text-base text-slate-300">${escapeHtml(item.staff_identifier || 'Unassigned ID')} · ${escapeHtml(item.department || 'Unassigned department')}${upcoming ? ` · ${escapeHtml(workforceDateLabel(item.date))}` : ''}</p></div><span class="shrink-0 rounded-full border px-3 py-1 text-[10px] font-bold tracking-[0.08em] ${statusClass(item.status)}">${workforceStatusLabel(item.status).toUpperCase() === 'MOBILIZING' ? 'MOBILIZE' : 'DEMOBILIZE'}</span></div></div>`;
          const column = (title, items, tone) => `<div class="min-w-0 overflow-hidden"><h4 class="border-b-2 ${tone === 'amber' ? 'border-amber-400 text-amber-300' : 'border-teal-400 text-teal-300'} px-5 py-3 text-base font-bold uppercase tracking-[0.14em]">${title}</h4><div class="divide-y divide-slate-700/80 overflow-y-auto">${items.length ? items.map(eventMarkup).join('') : '<div class="px-5 py-8 text-center text-base text-slate-400">No records</div>'}</div></div>`;
          records.innerHTML = `<div class="monitoring-workforce-timeline min-h-full w-full overflow-hidden" data-density="comfortable"><div class="grid h-full grid-cols-2 gap-6">${column('MOBILIZING', mobilizing, 'teal')}${column('DEMOBILIZING', demobilizing, 'amber')}</div></div>`;
          const timeline = records.querySelector('.monitoring-workforce-timeline');
          if (timeline) {
            const eventCount = mobilizing.length + demobilizing.length;
            timeline.dataset.density = eventCount > 8 || window.innerHeight < 800 ? 'compact' : 'comfortable';
          }
        } else {
          records.innerHTML = items.length ? items.map((item) => markup(key, item)).join('') : '<div class="px-5 py-10 text-center text-sm text-slate-500">No current records</div>';
        }
        if (panelData.hasOverflow) {
          ensureControls();
          indicator.textContent = `${frameIndex + 1} / ${panelPageCount()}`;
          donutProgress.style.transition = 'none';
          donutProgress.style.strokeDashoffset = '0';
          void donutProgress.getBoundingClientRect();
          donutProgress.style.transition = `stroke-dashoffset ${config.defaultDurationMs}ms linear`;
          requestAnimationFrame(() => {
            donutProgress.style.strokeDashoffset = '100';
          });
        } else if (controls) {
          controls.remove();
          controls = indicator = donut = donutProgress = null;
        }
      };
      render();
      return {
        renderPage(page) {
          if (destroyed) return;
          const pageCount = panelPageCount();
          frameIndex = Math.min(Math.max(0, page), pageCount - 1);
          render();
        },
        pageCount() {
          return panelPageCount();
        },
        rebuild(nextData) {
          const replacement = nextData || { summary: [], priority: [], frames: [], frameSummaries: [], hasOverflow: false };
          panelData = replacement;
          frameIndex = Math.min(frameIndex, panelPageCount() - 1);
          render();
        },
        destroy() {
          destroyed = true;
        },
      };
    };
    window.digitalMonitoringRotation = {
      config,
      panels: Object.fromEntries(Object.keys(data).map((key) => [key, createPanel(key)])),
    };

    const stateGrid = document.getElementById('monitoringStateGrid');
    const stateLabel = document.getElementById('monitoringStateLabel');
    let currentState = 'today';
    let currentPage = 0;
    let masterTimer = null;
    const statePanels = {
      today: ['vehicles', 'rooms'],
      workforce: ['workforce'],
    };
    const pageCountForPanel = (key) => {
      if (key === 'workforce') {
        const groups = data.workforce?.pageGroups || [];
        return groups.reduce((total, group) => total + Math.max(1, group.frames?.length || 0), 0) || 1;
      }
      return window.digitalMonitoringRotation.panels[key]?.pageCount() || 1;
    };
    const pageCountForState = (state) => Math.max(...(statePanels[state] || statePanels.today).map(pageCountForPanel));
    const renderMasterPage = () => {
      (statePanels[currentState] || statePanels.today).forEach((key) => {
        window.digitalMonitoringRotation.panels[key]?.renderPage(currentPage);
      });
    };
    const applyMonitoringState = (nextState) => {
      currentState = nextState;
      currentPage = 0;
      const visiblePanels = statePanels[nextState] || statePanels.today;
      Object.keys(statePanels).flatMap((state) => statePanels[state]).forEach((key) => {
        const panel = document.querySelector(`[data-monitoring-panel="${key}"]`);
        panel?.classList.toggle('hidden', !visiblePanels.includes(key));
      });
      stateGrid?.classList.toggle('grid-cols-1', nextState === 'workforce');
      if (stateGrid) stateGrid.style.gridTemplateColumns = nextState === 'workforce' ? 'minmax(0, 1fr)' : '';
      if (stateLabel) stateLabel.textContent = nextState === 'workforce' ? 'Workforce Movement' : "Today's Operations";
      renderMasterPage();
    };
    const scheduleMasterTick = () => {
      window.clearTimeout(masterTimer);
      const maxPages = pageCountForState(currentState);
      const dwellMs = maxPages === 1
        ? (currentState === 'today' ? 30000 : 20000)
        : config.defaultDurationMs;
      masterTimer = window.setTimeout(() => {
        if (maxPages > 1 && currentPage + 1 < maxPages) {
          currentPage += 1;
          renderMasterPage();
          scheduleMasterTick();
          return;
        }
        applyMonitoringState(currentState === 'today' ? 'workforce' : 'today');
        scheduleMasterTick();
      }, dwellMs);
    };
    applyMonitoringState(currentState);
    scheduleMasterTick();

    const panelSignature = (panel) => JSON.stringify(panel);
    const refresh = async () => {
      if (refreshInFlight) return;
      refreshInFlight = true;
      if (refreshStatus) refreshStatus.textContent = 'Refreshing data';
      try {
        const response = await fetch(refreshConfig.endpoint, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, cache: 'no-store' });
        if (!response.ok) throw new Error(`Refresh failed: ${response.status}`);
        const payload = await response.json();
        if (!payload.success || !payload.data) throw new Error('Invalid monitoring payload');
        const previousPageCount = pageCountForState(currentState);
        const nextData = buildPanelData(payload.data);
        Object.keys(nextData).forEach((key) => {
          if (panelSignature(data[key]) === panelSignature(nextData[key])) return;
          data[key] = nextData[key];
          window.digitalMonitoringRotation.panels[key]?.rebuild(nextData[key]);
        });
        currentPage = Math.min(currentPage, pageCountForState(currentState) - 1);
        renderMasterPage();
        if (pageCountForState(currentState) !== previousPageCount) scheduleMasterTick();
        if (refreshStatus) refreshStatus.textContent = `Live data updated ${new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
      } catch (error) {
        if (refreshStatus) refreshStatus.textContent = 'Live data unavailable';
      } finally {
        refreshInFlight = false;
      }
    };
    refreshTimer = window.setInterval(refresh, refreshConfig.intervalMs);
    window.digitalMonitoringRefresh = { config: refreshConfig, refresh, stop: () => window.clearInterval(refreshTimer) };
  })();
</script>
