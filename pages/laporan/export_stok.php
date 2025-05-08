<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

// Ambil parameter filter
$kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$lokasi = isset($_GET['lokasi']) ? $_GET['lokasi'] : '';
$kondisi = isset($_GET['kondisi']) ? $_GET['kondisi'] : '';

// Query data stok dengan filter (sama dengan stok.php)
$query = "SELECT b.*, k.nama_kategori, l.nama_lokasi 
          FROM barang b
          JOIN kategori k ON b.id_kategori = k.id_kategori
          JOIN lokasi l ON b.id_lokasi = l.id_lokasi";

$where = [];
$params = [];

if (!empty($kategori)) {
    $where[] = "b.id_kategori = ?";
    $params[] = $kategori;
}

if (!empty($lokasi)) {
    $where[] = "b.id_lokasi = ?";
    $params[] = $lokasi;
}

if (!empty($kondisi)) {
    $where[] = "b.kondisi = ?";
    $params[] = $kondisi;
}

if (count($where) > 0) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$query .= " ORDER BY b.nama_barang";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$barang = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Nama file untuk download
$filename = "Laporan_Stok_Barang_" . date('Ymd') . ".xls";

// Header untuk download file Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");

// Mulai output
echo "LAPORAN STOK BARANG\n";
echo "SMP TERPADU BUGELAN\n";
echo "Periode: " . date('d F Y') . "\n\n";

echo "No\tKode Barang\tNama Barang\tKategori\tLokasi\tStok\tSatuan\tKondisi\n";

foreach ($barang as $key => $item) {
    $kondisi = ucfirst($item['kondisi']);

    echo ($key + 1) . "\t";
    echo $item['kode_barang'] . "\t";
    echo $item['nama_barang'] . "\t";
    echo $item['nama_kategori'] . "\t";
    echo $item['nama_lokasi'] . "\t";
    echo $item['stok'] . "\t";
    echo $item['satuan'] . "\t";
    echo $kondisi . "\n";
}

exit();
