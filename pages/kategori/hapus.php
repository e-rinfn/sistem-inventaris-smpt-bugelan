<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

// Cek role admin atau staff
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff') {
    header("Location: ../../dashboard.php");
    exit();
}

// Ambil ID kategori dari URL
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id_kategori = $_GET['id'];

try {
    // Cek apakah kategori digunakan di tabel barang
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM barang WHERE id_kategori = ?");
    $stmt->execute([$id_kategori]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        header("Location: index.php?error=Kategori+tidak+dapat+dihapus+karena+masih+digunakan+di+data+barang");
        exit();
    }

    // Hapus kategori jika tidak digunakan
    $stmt = $pdo->prepare("DELETE FROM kategori WHERE id_kategori = ?");
    $stmt->execute([$id_kategori]);

    header("Location: index.php?success=Kategori+berhasil+dihapus");
    exit();
} catch (PDOException $e) {
    header("Location: index.php?error=Gagal+menghapus+kategori");
    exit();
}
