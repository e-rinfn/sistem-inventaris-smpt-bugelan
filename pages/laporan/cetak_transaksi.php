<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

// Ambil parameter filter
$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
$barang = isset($_GET['barang']) ? $_GET['barang'] : '';
$tanggal_awal = isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : date('Y-m-01');
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : date('Y-m-d');

// Query untuk data transaksi
if ($jenis == 'masuk') {
    $query = "SELECT 
                bm.*, 
                b.kode_barang, 
                b.nama_barang, 
                s.nama_supplier, 
                p.nama_lengkap AS operator,
                bm.tanggal_masuk AS tanggal  -- Tambahkan alias untuk kolom tanggal
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
                p.nama_lengkap AS operator,
                bk.tanggal_keluar AS tanggal  -- Tambahkan alias untuk kolom tanggal
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
                p.nama_lengkap AS operator,
                bh.tanggal_hilang AS tanggal  -- Tambahkan alias untuk kolom tanggal
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
$filename = "Laporan_Transaksi_Barang_" . date('Ymd') . ".pdf";

// Include TCPDF
require_once('../../vendor/tecnickcom/tcpdf/tcpdf.php');

class MYPDF extends TCPDF
{
    public function Header()
    {
        $this->SetFont('times', '', 10);
        $image_file = '../../assets/img/Logo.png';
        if (file_exists($image_file)) {
            $this->Image($image_file, 15, 10, 20, '', 'PNG');
        }

        $this->SetXY(40, 10);
        $this->SetFont('times', 'B', 12);
        $this->SetX(66); // 50% dari lebar halaman A4
        $this->Cell(0, 5, 'PEMERINTAH KOTA TASIKMALAYA', 0, 1, 'L');
        $this->Cell(0, 5, 'DINAS PENDIDIKAN', 0, 1, 'C');
        $this->SetFont('times', 'B', 14);
        $this->Cell(0, 6, 'SMP TERPADU BUGELAN', 0, 1, 'C');
        $this->SetFont('times', '', 10);
        $this->Cell(0, 5, 'Jl. Raya Bugelan No. 123, Kota Tasikmalaya, Jawa Barat', 0, 1, 'C');
        $this->Cell(0, 5, 'Telp. (0265) 7654321 | Email: smpterpadubugelan@example.com', 0, 1, 'C');
        $this->Line(10, 42, 200, 42);
        $this->Ln(5);
    }

    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('times', 'I', 8);
        $this->Cell(0, 10, 'Halaman ' . $this->getAliasNumPage() . ' dari ' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Laporan Transaksi Barang');
$pdf->SetMargins(15, 50, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 25);
$pdf->AddPage();

$pdf->SetFont('times', 'B', 14);
$pdf->Cell(0, 10, 'LAPORAN TRANSAKSI BARANG', 0, 1, 'C');
$pdf->SetFont('times', '', 11);
$pdf->Cell(0, 6, 'Periode: ' . date('d F Y'), 0, 1, 'C');

// Informasi filter
$filterText = '';
if ($jenis || $barang || $tanggal_awal || $tanggal_akhir) {
    $filterText .= '<b>Filter:</b> ';
    if ($jenis) $filterText .= 'Jenis: ' . ucfirst($jenis) . '; ';
    if ($barang) $filterText .= 'Barang: ' . $barang . '; ';
    if ($tanggal_awal) $filterText .= 'Tanggal Awal: ' . date('d F Y', strtotime($tanggal_awal)) . '; ';
    if ($tanggal_akhir) $filterText .= 'Tanggal Akhir: ' . date('d F Y', strtotime($tanggal_akhir)) . '; ';
}

if (!empty($filterText)) {
    $pdf->Ln(2);
    $pdf->SetFont('times', '', 10);
    $pdf->writeHTML('<p style="font-style:italic;">' . rtrim($filterText, '; ') . '</p>', true, false, true, false, '');
}


$pdf->Ln(3);
$pdf->SetFont('times', '', 10);

$html = '<table border="1" cellpadding="4" width="100%">
    <thead>
        <tr style="background-color:#f2f2f2;text-align:center;font-weight:bold;">
            <th width="5%">No</th>
            <th width="15%">Kode</th>
            <th width="25%">Nama Barang</th>
            <th width="15%">Kategori</th>
            <th width="15%">Lokasi</th>
            <th width="10%">Stok</th>
            <th width="15%">Kondisi</th>
        </tr>
    </thead>
    <tbody>';

if (empty($transaksi)) {
    $html .= '<tr><td colspan="7" style="text-align:center;">Tidak ada data stok barang</td></tr>';
} else {
    foreach ($transaksi as $key => $item) {
        $html .= '<tr>
                <td width="5%" style="text-align:center;">' . ($key + 1) . '</td>
                <td width="15%">' . htmlspecialchars($item['kode_barang']) . '</td>
                <td width="25%">' . htmlspecialchars($item['nama_barang']) . '</td>
                <td width="15%">' . htmlspecialchars($item['jenis'] ?? '-') . '</td>
                <td width="15%">' . htmlspecialchars($item['nama_supplier'] ?? '-') . '</td>
                <td width="10%" style="text-align:center;">' . $item['jumlah'] . '</td>
                <td width="15%">' . htmlspecialchars($item['keterangan'] ?? '-') . '</td>
            </tr>';
    }
}



$html .= '</tbody></table>';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Ln(10);

// Tanda tangan
$pdf->Cell(0, 0, 'Tasikmalaya, ' . date('d F Y'), 0, 1, 'R');
$pdf->Ln(10);
$pdf->SetFont('times', 'B', 10);
$pdf->Cell(0, 0, 'Kepala Sekolah', 0, 1, 'R');
$pdf->Ln(15);
$pdf->SetFont('times', 'BU', 10);
$pdf->Cell(0, 0, 'Nama Kepala Sekolah', 0, 1, 'R');
$pdf->Ln(5);
$pdf->SetFont('times', '', 10);
$pdf->Cell(0, 0, 'NIP. 1234567890', 0, 1, 'R');

$pdf->Output($filename, 'I');
