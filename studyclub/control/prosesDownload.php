<?php
session_start();
if (!isset($_SESSION['role']) || !isset($_SESSION['user_id'])) {
    header("Location: ../view/login.php");
    exit;
}

require_once '../config/database.php';
require_once '../model/courseModel.php';
require_once '../model/SubscriptionModel.php';

$db = (new Database())->getConnection();
$userId = (int) $_SESSION['user_id'];
$isAdmin = (strtolower($_SESSION['role']) === 'admin');

$subModel = new SubscriptionModel($db);
if ($subModel->isUserBanned($userId)) {
    session_unset();
    session_destroy();
    header("Location: ../view/login.php");
    exit;
}

$isPremium = ($subModel->getUserMembership($userId) === 'premium');

if (!$isAdmin && !$isPremium) {
    header("Location: ../view/premium.php");
    exit;
}

$materiId = (int) ($_GET['materi'] ?? 0);
$type = $_GET['type'] ?? 'modul';

if ($materiId <= 0) {
    header("Location: ../view/course.php");
    exit;
}

$courseModel = new CourseModel($db);
$materi = $courseModel->getMaterialById($materiId);

if (!$materi) {
    header("Location: ../view/course.php");
    exit;
}

if ($type === 'video') {
    if (empty($materi['video_url'])) {
        header("Location: ../view/course.php");
        exit;
    }
    $filename = basename($materi['video_url']);
    $filePath = "../public/uploads/video/" . $filename;
} else {
    if (empty($materi['file_modul'])) {
        header("Location: ../view/course.php");
        exit;
    }
    $filename = basename($materi['file_modul']);
    $filePath = "../public/uploads/modul/" . $filename;
}

if (!file_exists($filePath)) {
    http_response_code(404);
    echo "File tidak ditemukan di server.";
    exit;
}

$mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
header('Content-Description: File Transfer');
header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: must-revalidate');
header('Pragma: public');

readfile($filePath);
exit;