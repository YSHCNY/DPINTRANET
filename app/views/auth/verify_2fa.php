<?php
$email = $email ?? $_SESSION['twofactor_email'] ?? null;
$reference = $reference ?? $_SESSION['twofactor_reference'] ?? null;
$remainingAttempts = $remainingAttempts ?? $_SESSION['twofactor_remaining_attempts'] ?? 5;
$verifyAction = $verifyAction ?? 'index.php?controller=Auth&action=verify2fa';
$resendAction = $resendAction ?? 'index.php?controller=Auth&action=resend2fa';
$csrfMetaPath = __DIR__ . '/../../Services/CsrfService.php';
if (file_exists($csrfMetaPath)) {
    require_once $csrfMetaPath;
}

function mask_email(?string $e): string {
    if (!$e) return 'john********@company.com';
    $parts = explode('@', $e);
    if (count($parts) !== 2) return $e;
    $name = $parts[0];
    $domain = $parts[1];
    $visible = min(4, max(1, (int) floor(strlen($name) / 2)));
    return substr($name, 0, $visible) . str_repeat('*', max(4, strlen($name)-$visible)) . '@' . $domain;
}
?>
<div class="min-h-screen bg-slate-50 px-4 py-8 sm:px-6 lg:px-8">
  <div class="mx-auto flex min-h-[calc(100vh-4rem)] max-w-md items-center justify-center">
    <div class="w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm p-6">
      <header class="mb-4 text-center">
        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">DPINTRANET</p>
        <h1 class="mt-2 text-lg font-semibold text-slate-900">Verify Your Identity</h1>
        <p class="mt-1 text-sm text-slate-600">We've sent a 6-digit verification code to:</p>
        <p class="mt-1 text-sm font-medium text-slate-800" aria-live="polite"><?= htmlspecialchars(mask_email($email), ENT_QUOTES, 'UTF-8') ?></p>
      </header>

      <form id="verify2faForm" class="space-y-4" novalidate>
        <div>
          <label for="otp-input" class="sr-only">6-digit verification code</label>
          <div id="otp-input" class="flex items-center justify-center gap-2" aria-describedby="otp-desc">
            <?php for ($i = 0; $i < 6; $i++): ?>
              <input inputmode="numeric" pattern="[0-9]*" maxlength="1" aria-label="Digit <?= $i+1 ?>" class="otp-digit h-12 w-10 text-center text-lg font-medium rounded-md border border-slate-200 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-200" />
            <?php endfor; ?>
          </div>
          <p id="otp-desc" class="mt-2 text-center text-sm text-slate-500">Enter the 6-digit code. Numbers only.</p>
        </div>

        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <input id="rememberDevice" type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-200" />
            <label for="rememberDevice" class="text-sm text-slate-700">Trust this browser for 30 days</label>
          </div>
          <div class="text-sm text-slate-500">
            <button id="resendBtn" type="button" class="text-sm font-medium text-slate-600 hover:text-slate-900 disabled:opacity-50" data-cooldown="60">Resend code</button>
          </div>
        </div>

        <div>
          <button id="verifyBtn" type="submit" class="relative h-10 w-full rounded-lg bg-slate-900 text-sm font-medium text-white transition hover:bg-slate-800 disabled:opacity-60" disabled>
            <span id="verifyBtnText">Verify Code</span>
            <svg id="verifySpinner" class="hidden animate-spin absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
          </button>
        </div>

        <div class="text-sm text-slate-500">
          <p>You'll only be asked for another verification code on this browser after 30 days or if your password changes.</p>
          <p class="mt-2">Having trouble? <a href="mailto:it-support@company.com" class="font-medium text-slate-600 hover:text-slate-900">Contact your system administrator</a>.</p>
        </div>
      </form>

      <section id="auth-methods" class="mt-6 border-t border-slate-100 pt-4">
        <h2 class="text-sm font-semibold text-slate-700">Other authentication methods</h2>
        <p class="mt-1 text-sm text-slate-500">Authenticator apps and security keys will be available here in the future.</p>
      </section>
    </div>
  </div>
</div>

<script>
  (function(){
    const form = document.getElementById('verify2faForm');
    const digits = Array.from(document.querySelectorAll('.otp-digit'));
    const verifyBtn = document.getElementById('verifyBtn');
    const verifySpinner = document.getElementById('verifySpinner');
    const verifyBtnText = document.getElementById('verifyBtnText');
    const resendBtn = document.getElementById('resendBtn');
    const rememberDevice = document.getElementById('rememberDevice');
    const csrf = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : null;
    const verifyAction = <?= json_encode($verifyAction) ?>;
    const resendAction = <?= json_encode($resendAction) ?>;
    const reference = <?= json_encode($reference) ?>;
    

    // Focus first
    digits[0].focus();

    // Input behavior
    digits.forEach((el, idx) => {
      el.addEventListener('input', (e) => {
        const v = e.target.value.replace(/[^0-9]/g, '');
        e.target.value = v;
        if (v && idx < digits.length - 1) digits[idx+1].focus();
        updateSubmitState();
      });

      el.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !e.target.value && idx > 0) {
          digits[idx-1].focus();
        }
        if (e.key === 'ArrowLeft' && idx > 0) { digits[idx-1].focus(); }
        if (e.key === 'ArrowRight' && idx < digits.length -1) { digits[idx+1].focus(); }
      });

      // Paste support
      el.addEventListener('paste', (e) => {
        e.preventDefault();
        const paste = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g,'').slice(0,6);
        for (let i=0;i<digits.length;i++){ digits[i].value = paste[i] || ''; }
        const filled = digits.filter(d => d.value).length;
        if (filled < digits.length) digits[filled].focus(); else digits[digits.length-1].focus();
        updateSubmitState();
      });
    });

    function getOtp() { return digits.map(d => d.value).join(''); }
    function updateSubmitState(){ verifyBtn.disabled = getOtp().length !== 6; }

    form.addEventListener('submit', (ev) => {
      ev.preventDefault();
      if (verifyBtn.disabled) return;
      const payload = { otp: getOtp(), reference: reference, remember: rememberDevice.checked ? 1 : 0 };
      verifyBtn.disabled = true; verifySpinner.classList.remove('hidden');
      fetch(verifyAction, {
        method: 'POST',
        credentials: 'same-origin',
        headers: Object.assign({ 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, csrf ? { 'X-CSRF-Token': csrf } : {}),
        body: JSON.stringify(payload)
      }).then(r => {
        return r.text().then(text => {
          let data = null;
          try { data = text ? JSON.parse(text) : null; } catch (e) {
            // show server response text for easier debugging
            showError(text || 'Unexpected server response');
            throw new Error('invalid_json');
          }
          return { ok: r.ok, data };
        });
      }).then(({ ok, data }) => {
        if (ok && data && data.success) {
          if (data.redirectUrl) { window.location.href = data.redirectUrl; return; }
          window.location.reload();
          return;
        }
        showError(data && data.message ? data.message : 'The verification code is incorrect.');
        verifyBtn.disabled = false; verifySpinner.classList.add('hidden');
      }).catch((err) => {
        if (err && err.message === 'invalid_json') return; // already shown
        showError('Network error. Please try again.');
        verifyBtn.disabled = false; verifySpinner.classList.add('hidden');
      });
    });

    function showError(msg) {
      // Simple accessible alert
      let el = document.getElementById('verify2faError');
      if (!el) {
        el = document.createElement('div');
        el.id = 'verify2faError';
        el.className = 'mt-3 rounded-md border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700';
        form.prepend(el);
      }
      el.textContent = msg;
      el.setAttribute('role','alert');
    }

    // Resend cooldown
    (function(){
      const cooldown = parseInt(resendBtn.dataset.cooldown || resendBtn.getAttribute('data-cooldown') || '60',10);
      let remaining = cooldown;
      resendBtn.disabled = true; resendBtn.setAttribute('aria-disabled','true');
      resendBtn.textContent = `Resend in ${remaining}s`;
      const tick = setInterval(()=>{
        remaining -= 1;
        if (remaining <= 0) { clearInterval(tick); resendBtn.disabled = false; resendBtn.removeAttribute('aria-disabled'); resendBtn.textContent = 'Resend code'; return; }
        resendBtn.textContent = `Resend in ${remaining}s`;
      }, 1000);

      resendBtn.addEventListener('click', () => {
        resendBtn.disabled = true; resendBtn.textContent = 'Sending...';
        fetch(resendAction, { method: 'POST', credentials: 'same-origin', headers: Object.assign({'Accept':'application/json', 'X-Requested-With': 'XMLHttpRequest'}, csrf ? { 'X-CSRF-Token': csrf } : {}) }).then(r => {
          return r.text().then(text => {
            let d = null;
            try { d = text ? JSON.parse(text) : null; } catch(e) { showError(text || 'Unexpected server response'); throw new Error('invalid_json'); }
            return { ok: r.ok, d };
          });
        }).then(({ ok, d }) => {
          if (ok && d && d.success) {
            remaining = cooldown; resendBtn.textContent = `Resend in ${remaining}s`;
            const t = setInterval(()=>{ remaining -=1; if (remaining<=0){ clearInterval(t); resendBtn.disabled=false; resendBtn.textContent='Resend code'; } else { resendBtn.textContent=`Resend in ${remaining}s`; } },1000);
          } else {
            resendBtn.disabled = false; resendBtn.textContent = 'Resend code';
            showError(d && d.message ? d.message : 'Unable to send code.');
          }
        }).catch((err)=>{ if (err && err.message === 'invalid_json') return; resendBtn.disabled = false; resendBtn.textContent = 'Resend code'; showError('Network error sending code.'); });
      });
    })();
  })();
</script>
