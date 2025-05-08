<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

// Hanya admin yang bisa menghapus lokasi
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../dashboard.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id_lokasi = $_GET['id'];

try {
    // Cek apakah lokasi digunakan di tabel barang
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM barang WHERE id_lokasi = ?");
    $stmt->execute([$id_lokasi]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        header("Location: index.php?error=Lokasi+tidak+dapat+dihapus+karena+masih+digunakan+oleh+barang");
        exit();
    }

    // Hapus lokasi
    $stmt = $pdo->prepare("DELETE FROM lokasi WHERE id_lokasi = ?");
    $stmt->execute([$id_lokasi]);

    header("Location: index.php?success=Lokasi+berhasil+dihapus");
    exit();
} catch (PDOException $e) {
    header("Location: index.php?error=Gagal+menghapus+lokasi:+" . urlencode($e->getMessage()));
    exit();
}
