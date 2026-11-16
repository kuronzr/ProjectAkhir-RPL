<?php
session_start();
require_once '../config/database.php';
require_once '../model/ScheduleModel.php';

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$isAdmin = (strtolower($_SESSION['role']) === 'admin');
$db = (new Database())->getConnection();
$scheduleModel = new ScheduleModel($db);
$schedules = $scheduleModel->getAllSchedules();
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $isAdmin ? 'Schedule Admin - Study Club' : 'Jadwal Kegiatan - Study Club' ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../public/CSS/style.css">
</head>

<body>

  <!-- ===== HEADER ===== -->
  <header class="header">
    <div class="bar">
      <a href="home.php" class="logo"><img src="../public/img/logo.png" alt="Study Club"></a>

      <nav aria-label="Menu utama">
        <ul class="nav">
          <?php if ($isAdmin): ?>
            <li><a href="home.php">Home</a></li>
            <li><a href="course.php">Course</a></li>
            <li><a href="schedule.php" class="current" aria-current="page">Schedule</a></li>
            <li><a href="pengajuan-subscription.php">Pengajuan</a></li>
            <li><a href="anggota-subscription.php">Subscription</a></li>
          <?php else: ?>
            <li><a href="home.php">Home</a></li>
            <li><a href="course.php">Course</a></li>
            <li><a href="schedule.php" class="current" aria-current="page">Schedule</a></li>
          <?php endif; ?>
        </ul>
      </nav>

      <div class="actions">
        <?php if ($isAdmin): ?>
          <span class="role">Admin</span>
          <a href="profil.php" class="avatar" aria-label="Profil admin"></a>
        <?php else: ?>
          <a href="premium.php" class="btn-premium">Premium</a>
          <button class="icon-btn" aria-label="Notifikasi">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
          </button>
          <a href="profil.php" class="avatar" aria-label="Profil"></a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <main class="container">
    <h1 class="page-title">Jadwal Kegiatan</h1>
    <p class="page-sub">
      <?= $isAdmin
          ? 'Tambahkan kegiatan belajar, praktikum, atau pertemuan Study Club.'
          : 'Daftar kegiatan Study Club. Halaman ini hanya untuk dilihat.' ?>
    </p>

    <?php if ($isAdmin): ?>
      <!-- ===== FORM KHUSUS ADMIN ===== -->
      <section class="panel-box" aria-label="Form jadwal kegiatan">
        <form id="form-jadwal" action="../control/prosesSchedule.php" method="post">
          <input type="hidden" name="action" value="create">
          <div class="form-grid">
            <div class="full">
              <label for="nama">Nama Kegiatan</label>
              <input type="text" id="nama" name="nama" required>
            </div>

            <div>
              <label for="kategori">Kategori</label>
              <select id="kategori" name="kategori" required>
                <option value="" disabled selected>Pilih kategori</option>
                <option value="Praktikum">Praktikum</option>
                <option value="Materi">Materi</option>
                <option value="Diskusi">Diskusi</option>
                <option value="Lainnya">Lainnya</option>
              </select>
            </div>

            <div>
              <label for="tanggal">Tanggal</label>
              <input type="date" id="tanggal" name="tanggal" required>
            </div>

            <div>
              <label for="mulai">Waktu Mulai</label>
              <input type="time" id="mulai" name="mulai" required>
            </div>

            <div>
              <label for="selesai">Waktu Selesai</label>
              <input type="time" id="selesai" name="selesai" required>
            </div>

            <div class="full">
              <label for="lokasi">Lokasi / Link Meeting</label>
              <input type="text" id="lokasi" name="lokasi">
            </div>

            <div class="full">
              <label for="deskripsi">Deskripsi</label>
              <textarea id="deskripsi" name="deskripsi"></textarea>
              <p class="error" id="error" role="alert"></p>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan Jadwal</button>
            <button type="reset" class="btn btn-ghost">Reset</button>
          </div>
        </form>
      </section>

      <h2 class="list-title">Daftar Jadwal</h2>

      <div class="filter-bar">
        <div class="filter">
          <label for="filter">Kategori</label>
          <select id="filter">
            <option value="">Semua</option>
            <option value="Praktikum">Praktikum</option>
            <option value="Materi">Materi</option>
            <option value="Diskusi">Diskusi</option>
            <option value="Lainnya">Lainnya</option>
          </select>
        </div>
        <span class="count" id="jumlah" aria-live="polite">0 kegiatan</span>
      </div>
    <?php else: ?>
      <!-- ===== TOOLBAR KHUSUS MEMBER ===== -->
      <div class="toolbar">
        <div>
          <label for="filter">Kategori</label>
          <select id="filter">
            <option value="">Semua</option>
            <option value="Praktikum">Praktikum</option>
            <option value="Materi">Materi</option>
            <option value="Diskusi">Diskusi</option>
            <option value="Lainnya">Lainnya</option>
          </select>
        </div>
        <span class="count" id="jumlah" aria-live="polite"></span>
      </div>
    <?php endif; ?>

    <!-- ===== TABEL DAFTAR JADWAL ===== -->
    <div class="table-wrap">
      <table id="tabel-jadwal" data-admin="<?= $isAdmin ? '1' : '0' ?>">
        <thead>
          <tr>
            <th>Kegiatan</th>
            <th>Kategori</th>
            <th>Tanggal</th>
            <th>Waktu</th>
            <th>Lokasi</th>
            <th>Deskripsi</th>
            <?php if ($isAdmin): ?>
              <th>Aksi</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody id="daftar">
          <?php if (empty($schedules)): ?>
            <tr class="row-empty">
              <td colspan="<?= $isAdmin ? 7 : 6 ?>" style="text-align:center;">Belum ada jadwal kegiatan.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($schedules as $s): ?>
              <tr data-kategori="<?= htmlspecialchars($s['kategori']) ?>">
                <td><?= htmlspecialchars($s['nama_kegiatan']) ?></td>
                <td><?= htmlspecialchars($s['kategori']) ?></td>
                <td><?= date('d M Y', strtotime($s['tanggal'])) ?></td>
                <td><?= substr($s['waktu_mulai'], 0, 5) ?> - <?= substr($s['waktu_selesai'], 0, 5) ?></td>
                <td><?= htmlspecialchars($s['lokasi'] ?: '-') ?></td>
                <td><?= htmlspecialchars($s['deskripsi'] ?: '-') ?></td>
                <?php if ($isAdmin): ?>
                  <td>
                    <a href="../control/prosesSchedule.php?delete=<?= $s['id'] ?>"
                       class="btn-hapus-jadwal"
                       style="color:#dc2626; font-weight:600; text-decoration:none;">Hapus</a>
                  </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>

  <script src="../public/js/schedule.js"></script>
  <script src="../public/js/notif.js"></script>
</body>

</html>