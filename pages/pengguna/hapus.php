<?php
require_once '../../includes/auth_check.php';
// Hanya admin yang bisa mengakses
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../dashboard.php");
    exit();
}

require_once '../../config/database.php';

$id = $_GET['id'] ?? 0;

// Cek apakah pengguna mencoba menghapus dirinya sendiri
if ($id == $_SESSION['id_pengguna']) {
    header("Location: index.php?error=Tidak+bisa+menghapus+akun+sendiri");
    exit();
}

try {
    // Hapus pengguna
    $stmt = $pdo->prepare("DELETE FROM pengguna WHERE id_pengguna = ?");
    $stmt->execute([$id]);

    header("Location: index.php?success=Pengguna+berhasil+dihapus");
    exit();
} catch (PDOException $e) {
    header("Location: index.php?error=Gagal+menghapus+pengguna");
    exit();
}
