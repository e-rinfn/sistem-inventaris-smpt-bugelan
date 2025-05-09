<?php
require_once '../../../includes/auth_check.php';
require_once '../../../config/database.php';


// Query untuk mendapatkan data barang keluar
$query = "SELECT bk.*, b.kode_barang, b.nama_barang, p.nama_lengkap AS operator 
          FROM barang_keluar bk
          JOIN barang b ON bk.id_barang = b.id_barang
          JOIN pengguna p ON bk.id_pengguna = p.id_pengguna
          ORDER BY bk.tanggal_keluar DESC, bk.dibuat_pada DESC";
$transaksi = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>

<!-- =========================================================
* Sneat - Bootstrap 5 HTML Admin Template - Pro | v1.0.0
==============================================================

* Product Page: https://themeselection.com/products/sneat-bootstrap-html-admin-template/
* Created by: ThemeSelection
* License: You must have a valid license purchased in order to legally use the theme for your project.
* Copyright ThemeSelection (https://themeselection.com)

=========================================================
 -->
<!-- beautify ignore:start -->
<html
    lang="en"
    class="light-style layout-menu-fixed"
    dir="ltr"
    data-theme="theme-default"
    data-assets-path="../assets/"
    data-template="vertical-menu-template-free">

</html>

</html>

<!-- Header start -->

<?php include '../../../header.php'; ?>

<!-- Header end -->

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            <!-- Menu -->

            <?php include '../../../includes/sidebar.php'; ?>

            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">

                <!-- Navbar -->

                <?php include '../../../includes/navbar.php'; ?>


                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->

                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2>Data Barang Keluar</h2>
                            <a href="tambah.php" class="btn btn-primary">
                                <i class="bx bx-plus-circle"></i> Tambah Barang Keluar
                            </a>
                        </div>

                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success"><?= $_GET['success'] ?></div>
                        <?php endif; ?>

                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Tanggal</th>
                                                <th>Kode Barang</th>
                                                <th>Nama Barang</th>
                                                <th>Jumlah</th>
                                                <th>Penerima</th>
                                                <th>Keperluan</th>
                                                <th>Operator</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($transaksi as $key => $trx): ?>
                                                <tr>
                                                    <td><?= $key + 1 ?></td>
                                                    <td><?= date('d/m/Y', strtotime($trx['tanggal_keluar'])) ?></td>
                                                    <td><?= $trx['kode_barang'] ?></td>
                                                    <td><?= $trx['nama_barang'] ?></td>
                                                    <td><?= $trx['jumlah'] ?></td>
                                                    <td><?= $trx['penerima'] ?? '-' ?></td>
                                                    <td><?= $trx['keperluan'] ?? '-' ?></td>
                                                    <td><?= $trx['operator'] ?></td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <a href="#" class="btn btn-info"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#detailModal<?= $trx['id_keluar'] ?>"
                                                                title="Detail">
                                                                <i class="bx bx-info-circle"></i>
                                                            </a>
                                                            <a href="edit.php?id=<?= $trx['id_keluar'] ?>" class="btn btn-warning" title="Edit">
                                                                <i class="bx bx-pencil"></i>
                                                            </a>
                                                            <a href="hapus.php?id=<?= $trx['id_keluar'] ?>" class="btn btn-danger"
                                                                title="Hapus"
                                                                onclick="return confirm('Yakin ingin menghapus data ini? Stok barang akan ditambahkan kembali.')">
                                                                <i class="bx bx-trash"></i>
                                                            </a>
                                                        </div>
                                                    </td>


                                                </tr>

                                                <!-- Modal Detail -->
                                                <div class="modal fade" id="detailModal<?= $trx['id_keluar'] ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Detail Barang Keluar</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row mb-2">
                                                                    <div class="col-4 fw-bold">Tanggal</div>
                                                                    <div class="col-8"><?= date('d/m/Y', strtotime($trx['tanggal_keluar'])) ?></div>
                                                                </div>
                                                                <div class="row mb-2">
                                                                    <div class="col-4 fw-bold">Barang</div>
                                                                    <div class="col-8"><?= $trx['kode_barang'] ?> - <?= $trx['nama_barang'] ?></div>
                                                                </div>
                                                                <div class="row mb-2">
                                                                    <div class="col-4 fw-bold">Jumlah</div>
                                                                    <div class="col-8"><?= $trx['jumlah'] ?></div>
                                                                </div>
                                                                <div class="row mb-2">
                                                                    <div class="col-4 fw-bold">Penerima</div>
                                                                    <div class="col-8"><?= $trx['penerima'] ?? '-' ?></div>
                                                                </div>
                                                                <div class="row mb-2">
                                                                    <div class="col-4 fw-bold">Keperluan</div>
                                                                    <div class="col-8"><?= $trx['keperluan'] ?? '-' ?></div>
                                                                </div>
                                                                <div class="row mb-2">
                                                                    <div class="col-4 fw-bold">Operator</div>
                                                                    <div class="col-8"><?= $trx['operator'] ?></div>
                                                                </div>
                                                                <div class="row mb-2">
                                                                    <div class="col-4 fw-bold">Keterangan</div>
                                                                    <div class="col-8"><?= $trx['keterangan'] ?? '-' ?></div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
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


        <!-- Core JS -->
        <!-- build:js assets/vendor/js/core.js -->
        <script src="../../../assets/vendor/libs/jquery/jquery.js"></script>
        <script src="../../../assets/vendor/libs/popper/popper.js"></script>
        <script src="../../../assets/vendor/js/bootstrap.js"></script>
        <script src="../../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

        <script src="../../../assets/vendor/js/menu.js"></script>
        <!-- endbuild -->

        <!-- Vendors JS -->
        <script src="../../../assets/vendor/libs/apex-charts/apexcharts.js"></script>

        <!-- Main JS -->
        <script src="../../../assets/js/main.js"></script>

        <!-- Page JS -->
        <script src="../../../assets/js/dashboards-analytics.js"></script>

        <!-- Place this tag in your head or just before your close body tag. -->
        <script async defer src="https://buttons.github.io/buttons.js"></script>
</body>

</html>