<?php
class SubscriptionModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Fungsi ambil status membership user
    public function getUserMembership($userId)
    {
        $stmt = $this->conn->prepare("SELECT membership_status, membership_expired FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user)
            return 'reguler';

        if ($user['membership_status'] === 'premium' && !empty($user['membership_expired'])) {
            if ($user['membership_expired'] < date('Y-m-d')) {
                $this->conn->prepare("UPDATE users SET membership_status = 'reguler', membership_expired = NULL WHERE id = :id")
                    ->execute([':id' => $userId]);
                return 'reguler';
            }
        }
        return $user['membership_status'] ?? 'reguler';
    }

    // Fungsi simpan data checkout
    public function submitCheckout($userId, $paket, $nama, $negara, $alamat, $metode, $total)
    {
        $sql = "INSERT INTO subscriptions (user_id, paket, nama_lengkap, negara, alamat, metode_pembayaran, total_bayar, status)
                VALUES (:uid, :paket, :nama, :negara, :alamat, :metode, :total, 'pending')";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':uid' => $userId,
            ':paket' => $paket,
            ':nama' => $nama,
            ':negara' => $negara,
            ':alamat' => $alamat,
            ':metode' => $metode,
            ':total' => $total
        ]);
    }

    // Fungsi ambil semua data subscription
    public function getAllSubscriptions()
    {
        $sql = "SELECT id, user_id, nama_lengkap, paket, metode_pembayaran, total_bayar, status, DATE(created_at) AS tgl
                FROM subscriptions 
                ORDER BY created_at DESC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fungsi update status subscription dan membership user
    public function updateSubscriptionStatus($subId, $aksi)
    {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("SELECT user_id, paket FROM subscriptions WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $subId]);
            $sub = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$sub) {
                $this->conn->rollBack();
                return false;
            }

            $dbStatus = ($aksi === 'diterima') ? 'approved' : 'rejected';
            $updSub = $this->conn->prepare("UPDATE subscriptions SET status = :st WHERE id = :id");
            $updSub->execute([':st' => $dbStatus, ':id' => $subId]);

            if ($dbStatus === 'approved') {
                $expiredDate = ($sub['paket'] === 'tahunan')
                    ? date('Y-m-d', strtotime('+1 year'))
                    : date('Y-m-d', strtotime('+1 month'));

                $updUser = $this->conn->prepare("UPDATE users SET membership_status = 'premium', membership_expired = :exp WHERE id = :uid");
                $updUser->execute([':exp' => $expiredDate, ':uid' => $sub['user_id']]);
            }

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // Fungsi ambil semua member dengan status subscription terakhir
    public function getAllMembersWithSubscription()
    {
        $sql = "SELECT 
                    u.id,
                    u.nama,
                    u.membership_status,
                    u.is_banned,
                    (
                        SELECT s.paket 
                        FROM subscriptions s 
                        WHERE s.user_id = u.id AND s.status = 'approved' 
                        ORDER BY s.created_at DESC 
                        LIMIT 1
                    ) AS paket_terakhir
                FROM users u
                WHERE LOWER(u.role) != 'admin'
                ORDER BY u.id DESC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fungsi update status banned member
    public function setMemberBanStatus($userId, $isBanned)
    {
        $stmt = $this->conn->prepare("UPDATE users SET is_banned = :ban WHERE id = :id");
        return $stmt->execute([
            ':ban' => $isBanned ? 1 : 0,
            ':id' => $userId
        ]);
    }

    // Fungsi cek apakah user dibanned
    public function isUserBanned($userId) {
        $stmt = $this->conn->prepare("SELECT is_banned FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($row && (int)$row['is_banned'] === 1);
    }
}