<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

// Set default timezone
date_default_timezone_set('Asia/Jakarta');

// Get filter parameters with proper validation
$jenis = isset($_GET['jenis']) && in_array($_GET['jenis'], ['masuk', 'keluar', 'hilang']) ? $_GET['jenis'] : '';
$barang = isset($_GET['barang']) && is_numeric($_GET['barang']) ? (int)$_GET['barang'] : 0;
$tanggal_awal = isset($_GET['tanggal_awal']) ? date('Y-m-d', strtotime($_GET['tanggal_awal'])) : date('Y-m-01');
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? date('Y-m-d', strtotime($_GET['tanggal_akhir'])) : date('Y-m-d');

// Validate date range
if ($tanggal_awal > $tanggal_akhir) {
    $tanggal_awal = $tanggal_akhir;
}

// Get all items for filter dropdown
$barang_options = $pdo->query("SELECT id_barang, kode_barang, nama_barang FROM barang ORDER BY nama_barang")->fetchAll();

// Build the query based on filters
$params = [];
$query = "";

if ($jenis == 'masuk') {
    $query = "SELECT 
                bm.id_masuk AS id_transaksi,
                bm.tanggal_masuk AS tanggal,
                b.kode_barang, 
                b.nama_barang,
                k.nama_kategori,
                COALESCE(s.nama_supplier, '-') AS pemasok_penerima,
                bm.jumlah,
                'Barang Masuk' AS jenis_transaksi,
                bm.no_faktur AS keterangan,
                p.nama_lengkap AS operator
              FROM barang_masuk bm
              JOIN barang b ON bm.id_barang = b.id_barang
              JOIN kategori k ON b.id_kategori = k.id_kategori
              LEFT JOIN supplier s ON bm.id_supplier = s.id_supplier
              JOIN pengguna p ON bm.id_pengguna = p.id_pengguna
              WHERE bm.tanggal_masuk BETWEEN ? AND ?";

    $params = [$tanggal_awal, $tanggal_akhir];

    if ($barang > 0) {
        $query .= " AND bm.id_barang = ?";
        $params[] = $barang;
    }

    $query .= " ORDER BY bm.tanggal_masuk DESC";
} elseif ($jenis == 'keluar') {
    $query = "SELECT 
                bk.id_keluar AS id_transaksi,
                bk.tanggal_keluar AS tanggal,
                b.kode_barang, 
                b.nama_barang,
                k.nama_kategori,
                COALESCE(bk.penerima, '-') AS pemasok_penerima,
                bk.jumlah,
                'Barang Keluar' AS jenis_transaksi,
                COALESCE(bk.keperluan, '-') AS keterangan,
                p.nama_lengkap AS operator
              FROM barang_keluar bk
              JOIN barang b ON bk.id_barang = b.id_barang
              JOIN kategori k ON b.id_kategori = k.id_kategori
              JOIN pengguna p ON bk.id_pengguna = p.id_pengguna
              WHERE bk.tanggal_keluar BETWEEN ? AND ?";

    $params = [$tanggal_awal, $tanggal_akhir];

    if ($barang > 0) {
        $query .= " AND bk.id_barang = ?";
        $params[] = $barang;
    }

    $query .= " ORDER BY bk.tanggal_keluar DESC";
} elseif ($jenis == 'hilang') {
    $query = "SELECT 
                bh.id_hilang AS id_transaksi,
                bh.tanggal_hilang AS tanggal,
                b.kode_barang, 
                b.nama_barang,
                k.nama_kategori,
                '-' AS pemasok_penerima,
                bh.jumlah,
                'Barang Hilang' AS jenis_transaksi,
                COALESCE(bh.keterangan, '-') AS keterangan,
                p.nama_lengkap AS operator
              FROM barang_hilang bh
              JOIN barang b ON bh.id_barang = b.id_barang
              JOIN kategori k ON b.id_kategori = k.id_kategori
              JOIN pengguna p ON bh.id_pengguna = p.id_pengguna
              WHERE bh.tanggal_hilang BETWEEN ? AND ?";

    $params = [$tanggal_awal, $tanggal_akhir];

    if ($barang > 0) {
        $query .= " AND bh.id_barang = ?";
        $params[] = $barang;
    }

    $query .= " ORDER BY bh.tanggal_hilang DESC";
} else {
    // Show all transaction types
    $query = "(SELECT 
                'masuk' AS jenis,
                bm.id_masuk AS id_transaksi,
                bm.tanggal_masuk AS tanggal,
                b.kode_barang, 
                b.nama_barang,
                k.nama_kategori,
                COALESCE(s.nama_supplier, '-') AS pemasok_penerima,
                bm.jumlah,
                'Barang Masuk' AS jenis_transaksi,
                COALESCE(bm.no_faktur, '-') AS keterangan,
                p.nama_lengkap AS operator
              FROM barang_masuk bm
              JOIN barang b ON bm.id_barang = b.id_barang
              JOIN kategori k ON b.id_kategori = k.id_kategori
              LEFT JOIN supplier s ON bm.id_supplier = s.id_supplier
              JOIN pengguna p ON bm.id_pengguna = p.id_pengguna
              WHERE bm.tanggal_masuk BETWEEN ? AND ?";

    $params = [$tanggal_awal, $tanggal_akhir];

    if ($barang > 0) {
        $query .= " AND bm.id_barang = ?";
        $params[] = $barang;
    }

    $query .= ")
              UNION ALL
              (SELECT 
                'keluar' AS jenis,
                bk.id_keluar AS id_transaksi,
                bk.tanggal_keluar AS tanggal,
                b.kode_barang, 
                b.nama_barang,
                k.nama_kategori,
                COALESCE(bk.penerima, '-') AS pemasok_penerima,
                bk.jumlah,
                'Barang Keluar' AS jenis_transaksi,
                COALESCE(bk.keperluan, '-') AS keterangan,
                p.nama_lengkap AS operator
              FROM barang_keluar bk
              JOIN barang b ON bk.id_barang = b.id_barang
              JOIN kategori k ON b.id_kategori = k.id_kategori
              JOIN pengguna p ON bk.id_pengguna = p.id_pengguna
              WHERE bk.tanggal_keluar BETWEEN ? AND ?";

    array_push($params, $tanggal_awal, $tanggal_akhir);

    if ($barang > 0) {
        $query .= " AND bk.id_barang = ?";
        $params[] = $barang;
    }

    $query .= ")
              UNION ALL
              (SELECT 
                'hilang' AS jenis,
                bh.id_hilang AS id_transaksi,
                bh.tanggal_hilang AS tanggal,
                b.kode_barang, 
                b.nama_barang,
                k.nama_kategori,
                '-' AS pemasok_penerima,
                bh.jumlah,
                'Barang Hilang' AS jenis_transaksi,
                COALESCE(bh.keterangan, '-') AS keterangan,
                p.nama_lengkap AS operator
              FROM barang_hilang bh
              JOIN barang b ON bh.id_barang = b.id_barang
              JOIN kategori k ON b.id_kategori = k.id_kategori
              JOIN pengguna p ON bh.id_pengguna = p.id_pengguna
              WHERE bh.tanggal_hilang BETWEEN ? AND ?";

    array_push($params, $tanggal_awal, $tanggal_akhir);

    if ($barang > 0) {
        $query .= " AND bh.id_barang = ?";
        $params[] = $barang;
    }

    $query .= ") ORDER BY tanggal DESC";
}

// Execute the query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate PDF report
require_once('../../vendor/tecnickcom/tcpdf/tcpdf.php');

class InventoryPDF extends TCPDF
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
        $this->SetX(66);
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

$pdf = new InventoryPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Laporan Transaksi Barang');
$pdf->SetMargins(15, 50, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 25);
$pdf->AddPage();

// Report title
$pdf->SetFont('times', 'B', 14);
$pdf->Cell(0, 10, 'LAPORAN TRANSAKSI BARANG', 0, 1, 'C');
$pdf->SetFont('times', '', 11);
$pdf->Cell(0, 6, 'Periode: ' . date('d F Y', strtotime($tanggal_awal)) . ' - ' . date('d F Y', strtotime($tanggal_akhir)), 0, 1, 'C');

// Filter information
$filterText = '';
if ($jenis) {
    $filterText .= 'Jenis: ' . ucfirst($jenis) . '; ';
}
if ($barang > 0) {
    $barang_name = '';
    foreach ($barang_options as $item) {
        if ($item['id_barang'] == $barang) {
            $barang_name = $item['nama_barang'];
            break;
        }
    }
    $filterText .= 'Barang: ' . $barang_name . '; ';
}

if (!empty($filterText)) {
    $pdf->Ln(2);
    $pdf->SetFont('times', '', 10);
    $pdf->writeHTML('<p style="font-style:italic;">' . rtrim($filterText, '; ') . '</p>', true, false, true, false, '');
}

// Transaction table
$pdf->Ln(3);
$pdf->SetFont('times', '', 10);

$html = '<table border="1" cellpadding="4" width="100%">
    <thead>
        <tr style="background-color:#f2f2f2;text-align:center;font-weight:bold;">
            <th width="5%">No</th>
            <th width="10%">Tanggal</th>
            <th width="10%">Kode</th>
            <th width="15%">Nama Barang</th>
            <th width="10%">Kategori</th>
            <th width="15%">Jenis Transaksi</th>
            <th width="10%">Pemasok/Penerima</th>
            <th width="10%">Jumlah</th>
            <th width="15%">Keterangan</th>
        </tr>
    </thead>
    <tbody>';

if (empty($transaksi)) {
    $html .= '<tr><td colspan="9" style="text-align:center;">Tidak ada data transaksi</td></tr>';
} else {
    foreach ($transaksi as $key => $item) {
        $html .= '<tr>
                <td width="5%" style="text-align:center;">' . ($key + 1) . '</td>
                <td width="10%">' . date('d/m/Y', strtotime($item['tanggal'])) . '</td>
                <td width="10%">' . htmlspecialchars($item['kode_barang']) . '</td>
                <td width="15%">' . htmlspecialchars($item['nama_barang']) . '</td>
                <td width="10%">' . htmlspecialchars($item['nama_kategori']) . '</td>
                <td width="15%">' . htmlspecialchars($item['jenis_transaksi']) . '</td>
                <td width="10%">' . htmlspecialchars($item['pemasok_penerima']) . '</td>
                <td width="10%" style="text-align:center;">' . $item['jumlah'] . '</td>
                <td width="15%">' . htmlspecialchars($item['keterangan']) . '</td>
            </tr>';
    }
}

$html .= '</tbody></table>';
$pdf->writeHTML($html, true, false, true, false, '');

// Signature section
$pdf->Ln(10);
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

// Output PDF
$filename = "Laporan_Transaksi_Barang_" . date('Ymd_His') . ".pdf";
$pdf->Output($filename, 'I');
