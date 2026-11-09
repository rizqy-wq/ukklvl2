<?php
require_once '../config/kuis_db.php';
require_once '../middleware/auth_check.php';

// Memastikan hanya Super Admin yang bisa mengakses halaman ini
check_role(['super_admin']);

// Tambah Admin Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_admin'])) {
    $nama  = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $pass  = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, role) VALUES (:nama, :email, :pass, 'admin')");
    $stmt->execute([':nama' => $nama, ':email' => $email, ':pass' => $pass]);
}

// Hapus User
if (isset($_GET['hapus'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id AND role != 'super_admin'");
    $stmt->execute([':id' => $_GET['hapus']]);
    header('Location: users.php');
    exit();
}

$users = $pdo->query("SELECT * FROM users ORDER BY role ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Kelola User - Super Admin</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #f8f9fa; }
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #343a40; color: white; }
        .card { background: white; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
    </style>
    link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/form.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<a href="../index.php">&larr; Kembali ke Etalase</a>
<h2>Manajemen User (Super Admin Only)</h2>

<div class="card">
    <h3>Tambah Admin Baru</h3>
    <form method="POST">
        <input type="text" name="nama" placeholder="Nama Admin" required>
        <input type="email" name="email" placeholder="Email Admin" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="tambah_admin">Tambah Admin</button>
    </form>
</div>

<table>
    <tr>
        <th>ID</th><th>Nama</th><th>Email</th><th>Role</th><th>Aksi</th>
    </tr>
    <?php foreach($users as $u): ?>
    <tr>
        <td><?= $u['id'] ?></td>
        <td><?= htmlspecialchars($u['nama']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><b><?= $u['role'] ?></b></td>
        <td>
            <?php if($u['role'] !== 'super_admin'): ?>
                <a href="users.php?hapus=<?= $u['id'] ?>" onclick="return confirm('Hapus user ini?')">Hapus</a>
            <?php else: ?>
                -
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</body>
</html>
