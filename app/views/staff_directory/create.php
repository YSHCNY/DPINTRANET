<?php
function escape($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

$defaultProfile = BASE_URL . 'app/assets/profiles/default.png';
$importResults = $importResults ?? null;
$importErrors = $importErrors ?? [];
?>

<div class="space-y-4">
    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-900">Add new staff</p>
                <p class="mt-1 max-w-2xl text-sm text-slate-600">Create a staff profile manually or import a roster in one admin workflow.</p>
            </div>
            <a href="index.php?controller=StaffDirectory&action=index" class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-50">Back</a>
        </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-8">
        <section class="xl:col-span-5 space-y-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Manual staff entry</p>
                    <h2 class="mt-1 text-base font-semibold text-slate-900">Create one staff profile</h2>
                </div>
                <span class="rounded-full bg-slate-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500">Manual</span>
            </div>

            <form id="manualStaffForm" action="index.php?controller=StaffDirectory&action=store" method="post" enctype="multipart/form-data" class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-3">
                        <label class="block text-xs font-semibold text-slate-700" for="staffId">Staff ID <span class="text-rose-500">*</span></label>
                        <input id="staffId" name="staff_id" type="text" required class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-900 outline-none focus:border-slate-400 focus:ring-2 focus:ring-slate-100" placeholder="e.g. ST-1247">
                    </div>
                    <div class="space-y-3">
                        <label class="block text-xs font-semibold text-slate-700" for="firstName">First Name <span class="text-rose-500">*</span></label>
                        <input id="firstName" name="firstName" type="text" required class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-900 outline-none focus:border-slate-400 focus:ring-2 focus:ring-slate-100" placeholder="Jane">
                    </div>
                    <div class="space-y-3">
                        <label class="block text-xs font-semibold text-slate-700" for="lastName">Last Name <span class="text-rose-500">*</span></label>
                        <input id="lastName" name="lastName" type="text" required class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-900 outline-none focus:border-slate-400 focus:ring-2 focus:ring-slate-100" placeholder="Doe">
                    </div>
                    <div class="space-y-3">
                        <label class="block text-xs font-semibold text-slate-700" for="position">Position <span class="text-rose-500">*</span></label>
                        <input id="position" name="position" type="text" required class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-900 outline-none focus:border-slate-400 focus:ring-2 focus:ring-slate-100" placeholder="Project Manager">
                    </div>
                    <div class="space-y-3">
                        <label class="block text-xs font-semibold text-slate-700" for="department">Department</label>
                        <input id="department" name="department" type="text" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-900 outline-none focus:border-slate-400 focus:ring-2 focus:ring-slate-100" placeholder="Operations">
                    </div>
                    <div class="space-y-3">
                        <label class="block text-xs font-semibold text-slate-700" for="email">Email <span class="text-rose-500">*</span></label>
                        <input id="email" name="email" type="email" required class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-900 outline-none focus:border-slate-400 focus:ring-2 focus:ring-slate-100" placeholder="jane.doe@example.com">
                        <p class="text-xs text-slate-500">Use the staff email used for directory lookup.</p>
                    </div>
                    <div class="space-y-3">
                        <label class="block text-xs font-semibold text-slate-700" for="contactNumber">Contact Number</label>
                        <input id="contactNumber" name="contact_number" type="tel" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-900 outline-none focus:border-slate-400 focus:ring-2 focus:ring-slate-100" placeholder="+63 912 345 6789">
                    </div>
                    <div class="space-y-3">
                        <label class="block text-xs font-semibold text-slate-700" for="deploymentDate">Deployment Date</label>
                        <input id="deploymentDate" name="deployment_date" type="date" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-900 outline-none focus:border-slate-400 focus:ring-2 focus:ring-slate-100">
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Profile photo</p>
                            <p class="mt-1 text-sm text-slate-600">Optional avatar for the directory.</p>
                        </div>
                        <div class="text-xs font-medium text-slate-500">PNG or JPG • 2MB max</div>
                    </div>
                    <label for="profilePhoto" class="mt-3 flex min-h-[110px] flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white px-4 text-center text-sm text-slate-500 transition hover:border-slate-400 hover:bg-slate-50 cursor-pointer">
                        <span class="mb-2 inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-sm text-slate-600">📤</span>
                        <span class="font-semibold text-slate-900">Drag & drop or browse</span>
                        <span class="mt-1 text-xs text-slate-500">PNG or JPG up to 2MB</span>
                        <input id="profilePhoto" name="profile_photo" type="file" accept="image/png, image/jpeg" class="sr-only">
                    </label>
                    <div class="mt-3 flex flex-wrap items-center justify-end gap-3">
                        <a href="index.php?controller=StaffDirectory&action=index" class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 transition hover:bg-slate-50">Back</a>
                        <button type="submit" form="manualStaffForm" class="inline-flex h-9 items-center justify-center rounded-lg bg-slate-950 px-4 text-sm font-semibold text-white transition hover:bg-slate-800">Save staff</button>
                    </div>
                </div>
            </form>
        </section>

        <section class="xl:col-span-3 space-y-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <form id="bulkImportForm" action="index.php?controller=StaffDirectory&action=import" method="post" enctype="multipart/form-data" class="space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Bulk import</p>
                        <h2 class="mt-1 text-base font-semibold text-slate-900">Import multiple staff</h2>
                    </div>
                    <span class="rounded-full bg-slate-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500">CSV / XLSX</span>
                </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Download template</p>
                        <p class="text-xs text-slate-500">Use the approved Excel file for import.</p>
                    </div>
                    <a href="<?= rtrim(BASE_URL, '/') ?>/uploads/stafftemplate/staffdirtmplt.xlsx" class="inline-flex h-9 items-center rounded-lg border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-50">Download</a>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-600">
                <label for="bulkUpload" class="flex min-h-[120px] flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-4 text-center text-slate-500 transition hover:border-slate-400 hover:bg-slate-100 cursor-pointer">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-600">📂</span>
                    <span class="text-sm font-semibold text-slate-900">Upload Excel file</span>
                    <span id="bulkUploadHint" class="text-xs text-slate-500">Drop .xlsx or .csv here, or click to browse.</span>
                </label>
                <input id="bulkUpload" name="bulk_upload" type="file" accept=".xlsx,.csv" class="sr-only">
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
                    <span class="rounded-full bg-white px-2.5 py-1 text-slate-600">Download</span>
                    <span class="text-slate-400">→</span>
                    <span class="rounded-full bg-white px-2.5 py-1 text-slate-600">Upload</span>
                    <span class="text-slate-400">→</span>
                    <span class="rounded-full bg-white px-2.5 py-1 text-slate-600">Validate</span>
                    <span class="text-slate-400">→</span>
                    <span class="rounded-full bg-white px-2.5 py-1 text-slate-600">Import</span>
                </div>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row">
                <button type="submit" name="bulk_action" value="validate" class="inline-flex flex-1 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 transition hover:bg-slate-50">Validate</button>
                <button type="submit" name="bulk_action" value="import" class="inline-flex flex-1 items-center justify-center rounded-lg bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">Import staff</button>
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                <?php if (!empty($importResults)): ?>
                    <div class="mb-4 rounded-lg border border-slate-200 bg-white p-3 text-slate-800">
                        <p class="font-semibold">Import summary</p>
                        <p class="mt-2">Rows processed: <?= htmlspecialchars($importResults['rows'] ?? 0) ?></p>
                        <p>Valid rows: <?= htmlspecialchars($importResults['validRows'] ?? 0) ?></p>
                        <p>Errors found: <?= htmlspecialchars($importResults['errors'] ?? 0) ?></p>
                    </div>
                <?php endif; ?>

                <div id="validationErrors" class="<?= empty($importErrors) ? 'hidden' : '' ?> rounded-xl border border-rose-100 bg-rose-50 p-3 text-sm text-rose-700">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <p class="text-sm font-semibold text-rose-800">Validation issues</p>
                        <span class="rounded-full bg-rose-100 px-2 py-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-rose-700"><?= htmlspecialchars(count($importErrors)) ?> issues</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-xs text-rose-700">
                            <thead>
                                <tr>
                                    <th class="px-2 py-2 font-semibold uppercase tracking-[0.2em] text-rose-700">Row</th>
                                    <th class="px-2 py-2 font-semibold uppercase tracking-[0.2em] text-rose-700">Field</th>
                                    <th class="px-2 py-2 font-semibold uppercase tracking-[0.2em] text-rose-700">Issue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($importErrors as $error): ?>
                                    <tr class="border-t border-rose-100 bg-white">
                                        <td class="px-2 py-2"><?= htmlspecialchars($error['row']) ?></td>
                                        <td class="px-2 py-2"><?= htmlspecialchars($error['field']) ?></td>
                                        <td class="px-2 py-2"><?= htmlspecialchars($error['message']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </form>
        </section>
    </div>

</div>

<script>
    const bulkUploadInput = document.getElementById('bulkUpload');
    const validationErrors = document.getElementById('validationErrors');
    const dropZone = document.querySelector('label[for="bulkUpload"]');

    if (dropZone && bulkUploadInput) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, (event) => {
                event.preventDefault();
                dropZone.classList.add('border-slate-400', 'bg-slate-100');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.remove('border-slate-400', 'bg-slate-100');
            });
        });

        const bulkUploadHint = document.getElementById('bulkUploadHint');

        bulkUploadInput.addEventListener('change', () => {
            if (bulkUploadInput.files.length) {
                validationErrors.classList.remove('hidden');
                bulkUploadHint.textContent = bulkUploadInput.files[0].name;
            } else {
                bulkUploadHint.textContent = 'Drop .xlsx or .csv file here, or click to browse.';
            }
        });
    }
</script>
