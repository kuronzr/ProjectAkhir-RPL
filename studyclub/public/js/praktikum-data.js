// Detail praktikum: instruksi tugas per course (urutan sama dengan daftar praktikum di kursus-data.js)
// Referensi (PDF dan link GitHub) memakai nilai bawaan di praktikum.js.
// Untuk mengganti per praktikum, tambahkan objek { instruksi, ref: [[nama, tipe, url], ...] } sebagai ganti string.
window.PRAKTIKUM_DETAIL = {
  'web-dev': [
    'Buatlah sebuah halaman profil diri menggunakan HTML saja. Gunakan elemen semantik (header, main, section, footer), minimal satu gambar, satu tabel, dan satu daftar. Unggah hasilnya dalam bentuk laporan singkat beserta tangkapan layar.',
    'Percantik halaman profil dari Praktikum 1 menggunakan CSS. Terapkan layout dengan Flexbox, atur warna, tipografi, dan jarak antar elemen. Sertakan penjelasan singkat tentang properti Flexbox yang kamu gunakan.',
    'Buatlah aplikasi to-do list sederhana dengan JavaScript. Pengguna dapat menambah dan menghapus tugas melalui manipulasi DOM. Unggah laporan beserta tautan repositori GitHub kodenya.',
    'Ubah website yang sudah dibuat menjadi responsif untuk layar ponsel, tablet, dan desktop menggunakan media query. Lampirkan tangkapan layar dari ketiga ukuran layar tersebut.',
    'Buatlah halaman yang mengambil data dari sebuah REST API publik menggunakan Fetch, lalu tampilkan hasilnya dalam bentuk daftar atau kartu. Tangani juga kondisi loading dan error.'
  ],
  'cybersec': [
    'Pasang lab Kali Linux pada mesin virtual (VirtualBox atau VMware). Unggah laporan berisi langkah instalasi, konfigurasi jaringan, dan tangkapan layar hasil menjalankan perintah dasar.',
    'Lakukan pemindaian jaringan pada lab milikmu sendiri menggunakan Nmap. Dokumentasikan perintah yang digunakan, port yang terbuka, dan analisis singkat dari hasil pemindaian.',
    'Tangkap lalu analisis lalu lintas jaringan menggunakan Wireshark. Identifikasi minimal tiga protokol berbeda dan jelaskan isi paket yang kamu temukan.',
    'Uji kerentanan SQL Injection dan XSS pada aplikasi web latihan yang disediakan. Jelaskan langkah pengujian, bukti temuan, dan rekomendasi perbaikannya. Lakukan hanya pada lingkungan lab.'
  ],
  'iot': [
    'Pasang Arduino IDE, lakukan konfigurasi board ESP32, lalu unggah program Blink ke perangkat. Lampirkan foto rangkaian dan tangkapan layar hasil unggah program.',
    'Rangkai sensor suhu dan kelembapan (misalnya DHT11) pada ESP32 dan tampilkan hasil pembacaannya pada Serial Monitor. Sertakan kode dan skema rangkaian.',
    'Buatlah program agar LED dan relay dapat dikendalikan melalui halaman web lewat jaringan WiFi. Unggah kode, skema rangkaian, dan video singkat demonstrasinya.',
    'Kirim data sensor dari ESP32 ke broker MQTT, lalu tampilkan datanya pada MQTT client. Jelaskan topik yang digunakan dan lampirkan bukti data yang diterima.'
  ],
  'ui-ux': [
    'Lakukan riset pengguna sederhana melalui wawancara atau kuesioner terhadap minimal lima responden. Susun hasilnya menjadi satu persona lengkap dengan tujuan dan kendala pengguna.',
    'Rancang wireframe untuk aplikasi pilihanmu, minimal lima layar utama, dengan alur yang jelas. Unggah dalam bentuk PDF atau tautan Figma.',
    'Ubah wireframe menjadi desain high-fidelity di Figma. Terapkan warna, tipografi, dan komponen yang konsisten. Lampirkan tautan file Figma dan ekspor tiap layar.',
    'Buatlah prototype interaktif dari desain yang sudah dibuat, lalu uji dengan minimal tiga pengguna. Unggah laporan hasil usability testing dan perbaikan yang kamu lakukan.'
  ]
};
