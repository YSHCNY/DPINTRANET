<?php
// Extracted form block from index.php to be shared with new.php
// This file is intended to be included only when the form is needed.
?>
<?php
    $isFinalizeMode = !empty($isFinalizeMode);
    $formAction = !empty($draftDocument) ? "index.php?controller=correspondence&action=update&id=" . (int)$draftDocument['id'] : 'index.php?controller=correspondence&action=store';
?>
<form id="circulationForm" action="<?= $formAction ?>" method="POST" enctype="multipart/form-data" class="p-2 md:p-3 lg:p-4">
    <?php if (!empty($draftDocument) && $isFinalizeMode): ?>
        <input type="hidden" id="finalizeInput" name="finalize" value="1">
    <?php endif; ?>
    <?php if (!empty($draftDocument)): ?>
        <input type="hidden" name="id" value="<?= (int)$draftDocument['id'] ?>">
    <?php endif; ?>
    <div class="grid grid-cols-1 gap-3 md:gap-4 md:grid-cols-3">
        <!-- Left column: main document content -->
        <div class="md:col-span-2 space-y-3 md:space-y-4">
            <!-- Recipients & CC moved to top for email-like flow -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 md:gap-3 items-start">
                <section class="rounded-md border border-slate-200 bg-white p-2 md:p-2.5 shadow-sm">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="flex items-center gap-2">
                                <label class="block text-[10px] md:text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Recipients</label>
                                <span id="recipients-count-badge" class="hidden inline-flex items-center justify-center rounded-full bg-blue-600 px-1.5 py-0.5 text-[9px] font-semibold text-white">0</span>
                            </div>
                            <p class="mt-0.5 md:mt-1 text-[11px] md:text-xs text-slate-500">Select people to receive this circulation.</p>
                        </div>
                    </div>

                    <div class="mt-2">
                        <div id="recipients-chips" class="min-h-[40px] md:min-h-[44px] flex flex-wrap gap-1 md:gap-1.5 items-center">
                            <?php if (!empty($draftRecipients)): ?>
                                <?php foreach ($draftRecipients as $r): ?>
                                    <input type="hidden" name="recipients[]" value="<?= (int)$r['id'] ?>">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2 md:px-2.5 py-1 text-xs md:text-sm text-slate-700"><?= htmlspecialchars($r['name']) ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span id="recipients-placeholder" class="text-xs md:text-sm text-slate-500">No recipients</span>
                            <?php endif; ?>
                        </div>
                        <div class="mt-2">
                            <button type="button" onclick="openRecipientDrawer('recipients')" class="inline-flex items-center gap-1.5 rounded-full border border-green-300 bg-green-50 px-2.5 md:px-3 py-1 md:py-1.5 text-xs md:text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition">Add</button>
                        </div>
                    </div>
                </section>

                <section class="rounded-md border border-slate-200 bg-white p-2 md:p-2.5 shadow-sm">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <label class="block text-[10px] md:text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">CC</label>
                            <p class="mt-0.5 md:mt-1 text-[11px] md:text-xs text-slate-500">Carbon-copy recipients separate.</p>
                        </div>
                    </div>

                    <div class="mt-2">
                        <div id="cc-chips" class="min-h-[40px] md:min-h-[44px] flex flex-wrap gap-1 md:gap-1.5 items-center">
                            <?php if (!empty($draftCc)): ?>
                                <?php foreach ($draftCc as $c): ?>
                                    <input type="hidden" name="cc[]" value="<?= (int)$c['id'] ?>">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2 md:px-2.5 py-1 text-xs md:text-sm text-slate-700"><?= htmlspecialchars($c['name']) ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span id="cc-placeholder" class="text-xs md:text-sm text-slate-500">No CC</span>
                            <?php endif; ?>
                        </div>
                        <div class="mt-2">
                            <button type="button" onclick="openRecipientDrawer('cc')" class="inline-flex items-center gap-1.5 rounded-full border border-green-300 bg-green-50 px-2.5 md:px-3 py-1 md:py-1.5 text-xs md:text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition">Add</button>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Document Identity -->
            <section class="rounded-md border border-slate-200 bg-white p-2 md:p-2.5 shadow-sm">
                <div class="flex items-center justify-between gap-2 md:gap-4">
                    <div>
                        <p class="text-[10px] md:text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Document Identity</p>
                        <h3 class="mt-0.5 md:mt-1 text-xs md:text-sm font-semibold text-slate-900">Core details</h3>
                    </div>
                    <span class="rounded-full border border-red-300 bg-red-50 px-2 md:px-2.5 py-0.5 md:py-1 text-[9px] md:text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">Required</span>
                </div>

                <div class="mt-2 md:mt-3 grid grid-cols-1 md:grid-cols-[140px_minmax(0,1fr)] gap-2 md:gap-3">
                    <div>
                        <label class="block text-[10px] md:text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 mb-1">Tracking ID</label>
                        <input type="text" id="tracking_id" name="tracking_id"
                            value="<?= htmlspecialchars($draftDocument['tracking_id'] ?? $nextTrackingId) ?>"
                            class="w-full h-8 md:h-9 rounded-md border border-slate-300 bg-slate-50 px-2 font-mono text-[10px] md:text-xs font-semibold text-slate-700">
                    </div>
                    <div>
                        <label class="block text-[10px] md:text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 mb-1">Document Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" required placeholder="Subject of the document"
                            value="<?= htmlspecialchars($draftDocument['title'] ?? '') ?>"
                            class="w-full h-8 md:h-9 rounded-md border border-slate-300 px-2 text-xs md:text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-100">
                    </div>
                </div>
            </section>

            <!-- Document Body -->
            <section class="rounded-md border border-slate-200 bg-white p-2 md:p-2.5 shadow-sm">
                <div>
                    <p class="text-[10px] md:text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Document Body</p>
                    <h3 class="mt-0.5 md:mt-1 text-xs md:text-sm font-semibold text-slate-900">Description</h3>
                </div>
                <div class="mt-2 md:mt-3">
                    <label class="mb-1 block text-[10px] md:text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Body</label>
                    <div id="description" contenteditable="true"
                        class="min-h-[100px] md:min-h-[140px] lg:min-h-[200px] rounded-md border border-slate-300 bg-slate-50 p-2 md:p-2.5 text-xs md:text-sm leading-5 md:leading-6 text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-1 focus:ring-blue-100 prose max-w-none"><?= !empty($draftDocument['description']) ? htmlspecialchars($draftDocument['description']) : '' ?></div>
                    <input type="hidden" name="description" id="description-hidden" value="<?= htmlspecialchars($draftDocument['description'] ?? '') ?>">
                </div>
            </section>

            <!-- Attachments -->
            <section class="rounded-md border border-slate-200 bg-white p-2 md:p-2.5 shadow-sm">
                <label class="mb-1 block text-[10px] md:text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Attachments</label>
                <p class="mb-2 text-xs md:text-sm text-slate-500">Max 4 files, 40MB total.</p>
                <?php if (!empty($draftAttachments)): ?>
                    <div class="mt-1.5 md:mt-2" id="attachment-feedback">
                        <?php foreach ($draftAttachments as $a): ?>
                            <div class="text-xs md:text-sm"><a href="<?= htmlspecialchars($a['download_url']) ?>" target="_blank" class="text-blue-600 hover:underline"><?= htmlspecialchars($a['file_name']) ?></a></div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <input type="file" name="attachments[]" multiple data-max-files="4" data-max-total="41943040"
                        class="w-full rounded-lg border border-dashed border-slate-300 bg-white px-2 md:px-3 py-2 text-xs md:text-sm file:mr-2 file:border-0 file:bg-slate-100 file:text-slate-700 file:rounded-md file:px-2 file:py-1 hover:file:bg-slate-200">
                    <p id="attachment-feedback" class="mt-1 text-[10px] md:text-xs font-medium text-slate-500">No files selected.</p>
                <?php endif; ?>
            </section>
        </div>

        <!-- Right column: sidebar settings and actions -->
        <aside class="md:col-span-1 space-y-3 md:space-y-4">
            <section class="rounded-lg border border-slate-200 bg-white p-2 md:p-3 shadow-sm">
                <div>
                    <p class="text-[10px] md:text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Document Settings</p>
                    <h3 class="mt-0.5 md:mt-1 text-xs md:text-sm font-semibold text-slate-900">Fields</h3>
                </div>

                <div class="mt-2 md:mt-3 grid grid-cols-1 gap-2">
                    <div>
                        <label class="mb-1 block text-[10px] md:text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Type</label>
                        <?php $selType = $draftDocument['type'] ?? 'Memo'; ?>
                        <select name="type" class="w-full h-8 md:h-9 rounded-md border border-slate-300 bg-slate-50 px-2 text-xs text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-1 focus:ring-blue-100">
                            <option value="Memo" <?= $selType === 'Memo' ? 'selected' : '' ?>>Memo</option>
                            <option value="Letter" <?= $selType === 'Letter' ? 'selected' : '' ?>>Letter</option>
                            <option value="Report" <?= $selType === 'Report' ? 'selected' : '' ?>>Report</option>
                            <option value="Circular" <?= $selType === 'Circular' ? 'selected' : '' ?>>Circular</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-[10px] md:text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Priority</label>
                        <?php $selPriority = $draftDocument['priority'] ?? 'Medium'; ?>
                        <select name="priority" class="w-full h-8 md:h-9 rounded-md border border-slate-300 bg-slate-50 px-2 text-xs text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-1 focus:ring-blue-100">
                            <option value="Low" <?= $selPriority === 'Low' ? 'selected' : '' ?>>Low</option>
                            <option value="Medium" <?= $selPriority === 'Medium' ? 'selected' : '' ?>>Medium</option>
                            <option value="High" <?= $selPriority === 'High' ? 'selected' : '' ?>>High</option>
                            <option value="Urgent" <?= $selPriority === 'Urgent' ? 'selected' : '' ?>>Urgent</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-[10px] md:text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Due Date</label>
                        <input type="date" name="due_date" value="<?= htmlspecialchars($draftDocument['due_date'] ?? '') ?>" class="w-full h-8 md:h-9 rounded-md border border-slate-300 bg-slate-50 px-2 text-xs text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-1 focus:ring-blue-100">
                    </div>

                    <div>
                        <label class="mb-1 block text-[10px] md:text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Sender</label>
                        <input type="email" name="sender_email" value="<?= htmlspecialchars($draftDocument['sender_email'] ?? 'noreply@dalton.com.ph') ?>"
                            class="w-full h-8 md:h-9 rounded-md border border-slate-300 bg-slate-50 px-2 text-xs text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-1 focus:ring-blue-100">
                    </div>

                    <label for="confidential" class="flex items-start gap-2 md:gap-2.5 rounded-md border border-slate-200 bg-white px-2 md:px-2.5 py-1.5 md:py-2 shadow-sm transition hover:border-blue-200">
                        <input type="checkbox" name="is_confidential" id="confidential" <?= !empty($draftDocument['is_confidential']) ? 'checked' : '' ?> class="mt-1 h-3.5 md:h-4 w-3.5 md:w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="block text-xs md:text-sm font-semibold text-slate-800">Confidential</span>
                            <span class="mt-0.5 block text-[10px] md:text-xs leading-4 md:leading-5 text-slate-500">Limit visibility for sensitive items.</span>
                        </div>
                    </label>
                </div>
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-2 md:p-3 shadow-sm">
                <div>
                    <p class="text-[10px] md:text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Notes</p>
                    <h3 class="mt-0.5 md:mt-1 text-xs md:text-sm font-semibold text-slate-900">Remarks</h3>
                </div>
                <div class="mt-2 md:mt-3">
                    <label class="mb-1 block text-[10px] md:text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Internal notes</label>
                    <textarea name="notes" rows="2" placeholder="Optional remarks"
                        class="w-full rounded-md border border-slate-300 bg-slate-50 px-2 py-1.5 text-xs md:text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-1 focus:ring-blue-100"><?= htmlspecialchars($draftDocument['notes'] ?? '') ?></textarea>
                </div>
            </section>

            <section class="rounded-lg border border-slate-200 bg-slate-50 p-2 md:p-3 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] md:text-xs font-semibold uppercase text-slate-500">Summary</p>
                        <p class="mt-1 text-xs text-slate-700">Recipients: <span id="summary-recipient-count" class="font-semibold">0</span></p>
                        <p class="mt-0.5 text-xs text-slate-700">CC: <span id="summary-cc-count" class="font-semibold">0</span></p>
                    </div>
                    <div class="text-right text-xs text-slate-500">
                        <p id="summary-attachment-count">0</p>
                        <p id="summary-due-date" class="mt-1">—</p>
                    </div>
                </div>
            </section>

            <div id="formActions" class="flex flex-col-reverse sm:flex-row md:flex-col gap-2 pt-1">
                    <?php if (!empty($draftDocument)): ?>
                    <button type="button" onclick="saveDraftFromFinalize()"
                        class="h-8 md:h-9 px-2 md:px-3 rounded-md border border-slate-300 bg-white text-xs font-semibold text-slate-700 transition hover:bg-slate-50">Save Draft</button>
                    <button type="button" onclick="window.location.href='index.php?controller=correspondence&action=correspondence'"
                        class="h-8 md:h-9 px-2 md:px-3 rounded-md border border-slate-300 bg-white text-xs font-semibold text-slate-700 transition hover:bg-slate-50">Cancel</button>
                    <button type="submit"
                        class="h-8 md:h-9 px-3 md:px-4 rounded-md bg-blue-600 text-white text-xs font-semibold shadow-sm transition hover:bg-blue-700"><?= $isFinalizeMode ? 'Finalize' : (($currentUserLevel === 2) ? 'Draft & Notify' : 'Circulate') ?></button>
                <?php else: ?>
                    <button type="button" onclick="resetForm()"
                        class="h-8 md:h-9 px-2 md:px-3 rounded-md border border-slate-300 bg-white text-xs font-semibold text-slate-700 transition hover:bg-slate-50">Reset</button>
                    <button type="submit"
                        class="h-8 md:h-9 px-3 md:px-4 rounded-md bg-blue-600 text-white text-xs font-semibold shadow-sm transition hover:bg-blue-700"><?php echo ($currentUserLevel === 2) ? 'Draft & Notify' : 'Circulate'; ?></button>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</form>
<div id="editSnapshotContainer"></div>
