<?php
session_start();
if (!isset($_SESSION['role']) || !isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

require_once '../config/database.php';
require_once '../model/UserModel.php';
require_once '../model/courseModel.php';
require_once '../model/SubscriptionModel.php';

$db = (new Database())->getConnection();
$userId = (int) $_SESSION['user_id'];
$isAdmin = (strtolower($_SESSION['role']) === 'admin');

$subModel = new SubscriptionModel($db);
if ($subModel->isUserBanned($userId)) {
  session_unset();
  session_destroy();
  header("Location: login.php");
  exit;
}
$isPremium = ($subModel->getUserMembership($userId) === 'premium');

$userModel = new UserModel($db);
$user = $userModel->getUserById($userId);

$namaUser = $user['nama'] ?? ($_SESSION['nama'] ?? 'Pengguna');
$bioUser = !empty($user['bio']) ? $user['bio'] : 'Mahasiswa Informatika, Universitas Singaperbangsa Karawang.';
$fotoUser = $user['foto'] ?? '';

$courseModel = new CourseModel($db);
$allCourses = $courseModel->getAllCourses();
$takenIds = $userModel->getUserCourseIds($userId);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Profil - Study Club</title>
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
          <?php if ($isAdmin): ?>
            <li><a href="home.php">Home</a></li>
            <li><a href="course.php">Course</a></li>
            <li><a href="schedule.php">Schedule</a></li>
            <li><a href="pengajuan-subscription.php">Pengajuan</a></li>
            <li><a href="anggota-subscription.php">Subscription</a></li>
          <?php else: ?>
            <li><a href="home.php">Home</a></li>
            <li><a href="course.php">Course</a></li>
            <li><a href="schedule.php">Schedule</a></li>
          <?php endif; ?>
        </ul>
      </nav>

      <div class="actions">
        <?php if ($isAdmin): ?>
          <span class="role">Admin</span>
          <a href="profil.php" class="avatar" aria-label="Profil admin"></a>
        <?php else: ?>
          <?php if ($isPremium): ?>
            <span class="btn-premium" style="cursor: default;">Premium</span>
          <?php else: ?>
            <a href="premium.php" class="btn-premium">Reguler</a>
          <?php endif; ?>
          <button class="icon-btn" aria-label="Notifikasi">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round">
              <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
              <path d="M13.7 21a2 2 0 0 1-3.4 0" />
            </svg>
          </button>
          <a href="profil.php" class="avatar" aria-label="Profil"></a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <main class="container p-layout" id="profil-root" data-foto="<?= htmlspecialchars($fotoUser, ENT_QUOTES, 'UTF-8') ?>"
    data-all-courses="<?= htmlspecialchars(json_encode($allCourses), ENT_QUOTES, 'UTF-8') ?>"
    data-taken-ids="<?= htmlspecialchars(json_encode($takenIds), ENT_QUOTES, 'UTF-8') ?>">

    <!-- ===== KOLOM KIRI: PROFIL ===== -->
    <aside class="p-side">
      <div class="p-photo" id="foto"></div>

      <!-- Mode Tampil -->
      <div id="tampil">
        <h1 class="p-name" id="nama"><?= htmlspecialchars($namaUser) ?></h1>
        <p class="p-bio" id="bio"><?= htmlspecialchars($bioUser) ?></p>

        <button type="button" class="p-edit" id="btn-edit">Edit profile</button>
        <button type="button" class="p-edit p-logout" id="btn-logout">Logout</button>
      </div>

      <!-- Mode Edit -->
      <form id="form-edit" class="p-form" hidden>
        <label for="in-foto">Foto profil</label>
        <input type="file" id="in-foto" accept="image/*">
        <button type="button" class="p-edit p-small" id="btn-hapus-foto">Hapus foto</button>

        <label for="in-nama">Nama</label>
        <input type="text" id="in-nama" maxlength="100" required>

        <label for="in-bio">Bio</label>
        <textarea id="in-bio" rows="3" maxlength="255"></textarea>

        <fieldset class="p-fieldset">
          <legend>Mata Pelajaran yang Diambil</legend>
          <div id="pilih-mapel"></div>
        </fieldset>

        <p class="p-err" id="err" role="alert"></p>

        <div class="p-row">
          <button type="submit" class="p-edit p-save">Simpan</button>
          <button type="button" class="p-edit" id="btn-batal">Batal</button>
        </div>
      </form>
    </aside>

    <!-- ===== KOLOM KANAN: MATA PELAJARAN YANG DIAMBIL ===== -->
    <section class="p-main">
      <h2 class="p-heading">Mata Pelajaran yang Diambil</h2>
      <div class="p-grid" id="mapel"></div>
    </section>

  </main>

  <script src="../public/js/profil.js"></script>
  <script src="../public/js/notif.js"></script>
</body>

</html>