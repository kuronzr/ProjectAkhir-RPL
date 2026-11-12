// Form admin: tambah / edit materi atau praktikum
// ?course=ID&type=materi|praktikum  (tambah)   +  &n=URUTAN (edit, mulai dari 1)
(function () {
  const S = window.AdminStore;
  const DATA = S.data, DETAIL = S.detail;
  const q = new URLSearchParams(location.search);
  const id = q.get('course');
  const type = q.get('type') === 'praktikum' ? 'praktikum' : 'materi';
  const n = Number(q.get('n')) || 0;
  const edit = n > 0;
  const course = DATA[id];
  const $ = (x) => document.getElementById(x);
  const back = 'kursus-admin.html?course=' + encodeURIComponent(id || '') + '&tab=' + type;
  $('kembali').href = back;

  const MAKS_MODUL = 10 * 1024 * 1024, MAKS_VIDEO = 4 * 1024 * 1024;
  const IKON_X = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>';

  const lama = course && edit ? course[type][n - 1] : null;
  if (!course || (edit && !lama)) {
    $('kosong').textContent = !course ? 'Course tidak ditemukan.' : 'Data tidak ditemukan.';
    $('kosong').hidden = false;
    return;
  }

  const label = type === 'materi' ? 'Materi' : 'Praktikum';
  const judulHal = (edit ? 'Edit ' : 'Tambah ') + label;
  document.title = judulHal + ' - Study Club';
  $('judul-form').textContent = judulHal + ' · ' + course.nama;
  $('lbl-nama').textContent = 'Nama ' + label;
  document.querySelector('.only-materi').hidden = type !== 'materi';
  document.querySelector('.only-praktikum').hidden = type !== 'praktikum';
  if (type === 'praktikum') $('lbl-desk').textContent = 'Instruksi';
  $('hapus').hidden = !edit;
  $('form').hidden = false;

  // ----- nilai awal -----
  let moduls = [];
  let video = '';
  $('nama').value = lama ? lama[0] : '';
  if (type === 'materi') {
    $('desk').value = lama ? lama[2] || '' : '';
    moduls = lama ? (lama[3] || []).slice() : [];
    video = lama && lama[4] ? lama[4] : '';
  } else {
    $('tgl').value = lama ? lama[1] : '';
    const d = (DETAIL[id] || [])[n - 1];
    $('desk').value = lama ? (typeof d === 'string' ? d : (d && d.instruksi) || '') : '';
  }

  function pesan(t, jenis) { $('status').textContent = t; $('status').className = 'p-status' + (jenis ? ' ' + jenis : ''); }

  // ----- modul (chip + tombol +) -----
  function renderModul() {
    const box = $('modul');
    box.innerHTML = '';
    moduls.forEach((nama, i) => {
      const c = document.createElement('div');
      c.className = 'a-chip';
      const t = document.createElement('div');
      const u = document.createElement('u'); u.textContent = nama;
      t.append(u, 'PDF');
      const x = document.createElement('button');
      x.type = 'button'; x.className = 'a-x'; x.innerHTML = IKON_X;
      x.setAttribute('aria-label', 'Hapus modul ' + nama);
      x.addEventListener('click', () => { moduls.splice(i, 1); renderModul(); });
      c.append(t, x);
      box.appendChild(c);
    });
    $('modul-drop').hidden = moduls.length > 0;
    $('modul-add').hidden = moduls.length === 0;
  }

  function tambahModul(files) {
    for (const f of files) {
      if (!/\.pdf$/i.test(f.name)) { pesan('Modul harus berformat PDF.', 'err'); continue; }
      if (f.size > MAKS_MODUL) { pesan('Ukuran modul maksimal 10MB.', 'err'); continue; }
      moduls.push(f.name); pesan('', '');
    }
    renderModul();
  }

  function pilihVideo(f) {
    if (!f) return;
    if (!f.type.startsWith('video/')) { pesan('File harus berupa video.', 'err'); return; }
    if (f.size > MAKS_VIDEO) { pesan('Ukuran video maksimal 4MB.', 'err'); return; }
    video = f.name; pesan('', ''); tampilVideo();
  }
  function tampilVideo() {
    $('video-title').textContent = video || 'Pilih file di sini';
    $('video-info').textContent = video ? 'Klik untuk mengganti video' : 'Video maks 4MB';
  }

  function dropZone(btn, input, onFiles) {
    btn.addEventListener('click', () => input.click());
    input.addEventListener('change', () => { onFiles(input.files); input.value = ''; });
    ['dragenter', 'dragover'].forEach((ev) => btn.addEventListener(ev, (e) => { e.preventDefault(); btn.classList.add('over'); }));
    ['dragleave', 'drop'].forEach((ev) => btn.addEventListener(ev, (e) => { e.preventDefault(); btn.classList.remove('over'); }));
    btn.addEventListener('drop', (e) => onFiles(e.dataTransfer.files));
  }

  if (type === 'materi') {
    dropZone($('modul-drop'), $('modul-input'), tambahModul);
    $('modul-add').addEventListener('click', () => $('modul-input').click());
    dropZone($('video-drop'), $('video-input'), (fs) => pilihVideo(fs[0]));
    renderModul();
    tampilVideo();
  }

  // ----- simpan -----
  $('form').addEventListener('submit', (e) => {
    e.preventDefault();
    const nama = $('nama').value.trim();
    const desk = $('desk').value.trim();
    if (!nama) { pesan('Nama ' + label.toLowerCase() + ' wajib diisi.', 'err'); $('nama').focus(); return; }

    const arr = course[type];
    if (type === 'materi') {
      const hariIni = new Date().toISOString().slice(0, 10);
      const item = [nama, lama ? lama[1] : hariIni, desk, moduls.slice(), video];
      edit ? (arr[n - 1] = item) : arr.push(item);
    } else {
      const tgl = $('tgl').value;
      if (!tgl) { pesan('Tenggat wajib diisi.', 'err'); $('tgl').focus(); return; }
      const det = DETAIL[id] = DETAIL[id] || [];
      while (det.length < arr.length) det.push(null);
      if (edit) {
        arr[n - 1] = [nama, tgl];
        const o = det[n - 1];
        det[n - 1] = o && typeof o === 'object' ? Object.assign({}, o, { instruksi: desk }) : (desk || null);
      } else {
        arr.push([nama, tgl]);
        det.push(desk || null);
      }
    }
    S.save();
    location.href = back;
  });

  // ----- hapus -----
  $('hapus').addEventListener('click', () => {
    if (!confirm('Hapus "' + lama[0] + '"? Tindakan ini tidak bisa dibatalkan.')) return;
    course[type].splice(n - 1, 1);
    if (type === 'praktikum' && DETAIL[id]) DETAIL[id].splice(n - 1, 1);
    S.save();
    location.href = back;
  });
})();
