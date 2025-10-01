(function(){
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  function debounce(fn, wait) {
    let t;
    return function(...args){
      clearTimeout(t);
      t = setTimeout(() => fn.apply(this, args), wait);
    };
  }

  async function http(url, options = {}){
    const headers = options.headers || {};
    headers['X-CSRF-Token'] = csrfToken;
    headers['Accept'] = 'application/json';
    if (options.body && !(options.body instanceof FormData)) {
      headers['Content-Type'] = 'application/json';
    }
    const res = await fetch(url, { credentials: 'same-origin', ...options, headers });
    return res;
  }

  // Autosave binder for forms with data-autosave="true"
  function bindAutosave(){
    const form = document.querySelector('form[data-autosave="true"]');
    if (!form) return;
    const saveUrl = form.getAttribute('data-save-url');
    const journalIdInput = form.querySelector('input[name="jurnal_id"]');
    const saveIndicator = document.getElementById('autosave-indicator');
    const debounced = debounce(async () => {
      const formData = new FormData(form);
      const payload = {};
      formData.forEach((v, k) => {
        if (payload[k]) {
          if (!Array.isArray(payload[k])) payload[k] = [payload[k]];
          payload[k].push(v);
        } else {
          payload[k] = v;
        }
      });
      try {
        saveIndicator && (saveIndicator.textContent = 'Menyimpan...');
        const res = await http(saveUrl, { method: 'POST', body: JSON.stringify(payload) });
        const data = await res.json();
        if (data && data.ok) {
          if (journalIdInput && !journalIdInput.value && data.jurnal_id) {
            journalIdInput.value = data.jurnal_id;
          }
          saveIndicator && (saveIndicator.textContent = 'Tersimpan');
          setTimeout(() => { if (saveIndicator && saveIndicator.textContent === 'Tersimpan') saveIndicator.textContent = ''; }, 1200);
        } else {
          saveIndicator && (saveIndicator.textContent = 'Gagal menyimpan');
        }
      } catch (e) {
        saveIndicator && (saveIndicator.textContent = 'Gagal menyimpan');
      }
    }, 800);

    form.addEventListener('input', debounced);
    form.addEventListener('change', debounced);
  }

  function bindSubmitToHead(){
    const btn = document.getElementById('btn-submit-to-head');
    const form = document.querySelector('form[data-autosave="true"]');
    if (!btn || !form) return;
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      const jurnalId = form.querySelector('input[name="jurnal_id"]').value;
      if (!jurnalId) { alert('Silakan tunggu hingga auto-save selesai.'); return; }
      const res = await http('api/jurnal_submit.php', { method: 'POST', body: JSON.stringify({ jurnal_id: jurnalId }) });
      const data = await res.json();
      if (data && data.ok) {
        alert('Jurnal dikirim ke Kepala Sekolah.');
        window.location.href = 'dashboard.php';
      } else {
        alert('Gagal mengirim.');
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function(){
    bindAutosave();
    bindSubmitToHead();
  });
})();

