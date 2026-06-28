<div class="min-h-screen flex items-center justify-center px-4">

  <div class="w-full max-w-4xl">

    <div class="bg-white rounded-3xl shadow-xl border border-gray-200">

      <div class="p-6 md:p-10">

        <h3 class="text-2xl font-semibold text-gray-800 mb-8">
          Create a User
        </h3>

        <?php if (!empty($error)): ?>
          <div class="mb-6 rounded-xl bg-red-50 border border-red-200 text-red-600 px-4 py-3 text-center">
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-6" enctype="multipart/form-data">

<div class="mb-4">
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Profile Picture (optional)
    </label>

    <div id="profileDropZone" 
         class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-xl p-4 cursor-pointer hover:border-blue-500 transition relative">
        
        <img id="profilePreview" src="" alt="Preview" class="hidden w-24 h-24 rounded-full object-cover mb-2 shadow-sm">
        <p class="text-gray-500 mb-1">Drag & drop or click to select an image</p>
        <p class="text-gray-400 text-xs">PNG, JPG, GIF (Max 2MB)</p>
  
        <input type="file" name="profile_picture" id="profileInput" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
    </div>
</div>
          <!-- Name + Position -->
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">
                First Name
              </label>
              <input
                type="text"
                name="firstName"
                required
                placeholder="Enter first name"
                class="w-full rounded-xl border border-gray-300
                       px-4 py-3 text-gray-800
                       focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">
                Last Name
              </label>
              <input
                type="text"
                name="lastName"
                required
                placeholder="Enter last name"
                class="w-full rounded-xl border border-gray-300
                       px-4 py-3 text-gray-800
                       focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">
                Position
              </label>
              <input
                type="text"
                name="position"
                required
                placeholder="Enter position"
                class="w-full rounded-xl border border-gray-300
                       px-4 py-3 text-gray-800
                       focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>

          </div>

          <!-- Username -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">
                Username
              </label>
              <input
                type="text"
                name="username"
                required
                placeholder="Choose a username"
                class="w-full rounded-xl border border-gray-300
                       px-4 py-3 text-gray-800
                       focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">
                Email
              </label>
              <input
                type="email"
                name="email"
                placeholder="Enter email address"
                class="w-full rounded-xl border border-gray-300
                       px-4 py-3 text-gray-800
                       focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
          </div>

          <!-- Password -->
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">
              Password
            </label>
            <input
              type="password"
              name="password"
              required
              placeholder="Create a password"
              class="w-full rounded-xl border border-gray-300
                     px-4 py-3 text-gray-800
                     focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
          </div>

          <!-- Confirm Password -->
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">
              Confirm Password
            </label>
            <input
              type="password"
              name="confirm_password"
              required
              placeholder="Re-enter password"
              class="w-full rounded-xl border border-gray-300
                     px-4 py-3 text-gray-800
                     focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
          </div>

          <!-- User Level -->
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">
              User Level
            </label>
            <select
              name="user_level"
              required
              class="w-full rounded-xl border border-gray-300
                     px-4 py-3 text-gray-800
                     focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
            >
              <option value="1">Admin</option>
              <option value="2">Encoder</option>
              <option value="3">Viewer</option>
            </select>
          </div>

          <!-- Actions -->
          <div class="flex flex-col md:flex-row gap-4 pt-4">

            <a href="index.php?controller=Auth&action=users"
               class="w-full text-center
                      rounded-xl border border-blue-600
                      px-5 py-3 font-medium text-blue-600
                      hover:bg-blue-50 transition">
              Cancel Creation
            </a>

            <button type="submit"
                    class="w-full rounded-xl
                           bg-blue-600 px-5 py-3
                           font-medium text-white
                           hover:bg-blue-700 transition">
              Create User
            </button>

          </div>

        </form>

      </div>
    </div>

  </div>

</div>


<script>
const profileInput = document.getElementById('profileInput');
const profilePreview = document.getElementById('profilePreview');
const profileDropZone = document.getElementById('profileDropZone');

// profileDropZone.addEventListener('click', () => profileInput.click());

profileInput.addEventListener('change', () => {
    const file = profileInput.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = e => {
            profilePreview.src = e.target.result;
            profilePreview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
});

// Drag & drop feedback
profileDropZone.addEventListener('dragover', e => {
    e.preventDefault();
    profileDropZone.classList.add('border-blue-500', 'bg-blue-50');
});
profileDropZone.addEventListener('dragleave', () => {
    profileDropZone.classList.remove('border-blue-500', 'bg-blue-50');
});
profileDropZone.addEventListener('drop', e => {
    e.preventDefault();
    profileDropZone.classList.remove('border-blue-500', 'bg-blue-50');
    if (e.dataTransfer.files.length > 0) {
        profileInput.files = e.dataTransfer.files;
        const file = profileInput.files[0];
        const reader = new FileReader();
        reader.onload = e => {
            profilePreview.src = e.target.result;
            profilePreview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
});
</script>