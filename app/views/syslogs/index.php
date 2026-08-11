<div class="min-h-screen space-y-6 p-6">
  <div class="max-w-7xl mx-auto">
    


    <!-- Logs Table -->
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
      <div class="border-b border-slate-200 bg-slate-50/70 px-4 py-3">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <div class="min-w-0">
            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Activity feed</p>
            <h2 class="mt-1 text-base font-semibold text-slate-800">System activity</h2>
            <p class="mt-0.5 text-sm text-slate-500">Monitor recent actions across the intranet.</p>
          </div>
          <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <label class="relative">
              <span class="sr-only">Search logs</span>
              <input id="syslogsSearch" type="search" placeholder="Search logs" class="h-9 min-w-[220px] rounded-lg border border-slate-200 bg-white px-3 pr-9 text-sm text-slate-700 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-100" />
            </label>
            <select id="moduleFilter" class="h-9 min-w-[150px] rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-100">
              <option value="">All modules</option>
            </select>
            <button id="resetSyslogsFilters" type="button" class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-600 transition hover:bg-slate-50">Reset</button>
          </div>
        </div>
      </div>
      <div class="overflow-x-auto p-4">
        <?php if (!empty($syslogs)): ?>
        <table class="min-w-full table-fixed border-collapse text-sm text-left text-slate-700" id="syslogsTable">
          <thead class="bg-slate-50 text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">
            <tr>
              <th class="w-[94px] px-3 py-2.5 text-left">Time</th>
              <th class="w-[140px] px-3 py-2.5 text-left">Module</th>
              <th class="w-[220px] px-3 py-2.5 text-left">User</th>
              <th class="px-3 py-2.5 text-left">Activity</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-slate-100 bg-white">
            <!-- Log Row -->
            <?php foreach($syslogs as $log): ?>
            <?php
              $rawTimestamp = (string)($log['logDate'] ?? '');
              $timestampLabel = '';
              $timestampTime = '';

              if ($rawTimestamp !== '') {
                $timestampEpoch = strtotime($rawTimestamp);
                if ($timestampEpoch !== false) {
                  $todayEpoch = strtotime('today');
                  $timestampLabel = date('M j', $timestampEpoch);
                  if (date('Y-m-d', $timestampEpoch) === date('Y-m-d', $todayEpoch)) {
                    $timestampLabel = 'Today';
                  }
                  $timestampTime = date('g:i A', $timestampEpoch);
                } else {
                  $timestampLabel = $rawTimestamp;
                }
              }

              $messageText = trim((string)($log['logDesc'] ?? ''));
              $messageMain = $messageText;
              $messageSupport = '';
              $activityToneClass = 'bg-slate-400';

              if ($messageText !== '') {
                $messageParts = preg_split('/\s*(•|·)\s*/u', $messageText);
                $messageParts = array_values(array_filter(array_map('trim', $messageParts), static function ($part): bool {
                  return $part !== '';
                }));

                if (count($messageParts) > 1) {
                  $messageMain = $messageParts[0];
                  $messageSupport = implode(' • ', array_slice($messageParts, 1));
                }

                if (preg_match('/\b(error|failed|blocked|revoke|delete|deleted)\b/i', $messageText)) {
                  $activityToneClass = 'bg-rose-400';
                } elseif (preg_match('/\b(login|verified|trusted|security|auth|password)\b/i', $messageText)) {
                  $activityToneClass = 'bg-sky-400';
                } elseif (preg_match('/\b(created|created|added|circulated|new)\b/i', $messageText)) {
                  $activityToneClass = 'bg-emerald-400';
                }
              }
            ?>
            <tr class="align-middle transition-colors duration-150 hover:bg-slate-50/80 focus-within:bg-slate-50/80">
              <td class="w-[94px] px-3 py-3 text-slate-500 whitespace-nowrap" title="<?= htmlspecialchars($rawTimestamp) ?>">
                <div class="flex flex-col leading-tight">
                  <span class="text-[11px] font-semibold text-slate-700">
                    <?= htmlspecialchars($timestampLabel) ?>
                  </span>
                  <?php if ($timestampTime !== ''): ?>
                    <span class="text-[11px] text-slate-500">
                      <?= htmlspecialchars($timestampTime) ?>
                    </span>
                  <?php endif; ?>
                </div>
              </td>
              <td class="px-3 py-3">
                <span class="inline-flex max-w-[120px] items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-medium text-slate-600 whitespace-nowrap overflow-hidden text-ellipsis">
                  <?= htmlspecialchars((string)($log['module'] ?? '')) ?>
                </span>
              </td>
              
              <td class="px-3 py-3 text-slate-700">
                <div class="flex min-w-0 flex-col leading-tight">
                  <p class="truncate whitespace-nowrap font-medium text-slate-900">
                    <?= htmlspecialchars((string)($log['display_name'] ?? 'System')) ?>
                  </p>
                  <p class="truncate whitespace-nowrap text-[11px] text-slate-500">
                    ID #<?= htmlspecialchars((string)($log['resolved_user_id'] ?? '')) ?>
                  </p>
                </div>
              </td>

              <td class="px-3 py-3 text-slate-800">
                <div class="flex min-w-0 items-start gap-2">
                  <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full <?= $activityToneClass ?>"></span>
                  <div class="min-w-0 flex-1 leading-5">
                    <div class="line-clamp-2 font-medium text-slate-900">
                      <?= htmlspecialchars($messageMain) ?>
                    </div>
                    <?php if ($messageSupport !== ''): ?>
                      <div class="mt-0.5 text-[11px] leading-4 text-slate-500">
                        <?= htmlspecialchars($messageSupport) ?>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </td>
            </tr>

            <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?>
        <div class="flex min-h-[260px] flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/70 px-6 py-12 text-center">
          <p class="text-sm font-semibold text-slate-700">No activity found</p>
          <p class="mt-1 max-w-md text-sm text-slate-500">There are no system activities matching your current filters.</p>
        </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>
<script>
$(document).ready(function () {
  const tableBody = document.querySelector('#syslogsTable tbody');
  const rows = Array.from(tableBody ? tableBody.querySelectorAll('tr') : []);
  const searchInput = document.getElementById('syslogsSearch');
  const moduleFilter = document.getElementById('moduleFilter');
  const resetButton = document.getElementById('resetSyslogsFilters');
  const emptyState = document.getElementById('syslogsEmptyState');

  const getModuleValue = function (row) {
    const moduleCell = row.querySelectorAll('td')[1];
    return moduleCell ? moduleCell.textContent.trim().toLowerCase() : '';
  };

  const getRowText = function (row) {
    return row.textContent.toLowerCase();
  };

  const populateModuleFilter = function () {
    const modules = Array.from(new Set(rows.map(getModuleValue).filter(Boolean))).sort();
    modules.forEach(function (module) {
      const option = document.createElement('option');
      option.value = module;
      option.textContent = module;
      moduleFilter.appendChild(option);
    });
  };

  const applySyslogsFilters = function () {
    const searchValue = (searchInput.value || '').trim().toLowerCase();
    const moduleValue = (moduleFilter.value || '').trim().toLowerCase();
    let visibleCount = 0;

    rows.forEach(function (row) {
      const rowText = getRowText(row);
      const matchesSearch = !searchValue || rowText.includes(searchValue);
      const matchesModule = !moduleValue || getModuleValue(row) === moduleValue;
      const isVisible = matchesSearch && matchesModule;

      row.style.display = isVisible ? '' : 'none';
      if (isVisible) {
        visibleCount += 1;
      }
    });

    if (emptyState) {
      emptyState.style.display = visibleCount > 0 ? 'none' : 'flex';
    }
  };

  populateModuleFilter();
  searchInput.addEventListener('input', applySyslogsFilters);
  moduleFilter.addEventListener('change', applySyslogsFilters);
  resetButton.addEventListener('click', function () {
    searchInput.value = '';
    moduleFilter.value = '';
    applySyslogsFilters();
  });
});
</script>