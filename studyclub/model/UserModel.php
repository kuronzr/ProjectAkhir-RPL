<?php

class UserModel {
    private $conn;
    private $table = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Mengambil data user berdasarkan email (untuk login & cek duplikat)
    public function getUserByEmail($email) {
        $query = "SELECT id, nama, email, password, role FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Menyimpan user baru (default role = 'member')
    public function createUser($nama, $email, $hashedPassword, $role = 'member') {
        $query = "INSERT INTO " . $this->table . " (nama, email, password, role) VALUES (:nama, :email, :password, :role)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':nama', $nama, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);

        return $stmt->execute();
    }
}