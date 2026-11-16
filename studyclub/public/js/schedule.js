(function () {
  const filterSelect = document.getElementById('filter');
  const tbody = document.getElementById('daftar');
  const jumlahEl = document.getElementById('jumlah');
  const formJadwal = document.getElementById('form-jadwal');
  const errorEl = document.getElementById('error');

  function updateFilterAndCount() {
    if (!tbody) return;
    const selected = filterSelect ? filterSelect.value : '';
    const rows = tbody.querySelectorAll('tr[data-kategori]');
    let visibleCount = 0;

    rows.forEach((row) => {
      const match = !selected || row.dataset.kategori === selected;
      row.hidden = !match;
      if (match) visibleCount++;
    });

    if (jumlahEl) {
      jumlahEl.textContent = visibleCount + ' kegiatan';
    }
  }

  if (filterSelect) {
    filterSelect.addEventListener('change', updateFilterAndCount);
  }
  updateFilterAndCount();

  if (formJadwal) {
    formJadwal.addEventListener('submit', (e) => {
      const mulai = document.getElementById('mulai').value;
      const selesai = document.getElementById('selesai').value;
      if (mulai && selesai && selesai <= mulai) {
        e.preventDefault();
        if (errorEl) errorEl.textContent = 'Waktu selesai harus lebih akhir dari waktu mulai.';
      } else if (errorEl) {
        errorEl.textContent = '';
      }
    });
  }

  document.querySelectorAll('.btn-hapus-jadwal').forEach((link) => {
    link.addEventListener('click', (e) => {
      if (!confirm('Yakin ingin menghapus jadwal kegiatan ini?')) {
        e.preventDefault();
      }
    });
  });
})();