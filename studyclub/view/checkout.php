<?php
session_start();
require_once '../config/database.php';
require_once '../model/SubscriptionModel.php';
require_once '../control/prosesProfil.php';

if (!isset($_SESSION['role'])) {
  header("Location: login.php");
  exit;
}

$db = (new Database())->getConnection();
$subModel = new SubscriptionModel($db);
$membership = $subModel->getUserMembership($_SESSION['user_id']);
$isPremium = ($membership === 'premium');
$namaDefault = $_SESSION['nama'] ?? '';
$fotoHeader = getFotoProfil($db);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pilih Paket Premium - Study Club</title>
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
        <a href="premium.php" class="btn-premium" id="header-status-btn">
          <?= $isPremium ? 'Premium' : 'Reguler' ?>
        </a>
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
      </div>
    </div>
  </header>

  <main class="container">
    <a class="back" href="premium.php">&larr; Kembali ke paket</a>
    <h1 class="page-title">Pilih Paket Premium</h1>

    <form id="checkout" class="layout" novalidate>
      <div class="left">

        <!-- Pilih paket -->
        <fieldset class="packs">
          <legend class="sr-only">Pilih paket</legend>
          <label class="pack">
            <input type="radio" name="paket" value="bulanan" checked>
            <span class="dot"></span>
            <span class="pack-name">Premium Bulanan</span>
            <span class="pack-price">Rp 159.000</span>
            <span class="pack-sub">per bulan</span>
          </label>
          <label class="pack">
            <input type="radio" name="paket" value="tahunan">
            <span class="dot"></span>
            <span class="pack-name">Premium Tahunan</span>
            <span class="pack-price">Rp 1.600.000</span>
            <span class="pack-sub">per tahun &middot; hemat 16%</span>
          </label>
        </fieldset>

        <!-- Detail pembayaran -->
        <section class="block">
          <h2>Detail Pembayaran</h2>
          <div class="field">
            <label for="nama">Nama Lengkap</label>
            <input type="text" id="nama" name="nama" autocomplete="name" value="<?= htmlspecialchars($namaDefault) ?>"
              placeholder="Nama sesuai identitas">
            <p class="err" data-for="nama"></p>
          </div>
          <div class="field">
            <label for="negara">Negara</label>
            <input type="text" id="negara" name="negara" autocomplete="country-name" value="Indonesia">
            <p class="err" data-for="negara"></p>
          </div>
          <div class="field">
            <label for="alamat">Alamat</label>
            <input type="text" id="alamat" name="alamat" autocomplete="street-address"
              placeholder="Jalan, kota, kode pos">
            <p class="err" data-for="alamat"></p>
          </div>
        </section>

        <!-- Metode pembayaran -->
        <section class="block">
          <h2>Metode Pembayaran</h2>
          <div class="methods">
            <label class="method">
              <input type="radio" name="metode" value="Gopay">
              <span class="m-icon gopay" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                  stroke-linejoin="round">
                  <rect x="3" y="6" width="18" height="13" rx="3" />
                  <path d="M3 10h18" />
                  <circle cx="16.5" cy="14.5" r="1" />
                </svg>
              </span>
              <span class="m-name">Gopay</span>
              <span class="dot"></span>
            </label>
            <label class="method">
              <input type="radio" name="metode" value="ShopeePay">
              <span class="m-icon shopee" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                  stroke-linejoin="round">
                  <rect x="3" y="6" width="18" height="13" rx="3" />
                  <path d="M3 10h18" />
                  <circle cx="16.5" cy="14.5" r="1" />
                </svg>
              </span>
              <span class="m-name">ShopeePay</span>
              <span class="dot"></span>
            </label>
          </div>
          <p class="err" data-for="metode"></p>
        </section>
      </div>

      <!-- Ringkasan -->
      <aside class="summary" aria-label="Ringkasan pesanan">
        <h2>Premium Plan</h2>
        <dl class="rows">
          <div>
            <dt id="s-label">Premium bulanan</dt>
            <dd id="s-harga">Rp 159.000</dd>
          </div>
          <div>
            <dt>Subtotal</dt>
            <dd id="s-sub">Rp 159.000</dd>
          </div>
          <div>
            <dt>Pajak</dt>
            <dd id="s-pajak">Rp 2.000</dd>
          </div>
          <div class="total">
            <dt>Total</dt>
            <dd id="s-total">Rp 161.000</dd>
          </div>
        </dl>

        <label class="terms">
          <input type="checkbox" id="terms">
          <span>Saya setuju dengan Syarat &amp; Ketentuan</span>
        </label>
        <p class="err" data-for="terms"></p>

        <button type="submit" class="btn-pay" id="btn-submit-pay">Berlangganan</button>
        <p class="secure">Pembayaran aman &middot; batalkan kapan saja</p>
      </aside>
    </form>
  </main>

  <!-- Dialog berhasil -->
  <div class="overlay" id="sukses" hidden>
    <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="sukses-judul">
      <div class="check" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
          stroke-linejoin="round">
          <path d="M5 12.5l4.5 4.5L19 7.5" />
        </svg>
      </div>
      <h2 id="sukses-judul">Pesanan diterima</h2>
      <p id="sukses-teks"></p>
      <a href="home.php" class="btn-pay">Kembali ke Beranda</a>
    </div>
  </div>

  <script src="../public/js/checkout.js"></script>
  <script src="../public/js/notif.js"></script>
</body>

</html>