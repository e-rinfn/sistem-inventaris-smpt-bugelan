<?php
// Ambil total barang dengan stok rendah (misal ≤ 5)
$stmtNotif = $pdo->query("SELECT COUNT(*) FROM barang WHERE stok <= 5 AND stok > 0");
$stokRendahCount = $stmtNotif->fetchColumn();

// Ambil detail barang-barang stok rendah (maksimal 5 teratas)
$stmtList = $pdo->query("SELECT nama_barang, stok FROM barang WHERE stok <= 5 AND stok > 0 ORDER BY stok ASC LIMIT 5");
$stokRendahList = $stmtList->fetchAll(PDO::FETCH_ASSOC);
?>



<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

        <ul class="navbar-nav flex-row align-items-center ms-auto">

            <!-- Notifikasi Barang Stok Rendah -->
            <!-- <li class="nav-item dropdown me-3 border rounded m-2">
                <a class="nav-link dropdown-toggle hide-arrow" href="<?= $base_path ?>/barang/index.php">
                    <i class="bx bx-line-chart-down bx-sm"></i>
                    <?php if ($stokRendahCount > 0): ?>
                        <span class="badge bg-danger rounded-pill badge-notifications">
                            <?= $stokRendahCount ?>
                        </span>
                    <?php endif; ?>
                </a>
            </li> -->

            <!-- Nama User yang Sedang Login -->
            <li class="nav-item me-2 me-xl-0">
                <span class="nav-link d-flex align-items-center">
                    <span class="d-none d-xl-inline-block me-2">Hai,</span>
                    <span class="fw-semibold d-none d-xl-inline-block">
                        <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username']) ?>
                    </span>
                </span>
            </li>

            <!-- User Dropdown -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <div class="w-px-40 h-px-40 rounded-circle bg-warning d-flex align-items-center justify-content-center text-white fw-bold" style="font-size: 1.25rem; line-height: 1;">
                            <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                        </div>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="w-px-40 h-px-40 rounded-circle bg-warning d-flex align-items-center justify-content-center text-white fw-bold" style="font-size: 1.25rem; line-height: 1;">
                                        <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="fw-semibold d-block"><?= $_SESSION['username'] ?></span>
                                    <small class="text-muted"><?= $_SESSION['role'] === 'admin' ? 'Administrator' : 'Staff' ?></small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?= $base_path ?>/auth/profile.php">
                            <i class="bx bx-user me-2"></i>
                            <span class="align-middle">My Profile</span>
                        </a>
                    </li>

                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li>
                            <a class="dropdown-item" href="<?= $base_path ?>/pengguna">
                                <i class="bx bx-cog me-2"></i>
                                <span class="align-middle">Kelola Pengguna</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?= $base_path ?>/auth/logout.php">
                            <i class="bx bx-power-off me-2"></i>
                            <span class="align-middle">Log Out</span>
                        </a>
                    </li>
                </ul>
            </li>
            <!--/ User -->
        </ul>
    </div>
</nav>