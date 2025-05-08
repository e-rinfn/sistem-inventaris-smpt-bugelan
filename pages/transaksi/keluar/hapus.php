<?php
require_once '../../../includes/auth_check.php';
require_once '../../../config/database.php';

if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff') {
    header("Location: ../../../dashboard.php");
    exit();
}

$id_keluar = $_GET['id'] ?? null;

if ($id_keluar) {
    // Ambil data barang_keluar
    $stmt = $pdo->prepare("SELECT * FROM barang_keluar WHERE id_keluar = ?");
    $stmt->execute([$id_keluar]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        $id_barang = $data['id_barang'];
        $jumlah = $data['jumlah'];

        // Tambahkan kembali ke stok
        $pdo->prepare("UPDATE barang SET stok = stok + ? WHERE id_barang = ?")->execute([$jumlah, $id_barang]);

        // Hapus data barang_keluar
        $pdo->prepare("DELETE FROM barang_keluar WHERE id_keluar = ?")->execute([$id_keluar]);

        header("Location: index.php?success=Data berhasil dihapus dan stok dikembalikan.");
        exit();
    }
}

header("Location: index.php");
exit();
