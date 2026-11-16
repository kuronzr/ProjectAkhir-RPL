<?php
class UserModel
{
    private $conn;
    private $table = "users";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getUserByEmail($email)
    {
        $query = "SELECT id, nama, email, password, role, is_banned FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

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

    public function getUserById($id)
    {
        $stmt = $this->conn->prepare("SELECT id, nama, email, role, bio, foto FROM " . $this->table . " WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserCourseIds($userId)
    {
        $stmt = $this->conn->prepare("SELECT course_id FROM user_courses WHERE user_id = :uid");
        $stmt->execute([':uid' => $userId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    public function updateProfile($userId, $nama, $bio, $foto, $courseIds = [])
    {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("UPDATE " . $this->table . " SET nama = :nama, bio = :bio, foto = :foto WHERE id = :id");
            $stmt->execute([
                ':nama' => $nama,
                ':bio' => $bio,
                ':foto' => $foto ?: null,
                ':id' => $userId
            ]);

            $this->conn->prepare("DELETE FROM user_courses WHERE user_id = :uid")->execute([':uid' => $userId]);
            if (!empty($courseIds)) {
                $ins = $this->conn->prepare("INSERT INTO user_courses (user_id, course_id) VALUES (:uid, :cid)");
                foreach ($courseIds as $cid) {
                    $ins->execute([':uid' => $userId, ':cid' => (int) $cid]);
                }
            }

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}