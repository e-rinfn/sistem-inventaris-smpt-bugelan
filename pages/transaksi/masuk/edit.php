<?php
require_once '../../../includes/auth_check.php';
require_once '../../../config/database.php';

// Cek role admin atau staff
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff') {
    header("Location: ../../dashboard/index.php");
    exit();
}

// Ambil ID dari URL
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id_masuk = $_GET['id'];

// Ambil data transaksi beserta informasi barang terkait
$stmt = $pdo->prepare("SELECT bm.*, b.stok AS stok_sekarang 
                      FROM barang_masuk bm
                      JOIN barang b ON bm.id_barang = b.id_barang
                      WHERE bm.id_masuk = ?");
$stmt->execute([$id_masuk]);
$transaksi = $stmt->fetch();

// Cek apakah transaksi ada dan milik pengguna (kecuali admin)
if (!$transaksi || ($_SESSION['role'] !== 'admin' && $transaksi['id_pengguna'] !== $_SESSION['id_pengguna'])) {
    header("Location: index.php");
    exit();
}

// Ambil data barang dan supplier untuk dropdown
$barang = $pdo->query("SELECT id_barang, kode_barang, nama_barang FROM barang ORDER BY nama_barang")->fetchAll();
$supplier = $pdo->query("SELECT id_supplier, nama_supplier FROM supplier ORDER BY nama_supplier")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_barang = $_POST['id_barang'];
    $id_supplier = $_POST['id_supplier'] ?: null;
    $jumlah_baru = $_POST['jumlah'];
    $tanggal_masuk = $_POST['tanggal_masuk'];
    $harga_satuan = $_POST['harga_satuan'] ?: 0;
    $no_faktur = $_POST['no_faktur'] ?: null;
    $keterangan = $_POST['keterangan'] ?: null;

    $total_harga = $jumlah_baru * $harga_satuan;

    try {
        $pdo->beginTransaction();

        // 1. Hitung selisih jumlah baru dengan jumlah lama
        $selisih = $jumlah_baru - $transaksi['jumlah'];

        // 2. Update data barang masuk
        $stmt = $pdo->prepare("UPDATE barang_masuk SET 
            id_barang = ?, id_supplier = ?, jumlah = ?, tanggal_masuk = ?,
            harga_satuan = ?, total_harga = ?, no_faktur = ?, keterangan = ?
            WHERE id_masuk = ?");

        $stmt->execute([
            $id_barang,
            $id_supplier,
            $jumlah_baru,
            $tanggal_masuk,
            $harga_satuan,
            $total_harga,
            $no_faktur,
            $keterangan,
            $id_masuk
        ]);

        // 3. Update stok barang jika jumlah berubah
        if ($selisih != 0) {
            $stmt = $pdo->prepare("UPDATE barang SET stok = stok + ? WHERE id_barang = ?");
            $stmt->execute([$selisih, $id_barang]);

            // Catat perubahan stok di riwayat
            $stmt = $pdo->prepare("INSERT INTO riwayat_stok (
                id_barang, jenis_transaksi, id_transaksi, stok_sebelum, 
                perubahan, stok_sesudah, tanggal_transaksi
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())");

            // Ambil stok saat ini sebelum perubahan
            $stmt_current = $pdo->prepare("SELECT stok FROM barang WHERE id_barang = ?");
            $stmt_current->execute([$id_barang]);
            $current_stok = $stmt_current->fetchColumn();

            $stmt->execute([
                $id_barang,
                'masuk',
                $id_masuk,
                $current_stok - $selisih, // Stok sebelum perubahan
                $selisih,
                $current_stok // Stok setelah perubahan
            ]);
        }

        $pdo->commit();

        header("Location: index.php?success=Transaksi+barang+masuk+berhasil+diupdate");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Gagal mengupdate transaksi: " . $e->getMessage();
    }
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

    <title>Amba Kue - Invnetory</title>

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

                        <h2>Edit Barang Masuk</h2>
                        <a href="index.php" class="btn btn-secondary mb-3">
                            <i class="bx bx-arrow-back"></i> Kembali
                        </a>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <div class="card">
                            <div class="card-body">
                                <form method="POST">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="id_barang" class="form-label">Barang</label>
                                            <select class="form-select" id="id_barang" name="id_barang" required>
                                                <option value="">Pilih Barang</option>
                                                <?php foreach ($barang as $item): ?>
                                                    <option value="<?= $item['id_barang'] ?>"
                                                        <?= ($item['id_barang'] == $transaksi['id_barang']) ? 'selected' : '' ?>>
                                                        <?= $item['kode_barang'] ?> - <?= $item['nama_barang'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="id_supplier" class="form-label">Supplier (opsional)</label>
                                            <select class="form-select" id="id_supplier" name="id_supplier">
                                                <option value="">Pilih Supplier</option>
                                                <?php foreach ($supplier as $item): ?>
                                                    <option value="<?= $item['id_supplier'] ?>"
                                                        <?= ($item['id_supplier'] == $transaksi['id_supplier']) ? 'selected' : '' ?>>
                                                        <?= $item['nama_supplier'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label for="jumlah" class="form-label">Jumlah</label>
                                            <input type="number" class="form-control" id="jumlah" name="jumlah" min="1" required
                                                value="<?= $transaksi['jumlah'] ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="harga_satuan" class="form-label">Harga Satuan (opsional)</label>
                                            <input type="number" class="form-control" id="harga_satuan" name="harga_satuan" min="0" step="100"
                                                value="<?= $transaksi['harga_satuan'] ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="tanggal_masuk" class="form-label">Tanggal Masuk</label>
                                            <input type="date" class="form-control" id="tanggal_masuk" name="tanggal_masuk" required
                                                value="<?= $transaksi['tanggal_masuk'] ?>">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="no_faktur" class="form-label">No. Faktur (opsional)</label>
                                            <input type="text" class="form-control" id="no_faktur" name="no_faktur"
                                                value="<?= $transaksi['no_faktur'] ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="keterangan" class="form-label">Keterangan (opsional)</label>
                                            <input type="text" class="form-control" id="keterangan" name="keterangan"
                                                value="<?= $transaksi['keterangan'] ?>">
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-save"></i> Simpan Perubahan
                                    </button>
                                </form>
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