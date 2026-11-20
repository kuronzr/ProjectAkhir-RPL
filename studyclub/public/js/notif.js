(function () {
  const READ_KEY = 'studyclub_notif_read';
  const SCHEDULE_KEY = 'studyclub_schedules';

  const STATIS = [
    { id: 'promo', judul: 'Promo Premium diskon 67%', teks: 'Berlangganan sekarang dan hemat biaya paket belajar.', href: 'premium.php' },
  ];

  const btn = document.querySelector('.icon-btn[aria-label="Notifikasi"]');
  if (!btn) return;

  function loadJSON(key, fallback) {
    try { return JSON.parse(localStorage.getItem(key)) || fallback; } catch (e) { return fallback; }
  }
  const getRead = () => loadJSON(READ_KEY, []);
  const setRead = (arr) => { try { localStorage.setItem(READ_KEY, JSON.stringify(arr)); } catch (e) {} };

  function el(tag, cls, text) {
    const e = document.createElement(tag);
    if (cls) e.className = cls;
    if (text !== undefined) e.textContent = text;
    return e;
  }

  function jadwalNotif() {
    const now = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    const iso = (d) => d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    const dari = iso(now);
    const batas = new Date(now); batas.setDate(batas.getDate() + 14);
    const sampai = iso(batas);
    return loadJSON(SCHEDULE_KEY, [])
      .filter((j) => j && j.tanggal >= dari && j.tanggal <= sampai)
      .sort((a, b) => (a.tanggal + a.mulai).localeCompare(b.tanggal + b.mulai))
      .map((j) => {
        const [y, m, d] = j.tanggal.split('-');
        return {
          id: 'schedule-' + j.id,
          judul: 'Schedule: ' + j.nama,
          teks: d + '/' + m + '/' + y + ', ' + j.mulai + ' - ' + j.selesai + (j.lokasi ? ' · ' + j.lokasi : ''),
          href: 'schedule-anggota.html'
        };
      });
  }

  const semua = () => jadwalNotif().concat(STATIS);

  const wrap = el('div', 'notif-wrap');
  btn.parentNode.insertBefore(wrap, btn);
  wrap.appendChild(btn);

  const badge = el('span', 'notif-badge');
  badge.hidden = true;
  btn.appendChild(badge);

  const panel = el('div', 'notif-panel');
  panel.id = 'notif-panel';
  panel.hidden = true;
  panel.setAttribute('role', 'region');
  panel.setAttribute('aria-label', 'Daftar notifikasi');

  const head = el('div', 'notif-head');
  head.appendChild(el('h2', '', 'Notifikasi'));
  const readAll = el('button', 'notif-readall', 'Tandai semua dibaca');
  readAll.type = 'button';
  head.appendChild(readAll);
  panel.appendChild(head);

  const list = el('ul', 'notif-list');
  panel.appendChild(list);
  wrap.appendChild(panel);

  btn.setAttribute('aria-haspopup', 'true');
  btn.setAttribute('aria-expanded', 'false');
  btn.setAttribute('aria-controls', 'notif-panel');

  function render() {
    const read = getRead();
    const data = semua();
    const unread = data.filter((n) => read.indexOf(n.id) === -1).length;

    badge.hidden = unread === 0;
    badge.textContent = unread > 9 ? '9+' : String(unread);
    btn.setAttribute('aria-label', unread ? 'Notifikasi, ' + unread + ' belum dibaca' : 'Notifikasi');
    readAll.disabled = unread === 0;

    list.innerHTML = '';
    if (!data.length) {
      list.appendChild(el('li', 'notif-empty', 'Belum ada notifikasi.'));
      return;
    }
    data.forEach((n) => {
      const li = el('li');
      const a = el('a', 'notif-item' + (read.indexOf(n.id) === -1 ? ' unread' : ''));
      a.href = n.href;
      a.appendChild(el('span', 'notif-dot'));
      const box = el('div');
      box.appendChild(el('div', 'notif-title', n.judul));
      box.appendChild(el('div', 'notif-text', n.teks));
      a.appendChild(box);
      a.addEventListener('click', () => {
        const r = getRead();
        if (r.indexOf(n.id) === -1) { r.push(n.id); setRead(r); }
      });
      li.appendChild(a);
      list.appendChild(li);
    });
  }

  function posisi() {
    if (window.matchMedia('(max-width: 640px)').matches) {
      const h = document.querySelector('.header');
      if (h) panel.style.top = h.getBoundingClientRect().bottom + 8 + 'px';
    } else {
      panel.style.top = '';
    }
  }

  function buka(on) {
    if (on) { render(); posisi(); }
    panel.hidden = !on;
    btn.setAttribute('aria-expanded', on ? 'true' : 'false');
  }

  btn.addEventListener('click', (e) => { e.stopPropagation(); buka(panel.hidden); });
  readAll.addEventListener('click', () => {
    const ids = semua().map((n) => n.id);
    setRead(Array.from(new Set(getRead().concat(ids))));
    render();
  });
  panel.addEventListener('click', (e) => e.stopPropagation());
  document.addEventListener('click', () => { if (!panel.hidden) buka(false); });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !panel.hidden) { buka(false); btn.focus(); }
  });
  window.addEventListener('resize', () => { if (!panel.hidden) posisi(); });
  window.addEventListener('storage', render);

  render();
})();
