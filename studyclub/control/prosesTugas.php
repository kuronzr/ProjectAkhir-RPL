<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../view/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../view/course.php");
    exit;
}

require_once '../config/database.php';

$db = (new Database())->getConnection();
$userId = (int) $_SESSION['user_id'];
$courseId = (int) ($_POST['course_id'] ?? 0);
$practicalId = (int) ($_POST['practical_id'] ?? 0);
$action = $_POST['action'] ?? 'upload';

$redirectUrl = "../view/course.php?id={$courseId}&praktikum={$practicalId}";

if ($courseId <= 0 || $practicalId <= 0) {
    header("Location: ../view/course.php");
    exit;
}

$uploadDir = __DIR__ . '/../public/uploads/tugas/';

// =====================================================================
// AKSI CANCEL / BATALKAN PENGUMPULAN TUGAS
// =====================================================================
if ($action === 'cancel') {
    try {
        $stmtCheck = $db->prepare("SELECT id, file_tugas FROM practical_submissions WHERE practical_id = :pid AND user_id = :uid LIMIT 1");
        $stmtCheck->execute([':pid' => $practicalId, ':uid' => $userId]);
        $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            if (!empty($existing['file_tugas']) && file_exists($uploadDir . $existing['file_tugas'])) {
                @unlink($uploadDir . $existing['file_tugas']);
            }
            $stmtDel = $db->prepare("DELETE FROM practical_submissions WHERE id = :id");
            $stmtDel->execute([':id' => $existing['id']]);
        }

        $_SESSION['upload_msg'] = 'Pengumpulan tugas dibatalkan.';
    } catch (PDOException $e) {
        $_SESSION['upload_msg'] = 'Gagal membatalkan pengumpulan tugas.';
    }

    header("Location: " . $redirectUrl);
    exit;
}

// =====================================================================
// AKSI UPLOAD TUGAS
// =====================================================================
if (!isset($_FILES['berkas']) || $_FILES['berkas']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['upload_msg'] = 'Silakan pilih file tugas terlebih dahulu.';
    header("Location: " . $redirectUrl);
    exit;
}

$file = $_FILES['berkas'];
$maxSize = 10 * 1024 * 1024; // 10 MB

if ($file['size'] > $maxSize) {
    $_SESSION['upload_msg'] = 'Ukuran file melebihi batas maksimal 10MB.';
    header("Location: " . $redirectUrl);
    exit;
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowedExt = ['pdf', 'docx'];

if (!in_array($ext, $allowedExt, true)) {
    $_SESSION['upload_msg'] = 'Format file harus PDF atau DOCX.';
    header("Location: " . $redirectUrl);
    exit;
}

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$newFileName = 'tugas_' . $practicalId . '_' . $userId . '_' . time() . '.' . $ext;
$destPath = $uploadDir . $newFileName;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    $_SESSION['upload_msg'] = 'Gagal menyimpan file tugas ke server.';
    header("Location: " . $redirectUrl);
    exit;
}

try {
    $stmtCheck = $db->prepare("SELECT id, file_tugas FROM practical_submissions WHERE practical_id = :pid AND user_id = :uid LIMIT 1");
    $stmtCheck->execute([':pid' => $practicalId, ':uid' => $userId]);
    $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        if (!empty($existing['file_tugas']) && file_exists($uploadDir . $existing['file_tugas'])) {
            @unlink($uploadDir . $existing['file_tugas']);
        }
        $stmtUpd = $db->prepare("UPDATE practical_submissions SET file_tugas = :file, submitted_at = NOW() WHERE id = :id");
        $stmtUpd->execute([
            ':file' => $newFileName,
            ':id' => $existing['id']
        ]);
    } else {
        $stmtIns = $db->prepare("INSERT INTO practical_submissions (practical_id, user_id, file_tugas) VALUES (:pid, :uid, :file)");
        $stmtIns->execute([
            ':pid' => $practicalId,
            ':uid' => $userId,
            ':file' => $newFileName
        ]);
    }

    $_SESSION['upload_msg'] = 'Tugas berhasil diunggah!';
    header("Location: " . $redirectUrl);
    exit;
} catch (PDOException $e) {
    if (file_exists($destPath)) {
        @unlink($destPath);
    }
    $_SESSION['upload_msg'] = 'Gagal menyimpan data ke database.';
    header("Location: " . $redirectUrl);
    exit;
}