// Data course: dipakai halaman Kursus (daftar) dan halaman Materi (detail)
// Format materi: [judul, tanggal, deskripsi, [nama modul PDF]]
window.KURSUS_DATA = {
  'web-dev': {
    nama: 'WEB DEVELOPMENT',
    prodi: 'Programming',
    pola: 'p-plaid',
    praktikum: [
      ['Praktikum 1: Struktur Halaman dengan HTML', '2026-09-11'],
      ['Praktikum 2: Styling dengan CSS dan Flexbox', '2026-09-18'],
      ['Praktikum 3: Interaksi dengan JavaScript DOM', '2026-09-25'],
      ['Praktikum 4: Website Responsif', '2026-10-02'],
      ['Praktikum 5: Konsumsi REST API dengan Fetch', '2026-10-09']
    ],
    materi: [
      ['Materi 1: Cara Kerja Web dan HTTP', '2026-09-10',
        'Materi ini membahas bagaimana browser dan server saling berkomunikasi, mulai dari request dan response HTTP, status code, hingga peran DNS saat membuka sebuah website.',
        ['Modul Cara Kerja Web', 'Modul HTTP Request dan Response', 'Latihan Status Code']],
      ['Materi 2: Dasar HTML dan CSS', '2026-09-17',
        'Materi ini membahas struktur dokumen HTML, elemen semantik, serta dasar CSS seperti selector, box model, warna, dan tipografi untuk membangun tampilan halaman.',
        ['Modul Dasar HTML', 'Modul Dasar CSS', 'Cheat Sheet Selector CSS']],
      ['Materi 3: Dasar JavaScript', '2026-09-24',
        'Materi ini membahas variabel, tipe data, fungsi, percabangan, perulangan, dan cara JavaScript mengubah isi halaman melalui DOM.',
        ['Modul Dasar JavaScript', 'Modul Manipulasi DOM']],
      ['Materi 4: Pengenalan Backend dan Database', '2026-10-01',
        'Materi ini membahas peran backend, konsep API, serta dasar database relasional dan perintah SQL sederhana untuk menyimpan dan mengambil data.',
        ['Modul Pengenalan Backend', 'Modul Dasar SQL', 'Contoh Rancangan Database']]
    ]
  },
  'cybersec': {
    nama: 'CYBER SECURITY',
    prodi: 'Security',
    pola: 'p-navy',
    praktikum: [
      ['Praktikum 1: Instalasi Lab Kali Linux', '2026-09-11'],
      ['Praktikum 2: Network Scanning dengan Nmap', '2026-09-18'],
      ['Praktikum 3: Analisis Paket dengan Wireshark', '2026-09-25'],
      ['Praktikum 4: Uji Kerentanan Web (SQL Injection dan XSS)', '2026-10-02']
    ],
    materi: [
      ['Materi 1: Pengantar Keamanan Siber dan CIA Triad', '2026-09-10',
        'Materi ini memperkenalkan dunia keamanan siber dan tiga prinsip utamanya, yaitu confidentiality, integrity, dan availability, beserta contoh kasus nyata.',
        ['Modul Pengantar Keamanan Siber', 'Modul CIA Triad']],
      ['Materi 2: Jenis Ancaman dan Serangan', '2026-09-17',
        'Materi ini membahas berbagai jenis ancaman seperti malware, phishing, DDoS, dan social engineering, serta cara mengenali tanda-tandanya.',
        ['Modul Jenis Malware', 'Modul Phishing dan Social Engineering', 'Studi Kasus Serangan']],
      ['Materi 3: Kriptografi Dasar', '2026-09-24',
        'Materi ini membahas konsep enkripsi simetris dan asimetris, fungsi hash, serta penggunaannya dalam mengamankan data dan komunikasi.',
        ['Modul Kriptografi Dasar', 'Modul Fungsi Hash']],
      ['Materi 4: Keamanan Jaringan dan Firewall', '2026-10-01',
        'Materi ini membahas cara kerja firewall, segmentasi jaringan, VPN, dan praktik dasar untuk mengamankan jaringan dari akses yang tidak sah.',
        ['Modul Keamanan Jaringan', 'Modul Konfigurasi Firewall', 'Panduan VPN']]
    ]
  },
  'iot': {
    nama: 'INTERNET OF THINGS (IOT)',
    prodi: 'Hardware',
    pola: 'p-hex',
    praktikum: [
      ['Praktikum 1: Pengenalan Arduino dan ESP32', '2026-09-11'],
      ['Praktikum 2: Membaca Sensor Suhu dan Kelembapan', '2026-09-18'],
      ['Praktikum 3: Kontrol LED dan Relay via WiFi', '2026-09-25'],
      ['Praktikum 4: Kirim Data Sensor dengan MQTT', '2026-10-02']
    ],
    materi: [
      ['Materi 1: Konsep dan Arsitektur IoT', '2026-09-10',
        'Materi ini membahas pengertian IoT, lapisan arsitektur dari perangkat hingga cloud, serta contoh penerapannya di rumah, industri, dan pertanian.',
        ['Modul Konsep IoT', 'Modul Arsitektur IoT']],
      ['Materi 2: Sensor dan Aktuator', '2026-09-17',
        'Materi ini membahas jenis-jenis sensor dan aktuator, cara kerjanya, serta cara menghubungkannya ke mikrokontroler.',
        ['Modul Sensor', 'Modul Aktuator', 'Tabel Pin ESP32']],
      ['Materi 3: Protokol Komunikasi (MQTT dan HTTP)', '2026-09-24',
        'Materi ini membahas perbandingan protokol MQTT dan HTTP, konsep publish-subscribe, broker, dan topik pada pengiriman data perangkat IoT.',
        ['Modul MQTT', 'Modul HTTP untuk IoT']]
    ]
  },
  'ui-ux': {
    nama: 'UI/UX DESIGN',
    prodi: 'Design',
    pola: 'p-diamond',
    praktikum: [
      ['Praktikum 1: User Research dan Persona', '2026-09-11'],
      ['Praktikum 2: Wireframe Aplikasi', '2026-09-18'],
      ['Praktikum 3: Desain High-Fidelity dengan Figma', '2026-09-25'],
      ['Praktikum 4: Prototype dan Usability Testing', '2026-10-02']
    ],
    materi: [
      ['Materi 1: Pengantar UI/UX', '2026-09-10',
        'Materi ini membahas perbedaan UI dan UX, alur kerja desain produk digital, serta peran desainer dalam tim pengembangan.',
        ['Modul Pengantar UI/UX', 'Modul Proses Desain']],
      ['Materi 2: Prinsip Desain Visual', '2026-09-17',
        'Materi ini membahas prinsip desain visual seperti kontras, hierarki, keseimbangan, warna, dan tipografi agar antarmuka mudah dipahami.',
        ['Modul Prinsip Desain', 'Modul Warna dan Tipografi', 'Contoh Desain']],
      ['Materi 3: User Flow dan Information Architecture', '2026-09-24',
        'Materi ini membahas cara menyusun user flow dan struktur informasi aplikasi agar pengguna dapat menyelesaikan tugasnya dengan mudah.',
        ['Modul User Flow', 'Modul Information Architecture']]
    ]
  }
};
