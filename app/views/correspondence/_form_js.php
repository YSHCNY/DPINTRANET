// Form-related behaviors: recipient drawer, chips, attachments, and form submit
function isValidEmail(value) {
    if (!value) return false;
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim());
}

function showToast(type, message) {
    const typeClass = type === 'success'
        ? 'bg-emerald-50 border-emerald-300 text-emerald-900'
        : type === 'error'
            ? 'bg-rose-50 border-rose-300 text-rose-900'
            : 'bg-slate-50 border-slate-300 text-slate-900';

    const toast = document.createElement('div');
    toast.className = `fixed bottom-6 right-6 z-[99999] max-w-sm rounded-2xl border px-4 py-3 shadow-lg ${typeClass}`;
    toast.style.boxShadow = '0 20px 50px rgba(15,23,42,0.12)';

    const messageEl = document.createElement('div');
    messageEl.className = 'text-sm leading-6';
    messageEl.textContent = message;

    const closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'ml-3 inline-flex h-7 w-7 items-center justify-center rounded-full text-base font-semibold text-current opacity-80 hover:opacity-100';
    closeBtn.innerHTML = '&times;';
    closeBtn.addEventListener('click', () => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    });

    toast.appendChild(messageEl);
    toast.appendChild(closeBtn);
    document.body.appendChild(toast);

    window.setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 4500);
}

function setCustomEmailFeedback(section, message, tone = 'default') {
    const feedback = document.getElementById(section === 'cc' ? 'cc-email-feedback' : 'recipients-email-feedback');
    if (!feedback) return;
    feedback.textContent = message;
    feedback.classList.remove('text-slate-500', 'text-rose-600', 'text-emerald-600');
    if (tone === 'error') {
        feedback.classList.add('text-rose-600');
    } else if (tone === 'success') {
        feedback.classList.add('text-emerald-600');
    } else {
        feedback.classList.add('text-slate-500');
    }
}

function getCurrentSectionValues(section) {
    return Array.from(document.querySelectorAll(`input[name="${section}[]"]`))
        .filter(input => input.checked)
        .map(input => String(input.value));
}

function appendSelectionItem(section, value, label, options = {}) {
    const normalizedValue = String(value);
    const hiddenContainer = document.getElementById(section === 'cc' ? 'hidden-cc-inputs' : 'hidden-recipient-inputs');
    const visibleContainer = document.getElementById(section === 'cc' ? 'cc-chips' : 'recipients-chips');

    if (!hiddenContainer || !visibleContainer) return false;
    if (getCurrentSectionValues(section).some(current => String(current).toLowerCase() === normalizedValue.toLowerCase())) {
        return false;
    }

    const existingChip = Array.from(visibleContainer.querySelectorAll('[data-user-id]')).some(chip => String(chip.dataset.userId || '').toLowerCase() === normalizedValue.toLowerCase());
    if (existingChip) {
        return false;
    }

    const placeholder = visibleContainer.querySelector('[id$="-placeholder"]');
    if (placeholder) placeholder.remove();

    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'checkbox';
    hiddenInput.name = `${section}[]`;
    hiddenInput.value = normalizedValue;
    hiddenInput.checked = true;
    hiddenInput.hidden = true;
    hiddenInput.setAttribute('data-selection-section', section);
    hiddenInput.setAttribute('data-user-id', normalizedValue);
    hiddenContainer.appendChild(hiddenInput);

    const chip = document.createElement('span');
    chip.className = options.kind === 'custom'
        ? 'inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700'
        : 'inline-flex items-center gap-2 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700';
    chip.setAttribute('data-user-id', normalizedValue);
    chip.setAttribute('data-selection-section', section);

    const initials = document.createElement('span');
    initials.className = options.kind === 'custom'
        ? 'flex h-6 w-6 items-center justify-center rounded-full bg-amber-200 text-[10px] font-semibold uppercase text-amber-700'
        : 'flex h-6 w-6 items-center justify-center rounded-full bg-slate-200 text-[10px] font-semibold uppercase text-slate-700';
    initials.textContent = options.kind === 'custom' ? 'EM' : String(label || '•').charAt(0).toUpperCase();

    const labelEl = document.createElement('span');
    labelEl.textContent = label;

    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'chip-remove ml-1 h-5 w-5 rounded-full text-slate-500 hover:bg-slate-200 hover:text-slate-700';
    removeBtn.setAttribute('aria-label', `Remove ${section} recipient`);
    removeBtn.setAttribute('data-user-id', normalizedValue);
    removeBtn.setAttribute('data-selection-section', section);
    removeBtn.textContent = '×';

    chip.appendChild(initials);
    chip.appendChild(labelEl);
    chip.appendChild(removeBtn);
    visibleContainer.appendChild(chip);

    return true;
}

function addCustomEmailRecipient(section) {
    const input = document.getElementById(section === 'cc' ? 'cc-email-input' : 'recipients-email-input');
    if (!input) return;

    const rawValue = input.value.trim().toLowerCase();
    if (!rawValue) {
        setCustomEmailFeedback(section, 'Enter an email address to add an external recipient.', 'error');
        return;
    }

    if (!isValidEmail(rawValue)) {
        setCustomEmailFeedback(section, 'Please enter a valid email address.', 'error');
        return;
    }

    const normalizedValue = `email:${rawValue}`;
    if (!appendSelectionItem(section, normalizedValue, rawValue, { kind: 'custom' })) {
        setCustomEmailFeedback(section, 'That email address is already in the list.', 'error');
        return;
    }

    input.value = '';
    setCustomEmailFeedback(section, 'External recipient added.', 'success');
    syncSelectionState();
    updateRecipientBadge();
    updateSummaryPanel();
    updateRecipientTitleCounts();
    bindChipRemoval();
}

function syncSelectionState() {
    const recipientChecks = Array.from(document.querySelectorAll('input[data-selection-section="recipients"]'));
    const ccChecks = Array.from(document.querySelectorAll('input[data-selection-section="cc"]'));
    const selectedRecipients = new Set(recipientChecks.filter(cb => cb.checked).map(cb => String(cb.dataset.userId)));
    const selectedCc = new Set(ccChecks.filter(cb => cb.checked).map(cb => String(cb.dataset.userId)));

    const recipientCount = document.getElementById('recipients-count');
    const ccCount = document.getElementById('cc-count');

    if (recipientCount) {
        recipientCount.textContent = `${selectedRecipients.size} selected`;
    }

    if (ccCount) {
        ccCount.textContent = `${selectedCc.size} selected`;
    }

    updateRecipientTitleCounts();

    document.querySelectorAll('input[data-selection-section]').forEach(cb => {
        const section = cb.dataset.selectionSection;
        const userId = String(cb.dataset.userId || '');
        const label = cb.closest('[data-user-card]');
        const counterpartSelected = section === 'recipients'
            ? selectedCc.has(userId)
            : selectedRecipients.has(userId);

        cb.disabled = counterpartSelected && !cb.checked;

        if (label) {
            label.classList.toggle('border-blue-300', cb.checked);
            label.classList.toggle('bg-blue-50', cb.checked);
            label.classList.toggle('ring-2', cb.checked);
            label.classList.toggle('ring-blue-100', cb.checked);
            label.classList.toggle('opacity-60', counterpartSelected && !cb.checked);
        }
    });

    // Update compact summary as chips for recipients and cc
    function computeSelected(sectionChecks) {
        return sectionChecks.filter(cb => cb.checked).map(cb => {
            const userId = String(cb.dataset.userId || '');
            const label = cb.closest('[data-user-card]');
            if (!label) return null;
            const nameEl = label.querySelector('.user-fullname');
            const deptEl = label.querySelector('.flex > span');
            const emailEl = label.querySelector('p');
            const name = (nameEl && nameEl.textContent.trim()) || (deptEl && deptEl.textContent.trim()) || (emailEl && emailEl.textContent.trim()) || userId;
            return { id: userId, name };
        }).filter(Boolean);
    }

    function renderChips(container, items, section) {
        if (!container) return;
        container.innerHTML = '';
        if (!items || items.length === 0) {
            container.textContent = section === 'recipients' ? 'No recipients selected' : 'No CC selected';
            return;
        }

        const maxVisible = 6;
        items.slice(0, maxVisible).forEach(it => {
            const chip = document.createElement('span');
            chip.className = 'inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-base text-slate-700 mr-2 mb-2';
            chip.setAttribute('data-user-id', it.id);
            const label = document.createElement('span');
            label.textContent = it.name;
            chip.appendChild(label);

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'chip-remove ml-2 text-slate-500';
            remove.setAttribute('aria-label', 'Remove');
            remove.textContent = '×';
            remove.addEventListener('click', function(e) {
                e.stopPropagation();
                const cb = document.querySelector(`input[data-selection-section="${section}"][data-user-id="${it.id}"]`);
                if (cb) { cb.checked = false; syncSelectionState(); }
            });

            chip.appendChild(remove);
            container.appendChild(chip);
        });

        if (items.length > maxVisible) {
            const more = document.createElement('span');
            more.className = 'text-sm text-slate-500 align-middle';
            more.textContent = `+${items.length - maxVisible} more`;
            container.appendChild(more);
        }
    }

    const recipientsSummaryEl = document.getElementById('recipients-summary');
    const ccSummaryEl = document.getElementById('cc-summary');

    renderChips(recipientsSummaryEl, computeSelected(recipientChecks), 'recipients');
    renderChips(ccSummaryEl, computeSelected(ccChecks), 'cc');
}

function bindSelectionFilter(section) {
    const filter = document.querySelector(`[data-selection-filter="${section}"]`);
    const cards = Array.from(document.querySelectorAll(`[data-selection-list="${section}"] [data-user-card]`));

    if (!filter) return;

    filter.addEventListener('input', function() {
        const term = this.value.toLowerCase().trim();
        cards.forEach(card => {
            const haystack = String(card.dataset.userFilter || '');
            card.classList.toggle('hidden', term !== '' && !haystack.includes(term));
        });
    });
}

document.querySelectorAll('input[data-selection-section]').forEach(cb => {
    cb.addEventListener('change', function() {
        if (this.checked) {
            const oppositeSection = this.dataset.selectionSection === 'recipients' ? 'cc' : 'recipients';
            document.querySelectorAll(`input[data-selection-section="${oppositeSection}"][data-user-id="${this.dataset.userId}"]`).forEach(other => {
                other.checked = false;
            });
        }

        syncSelectionState();
    });
});

function bindChipRemoval() {
    document.querySelectorAll('.chip-remove').forEach(btn => {
        btn.addEventListener('click', function(event) {
            event.stopPropagation();
            const userId = this.dataset.userId;
            const section = this.dataset.selectionSection;
            const hiddenInput = document.querySelector(`input[name="${section}[]"][data-user-id="${userId}"]`);
            if (hiddenInput) {
                hiddenInput.remove();
            }
            const chip = this.closest('span[data-user-id]');
            if (chip) {
                chip.remove();
            }
            syncSelectionState();
            updateSummaryPanel();
            updateRecipientBadge();
            updateRecipientTitleCounts();
        });
    });
}

bindSelectionFilter('recipients');
bindSelectionFilter('cc');
syncSelectionState();
bindChipRemoval();

document.querySelectorAll('[data-custom-email-input]').forEach(input => {
    input.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            addCustomEmailRecipient(this.dataset.customEmailInput);
        }
    });
});

// Drawer for recipient/CC picker
const recipientDrawer = document.getElementById('recipientDrawer');
const recipientDrawerOverlay = document.getElementById('recipientDrawerOverlay');
const drawerSearch = document.getElementById('drawer-search');
let drawerMode = 'recipients';

function syncDrawerCheckboxesWithSelection(mode) {
    const existing = getCurrentSectionValues(mode);
    document.querySelectorAll('[data-drawer-id]').forEach(cb => {
        cb.checked = existing.includes(String(cb.dataset.drawerId));
    });
}

function openRecipientDrawer(mode) {
    drawerMode = mode === 'cc' ? 'cc' : 'recipients';
    document.getElementById('drawerTitleSmall').textContent = mode === 'cc' ? 'Add CC' : 'Add Recipients';
    document.getElementById('drawerTitle').textContent = mode === 'cc' ? 'Select CC recipients' : 'Select recipients';
    syncDrawerCheckboxesWithSelection(drawerMode);
    recipientDrawer.classList.remove('hidden');
    recipientDrawerOverlay.classList.remove('hidden');
    // slide in
    requestAnimationFrame(() => {
        recipientDrawer.classList.remove('translate-x-full');
        if (drawerSearch) drawerSearch.focus();
    });
}

function closeRecipientDrawer() {
    if (recipientDrawer) {
        syncDrawerCheckboxesWithSelection(drawerMode);
        recipientDrawer.classList.add('translate-x-full');
    }
    if (recipientDrawerOverlay) {
        recipientDrawerOverlay.classList.add('hidden');
    }
    setTimeout(() => {
        if (recipientDrawer) recipientDrawer.classList.add('hidden');
    }, 250);
}

if (drawerSearch) {
    drawerSearch.addEventListener('input', function() {
        const term = this.value.toLowerCase().trim();
        document.querySelectorAll('#drawerList [data-drawer-filter]').forEach(el => {
            const hay = el.getAttribute('data-drawer-filter') || '';
            el.classList.toggle('hidden', term !== '' && !hay.includes(term));
        });
    });
}

function applyRecipientDrawer() {
    const checked = Array.from(document.querySelectorAll('#drawerList input[type="checkbox"]:checked'));
    const hiddenContainer = document.getElementById(drawerMode === 'recipients' ? 'hidden-recipient-inputs' : 'hidden-cc-inputs');
    const visibleContainer = document.getElementById(drawerMode === 'recipients' ? 'recipients-chips' : 'cc-chips');

    if (!hiddenContainer || !visibleContainer) return;

    let addedCount = 0;
    checked.forEach(cb => {
        const id = String(cb.dataset.drawerId || '');
        const name = cb.dataset.drawerName || cb.dataset.drawerEmail || id;
        if (appendSelectionItem(drawerMode, id, name, { kind: 'user' })) {
            addedCount += 1;
        }
    });

    if (addedCount === 0 && !visibleContainer.querySelector('[data-user-id]')) {
        visibleContainer.innerHTML = drawerMode === 'recipients'
            ? '<span id="recipients-placeholder" class="text-sm text-slate-500">No recipients selected.</span>'
            : '<span id="cc-placeholder" class="text-sm text-slate-500">No CC selected.</span>';
    }

    closeRecipientDrawer();
    syncSelectionState();
    updateRecipientBadge();
    updateSummaryPanel();
    updateRecipientTitleCounts();
    bindChipRemoval();
}

function updateRecipientTitleCounts() {
    const recipientsCount = document.querySelectorAll('input[name="recipients[]"]:checked').length;
    const ccCount = document.querySelectorAll('input[name="cc[]"]:checked').length;
    const recipientsTitle = document.getElementById('recipients-count-title');
    const ccTitle = document.getElementById('cc-count-title');

    if (recipientsTitle) {
        recipientsTitle.textContent = `(${recipientsCount})`;
    }
    if (ccTitle) {
        ccTitle.textContent = `(${ccCount})`;
    }
}


function updateRecipientBadge() {
    const badge = document.getElementById('recipients-count-badge');
    if (!badge) return;
    const count = document.querySelectorAll('input[name="recipients[]"]:checked').length;
    if (count > 0) {
        badge.textContent = String(count);
        badge.classList.remove('hidden');
    } else {
        badge.classList.add('hidden');
    }
}

// Close drawer on Escape when open
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (recipientDrawer && !recipientDrawer.classList.contains('hidden')) {
            closeRecipientDrawer();
        }
    }
});

// initialize badge state on page load
updateRecipientBadge();

// update summary counts (recipients, cc, attachments, due date)
function updateSummaryPanel() {
    const rCount = document.querySelectorAll('input[name="recipients[]"]:checked').length;
    const cCount = document.querySelectorAll('input[name="cc[]"]:checked').length;
    const aCount = (document.querySelector('input[name="attachments[]"]')?.files || []).length;
    const due = document.querySelector('input[name="due_date"]')?.value || '—';

    const rEl = document.getElementById('summary-recipient-count');
    const cEl = document.getElementById('summary-cc-count');
    const aEl = document.getElementById('summary-attachment-count');
    const dEl = document.getElementById('summary-due-date');

    if (rEl) rEl.textContent = String(rCount);
    if (cEl) cEl.textContent = String(cCount);
    if (aEl) aEl.textContent = `${aCount} file(s)`;
    if (dEl) dEl.textContent = due;
}

// call when selection changes or attachments/due date change
document.addEventListener('change', function(e) {
    if (['recipients[]','cc[]','attachments[]','due_date'].some(name => (e.target.name || '') === name)) {
        updateSummaryPanel();
        updateRecipientBadge();
    }
});

// initialize summary
updateSummaryPanel();

const circulationForm = document.getElementById('circulationForm');
if (circulationForm) {
    circulationForm.addEventListener('submit', function() {
        const desc = document.getElementById('description');
        if (desc) {
            document.getElementById('description-hidden').value = desc.innerHTML;
        }
    });
}

function getCorrespondenceEntryUrl() {
    const pathname = window.location.pathname || '/';
    const origin = window.location.origin || '';
    const normalizedPath = pathname.replace(/\/+$/, '');

    if (normalizedPath.endsWith('/Public/index.php') || normalizedPath.endsWith('/index.php')) {
        return `${origin}${normalizedPath}`;
    }

    if (normalizedPath.endsWith('/Public')) {
        return `${origin}${normalizedPath}/index.php`;
    }

    return `${origin}${normalizedPath}/Public/index.php`;
}

function buildProgressUrl(documentId) {
    const entryUrl = getCorrespondenceEntryUrl();
    return new URL(`?controller=correspondence&action=emailProgress&id=${encodeURIComponent(documentId)}`, entryUrl);
}

function showEmailProgressModal(documentId) {
    const existing = document.getElementById('email-progress-modal');
    if (existing) {
        existing.remove();
    }

    const modal = document.createElement('div');
    modal.id = 'email-progress-modal';
    modal.className = 'fixed inset-0 z-[200] flex items-center justify-center bg-slate-900/60 px-4';
    modal.innerHTML = `
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-slate-900">Sending Email Notifications...</p>
                    <p class="mt-1 text-sm text-slate-500">Your correspondence is being processed in the background.</p>
                </div>
                <button type="button" id="email-progress-close" class="text-sm font-semibold text-slate-500 hover:text-slate-700">Close</button>
            </div>
            <div class="mt-5">
                <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                    <div id="email-progress-bar" class="h-full w-0 rounded-full bg-blue-600 transition-all duration-300"></div>
                </div>
                <div class="mt-3 flex items-center justify-between text-sm text-slate-600">
                    <span id="email-progress-text">0 / 0 Sent</span>
                    <span id="email-progress-percent">0%</span>
                </div>
                <p id="email-progress-status" class="mt-3 text-sm text-slate-500">Preparing background progress…</p>
                <div class="mt-4 grid grid-cols-2 gap-3 text-sm text-slate-600">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Failed</p>
                        <p id="email-progress-failed" class="mt-1 text-base font-semibold text-slate-900">0</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Remaining</p>
                        <p id="email-progress-remaining" class="mt-1 text-base font-semibold text-slate-900">0</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);

    const closeBtn = document.getElementById('email-progress-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', () => modal.remove());
    }

    const poll = () => {
        const progressUrl = buildProgressUrl(documentId);

        fetch(progressUrl.toString(), {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(async response => {
                const rawText = await response.text();
                let data = {};

                try {
                    data = rawText ? JSON.parse(rawText) : {};
                } catch (error) {
                    data = { success: false, message: 'Unable to load progress.' };
                }

                const bar = document.getElementById('email-progress-bar');
                const text = document.getElementById('email-progress-text');
                const percent = document.getElementById('email-progress-percent');
                const failed = document.getElementById('email-progress-failed');
                const remaining = document.getElementById('email-progress-remaining');
                const status = document.getElementById('email-progress-status');

                if (!bar || !text || !percent || !failed || !remaining) {
                    return;
                }

                const total = Number(data.total || 0);
                const sent = Number(data.sent || 0);
                const failedCount = Number(data.failed || 0);
                const remainingCount = Number(data.remaining || 0);
                const progress = Number(data.progress || 0);

                bar.style.width = `${Math.min(progress, 100)}%`;
                text.textContent = `${sent} / ${total} Sent`;
                percent.textContent = `${progress}%`;
                failed.textContent = String(failedCount);
                remaining.textContent = String(remainingCount);

                if (status) {
                    if (response.ok && data.success) {
                        status.textContent = total > 0 && (sent + failedCount) >= total
                            ? 'All notifications have finished processing.'
                            : 'Waiting for the background worker to finish…';
                        status.className = 'mt-3 text-sm text-slate-500';
                    } else {
                        status.textContent = data.message || 'Waiting for the background worker…';
                        status.className = 'mt-3 text-sm text-amber-600';
                    }
                }

                if ((total === 0 && data.success) || (total > 0 && (sent + failedCount) >= total)) {
                    clearInterval(intervalId);
                    if (modal) {
                        modal.remove();
                    }
                    showToast('success', 'Correspondence saved and email notifications are complete. Redirecting to your correspondence dashboard...');
                    setTimeout(() => {
                        window.location.href = new URL('?controller=correspondence&action=correspondence', getCorrespondenceEntryUrl()).toString();
                    }, 1100);
                }
            })
            .catch(error => {
                const status = document.getElementById('email-progress-status');
                if (status) {
                    status.textContent = error.message || 'Unable to reach the progress endpoint yet.';
                    status.className = 'mt-3 text-sm text-amber-600';
                }
            });
    };

    poll();
    const intervalId = window.setInterval(poll, 2000);
}

function resetForm() {
    if (!circulationForm) return;

    circulationForm.reset();
    const desc = document.getElementById('description');
    if (desc) {
        desc.innerHTML = '';
    }

    document.querySelectorAll('[data-selection-filter]').forEach(input => {
        input.value = '';
    });

    document.querySelectorAll('[data-user-card]').forEach(card => {
        card.classList.remove('hidden');
    });

    syncSelectionState();
    updateAttachmentFeedback();
}

function saveDraftFromFinalize() {
    const form = document.getElementById('circulationForm');
    if (!form) return;

    // remove finalize marker so update() treats this as a save-draft
    const fin = document.getElementById('finalizeInput');
    if (fin) fin.remove();

    const desc = document.getElementById('description');
    if (desc) {
        const hiddenDesc = document.getElementById('description-hidden');
        if (hiddenDesc) hiddenDesc.value = desc.innerHTML;
    }

    if (!updateAttachmentFeedback()) {
        return;
    }

    // ensure save flag exists
    let save = document.getElementById('saveDraftInput');
    if (!save) {
        save = document.createElement('input');
        save.type = 'hidden';
        save.name = 'save_draft';
        save.id = 'saveDraftInput';
        save.value = '1';
        form.appendChild(save);
    }

    if (typeof form.requestSubmit === 'function') {
        form.requestSubmit();
    } else {
        form.submit();
    }
}

function updateAttachmentFeedback() {
    const input = document.querySelector('input[name="attachments[]"]');
    const feedback = document.getElementById('attachment-feedback');
    if (!input || !feedback) return true;

    const files = Array.from(input.files || []);
    const maxFiles = parseInt(input.dataset.maxFiles || '4', 10);
    const maxTotal = parseInt(input.dataset.maxTotal || '41943040', 10);
    const totalSize = files.reduce((sum, file) => sum + (file.size || 0), 0);

    feedback.classList.remove('text-rose-600', 'text-amber-600', 'text-emerald-600', 'text-slate-500');

    if (files.length === 0) {
        feedback.textContent = 'No files selected.';
        feedback.classList.add('text-slate-500');
        return true;
    }

    if (files.length > maxFiles) {
        feedback.textContent = `Too many files selected. Max ${maxFiles} files allowed.`;
        feedback.classList.add('text-rose-600');
        return false;
    }

    if (totalSize > maxTotal) {
        feedback.textContent = 'Selected files exceed the 40MB total limit.';
        feedback.classList.add('text-rose-600');
        return false;
    }

    feedback.textContent = `${files.length} file(s) selected, ${(totalSize / (1024 * 1024)).toFixed(1)} MB total.`;
    feedback.classList.add('text-emerald-600');
    return true;
}

const attachmentInput = document.querySelector('input[name="attachments[]"]');
if (attachmentInput) {
    attachmentInput.addEventListener('change', updateAttachmentFeedback);
}

document.body.addEventListener('click', function(e) {
    const target = e.target.closest('.remove-attachment');
    if (!target) return;
    e.preventDefault();

    const attachmentRow = target.closest('[data-attachment-row]');
    if (!attachmentRow) return;

    const attachmentId = target.dataset.attachmentId;
    if (!attachmentId) return;

    const removedContainer = document.getElementById('removed-attachments-inputs');
    if (!removedContainer) return;

    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'removed_attachments[]';
    hiddenInput.value = attachmentId;
    removedContainer.appendChild(hiddenInput);

    attachmentRow.remove();
    updateAttachmentFeedback();
});

if (circulationForm) {
    circulationForm.addEventListener('submit', async function(e) {
        const desc = document.getElementById('description');
        if (desc) {
            document.getElementById('description-hidden').value = desc.innerHTML;
        }

        if (!updateAttachmentFeedback()) {
            e.preventDefault();
            return;
        }

        e.preventDefault();

        const formData = new FormData(circulationForm);
        const submitButton = circulationForm.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
        }

        try {
            const formAction = new URL(circulationForm.action, window.location.href);
            const response = await fetch(formAction, {
                method: circulationForm.method || 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json().catch(() => ({ success: false, message: 'Unable to process request.' }));

            if (data.success && data.document_id) {
                showEmailProgressModal(data.document_id);
            } else {
                window.alert(data.message || 'Unable to process request.');
            }
        } catch (error) {
            window.alert('Unable to process request. Please try again.');
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
            }
        }
    }, { capture: true });
}

function handleFormSuccess(responseText, fallbackMessage) {
    const message = fallbackMessage || 'Action completed.';
    if (responseText && responseText.includes('success')) {
        const match = responseText.match(/document_id=([0-9]+)/i);
        if (match && match[1]) {
            showEmailProgressModal(match[1]);
        }
    }
    window.alert(message);
}
