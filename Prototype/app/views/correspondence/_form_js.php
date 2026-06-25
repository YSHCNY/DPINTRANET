// Form-related behaviors: recipient drawer, chips, attachments, and form submit
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

bindSelectionFilter('recipients');
bindSelectionFilter('cc');
syncSelectionState();

// Drawer for recipient/CC picker
const recipientDrawer = document.getElementById('recipientDrawer');
const recipientDrawerOverlay = document.getElementById('recipientDrawerOverlay');
const drawerSearch = document.getElementById('drawer-search');
let drawerMode = 'recipients';

function openRecipientDrawer(mode) {
    drawerMode = mode === 'cc' ? 'cc' : 'recipients';
    document.getElementById('drawerTitleSmall').textContent = mode === 'cc' ? 'Add CC' : 'Add Recipients';
    document.getElementById('drawerTitle').textContent = mode === 'cc' ? 'Select CC recipients' : 'Select recipients';
    // pre-check boxes based on existing hidden inputs
    const existing = Array.from(document.querySelectorAll(`input[name="${mode}[]"]`)).map(i => String(i.value));
    document.querySelectorAll('[data-drawer-id]').forEach(cb => {
        cb.checked = existing.includes(String(cb.dataset.drawerId));
    });
    recipientDrawer.classList.remove('hidden');
    recipientDrawerOverlay.classList.remove('hidden');
    // slide in
    requestAnimationFrame(() => {
        recipientDrawer.classList.remove('translate-x-full');
        if (drawerSearch) drawerSearch.focus();
    });
}

function closeRecipientDrawer() {
    recipientDrawer.classList.add('translate-x-full');
    recipientDrawerOverlay.classList.add('hidden');
    setTimeout(() => recipientDrawer.classList.add('hidden'), 250);
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
    // gather checked
    const checked = Array.from(document.querySelectorAll('#drawerList input[type="checkbox"]:checked'));
    // remove existing inputs for current mode
    document.querySelectorAll(`input[name="${drawerMode}[]"]`).forEach(n => n.remove());

    const recipientsContainer = document.getElementById('recipients-chips');
    const ccContainer = document.getElementById('cc-chips');
    // only clear the container for the active drawer mode
    if (drawerMode === 'recipients') {
        recipientsContainer.innerHTML = '';
    } else {
        ccContainer.innerHTML = '';
    }

    checked.forEach(cb => {
        const id = cb.dataset.drawerId;
        const name = cb.dataset.drawerName || cb.dataset.drawerEmail || id;

        // create a hidden checkbox input (so it participates in selection logic)
        const input = document.createElement('input');
        input.type = 'checkbox';
        input.name = `${drawerMode}[]`;
        input.value = id;
        input.checked = true;
        input.style.display = 'none';
        input.setAttribute('data-selection-section', drawerMode);
        input.setAttribute('data-user-id', id);
        document.getElementById('circulationForm').appendChild(input);

        // create chip
        const chip = document.createElement('span');
        chip.className = 'inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-700';
        chip.textContent = name;
        const container = drawerMode === 'cc' ? ccContainer : recipientsContainer;
        // remove placeholder if exists
        const placeholder = container.querySelector('.text-sm.text-slate-500');
        if (placeholder) placeholder.remove();
        container.appendChild(chip);
    });

    // if nothing selected, restore placeholder
    if (!recipientsContainer.children.length) recipientsContainer.innerHTML = '<span id="recipients-placeholder" class="text-sm text-slate-500">No recipients selected</span>';
    if (!ccContainer.children.length) ccContainer.innerHTML = '<span id="cc-placeholder" class="text-sm text-slate-500">No CC selected</span>';

    closeRecipientDrawer();
    // refresh UI counts and chips
    syncSelectionState();
    updateRecipientBadge();
    updateSummaryPanel();
}

function updateRecipientBadge() {
    const badge = document.getElementById('recipients-count-badge');
    if (!badge) return;
    const count = document.querySelectorAll('input[name="recipients[]"]').length;
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
    const rCount = document.querySelectorAll('input[name="recipients[]"]').length;
    const cCount = document.querySelectorAll('input[name="cc[]"]').length;
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
    const fin = document.getElementById('finalizeInput'); if (fin) fin.remove();
    // ensure save flag exists
    let save = document.getElementById('saveDraftInput');
    if (!save) { save = document.createElement('input'); save.type = 'hidden'; save.name = 'save_draft'; save.id = 'saveDraftInput'; save.value = '1'; form.appendChild(save); }
    form.submit();
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

if (circulationForm) {
    circulationForm.addEventListener('submit', function(e) {
        if (!updateAttachmentFeedback()) {
            e.preventDefault();
        }
    }, { capture: true });
}
