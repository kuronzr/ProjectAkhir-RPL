DROP DATABASE IF EXISTS study_club;
CREATE DATABASE IF NOT EXISTS study_club;
USE study_club;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('member', 'admin') NOT NULL DEFAULT 'member',
  membership ENUM('reguler', 'premium') NOT NULL DEFAULT 'reguler',
  is_banned TINYINT(1) NOT NULL DEFAULT 0,
  bio TEXT NULL,
  foto_profil VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS courses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_kursus VARCHAR(100) NOT NULL,
  kategori VARCHAR(50) NOT NULL,
  tema_banner VARCHAR(30) NOT NULL DEFAULT 'p-navy',
  deskripsi TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS course_materials (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT NOT NULL,
  judul VARCHAR(120) NOT NULL,
  tanggal DATE NOT NULL,
  video_url VARCHAR(255) NULL,
  file_modul VARCHAR(255) NULL,
  deskripsi TEXT NULL,
  poin_modul TEXT NULL,
  is_premium TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_materials_course
    FOREIGN KEY (course_id) REFERENCES courses(id)
    ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS course_practicals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT NOT NULL,
  judul VARCHAR(120) NOT NULL,
  tanggal DATE NOT NULL,
  instruksi TEXT NOT NULL,
  referensi_link TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_practicals_course
    FOREIGN KEY (course_id) REFERENCES courses(id)
    ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS practical_submissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  practical_id INT NOT NULL,
  user_id INT NOT NULL,
  file_tugas VARCHAR(255) NOT NULL,
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_sub_practical
    FOREIGN KEY (practical_id) REFERENCES course_practicals(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_sub_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS subscriptions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  paket ENUM('bulanan', 'tahunan') NOT NULL,
  harga INT NOT NULL,
  metode_pembayaran VARCHAR(50) NOT NULL,
  nomor_hp VARCHAR(30) NULL,
  status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  tanggal_pengajuan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  tanggal_berakhir DATE NULL,
  CONSTRAINT fk_subs_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS schedules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT NULL,
  judul_kegiatan VARCHAR(150) NOT NULL,
  kategori VARCHAR(50) NOT NULL,
  tanggal DATE NOT NULL,
  waktu_mulai TIME NOT NULL,
  waktu_selesai TIME NOT NULL,
  lokasi_atau_link VARCHAR(255) NOT NULL,
  deskripsi TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_schedule_course
    FOREIGN KEY (course_id) REFERENCES courses(id)
    ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS announcements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  isi TEXT NOT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO announcements (isi)
VALUES (
  'Selamat datang di platform Study Club! Silakan cek menu Course untuk mengakses materi dan praktikum terbaru.'
);