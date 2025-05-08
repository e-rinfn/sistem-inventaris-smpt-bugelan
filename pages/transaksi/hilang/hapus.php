<?php
require_once '../../../includes/auth_check.php';
require_once '../../../config/database.php';

// Hanya admin dan staff yang bisa menghapus
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff') {
    header("Location: ../../../dashboard.php");
    exit();
}

// Pastikan ID ada
if (!isset($_GET['id'])) {
    header("Location: index.php?error=ID+tidak+valid");
    exit();
}

$id_hilang = $_GET['id'];

try {
    // Mulai transaksi
    $pdo->beginTransaction();

    // 1. Dapatkan data barang hilang untuk mengembalikan stok
    $stmt = $pdo->prepare("SELECT id_barang, jumlah FROM barang_hilang WHERE id_hilang = ?");
    $stmt->execute([$id_hilang]);
    $data = $stmt->fetch();

    if (!$data) {
        throw new Exception("Data barang hilang tidak ditemukan");
    }

    $id_barang = $data['id_barang'];
    $jumlah = $data['jumlah'];

    // 2. Hapus data barang hilang
    $stmt = $pdo->prepare("DELETE FROM barang_hilang WHERE id_hilang = ?");
    $stmt->execute([$id_hilang]);

    // 3. Kembalikan stok barang
    $stmt = $pdo->prepare("UPDATE barang SET stok = stok + ? WHERE id_barang = ?");
    $stmt->execute([$jumlah, $id_barang]);

    // 4. Catat di riwayat stok
    $stmt = $pdo->prepare("INSERT INTO riwayat_stok (id_barang, jenis_transaksi, id_transaksi, stok_sebelum, perubahan, stok_sesudah, tanggal_transaksi) 
                          SELECT 
                              id_barang, 
                              'penyesuaian', 
                              ?, 
                              stok, 
                              ?, 
                              stok + ?, 
                              NOW() 
                          FROM barang 
                          WHERE id_barang = ?");
    $stmt->execute([$id_hilang, $jumlah, $jumlah, $id_barang]);

    // Commit transaksi jika semua query berhasil
    $pdo->commit();

    header("Location: index.php?success=Laporan+barang+hilang+berhasil+dihapus+dan+stok+dikembalikan");
    exit();
} catch (Exception $e) {
    // Rollback jika terjadi error
    $pdo->rollBack();
    header("Location: index.php?error=Gagal+menghapus+laporan:+" . urlencode($e->getMessage()));
    exit();
}
