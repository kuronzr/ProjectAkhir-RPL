<?php
session_start();

require_once '../config/database.php';
require_once '../model/PengumumanModel.php';

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: ../view/home.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isi     = trim($_POST['isi'] ?? '');
    $adminId = $_SESSION['user_id'] ?? null;

    if (empty($isi)) {
        $_SESSION['error'] = "Isi pengumuman tidak boleh kosong!";
        header("Location: ../view/home.php");
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();
    $announcementModel = new AnnouncementModel($db);
    $announcementModel->saveAnnouncement($isi, $adminId);

    header("Location: ../view/home.php");
    exit;
} else {
    header("Location: ../view/home.php");
    exit;
}