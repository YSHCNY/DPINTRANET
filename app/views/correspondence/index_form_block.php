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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 md:gap-3 items-stretch">
                <section class="flex h-full flex-col rounded-md border border-slate-200 bg-white p-3 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Recipients</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">Recipients <span id="recipients-count-title" class="text-slate-500">(<?= count($draftRecipients ?? []) ?>)</span></p>
                        </div>
                        <button type="button" onclick="openRecipientDrawer('recipients')" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100">
                            Add
                        </button>
                    </div>

                    <div id="hidden-recipient-inputs" class="hidden">
                        <?php if (!empty($draftRecipients)): ?>
                            <?php foreach ($draftRecipients as $r): ?>
                                <input type="checkbox" name="recipients[]" value="<?= (int)$r['id'] ?>" checked data-selection-section="recipients" data-user-id="<?= (int)$r['id'] ?>">
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2" id="recipients-chips">
                        <?php if (!empty($draftRecipients)): ?>
                            <?php foreach ($draftRecipients as $r): ?>
                                <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700" data-user-id="<?= (int)$r['id'] ?>" data-selection-section="recipients">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-200 text-[10px] font-semibold uppercase text-slate-700"><?= htmlspecialchars(substr($r['name'], 0, 1)) ?></span>
                                    <span><?= htmlspecialchars($r['name']) ?></span>
                                    <button type="button" class="chip-remove ml-1 h-5 w-5 rounded-full text-slate-500 hover:bg-slate-200 hover:text-slate-700" aria-label="Remove recipient" data-user-id="<?= (int)$r['id'] ?>" data-selection-section="recipients">×</button>
                                </span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span id="recipients-placeholder" class="text-sm text-slate-500">No recipients selected.</span>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="flex h-full flex-col rounded-md border border-slate-200 bg-white p-3 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">CC</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">CC <span id="cc-count-title" class="text-slate-500">(<?= count($draftCc ?? []) ?>)</span></p>
                        </div>
                        <button type="button" onclick="openRecipientDrawer('cc')" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100">
                            Add
                        </button>
                    </div>

                    <div id="hidden-cc-inputs" class="hidden">
                        <?php if (!empty($draftCc)): ?>
                            <?php foreach ($draftCc as $c): ?>
                                <input type="checkbox" name="cc[]" value="<?= (int)$c['id'] ?>" checked data-selection-section="cc" data-user-id="<?= (int)$c['id'] ?>">
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2" id="cc-chips">
                        <?php if (!empty($draftCc)): ?>
                            <?php foreach ($draftCc as $c): ?>
                                <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700" data-user-id="<?= (int)$c['id'] ?>" data-selection-section="cc">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-200 text-[10px] font-semibold uppercase text-slate-700"><?= htmlspecialchars(substr($c['name'], 0, 1)) ?></span>
                                    <span><?= htmlspecialchars($c['name']) ?></span>
                                    <button type="button" class="chip-remove ml-1 h-5 w-5 rounded-full text-slate-500 hover:bg-slate-200 hover:text-slate-700" aria-label="Remove CC recipient" data-user-id="<?= (int)$c['id'] ?>" data-selection-section="cc">×</button>
                                </span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span id="cc-placeholder" class="text-sm text-slate-500">No CC selected.</span>
                        <?php endif; ?>
                    </div>
                </section>
            </div>

            <!-- Document Identity -->
            <section class="rounded-md border border-slate-200 bg-white p-3 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">Document identity</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">Core details</p>
                    </div>
                    <p class="text-xs text-slate-500">Required fields are marked with a red dot.</p>
                </div>

                <div class="mt-3 grid gap-3 sm:grid-cols-[140px_minmax(0,1fr)] sm:items-end">
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">
                            <span>Tracking ID</span>
                            <span class="inline-flex h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                        </label>
                        <input type="text" id="tracking_id" name="tracking_id"
                            value="<?= htmlspecialchars($draftDocument['tracking_id'] ?? $nextTrackingId) ?>"
                            class="w-full h-9 rounded-md border border-slate-300 bg-slate-50 px-2 font-mono text-sm font-semibold text-slate-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-100">
                    </div>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">
                            <span>Document Title</span>
                            <span class="inline-flex h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                        </label>
                        <input type="text" name="title" required placeholder="Subject of the document"
                            value="<?= htmlspecialchars($draftDocument['title'] ?? '') ?>"
                            class="w-full h-9 rounded-md border border-slate-300 px-2 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-100">
                    </div>
                </div>
            </section>

            <!-- Document Body -->
            <section class="rounded-md border border-slate-200 bg-white p-4 shadow-sm">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Document Body</p>
                </div>
                <div class="mt-3">
                    <div id="description" contenteditable="true" role="textbox" aria-label="Document body"
                        class="min-h-[96px] rounded-md border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-7 text-slate-900 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100 prose max-w-none">
                        <?= !empty($draftDocument['description']) ? $draftDocument['description'] : '' ?>
                    </div>
                    <input type="hidden" name="description" id="description-hidden" value="<?= $draftDocument['description'] ?? '' ?>">
                </div>
            </section>

            <!-- Attachments (compact file list) -->
            <section class="rounded-3xl border border-slate-200 bg-white p-3 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <label class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500">Attachments</label>
                    <span class="text-xs text-slate-500">Max 4 files · 40MB total</span>
                </div>

                <div class="mt-3 space-y-3">
                    <div id="existing-attachments-list" class="space-y-2">
                        <?php if (!empty($draftAttachments)): ?>
                            <?php foreach ($draftAttachments as $a): ?>
                                <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2" data-attachment-row data-attachment-id="<?= (int)$a['id'] ?>">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <svg class="h-5 w-5 flex-shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 3v6h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        <div class="min-w-0">
                                            <a href="<?= htmlspecialchars($a['download_url']) ?>" target="_blank" class="block truncate text-sm font-semibold text-slate-900" title="<?= htmlspecialchars($a['file_name']) ?>"><?= htmlspecialchars($a['file_name']) ?></a>
                                            <?php if (!empty($a['size'])): ?>
                                                <p class="mt-0.5 text-xs text-slate-500"><?= htmlspecialchars(number_format(((int)$a['size']) / 1024, 1)) ?> KB</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <button type="button" class="text-xs font-medium text-slate-500 hover:text-slate-900 remove-attachment" data-attachment-id="<?= (int)$a['id'] ?>">Remove</button>
                                        <a href="<?= htmlspecialchars($a['download_url']) ?>" class="text-xs font-medium text-blue-600 hover:text-blue-800">Download</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div id="removed-attachments-inputs" class="hidden"></div>

                    <div>
                        <input type="file" name="attachments[]" multiple data-max-files="4" data-max-total="41943040"
                            class="w-full rounded-2xl border border-dashed border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 file:mr-2 file:border-0 file:bg-slate-100 file:text-slate-700 file:rounded-md file:px-2 file:py-1 hover:file:bg-slate-200">
                        <p id="attachment-feedback" class="mt-2 text-xs text-slate-500">Attach files (optional)</p>
                    </div>
                </div>
            </section>
        </div>

        <!-- Right column: sidebar settings and actions -->
        <aside class="md:col-span-1 space-y-3 md:space-y-4">
            <section class="rounded-md border border-slate-200 bg-white p-3 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500">Document Settings</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">Properties</p>
                    </div>
                </div>

                <div class="mt-3 space-y-3">
                    <div class="grid grid-cols-2 gap-2">
                        <div class="grid gap-2">
                            <label class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">Type</label>
                            <?php $selType = $draftDocument['type'] ?? 'Memo'; ?>
                            <select name="type" class="w-full h-9 rounded-md border border-slate-200 bg-slate-50 px-3 text-sm text-slate-800 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-1 focus:ring-blue-100">
                                <option value="Memo" <?= $selType === 'Memo' ? 'selected' : '' ?>>Memo</option>
                                <option value="Letter" <?= $selType === 'Letter' ? 'selected' : '' ?>>Letter</option>
                                <option value="Report" <?= $selType === 'Report' ? 'selected' : '' ?>>Report</option>
                                <option value="Circular" <?= $selType === 'Circular' ? 'selected' : '' ?>>Circular</option>
                            </select>
                        </div>
                        <div class="grid gap-2">
                            <label class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">Priority</label>
                            <?php $selPriority = $draftDocument['priority'] ?? 'Medium'; ?>
                            <select name="priority" class="w-full h-9 rounded-md border border-slate-200 bg-slate-50 px-3 text-sm text-slate-800 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-1 focus:ring-blue-100">
                                <option value="Low" <?= $selPriority === 'Low' ? 'selected' : '' ?>>Low</option>
                                <option value="Medium" <?= $selPriority === 'Medium' ? 'selected' : '' ?>>Medium</option>
                                <option value="High" <?= $selPriority === 'High' ? 'selected' : '' ?>>High</option>
                                <option value="Urgent" <?= $selPriority === 'Urgent' ? 'selected' : '' ?>>Urgent</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="grid gap-2">
                            <label class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">Due Date</label>
                            <input type="date" name="due_date" value="<?= htmlspecialchars($draftDocument['due_date'] ?? '') ?>" class="w-full h-9 rounded-md border border-slate-200 bg-slate-50 px-3 text-sm text-slate-800 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-1 focus:ring-blue-100">
                        </div>
                        <div class="grid gap-2">
                            <label class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">Sender</label>
                            <input type="email" name="sender_email" value="<?= htmlspecialchars($draftDocument['sender_email'] ?? 'noreply@dalton.com.ph') ?>"
                                class="w-full h-9 rounded-md border border-slate-200 bg-slate-50 px-3 text-sm text-slate-800 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-1 focus:ring-blue-100">
                        </div>
                    </div>

                    <div class="rounded-md border border-slate-200 bg-slate-50 px-3 py-3">
                        <label for="confidential" class="flex items-start gap-3">
                            <input type="checkbox" name="is_confidential" id="confidential" <?= !empty($draftDocument['is_confidential']) ? 'checked' : '' ?> class="mt-1 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <div class="grid gap-1">
                                <span class="text-sm font-semibold text-slate-900">Confidential</span>
                                <span class="text-xs text-slate-500">Hide this document from general circulation.</span>
                            </div>
                        </label>
                    </div>
                </div>
            </section>

            <section class="rounded-md border border-slate-200 bg-white p-3 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500">Notes</p>
                    <span class="text-xs text-slate-500">Optional</span>
                </div>
                    <div class="mt-3">
                        <label class="sr-only" for="notes">Internal notes</label>
                        <textarea id="notes" name="notes" rows="2" placeholder="Add optional remarks..."
                            class="w-full min-h-[56px] max-h-[20rem] resize-vertical rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-1 focus:ring-blue-100"><?= htmlspecialchars($draftDocument['notes'] ?? '') ?></textarea>
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
                        class="h-8 md:h-9 px-3 md:px-4 rounded-md bg-blue-600 text-white text-xs font-semibold shadow-sm transition hover:bg-blue-700"><?= $isFinalizeMode ? 'Finalize' : (in_array($currentUserLevel, [2,6], true) ? 'Draft & Notify' : 'Circulate') ?></button>
                <?php else: ?>
                    <button type="button" onclick="resetForm()"
                        class="h-8 md:h-9 px-2 md:px-3 rounded-md border border-slate-300 bg-white text-xs font-semibold text-slate-700 transition hover:bg-slate-50">Reset</button>
                    <button type="submit"
                        class="h-8 md:h-9 px-3 md:px-4 rounded-md bg-blue-600 text-white text-xs font-semibold shadow-sm transition hover:bg-blue-700"><?php echo in_array($currentUserLevel, [2,6], true) ? 'Draft & Notify' : 'Circulate'; ?></button>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</form>
<div id="editSnapshotContainer"></div>
