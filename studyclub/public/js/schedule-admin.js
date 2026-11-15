(function () {
  const KEY = 'studyclub_schedules';
  const form = document.getElementById('form-jadwal');
  const tbody = document.getElementById('daftar');
  const err = document.getElementById('error');
  const filter = document.getElementById('filter-kategori');
  const jumlah = document.getElementById('jumlah');

  const load = () => { try { return JSON.parse(localStorage.getItem(KEY)) || []; } catch (e) { return []; } };
  const save = (d) => { try { localStorage.setItem(KEY, JSON.stringify(d)); } catch (e) {} };

  function fmtTanggal(iso) {
    const [y, m, d] = iso.split('-');
    return d + '/' + m + '/' + y;
  }

  function render() {
    const semua = load().sort((a, b) => (a.tanggal + a.mulai).localeCompare(b.tanggal + b.mulai));
    const data = filter.value === 'Semua' ? semua : semua.filter((x) => x.kategori === filter.value);
    jumlah.textContent = data.length + ' kegiatan';
    tbody.innerHTML = '';
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
      const cells = [
        j.nama,
        j.kategori,
        fmtTanggal(j.tanggal),
        j.mulai + ' - ' + j.selesai,
        j.lokasi || '-',
        j.deskripsi || '-'
      ];
      cells.forEach((t, i) => {
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

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const f = new FormData(form);
    const item = {
      id: Date.now(),
      nama: f.get('nama').trim(),
      kategori: f.get('kategori'),
      tanggal: f.get('tanggal'),
      mulai: f.get('mulai'),
      selesai: f.get('selesai'),
      lokasi: f.get('lokasi').trim(),
      deskripsi: f.get('deskripsi').trim()
    };
    if (item.selesai <= item.mulai) {
      err.textContent = 'Waktu selesai harus lebih akhir dari waktu mulai.';
      return;
    }
    err.textContent = '';
    const data = load(); data.push(item); save(data);
    form.reset();
    render();
  });

  filter.addEventListener('change', render);
  form.addEventListener('reset', () => { err.textContent = ''; });
  render();
})();
