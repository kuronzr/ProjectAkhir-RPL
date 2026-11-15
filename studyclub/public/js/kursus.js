// Halaman Kursus: tampilkan daftar praktikum/materi sesuai course (?course=)
// Data ada di kursus-data.js (window.KURSUS_DATA)
(function () {
  const DATA = window.KURSUS_DATA || {};

  const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sept', 'Okt', 'Nov', 'Des'];
  const fmt = (iso) => { const [, m, d] = iso.split('-'); return Number(d) + ' ' + BULAN[Number(m) - 1]; };

  const id = new URLSearchParams(location.search).get('course');
  const course = DATA[id];
  const list = document.getElementById('daftar');
  const tabs = document.querySelectorAll('.k-tab');

  function el(tag, cls, text) {
    const e = document.createElement(tag);
    if (cls) e.className = cls;
    if (text !== undefined) e.textContent = text;
    return e;
  }

  function render(tab) {
    list.innerHTML = '';
    const items = course ? course[tab] : [];
    if (!items.length) {
      list.appendChild(el('li', 'k-empty', course ? 'Belum ada kegiatan.' : 'Course tidak ditemukan.'));
      return;
    }
    items.forEach(([judul, tgl], i) => {
      // Materi ke-3 di setiap course dikunci
      const terkunci = tab === 'materi' && i === 2;
      // Yang tidak terkunci bisa dibuka: materi -> video, praktikum -> form tugas
      const bisaDibuka = !terkunci;
      const halaman = tab === 'materi' ? 'materi.html' : 'praktikum.html';

      const li = el('li', bisaDibuka ? '' : 'k-item locked');
      let root = li;
      if (bisaDibuka) {
        root = el('a', 'k-item k-link');
        root.href = halaman + '?course=' + encodeURIComponent(id) + '&n=' + (i + 1);
        li.appendChild(root);
      }

      root.appendChild(el('span', 'k-ava'));
      const box = el('div');
      box.appendChild(el('div', 'k-name', judul));
      box.appendChild(el('div', 'k-date', fmt(tgl)));
      root.appendChild(box);

      if (terkunci) {
        li.setAttribute('aria-disabled', 'true');
        const lock = el('span', 'k-lock');
        lock.setAttribute('role', 'img');
        lock.setAttribute('aria-label', 'Materi terkunci');
        lock.innerHTML =
          '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">' +
          '<path d="M12 2a5 5 0 0 0-5 5v3H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2h-1V7a5 5 0 0 0-5-5zm-3 8V7a3 3 0 0 1 6 0v3H9zm3 4a2 2 0 0 1 1 3.73V19h-2v-1.27A2 2 0 0 1 12 14z"/>' +
          '</svg>';
        li.appendChild(lock);
      }
      list.appendChild(li);
    });
  }

  document.getElementById('judul').textContent = course ? course.nama : 'Course tidak ditemukan';
  if (course) {
    document.title = course.nama + ' - Study Club';
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

  // ?tab=materi membuka tab Materi langsung; selain itu mulai dari Praktikum
  const tabAwal = new URLSearchParams(location.search).get('tab') === 'materi' ? 'materi' : 'praktikum';
  aktifkan(tabAwal);
})();
