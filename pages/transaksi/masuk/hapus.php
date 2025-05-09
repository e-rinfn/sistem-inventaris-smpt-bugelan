<?php
require_once '../../../includes/auth_check.php';
require_once '../../../config/database.php';

// Cek role admin atau staff
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff') {
    header("Location: ../../dashboard/index.php");
    exit();
}

// Ambil ID dari URL
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id_masuk = $_GET['id'];

// Ambil data transaksi beserta informasi barang terkait
$stmt = $pdo->prepare("SELECT bm.*, b.stok AS stok_sekarang 
                      FROM barang_masuk bm
                      JOIN barang b ON bm.id_barang = b.id_barang
                      WHERE bm.id_masuk = ?");
$stmt->execute([$id_masuk]);
$transaksi = $stmt->fetch();

// Cek apakah transaksi ada dan milik pengguna (kecuali admin)
if (!$transaksi || ($_SESSION['role'] !== 'admin' && $transaksi['id_pengguna'] !== $_SESSION['id_pengguna'])) {
    header("Location: index.php");
    exit();
}

try {
    $pdo->beginTransaction();

    // 1. Kembalikan stok barang (kurangi stok sesuai jumlah transaksi)
    $stmt = $pdo->prepare("UPDATE barang SET stok = stok - ? WHERE id_barang = ?");
    $stmt->execute([$transaksi['jumlah'], $transaksi['id_barang']]);

    // 2. Catat perubahan stok di riwayat
    $stmt = $pdo->prepare("INSERT INTO riwayat_stok (
        id_barang, jenis_transaksi, id_transaksi, stok_sebelum, 
        perubahan, stok_sesudah, tanggal_transaksi
    ) VALUES (?, ?, ?, ?, ?, ?, NOW())");

    // Ambil stok saat ini sebelum perubahan
    $stmt_current = $pdo->prepare("SELECT stok FROM barang WHERE id_barang = ?");
    $stmt_current->execute([$transaksi['id_barang']]);
    $current_stok = $stmt_current->fetchColumn();

    $stmt->execute([
        $transaksi['id_barang'],
        'hapus_masuk',
        $id_masuk,
        $current_stok + $transaksi['jumlah'], // Stok sebelum dikurangi
        -$transaksi['jumlah'], // Perubahan negatif (pengurangan)
        $current_stok // Stok setelah dikurangi
    ]);

    // 3. Hapus transaksi barang masuk
    $stmt = $pdo->prepare("DELETE FROM barang_masuk WHERE id_masuk = ?");
    $stmt->execute([$id_masuk]);

    $pdo->commit();

    header("Location: index.php?success=Transaksi+barang+masuk+berhasil+dihapus");
    exit();
} catch (PDOException $e) {
    $pdo->rollBack();
    header("Location: index.php?error=Gagal+menghapus+transaksi: " . urlencode($e->getMessage()));
    exit();
}
