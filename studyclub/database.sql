DROP DATABASE IF EXISTS study_club;
CREATE DATABASE study_club;
USE study_club;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('anggota', 'admin') DEFAULT 'anggota',
    bio VARCHAR(150) DEFAULT 'Mahasiswa Informatika',
    foto VARCHAR(255) NULL,
    status_subs ENUM('Regular', 'Bulanan', 'Premium') DEFAULT 'Regular',
    is_blocked TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(20) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    kategori VARCHAR(50) NOT NULL,
    warna_cover VARCHAR(30) DEFAULT 'p-navy'
);

CREATE TABLE course_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    tipe ENUM('materi', 'praktikum') NOT NULL,
    judul VARCHAR(120) NOT NULL,
    deskripsi TEXT,
    tenggat DATE NULL,
    file_modul VARCHAR(255) NULL,
    file_video VARCHAR(255) NULL,
    link_referensi VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    user_id INT NOT NULL,
    file_tugas VARCHAR(255) NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES course_items(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(120) NOT NULL,
    kategori ENUM('Praktikum', 'Materi', 'Diskusi', 'Lainnya') NOT NULL,
    tanggal DATE NOT NULL,
    waktu_mulai TIME NOT NULL,
    waktu_selesai TIME NOT NULL,
    lokasi VARCHAR(150),
    deskripsi TEXT
);

CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    isi TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    negara VARCHAR(50) NOT NULL,
    alamat TEXT NOT NULL,
    paket ENUM('Bulanan', 'Premium') NOT NULL,
    metode_pembayaran ENUM('Gopay', 'ShopeePay') NOT NULL,
    total_bayar INT NOT NULL,
    status ENUM('menunggu', 'diterima', 'ditolak') DEFAULT 'menunggu',
    tanggal DATE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);