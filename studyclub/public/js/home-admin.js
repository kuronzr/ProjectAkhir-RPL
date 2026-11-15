(function () {
  const modal = document.getElementById('editModal');
  const form = document.getElementById('editForm');
  const title = document.getElementById('editTitle');
  const field = document.getElementById('editText');
  const notice = document.querySelector('.notice-text');
  const editBtn = document.querySelector('.edit-btn');
  const createBtn = document.querySelector('.admin-actions .btn-create');
  let lastFocus = null;

  function open(mode) {
    lastFocus = document.activeElement;

    if (mode === 'create') {
      title.textContent = 'Buat pengumuman baru';
      field.value = '';
    } else {
      title.textContent = 'Edit pengumuman';
      field.value = notice.innerText.trim();
    }

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

  if (editBtn) editBtn.addEventListener('click', () => open('edit'));
  if (createBtn) createBtn.addEventListener('click', () => open('create'));

  modal.querySelectorAll('[data-close]').forEach((b) => b.addEventListener('click', close));
  modal.addEventListener('mousedown', (e) => { if (e.target === modal) close(); });

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
    if (!field.value.trim()) {
      e.preventDefault();
      field.focus();
    }
  });
})();