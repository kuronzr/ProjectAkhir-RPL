(function () {
  const rowsEl = document.getElementById('rows');
  const filterEl = document.getElementById('filter');
  const countEl = document.getElementById('count');
  const toastEl = document.getElementById('toast');
  if (!rowsEl) return;

  const data = JSON.parse(rowsEl.dataset.pengajuan || '[]');
  const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  let toastTimer;

  function formatTanggal(iso) {
    if (!iso) return '-';
    const [y, m, d] = iso.split('-');
    return parseInt(d, 10) + ' ' + BULAN[parseInt(m, 10) - 1] + ' ' + y;
  }

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
    if (d.status === 'menunggu') {
      return '<div class="btn-group">' +
        '<button class="btn-terima" data-id="' + d.id + '" data-aksi="diterima">Terima</button>' +
        '<button class="btn-tolak" data-id="' + d.id + '" data-aksi="ditolak">Tolak</button>' +
        '</div>';
    }
    const ok = d.status === 'diterima';
    return '<span class="badge ' + (ok ? 'ok' : 'no') + '">' + (ok ? 'Diterima' : 'Ditolak') + '</span>';
  }

  function render() {
    const f = filterEl.value;
    const list = data.filter(d => f === 'semua' || d.status === f);

    countEl.textContent = list.length + ' pengajuan';

    if (!list.length) {
      rowsEl.innerHTML = '<tr><td class="empty" colspan="6">Belum ada pengajuan subscription.</td></tr>';
      return;
    }

    rowsEl.innerHTML = list.map((d, i) =>
      '<tr>' +
      '<td class="num">' + (i + 1) + '</td>' +
      '<td>' + esc(d.nama) + '</td>' +
      '<td>' + esc(d.jenis) + '</td>' +
      '<td>' + esc(d.bayar) + '</td>' +
      '<td>' + formatTanggal(d.tgl) + '</td>' +
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

    btn.disabled = true;
    try {
      const formData = new FormData();
      formData.append('action', 'verifikasi');
      formData.append('id', id);
      formData.append('aksi', aksi);

      const res = await fetch('../control/prosesCheckout.php', {
        method: 'POST',
        body: formData
      });
      const result = await res.json();

      if (result.success) {
        item.status = aksi;
        toast('Pengajuan ' + item.nama + ' ' + aksi);
        render();
      } else {
        toast(result.message || 'Gagal memperbarui status.');
        btn.disabled = false;
      }
    } catch (err) {
      toast('Gagal terhubung ke server.');
      btn.disabled = false;
    }
  });

  filterEl.addEventListener('change', render);
  render();
})();