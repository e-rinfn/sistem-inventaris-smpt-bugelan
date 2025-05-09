<?php
require_once '../../../includes/auth_check.php';
require_once '../../../config/database.php';

// Query untuk mendapatkan data barang masuk
$query = "SELECT bm.*, b.nama_barang, b.kode_barang, s.nama_supplier, p.nama_lengkap 
          FROM barang_masuk bm
          JOIN barang b ON bm.id_barang = b.id_barang
          LEFT JOIN supplier s ON bm.id_supplier = s.id_supplier
          JOIN pengguna p ON bm.id_pengguna = p.id_pengguna
          ORDER BY bm.tanggal_masuk DESC, bm.id_masuk DESC";
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

<head>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Sistem Inventaris SMPT Bugelan</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../../../assets/img/favicon/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="../../../assets/vendor/fonts/boxicons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="../../../assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="../../../assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="../../../assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="../../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <link rel="stylesheet" href="../../../assets/vendor/libs/apex-charts/apex-charts.css" />

    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="../../../assets/vendor/js/helpers.js"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="../../../assets/js/config.js"></script>
</head>

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
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <!-- Content -->

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2>Data Barang Masuk</h2>
                            <div>
                                <a href="tambah.php" class="btn btn-primary">
                                    <i class="bx bx-plus-circle"></i> Tambah Barang Masuk
                                </a>
                            </div>
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
                                                <th>Supplier</th>
                                                <th>Harga Satuan</th>
                                                <th>Total</th>
                                                <th>Input Oleh</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($transaksi as $key => $item): ?>
                                                <tr>
                                                    <td><?= $key + 1 ?></td>
                                                    <td><?= date('d/m/Y', strtotime($item['tanggal_masuk'])) ?></td>
                                                    <td><?= $item['kode_barang'] ?></td>
                                                    <td><?= $item['nama_barang'] ?></td>
                                                    <td><?= $item['jumlah'] ?></td>
                                                    <td><?= $item['nama_supplier'] ?? '-' ?></td>
                                                    <td><?= $item['harga_satuan'] ? 'Rp ' . number_format($item['harga_satuan'], 0, ',', '.') : '-' ?></td>
                                                    <td><?= $item['total_harga'] ? 'Rp ' . number_format($item['total_harga'], 0, ',', '.') : '-' ?></td>
                                                    <td><?= $item['nama_lengkap'] ?></td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <a href="detail.php?id=<?= $item['id_masuk'] ?>" class="btn btn-info" title="Detail">
                                                                <i class="bx bx-info-circle"></i>
                                                            </a>
                                                            <?php if ($_SESSION['role'] === 'admin' || $_SESSION['id_pengguna'] === $item['id_pengguna']): ?>
                                                                <a href="edit.php?id=<?= $item['id_masuk'] ?>" class="btn btn-warning" title="Edit">
                                                                    <i class="bx bx-pencil"></i>
                                                                </a>
                                                                <a href="hapus.php?id=<?= $item['id_masuk'] ?>" class="btn btn-danger" title="Hapus"
                                                                    onclick="return confirm('Yakin ingin menghapus transaksi ini?')">
                                                                    <i class="bx bx-trash"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>

                                                </tr>
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