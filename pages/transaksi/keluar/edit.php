<?php
require_once '../../../includes/auth_check.php';
require_once '../../../config/database.php';

// Cek role admin atau staff
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff') {
    header("Location: ../../../dashboard.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id_keluar = $_GET['id'];

// Ambil data barang keluar
$query = "SELECT * FROM barang_keluar WHERE id_keluar = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$id_keluar]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    header("Location: index.php");
    exit();
}

// Ambil data barang
$barang = $pdo->query("SELECT * FROM barang")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_barang = $_POST['id_barang'];
    $jumlah_baru = (int)$_POST['jumlah'];
    $tanggal_keluar = $_POST['tanggal_keluar'];
    $penerima = $_POST['penerima'];
    $keperluan = $_POST['keperluan'];
    $keterangan = $_POST['keterangan'];
    $id_pengguna = $_SESSION['id_pengguna'];

    try {
        $pdo->beginTransaction();

        // Ambil stok barang lama dan update stok
        $queryStok = "SELECT stok FROM barang WHERE id_barang = ?";
        $stmtStok = $pdo->prepare($queryStok);
        $stmtStok->execute([$data['id_barang']]);
        $stok_lama = $stmtStok->fetchColumn();

        // Kembalikan stok lama
        $pdo->prepare("UPDATE barang SET stok = stok + ? WHERE id_barang = ?")
            ->execute([$data['jumlah'], $data['id_barang']]);

        // Kurangi stok baru
        $pdo->prepare("UPDATE barang SET stok = stok - ? WHERE id_barang = ?")
            ->execute([$jumlah_baru, $id_barang]);

        // Update data barang keluar
        $update = "UPDATE barang_keluar SET id_barang = ?, jumlah = ?, tanggal_keluar = ?, penerima = ?, keperluan = ?, keterangan = ?, id_pengguna = ? WHERE id_keluar = ?";
        $stmt = $pdo->prepare($update);
        $stmt->execute([$id_barang, $jumlah_baru, $tanggal_keluar, $penerima, $keperluan, $keterangan, $id_pengguna, $id_keluar]);

        $pdo->commit();

        header("Location: index.php?success=Data berhasil diperbarui.");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Gagal menyimpan perubahan: " . $e->getMessage());
    }
}
?>

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
                        <h2>Edit Barang Keluar</h2>
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
                                                <?php foreach ($barang as $b): ?>
                                                    <option value="<?= $b['id_barang'] ?>" <?= $b['id_barang'] == $data['id_barang'] ? 'selected' : '' ?>>
                                                        <?= $b['kode_barang'] ?> - <?= $b['nama_barang'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <label for="jumlah" class="form-label">Jumlah</label>
                                            <input type="number" class="form-control" id="jumlah" name="jumlah" required min="1" value="<?= $data['jumlah'] ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="tanggal_keluar" class="form-label">Tanggal Keluar</label>
                                            <input type="date" class="form-control" id="tanggal_keluar" name="tanggal_keluar" required
                                                value="<?= $data['tanggal_keluar'] ?>">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="penerima" class="form-label">Penerima</label>
                                            <input type="number" class="form-control" id="penerima" name="penerima" min="0" step="100"
                                                value="<?= $data['penerima'] ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="tanggal_masuk" class="form-label">Tanggal Masuk</label>
                                            <input type="date" class="form-control" id="tanggal_masuk" name="tanggal_masuk" required
                                                value="<?= $data['tanggal_keluar'] ?>">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="keterangan" class="form-label">Keterangan</label>
                                        <textarea class="form-control" name="keterangan"><?= $data['keterangan'] ?></textarea>
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