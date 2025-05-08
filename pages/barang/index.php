<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

$query = "SELECT b.*, k.nama_kategori, l.nama_lokasi 
          FROM barang b
          JOIN kategori k ON b.id_kategori = k.id_kategori
          JOIN lokasi l ON b.id_lokasi = l.id_lokasi
          ORDER BY b.nama_barang";
$stmt = $pdo->query($query);
$barang = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil parameter pencarian dan halaman
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
// Limit per page (default 100)
$limitOptions = [5, 10, 25, 50, 100];
$limit = isset($_GET['limit']) && in_array((int)$_GET['limit'], $limitOptions) ? (int)$_GET['limit'] : 10;

$offset = ($page - 1) * $limit;

// Query pencarian
// $where = '';
// $params = [];

// if (!empty($search)) {
//     $where = "WHERE b.nama_barang LIKE :search OR b.kode_barang LIKE :search";
//     $params[':search'] = "%$search%";
// }



// // Hitung total data
// $countQuery = "SELECT COUNT(*) FROM barang b 
//                JOIN kategori k ON b.id_kategori = k.id_kategori 
//                JOIN lokasi l ON b.id_lokasi = l.id_lokasi 
//                $where";
// $stmt = $pdo->prepare($countQuery);
// $stmt->execute($params);
// $totalRows = $stmt->fetchColumn();
// $totalPages = ceil($totalRows / $limit);

// // Ambil data barang
// $query = "SELECT b.*, k.nama_kategori, l.nama_lokasi 
//           FROM barang b 
//           JOIN kategori k ON b.id_kategori = k.id_kategori 
//           JOIN lokasi l ON b.id_lokasi = l.id_lokasi 
//           $where 
//           ORDER BY b.nama_barang 
//           LIMIT :limit OFFSET :offset";
// $stmt = $pdo->prepare($query);

// if (!empty($search)) {
//     $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
// }
// $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
// $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
// $stmt->execute();
// $barang = $stmt->fetchAll(PDO::FETCH_ASSOC);


// At the beginning of your PHP code where you handle filters
$selectedLokasi = isset($_GET['lokasi']) ? $_GET['lokasi'] : '';

// Modify your WHERE conditions
$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(b.nama_barang LIKE :search OR b.kode_barang LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($selectedLokasi)) {
    $where[] = "b.id_lokasi = :lokasi";
    $params[':lokasi'] = $selectedLokasi;
}

// if ($filter === 'stok_rendah') {
//     $where[] = "b.stok <= 5";
// } elseif ($filter === 'stok_kosong') {
//     $where[] = "b.stok <= 0";
// }

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Update your count query
$countQuery = "SELECT COUNT(*) FROM barang b 
               JOIN kategori k ON b.id_kategori = k.id_kategori 
               JOIN lokasi l ON b.id_lokasi = l.id_lokasi 
               $whereClause";
$stmt = $pdo->prepare($countQuery);
$stmt->execute($params);
$totalRows = $stmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// Update your main query
$query = "SELECT b.*, k.nama_kategori, l.nama_lokasi 
          FROM barang b 
          JOIN kategori k ON b.id_kategori = k.id_kategori 
          JOIN lokasi l ON b.id_lokasi = l.id_lokasi 
          $whereClause 
          ORDER BY b.nama_barang 
          LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($query);

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}

$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$barang = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Di bagian query barang
$filter = $_GET['filter'] ?? '';
$where = [];

if ($filter === 'stok_rendah') {
    $where[] = "b.stok <= 5"; // Sesuaikan dengan threshold
}

$query = "SELECT b.*, k.nama_kategori, l.nama_lokasi 
          FROM barang b
          JOIN kategori k ON b.id_kategori = k.id_kategori
          JOIN lokasi l ON b.id_lokasi = l.id_lokasi";

if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$query .= " ORDER BY b.nama_barang";
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

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2>Data Barang</h2>
                            <div>
                                <a href="tambah.php" class="btn btn-success m-1">
                                    <i class="bx bx-plus-circle"></i> Tambah Barang
                                </a>
                                <a href="../laporan/stok.php" class="btn btn-warning m-1">
                                    <i class="bx bx-file"></i> Laporan
                                </a>
                            </div>
                        </div>

                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
                        <?php endif; ?>

                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
                        <?php endif; ?>

                        <!-- <form method="GET" class="mb-3 row">
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
                        </form> -->



                        <!-- In the form section (after the existing search form) -->
                        <form method="GET" class="mb-3 row">
                            <div class="d-flex justify-content-between">

                                <!-- Add this new row for filters -->
                                <div class="col-md-2 mt-2">
                                    <select name="limit" class="form-select" onchange="this.form.submit()">
                                        <?php foreach ($limitOptions as $opt): ?>
                                            <option value="<?= $opt ?>" <?= $limit == $opt ? 'selected' : '' ?>>Tampilkan <?= $opt ?> data</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="row mt-2 m-1">
                                    <div class="col-md-5">
                                        <select name="lokasi" class="form-select" onchange="this.form.submit()">
                                            <option value="">Semua Lokasi</option>
                                            <?php
                                            // Query to get all locations
                                            $lokasiQuery = "SELECT * FROM lokasi ORDER BY nama_lokasi";
                                            $lokasiStmt = $pdo->query($lokasiQuery);
                                            $allLokasi = $lokasiStmt->fetchAll(PDO::FETCH_ASSOC);

                                            $selectedLokasi = isset($_GET['lokasi']) ? $_GET['lokasi'] : '';

                                            foreach ($allLokasi as $lokasi): ?>
                                                <option value="<?= $lokasi['id_lokasi'] ?>" <?= $selectedLokasi == $lokasi['id_lokasi'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($lokasi['nama_lokasi']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="input-group">
                                            <input type="text" name="search" class="form-control" placeholder="Cari barang..." value="<?= htmlspecialchars($search) ?>">
                                            <button class="btn btn-outline-primary me-1" type="submit">Cari</button>
                                            <a href="?limit=<?= $limit ?>" class="btn btn-outline-secondary">Reset</a>
                                        </div>
                                    </div>
                                </div>

                                <!-- <div class="col-md-4">
                                    <select name="filter" class="form-select" onchange="this.form.submit()">
                                        <option value="">Semua Stok</option>
                                        <option value="stok_rendah" <?= ($filter ?? '') == 'stok_rendah' ? 'selected' : '' ?>>Stok Rendah (≤5)</option>
                                        <option value="stok_kosong" <?= ($filter ?? '') == 'stok_kosong' ? 'selected' : '' ?>>Stok Kosong</option>
                                    </select>
                                </div> -->
                            </div>
                        </form>

                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <!-- Tabel barang -->
                                    <table id="tabelBarang" class="table table-striped table-hover table-bordered">
                                        <thead class="table-warning">
                                            <tr class="text-center">
                                                <th>No</th>
                                                <th>Kode</th>
                                                <!-- <th>Gambar</th> -->
                                                <th>Nama Barang</th>
                                                <th>Kategori</th>
                                                <th>Lokasi</th>
                                                <th>Stok</th>
                                                <th>Kondisi</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($barang)): ?>
                                                <tr>
                                                    <td colspan="8" class="text-center">Tidak ada data barang</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($barang as $key => $item): ?>
                                                    <tr>
                                                        <td><?= ($offset + $key + 1) ?></td>
                                                        <td><?= htmlspecialchars($item['kode_barang']) ?></td>
                                                        <!-- <td class="text-center">
                                                            <?php if (!empty($item['gambar'])): ?>
                                                                <img src="../../uploads/<?= htmlspecialchars($item['gambar']) ?>"
                                                                    alt="Gambar Barang"
                                                                    width="200" style="cursor: pointer; object-fit: cover;"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#imageModal"
                                                                    data-img-src="../../uploads/<?= htmlspecialchars($item['gambar']) ?>">
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td> -->
                                                        <td><?= htmlspecialchars($item['nama_barang']) ?></td>
                                                        <td><?= htmlspecialchars($item['nama_kategori']) ?></td>
                                                        <td><?= htmlspecialchars($item['nama_lokasi']) ?></td>
                                                        <td><?= htmlspecialchars($item['stok']) ?> <?= htmlspecialchars($item['satuan']) ?></td>
                                                        <td class="text-center">
                                                            <span class="badge bg-<?= $item['kondisi'] == 'baik' ? 'success' : ($item['kondisi'] == 'rusak_ringan' ? 'warning' : 'danger') ?>">
                                                                <?= ucfirst(htmlspecialchars($item['kondisi'])) ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                <a href="edit.php?id=<?= $item['id_barang'] ?>" class="btn btn-warning" title="Edit">
                                                                    <i class="bx bx-pencil"></i>
                                                                </a>
                                                                <a href="hapus.php?id=<?= $item['id_barang'] ?>"
                                                                    class="btn btn-danger"
                                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus barang ini?\n\nJika ada transaksi terkait, Anda akan diarahkan ke halaman verifikasi terlebih dahulu.')"
                                                                    title="Hapus">
                                                                    <i class="bx bx-trash"></i>
                                                                </a>
                                                            </div>
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
                                                    <a class="page-link" href="?search=<?= urlencode($search) ?>&limit=<?= $limit ?>&page=<?= $page - 1 ?>&lokasi=<?= $selectedLokasi ?>&filter=<?= $filter ?>">Sebelumnya</a>
                                                </li>
                                            <?php endif; ?>

                                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                                    <a class="page-link" href="?search=<?= urlencode($search) ?>&limit=<?= $limit ?>&page=<?= $i ?>&lokasi=<?= $selectedLokasi ?>&filter=<?= $filter ?>"><?= $i ?></a>
                                                </li>
                                            <?php endfor; ?>

                                            <?php if ($page < $totalPages): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?search=<?= urlencode($search) ?>&limit=<?= $limit ?>&page=<?= $page + 1 ?>&lokasi=<?= $selectedLokasi ?>&filter=<?= $filter ?>">Berikutnya</a>
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

    <!-- Modal Bootstrap -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <!-- Modal Header dengan tombol Close -->
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="imageModalLabel">Gambar Inventaris</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body text-center">
                    <img id="modalImage" src="" class="img-fluid rounded" alt="Gambar Barang">
                </div>
            </div>
        </div>
    </div>



    <!-- Core JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const imageModal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');

            imageModal.addEventListener('show.bs.modal', function(event) {
                const triggerImg = event.relatedTarget;
                const imgSrc = triggerImg.getAttribute('data-img-src');
                modalImage.setAttribute('src', imgSrc);
            });
        });
    </script>



    <?php include '../../includes/footer.php'; ?>