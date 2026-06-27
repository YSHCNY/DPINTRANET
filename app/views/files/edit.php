<?php require __DIR__ . '/../partials/icons.php'; ?>
<?php $filesCateg = $filesCateg ?? []; $recipientsCateg = $recipientsCateg ?? []; ?>

<div class="min-h-screen theme-palette">
  <div class="max-w-4xl mx-auto px-4 py-6 lg:px-5 lg:py-7">
    <div class="mb-6 overflow-hidden rounded-[26px] border border-slate-200 bg-white shadow-[0_14px_40px_rgba(15,23,42,0.06)]">
      <div class="border-b border-slate-200 bg-slate-50 px-5 py-4">
        <p class="text-[10px] font-semibold uppercase tracking-[0.26em] text-emerald-600">Files Repository</p>
        <h2 class="mt-1 text-xl font-semibold tracking-tight text-slate-900">Edit File Information</h2>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
          Adjust file routing, category, or content while keeping the layout clean and easy to review.
        </p>
      </div>

      <div class="p-4 lg:p-5">
        <form action="index.php?controller=Files&action=update" method="post" enctype="multipart/form-data" class="space-y-5">
          <input type="hidden" name="id" value="<?= htmlspecialchars($file['id']) ?>">
          <input type="hidden" name="routingEnabled" id="routingEnabled" value="0">

          <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_320px] gap-5">
            <section class="space-y-4">
              <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-4 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                  <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">Routing</p>
                    <h3 class="mt-1 text-base font-semibold text-slate-900">From and to categories</h3>
                  </div>
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="routingToggle" class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-2 focus:ring-emerald-500" <?= (!empty($file['directionFrom']) || !empty($file['directionTo'])) ? 'checked' : '' ?>>
                    <span class="text-sm font-medium text-slate-600">Enable Routing</span>
                  </label>
                </div>

                <div id="routingFields" class="mt-3 grid grid-cols-1 md:grid-cols-[1fr_auto_1fr] gap-3 items-end <?= (empty($file['directionFrom']) && empty($file['directionTo'])) ? 'opacity-50' : '' ?> transition-opacity duration-200">
                  <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">From</label>
                    <select name="fromCategory" id="fromCategory" class="w-full h-10 rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" <?= (empty($file['directionFrom']) && empty($file['directionTo'])) ? 'disabled' : '' ?>>
                      <option value="">No Routing</option>
                      <?php foreach ($recipientsCateg as $category): ?>
                        <option value="<?= htmlspecialchars($category['category']) ?>" <?= $category['category'] === $file['directionFrom'] ? 'selected' : '' ?>>
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
                    <select name="toCategory" id="toCategory" class="w-full h-10 rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" <?= (empty($file['directionFrom']) && empty($file['directionTo'])) ? 'disabled' : '' ?>>
                      <option value="">No Routing</option>
                      <?php foreach ($recipientsCateg as $category): ?>
                        <option value="<?= htmlspecialchars($category['category']) ?>" <?= $category['category'] === $file['directionTo'] ? 'selected' : '' ?>>
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
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">Category</label>
                    <select name="fileCategory" required class="w-full h-10 rounded-2xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-100">
                      <?php foreach ($filesCateg as $category): ?>
                        <option value="<?= htmlspecialchars($category['category']) ?>" <?= $category['category'] === $file['category'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($category['category']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">Description</label>
                    <textarea name="description" required rows="5" placeholder="Enter a concise file description"
                              class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-3 py-3 text-sm leading-6 text-slate-700 outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-100"><?= htmlspecialchars($file['desc']) ?></textarea>
                  </div>
                </div>
              </div>

              <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">Upload</p>
                <h3 class="mt-1 text-base font-semibold text-slate-900">Replace file</h3>

                <div id="dropZone" class="mt-3 rounded-3xl border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center transition hover:border-emerald-300 hover:bg-emerald-50/50">
                  <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white shadow-sm">
                    <span class="text-2xl text-emerald-600"><?= $cloudIcon ?? '☁️' ?></span>
                  </div>
                  <p class="mt-3 text-sm font-semibold text-slate-900">Drag & drop a replacement file</p>
                  <p class="mt-1 text-xs text-slate-500">or click to browse your computer</p>
                  <div id="fileName" class="mt-3 text-sm font-medium text-emerald-700"></div>
                  <input type="file" name="file" id="fileInput" class="hidden">
                </div>

                <?php if (!empty($file['filename'])): ?>
                  <p class="mt-3 text-sm text-slate-500">
                    Current file:
                    <a href="index.php?controller=Files&action=download&file=<?= urlencode($file['filename']) ?>" class="font-medium text-emerald-700 hover:underline">
                      <?= htmlspecialchars($file['filename']) ?>
                    </a>
                  </p>
                <?php endif; ?>
              </div>
            </section>

            <aside class="space-y-4">
              <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
                <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">Summary</p>
                <h3 class="mt-1 text-base font-semibold text-slate-900">Current record</h3>
                <div class="mt-3 space-y-3 text-sm text-slate-600">
                  <div class="rounded-2xl border border-slate-200 bg-white px-3 py-3">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">File name</p>
                    <p class="mt-1 font-semibold text-slate-900"><?= htmlspecialchars($file['filename'] ?? '—') ?></p>
                  </div>
                  <div class="rounded-2xl border border-slate-200 bg-white px-3 py-3">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">Category</p>
                    <p class="mt-1 font-semibold text-slate-900"><?= htmlspecialchars($file['category'] ?? '—') ?></p>
                  </div>
                  <div class="rounded-2xl border border-slate-200 bg-white px-3 py-3">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">Direction</p>
                    <p class="mt-1 font-semibold text-slate-900">
                      <?php 
                        $directionFrom = trim($file['directionFrom'] ?? '');
                        $directionTo = trim($file['directionTo'] ?? '');
                        if (empty($directionFrom) && empty($directionTo)) {
                          echo 'No route needed';
                        } else {
                          echo htmlspecialchars($directionFrom . ' ⇄ ' . $directionTo);
                        }
                      ?>
                    </p>
                  </div>
                </div>
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
                    Save Changes
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
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');
const fileName = document.getElementById('fileName');
const routingToggle = document.getElementById('routingToggle');
const routingFields = document.getElementById('routingFields');
const fromSelect = document.getElementById('fromCategory');
const toSelect = document.getElementById('toCategory');

// Routing toggle functionality
if (routingToggle && routingFields) {
  routingToggle.addEventListener('change', function() {
    const routingEnabledInput = document.getElementById('routingEnabled');
    if (this.checked) {
      routingFields.classList.remove('opacity-50');
      fromSelect.disabled = false;
      toSelect.disabled = false;
      routingEnabledInput.value = '1';
    } else {
      routingFields.classList.add('opacity-50');
      fromSelect.disabled = true;
      toSelect.disabled = true;
      // Reset to "No Routing" when disabled
      fromSelect.value = '';
      toSelect.value = '';
      routingEnabledInput.value = '0';
    }
  });
  
  // Set initial value on page load
  const routingEnabledInput = document.getElementById('routingEnabled');
  routingEnabledInput.value = routingToggle.checked ? '1' : '0';
}

if (dropZone && fileInput) {
  dropZone.addEventListener('click', () => fileInput.click());

  fileInput.addEventListener('change', () => {
    if (fileInput.files.length > 0) {
      fileName.textContent = `Selected: ${fileInput.files[0].name}`;
    } else {
      fileName.textContent = '';
    }
  });

  dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-sky-400', 'bg-sky-50');
  });

  dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-sky-400', 'bg-sky-50');
  });

  dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-sky-400', 'bg-sky-50');
    if (e.dataTransfer.files.length) {
      fileInput.files = e.dataTransfer.files;
      fileName.textContent = `Selected: ${e.dataTransfer.files[0].name}`;
    }
  });
}
</script>
