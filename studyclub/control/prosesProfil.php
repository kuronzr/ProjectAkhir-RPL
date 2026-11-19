<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function getFotoProfil($db = null)
{
    if (!empty($_SESSION['foto'])) {
        return $_SESSION['foto'];
    }

    if ($db && !empty($_SESSION['user_id'])) {
        $stmt = $db->prepare("SELECT foto FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => (int) $_SESSION['user_id']]);
        $foto = $stmt->fetchColumn() ?: '';
        $_SESSION['foto'] = $foto;
        return $foto;
    }

    return '';
}

if (basename($_SERVER['SCRIPT_FILENAME']) === 'prosesProfil.php') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../view/login.php");
        exit;
    }

    require_once '../config/database.php';

    $db = (new Database())->getConnection();
    $userId = (int) $_SESSION['user_id'];

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: ../view/profil.php");
        exit;
    }

    $nama = trim($_POST['nama'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    if ($bio === '') {
        $bio = 'Mahasiswa Informatika, Universitas Singaperbangsa Karawang.';
    }
    $hapusFoto = ($_POST['hapus_foto'] ?? '0') === '1';
    $mapelIds = isset($_POST['mapel']) && is_array($_POST['mapel']) ? $_POST['mapel'] : [];

    if ($nama === '') {
        $_SESSION['profil_err'] = 'Nama tidak boleh kosong.';
        header("Location: ../view/profil.php");
        exit;
    }

    try {
        $stmtOld = $db->prepare("SELECT foto FROM users WHERE id = :id LIMIT 1");
        $stmtOld->execute([':id' => $userId]);
        $oldUser = $stmtOld->fetch(PDO::FETCH_ASSOC);
        $fotoPath = $oldUser['foto'] ?? '';

        $uploadDir = __DIR__ . '/../public/uploads/profil/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if ($hapusFoto) {
            if ($fotoPath && file_exists(__DIR__ . '/' . $fotoPath)) {
                @unlink(__DIR__ . '/' . $fotoPath);
            }
            $fotoPath = '';
        }

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['foto']['tmp_name'];
            $size = $_FILES['foto']['size'];

            if ($size > 10 * 1024 * 1024) {
                $_SESSION['profil_err'] = 'Ukuran foto maksimal 10 MB.';
                header("Location: ../view/profil.php");
                exit;
            }

            $info = @getimagesize($tmpName);
            $allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF];
            if (!$info || !in_array($info[2], $allowedTypes, true)) {
                $_SESSION['profil_err'] = 'Format foto harus JPG, PNG, WEBP, atau GIF.';
                header("Location: ../view/profil.php");
                exit;
            }

            if ($fotoPath && file_exists(__DIR__ . '/' . $fotoPath)) {
                @unlink(__DIR__ . '/' . $fotoPath);
            }

            [$origW, $origH, $imgType] = $info;
            $srcImg = match ($imgType) {
                IMAGETYPE_JPEG => imagecreatefromjpeg($tmpName),
                IMAGETYPE_PNG => imagecreatefrompng($tmpName),
                IMAGETYPE_WEBP => imagecreatefromwebp($tmpName),
                IMAGETYPE_GIF => imagecreatefromgif($tmpName),
            };

            $cropSize = min($origW, $origH);
            $srcX = (int) floor(($origW - $cropSize) / 2);
            $srcY = (int) floor(($origH - $cropSize) / 2);
            $targetSize = 320;

            $dstImg = imagecreatetruecolor($targetSize, $targetSize);
            imagecopyresampled($dstImg, $srcImg, 0, 0, $srcX, $srcY, $targetSize, $targetSize, $cropSize, $cropSize);

            $newFileName = 'profil_' . $userId . '_' . time() . '.jpg';
            $destPath = $uploadDir . $newFileName;

            if (!imagejpeg($dstImg, $destPath, 88)) {
                imagedestroy($srcImg);
                imagedestroy($dstImg);
                $_SESSION['profil_err'] = 'Gagal menyimpan hasil crop foto ke server.';
                header("Location: ../view/profil.php");
                exit;
            }

            imagedestroy($srcImg);
            imagedestroy($dstImg);

            $fotoPath = '../public/uploads/profil/' . $newFileName;
        }

        $db->beginTransaction();

        $updUser = $db->prepare("UPDATE users SET nama = :nama, bio = :bio, foto = :foto WHERE id = :id");
        $updUser->execute([
            ':nama' => $nama,
            ':bio' => $bio,
            ':foto' => $fotoPath !== '' ? $fotoPath : null,
            ':id' => $userId
        ]);

        $delMapel = $db->prepare("DELETE FROM user_courses WHERE user_id = :uid");
        $delMapel->execute([':uid' => $userId]);

        if (!empty($mapelIds)) {
            $insMapel = $db->prepare("INSERT INTO user_courses (user_id, course_id) VALUES (:uid, :cid)");
            foreach ($mapelIds as $cid) {
                $cidInt = (int) $cid;
                if ($cidInt > 0) {
                    $insMapel->execute([':uid' => $userId, ':cid' => $cidInt]);
                }
            }
        }

        $db->commit();
        $_SESSION['nama'] = $nama;
        $_SESSION['foto'] = $fotoPath;

        header("Location: ../view/profil.php");
        exit;
    } catch (PDOException $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $_SESSION['profil_err'] = 'Kesalahan database: ' . $e->getMessage();
        header("Location: ../view/profil.php");
        exit;
    }
}