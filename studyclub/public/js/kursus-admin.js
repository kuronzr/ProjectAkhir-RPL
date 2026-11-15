// Daftar kursus (admin): tab Praktikum/Materi, tombol tambah, dan ikon edit per item
(function () {
  const DATA = window.KURSUS_DATA || {};
  const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sept', 'Okt', 'Nov', 'Des'];
  const fmt = (iso) => { const [, m, d] = iso.split('-'); return Number(d) + ' ' + BULAN[Number(m) - 1]; };

  const q = new URLSearchParams(location.search);
  const id = q.get('course');
  const course = DATA[id];
  const cid = encodeURIComponent(id || '');
  const list = document.getElementById('daftar');
  const plus = document.getElementById('tambah');
  const tabs = document.querySelectorAll('.k-tab');

  const PENSIL = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20l1-4L17 4l3 3L8 19z"/><path d="M14 7l3 3"/></svg>';

  function el(tag, cls, text) {
    const e = document.createElement(tag);
    if (cls) e.className = cls;
    if (text !== undefined) e.textContent = text;
    return e;
  }

  function render(tab) {
    list.innerHTML = '';
    plus.href = 'form-admin.html?course=' + cid + '&type=' + tab;
    plus.setAttribute('aria-label', 'Tambah ' + tab);
    plus.hidden = !course;
    const items = course ? course[tab] : [];
    if (!items.length) {
      list.appendChild(el('li', 'k-empty', course ? 'Belum ada kegiatan.' : 'Course tidak ditemukan.'));
      return;
    }
    items.forEach(([judul, tgl], i) => {
      const li = el('li', 'k-item');
      li.appendChild(el('span', 'k-ava'));

      // Materi: judul membuka detail. Praktikum: diedit lewat ikon pensil.
      const box = el(tab === 'materi' ? 'a' : 'div', 'a-box');
      if (tab === 'materi') box.href = 'materi-admin.html?course=' + cid + '&n=' + (i + 1);
      box.appendChild(el('div', 'k-name', judul));
      box.appendChild(el('div', 'k-date', (tab === 'praktikum' ? 'Due ' : '') + fmt(tgl)));
      li.appendChild(box);

      const pen = el('a', 'a-pencil');
      pen.href = 'form-admin.html?course=' + cid + '&type=' + tab + '&n=' + (i + 1);
      pen.setAttribute('aria-label', 'Edit ' + judul);
      pen.innerHTML = PENSIL;
      li.appendChild(pen);
      list.appendChild(li);
    });
  }

  document.getElementById('judul').textContent = course ? course.nama : 'Course tidak ditemukan';
  if (course) {
    document.title = course.nama + ' (Admin) - Study Club';
    document.getElementById('cover').className = 'k-cover ' + course.pola;
    document.getElementById('tag').textContent = course.prodi;
  }

  function aktifkan(nama) {
    tabs.forEach((x) => {
      const on = x.dataset.tab === nama;
      x.classList.toggle('active', on);
      x.setAttribute('aria-pressed', on);
    });
    render(nama);
  }
  tabs.forEach((b) => b.addEventListener('click', () => aktifkan(b.dataset.tab)));
  aktifkan(q.get('tab') === 'materi' ? 'materi' : 'praktikum');
})();
