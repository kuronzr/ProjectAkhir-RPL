(function () {
  const root = document.getElementById('profil-root');
  if (!root) return;

  const RAW_COURSES = JSON.parse(root.dataset.allCourses || '[]');
  const MAPEL = RAW_COURSES.map((c) => ({
    id: Number(c.id),
    nama: c.nama_kursus,
    kategori: c.kategori,
    pola: (c.tema_banner || 'p-navy').startsWith('p-') ? c.tema_banner : 'p-' + c.tema_banner
  }));

  const UKURAN_FOTO = 320;
  const MAKS_FILE = 10 * 1024 * 1024;
  const $ = (id) => document.getElementById(id);

  function el(tag, cls, text) {
    const e = document.createElement(tag);
    if (cls) e.className = cls;
    if (text !== undefined) e.textContent = text;
    return e;
  }

  let nama = $('nama').textContent.trim();
  let bio = $('bio').textContent.trim();
  let foto = root.dataset.foto || '';
  let ambil = JSON.parse(root.dataset.takenIds || '[]').map(Number);
  let fotoDraft = foto;

  function setFoto(src) {
    $('foto').style.backgroundImage = src ? 'url("' + src + '")' : '';
  }

  function renderMapel() {
    const box = $('mapel');
    box.innerHTML = '';
    const daftar = MAPEL.filter((m) => ambil.includes(m.id));
    if (!daftar.length) {
      box.appendChild(el('p', 'p-empty', 'Belum ada kursus yang diambil.'));
      return;
    }
    daftar.forEach((m) => {
      const a = el('a', 'p-card');
      a.href = 'course.php?id=' + encodeURIComponent(m.id);
      a.appendChild(el('div', 'p-cover ' + m.pola));
      const info = el('div', 'p-info');
      info.appendChild(el('h3', '', m.nama));
      info.appendChild(el('p', '', m.kategori));
      a.appendChild(info);
      box.appendChild(a);
    });
  }

  function renderProfil() {
    $('nama').textContent = nama;
    $('bio').textContent = bio;
    setFoto(foto);
    renderMapel();
  }

  MAPEL.forEach((m) => {
    const lb = el('label');
    const cb = el('input');
    cb.type = 'checkbox';
    cb.value = m.id;
    lb.appendChild(cb);
    lb.appendChild(document.createTextNode(m.nama));
    $('pilih-mapel').appendChild(lb);
  });
  const cbs = () => $('pilih-mapel').querySelectorAll('input[type="checkbox"]');

  renderProfil();

  const tampil = $('tampil'), form = $('form-edit');
  function buka(on) { tampil.hidden = on; form.hidden = !on; }

  $('btn-edit').addEventListener('click', () => {
    $('in-nama').value = nama;
    $('in-bio').value = bio;
    $('in-foto').value = '';
    $('err').textContent = '';
    fotoDraft = foto;
    cbs().forEach((cb) => { cb.checked = ambil.includes(Number(cb.value)); });
    buka(true);
    $('in-nama').focus();
  });

  $('btn-batal').addEventListener('click', () => {
    setFoto(foto);
    buka(false);
    $('btn-edit').focus();
  });

  $('in-foto').addEventListener('change', (e) => {
    const file = e.target.files && e.target.files[0];
    if (!file) return;
    $('err').textContent = '';
    if (file.type.indexOf('image/') !== 0) { $('err').textContent = 'File harus berupa gambar.'; e.target.value = ''; return; }
    if (file.size > MAKS_FILE) { $('err').textContent = 'Ukuran foto maksimal 10 MB.'; e.target.value = ''; return; }

    const url = URL.createObjectURL(file);
    const img = new Image();
    img.onload = () => {
      const s = Math.min(img.width, img.height);
      const cv = document.createElement('canvas');
      cv.width = cv.height = UKURAN_FOTO;
      cv.getContext('2d').drawImage(img, (img.width - s) / 2, (img.height - s) / 2, s, s, 0, 0, UKURAN_FOTO, UKURAN_FOTO);
      fotoDraft = cv.toDataURL('image/jpeg', 0.85);
      setFoto(fotoDraft);
      URL.revokeObjectURL(url);
    };
    img.onerror = () => {
      $('err').textContent = 'Foto tidak bisa dibaca. Coba file lain.';
      URL.revokeObjectURL(url);
    };
    img.src = url;
  });

  $('btn-hapus-foto').addEventListener('click', () => {
    fotoDraft = '';
    $('in-foto').value = '';
    setFoto('');
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const namaBaru = $('in-nama').value.trim();
    const bioBaru = $('in-bio').value.trim() || 'Mahasiswa Informatika, Universitas Singaperbangsa Karawang.';
    if (!namaBaru) { $('err').textContent = 'Nama tidak boleh kosong.'; return; }

    const mapelBaru = Array.prototype.filter.call(cbs(), (cb) => cb.checked).map((cb) => Number(cb.value));

    try {
      const fd = new FormData();
      fd.append('nama', namaBaru);
      fd.append('bio', bioBaru);
      fd.append('foto', fotoDraft);
      fd.append('mapel', JSON.stringify(mapelBaru));

      const res = await fetch('../control/prosesProfil.php', {
        method: 'POST',
        body: fd
      });
      const result = await res.json();

      if (!result.success) {
        $('err').textContent = result.message || 'Gagal menyimpan profil.';
        return;
      }

      nama = namaBaru;
      bio = bioBaru;
      foto = fotoDraft;
      ambil = mapelBaru;
      renderProfil();
      buka(false);
      $('btn-edit').focus();
    } catch (err) {
      $('err').textContent = 'Gagal terhubung ke server.';
    }
  });

  $('btn-logout').addEventListener('click', () => {
    if (!confirm('Yakin ingin logout?')) return;
    try { sessionStorage.clear(); } catch (e) { }
    window.location.href = '../control/prosesLogout.php';
  });
})();