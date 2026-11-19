<?php
session_start();
if (!isset($_SESSION['role']) || !isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

require_once '../config/database.php';
require_once '../model/courseModel.php';
require_once '../model/SubscriptionModel.php';
require_once '../control/prosesProfil.php';

$db = (new Database())->getConnection();
$userId = (int) $_SESSION['user_id'];
$isAdmin = (strtolower($_SESSION['role']) === 'admin');
$fotoHeader = getFotoProfil($db);

$subModel = new SubscriptionModel($db);
if ($subModel->isUserBanned($userId)) {
  session_unset();
  session_destroy();
  header("Location: login.php");
  exit;
}
$isPremium = ($subModel->getUserMembership($userId) === 'premium');

$courseModel = new CourseModel($db);

$courseId = (int) ($_GET['id'] ?? 0);
$materiId = (int) ($_GET['materi'] ?? 0);
$praktikumId = (int) ($_GET['praktikum'] ?? 0);

$course = null;
$subMode = 'list';
$detail = null;
$submissions = [];

if ($courseId > 0) {
  $course = $courseModel->getCourseById($courseId);
  if (!$course) {
    header("Location: course.php");
    exit;
  }

  if ($materiId > 0) {
    $subMode = 'materi';
    $detail = $courseModel->getMaterialById($materiId);

    if ($detail && !empty($detail['is_premium']) && !$isAdmin && !$isPremium) {
      header("Location: premium.php");
      exit;
    }
  } elseif ($praktikumId > 0) {
    $subMode = 'praktikum';
    $detail = $courseModel->getPracticalById($praktikumId);

    $mySubmission = null;
    if ($detail && $isAdmin) {
      $stmtSub = $db->prepare("
      SELECT ps.id, ps.file_tugas, ps.submitted_at, u.nama, u.email
      FROM practical_submissions ps
      JOIN users u ON ps.user_id = u.id
      WHERE ps.practical_id = :pid
      ORDER BY ps.submitted_at DESC
    ");
      $stmtSub->execute([':pid' => $praktikumId]);
      $submissions = $stmtSub->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($detail && !$isAdmin) {
      $stmtMy = $db->prepare("
      SELECT id, file_tugas, submitted_at
      FROM practical_submissions
      WHERE practical_id = :pid AND user_id = :uid
      LIMIT 1
    ");
      $stmtMy->execute([':pid' => $praktikumId, ':uid' => $userId]);
      $mySubmission = $stmtMy->fetch(PDO::FETCH_ASSOC);
    }
  } else {
    $listPraktikum = $courseModel->getPracticalsByCourse($courseId);
    $listMateri = $courseModel->getMaterialsByCourse($courseId);
  }
} else {
  $courses = $courseModel->getAllCourses();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>
    <?php
    if ($course)
      echo htmlspecialchars($course['nama_kursus']) . ' - Study Club';
    else
      echo $isAdmin ? 'Course Admin - Study Club' : 'Course - Study Club';
    ?>
  </title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../public/CSS/style.css?v=8">
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
            <li><a href="course.php" class="current" aria-current="page">Course</a></li>
            <li><a href="schedule.php">Schedule</a></li>
            <li><a href="pengajuan-subscription.php">Pengajuan</a></li>
            <li><a href="anggota-subscription.php">Subscription</a></li>
          <?php else: ?>
            <li><a href="home.php">Home</a></li>
            <li><a href="course.php" class="current" aria-current="page">Course</a></li>
            <li><a href="schedule.php">Schedule</a></li>
          <?php endif; ?>
        </ul>
      </nav>

      <div class="actions">
        <?php if ($isAdmin): ?>
          <span class="role">Admin</span>
          <a href="profil.php" class="avatar" aria-label="Profil admin">
            <?php if (!empty($fotoHeader)): ?>
              <img src="<?= htmlspecialchars($fotoHeader, ENT_QUOTES, 'UTF-8') ?>" alt="Profil">
            <?php endif; ?>
          </a>
        <?php else: ?>
          <?php if ($isPremium): ?>
            <span class="btn-premium static">Premium</span>
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

  <?php if (!$course): ?>
    <!-- ============================================================ -->
    <!-- TAMPILAN UTAMA: DAFTAR KARTU COURSE                          -->
    <!-- ============================================================ -->
    <main class="container">
      <div class="c-head-row">
        <h1 class="page-title">Course <?= $isAdmin ? '<span class="c-admin-badge">Admin</span>' : '' ?></h1>

        <?php if ($isAdmin): ?>
          <button type="button" class="c-btn solid auto-w" id="btnTambahCourse">+ Tambah Course</button>
        <?php endif; ?>
      </div>

      <!-- ===== TOOLBAR ===== -->
      <div class="c-toolbar">
        <select id="urut" aria-label="Urutkan course">
          <option value="nama">Urut berdasarkan nama kursus</option>
        </select>

        <label class="search" for="cari">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
            stroke-linejoin="round">
            <circle cx="11" cy="11" r="7" />
            <path d="M20 20l-3.5-3.5" />
          </svg>
          <input type="search" id="cari" placeholder="Cari">
        </label>

        <div class="views" role="group" aria-label="Tampilan">
          <button type="button" class="view-btn active" data-view="grid" aria-label="Tampilan kartu" aria-pressed="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round">
              <rect x="3" y="3" width="7" height="7" rx="2" />
              <rect x="14" y="3" width="7" height="7" rx="2" />
              <rect x="3" y="14" width="7" height="7" rx="2" />
              <rect x="14" y="14" width="7" height="7" rx="2" />
            </svg>
          </button>
          <button type="button" class="view-btn" data-view="list" aria-label="Tampilan daftar" aria-pressed="false">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
              <path d="M9 6h12M9 12h12M9 18h12" />
              <circle cx="4.5" cy="6" r="1" />
              <circle cx="4.5" cy="12" r="1" />
              <circle cx="4.5" cy="18" r="1" />
            </svg>
          </button>
        </div>
      </div>

      <section id="courses" class="courses grid" aria-live="polite" data-admin="<?= $isAdmin ? '1' : '0' ?>"
        data-courses="<?= htmlspecialchars(json_encode($courses), ENT_QUOTES, 'UTF-8') ?>"></section>
    </main>

    <?php if ($isAdmin): ?>
      <!-- ===== MODAL TAMBAH COURSE ===== -->
      <div class="modal-overlay" id="modalTambahCourse" hidden>
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="tambahCourseTitle">
          <div class="modal-head">
            <h2 id="tambahCourseTitle">Tambah Course</h2>
            <button type="button" class="modal-close" data-close-course aria-label="Tutup">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M6 6l12 12M18 6L6 18" />
              </svg>
            </button>
          </div>

          <form action="../control/prosesCourse.php" method="POST">
            <input type="hidden" name="action" value="create">

            <label class="a-label" for="nama_kursus">Nama Kursus</label>
            <input class="a-input" type="text" id="nama_kursus" name="nama_kursus" maxlength="100" required
              placeholder="Contoh: WEB DEVELOPMENT">

            <label class="a-label" for="kategori">Kategori / Prodi</label>
            <input class="a-input" type="text" id="kategori" name="kategori" maxlength="50" required
              placeholder="Contoh: Programming">

            <label class="a-label" for="tema_banner">Pola Banner</label>
            <select class="a-input" id="tema_banner" name="tema_banner">
              <option value="p-plaid">Plaid (Biru Kotak)</option>
              <option value="p-navy">Navy (Biru Gelap)</option>
              <option value="p-hex">Hexagon</option>
              <option value="p-diamond">Diamond</option>
            </select>

            <label class="a-label" for="deskripsi">Deskripsi</label>
            <textarea class="a-input" id="deskripsi" name="deskripsi" rows="3"
              placeholder="Deskripsi singkat kursus..."></textarea>

            <div class="modal-foot">
              <button type="button" class="btn-ghost" data-close-course>Batal</button>
              <button type="submit" class="btn-create">Simpan</button>
            </div>
          </form>
        </div>
      </div>
    <?php endif; ?>

  <?php elseif ($subMode === 'list'): ?>
    <!-- ============================================================ -->
    <!-- TAMPILAN ISI KURSUS: BANNER, TAB PRAKTIKUM & MATERI          -->
    <!-- ============================================================ -->
    <?php
    $tema = $course['tema_banner'] ?? 'navy';
    $coverClass = str_starts_with($tema, 'p-') ? $tema : 'p-' . $tema;
    ?>
    <main class="container">
      <section class="k-banner" aria-label="Banner kursus">
        <div class="k-cover <?= htmlspecialchars($coverClass) ?>">
          <span class="k-tag"><?= htmlspecialchars($course['kategori']) ?></span>
        </div>
      </section>

      <a class="k-back" href="course.php">&larr; Kembali ke Course</a>
      <h1 class="k-title"><?= htmlspecialchars($course['nama_kursus']) ?></h1>

      <div class="k-tabs z-top" role="group" aria-label="Jenis kegiatan">
        <button type="button" class="k-tab active" data-tab="praktikum" aria-pressed="true">Praktikum</button>
        <button type="button" class="k-tab" data-tab="materi" aria-pressed="false">Materi</button>
      </div>

      <!-- DAFTAR PRAKTIKUM + TOMBOL TAMBAH PRAKTIKUM -->
      <div id="wrap-praktikum">
        <ul class="k-list" id="list-praktikum">
          <?php if (empty($listPraktikum)): ?>
            <li class="k-empty">Belum ada praktikum untuk kursus ini.</li>
          <?php else: ?>
            <?php foreach ($listPraktikum as $p): ?>
              <li>
                <a href="course.php?id=<?= $courseId ?>&praktikum=<?= $p['id'] ?>" class="k-item k-link">
                  <span class="k-ava"></span>
                  <div>
                    <div class="k-name"><?= htmlspecialchars($p['judul']) ?></div>
                    <div class="k-date"><?= date('d M Y', strtotime($p['tanggal'])) ?></div>
                  </div>
                </a>
              </li>
            <?php endforeach; ?>
          <?php endif; ?>
        </ul>

        <?php if ($isAdmin): ?>
          <div class="admin-actions mt-16">
            <a href="form-admin.php?course=<?= $courseId ?>&type=praktikum" class="btn-create block-link">
              + Tambah Praktikum
            </a>
          </div>
        <?php endif; ?>
      </div>

      <!-- DAFTAR MATERI + TOMBOL TAMBAH MATERI -->
      <div id="wrap-materi" hidden>
        <ul class="k-list" id="list-materi">
          <?php if (empty($listMateri)): ?>
            <li class="k-empty">Belum ada materi untuk kursus ini.</li>
          <?php else: ?>
            <?php foreach ($listMateri as $m): ?>
              <?php
              $isMatPremium = !empty($m['is_premium']);
              $isLocked = ($isMatPremium && !$isAdmin && !$isPremium);
              ?>
              <li>
                <?php if ($isLocked): ?>
                  <a href="premium.php" class="k-item locked" title="Khusus anggota Premium">
                    <span class="k-ava"></span>
                    <div>
                      <div class="k-name"><?= htmlspecialchars($m['judul']) ?></div>
                      <div class="k-date"><?= date('d M Y', strtotime($m['tanggal'])) ?></div>
                    </div>
                    <span class="k-lock" aria-label="Terkunci">
                      <svg viewBox="0 0 24 24" fill="currentColor">
                        <path
                          d="M12 2a5 5 0 0 0-5 5v3H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2h-1V7a5 5 0 0 0-5-5zm-3 5a3 3 0 0 1 6 0v3H9V7zm3 7a1.5 1.5 0 0 1 .75 2.8V18h-1.5v-1.2A1.5 1.5 0 0 1 12 14z" />
                      </svg>
                    </span>
                  </a>
                <?php else: ?>
                  <a href="course.php?id=<?= $courseId ?>&materi=<?= $m['id'] ?>" class="k-item k-link">
                    <span class="k-ava"></span>
                    <div>
                      <div class="k-name"><?= htmlspecialchars($m['judul']) ?></div>
                      <div class="k-date"><?= date('d M Y', strtotime($m['tanggal'])) ?></div>
                    </div>
                    <?php if ($isMatPremium): ?>
                      <span class="k-badge-premium">Premium</span>
                    <?php endif; ?>
                  </a>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
          <?php endif; ?>
        </ul>

        <?php if ($isAdmin): ?>
          <div class="admin-actions mt-16">
            <a href="form-admin.php?course=<?= $courseId ?>&type=materi" class="btn-create block-link">
              + Tambah Materi
            </a>
          </div>
        <?php endif; ?>
      </div>
    </main>

  <?php elseif ($subMode === 'materi'): ?>
    <!-- ============================================================ -->
    <!-- TAMPILAN DETAIL MATERI (IKLAN REGULER & DOWNLOAD PREMIUM)     -->
    <!-- ============================================================ -->
    <main class="container m-page">
      <a class="m-back" href="course.php?id=<?= $courseId ?>">&larr; Kembali</a>

      <?php if (!$detail): ?>
        <p class="m-empty">Materi tidak ditemukan.</p>
      <?php else: ?>
        <div class="m-head">
          <span class="m-ava"></span>
          <div>
            <h1 class="m-title"><?= htmlspecialchars($detail['judul']) ?></h1>
            <div class="m-date"><?= date('d F Y', strtotime($detail['tanggal'])) ?></div>
          </div>
        </div>

        <div id="isi">
          <?php if (!empty($detail['video_url'])): ?>
            <?php $showAd = (!$isAdmin && !$isPremium); ?>
            <div class="m-video" id="video-wrapper" data-show-ad="<?= $showAd ? '1' : '0' ?>"
              data-ad-src="../public/video/INI%20CRUSIAL.mp4"
              data-main-src="<?= htmlspecialchars($detail['video_url'], ENT_QUOTES, 'UTF-8') ?>">

              <div class="m-video-box">
                <video id="materi-video" <?= $showAd ? '' : 'controls' ?> controlsList="nodownload"
                  oncontextmenu="return false;" preload="metadata" playsinline
                  src="<?= $showAd ? '../public/video/INI%20CRUSIAL.mp4' : htmlspecialchars($detail['video_url'], ENT_QUOTES, 'UTF-8') ?>"></video>

                <?php if ($showAd): ?>
                  <div id="ad-overlay" class="ad-overlay">
                    <div class="ad-row">
                      <span id="ad-badge" class="ad-badge">Iklan · Bebas iklan dengan Premium</span>
                      <a href="premium.php" class="ad-upgrade-btn">Upgrade</a>
                    </div>

                    <div class="ad-row">
                      <button type="button" id="btn-ad-play" class="ad-ctrl-btn">▶ Putar Iklan</button>
                      <button type="button" id="btn-skip-ad" class="ad-ctrl-btn" disabled>Lewati dalam 5d</button>
                    </div>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>

          <?php if (!empty($detail['file_modul'])): ?>
            <div class="m-center">
              <?php if ($isAdmin || $isPremium): ?>
                <a class="m-unduh" href="../control/download.php?materi=<?= $detail['id'] ?>">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M12 3v12" />
                    <path d="M7 11l5 5 5-5" />
                    <path d="M5 21h14" />
                  </svg>
                  Unduh
                </a>
              <?php else: ?>
                <a class="m-unduh locked" href="premium.php" title="Upgrade ke Premium untuk mengunduh modul">
                  <svg viewBox="0 0 24 24" fill="currentColor">
                    <path
                      d="M12 2a5 5 0 0 0-5 5v3H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2h-1V7a5 5 0 0 0-5-5zm-3 5a3 3 0 0 1 6 0v3H9V7zm3 7a1.5 1.5 0 0 1 .75 2.8V18h-1.5v-1.2A1.5 1.5 0 0 1 12 14z" />
                  </svg>
                  Unduh (Premium)
                </a>
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <div class="m-tabs z-top" role="group" aria-label="Isi materi">
            <button type="button" class="m-tab active" data-tab="deskripsi" aria-pressed="true">Deskripsi</button>
            <button type="button" class="m-tab" data-tab="modul" aria-pressed="false">Modul</button>
          </div>

          <section class="m-panel" id="panel-deskripsi">
            <p><?= nl2br(htmlspecialchars($detail['deskripsi'])) ?></p>
          </section>

          <section class="m-panel m-panel-modul" id="panel-modul" hidden>
            <?php if (!empty($detail['file_modul'])): ?>
              <?php
              $modulPath = "../public/uploads/modul/" . htmlspecialchars($detail['file_modul']);
              $extModul = strtolower(pathinfo($detail['file_modul'], PATHINFO_EXTENSION));
              ?>
              <div class="m-modul-bar">
                <span class="m-modul-label">
                  File Modul: <?= htmlspecialchars($detail['file_modul']) ?>
                </span>

                <?php if ($isAdmin || $isPremium): ?>
                  <a class="m-unduh" href="../control/download.php?materi=<?= $detail['id'] ?>">
                    Unduh File Modul
                  </a>
                <?php else: ?>
                  <a class="m-unduh locked" href="premium.php" title="Khusus anggota Premium">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                      <path
                        d="M12 2a5 5 0 0 0-5 5v3H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2h-1V7a5 5 0 0 0-5-5zm-3 5a3 3 0 0 1 6 0v3H9V7zm3 7a1.5 1.5 0 0 1 .75 2.8V18h-1.5v-1.2A1.5 1.5 0 0 1 12 14z" />
                    </svg>
                    Unduh Khusus Premium
                  </a>
                <?php endif; ?>
              </div>

              <?php if ($extModul === 'pdf'): ?>
                <?php if ($isAdmin || $isPremium): ?>
                  <iframe class="m-pdf-frame" src="<?= $modulPath ?>"></iframe>
                <?php else: ?>
                  <iframe class="m-pdf-frame" src="<?= $modulPath ?>#toolbar=0"></iframe>
                <?php endif; ?>
              <?php endif; ?>
            <?php else: ?>
              <p class="m-empty">Belum ada file modul yang diunggah.</p>
            <?php endif; ?>
          </section>
        </div>
      <?php endif; ?>
    </main>

  <?php elseif ($subMode === 'praktikum'): ?>
    <!-- ============================================================ -->
    <!-- TAMPILAN DETAIL PRAKTIKUM                                    -->
    <!-- ============================================================ -->
    <main class="container m-page">
      <a class="m-back" href="course.php?id=<?= $courseId ?>">&larr; Kembali</a>

      <?php if (!$detail): ?>
        <p class="m-empty">Praktikum tidak ditemukan.</p>
      <?php else: ?>
        <div class="m-head">
          <span class="m-ava"></span>
          <div>
            <h1 class="m-title"><?= htmlspecialchars($detail['judul']) ?></h1>
            <div class="m-date"><?= date('d F Y', strtotime($detail['tanggal'])) ?></div>
          </div>
        </div>

        <div id="isi">
          <section class="p-box">
            <p class="p-instruksi"><?= nl2br(htmlspecialchars($detail['instruksi'])) ?></p>
          </section>

          <section class="p-box">
            <h2 class="p-sub">Referensi dan Link GitHub</h2>
            <ul class="m-modul">
              <?php
              $links = array_filter(explode("\n", $detail['referensi_link'] ?? ''));
              foreach ($links as $link):
                $cleanLink = trim($link);
                ?>
                <li><a href="<?= htmlspecialchars($cleanLink) ?>" target="_blank"
                    rel="noopener"><?= htmlspecialchars($cleanLink) ?></a></li>
              <?php endforeach; ?>
            </ul>
          </section>

          <?php if ($isAdmin): ?>
            <section class="p-box">
              <h2 class="p-sub">Daftar Tugas yang Dikumpulkan Mahasiswa (<?= count($submissions) ?>)</h2>

              <?php if (empty($submissions)): ?>
                <p class="m-empty">Belum ada mahasiswa yang mengumpulkan tugas praktikum ini.</p>
              <?php else: ?>
                <div class="table-box">
                  <div class="table-scroll">
                    <table>
                      <thead>
                        <tr>
                          <th>No</th>
                          <th>Nama Mahasiswa</th>
                          <th>Email</th>
                          <th>Waktu Kumpul</th>
                          <th>File Tugas</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($submissions as $idx => $sub): ?>
                          <tr>
                            <td><?= $idx + 1 ?></td>
                            <td><?= htmlspecialchars($sub['nama']) ?></td>
                            <td><?= htmlspecialchars($sub['email']) ?></td>
                            <td><?= date('d M Y, H:i', strtotime($sub['submitted_at'])) ?></td>
                            <td>
                              <a class="c-btn solid" href="../public/uploads/tugas/<?= rawurlencode($sub['file_tugas']) ?>"
                                target="_blank" rel="noopener">
                                Lihat / Unduh File
                              </a>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              <?php endif; ?>
            </section>
          <?php else: ?>
            <?php if (!empty($_SESSION['upload_msg'])): ?>
              <p class="p-status ok" role="status">
                <?= htmlspecialchars($_SESSION['upload_msg']);
                unset($_SESSION['upload_msg']); ?>
              </p>
            <?php endif; ?>

            <?php if ($mySubmission): ?>
              <?php
              $fileTugas = $mySubmission['file_tugas'];
              $fileUrl = '../public/uploads/tugas/' . rawurlencode($fileTugas);
              $extTugas = strtolower(pathinfo($fileTugas, PATHINFO_EXTENSION));
              ?>
              <section class="p-box p-submitted-box">
                <div class="p-sub-head">
                  <div>
                    <h2 class="p-sub-title">Tugas Sudah Dikumpulkan</h2>
                    <p class="p-sub-meta">
                      Dikumpulkan pada <?= date('d M Y, H:i', strtotime($mySubmission['submitted_at'])) ?>
                    </p>
                  </div>

                  <form action="../control/submit_praktikum.php" method="POST"
                    onsubmit="return confirm('Batalkan dan hapus tugas yang sudah diunggah?');">
                    <input type="hidden" name="action" value="cancel">
                    <input type="hidden" name="course_id" value="<?= $courseId ?>">
                    <input type="hidden" name="practical_id" value="<?= $detail['id'] ?>">
                    <button type="submit" class="p-cancel-x" title="Batalkan pengumpulan" aria-label="Batalkan pengumpulan">
                      &times;
                    </button>
                  </form>
                </div>

                <div class="p-file-card">
                  <span class="p-file-name"><?= htmlspecialchars($fileTugas) ?></span>
                  <a href="<?= $fileUrl ?>" target="_blank" rel="noopener" class="c-btn solid">
                    Buka / Unduh
                  </a>
                </div>

                <?php if ($extTugas === 'pdf'): ?>
                  <iframe class="m-pdf-frame mt-12" src="<?= $fileUrl ?>"></iframe>
                <?php else: ?>
                  <p class="p-docx-note">File DOCX tersimpan. Klik <strong>Buka / Unduh</strong> untuk memeriksa dokumen.</p>
                <?php endif; ?>
              </section>
            <?php else: ?>
              <form action="../control/submit_praktikum.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload">
                <input type="hidden" name="course_id" value="<?= $courseId ?>">
                <input type="hidden" name="practical_id" value="<?= $detail['id'] ?>">
                <input type="file" id="berkas" name="berkas" accept=".pdf,.docx" required hidden>

                <button type="button" class="p-drop" id="drop" aria-describedby="info">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                    <path d="M12 16v-5" />
                    <path d="M9.5 13l2.5-2.5 2.5 2.5" />
                  </svg>
                  <span class="p-drop-title" id="drop-title">Pilih file di sini</span>
                  <span class="p-drop-info" id="info">pdf, docx, maks 10MB</span>
                </button>

                <button type="submit" class="p-kirim">Unggah Tugas</button>
              </form>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </main>
  <?php endif; ?>

  <script src="../public/js/course.js"></script>
  <script src="../public/js/notif.js"></script>
</body>

</html>