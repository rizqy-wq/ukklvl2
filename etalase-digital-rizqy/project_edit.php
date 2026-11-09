<?php
require_once 'config/kuis_db.php';
require_once 'middleware/auth_check.php';

check_login();

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: index.php'); exit(); }

// Fetch data project
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = :id");
$stmt->execute([':id' => $id]);
$project = $stmt->fetch();

if (!$project) { die("Data karya tidak ditemukan."); }

// Pengecekan Otorisasi
if ($_SESSION['role'] === 'siswa' && $project['user_id'] != $_SESSION['user_id']) {
    die("Akses Ditolak: Anda tidak dapat mengedit karya milik siswa lain.");
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_project = trim($_POST['nama_project']);
    $deskripsi    = trim($_POST['deskripsi']);
    $link_project = trim($_POST['link_project']);
    $gambar_nama  = $project['gambar'];

    // Jika user mengunggah gambar baru
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $new_name = uniqid('project_', true) . '.' . $ext;
        
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], 'uploads/' . $new_name)) {
            if (file_exists('uploads/' . $project['gambar'])) {
                unlink('uploads/' . $project['gambar']); // Hapus gambar lama
            }
            $gambar_nama = $new_name;
        }
    }

    $stmt = $pdo->prepare("UPDATE projects SET nama_project = :nama, deskripsi = :desk, gambar = :img, link_project = :link WHERE id = :id");
    $stmt->execute([':nama' => $nama_project, ':desk' => $deskripsi, ':img' => $gambar_nama, ':link' => $link_project, ':id' => $id]);
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Edit Karya</title>
    <style>
        body { font-family: sans-serif; background: #f4f6f9; padding: 20px; }
        .form-card { max-width: 500px; margin: auto; background: white; padding: 20px; border-radius: 8px; }
        input, textarea { width: 100%; margin-bottom: 12px; padding: 8px; box-sizing: border-box; }
        button { background: #ffc107; padding: 10px; width: 100%; border: none; cursor: pointer; }
    </style>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/form.css">
</head>
<body>
<div class="form-card">
    <h2>Edit Karya Digital</h2>
    <form method="POST" enctype="multipart/form-data">
        <label>Nama Karya</label>
        <input type="text" name="nama_project" value="<?= htmlspecialchars($project['nama_project']) ?>" required>
        <label>Deskripsi</label>
        <textarea name="deskripsi" required><?= htmlspecialchars($project['deskripsi']) ?></textarea>
        <label>Link Project</label>
        <input type="url" name="link_project" value="<?= htmlspecialchars($project['link_project']) ?>" required>
        <label>Ganti Gambar (Opsional)</label>
        <input type="file" name="gambar" accept="image/*">
        <button type="submit">Simpan Perubahan</button>
    </form>
</div>
</body>
