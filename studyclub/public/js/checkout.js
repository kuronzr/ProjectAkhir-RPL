// Checkout: pilih paket (dari ?paket=), ringkasan harga, validasi, konfirmasi
(function () {
  const PAKET = {
    bulanan: { label: 'Premium bulanan', harga: 159000 },
    tahunan: { label: 'Premium tahunan', harga: 1600000 }
  };
  const PAJAK = 2000;

  const form = document.getElementById('checkout');
  const rp = (n) => 'Rp ' + n.toLocaleString('id-ID');
  const $ = (id) => document.getElementById(id);

  function paketDipilih() { return form.paket.value; }

  function updateRingkasan() {
    const p = PAKET[paketDipilih()];
    $('s-label').textContent = p.label;
    $('s-harga').textContent = rp(p.harga);
    $('s-sub').textContent = rp(p.harga);
    $('s-pajak').textContent = rp(PAJAK);
    $('s-total').textContent = rp(p.harga + PAJAK);
  }

  // Paket awal dari link "Langganan"
  const awal = new URLSearchParams(location.search).get('paket');
  if (awal && PAKET[awal]) {
    form.querySelector('input[name="paket"][value="' + awal + '"]').checked = true;
  }
  form.querySelectorAll('input[name="paket"]').forEach((r) => r.addEventListener('change', updateRingkasan));
  updateRingkasan();

  // Validasi
  function setErr(name, msg) {
    const p = form.querySelector('.err[data-for="' + name + '"]');
    if (p) p.textContent = msg;
    const inp = form.elements[name];
    if (inp && inp.classList) inp.classList.toggle('invalid', !!msg);
  }

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const v = {
      nama: form.nama.value.trim(),
      negara: form.negara.value.trim(),
      alamat: form.alamat.value.trim(),
      metode: form.metode.value
    };
    let ok = true;
    const cek = (name, salah, pesan) => { setErr(name, salah ? pesan : ''); if (salah) ok = false; };

    cek('nama', !v.nama, 'Nama lengkap wajib diisi.');
    cek('negara', !v.negara, 'Negara wajib diisi.');
    cek('alamat', !v.alamat, 'Alamat wajib diisi.');
    cek('metode', !v.metode, 'Pilih metode pembayaran.');
    cek('terms', !$('terms').checked, 'Setujui Syarat & Ketentuan untuk melanjutkan.');
    if (!ok) {
      const first = form.querySelector('.invalid') || form.querySelector('.err:not(:empty)');
      if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }

    const p = PAKET[paketDipilih()];
    const total = p.harga + PAJAK;
    try {
      localStorage.setItem('studyclub_premium', JSON.stringify({
        paket: paketDipilih(), nama: v.nama, negara: v.negara, alamat: v.alamat,
        metode: v.metode, total: total, waktu: new Date().toISOString()
      }));
    } catch (err) {}

    $('sukses-teks').textContent =
      'Terima kasih, ' + v.nama + '. ' + p.label + ' dengan total ' + rp(total) +
      ' akan dibayar lewat ' + v.metode + '.';
    $('sukses').hidden = false;
    document.querySelector('#sukses .btn-pay').focus();
  });

  // Hapus pesan error saat pengguna memperbaiki isian
  form.addEventListener('input', (e) => {
    const n = e.target.name || e.target.id;
    if (n === 'terms') setErr('terms', '');
    else if (n) setErr(n, '');
  });
})();
