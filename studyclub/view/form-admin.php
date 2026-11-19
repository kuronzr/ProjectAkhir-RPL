<?php
session_start();
require_once '../config/database.php';
require_once '../model/courseModel.php';
require_once '../control/prosesProfil.php';

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
  header("Location: course.php");
  exit;
}

$courseId = (int) ($_GET['course'] ?? 0);
$type = ($_GET['type'] ?? 'materi') === 'praktikum' ? 'praktikum' : 'materi';
$isPrak = ($type === 'praktikum');
$db = (new Database())->getConnection();
$fotoHeader = getFotoProfil($db);
$courseModel = new CourseModel($db);
$course = $courseModel->getCourseById($courseId);

if (!$course) {
  header("Location: course.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tambah <?= $isPrak ? 'Praktikum' : 'Materi' ?> - Study Club</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../public/CSS/style.css">
</head>

<body>

  <!-- ===== HEADER ===== -->
  <header class="header">
    <div class="bar">
      <a href="home.php" class="logo"><img src="../public/img/logo.png" alt="Study Club"></a>
      <nav aria-label="Menu utama">
        <ul class="nav">
          <li><a href="home.php">Home</a></li>
          <li><a href="course.php" class="current" aria-current="page">Course</a></li>
          <li><a href="schedule.php">Schedule</a></li>
          <li><a href="pengajuan-subscription.php">Pengajuan</a></li>
          <li><a href="anggota-subscription.php">Subscription</a></li>
        </ul>
      </nav>
      <div class="actions">
        <span class="role">Admin</span>
        <a href="profil.php" class="avatar" aria-label="Profil admin">
          <?php if (!empty($fotoHeader)): ?>
            <img src="<?= htmlspecialchars($fotoHeader, ENT_QUOTES, 'UTF-8') ?>" alt="Profil">
          <?php endif; ?>
        </a>
      </div>
    </div>
  </header>

  <main class="container m-page">
    <a class="m-back" href="course.php?id=<?= $courseId ?>">&larr; Kembali</a>

    <form class="a-card" action="../control/prosesCourse.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="<?= $isPrak ? 'create_praktikum' : 'create_materi' ?>">
      <input type="hidden" name="course_id" value="<?= $courseId ?>">

      <h1 class="a-judul">Tambah <?= $isPrak ? 'Praktikum' : 'Materi' ?> -
        <?= htmlspecialchars($course['nama_kursus']) ?></h1>

      <label class="a-label" for="nama"><?= $isPrak ? 'Nama Praktikum' : 'Nama Materi' ?></label>
      <input class="a-input" type="text" id="nama" name="judul" maxlength="120" required>

      <label class="a-label" for="desk"><?= $isPrak ? 'Instruksi Praktikum' : 'Deskripsi' ?></label>
      <textarea class="a-input" id="desk" name="deskripsi" rows="3" required></textarea>

      <?php if ($isPrak): ?>
        <div class="only-praktikum">
          <label class="a-label" for="tgl">Tenggat</label>
          <input class="a-input" type="date" id="tgl" name="tanggal" value="<?= date('Y-m-d') ?>" required>

          <label class="a-label" for="ref">Referensi & Link GitHub (pisahkan per baris)</label>
          <textarea class="a-input" id="ref" name="referensi_link" rows="3"
            placeholder="https://github.com/..."></textarea>
        </div>
      <?php else: ?>
        <div class="only-materi">
          <label class="a-label" for="is_premium">Akses Materi</label>
          <select class="a-input" id="is_premium" name="is_premium">
            <option value="0">Gratis (Semua Anggota / Reguler)</option>
            <option value="1">Khusus Premium (Terkunci untuk Reguler)</option>
          </select>

          <label class="a-label" for="poin">Poin Modul (pisahkan per baris)</label>
          <textarea class="a-input" id="poin" name="poin_modul" rows="3"
            placeholder="Pengenalan Konsep&#10;Studi Kasus"></textarea>

          <span class="a-label">Modul (PDF)</span>
          <input type="file" id="modul-input" name="file_modul" accept=".pdf" hidden>
          <button type="button" class="a-drop" id="modul-drop">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
              stroke-linejoin="round" aria-hidden="true">
              <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
              <path d="M12 16v-5" />
              <path d="M9.5 13l2.5-2.5 2.5 2.5" />
            </svg>
            <span class="a-drop-title" id="modul-title">Pilih file di sini</span>
            <span class="a-drop-info">pdf, maks 10MB</span>
          </button>

          <span class="a-label">Video</span>
          <input type="file" id="video-input" name="file_video" accept="video/*" hidden>
          <button type="button" class="a-drop" id="video-drop">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
              stroke-linejoin="round" aria-hidden="true">
              <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
              <path d="M12 16v-5" />
              <path d="M9.5 13l2.5-2.5 2.5 2.5" />
            </svg>
            <span class="a-drop-title" id="video-title">Pilih file di sini</span>
            <span class="a-drop-info">Video maks 200MB</span>
          </button>
        </div>
      <?php endif; ?>

      <div class="a-actions">
        <button type="submit" class="a-btn a-simpan">Unggah</button>
      </div>
    </form>
  </main>

  <script src="../public/js/form-admin.js"></script>
  <script src="../public/js/notif.js"></script>
</body>

</html>