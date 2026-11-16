<?php
session_start();
require_once '../config/database.php';
require_once '../model/ScheduleModel.php';

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: ../view/schedule.php");
    exit;
}

$db = (new Database())->getConnection();
$scheduleModel = new ScheduleModel($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $nama      = trim($_POST['nama'] ?? '');
    $kategori  = trim($_POST['kategori'] ?? '');
    $tanggal   = $_POST['tanggal'] ?? '';
    $mulai     = $_POST['mulai'] ?? '';
    $selesai   = $_POST['selesai'] ?? '';
    $lokasi    = trim($_POST['lokasi'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if (!empty($nama) && !empty($kategori) && !empty($tanggal) && !empty($mulai) && !empty($selesai)) {
        $scheduleModel->createSchedule($nama, $kategori, $tanggal, $mulai, $selesai, $lokasi, $deskripsi);
    }
    header("Location: ../view/schedule.php");
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $scheduleModel->deleteSchedule($id);
    }
    header("Location: ../view/schedule.php");
    exit;
}

header("Location: ../view/schedule.php");
exit;