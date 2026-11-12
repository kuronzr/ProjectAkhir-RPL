// Halaman Materi: tampilkan detail materi sesuai ?course=...&n=... (n mulai dari 1)
(function () {
  const DATA = window.KURSUS_DATA || {};
  const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sept', 'Okt', 'Nov', 'Des'];
  const fmt = (iso) => { const [, m, d] = iso.split('-'); return Number(d) + ' ' + BULAN[Number(m) - 1]; };

  // Video dummy (sama untuk semua materi)
  const VIDEO_DUMMY = 'video/INI CRUSIAL.mp4';

  const params = new URLSearchParams(location.search);
  const courseId = params.get('course');
  const n = Number(params.get('n'));
  const course = DATA[courseId];
  const materi = course ? course.materi[n - 1] : null;

  const $ = (id) => document.getElementById(id);
  document.getElementById('kembali').href = 'kursus.html?course=' + encodeURIComponent(courseId || '') + '&tab=materi';

  function kosong(pesan) {
    $('judul').textContent = 'Materi';
    $('kosong').textContent = pesan;
    $('kosong').hidden = false;
  }

  // Materi ke-3 terkunci
  if (!materi) { kosong('Materi tidak ditemukan.'); return; }
  if (n === 3) { $('judul').textContent = materi[0]; $('tanggal').textContent = fmt(materi[1]); kosong('Materi ini masih terkunci.'); return; }

  const [judul, tgl, deskripsi, modul] = materi;
  document.title = judul + ' - Study Club';
  $('judul').textContent = judul;
  $('tanggal').textContent = fmt(tgl);
  $('deskripsi').textContent = deskripsi;

  const video = $('video');
  video.src = VIDEO_DUMMY;
  $('unduh').href = VIDEO_DUMMY;

  const ul = $('modul');
  modul.forEach((nama) => {
    const li = document.createElement('li');
    const a = document.createElement('a');
    a.href = '#';
    a.addEventListener('click', (e) => e.preventDefault()); // PDF masih dummy
    const t = document.createElement('span');
    t.className = 'm-modul-nama';
    t.textContent = nama;
    const tipe = document.createElement('span');
    tipe.className = 'm-modul-tipe';
    tipe.textContent = 'PDF';
    a.appendChild(t);
    a.appendChild(tipe);
    li.appendChild(a);
    ul.appendChild(li);
  });

  const tabs = document.querySelectorAll('.m-tab');
  tabs.forEach((b) => b.addEventListener('click', () => {
    tabs.forEach((x) => {
      const on = x === b;
      x.classList.toggle('active', on);
      x.setAttribute('aria-pressed', on);
    });
    $('panel-deskripsi').hidden = b.dataset.tab !== 'deskripsi';
    $('panel-modul').hidden = b.dataset.tab !== 'modul';
  }));

  $('isi').hidden = false;
})();
