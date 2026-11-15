<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('UPLOAD_DIR', __DIR__ . '/../public/uploads/');

// Fungsi untuk mengecek apakah pengguna sudah login
function checkAuth()
{
    if (!isset($_SESSION['user'])) {
        echo "<script>
            alert('Silakan login terlebih dahulu untuk mengakses fitur ini!');
            window.location.href = '../view/login.php';
        </script>";
        exit();
    }
}

// Fungsi tambahan untuk mengecek apakah pengguna memiliki role admin
function checkAdmin()
{
    checkAuth();
    if ($_SESSION['user']['role'] !== 'admin') {
        echo "<script>
            alert('Akses ditolak! Halaman ini khusus untuk Admin.');
            window.location.href = '../view/home.php';
        </script>";
        exit();
    }
}

// Helper untuk mengecek role admin tanpa redirect
function isAdmin()
{
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}
?>