<?php
    $currentUserLevel = (int)($_SESSION['user_level'] ?? 3);
    $selectableUsers = $users ?? [];
    usort($selectableUsers, function ($left, $right) {
        $leftDepartment = strtolower(trim($left['department'] ?? ''));
        $rightDepartment = strtolower(trim($right['department'] ?? ''));
        $leftName = strtolower(trim(($left['firstName'] ?? '') . ' ' . ($left['lastName'] ?? '')));
        $rightName = strtolower(trim(($right['firstName'] ?? '') . ' ' . ($right['lastName'] ?? '')));
        return $leftDepartment <=> $rightDepartment ?: $leftName <=> $rightName;
    });
?>
<?php require __DIR__ . '/_shared.php'; ?>

<div class="mb-4 md:mb-6 overflow-hidden rounded-lg md:rounded-xl border border-slate-200 bg-white shadow-sm md:shadow-md">
    <div class="border-b border-slate-200 bg-gradient-to-r from-white via-slate-50 to-white px-3 md:px-4 py-3 md:py-4">
        <div class="flex flex-col gap-2 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <p class="text-[10px] md:text-[11px] font-semibold uppercase tracking-[0.2em] text-blue-600">Digital Correspondence</p>
                <h2 class="mt-1 text-lg md:text-xl font-semibold tracking-tight text-slate-900">New Circulation</h2>
                <p class="mt-1 md:mt-2 max-w-2xl text-xs md:text-sm leading-5 md:leading-6 text-slate-500">
                    Capture document details, assign recipients and CC with minimal friction.
                </p>
            </div>
            <div class="flex flex-wrap gap-1 md:gap-2 mt-2 lg:mt-0">
                <div class="inline-flex items-center gap-1.5 md:gap-2 rounded-full border border-slate-200 bg-slate-50 px-2 md:px-3 py-1 md:py-1.5 text-[10px] md:text-xs font-medium text-slate-600 shadow-sm">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    <span>Role-enabled</span>
                </div>
                <div class="inline-flex items-center gap-1.5 md:gap-2 rounded-full border border-slate-200 bg-slate-50 px-2 md:px-3 py-1 md:py-1.5 text-[10px] md:text-xs font-medium text-slate-600 shadow-sm">
                    <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                    <span>Live filters</span>
                </div>
            </div>
        </div>
    </div>

    <?php // include the original circulation form markup from index.php by copying the block there. To avoid duplication, keep form JS in index.php but include shared modals here. ?>
    <?php include __DIR__ . '/index_form_block.php'; ?>
</div>

<script>
// include form JS only when the form is present
<?php include __DIR__ . '/_form_js.php'; ?>
</script>
