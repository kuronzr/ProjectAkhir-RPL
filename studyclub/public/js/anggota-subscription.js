(function () {
  const rowsEl = document.getElementById('rows');
  const filterEl = document.getElementById('filter');
  const countEl = document.getElementById('count');
  const toastEl = document.getElementById('toast');
  if (!rowsEl) return;

  const data = JSON.parse(rowsEl.dataset.anggota || '[]');
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
    const list = data.filter(d => {
      if (f === 'semua') return true;
      if (f === 'Premium') return d.isPremium;
      return d.jenis === f;
    });

    countEl.textContent = list.length + ' anggota';

    if (!list.length) {
      rowsEl.innerHTML = '<tr><td class="empty" colspan="4">Belum ada anggota terdaftar.</td></tr>';
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

  rowsEl.addEventListener('click', async function (e) {
    const btn = e.target.closest('button[data-id]');
    if (!btn) return;

    const id = parseInt(btn.dataset.id, 10);
    const aksi = btn.dataset.aksi;
    const item = data.find(d => d.id === id);
    if (!item) return;

    if (aksi === 'ban' && !confirm('Blokir anggota "' + item.nama + '"?')) return;

    btn.disabled = true;
    try {
      const formData = new FormData();
      formData.append('action', 'toggle_ban');
      formData.append('id', id);
      formData.append('aksi', aksi);

      const res = await fetch('../control/prosesCheckout.php', {
        method: 'POST',
        body: formData
      });
      const result = await res.json();

      if (result.success) {
        item.diblokir = (aksi === 'ban');
        toast(aksi === 'ban' ? (item.nama + ' diblokir') : ('Blokir ' + item.nama + ' dibuka'));
        render();
      } else {
        toast(result.message || 'Gagal memperbarui status blokir.');
        btn.disabled = false;
      }
    } catch (err) {
      toast('Gagal terhubung ke server.');
      btn.disabled = false;
    }
  });

  filterEl.addEventListener('change', render);
  render();

  function badgeStyle(jenis) {
    const j = jenis.toLowerCase();
    if (j === 'tahunan') {
      return 'background:#fef3c7; color:#92400e; padding:4px 12px; border-radius:999px; font-weight:600; font-size:0.85rem; display:inline-block;';
    }
    if (j === 'bulanan' || j === 'premium') {
      return 'background:#dbeafe; color:#1e40af; padding:4px 12px; border-radius:999px; font-weight:600; font-size:0.85rem; display:inline-block;';
    }
    return 'background:#e2e8f0; color:#334155; padding:4px 12px; border-radius:999px; font-weight:600; font-size:0.85rem; display:inline-block;';
  }

  function render() {
    const f = filterEl.value;
    const list = data.filter(d => {
      if (f === 'semua') return true;
      if (f === 'Premium') return d.isPremium;
      return d.jenis === f;
    });

    countEl.textContent = list.length + ' anggota';

    if (!list.length) {
      rowsEl.innerHTML = '<tr><td class="empty" colspan="4">Belum ada anggota terdaftar.</td></tr>';
      return;
    }

    rowsEl.innerHTML = list.map((d, i) =>
      '<tr class="' + (d.diblokir ? 'diblokir' : '') + '">' +
        '<td class="num">' + (i + 1) + '</td>' +
        '<td>' + esc(d.nama) + '</td>' +
        '<td><span style="' + badgeStyle(d.jenis) + '">' + esc(d.jenis) + '</span></td>' +
        '<td>' + aksiHtml(d) + '</td>' +
      '</tr>'
    ).join('');
  }
})();