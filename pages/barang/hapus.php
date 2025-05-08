<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

// Hanya admin yang bisa menghapus barang
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../dashboard.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id_barang = $_GET['id'];

try {
    // 1. Cek apakah ada transaksi terkait
    $query = "SELECT 
                (SELECT COUNT(*) FROM barang_masuk WHERE id_barang = ?) as masuk,
                (SELECT COUNT(*) FROM barang_keluar WHERE id_barang = ?) as keluar,
                (SELECT COUNT(*) FROM barang_hilang WHERE id_barang = ?) as hilang";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$id_barang, $id_barang, $id_barang]);
    $counts = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Jika ada transaksi, redirect ke halaman verifikasi
    if ($counts['masuk'] > 0 || $counts['keluar'] > 0 || $counts['hilang'] > 0) {
        $_SESSION['barang_dihapus'] = $id_barang;
        $_SESSION['transaksi_terkait'] = $counts;
        header("Location: verifikasi_hapus.php");
        exit();
    }

    // 3. Jika tidak ada transaksi, langsung hapus
    $stmt = $pdo->prepare("DELETE FROM barang WHERE id_barang = ?");
    $stmt->execute([$id_barang]);

    header("Location: index.php?success=Barang+berhasil+dihapus");
    exit();
} catch (PDOException $e) {
    header("Location: index.php?error=Gagal+memproses+permintaan");
    exit();
}
