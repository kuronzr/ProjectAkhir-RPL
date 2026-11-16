(function () {
  const box = document.getElementById('courses');
  if (box) {
    const isAdmin = box.dataset.admin === '1';
    const RAW = JSON.parse(box.dataset.courses || '[]');
    const COURSES = RAW.map((item) => ({
      id: item.id,
      nama: item.nama_kursus,
      prodi: item.kategori,
      pola: (item.tema_banner || 'p-navy').startsWith('p-') ? item.tema_banner : 'p-' + item.tema_banner,
      jmlPraktikum: item.jumlah_praktikum || 0,
      jmlMateri: item.jumlah_materi || 0,
      progres: item.progres || 0
    }));

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

      if (isAdmin) {
        body.appendChild(el('div', 'c-count', c.jmlPraktikum + ' praktikum · ' + c.jmlMateri + ' materi'));
      }

      const act = el('div', 'c-action');
      if (isAdmin) {
        const a = el('a', 'c-btn solid', 'Kelola kursus');
        a.href = 'course.php?id=' + encodeURIComponent(c.id);
        act.appendChild(a);

        const del = el('a', 'c-btn', 'Hapus');
        del.href = '../control/prosesCourse.php?delete=' + encodeURIComponent(c.id);
        del.addEventListener('click', (e) => {
          if (!confirm('Yakin ingin menghapus kursus "' + c.nama + '"?')) {
            e.preventDefault();
          }
        });
        act.appendChild(del);
      } else {
        const a = el('a', 'c-btn', 'Lihat kursus');
        a.href = 'course.php?id=' + encodeURIComponent(c.id);
        act.appendChild(a);
      }

      body.appendChild(act);
      art.appendChild(body);
      return art;
    }

    function render() {
      const q = (cari ? cari.value : '').trim().toLowerCase();
      const data = COURSES.filter((c) => c.nama.toLowerCase().includes(q));

      if (urut && urut.value === 'progres') {
        data.sort((a, b) => b.progres - a.progres);
      } else {
        data.sort((a, b) => a.nama.localeCompare(b.nama));
      }

      box.innerHTML = '';
      if (!data.length) {
        const emptyMsg = isAdmin
          ? 'Belum ada course. Klik "+ Tambah Course" untuk membuat kursus baru.'
          : 'Course tidak ditemukan.';
        box.appendChild(el('p', 'c-empty', emptyMsg));
        return;
      }
      data.forEach((c) => box.appendChild(card(c)));
    }

    viewBtns.forEach((b) => b.addEventListener('click', () => {
      box.className = 'courses ' + b.dataset.view;
      viewBtns.forEach((x) => {
        const on = x === b;
        x.classList.toggle('active', on);
        x.setAttribute('aria-pressed', on);
      });
    }));

    if (urut) urut.addEventListener('change', render);
    if (cari) cari.addEventListener('input', render);
    render();

    const modal = document.getElementById('modalTambahCourse');
    const openBtn = document.getElementById('btnTambahCourse');
    if (modal && openBtn) {
      const closeModal = () => {
        modal.hidden = true;
        document.body.classList.remove('modal-open');
      };

      openBtn.addEventListener('click', () => {
        modal.hidden = false;
        document.body.classList.add('modal-open');
      });

      modal.querySelectorAll('[data-close-course]').forEach((btn) => {
        btn.addEventListener('click', closeModal);
      });

      modal.addEventListener('mousedown', (e) => {
        if (e.target === modal) closeModal();
      });

      document.addEventListener('keydown', (e) => {
        if (!modal.hidden && e.key === 'Escape') closeModal();
      });
    }
  }

  document.addEventListener('click', (e) => {
    const kBtn = e.target.closest('.k-tab');
    if (kBtn) {
      document.querySelectorAll('.k-tab').forEach((b) => {
        b.classList.remove('active');
        b.setAttribute('aria-pressed', 'false');
      });
      kBtn.classList.add('active');
      kBtn.setAttribute('aria-pressed', 'true');

      const tab = kBtn.dataset.tab;
      const wrapPrak = document.getElementById('wrap-praktikum');
      const wrapMat = document.getElementById('wrap-materi');
      if (wrapPrak) wrapPrak.hidden = (tab !== 'praktikum');
      if (wrapMat) wrapMat.hidden = (tab !== 'materi');
      return;
    }

    const mBtn = e.target.closest('.m-tab');
    if (mBtn) {
      document.querySelectorAll('.m-tab').forEach((b) => {
        b.classList.remove('active');
        b.setAttribute('aria-pressed', 'false');
      });
      mBtn.classList.add('active');
      mBtn.setAttribute('aria-pressed', 'true');

      const tab = mBtn.dataset.tab;
      const panelDesc = document.getElementById('panel-deskripsi');
      const panelMod = document.getElementById('panel-modul');
      if (panelDesc) panelDesc.hidden = (tab !== 'deskripsi');
      if (panelMod) panelMod.hidden = (tab !== 'modul');
    }
  });

  const fileInput = document.getElementById('berkas');
  const dropBtn = document.getElementById('drop');
  const dropTitle = document.getElementById('drop-title');
  if (dropBtn && fileInput && dropTitle) {
    dropBtn.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', () => {
      if (fileInput.files.length > 0) {
        dropTitle.textContent = fileInput.files[0].name;
      }
    });
  }

  const vidWrap = document.getElementById('video-wrapper');
  const videoEl = document.getElementById('materi-video');
  if (vidWrap && videoEl && vidWrap.dataset.showAd === '1') {
    const mainSrc = vidWrap.dataset.mainSrc;
    const adOverlay = document.getElementById('ad-overlay');
    const skipBtn = document.getElementById('btn-skip-ad');
    const playAdBtn = document.getElementById('btn-ad-play');

    let isPlayingAd = true;
    let countdown = 5;
    let timerStarted = false;
    let timerId = null;
    let lastAllowedTime = 0;

    videoEl.addEventListener('timeupdate', () => {
      if (isPlayingAd && !videoEl.seeking) {
        lastAllowedTime = videoEl.currentTime;
      }
    });

    videoEl.addEventListener('seeking', () => {
      if (isPlayingAd && Math.abs(videoEl.currentTime - lastAllowedTime) > 0.5) {
        videoEl.currentTime = lastAllowedTime;
      }
    });

    if (playAdBtn) {
      playAdBtn.addEventListener('click', () => {
        if (videoEl.paused) {
          videoEl.play().catch(() => { });
        } else {
          videoEl.pause();
        }
      });
    }

    videoEl.addEventListener('pause', () => {
      if (isPlayingAd && playAdBtn) playAdBtn.textContent = '▶ Lanjutkan Iklan';
    });

    function switchToMainVideo() {
      if (!isPlayingAd) return;
      isPlayingAd = false;

      if (timerId) clearInterval(timerId);
      if (adOverlay) adOverlay.remove();

      videoEl.pause();
      videoEl.currentTime = 0;
      videoEl.setAttribute('controls', 'controls');
      videoEl.setAttribute('controlsList', 'nodownload');
      videoEl.controls = true;

      if (mainSrc) {
        videoEl.src = mainSrc;
        videoEl.load();
        const playPromise = videoEl.play();
        if (playPromise !== undefined) {
          playPromise.catch(() => {
          });
        }
      }
    }

    videoEl.addEventListener('play', () => {
      if (!isPlayingAd) return;
      if (playAdBtn) playAdBtn.textContent = '❚❚ Jeda Iklan';

      if (timerStarted || !skipBtn) return;
      timerStarted = true;

      timerId = setInterval(() => {
        countdown--;
        if (countdown > 0) {
          skipBtn.textContent = 'Lewati dalam ' + countdown + 'd';
        } else {
          clearInterval(timerId);
          skipBtn.disabled = false;
          skipBtn.style.cursor = 'pointer';
          skipBtn.style.background = '#05003a';
          skipBtn.textContent = 'Lewati Iklan →';
        }
      }, 1000);
    });

    if (skipBtn) {
      skipBtn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        if (!skipBtn.disabled) {
          switchToMainVideo();
        }
      });
    }

    videoEl.addEventListener('ended', () => {
      if (isPlayingAd) {
        switchToMainVideo();
      }
    });
  }
})();