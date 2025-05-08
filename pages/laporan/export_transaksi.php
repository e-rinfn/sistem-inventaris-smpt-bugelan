<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

// Ambil parameter filter
$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
$barang = isset($_GET['barang']) ? $_GET['barang'] : '';
$tanggal_awal = isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : date('Y-m-01');
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : date('Y-m-d');

// Query untuk data transaksi (sama dengan transaksi.php)
if ($jenis == 'masuk') {
    $query = "SELECT 
                bm.*, 
                b.kode_barang, 
                b.nama_barang, 
                s.nama_supplier, 
                p.nama_lengkap AS operator
              FROM barang_masuk bm
              JOIN barang b ON bm.id_barang = b.id_barang
              LEFT JOIN supplier s ON bm.id_supplier = s.id_supplier
              JOIN pengguna p ON bm.id_pengguna = p.id_pengguna
              WHERE bm.tanggal_masuk BETWEEN ? AND ?";

    if (!empty($barang)) {
        $query .= " AND bm.id_barang = ?";
        $params = [$tanggal_awal, $tanggal_akhir, $barang];
    } else {
        $params = [$tanggal_awal, $tanggal_akhir];
    }

    $query .= " ORDER BY bm.tanggal_masuk DESC";
} elseif ($jenis == 'keluar') {
    $query = "SELECT 
                bk.*, 
                b.kode_barang, 
                b.nama_barang, 
                p.nama_lengkap AS operator
              FROM barang_keluar bk
              JOIN barang b ON bk.id_barang = b.id_barang
              JOIN pengguna p ON bk.id_pengguna = p.id_pengguna
              WHERE bk.tanggal_keluar BETWEEN ? AND ?";

    if (!empty($barang)) {
        $query .= " AND bk.id_barang = ?";
        $params = [$tanggal_awal, $tanggal_akhir, $barang];
    } else {
        $params = [$tanggal_awal, $tanggal_akhir];
    }

    $query .= " ORDER BY bk.tanggal_keluar DESC";
} elseif ($jenis == 'hilang') {
    $query = "SELECT 
                bh.*, 
                b.kode_barang, 
                b.nama_barang, 
                p.nama_lengkap AS operator
              FROM barang_hilang bh
              JOIN barang b ON bh.id_barang = b.id_barang
              JOIN pengguna p ON bh.id_pengguna = p.id_pengguna
              WHERE bh.tanggal_hilang BETWEEN ? AND ?";

    if (!empty($barang)) {
        $query .= " AND bh.id_barang = ?";
        $params = [$tanggal_awal, $tanggal_akhir, $barang];
    } else {
        $params = [$tanggal_awal, $tanggal_akhir];
    }

    $query .= " ORDER BY bh.tanggal_hilang DESC";
} else {
    // Default: tampilkan semua jenis transaksi
    $query = "(
                SELECT 
                    'masuk' AS jenis, 
                    bm.id_masuk AS id_transaksi, 
                    bm.tanggal_masuk AS tanggal, 
                    b.kode_barang, 
                    b.nama_barang, 
                    bm.jumlah, 
                    'Masuk' AS keterangan,
                    s.nama_supplier,
                    p.nama_lengkap AS operator
                FROM barang_masuk bm
                JOIN barang b ON bm.id_barang = b.id_barang
                LEFT JOIN supplier s ON bm.id_supplier = s.id_supplier
                JOIN pengguna p ON bm.id_pengguna = p.id_pengguna
                WHERE bm.tanggal_masuk BETWEEN ? AND ?
              )
              UNION ALL
              (
                SELECT 
                    'keluar' AS jenis, 
                    bk.id_keluar AS id_transaksi, 
                    bk.tanggal_keluar AS tanggal, 
                    b.kode_barang, 
                    b.nama_barang, 
                    bk.jumlah, 
                    CONCAT('Keluar - ', bk.keperluan) AS keterangan,
                    bk.penerima AS nama_supplier,
                    p.nama_lengkap AS operator
                FROM barang_keluar bk
                JOIN barang b ON bk.id_barang = b.id_barang
                JOIN pengguna p ON bk.id_pengguna = p.id_pengguna
                WHERE bk.tanggal_keluar BETWEEN ? AND ?
              )
              UNION ALL
              (
                SELECT 
                    'hilang' AS jenis, 
                    bh.id_hilang AS id_transaksi, 
                    bh.tanggal_hilang AS tanggal, 
                    b.kode_barang, 
                    b.nama_barang, 
                    bh.jumlah, 
                    CONCAT('Hilang - ', bh.keterangan) AS keterangan,
                    NULL AS nama_supplier,
                    p.nama_lengkap AS operator
                FROM barang_hilang bh
                JOIN barang b ON bh.id_barang = b.id_barang
                JOIN pengguna p ON bh.id_pengguna = p.id_pengguna
                WHERE bh.tanggal_hilang BETWEEN ? AND ?
              )";

    if (!empty($barang)) {
        $query = "(
                    SELECT 
                        'masuk' AS jenis, 
                        bm.id_masuk AS id_transaksi, 
                        bm.tanggal_masuk AS tanggal, 
                        b.kode_barang, 
                        b.nama_barang, 
                        bm.jumlah, 
                        'Masuk' AS keterangan,
                        s.nama_supplier,
                        p.nama_lengkap AS operator
                    FROM barang_masuk bm
                    JOIN barang b ON bm.id_barang = b.id_barang
                    LEFT JOIN supplier s ON bm.id_supplier = s.id_supplier
                    JOIN pengguna p ON bm.id_pengguna = p.id_pengguna
                    WHERE bm.tanggal_masuk BETWEEN ? AND ? AND bm.id_barang = ?
                  )
                  UNION ALL
                  (
                    SELECT 
                        'keluar' AS jenis, 
                        bk.id_keluar AS id_transaksi, 
                        bk.tanggal_keluar AS tanggal, 
                        b.kode_barang, 
                        b.nama_barang, 
                        bk.jumlah, 
                        CONCAT('Keluar - ', bk.keperluan) AS keterangan,
                        bk.penerima AS nama_supplier,
                        p.nama_lengkap AS operator
                    FROM barang_keluar bk
                    JOIN barang b ON bk.id_barang = b.id_barang
                    JOIN pengguna p ON bk.id_pengguna = p.id_pengguna
                    WHERE bk.tanggal_keluar BETWEEN ? AND ? AND bk.id_barang = ?
                  )
                  UNION ALL
                  (
                    SELECT 
                        'hilang' AS jenis, 
                        bh.id_hilang AS id_transaksi, 
                        bh.tanggal_hilang AS tanggal, 
                        b.kode_barang, 
                        b.nama_barang, 
                        bh.jumlah, 
                        CONCAT('Hilang - ', bh.keterangan) AS keterangan,
                        NULL AS nama_supplier,
                        p.nama_lengkap AS operator
                    FROM barang_hilang bh
                    JOIN barang b ON bh.id_barang = b.id_barang
                    JOIN pengguna p ON bh.id_pengguna = p.id_pengguna
                    WHERE bh.tanggal_hilang BETWEEN ? AND ? AND bh.id_barang = ?
                  )";
        $params = [
            $tanggal_awal,
            $tanggal_akhir,
            $barang,
            $tanggal_awal,
            $tanggal_akhir,
            $barang,
            $tanggal_awal,
            $tanggal_akhir,
            $barang
        ];
    } else {
        $params = [
            $tanggal_awal,
            $tanggal_akhir,
            $tanggal_awal,
            $tanggal_akhir,
            $tanggal_awal,
            $tanggal_akhir
        ];
    }

    $query .= " ORDER BY tanggal DESC";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Nama file untuk download
$filename = "Laporan_Transaksi_Barang_" . date('Ymd') . ".xls";

// Header untuk download file Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");

// Mulai output
echo "LAPORAN TRANSAKSI BARANG\n";
echo "SMP TERPADU BUGELAN\n";
echo "Periode: " . date('d/m/Y', strtotime($tanggal_awal)) . " - " . date('d/m/Y', strtotime($tanggal_akhir)) . "\n";
if (!empty($jenis)) {
    echo "Jenis Transaksi: " . ucfirst($jenis) . "\n";
}
echo "\n";

echo "No\tTanggal\tJenis\tKode Barang\tNama Barang\tJumlah\tKeterangan\tPemasok/Penerima\tOperator\n";

foreach ($transaksi as $key => $item) {
    echo ($key + 1) . "\t";
    echo date('d/m/Y', strtotime($item['tanggal'])) . "\t";
    echo ucfirst($item['jenis'] ?? $jenis) . "\t";
    echo $item['kode_barang'] . "\t";
    echo $item['nama_barang'] . "\t";
    echo $item['jumlah'] . "\t";
    echo $item['keterangan'] . "\t";
    echo ($item['nama_supplier'] ?? '-') . "\t";
    echo $item['operator'] . "\n";
}

exit();
