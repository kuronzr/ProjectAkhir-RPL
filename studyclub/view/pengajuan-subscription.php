<?php
session_start();
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
  header("Location: home.php");
  exit;
}

require_once '../config/database.php';
require_once '../model/SubscriptionModel.php';
require_once '../control/prosesProfil.php';

$db = (new Database())->getConnection();
$fotoHeader = getFotoProfil($db);
$subModel = new SubscriptionModel($db);
$rawList = $subModel->getAllSubscriptions();

$statusMap = [
  'pending' => 'menunggu',
  'approved' => 'diterima',
  'rejected' => 'ditolak'
];

$formattedData = array_map(function ($row) use ($statusMap) {
  return [
    'id' => (int) $row['id'],
    'nama' => $row['nama_lengkap'],
    'jenis' => ucfirst($row['paket']),
    'bayar' => $row['metode_pembayaran'],
    'tgl' => $row['tgl'],
    'status' => $statusMap[$row['status']] ?? 'menunggu'
  ];
}, $rawList);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pengajuan Subscription - Admin Study Club</title>
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
          <li><a href="pengajuan-subscription.php" class="current" aria-current="page">Pengajuan</a></li>
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

  <main class="container page">
    <h1>Pengajuan Subscription</h1>
    <p class="sub">Tinjau pengajuan subscription study club yang masuk, lalu terima atau tolak.</p>

    <!-- ===== FILTER ===== -->
    <div class="toolbar">
      <div class="filter">
        <label for="filter">Status</label>
        <select id="filter">
          <option value="menunggu">Menunggu</option>
          <option value="diterima">Diterima</option>
          <option value="ditolak">Ditolak</option>
          <option value="semua">Semua</option>
        </select>
      </div>
      <span class="count" id="count">0 pengajuan</span>
    </div>

    <!-- ===== TABEL ===== -->
    <div class="table-box">
      <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th>Nomor</th>
              <th>Nama</th>
              <th>Jenis Subs</th>
              <th>Metode Pembayaran</th>
              <th>Tanggal</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody id="rows" data-pengajuan="<?= htmlspecialchars(json_encode($formattedData), ENT_QUOTES, 'UTF-8') ?>">
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <div class="toast" id="toast" role="status"></div>

  <script src="../public/js/pengajuan-subscription.js?"></script>
</body>

</html>