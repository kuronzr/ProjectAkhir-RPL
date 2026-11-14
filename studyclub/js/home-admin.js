// Modal edit pengumuman: buka lewat ikon pensil, simpan memperbarui teks di halaman
(function () {
  const modal = document.getElementById('editModal');
  const form = document.getElementById('editForm');
  const field = document.getElementById('editText');
  const notice = document.querySelector('.notice-text');
  const openBtn = document.querySelector('.edit-btn');
  let lastFocus = null;

  function open() {
    lastFocus = document.activeElement;
    field.value = notice.textContent.trim();
    modal.hidden = false;
    document.body.classList.add('modal-open');
    field.focus();
    field.setSelectionRange(field.value.length, field.value.length);
  }

  function close() {
    modal.hidden = true;
    document.body.classList.remove('modal-open');
    if (lastFocus) lastFocus.focus();
  }

  openBtn.addEventListener('click', open);

  // tombol Batal dan X
  modal.querySelectorAll('[data-close]').forEach((b) => b.addEventListener('click', close));

  // klik area gelap di luar kotak
  modal.addEventListener('mousedown', (e) => { if (e.target === modal) close(); });

  // tombol Esc, dan Tab tetap berputar di dalam modal
  document.addEventListener('keydown', (e) => {
    if (modal.hidden) return;
    if (e.key === 'Escape') { close(); return; }
    if (e.key === 'Tab') {
      const items = modal.querySelectorAll('button, textarea');
      const first = items[0], last = items[items.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    }
  });

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const text = field.value.trim();
    if (!text) { field.focus(); return; }
    notice.textContent = text;
    // TODO: kirim `text` ke server/database di sini agar tersimpan permanen
    close();
  });
})();
