<?php
session_start();
require_once 'config/kuis_db.php';
require_once 'middleware/auth_check.php';

check_login();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_project = trim($_POST['nama_project'] ?? '');
    $deskripsi    = trim($_POST['deskripsi'] ?? '');
    $link_project = trim($_POST['link_project'] ?? '');
    $user_id      = $_SESSION['user_id'];

    if (empty($nama_project) || empty($deskripsi) || empty($link_project)) {
        $error = "Semua kolom input wajib diisi!";
    } elseif (!filter_var($link_project, FILTER_VALIDATE_URL)) {
        $error = "Format Link Project tidak valid (Gunakan format https://...)!";
    } elseif (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
        $error = "Pilih gambar karya digital terlebih dahulu!";
    } else {
        $file_tmp   = $_FILES['gambar']['tmp_name'];
        $file_name  = $_FILES['gambar']['name'];
        $file_size  = $_FILES['gambar']['size'];
        $file_ext   = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        $max_size    = 2 * 1024 * 1024; // Maksimal 2MB

        if (!in_array($file_ext, $allowed_ext)) {
            $error = "Format gambar harus berupa JPG, JPEG, PNG, atau WEBP!";
        } elseif ($file_size > $max_size) {
            $error = "Ukuran berkas gambar tidak boleh lebih dari 2MB!";
        } else {
            $new_file_name = uniqid('project_', true) . '.' . $file_ext;
            $upload_dir    = 'uploads/';

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $destination = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $destination)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO projects (user_id, nama_project, deskripsi, gambar, link_project) VALUES (:user_id, :nama_project, :deskripsi, :gambar, :link_project)");
                    $stmt->execute([
                        ':user_id'      => $user_id,
                        ':nama_project' => $nama_project,
                        ':deskripsi'    => $deskripsi,
                        ':gambar'       => $new_file_name,
                        ':link_project' => $link_project
                    ]);

                    header('Location: index.php');
                    exit();
                } catch (PDOException $e) {
                    if (file_exists($destination)) {
                        unlink($destination);
                    }
                    $error = "Terjadi kesalahan sistem: " . $e->getMessage();
                }
            } else {
                $error = "Gagal mengunggah berkas gambar ke server.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Karya - Etalase Digital Stella</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/form.css">
</head>
<body>

<div class="form-container">
    <a href="index.php" class="btn btn-secondary" style="margin-bottom: 15px;">&larr; Kembali ke Etalase</a>
    <h2>Tambah Karya Digital Baru</h2>

    <?php if (!empty($error)): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
            <?= htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="nama_project">Nama Karya Digital</label>
            <input type="text" id="nama_project" name="nama_project" value="<?= htmlspecialchars($_POST['nama_project'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="deskripsi">Deskripsi Karya</label>
            <textarea id="deskripsi" name="deskripsi" required><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label for="link_project">Link Tautan Project (GitHub / Website Live)</label>
            <input type="url" id="link_project" name="link_project" value="<?= htmlspecialchars($_POST['link_project'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="gambar">Unggah Tangkapan Layar Karya (Maks. 2MB)</label>
            <input type="file" id="gambar" name="gambar" accept="image/png, image/jpeg, image/jpg, image/webp" required>
            
            <div class="preview-box" id="previewBox">
                <p style="font-size: 12px; color: #6c757d; margin-bottom: 5px;">Pratinjau Gambar:</p>
                <img id="imagePreview" src="" alt="Preview Gambar">
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%;">Simpan Karya Digital</button>
    </form>
</div>

<!-- Script JS Preview Gambar Native -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputGambar = document.getElementById('gambar');
        const previewBox = document.getElementById('previewBox');
        const imagePreview = document.getElementById('imagePreview');

        inputGambar.addEventListener('change', function() {
            const file = this.files[0];

            if (file) {
                const reader = new FileReader();

                reader.addEventListener('load', function() {
                    imagePreview.setAttribute('src', this.result);
                    previewBox.style.display = 'block';
                });

                reader.readAsDataURL(file);
            } else {
                imagePreview.setAttribute('src', '');
                previewBox.style.display = 'none';
            }
        });
    });
</script>

</body>
</html>
