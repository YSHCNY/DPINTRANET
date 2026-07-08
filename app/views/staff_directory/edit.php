<?php
function escape($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

$staff = $staff ?? null;
?>

<div class="space-y-4">
    <div class="rounded-md border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Edit Staff Profile</p>
                <h1 class="text-base font-semibold text-slate-900">Update staff details</h1>
                <p class="text-sm text-slate-600">Modify the selected staff entry and save changes.</p>
            </div>
            <a href="index.php?controller=StaffDirectory&action=index" class="inline-flex h-9 items-center rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-50">Back to directory</a>
        </div>
    </div>

    <div class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
        <form action="index.php?controller=StaffDirectory&action=update" method="post" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="staff_id" value="<?= escape($staff['staff_id']) ?>">
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-3">
                    <label class="block text-xs font-semibold text-slate-700" for="firstName">First Name</label>
                    <input id="firstName" name="firstName" type="text" value="<?= escape($staff['firstName']) ?>" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-900 outline-none focus:border-slate-400 focus:ring-2 focus:ring-slate-100">
                </div>
                <div class="space-y-3">
                    <label class="block text-xs font-semibold text-slate-700" for="lastName">Last Name</label>
                    <input id="lastName" name="lastName" type="text" value="<?= escape($staff['lastName']) ?>" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-900 outline-none focus:border-slate-400 focus:ring-2 focus:ring-slate-100">
                </div>
                <div class="space-y-3">
                    <label class="block text-xs font-semibold text-slate-700" for="position">Position</label>
                    <input id="position" name="position" type="text" value="<?= escape($staff['position']) ?>" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-900 outline-none focus:border-slate-400 focus:ring-2 focus:ring-slate-100">
                </div>
                <div class="space-y-3">
                    <label class="block text-xs font-semibold text-slate-700" for="department">Department</label>
                    <input id="department" name="department" type="text" value="<?= escape($staff['department']) ?>" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-900 outline-none focus:border-slate-400 focus:ring-2 focus:ring-slate-100">
                </div>
                <div class="space-y-3">
                    <label class="block text-xs font-semibold text-slate-700" for="firm">Firm / Organization</label>
                    <input id="firm" name="firm" type="text" value="<?= escape($staff['firm'] ?? '') ?>" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-900 outline-none focus:border-slate-400 focus:ring-2 focus:ring-slate-100">
                </div>
                <div class="space-y-3">
                    <label class="block text-xs font-semibold text-slate-700" for="email">Email</label>
                    <input id="email" name="email" type="email" value="<?= escape($staff['email']) ?>" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-900 outline-none focus:border-slate-400 focus:ring-2 focus:ring-slate-100">
                </div>
                <div class="space-y-3">
                    <label class="block text-xs font-semibold text-slate-700" for="contactNumber">Contact Number</label>
                    <input id="contactNumber" name="contact_number" type="tel" value="<?= escape($staff['contact_number']) ?>" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-900 outline-none focus:border-slate-400 focus:ring-2 focus:ring-slate-100">
                </div>
                <div class="space-y-3">
                    <label class="block text-xs font-semibold text-slate-700" for="deploymentDate">Deployment Date</label>
                    <input id="deploymentDate" name="deployment_date" type="date" value="<?= escape($staff['deployment_date']) ?>" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-900 outline-none focus:border-slate-400 focus:ring-2 focus:ring-slate-100">
                </div>
                <div class="space-y-3">
                    <label class="block text-xs font-semibold text-slate-700" for="status">Status</label>
                    <select id="status" name="status" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-900 outline-none focus:border-slate-400 focus:ring-2 focus:ring-slate-100">
                        <?php foreach (['active', 'inactive', 'on_leave', 'contract'] as $statusOption): ?>
                            <option value="<?= escape($statusOption) ?>" <?= $staff['status'] === $statusOption ? 'selected' : '' ?>><?= escape(ucfirst(str_replace('_', ' ', $statusOption))) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Profile photo</p>
                        <p class="mt-1 text-sm text-slate-600">Upload a new photo to replace the current avatar.</p>
                    </div>
                    <span class="text-xs font-medium text-slate-500">Max 2MB, JPG or PNG</span>
                </div>
                <label for="profilePhoto" class="mt-4 flex min-h-[120px] flex-col items-center justify-center gap-3 rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-6 text-center text-sm text-slate-500 transition hover:border-slate-400 hover:bg-slate-50 cursor-pointer">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-700">📤</span>
                    <span class="font-semibold text-slate-900">Upload new photo</span>
                    <span class="text-xs text-slate-500">Click to choose a JPG or PNG file.</span>
                    <input id="profilePhoto" name="profile_photo" type="file" accept="image/png, image/jpeg" class="sr-only">
                </label>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                <a href="index.php?controller=StaffDirectory&action=index" class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 transition hover:bg-slate-50">Cancel</a>
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-slate-950 px-4 text-sm font-semibold text-white transition hover:bg-slate-800">Save changes</button>
            </div>
        </form>
    </div>
</div>
