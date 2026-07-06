<?php require __DIR__ . '/../partials/icons.php'; ?>
<?php $filesCateg = $filesCateg ?? []; $recipientsCateg = $recipientsCateg ?? []; ?>

<div class="min-h-screen theme-palette">
  <div class="max-w-4xl mx-auto px-4 py-6 lg:px-5 lg:py-7">
    <div class="mb-6 overflow-hidden rounded-[26px] border border-slate-200 bg-white shadow-[0_14px_40px_rgba(15,23,42,0.06)]">
      <div class="border-b border-slate-200 bg-slate-50 px-5 py-4">
        <p class="text-[10px] font-semibold uppercase tracking-[0.26em] text-emerald-600">Files Repository</p>
        <h2 class="mt-1 text-xl font-semibold tracking-tight text-slate-900">Upload New File</h2>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
          Add a file with a clear path, category, and description so it stays easy to find later.
        </p>
      </div>

      <div class="p-4 lg:p-5">
        <form action="index.php?controller=Files&action=store" method="post" enctype="multipart/form-data" class="space-y-5">
          <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_320px] gap-5">
            <section class="space-y-4">
              <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-4 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                  <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">Routing</p>
                    <h3 class="mt-1 text-base font-semibold text-slate-900">From and to categories</h3>
                  </div>
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="routingToggle" class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-2 focus:ring-emerald-500">
                    <span class="text-sm font-medium text-slate-600">Enable Routing</span>
                  </label>
                </div>

                <div id="routingFields" class="mt-3 grid grid-cols-1 md:grid-cols-[1fr_auto_1fr] gap-3 items-end opacity-50 pointer-events-none transition-opacity duration-200">
                  <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">From</label>
                    <select name="fromCategory" class="w-full h-10 rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                      <option value="">No Routing</option>
                      <?php foreach ($recipientsCateg as $category): ?>
                        <option value="<?= htmlspecialchars($category['category']) ?>">
                          <?= htmlspecialchars($category['category']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="hidden md:flex items-center justify-center pb-2 text-slate-400">
                    <?= $viseVersaIcon ?? '' ?>
                  </div>
                  <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">To</label>
                    <select name="toCategory" class="w-full h-10 rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                      <option value="">No Routing</option>
                      <?php foreach ($recipientsCateg as $category): ?>
                        <option value="<?= htmlspecialchars($category['category']) ?>">
                          <?= htmlspecialchars($category['category']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>

              <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">Classification</p>
                <h3 class="mt-1 text-base font-semibold text-slate-900">File category and description</h3>

                <div class="mt-3 space-y-3">
                  <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">File Category</label>
                    <select name="fileCategory" required class="w-full h-10 rounded-2xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                      <?php if (!empty($filesCateg)): ?>
                        <?php foreach ($filesCateg as $category): ?>
                          <option value="<?= htmlspecialchars($category['category']) ?>"><?= htmlspecialchars($category['category']) ?></option>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <option value="">No categories available</option>
                      <?php endif; ?>
                    </select>
                  </div>

                  <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">Description</label>
                    <textarea name="description" required rows="5" placeholder="Enter a concise file description"
                              class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-3 py-3 text-sm leading-6 text-slate-700 outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-100"></textarea>
                  </div>
                </div>
              </div>

              <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">Upload</p>
                <h3 class="mt-1 text-base font-semibold text-slate-900">Drop your files</h3>
                <p class="mt-2 text-[11px] text-slate-500">Upload up to 4 files with a maximum total size of 40MB</p>

                <div id="dropZone" class="mt-3 rounded-3xl border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center transition hover:border-emerald-300 hover:bg-emerald-50/50">
                  <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white shadow-sm">
                    <span class="text-2xl text-emerald-600"><?= $cloudIcon ?? '☁️' ?></span>
                  </div>
                  <p class="mt-3 text-sm font-semibold text-slate-900">Drag & drop files here</p>
                  <p class="mt-1 text-xs text-slate-500">or click to browse your computer</p>
                  <div id="fileList" class="mt-3 space-y-2"></div>
                  <input type="file" id="fileInput" name="files[]" class="hidden" multiple required>
                </div>

                <div id="filePreview" class="mt-3 space-y-2"></div>
                <div id="sizeWarning" class="mt-3 hidden rounded-2xl border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700">
                  Total file size exceeds 40MB limit
                </div>
              </div>
            </section>

            <aside class="space-y-4">
              <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
                <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">Checklist</p>
                <h3 class="mt-1 text-base font-semibold text-slate-900">Before upload</h3>
                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                  <li class="flex gap-2">
                    <span class="mt-1 h-2 w-2 rounded-full bg-slate-400"></span>
                    Choose the correct routing categories.
                  </li>
                  <li class="flex gap-2">
                    <span class="mt-1 h-2 w-2 rounded-full bg-emerald-500"></span>
                    Use a clear file category and description.
                  </li>
                  <li class="flex gap-2">
                    <span class="mt-1 h-2 w-2 rounded-full bg-slate-400"></span>
                    Confirm the file you selected is final.
                  </li>
                </ul>
              </div>

              <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                <label class="mb-1 block text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">Actions</label>
                <div class="space-y-3">
                  <a href="index.php?controller=Files&action=files"
                     class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Cancel
                  </a>
                  <button type="submit"
                          class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                    Upload File
                  </button>
                </div>
              </div>
            </aside>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
const MAX_FILES = 4;
const MAX_TOTAL_SIZE = 40 * 1024 * 1024; // 40MB in bytes
const MAX_FILE_SIZE = 20 * 1024 * 1024; // 20MB per file
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');
const filePreview = document.getElementById('filePreview');
const sizeWarning = document.getElementById('sizeWarning');
const fileList = document.getElementById('fileList');
const routingToggle = document.getElementById('routingToggle');
const routingFields = document.getElementById('routingFields');

function formatFileSize(bytes) {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

function validateAndDisplay(files) {
  const fileArray = Array.from(files);
  
  // Check max files limit
  if (fileArray.length > MAX_FILES) {
    alert(`Maximum ${MAX_FILES} files allowed`);
    return;
  }

  // Calculate total size
  let totalSize = 0;
  fileArray.forEach(file => {
    totalSize += file.size;
  });

  // Check total size limit
  if (totalSize > MAX_TOTAL_SIZE) {
    sizeWarning.classList.remove('hidden');
    fileInput.value = '';
    filePreview.innerHTML = '';
    return;
  }

  sizeWarning.classList.add('hidden');
  const dataTransfer = new DataTransfer();
  fileArray.forEach(file => dataTransfer.items.add(file));
  fileInput.files = dataTransfer.files;

  // Display file preview
  filePreview.innerHTML = '';
  fileArray.forEach((file, index) => {
    const fileItem = document.createElement('div');
    fileItem.className = 'flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3';
    fileItem.innerHTML = `
      <div class="flex items-center gap-3">
        <span class="h-10 w-10 flex items-center justify-center rounded-lg bg-sky-100 text-sky-600 font-semibold text-sm">
          ${file.name.split('.').pop().toUpperCase()}
        </span>
        <div>
          <p class="text-sm font-medium break-words text-balance text-slate-900">${file.name}</p>
          <p class="text-xs text-slate-500">${formatFileSize(file.size)}</p>
        </div>
      </div>
      <button type="button" onclick="removeFile(${index})" class="text-rose-600 hover:text-rose-700 font-semibold text-sm">Remove</button>
    `;
    filePreview.appendChild(fileItem);
  });

  // Update file list summary
  const totalSizeDisplay = formatFileSize(totalSize);
  fileList.innerHTML = `
    <p class="text-sm font-medium text-slate-700">
      <span class="text-blue-600">${fileArray.length}</span> file(s) selected - <span class="text-blue-600">${totalSizeDisplay}</span> of 40MB
    </p>
  `;
}

function removeFile(index) {
  const files = Array.from(fileInput.files);
  files.splice(index, 1);
  
  // Create a new DataTransfer object to update the input
  const dt = new DataTransfer();
  files.forEach(file => {
    dt.items.add(file);
  });
  
  validateAndDisplay(dt.files);
}

// Routing toggle functionality
if (routingToggle && routingFields) {
  const fromSelect = routingFields.querySelector('select[name="fromCategory"]');
  const toSelect = routingFields.querySelector('select[name="toCategory"]');

  routingToggle.addEventListener('change', function() {
    if (this.checked) {
      routingFields.classList.remove('opacity-50', 'pointer-events-none');
    } else {
      routingFields.classList.add('opacity-50', 'pointer-events-none');
      // Reset to "No Routing" when disabled
      if (fromSelect) fromSelect.value = '';
      if (toSelect) toSelect.value = '';
    }
  });
}

if (dropZone && fileInput) {
  dropZone.addEventListener('click', () => fileInput.click());

  fileInput.addEventListener('change', () => {
    if (fileInput.files.length > 0) {
      validateAndDisplay(fileInput.files);
    } else {
      filePreview.innerHTML = '';
      fileList.innerHTML = '';
    }
  });

  dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-blue-400', 'bg-blue-50');
  });

  dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-blue-400', 'bg-blue-50');
  });

  dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-blue-400', 'bg-blue-50');
    if (e.dataTransfer.files.length) {
      validateAndDisplay(e.dataTransfer.files);
    }
  });
}

// Validate form submission
document.querySelector('form').addEventListener('submit', function(e) {
  if (!fileInput.files || fileInput.files.length === 0) {
    e.preventDefault();
    alert('Please select at least one file');
  }
});
</script>
