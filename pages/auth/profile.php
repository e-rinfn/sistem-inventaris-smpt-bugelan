<?php

require_once '../../config/database.php';
require_once '../../includes/auth_check.php';


$id_pengguna = $_SESSION['id_pengguna'];

// Get user data
$stmt = $pdo->prepare("SELECT * FROM pengguna WHERE id_pengguna = ?");
$stmt->execute([$id_pengguna]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $username = trim($_POST['username']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validasi
    if (empty($nama_lengkap)) {
        $error = 'Nama lengkap harus diisi!';
    } elseif (empty($username)) {
        $error = 'Username harus diisi!';
    } else {
        try {
            // Check if username already exists (except current user)
            $check_stmt = $pdo->prepare("SELECT id_pengguna FROM pengguna WHERE username = ? AND id_pengguna != ?");
            $check_stmt->execute([$username, $id_pengguna]);

            if ($check_stmt->rowCount() > 0) {
                $error = 'Username sudah digunakan!';
            } else {
                // Update profile
                $update_data = [
                    'nama_lengkap' => $nama_lengkap,
                    'username' => $username,
                    'id_pengguna' => $id_pengguna
                ];
                $update_sql = "UPDATE pengguna SET nama_lengkap = :nama_lengkap, username = :username";


                // Password change logic
                if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
                    if (empty($current_password)) {
                        $error = 'Password saat ini harus diisi untuk mengubah password!';
                    } elseif (!password_verify($current_password, $user['password'])) {
                        $error = 'Password saat ini salah!';
                    } elseif (empty($new_password)) {
                        $error = 'Password baru harus diisi!';
                    } elseif (strlen($new_password) < 6) {
                        $error = 'Password baru minimal 6 karakter!';
                    } elseif ($new_password !== $confirm_password) {
                        $error = 'Konfirmasi password tidak cocok!';
                    } else {
                        $update_sql .= ", password = :password";
                        $update_data['password'] = password_hash($new_password, PASSWORD_DEFAULT);
                    }
                }

                if (empty($error)) {
                    $update_sql .= " WHERE id_pengguna = :id_pengguna";
                    $update_stmt = $pdo->prepare($update_sql);
                    $update_stmt->execute($update_data);

                    // Update session username if changed
                    if ($username !== $_SESSION['username']) {
                        $_SESSION['username'] = $username;
                    }

                    $success = 'Profil berhasil diperbarui!';

                    // Refresh user data
                    $stmt->execute([$id_pengguna]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                }
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}
?>

<?php include '../../includes/header.php'; ?>

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

<head>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Account settings - Account | Sneat - Bootstrap 5 HTML Admin Template - Pro</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="../assets/vendor/fonts/boxicons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="../../assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="../../assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="../../assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="../../assets/vendor/js/helpers.js"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="../../assets/js/config.js"></script>
</head>

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
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <h5 class="card-header">Profile Details</h5>
                                    <!-- Account -->

                                    <hr class="my-0" />
                                    <div class="card-body">
                                        <?php if ($error): ?>
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <?= $error ?>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($success): ?>
                                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                <?= $success ?>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        <?php endif; ?>

                                        <form method="post">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                                    <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap"
                                                        value="<?= htmlspecialchars($user['nama_lengkap']) ?>" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="username" class="form-label">Username</label>
                                                    <input type="text" class="form-control" id="username" name="username"
                                                        value="<?= htmlspecialchars($user['username']) ?>" required>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="email"
                                                    value="<?= htmlspecialchars($user['email'] ?? 'Belum diatur') ?>" readonly>
                                            </div>

                                            <div class="mb-3">
                                                <label for="role" class="form-label">Peran</label>
                                                <input type="text" class="form-control" id="role"
                                                    value="<?= ucfirst($user['role']) ?>" readonly>
                                            </div>

                                            <div class="mb-3">
                                                <label for="dibuat_pada" class="form-label">Bergabung Pada</label>
                                                <input type="text" class="form-control" id="dibuat_pada"
                                                    value="<?= date('d/m/Y H:i', strtotime($user['dibuat_pada'])) ?>" readonly>
                                            </div>

                                            <hr>

                                            <h5 class="mb-3">Ubah Password</h5>

                                            <div class="row mb-3">
                                                <div class="col-md-4">
                                                    <label for="current_password" class="form-label">Password Saat Ini</label>
                                                    <input type="password" class="form-control" id="current_password" name="current_password">
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="new_password" class="form-label">Password Baru</label>
                                                    <input type="password" class="form-control" id="new_password" name="new_password">
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                                </div>
                                            </div>

                                            <div class="alert alert-info">
                                                <i class="bi bi-info-circle"></i> Kosongkan field password jika tidak ingin mengubah password.
                                            </div>

                                            <div class="d-grid gap-2">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bi bi-save"></i> Simpan Perubahan
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    <!-- /Account -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- / Content -->

                    <!-- Footer -->

                    <!-- / Footer -->

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
    <script src="../../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../../assets/vendor/libs/popper/popper.js"></script>
    <script src="../../assets/vendor/js/bootstrap.js"></script>
    <script src="../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

    <script src="../../assets/vendor/js/menu.js"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->

    <!-- Main JS -->
    <script src="../../assets/js/main.js"></script>

    <!-- Page JS -->
    <script src="../../assets/js/pages-account-settings-account.js"></script>

    <!-- Place this tag in your head or just before your close body tag. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
</body>

</html>