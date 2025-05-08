<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

// Ambil parameter filter
$kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$lokasi = isset($_GET['lokasi']) ? $_GET['lokasi'] : '';
$kondisi = isset($_GET['kondisi']) ? $_GET['kondisi'] : '';

// Query data stok dengan filter
$query = "SELECT b.*, k.nama_kategori, l.nama_lokasi 
          FROM barang b
          JOIN kategori k ON b.id_kategori = k.id_kategori
          JOIN lokasi l ON b.id_lokasi = l.id_lokasi";

$where = [];
$params = [];

$kategoriNama = '';
$lokasiNama = '';
$kondisiNama = '';

if (!empty($kategori)) {
    $stmtKategori = $pdo->prepare("SELECT nama_kategori FROM kategori WHERE id_kategori = ?");
    $stmtKategori->execute([$kategori]);
    $rowKategori = $stmtKategori->fetch();
    $kategoriNama = $rowKategori ? $rowKategori['nama_kategori'] : $kategori;
}

if (!empty($lokasi)) {
    $stmtLokasi = $pdo->prepare("SELECT nama_lokasi FROM lokasi WHERE id_lokasi = ?");
    $stmtLokasi->execute([$lokasi]);
    $rowLokasi = $stmtLokasi->fetch();
    $lokasiNama = $rowLokasi ? $rowLokasi['nama_lokasi'] : $lokasi;
}

if (!empty($kondisi)) {
    switch ($kondisi) {
        case 'baik':
            $kondisiNama = 'Baik';
            break;
        case 'rusak_ringan':
            $kondisiNama = 'Rusak Ringan';
            break;
        case 'rusak_berat':
            $kondisiNama = 'Rusak Berat';
            break;
        default:
            $kondisiNama = ucfirst($kondisi);
    }
}


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

// Nama file
$filename = "Laporan_Stok_Barang_" . date('Ymd') . ".pdf";

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
$pdf->SetTitle('Laporan Stok Barang');
$pdf->SetMargins(15, 50, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 25);
$pdf->AddPage();

$pdf->SetFont('times', 'B', 14);
$pdf->Cell(0, 10, 'LAPORAN STOK BARANG', 0, 1, 'C');
$pdf->SetFont('times', '', 11);
$pdf->Cell(0, 6, 'Periode: ' . date('d F Y'), 0, 1, 'C');

// Informasi filter
$filterText = '';
if ($kategoriNama || $lokasiNama || $kondisiNama) {
    $filterText .= '<b>Filter:</b> ';
    if ($kategoriNama) $filterText .= 'Kategori: ' . $kategoriNama . '; ';
    if ($lokasiNama) $filterText .= 'Lokasi: ' . $lokasiNama . '; ';
    if ($kondisiNama) $filterText .= 'Kondisi: ' . $kondisiNama . '; ';
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

if (empty($barang)) {
    $html .= '<tr><td colspan="7" style="text-align:center;">Tidak ada data stok barang</td></tr>';
} else {
    foreach ($barang as $key => $item) {
        $html .= '<tr>
            <td width="5%" style="text-align:center;">' . ($key + 1) . '</td>
            <td width="15%" >' . $item['kode_barang'] . '</td>
            <td width="25%" >' . $item['nama_barang'] . '</td>
            <td width="15%" >' . $item['nama_kategori'] . '</td>
            <td width="15%" >' . $item['nama_lokasi'] . '</td>
            <td width="10%" style="text-align:center;">' . $item['stok'] . ' ' . $item['satuan'] . '</td>
            <td width="15%" style="text-align:center;">' . ucfirst(str_replace('_', ' ', $item['kondisi'])) . '</td>
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
