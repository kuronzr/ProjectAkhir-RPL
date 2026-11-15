// Penyimpanan perubahan admin (sementara di localStorage, sampai ada backend).
// Muat SETELAH kursus-data.js dan praktikum-data.js. Dipakai halaman admin,
// dan boleh juga dipasang di halaman siswa agar perubahan admin ikut tampil.
(function () {
  const KEY = 'kursus_admin_v1';
  const copy = (o) => JSON.parse(JSON.stringify(o || {}));
  let saved = null;
  try { saved = JSON.parse(localStorage.getItem(KEY)); } catch (e) {}

  const data = saved && saved.data ? saved.data : copy(window.KURSUS_DATA);
  const detail = saved && saved.detail ? saved.detail : copy(window.PRAKTIKUM_DETAIL);
  window.KURSUS_DATA = data;
  window.PRAKTIKUM_DETAIL = detail;

  window.AdminStore = {
    data, detail,
    save() { try { localStorage.setItem(KEY, JSON.stringify({ data, detail })); } catch (e) {} },
    reset() { try { localStorage.removeItem(KEY); } catch (e) {} }
  };
})();
