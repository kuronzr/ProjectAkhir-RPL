<?php
session_start();
require_once '../config/database.php';
require_once '../model/courseModel.php';

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: ../view/course.php");
    exit;
}

$db = (new Database())->getConnection();
$courseModel = new CourseModel($db);
$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
    $nama = trim($_POST['nama_kursus'] ?? '');
    $kat = trim($_POST['kategori'] ?? '');
    $tema = trim($_POST['tema_banner'] ?? 'p-navy');
    $desk = trim($_POST['deskripsi'] ?? '');

    if (!empty($nama) && !empty($kat)) {
        $courseModel->createCourse($nama, $kat, $tema, $desk);
    }
    header("Location: ../view/course.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create_praktikum') {
    $courseId = (int) ($_POST['course_id'] ?? 0);
    $judul = trim($_POST['judul'] ?? '');
    $instruksi = trim($_POST['deskripsi'] ?? '');
    $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
    $refLink = trim($_POST['referensi_link'] ?? '');

    if ($courseId > 0 && !empty($judul)) {
        $courseModel->createPractical($courseId, $judul, $tanggal, $instruksi, $refLink);
    }
    header("Location: ../view/course.php?id=" . $courseId);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create_materi') {
    $courseId = (int) ($_POST['course_id'] ?? 0);
    $judul = trim($_POST['judul'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $poinModul = trim($_POST['poin_modul'] ?? '');
    $isPremium = (int) ($_POST['is_premium'] ?? 0);
    $tanggal = date('Y-m-d');

    $maxModulSize = 10 * 1024 * 1024;  // 10 MB
    $maxVideoSize = 200 * 1024 * 1024; // 200 MB

    $fileModul = null;
    if (isset($_FILES['file_modul']) && $_FILES['file_modul']['error'] === UPLOAD_ERR_OK) {
        if ($_FILES['file_modul']['size'] <= $maxModulSize) {
            $dir = "../public/uploads/modul/";
            if (!is_dir($dir))
                mkdir($dir, 0777, true);
            $ext = pathinfo($_FILES['file_modul']['name'], PATHINFO_EXTENSION);
            $fileModul = "modul_{$courseId}_" . time() . "." . $ext;
            move_uploaded_file($_FILES['file_modul']['tmp_name'], $dir . $fileModul);
        }
    }

    $videoUrl = null;
    if (isset($_FILES['file_video']) && $_FILES['file_video']['error'] === UPLOAD_ERR_OK) {
        if ($_FILES['file_video']['size'] <= $maxVideoSize) {
            $dirVid = "../public/uploads/video/";
            if (!is_dir($dirVid))
                mkdir($dirVid, 0777, true);
            $extVid = pathinfo($_FILES['file_video']['name'], PATHINFO_EXTENSION);
            $vidName = "video_{$courseId}_" . time() . "." . $extVid;
            if (move_uploaded_file($_FILES['file_video']['tmp_name'], $dirVid . $vidName)) {
                $videoUrl = "../public/uploads/video/" . $vidName;
            }
        }
    }

    if ($courseId > 0 && !empty($judul)) {
        $courseModel->createMaterial($courseId, $judul, $tanggal, $videoUrl, $fileModul, $deskripsi, $poinModul, $isPremium);
    }
    header("Location: ../view/course.php?id=" . $courseId);
    exit;
}

if ($action === 'update_praktikum') {
    $id = (int) ($_POST['id'] ?? 0);
    $courseId = (int) ($_POST['course_id'] ?? 0);
    $judul = trim($_POST['judul'] ?? '');
    $instruksi = trim($_POST['deskripsi'] ?? '');
    $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
    $refLink = trim($_POST['referensi_link'] ?? '');

    $stmt = $db->prepare("UPDATE course_practicals SET judul = :j, tanggal = :t, instruksi = :i, referensi_link = :r WHERE id = :id");
    $stmt->execute([
        ':j'  => $judul,
        ':t'  => $tanggal,
        ':i'  => $instruksi,
        ':r'  => $refLink,
        ':id' => $id
    ]);

    header("Location: ../view/course.php?id={$courseId}&praktikum={$id}");
    exit;
}

if ($action === 'delete_praktikum') {
    $id = (int) ($_POST['id'] ?? 0);
    $courseId = (int) ($_POST['course_id'] ?? 0);

    $stmt = $db->prepare("DELETE FROM course_practicals WHERE id = :id");
    $stmt->execute([':id' => $id]);

    header("Location: ../view/course.php?id={$courseId}");
    exit;
}

if ($action === 'update_materi') {
    $id = (int) ($_POST['id'] ?? 0);
    $courseId = (int) ($_POST['course_id'] ?? 0);
    $judul = trim($_POST['judul'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $poinModul = trim($_POST['poin_modul'] ?? '');
    $isPremium = (int) ($_POST['is_premium'] ?? 0);

    $old = $courseModel->getMaterialById($id);
    $fileModul = $old['file_modul'] ?? null;
    $videoUrl = $old['video_url'] ?? null;

    if (isset($_FILES['file_modul']) && $_FILES['file_modul']['error'] === UPLOAD_ERR_OK) {
        $modulDir = __DIR__ . '/../public/uploads/modul/';
        if (!is_dir($modulDir)) mkdir($modulDir, 0777, true);
        if ($fileModul && file_exists($modulDir . $fileModul)) @unlink($modulDir . $fileModul);

        $fileModul = time() . '_' . basename($_FILES['file_modul']['name']);
        move_uploaded_file($_FILES['file_modul']['tmp_name'], $modulDir . $fileModul);
    }

    // Jika upload video baru
    if (isset($_FILES['file_video']) && $_FILES['file_video']['error'] === UPLOAD_ERR_OK) {
        $videoDir = __DIR__ . '/../public/uploads/video/';
        if (!is_dir($videoDir)) mkdir($videoDir, 0777, true);
        if ($videoUrl && file_exists(__DIR__ . '/' . $videoUrl)) @unlink(__DIR__ . '/' . $videoUrl);

        $newVideoName = time() . '_' . basename($_FILES['file_video']['name']);
        move_uploaded_file($_FILES['file_video']['tmp_name'], $videoDir . $newVideoName);
        $videoUrl = '../public/uploads/video/' . $newVideoName;
    }

    $stmt = $db->prepare("UPDATE course_materials SET judul = :j, deskripsi = :d, poin_modul = :p, is_premium = :prem, file_modul = :m, video_url = :v WHERE id = :id");
    $stmt->execute([
        ':j'    => $judul,
        ':d'    => $deskripsi,
        ':p'    => $poinModul,
        ':prem' => $isPremium,
        ':m'    => $fileModul,
        ':v'    => $videoUrl,
        ':id'   => $id
    ]);

    header("Location: ../view/course.php?id={$courseId}&materi={$id}");
    exit;
}

if ($action === 'delete_materi') {
    $id = (int) ($_POST['id'] ?? 0);
    $courseId = (int) ($_POST['course_id'] ?? 0);
    $old = $courseModel->getMaterialById($id);

    if ($old) {
        if (!empty($old['file_modul']) && file_exists(__DIR__ . '/../public/uploads/modul/' . $old['file_modul'])) {
            @unlink(__DIR__ . '/../public/uploads/modul/' . $old['file_modul']);
        }
        if (!empty($old['video_url']) && file_exists(__DIR__ . '/' . $old['video_url'])) {
            @unlink(__DIR__ . '/' . $old['video_url']);
        }
    }

    $stmt = $db->prepare("DELETE FROM course_materials WHERE id = :id");
    $stmt->execute([':id' => $id]);

    header("Location: ../view/course.php?id={$courseId}");
    exit;
}   

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    if ($id > 0) {
        $courseModel->deleteCourse($id);
    }
    header("Location: ../view/course.php");
    exit;
}

header("Location: ../view/course.php");
exit;