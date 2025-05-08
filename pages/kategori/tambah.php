<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

// Cek role admin atau staff
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff') {
    header("Location: ../../dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_kategori = $_POST['nama_kategori'];
    $deskripsi = $_POST['deskripsi'] ?: null;

    try {
        $stmt = $pdo->prepare("INSERT INTO kategori (nama_kategori, deskripsi) VALUES (?, ?)");
        $stmt->execute([$nama_kategori, $deskripsi]);

        header("Location: index.php?success=Kategori+baru+berhasil+ditambahkan");
        exit();
    } catch (PDOException $e) {
        $error = "Gagal menambahkan kategori: " . $e->getMessage();
    }
}
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

                        <h2>Tambah Kategori Barang</h2>
                        <a href="index.php" class="btn btn-secondary mb-3">
                            <i class="bx bx-arrow-back"></i> Kembali
                        </a>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <div class="card">
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="nama_kategori" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="deskripsi" class="form-label">Deskripsi (opsional)</label>
                                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-warning">
                                        <i class="bx bx-save"></i> Simpan
                                    </button>
                                </form>
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