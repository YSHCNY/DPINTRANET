document.addEventListener('DOMContentLoaded', function() {
    const threadFileInput = document.querySelector('input[name="thread_files[]"]');
    const attachmentStatus = document.getElementById('thread-attachment-status');

    if (!threadFileInput || !attachmentStatus) {
        return;
    }

    const maxFiles = parseInt(threadFileInput.dataset.maxFiles || '4', 10);
    const maxTotal = parseInt(threadFileInput.dataset.maxTotal || '41943040', 10);

    function formatBytes(bytes) {
        if (bytes >= 1024 * 1024) {
            return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
        }
        if (bytes >= 1024) {
            return `${(bytes / 1024).toFixed(1)} KB`;
        }
        return `${bytes} bytes`;
    }

    function updateThreadAttachmentStatus() {
        const files = Array.from(threadFileInput.files || []);
        const totalSize = files.reduce((sum, file) => sum + (file.size || 0), 0);

        if (files.length === 0) {
            attachmentStatus.textContent = `No attachments selected. Max ${maxFiles} files, ${formatBytes(maxTotal)} total.`;
            attachmentStatus.className = 'text-[10px] text-slate-500 mt-1 ml-1';
            attachmentStatus.classList.remove('text-rose-600', 'text-amber-600', 'text-emerald-600');
            attachmentStatus.classList.remove('hidden');
            return true;
        }

        let statusText = `${files.length} file(s) selected • ${formatBytes(totalSize)} total of ${formatBytes(maxTotal)}`;
        let valid = true;

        if (files.length > maxFiles) {
            statusText = `Too many files selected: ${files.length}/${maxFiles}. ${statusText}`;
            attachmentStatus.className = 'text-[10px] text-rose-600 mt-1 ml-1';
            valid = false;
        } else if (totalSize > maxTotal) {
            statusText = `Total size limit exceeded: ${formatBytes(totalSize)} / ${formatBytes(maxTotal)}. ${statusText}`;
            attachmentStatus.className = 'text-[10px] text-rose-600 mt-1 ml-1';
            valid = false;
        } else {
            attachmentStatus.className = 'text-[10px] text-emerald-600 mt-1 ml-1';
        }

        attachmentStatus.textContent = statusText;
        attachmentStatus.classList.remove('hidden');
        return valid;
    }

    threadFileInput.addEventListener('change', updateThreadAttachmentStatus);
    updateThreadAttachmentStatus();
});