<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

// Ambil data kategori dari database
$query = "SELECT * FROM kategori ORDER BY nama_kategori";
$stmt = $pdo->query($query);
$kategori = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2>Data Kategori Barang</h2>
                            <a href="tambah.php" class="btn btn-warning">
                                <i class="bx bx-plus-circle"></i> Tambah Kategori
                            </a>
                        </div>

                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success"><?= $_GET['success'] ?></div>
                        <?php endif; ?>

                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger"><?= $_GET['error'] ?></div>
                        <?php endif; ?>

                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nama Kategori</th>
                                                <th>Deskripsi</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($kategori)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">Tidak ada data kategori</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($kategori as $key => $item): ?>
                                                    <tr>
                                                        <td><?= $key + 1 ?></td>
                                                        <td><?= $item['nama_kategori'] ?></td>
                                                        <td><?= $item['deskripsi'] ?: '-' ?></td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                <a href="edit.php?id=<?= $item['id_kategori'] ?>" class="btn btn-warning" title="Edit">
                                                                    <i class="bx bx-pencil"></i>
                                                                </a>
                                                                <a href="hapus.php?id=<?= $item['id_kategori'] ?>" class="btn btn-danger" title="Hapus"
                                                                    onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                                                                    <i class="bx bx-trash"></i>
                                                                </a>
                                                            </div>
                                                        </td>

                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
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