<?php
session_start();

require_once '../config/database.php';
require_once '../model/UserModel.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Email dan password wajib diisi!";
        header("Location: ../view/login.php");
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();
    $userModel = new UserModel($db);
    $user = $userModel->getUserByEmail($email);

    if ($user && password_verify($password, $user['password'])) {
        if (isset($user['is_banned']) && (int) $user['is_banned'] === 1) {
            $_SESSION['error'] = 'Maaf, akun Anda telah diblokir oleh Admin karena melanggar aturan.';
            header("Location: ../view/login.php");
            exit;
        }

        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['foto'] = $user['foto'] ?? '';

        header("Location: ../view/home.php");
        exit;
    } else {
        $_SESSION['error'] = "Maaf, email atau password salah!";
        header("Location: ../view/login.php");
        exit;
    }
} else {
    header("Location: ../view/login.php");
    exit;
}