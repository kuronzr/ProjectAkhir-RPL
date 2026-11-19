(function () {
  const $ = (id) => document.getElementById(id);
  const fotoBox = $('foto');
  if (!fotoBox) return;

  const MAKS_FILE = 10 * 1024 * 1024;
  const fotoAsli = fotoBox.dataset.src || '';
  let previewUrl = null;

  function setFoto(src) {
    const containers = [fotoBox, ...document.querySelectorAll('.header .avatar')];
    containers.forEach((box) => {
      if (!box) return;
      box.innerHTML = '';
      if (src) {
        const img = document.createElement('img');
        img.src = src;
        img.alt = 'Profil';
        img.style.width = '100%';
        img.style.height = '100%';
        img.style.objectFit = 'cover';
        img.style.borderRadius = '50%';
        img.style.display = 'block';
        box.appendChild(img);
      }
    });
  }

  setFoto(fotoAsli);

  const tampil = $('tampil');
  const form = $('form-edit');
  function buka(on) {
    tampil.hidden = on;
    form.hidden = !on;
  }

  $('btn-edit').addEventListener('click', () => {
    $('err').textContent = '';
    $('hapus-foto-flag').value = '0';
    $('in-foto').value = '';
    buka(true);
    $('in-nama').focus();
  });

  $('btn-batal').addEventListener('click', () => {
    if (previewUrl) {
      URL.revokeObjectURL(previewUrl);
      previewUrl = null;
    }
    $('in-foto').value = '';
    $('hapus-foto-flag').value = '0';
    setFoto(fotoAsli);
    buka(false);
    $('btn-edit').focus();
  });

  $('in-foto').addEventListener('change', (e) => {
    const file = e.target.files && e.target.files[0];
    if (!file) return;
    $('err').textContent = '';

    if (file.type.indexOf('image/') !== 0) {
      $('err').textContent = 'File harus berupa gambar.';
      e.target.value = '';
      return;
    }
    if (file.size > MAKS_FILE) {
      $('err').textContent = 'Ukuran foto maksimal 10 MB.';
      e.target.value = '';
      return;
    }

    if (previewUrl) URL.revokeObjectURL(previewUrl);
    previewUrl = URL.createObjectURL(file);
    $('hapus-foto-flag').value = '0';
    setFoto(previewUrl);
  });

  $('btn-hapus-foto').addEventListener('click', () => {
    if (previewUrl) {
      URL.revokeObjectURL(previewUrl);
      previewUrl = null;
    }
    $('in-foto').value = '';
    $('hapus-foto-flag').value = '1';
    setFoto('');
  });

  $('btn-logout').addEventListener('click', () => {
    if (!confirm('Yakin ingin logout?')) return;
    try { sessionStorage.clear(); } catch (e) { }
    window.location.href = '../control/prosesLogout.php';
  });
})();