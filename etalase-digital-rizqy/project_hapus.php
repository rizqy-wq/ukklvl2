<?php
require_once 'config/kuis_db.php';
require_once 'middleware/auth_check.php';

check_login();

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $project = $stmt->fetch();

    if ($project) {
        // Pengecekan Otorisasi: Siswa hanya dapat menghapus miliknya sendiri
        if ($_SESSION['role'] === 'siswa' && $project['user_id'] != $_SESSION['user_id']) {
            die("Akses Ditolak!");
        }

        // Hapus file dari folder uploads/
        if (file_exists('uploads/' . $project['gambar'])) {
            unlink('uploads/' . $project['gambar']);
        }

        // Hapus baris data dari database
        $del = $pdo->prepare("DELETE FROM projects WHERE id = :id");
        $del->execute([':id' => $id]);
    }
}

header('Location: index.php');
exit();
?>
