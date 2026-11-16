<?php
session_start();
require_once '../config/database.php';
require_once '../model/SubscriptionModel.php';

if (!isset($_SESSION['role'])) {
  header("Location: login.php");
  exit;
}

$db = (new Database())->getConnection();
$subModel = new SubscriptionModel($db);
$membership = $subModel->getUserMembership($_SESSION['user_id']);
$isPremium = ($membership === 'premium');

if ($isPremium) {
    header("Location: home.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Premium - Study Club</title>
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
          <li><a href="course.php">Course</a></li>
          <li><a href="schedule.php">Schedule</a></li>
        </ul>
      </nav>

      <div class="actions">
        <a href="premium.php" class="btn-premium current" aria-current="page">
          <?= $isPremium ? 'Premium' : 'Reguler' ?>
        </a>
        <button class="icon-btn" aria-label="Notifikasi">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round">
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
            <path d="M13.7 21a2 2 0 0 1-3.4 0" />
          </svg>
        </button>
        <a href="profil.html" class="avatar" aria-label="Profil"></a>
      </div>
    </div>
  </header>

  <main class="container">
    <section class="intro">
      <span class="eyebrow">Study Club Premium</span>
      <h1>Belajar lebih cepat, tanpa batas</h1>
      <p>
        <?= $isPremium
          ? 'Status keanggotaanmu saat ini sudah <strong>Premium</strong>. Kamu dapat memperpanjang paket kapan saja.'
          : 'Pilih paket yang paling cocok untukmu. Bisa berhenti kapan saja.' ?>
      </p>
    </section>

    <section class="plans" aria-label="Pilihan paket">
      <!-- Bulanan -->
      <article class="plan plan-month">
        <h2>Perbulan</h2>
        <p class="price"><strong>Rp 159.000</strong> <span>/bulan</span></p>
        <a class="btn-sub" href="checkout.php?paket=bulanan">Langganan</a>

        <h3>Keuntungan</h3>
        <ul class="perks">
          <li>Akses semua materi dan praktikum</li>
          <li>Rekaman sesi belajar</li>
          <li>Latihan soal dan pembahasan</li>
          <li>Tanya jawab dengan mentor</li>
        </ul>
      </article>

      <!-- Tahunan -->
      <article class="plan plan-year">
        <span class="badge">Hemat Rp 308.000</span>
        <h2>Pertahun</h2>
        <p class="price"><strong>Rp 1.600.000</strong> <span>/tahun</span></p>
        <a class="btn-sub" href="checkout.php?paket=tahunan">Langganan</a>

        <h3>Keuntungan</h3>
        <ul class="perks">
          <li>Semua keuntungan paket bulanan</li>
          <li>Kelas dan materi baru gratis</li>
          <li>Sertifikat penyelesaian</li>
          <li>Prioritas dukungan mentor</li>
        </ul>
      </article>
    </section>

    <p class="note">Harga belum termasuk pajak. Pembayaran lewat Gopay atau ShopeePay.</p>
  </main>

  <script src="../public/js/notif.js"></script>
</body>

</html>