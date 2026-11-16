<?php
class ScheduleModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllSchedules() {
        $stmt = $this->conn->query("SELECT * FROM schedules ORDER BY tanggal ASC, waktu_mulai ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createSchedule($nama, $kategori, $tanggal, $mulai, $selesai, $lokasi, $deskripsi) {
        $sql = "INSERT INTO schedules (nama_kegiatan, kategori, tanggal, waktu_mulai, waktu_selesai, lokasi, deskripsi)
                VALUES (:nama, :kat, :tgl, :mulai, :selesai, :lok, :desk)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':nama'    => $nama,
            ':kat'     => $kategori,
            ':tgl'     => $tanggal,
            ':mulai'   => $mulai,
            ':selesai' => $selesai,
            ':lok'     => $lokasi,
            ':desk'    => $deskripsi
        ]);
    }

    public function deleteSchedule($id) {
        $stmt = $this->conn->prepare("DELETE FROM schedules WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}