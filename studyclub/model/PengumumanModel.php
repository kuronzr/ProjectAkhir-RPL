<?php
// models/AnnouncementModel.php

class AnnouncementModel {
    private $conn;
    private $table = "announcements";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Mengambil pengumuman yang ada di database
    public function getAnnouncement() {
        $query = "SELECT id, isi, updated_at FROM " . $this->table . " ORDER BY id ASC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Selalu UPDATE data yang sudah ada (atau INSERT jika tabel masih kosong)
    public function saveAnnouncement($isi, $adminId) {
        $existing = $this->getAnnouncement();

        if ($existing) {
            // Ubah data yang sudah ada di database
            $query = "UPDATE " . $this->table . " SET isi = :isi, updated_by = :updated_by WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':isi', $isi, PDO::PARAM_STR);
            $stmt->bindParam(':updated_by', $adminId, PDO::PARAM_INT);
            $stmt->bindParam(':id', $existing['id'], PDO::PARAM_INT);
            return $stmt->execute();
        } else {
            // Jika database masih kosong, buat baris pertama
            $query = "INSERT INTO " . $this->table . " (isi, updated_by) VALUES (:isi, :updated_by)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':isi', $isi, PDO::PARAM_STR);
            $stmt->bindParam(':updated_by', $adminId, PDO::PARAM_INT);
            return $stmt->execute();
        }
    }
}