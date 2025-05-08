<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

// Ambil parameter filter jika ada
$kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$lokasi = isset($_GET['lokasi']) ? $_GET['lokasi'] : '';
$kondisi = isset($_GET['kondisi']) ? $_GET['kondisi'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$limitOptions = [5, 10, 25, 50, 100];
$limit = isset($_GET['limit']) && in_array((int)$_GET['limit'], $limitOptions) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Query dasar
$query = "SELECT b.*, k.nama_kategori, l.nama_lokasi 
          FROM barang b
          JOIN kategori k ON b.id_kategori = k.id_kategori
          JOIN lokasi l ON b.id_lokasi = l.id_lokasi";

// Tambahkan filter jika ada
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

if (!empty($search)) {
    $where[] = "(b.nama_barang LIKE ? OR b.kode_barang LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$totalQuery = "SELECT COUNT(*) FROM barang b
               JOIN kategori k ON b.id_kategori = k.id_kategori
               JOIN lokasi l ON b.id_lokasi = l.id_lokasi";
if (!empty($where)) {
    $totalQuery .= " WHERE " . implode(" AND ", $where);
}

$totalStmt = $pdo->prepare($totalQuery);
$totalStmt->execute($params);
$totalRows = $totalStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// Tambahkan LIMIT dan OFFSET ke query utama
$query .= " ORDER BY b.nama_barang LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$barang = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil data untuk filter
$kategori_list = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();
$lokasi_list = $pdo->query("SELECT * FROM lokasi ORDER BY nama_lokasi")->fetchAll();
?>

<!-- Include Header -->
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

                        <h2>Laporan Stok Barang</h2>


                        <div class="card mb-4">
                            <div class="card-header bg-warning text-white">
                                <i class='bx bx-filter-alt'></i> Filter Laporan
                            </div>
                            <div class="card-body mt-3">

                                <form method="GET" class="row g-3">
                                    <div class="col-md-2">
                                        <label for="kategori" class="form-label">Kategori</label>
                                        <select class="form-select" id="kategori" name="kategori">
                                            <option value="">Semua Kategori</option>
                                            <?php foreach ($kategori_list as $item): ?>
                                                <option value="<?= $item['id_kategori'] ?>" <?= $item['id_kategori'] == $kategori ? 'selected' : '' ?>>
                                                    <?= $item['nama_kategori'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="lokasi" class="form-label">Lokasi</label>
                                        <select class="form-select" id="lokasi" name="lokasi">
                                            <option value="">Semua Lokasi</option>
                                            <?php foreach ($lokasi_list as $item): ?>
                                                <option value="<?= $item['id_lokasi'] ?>" <?= $item['id_lokasi'] == $lokasi ? 'selected' : '' ?>>
                                                    <?= $item['nama_lokasi'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="kondisi" class="form-label">Kondisi</label>
                                        <select class="form-select" id="kondisi" name="kondisi">
                                            <option value="">Semua Kondisi</option>
                                            <option value="baik" <?= $kondisi == 'baik' ? 'selected' : '' ?>>Baik</option>
                                            <option value="rusak_ringan" <?= $kondisi == 'rusak_ringan' ? 'selected' : '' ?>>Rusak Ringan</option>
                                            <option value="rusak_berat" <?= $kondisi == 'rusak_berat' ? 'selected' : '' ?>>Rusak Berat</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class='bx bx-filter-alt'></i> Filter
                                        </button>
                                        <a href="stok.php" class="btn btn-secondary ms-2">
                                            <i class='bx bx-reset'></i> Reset
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3>Daftar Stok Barang</h3>
                                <div>
                                    <a href="cetak_stok.php?kategori=<?= $kategori ?>&lokasi=<?= $lokasi ?>&kondisi=<?= $kondisi ?>"
                                        class="btn btn-sm btn-danger" target="_blank">
                                        <i class='bx bxs-file-pdf fs-3'></i> Cetak
                                    </a>
                                    <a href="export_stok.php?kategori=<?= $kategori ?>&lokasi=<?= $lokasi ?>&kondisi=<?= $kondisi ?>"
                                        class="btn btn-sm btn-success ms-2">
                                        <i class="bx bxs-file fs-3"></i> Excel
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <form method="GET" class="mb-3 row">
                                        <div class="d-flex justify-content-between">
                                            <div class="col-md-3">
                                                <select name="limit" class="form-select" onchange="this.form.submit()">
                                                    <?php foreach ($limitOptions as $opt): ?>
                                                        <option value="<?= $opt ?>" <?= $limit == $opt ? 'selected' : '' ?>>Tampilkan <?= $opt ?> data</option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="input-group">
                                                    <input type="text" name="search" class="form-control" placeholder="Cari barang..." value="<?= htmlspecialchars($search) ?>">
                                                    <button class="btn btn-outline-primary me-1" type="submit">Cari</button>
                                                    <a href="?limit=<?= $limit ?>" class="btn btn-outline-secondary">Reset</a>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    <table class="table table-striped table-bordered">
                                        <thead class="table-warning">
                                            <tr>
                                                <th>No</th>
                                                <th>Kode</th>
                                                <th>Nama Barang</th>
                                                <th>Kategori</th>
                                                <th>Lokasi</th>
                                                <th>Stok</th>
                                                <th>Satuan</th>
                                                <th>Kondisi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($barang)): ?>
                                                <tr>
                                                    <td colspan="8" class="text-center">Tidak ada data ditemukan</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($barang as $key => $item): ?>
                                                    <tr>
                                                        <td><?= ($offset + $key + 1) ?></td>
                                                        <td><?= $item['kode_barang'] ?></td>
                                                        <td><?= $item['nama_barang'] ?></td>
                                                        <td><?= $item['nama_kategori'] ?></td>
                                                        <td><?= $item['nama_lokasi'] ?></td>
                                                        <td><?= $item['stok'] ?></td>
                                                        <td><?= $item['satuan'] ?></td>
                                                        <td>
                                                            <span class="badge bg-<?=
                                                                                    $item['kondisi'] == 'baik' ? 'success' : ($item['kondisi'] == 'rusak_ringan' ? 'warning' : 'danger')
                                                                                    ?>">
                                                                <?= ucfirst($item['kondisi']) ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>


                                    <nav class="mt-3">
                                        <ul class="pagination justify-content-end">
                                            <?php if ($page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?search=<?= urlencode($search) ?>&limit=<?= $limit ?>&page=<?= $page - 1 ?>">Sebelumnya</a>
                                                </li>
                                            <?php endif; ?>

                                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                                    <a class="page-link" href="?search=<?= urlencode($search) ?>&limit=<?= $limit ?>&page=<?= $i ?>"><?= $i ?></a>
                                                </li>
                                            <?php endfor; ?>

                                            <?php if ($page < $totalPages): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?search=<?= urlencode($search) ?>&limit=<?= $limit ?>&page=<?= $page + 1 ?>">Berikutnya</a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>

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