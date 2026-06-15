<?php require __DIR__ . '/../partials/icons.php'; ?>
<?php $filesCateg = $filesCateg ?? []; $recipientsCateg = $recipientsCateg ?? []; ?>

<div class="min-h-screen bg-gray-50">
  <div class="max-w-5xl mx-auto px-4 py-8">
    <div class="mb-8 overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-[0_18px_60px_rgba(15,23,42,0.08)]">
      <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-slate-50 px-6 py-5">
        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-blue-600">Files Repository</p>
        <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">Edit File Information</h2>
        <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-500">
          Adjust file routing, category, or content while keeping the layout clean and easy to review.
        </p>
      </div>

      <div class="p-6 lg:p-7">
        <form action="index.php?controller=Files&action=update" method="post" enctype="multipart/form-data" class="space-y-6">
          <input type="hidden" name="id" value="<?= htmlspecialchars($file['id']) ?>">

          <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_360px] gap-6">
            <section class="space-y-5">
              <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Routing</p>
                <h3 class="mt-1 text-lg font-semibold text-slate-900">From and to categories</h3>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-[1fr_auto_1fr] gap-4 items-end">
                  <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">From</label>
                    <select name="fromCategory" required class="w-full h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                      <?php foreach ($recipientsCateg as $category): ?>
                        <option value="<?= htmlspecialchars($category['category']) ?>" <?= $category['category'] === $file['directionFrom'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($category['category']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="hidden md:flex items-center justify-center pb-3 text-slate-400">
                    <?= $viseVersaIcon ?? '' ?>
                  </div>
                  <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">To</label>
                    <select name="toCategory" required class="w-full h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                      <?php foreach ($recipientsCateg as $category): ?>
                        <option value="<?= htmlspecialchars($category['category']) ?>" <?= $category['category'] === $file['directionTo'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($category['category']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>

              <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Classification</p>
                <h3 class="mt-1 text-lg font-semibold text-slate-900">File category and description</h3>

                <div class="mt-4 space-y-4">
                  <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Category</label>
                    <select name="fileCategory" required class="w-full h-11 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 outline-none transition focus:border-sky-500 focus:bg-white focus:ring-2 focus:ring-sky-100">
                      <?php foreach ($filesCateg as $category): ?>
                        <option value="<?= htmlspecialchars($category['category']) ?>" <?= $category['category'] === $file['category'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($category['category']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Description</label>
                    <input type="text" name="description" required value="<?= htmlspecialchars($file['desc']) ?>" placeholder="Enter file description"
                           class="w-full h-11 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 outline-none transition focus:border-sky-500 focus:bg-white focus:ring-2 focus:ring-sky-100">
                  </div>
                </div>
              </div>

              <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Upload</p>
                <h3 class="mt-1 text-lg font-semibold text-slate-900">Replace file</h3>

                <div id="dropZone" class="mt-4 rounded-3xl border-2 border-dashed border-slate-300 bg-slate-50 px-5 py-10 text-center transition hover:border-sky-300 hover:bg-sky-50/50">
                  <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-white shadow-sm">
                    <span class="text-2xl text-sky-600"><?= $cloudIcon ?? '☁️' ?></span>
                  </div>
                  <p class="mt-4 text-base font-semibold text-slate-900">Drag & drop a replacement file</p>
                  <p class="mt-1 text-sm text-slate-500">or click to browse your computer</p>
                  <div id="fileName" class="mt-3 text-sm font-medium text-sky-700"></div>
                  <input type="file" name="file" id="fileInput" class="hidden">
                </div>

                <?php if (!empty($file['filename'])): ?>
                  <p class="mt-3 text-sm text-slate-500">
                    Current file:
                    <a href="index.php?controller=Files&action=download&file=<?= urlencode($file['filename']) ?>" class="font-medium text-sky-700 hover:underline">
                      <?= htmlspecialchars($file['filename']) ?>
                    </a>
                  </p>
                <?php endif; ?>
              </div>
            </section>

            <aside class="space-y-4">
              <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Summary</p>
                <h3 class="mt-1 text-lg font-semibold text-slate-900">Current record</h3>
                <div class="mt-4 space-y-3 text-sm text-slate-600">
                  <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">File name</p>
                    <p class="mt-1 font-semibold text-slate-900"><?= htmlspecialchars($file['filename'] ?? '—') ?></p>
                  </div>
                  <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Category</p>
                    <p class="mt-1 font-semibold text-slate-900"><?= htmlspecialchars($file['category'] ?? '—') ?></p>
                  </div>
                  <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Direction</p>
                    <p class="mt-1 font-semibold text-slate-900"><?= htmlspecialchars(trim(($file['directionFrom'] ?? '') . ' ⇄ ' . ($file['directionTo'] ?? 'No Direction'))) ?></p>
                  </div>
                </div>
              </div>

              <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Actions</label>
                <div class="space-y-3">
                  <a href="index.php?controller=Files&action=files"
                     class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Cancel
                  </a>
                  <button type="submit"
                          class="inline-flex w-full items-center justify-center rounded-2xl bg-sky-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-sky-700">
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
