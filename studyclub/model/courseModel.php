<?php
class CourseModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Fungsi ambil semua kursus
    public function getAllCourses()
    {
        $sql = "SELECT c.*,
                (SELECT COUNT(*) FROM course_practicals cp WHERE cp.course_id = c.id) AS jumlah_praktikum,
                (SELECT COUNT(*) FROM course_materials cm WHERE cm.course_id = c.id) AS jumlah_materi
                FROM courses c
                ORDER BY c.nama_kursus ASC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fungsi ambil kursus berdasarkan ID
    public function getCourseById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM courses WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Fungsi ambil kursus berdasarkan nama
    public function createCourse($nama, $kategori, $tema, $deskripsi)
    {
        $sql = "INSERT INTO courses (nama_kursus, kategori, tema_banner, deskripsi) VALUES (:nama, :kat, :tema, :desk)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':nama' => $nama,
            ':kat' => $kategori,
            ':tema' => $tema,
            ':desk' => $deskripsi
        ]);
    }

    // Fungsi update kursus
    public function deleteCourse($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM courses WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // Fungsi ambil materi berdasarkan kursus
    public function getMaterialsByCourse($courseId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM course_materials WHERE course_id = :cid ORDER BY id ASC");
        $stmt->execute([':cid' => $courseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fungsi ambil materi berdasarkan ID
    public function getMaterialById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM course_materials WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Fungsi buat materi baru
    public function createMaterial($courseId, $judul, $tanggal, $videoUrl, $fileModul, $deskripsi, $poinModul, $isPremium = 0)
    {
        $sql = "INSERT INTO course_materials (course_id, judul, tanggal, video_url, file_modul, deskripsi, poin_modul, is_premium)
                VALUES (:cid, :judul, :tgl, :vid, :modul, :desk, :poin, :prem)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':cid' => $courseId,
            ':judul' => $judul,
            ':tgl' => $tanggal,
            ':vid' => $videoUrl,
            ':modul' => $fileModul,
            ':desk' => $deskripsi,
            ':poin' => $poinModul,
            ':prem' => $isPremium
        ]);
    }

    // Fungsi update materi
    public function getPracticalsByCourse($courseId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM course_practicals WHERE course_id = :cid ORDER BY id ASC");
        $stmt->execute([':cid' => $courseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fungsi ambil praktikum berdasarkan ID
    public function getPracticalById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM course_practicals WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Fungsi buat praktikum baru
    public function createPractical($courseId, $judul, $tanggal, $instruksi, $referensiLink)
    {
        $sql = "INSERT INTO course_practicals (course_id, judul, tanggal, instruksi, referensi_link)
                VALUES (:cid, :judul, :tgl, :inst, :ref)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':cid' => $courseId,
            ':judul' => $judul,
            ':tgl' => $tanggal,
            ':inst' => $instruksi,
            ':ref' => $referensiLink
        ]);
    }
}