<?php
session_start();
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
  header("Location: home.php");
  exit;
}

require_once '../config/database.php';
require_once '../model/SubscriptionModel.php';

$db = (new Database())->getConnection();
$subModel = new SubscriptionModel($db);
$rawMembers = $subModel->getAllMembersWithSubscription();

$formattedMembers = array_map(function ($row) {
  $isPremium = ($row['membership_status'] === 'premium');
  $jenis = $isPremium
    ? (!empty($row['paket_terakhir']) ? ucfirst($row['paket_terakhir']) : 'Premium')
    : 'Reguler';

  return [
    'id' => (int) $row['id'],
    'nama' => $row['nama'],
    'jenis' => $jenis,
    'isPremium' => $isPremium,
    'diblokir' => ((int) $row['is_banned'] === 1)
  ];
}, $rawMembers);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Anggota Subscription - Admin Study Club</title>
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
          <li><a href="pengajuan-subscription.php">Pengajuan</a></li>
          <li><a href="anggota-subscription.php" class="current" aria-current="page">Subscription</a></li>
        </ul>
      </nav>

      <div class="actions">
        <span class="role">Admin</span>
        <a href="profil.php" class="avatar" aria-label="Profil admin"></a>
      </div>
    </div>
  </header>

  <main class="container page">
    <h1>Anggota Subscription</h1>
    <p class="sub">Daftar seluruh anggota Study Club yang terdaftar. Anggota yang melanggar aturan dapat diblokir.</p>

    <!-- ===== FILTER ===== -->
    <div class="toolbar">
      <div class="filter">
        <label for="filter">Jenis Subs</label>
        <select id="filter">
          <option value="semua">Semua</option>
          <option value="Reguler">Reguler</option>
          <option value="Bulanan">Bulanan</option>
          <option value="Tahunan">Tahunan</option>
          <option value="Premium">Semua Premium</option>
        </select>
      </div>
      <span class="count" id="count">0 anggota</span>
    </div>

    <!-- ===== TABEL ===== -->
    <div class="table-box">
      <div class="table-scroll">
        <table class="tbl-anggota">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama</th>
              <th>Jenis Subs</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody id="rows" data-anggota="<?= htmlspecialchars(json_encode($formattedMembers), ENT_QUOTES, 'UTF-8') ?>">
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <div class="toast" id="toast" role="status"></div>

  <script src="../public/js/anggota-subscription.js"></script>
</body>

</html>