<?php
require_once '../../../includes/auth_check.php';
require_once '../../../config/database.php';

// Hanya admin atau staff yang bisa mengakses
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff') {
    header("Location: ../../../dashboard.php");
    exit();
}

$id_keluar = $_GET['id'] ?? 0;

// Ambil data transaksi keluar
$stmt = $pdo->prepare("SELECT bk.*, b.kode_barang, b.nama_barang, b.id_barang
                      FROM barang_keluar bk
                      JOIN barang b ON bk.id_barang = b.id_barang
                      WHERE bk.id_keluar = ?");
$stmt->execute([$id_keluar]);
$transaksi = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaksi) {
    header("Location: ../keluar/index.php?error=Transaksi+tidak+ditemukan");
    exit();
}

// Hitung jumlah yang sudah dikembalikan
$stmt = $pdo->prepare("SELECT COALESCE(SUM(jumlah), 0) AS total_kembali 
                      FROM barang_kembali 
                      WHERE id_keluar = ?");
$stmt->execute([$id_keluar]);
$total_kembali = $stmt->fetchColumn();

$sisa_pinjam = $transaksi['jumlah'] - $total_kembali;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jumlah = (int)$_POST['jumlah'];
    $tanggal_kembali = $_POST['tanggal_kembali'];
    $kondisi = $_POST['kondisi'];
    $keterangan = $_POST['keterangan'] ?? null;

    // Validasi
    if ($jumlah <= 0 || $jumlah > $sisa_pinjam) {
        $error = "Jumlah tidak valid. Sisa pinjam: $sisa_pinjam";
    } else {
        try {
            $pdo->beginTransaction();

            // Insert ke tabel barang_kembali
            $insert = $pdo->prepare("INSERT INTO barang_kembali (
                id_keluar, id_barang, id_pengguna, jumlah, tanggal_kembali, kondisi, keterangan
            ) VALUES (?, ?, ?, ?, ?, ?, ?)");

            $insert->execute([
                $id_keluar,
                $transaksi['id_barang'],
                $_SESSION['id_pengguna'],
                $jumlah,
                $tanggal_kembali,
                $kondisi,
                $keterangan
            ]);

            // Trigger akan otomatis update stok dan riwayat

            $pdo->commit();

            header("Location: ../keluar/index.php?success=Barang+berhasil+dikembalikan");
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Gagal menyimpan pengembalian: " . $e->getMessage();
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

                        <h2>Tambah Barang Masuk</h2>


                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <div class="card">
                            <div class="card-body">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <h5>Detail Peminjaman</h5>
                                        <table class="table table-bordered">
                                            <tr>
                                                <th>Tanggal Keluar</th>
                                                <td><?= date('d/m/Y', strtotime($transaksi['tanggal_keluar'])) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Barang</th>
                                                <td><?= $transaksi['kode_barang'] ?> - <?= $transaksi['nama_barang'] ?></td>
                                            </tr>
                                            <tr>
                                                <th>Jumlah Dipinjam</th>
                                                <td><?= $transaksi['jumlah'] ?></td>
                                            </tr>
                                            <tr>
                                                <th>Sudah Kembali</th>
                                                <td><?= $total_kembali ?></td>
                                            </tr>
                                            <tr>
                                                <th>Status Pinjam</th>
                                                <td>
                                                    <?php
                                                    $status_class = '';
                                                    $status_text = '';

                                                    if ($sisa_pinjam == 0) {
                                                        $status_class = 'bg-success';
                                                        $status_text = 'Selesai';
                                                    } elseif ($sisa_pinjam == $transaksi['jumlah']) {
                                                        $status_class = 'bg-danger';
                                                        $status_text = 'Belum Dikembalikan';
                                                    } else {
                                                        $status_class = 'bg-warning';
                                                        $status_text = 'Sebagian (Sisa: ' . $sisa_pinjam . ')';
                                                    }
                                                    ?>
                                                    <span class="badge <?= $status_class ?>"><?= $status_text ?></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Penerima</th>
                                                <td><?= $transaksi['penerima'] ?? '-' ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <h5>Form Pengembalian</h5>
                                <form method="POST">
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label for="jumlah" class="form-label">Jumlah Dikembalikan</label>
                                            <input type="number" class="form-control" id="jumlah" name="jumlah"
                                                min="1" max="<?= $sisa_pinjam ?>" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="tanggal_kembali" class="form-label">Tanggal Kembali</label>
                                            <input type="date" class="form-control" id="tanggal_kembali" name="tanggal_kembali"
                                                value="<?= date('Y-m-d') ?>" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="kondisi" class="form-label">Kondisi Barang</label>
                                            <select class="form-select" id="kondisi" name="kondisi" required>
                                                <option value="baik">Baik</option>
                                                <option value="rusak_ringan">Rusak Ringan</option>
                                                <option value="rusak_berat">Rusak Berat</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="keterangan" class="form-label">Keterangan (Opsional)</label>
                                        <textarea class="form-control" id="keterangan" name="keterangan" rows="2"></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-save"></i> Simpan Pengembalian
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