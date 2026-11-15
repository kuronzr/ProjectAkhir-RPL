// Tampilan anggota: hanya MEMBACA data jadwal (tidak ada tambah/ubah/hapus)
(function () {
  const KEY = 'studyclub_schedules';
  const tbody = document.getElementById('daftar');
  const filter = document.getElementById('filter');
  const count = document.getElementById('jumlah');

  function load() {
    try { return JSON.parse(localStorage.getItem(KEY)) || []; } catch (e) { return []; }
  }
  const fmt = (iso) => { const [y, m, d] = iso.split('-'); return d + '/' + m + '/' + y; };

  function render() {
    const kat = filter.value;
    const data = load()
      .filter((j) => !kat || j.kategori === kat)
      .sort((a, b) => (a.tanggal + a.mulai).localeCompare(b.tanggal + b.mulai));

    tbody.innerHTML = '';
    count.textContent = data.length + ' kegiatan';

    if (!data.length) {
      const tr = document.createElement('tr');
      const td = document.createElement('td');
      td.colSpan = 6; td.className = 'empty';
      td.textContent = 'Belum ada jadwal kegiatan.';
      tr.appendChild(td); tbody.appendChild(tr);
      return;
    }

    data.forEach((j) => {
      const tr = document.createElement('tr');
      [j.nama, j.kategori, fmt(j.tanggal), j.mulai + ' - ' + j.selesai, j.lokasi || '-', j.deskripsi || '-']
        .forEach((t, i) => {
          const td = document.createElement('td');
          if (i === 1) {
            const s = document.createElement('span');
            s.className = 'tag'; s.textContent = t; td.appendChild(s);
          } else td.textContent = t;
          tr.appendChild(td);
        });
      tbody.appendChild(tr);
    });
  }

  filter.addEventListener('change', render);
  render();
})();
