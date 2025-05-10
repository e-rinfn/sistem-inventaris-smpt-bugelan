<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

// Ambil parameter filter
$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
$tanggal_awal = isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : date('Y-m-01');
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : date('Y-m-d');
$barang = isset($_GET['barang']) ? $_GET['barang'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

// Query untuk data transaksi
// Query untuk data transaksi
$params = [];
$query = "";

if ($jenis == 'masuk') {
    $query = "SELECT 
                bm.*, 
                b.kode_barang, 
                b.nama_barang, 
                COALESCE(s.nama_supplier, '-') AS nama_supplier, 
                p.nama_lengkap AS operator,
                bm.tanggal_masuk AS tanggal,
                'masuk' AS jenis
              FROM barang_masuk bm
              JOIN barang b ON bm.id_barang = b.id_barang
              LEFT JOIN supplier s ON bm.id_supplier = s.id_supplier
              JOIN pengguna p ON bm.id_pengguna = p.id_pengguna
              WHERE bm.tanggal_masuk BETWEEN ? AND ?";

    $params = [$tanggal_awal, $tanggal_akhir];

    if (!empty($barang)) {
        $query .= " AND bm.id_barang = ?";
        $params[] = $barang;
    }

    $query .= " ORDER BY bm.tanggal_masuk DESC";
} elseif ($jenis == 'keluar') {
    $query = "SELECT 
                bk.*, 
                b.kode_barang, 
                b.nama_barang, 
                COALESCE(bk.penerima, '-') AS nama_supplier, 
                p.nama_lengkap AS operator,
                bk.tanggal_keluar AS tanggal,
                'keluar' AS jenis
              FROM barang_keluar bk
              JOIN barang b ON bk.id_barang = b.id_barang
              JOIN pengguna p ON bk.id_pengguna = p.id_pengguna
              WHERE bk.tanggal_keluar BETWEEN ? AND ?";

    $params = [$tanggal_awal, $tanggal_akhir];

    if (!empty($barang)) {
        $query .= " AND bk.id_barang = ?";
        $params[] = $barang;
    }

    $query .= " ORDER BY bk.tanggal_keluar DESC";
} elseif ($jenis == 'hilang') {
    $query = "SELECT 
                bh.*, 
                b.kode_barang, 
                b.nama_barang, 
                '-' AS nama_supplier, 
                p.nama_lengkap AS operator,
                bh.tanggal_hilang AS tanggal,
                'hilang' AS jenis
              FROM barang_hilang bh
              JOIN barang b ON bh.id_barang = b.id_barang
              JOIN pengguna p ON bh.id_pengguna = p.id_pengguna
              WHERE bh.tanggal_hilang BETWEEN ? AND ?";

    $params = [$tanggal_awal, $tanggal_akhir];

    if (!empty($barang)) {
        $query .= " AND bh.id_barang = ?";
        $params[] = $barang;
    }

    $query .= " ORDER BY bh.tanggal_hilang DESC";
} else {
    // Default: tampilkan semua jenis transaksi
    $query = "(
                SELECT 
                    bm.id_masuk AS id_transaksi, 
                    bm.tanggal_masuk AS tanggal, 
                    b.kode_barang, 
                    b.nama_barang, 
                    bm.jumlah, 
                    'Masuk' AS keterangan,
                    COALESCE(s.nama_supplier, '-') AS nama_supplier,
                    p.nama_lengkap AS operator,
                    'masuk' AS jenis
                FROM barang_masuk bm
                JOIN barang b ON bm.id_barang = b.id_barang
                LEFT JOIN supplier s ON bm.id_supplier = s.id_supplier
                JOIN pengguna p ON bm.id_pengguna = p.id_pengguna
                WHERE bm.tanggal_masuk BETWEEN ? AND ?
              )
              UNION ALL
              (
                SELECT 
                    bk.id_keluar AS id_transaksi, 
                    bk.tanggal_keluar AS tanggal, 
                    b.kode_barang, 
                    b.nama_barang, 
                    bk.jumlah, 
                    CONCAT('Keluar - ', COALESCE(bk.keperluan, '-')) AS keterangan,
                    COALESCE(bk.penerima, '-') AS nama_supplier,
                    p.nama_lengkap AS operator,
                    'keluar' AS jenis
                FROM barang_keluar bk
                JOIN barang b ON bk.id_barang = b.id_barang
                JOIN pengguna p ON bk.id_pengguna = p.id_pengguna
                WHERE bk.tanggal_keluar BETWEEN ? AND ?
              )
              UNION ALL
              (
                SELECT 
                    bh.id_hilang AS id_transaksi, 
                    bh.tanggal_hilang AS tanggal, 
                    b.kode_barang, 
                    b.nama_barang, 
                    bh.jumlah, 
                    CONCAT('Hilang - ', COALESCE(bh.keterangan, '-')) AS keterangan,
                    '-' AS nama_supplier,
                    p.nama_lengkap AS operator,
                    'hilang' AS jenis
                FROM barang_hilang bh
                JOIN barang b ON bh.id_barang = b.id_barang
                JOIN pengguna p ON bh.id_pengguna = p.id_pengguna
                WHERE bh.tanggal_hilang BETWEEN ? AND ?
              )";

    $params = [
        $tanggal_awal,
        $tanggal_akhir,
        $tanggal_awal,
        $tanggal_akhir,
        $tanggal_awal,
        $tanggal_akhir
    ];

    if (!empty($barang)) {
        $query = "(
                    SELECT 
                        bm.id_masuk AS id_transaksi, 
                        bm.tanggal_masuk AS tanggal, 
                        b.kode_barang, 
                        b.nama_barang, 
                        bm.jumlah, 
                        'Masuk' AS keterangan,
                        COALESCE(s.nama_supplier, '-') AS nama_supplier,
                        p.nama_lengkap AS operator,
                        'masuk' AS jenis
                    FROM barang_masuk bm
                    JOIN barang b ON bm.id_barang = b.id_barang
                    LEFT JOIN supplier s ON bm.id_supplier = s.id_supplier
                    JOIN pengguna p ON bm.id_pengguna = p.id_pengguna
                    WHERE bm.tanggal_masuk BETWEEN ? AND ? AND bm.id_barang = ?
                  )
                  UNION ALL
                  (
                    SELECT 
                        bk.id_keluar AS id_transaksi, 
                        bk.tanggal_keluar AS tanggal, 
                        b.kode_barang, 
                        b.nama_barang, 
                        bk.jumlah, 
                        CONCAT('Keluar - ', COALESCE(bk.keperluan, '-')) AS keterangan,
                        COALESCE(bk.penerima, '-') AS nama_supplier,
                        p.nama_lengkap AS operator,
                        'keluar' AS jenis
                    FROM barang_keluar bk
                    JOIN barang b ON bk.id_barang = b.id_barang
                    JOIN pengguna p ON bk.id_pengguna = p.id_pengguna
                    WHERE bk.tanggal_keluar BETWEEN ? AND ? AND bk.id_barang = ?
                  )
                  UNION ALL
                  (
                    SELECT 
                        bh.id_hilang AS id_transaksi, 
                        bh.tanggal_hilang AS tanggal, 
                        b.kode_barang, 
                        b.nama_barang, 
                        bh.jumlah, 
                        CONCAT('Hilang - ', COALESCE(bh.keterangan, '-')) AS keterangan,
                        '-' AS nama_supplier,
                        p.nama_lengkap AS operator,
                        'hilang' AS jenis
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
    }

    $query .= " ORDER BY tanggal DESC";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil data barang untuk dropdown filter
$barang_list = $pdo->query("SELECT id_barang, kode_barang, nama_barang FROM barang ORDER BY nama_barang")->fetchAll();
?>

<?php include '../../includes/header.php'; ?>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            <!-- Menu -->

            <?php include '../../includes/sidebar.php'; ?>

            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">

                <!-- Navbar -->

                <?php include '../../includes/navbar.php'; ?>


                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->

                    <div class="container-xxl flex-grow-1 container-p-y">

                        <h2>Laporan Transaksi Barang</h2>

                        <div class="card mb-4">
                            <div class="card-header bg-warning text-white">
                                <i class='bx bx-filter-alt'></i> Filter Laporan
                            </div>
                            <div class="card-body mt-3">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-2">
                                        <label for="jenis" class="form-label">Jenis Transaksi</label>
                                        <select class="form-select" id="jenis" name="jenis">
                                            <option value="">Semua Jenis</option>
                                            <option value="masuk" <?= $jenis == 'masuk' ? 'selected' : '' ?>>Barang Masuk</option>
                                            <option value="keluar" <?= $jenis == 'keluar' ? 'selected' : '' ?>>Barang Keluar</option>
                                            <option value="hilang" <?= $jenis == 'hilang' ? 'selected' : '' ?>>Barang Hilang</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="barang" class="form-label">Barang</label>
                                        <select class="form-select" id="barang" name="barang">
                                            <option value="">Semua Barang</option>
                                            <?php foreach ($barang_list as $item): ?>
                                                <option value="<?= $item['id_barang'] ?>" <?= $item['id_barang'] == $barang ? 'selected' : '' ?>>
                                                    <?= $item['kode_barang'] ?> - <?= $item['nama_barang'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
                                        <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal"
                                            value="<?= $tanggal_awal ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
                                        <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir"
                                            value="<?= $tanggal_akhir ?>">
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class='bx bx-filter-alt'></i> Filter
                                        </button>
                                        <a href="transaksi.php" class="btn btn-secondary ms-2">
                                            <i class='bx bx-reset'></i> Reset
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>


                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3>Daftar Transaksi</h3>
                                <div>
                                    <a href="cetak_transaksi.php?jenis=<?= $jenis ?>&barang=<?= $barang ?>&tanggal_awal=<?= $tanggal_awal ?>&tanggal_akhir=<?= $tanggal_akhir ?>"
                                        class="btn btn-sm btn-danger" target="_blank">
                                        <i class="bx bxs-file-pdf fs-3"></i> Cetak
                                    </a>
                                    <a href="export_transaksi.php?jenis=<?= $jenis ?>&barang=<?= $barang ?>&tanggal_awal=<?= $tanggal_awal ?>&tanggal_akhir=<?= $tanggal_akhir ?>"
                                        class="btn btn-sm btn-success ms-2">
                                        <i class="bx bxs-file fs-3"></i> Excel
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead class="table-warning">
                                            <tr>
                                                <th>#</th>
                                                <th>Tanggal</th>
                                                <th>Jenis</th>
                                                <th>Kode Barang</th>
                                                <th>Nama Barang</th>
                                                <th>Jumlah</th>
                                                <th>Keterangan</th>
                                                <th>Pemasok/Penerima</th>
                                                <th>Operator</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($transaksi)): ?>
                                                <tr>
                                                    <td colspan="9" class="text-center">Tidak ada data transaksi ditemukan</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($transaksi as $key => $item): ?>
                                                    <tr>
                                                        <td><?= $key + 1 ?></td>
                                                        <td><?= date('d/m/Y', strtotime($item['tanggal'])) ?></td>
                                                        <td>
                                                            <span class="badge bg-<?=
                                                                                    $item['jenis'] == 'masuk' ? 'success' : ($item['jenis'] == 'keluar' ? 'warning' : 'danger')
                                                                                    ?>">
                                                                <?= ucfirst($item['jenis']) ?>
                                                            </span>
                                                        </td>
                                                        <td><?= $item['kode_barang'] ?></td>
                                                        <td><?= $item['nama_barang'] ?></td>
                                                        <td><?= $item['jumlah'] ?></td>
                                                        <td><?= $item['keterangan'] ?></td>
                                                        <td><?= $item['nama_supplier'] ?></td>
                                                        <td><?= $item['operator'] ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>

                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- / Content -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->


    <?php include '../../includes/footer.php'; ?>