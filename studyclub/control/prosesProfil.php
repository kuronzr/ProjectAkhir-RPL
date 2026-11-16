<?php
session_start();
header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../model/UserModel.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit;
}

$userId = (int) $_SESSION['user_id'];
$nama = trim($_POST['nama'] ?? '');
$bio = trim($_POST['bio'] ?? '');
$foto = $_POST['foto'] ?? '';
$mapel = json_decode($_POST['mapel'] ?? '[]', true);

if (empty($nama)) {
    echo json_encode(['success' => false, 'message' => 'Nama tidak boleh kosong.']);
    exit;
}

if (empty($bio)) {
    $bio = 'Mahasiswa Informatika, Universitas Singaperbangsa Karawang.';
}

$db = (new Database())->getConnection();
$userModel = new UserModel($db);

if ($userModel->updateProfile($userId, $nama, $bio, $foto, is_array($mapel) ? $mapel : [])) {
    $_SESSION['nama'] = $nama;
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan profil ke database.']);
}
exit;