<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

// Query untuk mendapatkan semua supplier
$query = "SELECT * FROM supplier ORDER BY nama_supplier";
$stmt = $pdo->query($query);
$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                            <h2>Data Supplier</h2>
                            <a href="tambah.php" class="btn btn-warning">
                                <i class="bx bx-plus-circle"></i> Tambah Supplier
                            </a>
                        </div>

                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
                        <?php endif; ?>

                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
                        <?php endif; ?>

                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nama Supplier</th>
                                                <th>Alamat</th>
                                                <th>No. Telepon</th>
                                                <th>Email</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($suppliers) > 0): ?>
                                                <?php foreach ($suppliers as $key => $supplier): ?>
                                                    <tr>
                                                        <td><?= $key + 1 ?></td>
                                                        <td><?= htmlspecialchars($supplier['nama_supplier']) ?></td>
                                                        <td><?= htmlspecialchars($supplier['alamat']) ?></td>
                                                        <td><?= htmlspecialchars($supplier['no_telepon']) ?></td>
                                                        <td><?= htmlspecialchars($supplier['email']) ?></td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                <a href="edit.php?id=<?= $supplier['id_supplier'] ?>" class="btn btn-warning" title="Edit">
                                                                    <i class="bx bx-pencil"></i>
                                                                </a>
                                                                <a href="hapus.php?id=<?= $supplier['id_supplier'] ?>" class="btn btn-danger" title="Hapus"
                                                                    onclick="return confirm('Yakin ingin menghapus supplier ini?')">
                                                                    <i class="bx bx-trash"></i>
                                                                </a>
                                                            </div>
                                                        </td>

                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="text-center">Tidak ada data supplier</td>
                                                </tr>
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