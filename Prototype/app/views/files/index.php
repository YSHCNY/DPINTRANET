<?php require __DIR__ . '/../partials/icons.php'; ?>
<?php
    $canManageFiles = !empty($canManageFiles);
    $fileCount = count($files ?? []);
    $categoryOptions = [];
    $directionOptions = [];
    $latestUpload = null;

    foreach ($files ?? [] as $file) {
        $category = trim($file['category'] ?? '');
        if ($category !== '') {
            $categoryOptions[$category] = true;
        }

        $direction = trim(($file['directionFrom'] ?? '') . ' ⇄ ' . ($file['directionTo'] ?? 'No Direction'));
        if ($direction !== '') {
            $directionOptions[$direction] = true;
        }

        if (!empty($file['uploadedat'])) {
            $ts = strtotime($file['uploadedat']);
            if ($ts && ($latestUpload === null || $ts > $latestUpload)) {
                $latestUpload = $ts;
            }
        }
    }

    $categoryOptions = array_keys($categoryOptions);
    sort($categoryOptions);
    $directionOptions = array_keys($directionOptions);
    sort($directionOptions);
?>

<div class="min-h-screen theme-palette">
    <div class="max-w-[1600px] mx-auto px-4 py-8">
        <div class="mb-8 overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-[0_18px_60px_rgba(15,23,42,0.08)]">
            <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-slate-50 px-6 py-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-3xl">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-blue-600">Files Repository</p>
                        <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">Official File Repository</h2>
                        <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-500">
                            Upload, classify, and maintain official files in a clean workspace built for fast scanning and minimal friction.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                            <span><?= $fileCount ?> file(s)</span>
                        </div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            <span><?= count($categoryOptions) ?> category(s)</span>
                        </div>
                        <?php if ($latestUpload): ?>
                            <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-sm">
                                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                <span>Latest <?= date('M d, Y', $latestUpload) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="p-6 lg:p-7">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-[220px_260px_minmax(0,1fr)] gap-3 flex-1">
                        <div>
                            <label for="categoryFilter" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Category</label>
                            <select id="categoryFilter" class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">All categories</option>
                                <?php foreach ($categoryOptions as $category): ?>
                                    <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="directionFilter" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Direction</label>
                            <select id="directionFilter" class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">All directions</option>
                                <?php foreach ($directionOptions as $direction): ?>
                                    <option value="<?= htmlspecialchars($direction) ?>"><?= htmlspecialchars($direction) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <p class="text-xs leading-5 text-slate-500">
                                Filter by category or direction. Search remains available for quick lookup.
                            </p>
                        </div>
                    </div>

                    <?php if ($canManageFiles): ?>
                        <a href="index.php?controller=Files&action=create"
                           class="inline-flex items-center justify-center gap-2 rounded-2xl bg-sky-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
                            <?= $fileIcon ?? '' ?>
                            Upload New File
                        </a>
                    <?php else: ?>
                        <div class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                            <span class="h-2.5 w-2.5 rounded-full bg-slate-300"></span>
                            Viewer access only
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-[0_18px_60px_rgba(15,23,42,0.08)]">
            <div class="border-b border-slate-200 px-6 py-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-600">Repository Table</p>
                        <h3 class="mt-1 text-lg font-semibold text-slate-900">All Files</h3>
                    </div>
                    <p class="text-sm text-slate-500">One row per file record, styled for quick scanning.</p>
                </div>
            </div>

            <div class="overflow-x-auto p-6">
                <table id="filesTable" class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr class="border-y border-slate-200">
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em]">File</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em]">Description</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em]">Category</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em]">Direction</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em]">Uploader</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em]">Uploaded</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.16em]">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (!empty($files)): ?>
                            <?php foreach ($files as $file): ?>
                                <?php
                                    $directionLabel = trim(($file['directionFrom'] ?? '') . ' ⇄ ' . ($file['directionTo'] ?? 'No Direction'));
                                    $uploaderName = trim(($file['firstName'] ?? '') . ' ' . ($file['lastName'] ?? ''));
                                    $position = trim($file['position'] ?? '');
                                    $category = trim($file['category'] ?? 'Uncategorized');
                                ?>
                                <tr class="transition hover:bg-slate-50/70">
                                    <td class="px-4 py-4 min-w-[240px]">
                                        <div class="space-y-1">
                                            <p class="font-semibold text-slate-900"><?= htmlspecialchars($file['filename'] ?? '') ?></p>
                                            <p class="font-mono text-xs text-slate-500">Upload #<?= htmlspecialchars($file['id'] ?? '') ?></p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 min-w-[280px] text-slate-600">
                                        <div class="max-w-[360px]">
                                            <p class="leading-6"><?= htmlspecialchars($file['desc'] ?? $file['description'] ?? '') ?></p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                            <?= htmlspecialchars($category) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-slate-600 whitespace-nowrap">
                                        <?= htmlspecialchars($directionLabel) ?>
                                    </td>
                                    <td class="px-4 py-4 min-w-[190px]">
                                        <p class="font-semibold text-slate-900"><?= htmlspecialchars($uploaderName !== '' ? $uploaderName : '—') ?></p>
                                        <p class="text-xs text-slate-500"><?= htmlspecialchars($position !== '' ? $position : '—') ?></p>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-slate-500">
                                        <?php if (!empty($file['uploadedat'])): ?>
                                            <p class="font-medium text-slate-700"><?= date('M d, Y', strtotime($file['uploadedat'])) ?></p>
                                            <p class="text-xs text-slate-500"><?= date('g:i A', strtotime($file['uploadedat'])) ?></p>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-center gap-2" onclick="event.stopPropagation()">
                                            <a href="index.php?controller=Files&action=download&file=<?= urlencode($file['filename'] ?? '') ?>"
                                               class="inline-flex h-9 w-9 items-center justify-center rounded-2xl border border-sky-200 bg-sky-50 text-sky-700 transition hover:bg-sky-600 hover:text-white"
                                               title="Download">
                                                <?= $downloadIcon ?? '' ?>
                                            </a>

                                            <?php if ($canManageFiles): ?>
                                                <a href="index.php?controller=Files&action=edit&id=<?= (int)$file['id'] ?>"
                                                   class="inline-flex h-9 w-9 items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-700 transition hover:bg-emerald-600 hover:text-white"
                                                   title="Edit">
                                                    <?= $editIcon ?? '' ?>
                                                </a>
                                            <?php endif; ?>

                                            <?php if (in_array((string)($_SESSION['user_level'] ?? ''), ['0', '1'], true)): ?>
                                                <a href="index.php?controller=Files&action=delete&id=<?= (int)$file['id'] ?>"
                                                   onclick="return confirm('Delete this file?')"
                                                   class="inline-flex h-9 w-9 items-center justify-center rounded-2xl border border-rose-200 bg-rose-50 text-rose-700 transition hover:bg-rose-600 hover:text-white"
                                                   title="Delete">
                                                    <?= $deleteIcon ?? '' ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500">
                                    No files found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Module-specific CSS removed: replaced by global Tailwind utilities and shared DataTables tailwind stylesheet -->

<script>
$(document).ready(function () {
  const table = $('#filesTable').DataTable({
    pageLength: 25,
    order: [[5, 'desc']],
    responsive: true,
    orderCellsTop: true,
    dom: '<"flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 mb-4"lf>rt<"flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 mt-4"ip>',
    language: {
      search: '',
      searchPlaceholder: 'Search files'
    }
  });

  $('#categoryFilter').on('change', function () {
    table.column(2).search(this.value ? '^' + $.fn.dataTable.util.escapeRegex(this.value) + '$' : '', true, false).draw();
  });

  $('#directionFilter').on('change', function () {
    table.column(3).search(this.value ? '^' + $.fn.dataTable.util.escapeRegex(this.value) + '$' : '', true, false).draw();
  });
});
</script>
