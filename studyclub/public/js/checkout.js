(function () {
  const PAKET = {
    bulanan: { label: 'Premium bulanan', harga: 159000 },
    tahunan: { label: 'Premium tahunan', harga: 1600000 }
  };
  const PAJAK = 2000;

  const form = document.getElementById('checkout');
  if (!form) return;

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

  const awal = new URLSearchParams(location.search).get('paket');
  if (awal && PAKET[awal]) {
    form.querySelector('input[name="paket"][value="' + awal + '"]').checked = true;
  }
  form.querySelectorAll('input[name="paket"]').forEach((r) => r.addEventListener('change', updateRingkasan));
  updateRingkasan();

  function setErr(name, msg) {
    const p = form.querySelector('.err[data-for="' + name + '"]');
    if (p) p.textContent = msg;
    const inp = form.elements[name];
    if (inp && inp.classList) inp.classList.toggle('invalid', !!msg);
  }

  form.addEventListener('submit', async (e) => {
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

    const submitBtn = $('btn-submit-pay');
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = 'Memproses...';
    }

    try {
      const formData = new FormData();
      formData.append('paket', paketDipilih());
      formData.append('nama', v.nama);
      formData.append('negara', v.negara);
      formData.append('alamat', v.alamat);
      formData.append('metode', v.metode);

      const res = await fetch('../control/prosesCheckout.php', {
        method: 'POST',
        body: formData
      });
      const result = await res.json();

      if (result.success) {
        const p = PAKET[paketDipilih()];
        const total = p.harga + PAJAK;

        $('sukses-teks').textContent =
          'Terima kasih, ' + v.nama + '. Pengajuan ' + p.label + ' sebesar ' + rp(total) +
          ' via ' + v.metode + ' telah dikirim dan sedang menunggu persetujuan Admin.';
        $('sukses').hidden = false;
        document.querySelector('#sukses .btn-pay').focus();
      } else {
        alert(result.message || 'Gagal memproses pengajuan.');
      }
    } catch (err) {
      alert('Gagal terhubung ke server.');
    } finally {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Berlangganan';
      }
    }
  });

  form.addEventListener('input', (e) => {
    const n = e.target.name || e.target.id;
    if (n === 'terms') setErr('terms', '');
    else if (n) setErr(n, '');
  });
})();