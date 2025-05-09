<?php
require_once '../../../includes/auth_check.php';
require_once '../../../config/database.php';

// Cek role admin atau staff
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff') {
    header("Location: ../../dashboard/index.php");
    exit();
}

// Ambil data barang untuk dropdown
$barang = $pdo->query("SELECT id_barang, kode_barang, nama_barang, stok FROM barang ORDER BY nama_barang")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_barang = $_POST['id_barang'];
    $jumlah = $_POST['jumlah'];
    $tanggal_hilang = $_POST['tanggal_hilang'];
    $keterangan = $_POST['keterangan'] ?: null;

    // Validasi stok
    $stok_barang = $pdo->query("SELECT stok FROM barang WHERE id_barang = $id_barang")->fetchColumn();

    if ($jumlah > $stok_barang) {
        $error = "Jumlah barang hilang tidak boleh melebihi stok yang tersedia!";
    } else {
        try {
            $pdo->beginTransaction();

            // Insert ke tabel barang_hilang
            $stmt = $pdo->prepare("INSERT INTO barang_hilang (
                id_barang, id_pengguna, jumlah, tanggal_hilang, keterangan
            ) VALUES (?, ?, ?, ?, ?)");

            $stmt->execute([
                $id_barang,
                $_SESSION['id_pengguna'],
                $jumlah,
                $tanggal_hilang,
                $keterangan
            ]);

            // Trigger akan otomatis update stok dan riwayat

            $pdo->commit();

            header("Location: index.php?success=Laporan+barang+hilang+berhasil+disimpan");
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Gagal menyimpan laporan: " . $e->getMessage();
        }
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

                        <h2>Tambah Laporan Barang Hilang</h2>
                        <a href="index.php" class="btn btn-secondary mb-3">
                            <i class="bx bx-arrow-back"></i> Kembali
                        </a>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
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
                                                    <option value="<?= $item['id_barang'] ?>" data-stok="<?= $item['stok'] ?>">
                                                        <?= htmlspecialchars($item['kode_barang']) ?> - <?= htmlspecialchars($item['nama_barang']) ?>
                                                        (Stok: <?= $item['stok'] ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="jumlah" class="form-label">Jumlah Hilang</label>
                                            <input type="number" class="form-control" id="jumlah" name="jumlah" min="1" required>
                                            <small id="stokInfo" class="form-text text-muted"></small>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="tanggal_hilang" class="form-label">Tanggal Hilang</label>
                                            <input type="date" class="form-control" id="tanggal_hilang" name="tanggal_hilang" required
                                                value="<?= date('Y-m-d') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="keterangan" class="form-label">Keterangan (opsional)</label>
                                            <textarea class="form-control" id="keterangan" name="keterangan" rows="1"></textarea>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-save"></i> Simpan Laporan
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
        <script>
            // Validasi jumlah tidak melebihi stok
            document.getElementById('id_barang').addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const stok = selectedOption.getAttribute('data-stok');
                const stokInfo = document.getElementById('stokInfo');

                if (stok) {
                    stokInfo.textContent = `Stok tersedia: ${stok}`;
                    document.getElementById('jumlah').max = stok;
                } else {
                    stokInfo.textContent = '';
                }
            });
        </script>

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