DROP DATABASE IF EXISTS study_club;
CREATE DATABASE IF NOT EXISTS study_club;
USE study_club;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('member', 'admin') NOT NULL DEFAULT 'member',
  membership_status ENUM('reguler', 'premium') NOT NULL DEFAULT 'reguler',
  membership_expired DATE NULL,
  is_banned TINYINT(1) NOT NULL DEFAULT 0,
  bio TEXT NULL,
  foto VARCHAR(255) NULL,
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

CREATE TABLE IF NOT EXISTS user_courses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  course_id INT NOT NULL,
  enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_user_course (user_id, course_id),
  CONSTRAINT fk_uc_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_uc_course
    FOREIGN KEY (course_id) REFERENCES courses(id)
    ON DELETE CASCADE ON UPDATE CASCADE
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
  nama_lengkap VARCHAR(100) NOT NULL,
  negara VARCHAR(60) NOT NULL,
  alamat TEXT NOT NULL,
  metode_pembayaran VARCHAR(50) NOT NULL,
  total_bayar INT NOT NULL,
  status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_subs_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS schedules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT NULL,
  nama_kegiatan VARCHAR(150) NOT NULL,
  kategori VARCHAR(50) NOT NULL,
  tanggal DATE NOT NULL,
  waktu_mulai TIME NOT NULL,
  waktu_selesai TIME NOT NULL,
  lokasi VARCHAR(255) NOT NULL,
  deskripsi TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_schedule_course
    FOREIGN KEY (course_id) REFERENCES courses(id)
    ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS announcements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  isi TEXT NOT NULL,
  updated_by INT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_announcements_user
    FOREIGN KEY (updated_by) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE
);

INSERT INTO announcements (isi)
VALUES (
  'Selamat datang di platform Study Club! Silakan cek menu Course untuk mengakses materi dan praktikum terbaru.'
);