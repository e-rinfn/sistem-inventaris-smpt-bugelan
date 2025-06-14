<?php
require_once '../../../includes/auth_check.php';
require_once '../../../config/database.php';

$id_keluar = $_GET['id'] ?? 0;

if ($id_keluar > 0) {
    // Cek apakah ada data pengembalian
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM barang_kembali WHERE id_keluar = ?");
    $stmt->execute([$id_keluar]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        $_SESSION['error'] = "Tidak dapat menghapus karena sudah ada data pengembalian";
    } else {
        // Dapatkan data barang keluar untuk update stok
        $stmt = $pdo->prepare("SELECT id_barang, jumlah FROM barang_keluar WHERE id_keluar = ?");
        $stmt->execute([$id_keluar]);
        $barang_keluar = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($barang_keluar) {
            try {
                $pdo->beginTransaction();

                // Hapus data barang keluar
                $pdo->prepare("DELETE FROM barang_keluar WHERE id_keluar = ?")->execute([$id_keluar]);

                // Update stok barang
                $pdo->prepare("UPDATE barang SET stok = stok + ? WHERE id_barang = ?")
                    ->execute([$barang_keluar['jumlah'], $barang_keluar['id_barang']]);

                $pdo->commit();
                $_SESSION['success'] = "Data barang keluar berhasil dihapus";
            } catch (PDOException $e) {
                $pdo->rollBack();
                $_SESSION['error'] = "Gagal menghapus data: " . $e->getMessage();
            }
        } else {
            $_SESSION['error'] = "Data tidak ditemukan";
        }
    }
}

header("Location: index.php");
exit();
