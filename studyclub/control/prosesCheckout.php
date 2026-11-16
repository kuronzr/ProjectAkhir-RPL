<?php
session_start();
header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../model/SubscriptionModel.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit;
}

$db = (new Database())->getConnection();
$subModel = new SubscriptionModel($db);
$action = $_POST['action'] ?? 'checkout';

if ($action === 'verifikasi') {
    if (strtolower($_SESSION['role'] ?? '') !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Hanya admin yang berwenang.']);
        exit;
    }

    $subId = (int) ($_POST['id'] ?? 0);
    $aksi = $_POST['aksi'] ?? ''; 

    if ($subId > 0 && in_array($aksi, ['diterima', 'ditolak'])) {
        $ok = $subModel->updateSubscriptionStatus($subId, $aksi);
        echo json_encode(['success' => $ok]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Data pengajuan tidak valid.']);
    }
    exit;
}

if ($action === 'toggle_ban') {
    if (strtolower($_SESSION['role'] ?? '') !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Hanya admin yang berwenang.']);
        exit;
    }

    $memberId = (int) ($_POST['id'] ?? 0);
    $aksi = $_POST['aksi'] ?? ''; 

    if ($memberId > 0 && in_array($aksi, ['ban', 'buka'])) {
        $isBanned = ($aksi === 'ban');
        $ok = $subModel->setMemberBanStatus($memberId, $isBanned);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Berhasil memperbarui status anggota.' : 'Gagal memperbarui status di database.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Data anggota tidak valid.']);
    }
    exit;
}

$userId = (int) $_SESSION['user_id'];
$paket = ($_POST['paket'] ?? '') === 'tahunan' ? 'tahunan' : 'bulanan';
$nama = trim($_POST['nama'] ?? '');
$negara = trim($_POST['negara'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');
$metode = trim($_POST['metode'] ?? '');

if (empty($nama) || empty($negara) || empty($alamat) || empty($metode)) {
    echo json_encode(['success' => false, 'message' => 'Semua kolom pembayaran wajib diisi.']);
    exit;
}

$harga = ($paket === 'tahunan') ? 1600000 : 159000;
$total = $harga + 2000;

if ($subModel->submitCheckout($userId, $paket, $nama, $negara, $alamat, $metode, $total)) {
    echo json_encode([
        'success' => true,
        'message' => 'Pengajuan berhasil dikirim! Menunggu verifikasi admin.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menyimpan pengajuan ke database.'
    ]);
}
exit;