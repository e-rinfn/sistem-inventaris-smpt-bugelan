<?php
require_once '../../includes/auth_check.php';
// Hanya admin yang bisa mengakses
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../dashboard.php");
    exit();
}

require_once '../../config/database.php';

$id = $_GET['id'] ?? 0;

// Ambil data pengguna yang akan diedit
$stmt = $pdo->prepare("SELECT * FROM pengguna WHERE id_pengguna = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: index.php?error=Pengguna+tidak+ditemukan");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $role = $_POST['role'];
    $password = $_POST['password'] ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    try {
        // Cek apakah username sudah digunakan oleh orang lain
        $stmt = $pdo->prepare("SELECT id_pengguna FROM pengguna WHERE username = ? AND id_pengguna != ?");
        $stmt->execute([$username, $id]);

        if ($stmt->rowCount() > 0) {
            $error = "Username sudah digunakan!";
        } else {
            // Update data pengguna
            if ($password) {
                $update = $pdo->prepare("UPDATE pengguna SET username = ?, nama_lengkap = ?, role = ?, password = ? WHERE id_pengguna = ?");
                $update->execute([$username, $nama_lengkap, $role, $password, $id]);
            } else {
                $update = $pdo->prepare("UPDATE pengguna SET username = ?, nama_lengkap = ?, role = ? WHERE id_pengguna = ?");
                $update->execute([$username, $nama_lengkap, $role, $id]);
            }

            header("Location: index.php?success=Pengguna+berhasil+diupdate");
            exit();
        }
    } catch (PDOException $e) {
        $error = "Gagal mengupdate pengguna: " . $e->getMessage();
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

                        <h2>Edit Pengguna</h2>
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
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class="form-control" id="username" name="username" value="<?= $user['username'] ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="password" class="form-label">Password (kosongkan jika tidak diubah)</label>
                                            <input type="password" class="form-control" id="password" name="password">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?= $user['nama_lengkap'] ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="role" class="form-label">Role</label>
                                            <select class="form-select" id="role" name="role" required>
                                                <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                                <option value="guru" <?= $user['role'] == 'guru' ? 'selected' : '' ?>>Guru</option>
                                                <option value="staff" <?= $user['role'] == 'staff' ? 'selected' : '' ?>>Staff</option>
                                            </select>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
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