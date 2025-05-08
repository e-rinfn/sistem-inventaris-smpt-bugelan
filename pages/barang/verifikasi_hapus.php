<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

// Hanya admin yang bisa mengakses
if ($_SESSION['role'] !== 'admin' || !isset($_SESSION['barang_dihapus'])) {
    header("Location: ../../dashboard.php");
    exit();
}

$id_barang = $_SESSION['barang_dihapus'];
$counts = $_SESSION['transaksi_terkait'];

// Ambil detail barang
$stmt = $pdo->prepare("SELECT * FROM barang WHERE id_barang = ?");
$stmt->execute([$id_barang]);
$barang = $stmt->fetch();

// Ambil contoh transaksi terkait (untuk ditampilkan)
$transaksi_masuk = $pdo->prepare("SELECT * FROM barang_masuk WHERE id_barang = ? LIMIT 5");
$transaksi_masuk->execute([$id_barang]);

$transaksi_keluar = $pdo->prepare("SELECT * FROM barang_keluar WHERE id_barang = ? LIMIT 5");
$transaksi_keluar->execute([$id_barang]);

$transaksi_hilang = $pdo->prepare("SELECT * FROM barang_hilang WHERE id_barang = ? LIMIT 5");
$transaksi_hilang->execute([$id_barang]);
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
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <!-- Content -->


                        <h2>Verifikasi Penghapusan Barang</h2>

                        <div class="card mb-4">
                            <div class="card-body">
                                <h4>Barang yang akan dihapus:</h4>
                                <p><strong>Kode:</strong> <?= htmlspecialchars($barang['kode_barang']) ?></p>
                                <p><strong>Nama:</strong> <?= htmlspecialchars($barang['nama_barang']) ?></p>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header bg-warning text-white">
                                <h4>Transaksi Terkait</h4>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning mt-3">
                                    <strong>Total Transaksi:</strong><br>
                                    - Barang Masuk: <?= $counts['masuk'] ?><br>
                                    - Barang Keluar: <?= $counts['keluar'] ?><br>
                                    - Barang Hilang: <?= $counts['hilang'] ?>
                                </div>

                                <!-- Tampilkan contoh transaksi -->
                                <h5>Contoh Transaksi Barang Masuk</h5>
                                <?php if ($counts['masuk'] > 0): ?>
                                    <ul>
                                        <?php while ($row = $transaksi_masuk->fetch()): ?>
                                            <li>
                                                <?= date('d/m/Y', strtotime($row['tanggal_masuk'])) ?> -
                                                Jumlah: <?= $row['jumlah'] ?> -
                                                No. Faktur: <?= $row['no_faktur'] ?? '-' ?>
                                            </li>
                                        <?php endwhile; ?>
                                        <?php if ($counts['masuk'] > 5): ?>
                                            <li>... dan <?= $counts['masuk'] - 5 ?> transaksi lainnya</li>
                                        <?php endif; ?>
                                    </ul>
                                <?php else: ?>
                                    <p>Tidak ada transaksi barang masuk</p>
                                <?php endif; ?>

                                <!-- Ulangi untuk transaksi keluar dan hilang -->
                                <!-- ... -->

                                <div class="alert alert-danger">
                                    <strong>Peringatan!</strong> Anda harus menghapus semua transaksi terkait terlebih dahulu sebelum dapat menghapus barang ini.
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="bx bx-arrow-back"></i> Kembali
                                    </a>
                                    <div>
                                        <a href="../transaksi/masuk/index.php?barang=<?= $id_barang ?>" class="btn btn-primary me-2">
                                            <i class="bx bx-list-ul"></i> Lihat Transaksi Masuk
                                        </a>
                                        <a href="../transaksi/keluar/index.php?barang=<?= $id_barang ?>" class="btn btn-primary me-2">
                                            <i class="bx bx-list-ul"></i> Lihat Transaksi Keluar
                                        </a>
                                        <a href="../transaksi/hilang/index.php?barang=<?= $id_barang ?>" class="btn btn-primary">
                                            <i class="bx bx-list-ul"></i> Lihat Barang Hilang
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- / Content -->
                    </div>

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