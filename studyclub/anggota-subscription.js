// Halaman admin: daftar anggota subscription study club
(function () {
  // Data contoh. Ganti dengan data dari backend/API jika sudah ada.
  const data = [
    { id: 1, nama: 'Ahmad Kanabawi',  jenis: 'Bulanan', diblokir: false },
    { id: 2, nama: 'Mas Amba',     jenis: 'Tahunan', diblokir: false },
    { id: 3, nama: 'Reza Auditore',  jenis: 'Bulanan', diblokir: false },
    { id: 4, nama: 'Gibran Mundur', jenis: 'Bulanan', diblokir: false },
    { id: 5, nama: 'Windah Basudara',   jenis: 'Tahunan', diblokir: false }
  ];

  const rowsEl   = document.getElementById('rows');
  const filterEl = document.getElementById('filter');
  const countEl  = document.getElementById('count');
  const toastEl  = document.getElementById('toast');
  let toastTimer;

  function esc(text) {
    const el = document.createElement('div');
    el.textContent = text;
    return el.innerHTML;
  }

  function toast(message) {
    toastEl.textContent = message;
    toastEl.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toastEl.classList.remove('show'), 2200);
  }

  function aksiHtml(d) {
    if (d.diblokir) {
      return '<div class="btn-group">' +
        '<span class="badge no">Diblokir</span>' +
        '<button class="btn-buka" data-id="' + d.id + '" data-aksi="buka">Buka Blokir</button>' +
        '</div>';
    }
    return '<button class="btn-ban" data-id="' + d.id + '" data-aksi="ban">Ban</button>';
  }

  function render() {
    const f = filterEl.value;
    const list = data.filter(d => f === 'semua' || d.jenis === f);

    countEl.textContent = list.length + ' anggota';

    if (!list.length) {
      rowsEl.innerHTML = '<tr><td class="empty" colspan="4">Belum ada anggota subscription.</td></tr>';
      return;
    }

    rowsEl.innerHTML = list.map((d, i) =>
      '<tr class="' + (d.diblokir ? 'diblokir' : '') + '">' +
        '<td class="num">' + (i + 1) + '</td>' +
        '<td>' + esc(d.nama) + '</td>' +
        '<td><span class="badge ' + d.jenis.toLowerCase() + '">' + esc(d.jenis) + '</span></td>' +
        '<td>' + aksiHtml(d) + '</td>' +
      '</tr>'
    ).join('');
  }

  // Tombol Ban / Buka Blokir
  rowsEl.addEventListener('click', function (e) {
    const btn = e.target.closest('button[data-id]');
    if (!btn) return;

    const id = parseInt(btn.dataset.id, 10);
    const item = data.find(d => d.id === id);
    if (!item) return;

    if (btn.dataset.aksi === 'ban') {
      if (!confirm('Blokir anggota "' + item.nama + '"?')) return;
      // TODO: kirim ke backend, mis. fetch('/api/anggota/' + id + '/ban', { method: 'POST' })
      item.diblokir = true;
      toast(item.nama + ' diblokir');
    } else {
      // TODO: kirim ke backend untuk membuka blokir
      item.diblokir = false;
      toast('Blokir ' + item.nama + ' dibuka');
    }
    render();
  });

  filterEl.addEventListener('change', render);

  render();
})();
