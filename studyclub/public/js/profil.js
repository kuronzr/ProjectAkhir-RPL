// Halaman Profil: kursus yang diambil + edit nama, bio, foto, dan kursus (disimpan di localStorage)
(function () {
  const KEY = 'studyclub_profil';
  const MAPEL = [
    { id: 'web-dev', nama: 'Web Development', kategori: 'Programming', pola: 'p-plaid' },
    { id: 'cybersec', nama: 'Cyber Security', kategori: 'Security', pola: 'p-navy' },
    { id: 'iot', nama: 'Internet of Things (IoT)', kategori: 'Hardware', pola: 'p-hex' },
    { id: 'ui-ux', nama: 'UI/UX Design', kategori: 'Design', pola: 'p-diamond' }
  ];
  const SEMUA = MAPEL.map((m) => m.id);
  const UKURAN_FOTO = 320;      // px, hasil foto dipotong persegi
  const MAKS_FILE = 10 * 1024 * 1024;

  const $ = (id) => document.getElementById(id);
  const load = () => { try { return JSON.parse(localStorage.getItem(KEY)) || {}; } catch (e) { return {}; } };
  const save = (d) => { try { localStorage.setItem(KEY, JSON.stringify(d)); return true; } catch (e) { return false; } };

  function el(tag, cls, text) {
    const e = document.createElement(tag);
    if (cls) e.className = cls;
    if (text !== undefined) e.textContent = text;
    return e;
  }

  // ---- State awal ----
  const p = load();
  let nama = p.nama || $('nama').textContent;
  let bio = typeof p.bio === 'string' ? p.bio : $('bio').textContent;
  let foto = typeof p.foto === 'string' && p.foto.indexOf('data:image/') === 0 ? p.foto : '';
  let ambil = Array.isArray(p.mapel) ? p.mapel.filter((id) => SEMUA.indexOf(id) !== -1) : SEMUA.slice();
  let fotoDraft = foto;

  // ---- Tampilan ----
  function setFoto(src) {
    $('foto').style.backgroundImage = src ? 'url("' + src + '")' : '';
  }

  function renderMapel() {
    const box = $('mapel');
    box.innerHTML = '';
    const daftar = MAPEL.filter((m) => ambil.indexOf(m.id) !== -1);
    if (!daftar.length) {
      box.appendChild(el('p', 'p-empty', 'Belum ada kursus yang diambil.'));
      return;
    }
    daftar.forEach((m) => {
      const a = el('a', 'p-card');
      a.href = 'kursus.html?course=' + m.id;
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

  // Pilihan kursus di form edit
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

  // ---- Edit ----
  const tampil = $('tampil'), form = $('form-edit');
  function buka(on) { tampil.hidden = on; form.hidden = !on; }

  $('btn-edit').addEventListener('click', () => {
    $('in-nama').value = nama;
    $('in-bio').value = bio;
    $('in-foto').value = '';
    $('err').textContent = '';
    fotoDraft = foto;
    cbs().forEach((cb) => { cb.checked = ambil.indexOf(cb.value) !== -1; });
    buka(true);
    $('in-nama').focus();
  });

  $('btn-batal').addEventListener('click', () => {
    setFoto(foto);          // buang perubahan foto yang belum disimpan
    buka(false);
    $('btn-edit').focus();
  });

  // Foto: pilih file, potong persegi, kecilkan, lalu pratinjau
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

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const namaBaru = $('in-nama').value.trim();
    const bioBaru = $('in-bio').value.trim();
    if (!namaBaru) { $('err').textContent = 'Nama tidak boleh kosong.'; return; }
    const mapelBaru = Array.prototype.filter.call(cbs(), (cb) => cb.checked).map((cb) => cb.value);

    const ok = save({ nama: namaBaru, bio: bioBaru, foto: fotoDraft, mapel: mapelBaru });
    if (!ok) { $('err').textContent = 'Gagal menyimpan. Penyimpanan browser penuh atau diblokir.'; return; }

    nama = namaBaru; bio = bioBaru; foto = fotoDraft; ambil = mapelBaru;
    renderProfil();
    buka(false);
    $('btn-edit').focus();
  });

  // ---- Logout ----
  $('btn-logout').addEventListener('click', () => {
    if (!confirm('Yakin ingin logout?')) return;
    try { sessionStorage.clear(); } catch (e) {}
    window.location.href = '../view/logout.php';   // ganti sesuai file logout project kamu
  });
})();
