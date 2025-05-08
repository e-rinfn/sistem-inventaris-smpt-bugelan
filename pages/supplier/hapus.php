<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

// Cek role admin atau staff
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff') {
    header("Location: ../../dashboard.php");
    exit();
}

// Ambil ID supplier dari URL
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id_supplier = $_GET['id'];

try {
    // Cek apakah supplier digunakan di transaksi barang masuk
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM barang_masuk WHERE id_supplier = ?");
    $stmt->execute([$id_supplier]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        header("Location: index.php?error=Supplier+tidak+dapat+dihapus+karena+sudah+digunakan+di+transaksi");
        exit();
    }

    // Hapus supplier
    $stmt = $pdo->prepare("DELETE FROM supplier WHERE id_supplier = ?");
    $stmt->execute([$id_supplier]);

    header("Location: index.php?success=Supplier+berhasil+dihapus");
    exit();
} catch (PDOException $e) {
    header("Location: index.php?error=Gagal+menghapus+supplier");
    exit();
}
