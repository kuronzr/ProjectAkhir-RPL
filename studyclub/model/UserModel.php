<?php
class UserModel
{
    private $conn;
    private $table = "users";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Fungsi ambil user berdasarkan email
    public function getUserByEmail($email)
    {
        $query = "SELECT id, nama, email, password, role, is_banned FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Fungsi buat user baru
    public function createUser($nama, $email, $hashedPassword, $role = 'member')
    {
        $query = "INSERT INTO " . $this->table . " (nama, email, password, role) VALUES (:nama, :email, :password, :role)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':nama', $nama, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);

        return $stmt->execute();
    }

    // Fungsi ambil user berdasarkan ID
    public function getUserById($id)
    {
        $stmt = $this->conn->prepare("SELECT id, nama, email, role, bio, foto FROM " . $this->table . " WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Fungsi ambil ID kursus yang dimiliki user
    public function getUserCourseIds($userId)
    {
        $stmt = $this->conn->prepare("SELECT course_id FROM user_courses WHERE user_id = :uid");
        $stmt->execute([':uid' => $userId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

}