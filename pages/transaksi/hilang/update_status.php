<?php
require_once '../../../includes/auth_check.php';
require_once '../../../config/database.php';

// Hanya admin dan staff yang bisa mengupdate status
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff') {
    header("Location: ../../../dashboard.php");
    exit();
}

if (!isset($_GET['id']) || !isset($_GET['status'])) {
    header("Location: index.php");
    exit();
}

$id_hilang = $_GET['id'];
$status = $_GET['status'];

// Validasi status
$allowed_status = ['ditemukan', 'ditutup'];
if (!in_array($status, $allowed_status)) {
    header("Location: index.php?error=Status+tidak+valid");
    exit();
}

try {
    // Update status barang hilang
    $stmt = $pdo->prepare("UPDATE barang_hilang SET status = ? WHERE id_hilang = ?");
    $stmt->execute([$status, $id_hilang]);

    // Jika status ditemukan, kembalikan stok
    if ($status === 'ditemukan') {
        // Dapatkan data barang hilang
        $hilang = $pdo->query("SELECT id_barang, jumlah FROM barang_hilang WHERE id_hilang = $id_hilang")->fetch();

        if ($hilang) {
            // Update stok barang
            $pdo->query("UPDATE barang SET stok = stok + {$hilang['jumlah']} WHERE id_barang = {$hilang['id_barang']}");

            // Catat riwayat stok
            $pdo->prepare("INSERT INTO riwayat_stok (
                id_barang, 
                jenis_transaksi, 
                id_transaksi, 
                stok_sebelum, 
                perubahan, 
                stok_sesudah, 
                tanggal_transaksi
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())")->execute([
                $hilang['id_barang'],
                'penyesuaian',
                $id_hilang,
                $pdo->query("SELECT stok FROM barang WHERE id_barang = {$hilang['id_barang']}")->fetchColumn() - $hilang['jumlah'],
                $hilang['jumlah'],
                $pdo->query("SELECT stok FROM barang WHERE id_barang = {$hilang['id_barang']}")->fetchColumn()
            ]);
        }
    }

    header("Location: index.php?success=Status+berhasil+diupdate");
    exit();
} catch (PDOException $e) {
    header("Location: index.php?error=Gagal+update+status");
    exit();
}
