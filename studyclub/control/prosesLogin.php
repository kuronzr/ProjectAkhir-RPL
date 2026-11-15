<?php
session_start();

require_once '../config/database.php'; // Sesuaikan path ke file koneksi database kamu
require_once '../model/UserModel.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validasi input kosong
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Email dan password wajib diisi!";
        header("Location: ../view/login.php");
        exit;
    }

    // Inisialisasi koneksi & model
    // Catatan: Sesuaikan baris $db jika file database.php kamu langsung menghasilkan variabel $pdo / $conn
    $database = new Database();
    $db = $database->getConnection();
    $userModel = new UserModel($db);

    // Cari user di database
    $user = $userModel->getUserByEmail($email);

    // Verifikasi user ditemukan dan password cocok
    if ($user && password_verify($password, $user['password'])) {
        // Regenerasi ID session untuk mencegah session fixation
        session_regenerate_id(true);

        // Simpan data ke session untuk role-based access di home.php
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama']    = $user['nama'];
        $_SESSION['email']   = $user['email'];
        $_SESSION['role']    = $user['role']; // 'admin' atau 'member'

        // Arahkan semua role ke satu halaman home dinamis
        header("Location: ../view/home.php");
        exit;
    } else {
        $_SESSION['error'] = "Email atau password salah!";
        header("Location: ../view/login.php");
        exit;
    }
} else {
    // Jika diakses langsung tanpa POST
    header("Location: ../view/login.php");
    exit;
}