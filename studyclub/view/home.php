<?php
session_start();

if (!isset($_SESSION['role']) || !isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

require_once '../config/database.php';
require_once '../model/PengumumanModel.php';
require_once '../model/SubscriptionModel.php';
require_once '../control/prosesProfil.php';


$database = new Database();
$db = $database->getConnection();

$role = strtolower($_SESSION['role']);
$isAdmin = ($role === 'admin');

$subModel = new SubscriptionModel($db);
$membership = $subModel->getUserMembership($_SESSION['user_id']);
$_SESSION['membership_status'] = $membership;
$isPremium = ($membership === 'premium');
$fotoHeader = getFotoProfil($db);

if ($subModel->isUserBanned($_SESSION['user_id'])) {
  session_unset();
  session_destroy();
  session_start();
  $_SESSION['login_error'] = 'Akun Anda telah diblokir oleh Admin.';
  header("Location: login.php");
  exit;
}

$announcementModel = new AnnouncementModel($db);
$pengumuman = $announcementModel->getAnnouncement();
$isiPengumuman = $pengumuman ? $pengumuman['isi'] : 'Belum ada pengumuman saat ini.';
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $isAdmin ? 'Beranda Admin - Study Club' : 'Beranda - Study Club' ?></title>
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
            <!-- Header Admin -->
            <li><a href="home.php" class="current" aria-current="page">Home</a></li>
            <li><a href="../view/course.php">Course</a></li>
            <li><a href="../view/schedule.php">Schedule</a></li>
            <li><a href="../view/pengajuan-subscription.php">Pengajuan</a></li>
            <li><a href="../view/anggota-subscription.php">Subscription</a></li>
          <?php else: ?>
            <!-- Header User Biasa -->
            <li><a href="home.php" class="current" aria-current="page">Home</a></li>
            <li><a href="../view/course.php">Course</a></li>
            <li><a href="../view/schedule.php">Schedule</a></li>
          <?php endif; ?>
        </ul>
      </nav>

      <div class="actions">
        <?php if ($isAdmin): ?>
          <!-- Actions Khusus Admin -->
          <span class="role">Admin</span>
          <a href="../view/profil.php" class="avatar" aria-label="Profil admin"></a>
        <?php else: ?>
          <!-- Actions User Biasa -->
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
          <a href="profil.php" class="avatar" aria-label="Profil">
            <?php if (!empty($fotoHeader)): ?>
              <img src="<?= htmlspecialchars($fotoHeader, ENT_QUOTES, 'UTF-8') ?>" alt="Profil">
            <?php endif; ?>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <main>
    <?php if ($isAdmin): ?>
      <!-- ===================================== -->
      <!-- TAMPILAN KONTEN UTAMA KHUSUS ADMIN    -->
      <!-- ===================================== -->
      <div class="container admin-wrap">
        <!-- ===== PENGUMUMAN (ADMIN) ===== -->
        <section class="notice admin-notice" aria-label="Pengumuman">
          <svg class="notice-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 11v2a1 1 0 0 0 1 1h3l8 5V5L7 10H4a1 1 0 0 0-1 1z" />
            <path d="M18.5 9a4 4 0 0 1 0 6" />
            <path d="M7 14l1.2 5.2a1 1 0 0 0 1 .8h1.3a1 1 0 0 0 1-1.2L10.7 16" />
          </svg>

          <p class="notice-text" id="currentNoticeText"><?= nl2br(htmlspecialchars($isiPengumuman)) ?></p>

          <button type="button" class="edit-btn" aria-label="Edit pengumuman">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
              stroke-linejoin="round">
              <path d="M17 3l4 4L8 20l-5 1 1-5z" />
              <path d="M14.5 5.5l4 4" />
              <path d="M5 16l3 3" />
            </svg>
          </button>
        </section>

        <div class="admin-actions">
          <button type="button" class="btn-create">Buat pengumuman</button>
        </div>
      </div>

    <?php else: ?>
      <!-- ===================================== -->
      <!-- TAMPILAN KONTEN UTAMA KHUSUS MEMBER   -->
      <!-- ===================================== -->
      <!-- ===== BANNER / CAROUSEL ===== -->
      <section class="container carousel" aria-label="Banner promosi">
        <button class="arrow prev" aria-label="Sebelumnya">
          <svg viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="3" stroke-linecap="round"
            stroke-linejoin="round">
            <path d="M15 18l-6-6 6-6" />
          </svg>
        </button>

        <div class="slides">
          <div class="slide active">
            <div class="panel"><small>PROMO PREMIUM</small><strong>16%</strong><span>DISKON PAKET TAHUNAN</span></div>
            <div class="art"><img src="../public/img/logo.png" alt=""></div>
          </div>
          <div class="slide alt">
            <div class="panel"><small>KELAS BARU</small><strong>PBO</strong><span>JAVA + MYSQL</span></div>
            <div class="art"><img src="../public/img/logo.png" alt=""></div>
          </div>
        </div>

        <div class="dots"></div>

        <button class="arrow next" aria-label="Berikutnya">
          <svg viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="3" stroke-linecap="round"
            stroke-linejoin="round">
            <path d="M9 18l6-6-6-6" />
          </svg>
        </button>
      </section>

      <!-- ===== TEKS BERJALAN ===== -->
      <div class="ticker" aria-hidden="true">
        <span class="track">Pendaftaran anggota baru batch semester ini resmi dibuka. Pelajari Web Development, UI/UX,
          hingga Data Science langsung dari praktik dan proyek kolaboratif—terbuka untuk seluruh mahasiswa Informatika
          dari tingkat pemula hingga mahir.</span>
      </div>

      <div class="container">
        <!-- ===== PENGUMUMAN ===== -->
        <section class="notice" aria-label="Pengumuman">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
            stroke-linejoin="round">
            <path d="M3 11v2a1 1 0 0 0 1 1h3l8 5V5L7 10H4a1 1 0 0 0-1 1z" />
            <path d="M18.5 9a4 4 0 0 1 0 6" />
            <path d="M7 14l1.2 5.2a1 1 0 0 0 1 .8h1.3a1 1 0 0 0 1-1.2L10.7 16" />
          </svg>
          <p><?= nl2br(htmlspecialchars($isiPengumuman)) ?></p>
        </section>

        <!-- ===== TESTIMONI (2 x 2) ===== -->
        <section class="testi" aria-label="Testimoni">
          <article class="testi-card">
            <div class="testi-ava" aria-hidden="true">K</div>
            <p class="testi-text">Materi dan praktikumnya tersusun rapi, jadi belajar bareng di Study Club terasa jauh
              lebih terarah.</p>
            <h3 class="testi-name">Jerome Polin</h3>
          </article>

          <article class="testi-card">
            <div class="testi-ava" aria-hidden="true">Z</div>
            <p class="testi-text">Sesi tanya jawab dengan mentor sangat membantu waktu saya buntu mengerjakan tugas
              praktikum.</p>
            <h3 class="testi-name">Kanye West</h3>
          </article>

          <article class="testi-card">
            <div class="testi-ava" aria-hidden="true">F</div>
            <p class="testi-text">Jadwal kegiatan yang jelas bikin saya tidak pernah ketinggalan pertemuan dan materi
              baru.</p>
            <h3 class="testi-name">Windah Basudara</h3>
          </article>

          <article class="testi-card">
            <div class="testi-ava" aria-hidden="true">J</div>
            <p class="testi-text">Latihan soal dan pembahasannya membantu saya lebih siap menghadapi ujian di kelas.</p>
            <h3 class="testi-name">Joker</h3>
          </article>
        </section>
      </div>
    <?php endif; ?>
  </main>

  <?php if ($isAdmin): ?>
    <!-- ===== MODAL EDIT PENGUMUMAN (HANYA DIRENDER UNTUK ADMIN) ===== -->
    <div class="modal-overlay" id="editModal" hidden>
      <div class="modal" role="dialog" aria-modal="true" aria-labelledby="editTitle">
        <div class="modal-head">
          <h2 id="editTitle">Edit pengumuman</h2>
          <button type="button" class="modal-close" data-close aria-label="Tutup">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
              <path d="M6 6l12 12M18 6L6 18" />
            </svg>
          </button>
        </div>

        <form id="editForm" action="../control/prosesPengumuman.php" method="POST">
          <label for="editText" class="sr-only">Isi pengumuman</label>
          <textarea id="editText" name="isi" rows="8" required
            placeholder="Tulis pengumuman di sini..."><?= htmlspecialchars($isiPengumuman) ?></textarea>

          <div class="modal-foot">
            <button type="button" class="btn-ghost" data-close>Batal</button>
            <button type="submit" class="btn-create">Simpan</button>
          </div>
        </form>
      </div>
    </div>

    <script src="../public/js/home-admin.js"></script>
  <?php else: ?>
    <script src="../public/js/home.js"></script>
  <?php endif; ?>

  <script src="../public/js/notif.js"></script>
</body>

</html>