// Halaman Praktikum: detail tugas sesuai ?course=...&n=... (n mulai dari 1)
(function () {
  const DATA = window.KURSUS_DATA || {};
  const DETAIL = window.PRAKTIKUM_DETAIL || {};
  const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sept', 'Okt', 'Nov', 'Des'];
  const fmt = (iso) => { const [, m, d] = iso.split('-'); return Number(d) + ' ' + BULAN[Number(m) - 1]; };

  // Referensi bawaan (dummy). Ganti url sesuai file/link yang sebenarnya.
  const REF_BAWAAN = [
    ['Refrensi', 'PDF', '#'],
    ['GitHub', 'Link', 'https://github.com/']
  ];

  const MAKS = 10 * 1024 * 1024; // 10 MB
  const EKSTENSI = /\.(pdf|docx)$/i;

  const params = new URLSearchParams(location.search);
  const courseId = params.get('course');
  const n = Number(params.get('n'));
  const course = DATA[courseId];
  const item = course ? course.praktikum[n - 1] : null;

  const $ = (id) => document.getElementById(id);
  $('kembali').href = 'kursus.html?course=' + encodeURIComponent(courseId || '');

  if (!item) {
    $('kosong').textContent = 'Praktikum tidak ditemukan.';
    $('kosong').hidden = false;
    return;
  }

  const [judul, tgl] = item;
  const d = (DETAIL[courseId] || [])[n - 1];
  const instruksi = typeof d === 'string' ? d : (d && d.instruksi) || 'Kerjakan tugas praktikum sesuai arahan dosen.';
  const refs = (d && d.ref) || REF_BAWAAN;

  document.title = judul + ' - Study Club';
  $('judul').textContent = judul;
  $('tanggal').textContent = 'Due ' + fmt(tgl);
  $('instruksi').textContent = instruksi;

  // Referensi
  const ul = $('referensi');
  refs.forEach(([nama, tipe, url]) => {
    const li = document.createElement('li');
    const a = document.createElement('a');
    a.href = url;
    if (url === '#') {
      a.addEventListener('click', (e) => e.preventDefault()); // file masih dummy
    } else {
      a.target = '_blank';
      a.rel = 'noopener';
    }
    const t = document.createElement('span');
    t.className = 'm-modul-nama';
    t.textContent = nama;
    const k = document.createElement('span');
    k.className = 'm-modul-tipe';
    k.textContent = tipe;
    a.appendChild(t);
    a.appendChild(k);
    li.appendChild(a);
    ul.appendChild(li);
  });

  // Unggah tugas
  const input = $('berkas');
  const drop = $('drop');
  const status = $('status');
  const kirim = $('kirim');
  let berkas = null;

  function pesan(teks, jenis) {
    status.textContent = teks;
    status.className = 'p-status' + (jenis ? ' ' + jenis : '');
  }

  function pilih(file) {
    if (!file) return;
    if (!EKSTENSI.test(file.name)) { pesan('Format file harus PDF atau DOCX.', 'err'); return; }
    if (file.size > MAKS) { pesan('Ukuran file maksimal 10MB.', 'err'); return; }
    berkas = file;
    $('drop-title').textContent = file.name;
    $('info').textContent = 'Klik untuk mengganti file';
    kirim.disabled = false;
    kirim.textContent = 'Unggah Tugas';
    pesan('', '');
  }

  drop.addEventListener('click', () => input.click());
  input.addEventListener('change', () => pilih(input.files[0]));

  ['dragenter', 'dragover'].forEach((ev) => drop.addEventListener(ev, (e) => {
    e.preventDefault();
    drop.classList.add('over');
  }));
  ['dragleave', 'drop'].forEach((ev) => drop.addEventListener(ev, (e) => {
    e.preventDefault();
    drop.classList.remove('over');
  }));
  drop.addEventListener('drop', (e) => pilih(e.dataTransfer.files[0]));

  $('form').addEventListener('submit', (e) => {
    e.preventDefault();
    if (!berkas) { pesan('Pilih file tugas terlebih dahulu.', 'err'); return; }
    // Belum ada server: di sini nanti kirim file (fetch/FormData) ke backend.
    kirim.disabled = true;
    kirim.textContent = 'Terunggah';
    pesan('Tugas "' + berkas.name + '" berhasil diunggah.', 'ok');
  });

  $('isi').hidden = false;
})();
