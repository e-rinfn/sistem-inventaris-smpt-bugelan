<?php
require_once '../../../includes/auth_check.php';
require_once '../../../config/database.php';

// 1. Validasi Role dan Autentikasi
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff') {
    $_SESSION['error'] = "Anda tidak memiliki izin untuk melakukan operasi ini";
    header("Location: ../../dashboard/index.php");
    exit();
}

// 2. Validasi Input ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID transaksi tidak valid";
    header("Location: index.php");
    exit();
}

$id_masuk = (int)$_GET['id'];
if ($id_masuk <= 0) {
    $_SESSION['error'] = "ID transaksi tidak valid";
    header("Location: index.php");
    exit();
}

try {
    // 3. Mulai transaksi dengan isolasi level tinggi
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->beginTransaction();

    // 4. Ambil data transaksi dengan LOCKING
    $stmt = $pdo->prepare("SELECT 
                            bm.*, 
                            b.stok AS stok_sekarang,
                            b.kode_barang,
                            b.nama_barang,
                            b.id_barang
                          FROM barang_masuk bm
                          JOIN barang b ON bm.id_barang = b.id_barang
                          WHERE bm.id_masuk = ? FOR UPDATE");
    $stmt->execute([$id_masuk]);
    $transaksi = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$transaksi) {
        $pdo->rollBack();
        $_SESSION['error'] = "Transaksi tidak ditemukan";
        header("Location: index.php");
        exit();
    }

    // 5. Validasi kepemilikan transaksi
    if ($_SESSION['role'] !== 'admin' && $transaksi['id_pengguna'] != $_SESSION['id_pengguna']) {
        $pdo->rollBack();
        $_SESSION['error'] = "Anda hanya bisa menghapus transaksi yang Anda buat";
        header("Location: index.php");
        exit();
    }

    // 6. Hitung stok baru dan validasi
    $stok_baru = $transaksi['stok_sekarang'] - $transaksi['jumlah'];
    if ($stok_baru < 0) {
        $pdo->rollBack();
        $_SESSION['error'] = "Tidak bisa menghapus karena stok akan menjadi negatif (Stok saat ini: {$transaksi['stok_sekarang']}, Akan dikurangi: {$transaksi['jumlah']})";
        header("Location: index.php");
        exit();
    }

    // 7. Update stok barang
    $stmt = $pdo->prepare("UPDATE barang SET stok = ? WHERE id_barang = ?");
    $stmt->execute([$stok_baru, $transaksi['id_barang']]);

    if ($stmt->rowCount() === 0) {
        $pdo->rollBack();
        $_SESSION['error'] = "Gagal mengupdate stok barang";
        header("Location: index.php");
        exit();
    }

    // 8. Catat riwayat stok
    $keterangan = sprintf(
        "Penghapusan barang masuk ID: %d | %s - %s | Jumlah: %d | Oleh: %s",
        $id_masuk,
        $transaksi['kode_barang'],
        $transaksi['nama_barang'],
        $transaksi['jumlah'],
        $_SESSION['nama_lengkap']
    );

    $stmt = $pdo->prepare("INSERT INTO riwayat_stok (
        id_barang, 
        jenis_transaksi, 
        id_transaksi, 
        stok_sebelum, 
        perubahan, 
        stok_sesudah, 
        tanggal_transaksi,
        keterangan,
        id_pengguna
    ) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?)");

    $stmt->execute([
        $transaksi['id_barang'],
        'hapus_masuk',
        $id_masuk,
        $transaksi['stok_sekarang'],
        -$transaksi['jumlah'],
        $stok_baru,
        $keterangan,
        $_SESSION['id_pengguna']
    ]);

    // 9. Hapus transaksi barang masuk
    $stmt = $pdo->prepare("DELETE FROM barang_masuk WHERE id_masuk = ?");
    $stmt->execute([$id_masuk]);

    if ($stmt->rowCount() === 0) {
        $pdo->rollBack();
        $_SESSION['error'] = "Gagal menghapus transaksi (mungkin sudah dihapus)";
        header("Location: index.php");
        exit();
    }

    // 10. Commit transaksi
    $pdo->commit();

    $_SESSION['success'] = sprintf(
        "Transaksi barang masuk berhasil dihapus. %s - %s (Jumlah: %d) telah dikurangi dari stok.",
        $transaksi['kode_barang'],
        $transaksi['nama_barang'],
        $transaksi['jumlah']
    );
    header("Location: index.php");
    exit();
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        try {
            $pdo->rollBack();
        } catch (Exception $rollbackEx) {
            error_log("Gagal rollback: " . $rollbackEx->getMessage());
        }
    }

    error_log("Error menghapus barang masuk ID {$id_masuk}: " . $e->getMessage());

    // Cek constraint violation
    if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
        $_SESSION['error'] = "Tidak bisa menghapus karena data terkait masih digunakan";
    } else {
        $_SESSION['error'] = "Terjadi kesalahan sistem: " . $e->getMessage();
    }

    header("Location: index.php");
    exit();
}
