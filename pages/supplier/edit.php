<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

// Cek role admin atau staff
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff') {
    header("Location: ../../dashboard.php");
    exit();
}

// Ambil ID supplier dari URL
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id_supplier = $_GET['id'];

// Ambil data supplier yang akan diedit
$stmt = $pdo->prepare("SELECT * FROM supplier WHERE id_supplier = ?");
$stmt->execute([$id_supplier]);
$supplier = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$supplier) {
    header("Location: index.php?error=Supplier+tidak+ditemukan");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_supplier = $_POST['nama_supplier'];
    $alamat = $_POST['alamat'] ?? null;
    $no_telepon = $_POST['no_telepon'] ?? null;
    $email = $_POST['email'] ?? null;

    try {
        $stmt = $pdo->prepare("UPDATE supplier SET 
                              nama_supplier = ?, 
                              alamat = ?, 
                              no_telepon = ?, 
                              email = ?
                              WHERE id_supplier = ?");
        $stmt->execute([$nama_supplier, $alamat, $no_telepon, $email, $id_supplier]);

        header("Location: index.php?success=Supplier+berhasil+diupdate");
        exit();
    } catch (PDOException $e) {
        $error = "Gagal mengupdate supplier: " . $e->getMessage();
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

                        <h2>Edit Supplier</h2>
                        <a href="index.php" class="btn btn-secondary mb-3">
                            <i class="bx bx-arrow-back"></i> Kembali
                        </a>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <div class="card">
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="nama_supplier" class="form-label">Nama Supplier <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nama_supplier" name="nama_supplier"
                                            value="<?= htmlspecialchars($supplier['nama_supplier']) ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="alamat" class="form-label">Alamat</label>
                                        <textarea class="form-control" id="alamat" name="alamat" rows="2"><?= htmlspecialchars($supplier['alamat']) ?></textarea>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="no_telepon" class="form-label">No. Telepon</label>
                                            <input type="text" class="form-control" id="no_telepon" name="no_telepon"
                                                value="<?= htmlspecialchars($supplier['no_telepon']) ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                value="<?= htmlspecialchars($supplier['email']) ?>">
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-warning">
                                        <i class="bx bx-save"></i> Simpan Perubahan
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