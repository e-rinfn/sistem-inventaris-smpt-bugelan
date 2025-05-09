<?php
require_once '../../../includes/auth_check.php';
require_once '../../../config/database.php';

// Query data barang hilang
$query = "SELECT bh.*, b.nama_barang, b.kode_barang, p.nama_lengkap 
          FROM barang_hilang bh
          JOIN barang b ON bh.id_barang = b.id_barang
          JOIN pengguna p ON bh.id_pengguna = p.id_pengguna
          ORDER BY bh.tanggal_hilang DESC, bh.dibuat_pada DESC";
$stmt = $pdo->query($query);
$barang_hilang = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="../../../assets/" data-template="vertical-menu-template-free">

<!-- Header start -->

<?php include '../header.php'; ?>

<!-- Header end -->

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            <!-- Sidebar -->
            <?php include '../../../includes/sidebar.php'; ?>
            <!-- /Sidebar -->

            <div class="layout-page">

                <!-- Navbar -->
                <?php include '../../../includes/navbar.php'; ?>
                <!-- /Navbar -->

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2>Laporan Barang Hilang</h2>
                            <a href="tambah.php" class="btn btn-primary">
                                <i class="bx bx-plus-circle"></i> Tambah Laporan
                            </a>
                        </div>

                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
                        <?php endif; ?>

                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
                        <?php endif; ?>

                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Tanggal</th>
                                                <th>Barang</th>
                                                <th>Jumlah</th>
                                                <th>Pelapor</th>
                                                <th>Status</th>
                                                <th>Keterangan</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($barang_hilang as $key => $item): ?>
                                                <tr>
                                                    <td><?= $key + 1 ?></td>
                                                    <td><?= date('d/m/Y', strtotime($item['tanggal_hilang'])) ?></td>
                                                    <td><?= htmlspecialchars($item['kode_barang']) ?> - <?= htmlspecialchars($item['nama_barang']) ?></td>
                                                    <td><?= $item['jumlah'] ?></td>
                                                    <td><?= htmlspecialchars($item['nama_lengkap']) ?></td>
                                                    <td>
                                                        <span class="badge bg-<?=
                                                                                $item['status'] == 'dilaporkan' ? 'warning' : ($item['status'] == 'ditemukan' ? 'success' : ($item['status'] == 'dikembalikan' ? 'info' : 'secondary'))
                                                                                ?>">
                                                            <?= ucfirst($item['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= htmlspecialchars(substr($item['keterangan'], 0, 50)) . (strlen($item['keterangan']) > 50 ? '...' : '') ?></td>
                                                    <td>
                                                        <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                <?php if ($item['status'] == 'dilaporkan'): ?>
                                                                    <a href="update_status.php?id=<?= $item['id_hilang'] ?>&status=ditemukan"
                                                                        class="btn btn-success"
                                                                        title="Tandai ditemukan"
                                                                        onclick="return confirm('Apakah Anda yakin ingin menandai barang ini sebagai ditemukan?')">
                                                                        <i class="bx bx-check-circle"></i>
                                                                    </a>
                                                                <?php endif; ?>

                                                                <a href="update_status.php?id=<?= $item['id_hilang'] ?>&status=ditutup"
                                                                    class="btn btn-secondary"
                                                                    title="Tutup laporan"
                                                                    onclick="return confirm('Apakah Anda yakin ingin menutup laporan ini?')">
                                                                    <i class="bx bx-x-circle"></i>
                                                                </a>

                                                                <!-- Tombol Hapus -->
                                                                <a href="hapus.php?id=<?= $item['id_hilang'] ?>"
                                                                    class="btn btn-danger"
                                                                    title="Hapus laporan"
                                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus laporan ini? Data yang dihapus tidak dapat dikembalikan.')">
                                                                    <i class="bx bx-trash"></i>
                                                                </a>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>

                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>

        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    <!-- Core JS -->
    <script src="../../../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../../../assets/vendor/libs/popper/popper.js"></script>
    <script src="../../../assets/vendor/js/bootstrap.js"></script>
    <script src="../../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../../../assets/vendor/js/menu.js"></script>

    <!-- Vendor JS -->
    <script src="../../../assets/vendor/libs/apex-charts/apexcharts.js"></script>

    <!-- Main JS -->
    <script src="../../../assets/js/main.js"></script>
    <script src="../../../assets/js/dashboards-analytics.js"></script>

    <!-- GitHub Buttons -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
</body>

</html>