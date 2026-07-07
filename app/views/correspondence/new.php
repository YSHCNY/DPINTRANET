<?php
    $currentUserLevel = (int)($_SESSION['user_level'] ?? 3);
    $selectableUsers = $users ?? [];
    $drafterName = trim(($_SESSION['firstName'] ?? '') . ' ' . ($_SESSION['lastName'] ?? '')) ?: 'You';
    usort($selectableUsers, function ($left, $right) {
        $leftDepartment = strtolower(trim($left['department'] ?? ''));
        $rightDepartment = strtolower(trim($right['department'] ?? ''));
        $leftName = strtolower(trim(($left['firstName'] ?? '') . ' ' . ($left['lastName'] ?? '')));
        $rightName = strtolower(trim(($right['firstName'] ?? '') . ' ' . ($right['lastName'] ?? '')));
        return $leftDepartment <=> $rightDepartment ?: $leftName <=> $rightName;
    });
?>
<?php require __DIR__ . '/_shared.php'; ?>

<div class="max-w-[1240px] mx-auto px-3 py-6">
    <section class="mb-5 rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="px-4 py-3 sm:px-5 sm:py-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Correspondence</p>
                    <div class="mt-1 flex flex-wrap items-center gap-3">
                        <h1 class="text-base font-semibold tracking-tight text-slate-900">New Circulation</h1>
                        <p class="text-xs text-slate-500">Create a new draft or circulation with fast recipient and attachment setup.</p>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">Drafter: <span class="font-semibold text-slate-900"><?= htmlspecialchars($drafterName) ?></span></p>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-2.5 py-1">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Ready to send
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-2.5 py-1">
                        <span class="h-2 w-2 rounded-full bg-sky-500"></span>
                        Draft-friendly
                    </span>
                </div>
            </div>
        </div>
    </section>

    <section class="">
        <?php include __DIR__ . '/index_form_block.php'; ?>
    </section>
</div>

<script>
// include form JS only when the form is present
<?php include __DIR__ . '/_form_js.php'; ?>
</script>
