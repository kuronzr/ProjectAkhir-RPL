// Halaman Course (admin): urut, cari, ganti tampilan. Daftar course diambil dari KURSUS_DATA
// sehingga jumlah materi/praktikum ikut berubah setelah admin menambah atau menghapus.
(function () {
  const DATA = window.KURSUS_DATA || {};
  const COURSES = Object.keys(DATA).map((id) => ({ id, ...DATA[id] }));

  const box = document.getElementById('courses');
  const urut = document.getElementById('urut');
  const cari = document.getElementById('cari');
  const viewBtns = document.querySelectorAll('.view-btn');

  function el(tag, cls, text) {
    const e = document.createElement(tag);
    if (cls) e.className = cls;
    if (text !== undefined) e.textContent = text;
    return e;
  }

  function card(c) {
    const art = el('article', 'c-card');
    const cover = el('div', 'c-cover ' + c.pola);
    cover.appendChild(el('span', 'c-tag', c.prodi));
    art.appendChild(cover);

    const body = el('div', 'c-body');
    body.appendChild(el('h2', 'c-title', c.nama));
    body.appendChild(el('div', 'c-count', c.praktikum.length + ' praktikum · ' + c.materi.length + ' materi'));

    const act = el('div', 'c-action');
    const a = el('a', 'c-btn solid', 'Kelola kursus');
    a.href = 'kursus-admin.html?course=' + encodeURIComponent(c.id);
    act.appendChild(a);
    body.appendChild(act);
    art.appendChild(body);
    return art;
  }

  function render() {
    const q = cari.value.trim().toLowerCase();
    const data = COURSES.filter((c) => c.nama.toLowerCase().includes(q))
      .sort((a, b) => a.nama.localeCompare(b.nama));
    box.innerHTML = '';
    if (!data.length) { box.appendChild(el('p', 'c-empty', 'Course tidak ditemukan.')); return; }
    data.forEach((c) => box.appendChild(card(c)));
  }

  viewBtns.forEach((b) => b.addEventListener('click', () => {
    box.className = 'courses ' + b.dataset.view;
    viewBtns.forEach((x) => { const on = x === b; x.classList.toggle('active', on); x.setAttribute('aria-pressed', on); });
  }));
  urut.addEventListener('change', render);
  cari.addEventListener('input', render);
  render();
})();
