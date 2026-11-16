<?php
session_start();

require_once '../config/database.php';
require_once '../model/UserModel.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama             = trim($_POST['nama'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($nama) || empty($email) || empty($password)) {
        $_SESSION['error'] = "Semua kolom wajib diisi!";
        header("Location: ../view/register.php");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Format email tidak valid!";
        header("Location: ../view/register.php");
        exit;
    }

    if (isset($_POST['confirm_password']) && $password !== $confirm_password) {
        $_SESSION['error'] = "Konfirmasi password tidak cocok!";
        header("Location: ../view/register.php");
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();
    $userModel = new UserModel($db);

    if ($userModel->getUserByEmail($email)) {
        $_SESSION['error'] = "Email sudah terdaftar! Silakan gunakan email lain atau login.";
        header("Location: ../view/register.php");
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    if ($userModel->createUser($nama, $email, $hashedPassword, 'member')) {
        $_SESSION['success'] = "Registrasi berhasil! Silakan login.";
        header("Location: ../view/login.php");
        exit;
    } else {
        $_SESSION['error'] = "Terjadi kesalahan sistem saat mendaftar.";
        header("Location: ../view/register.php");
        exit;
    }
} else {
    header("Location: ../view/register.php");
    exit;
}