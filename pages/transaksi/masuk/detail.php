<?php
require_once '../../../includes/auth_check.php';
require_once '../../../config/database.php';

// Ambil ID dari URL
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id_masuk = $_GET['id'];

// Query untuk mendapatkan detail barang masuk
$query = "SELECT bm.*, b.nama_barang, b.kode_barang, s.nama_supplier, p.nama_lengkap 
          FROM barang_masuk bm
          JOIN barang b ON bm.id_barang = b.id_barang
          LEFT JOIN supplier s ON bm.id_supplier = s.id_supplier
          JOIN pengguna p ON bm.id_pengguna = p.id_pengguna
          WHERE bm.id_masuk = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$id_masuk]);
$transaksi = $stmt->fetch();

if (!$transaksi) {
    header("Location: index.php");
    exit();
}
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
                            <h2>Detail Barang Masuk</h2>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="bx bx-arrow-back"></i> Kembali
                            </a>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <h5>Informasi Transaksi</h5>
                                        <table class="table table-bordered">
                                            <tr>
                                                <th width="40%">ID Transaksi</th>
                                                <td><?= $transaksi['id_masuk'] ?></td>
                                            </tr>
                                            <tr>
                                                <th>Tanggal Masuk</th>
                                                <td><?= date('d/m/Y', strtotime($transaksi['tanggal_masuk'])) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Input Oleh</th>
                                                <td><?= $transaksi['nama_lengkap'] ?></td>
                                            </tr>
                                            <tr>
                                                <th>No. Faktur</th>
                                                <td><?= $transaksi['no_faktur'] ?: '-' ?></td>
                                            </tr>
                                            <tr>
                                                <th>Keterangan</th>
                                                <td><?= $transaksi['keterangan'] ?: '-' ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h5>Informasi Barang</h5>
                                        <table class="table table-bordered">
                                            <tr>
                                                <th width="40%">Kode Barang</th>
                                                <td><?= $transaksi['kode_barang'] ?></td>
                                            </tr>
                                            <tr>
                                                <th>Nama Barang</th>
                                                <td><?= $transaksi['nama_barang'] ?></td>
                                            </tr>
                                            <tr>
                                                <th>Supplier</th>
                                                <td><?= $transaksi['nama_supplier'] ?: '-' ?></td>
                                            </tr>
                                            <tr>
                                                <th>Jumlah</th>
                                                <td><?= $transaksi['jumlah'] ?></td>
                                            </tr>
                                            <tr>
                                                <th>Harga Satuan</th>
                                                <td><?= $transaksi['harga_satuan'] ? 'Rp ' . number_format($transaksi['harga_satuan'], 0, ',', '.') : '-' ?></td>
                                            </tr>
                                            <tr>
                                                <th>Total Harga</th>
                                                <td><?= $transaksi['total_harga'] ? 'Rp ' . number_format($transaksi['total_harga'], 0, ',', '.') : '-' ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <?php if ($_SESSION['role'] === 'admin' || $_SESSION['id_pengguna'] === $transaksi['id_pengguna']): ?>
                                    <div class="d-flex justify-content-end">
                                        <a href="edit.php?id=<?= $transaksi['id_masuk'] ?>" class="btn btn-warning me-2">
                                            <i class="bx bx-pencil"></i> Edit
                                        </a>
                                        <a href="hapus.php?id=<?= $transaksi['id_masuk'] ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus transaksi ini?')">
                                            <i class="bx bx-trash"></i> Hapus
                                        </a>
                                    </div>
                                <?php endif; ?>
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