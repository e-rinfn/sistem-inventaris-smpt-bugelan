<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

// Hanya admin dan staff yang bisa menambah barang
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff') {
    header("Location: ../../dashboard.php");
    exit();
}

// Ambil data untuk dropdown
$kategori = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();
$lokasi = $pdo->query("SELECT * FROM lokasi ORDER BY nama_lokasi")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_barang = trim($_POST['kode_barang']);
    $nama_barang = $_POST['nama_barang'];
    $gambar = $_POST['gambar'] ?? null;
    $id_kategori = $_POST['id_kategori'];
    $id_lokasi = $_POST['id_lokasi'];
    $stok = $_POST['stok'] ?? 0;
    $satuan = $_POST['satuan'];
    $kondisi = $_POST['kondisi'];
    $keterangan = $_POST['keterangan'] ?? null;

    // Proses upload gambar
    $gambar = null;
    if (!empty($_FILES['gambar']['name'])) {
        $uploadDir = '../../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fileName = uniqid() . '_' . basename($_FILES['gambar']['name']);
        $targetFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetFilePath)) {
            $gambar = $fileName;
        }
    }

    try {
        // Generate kode barang jika kosong
        if (empty($kode_barang)) {
            $prefix = 'BRG';
            $last_code = $pdo->query("SELECT MAX(kode_barang) as last_code FROM barang WHERE kode_barang LIKE '$prefix%'")->fetch();
            $last_num = $last_code['last_code'] ? intval(substr($last_code['last_code'], strlen($prefix))) : 0;
            $kode_barang = $prefix . str_pad($last_num + 1, 4, '0', STR_PAD_LEFT);
        } else {
            // Cek apakah kode barang sudah ada
            $stmt_check = $pdo->prepare("SELECT id_barang FROM barang WHERE kode_barang = ?");
            $stmt_check->execute([$kode_barang]);
            if ($stmt_check->rowCount() > 0) {
                throw new Exception("Kode barang '$kode_barang' sudah digunakan. Silakan gunakan kode lain atau biarkan kosong untuk generate otomatis.");
            }
        }

        $stmt = $pdo->prepare("INSERT INTO barang (
            kode_barang, nama_barang, id_kategori, id_lokasi, stok, satuan, kondisi, keterangan, gambar
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $kode_barang,
            $nama_barang,
            $id_kategori,
            $id_lokasi,
            $stok,
            $satuan,
            $kondisi,
            $keterangan,
            $gambar,
        ]);

        header("Location: index.php?success=Barang+berhasil+ditambahkan");
        exit();
    } catch (PDOException $e) {
        $error = "Gagal menambahkan barang: " . $e->getMessage();
    } catch (Exception $e) {
        $error = $e->getMessage();
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

                        <h2>Tambah Barang</h2>
                        <a href="index.php" class="btn btn-secondary mb-3">
                            <i class="bx bx-arrow-back"></i> Kembali
                        </a>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <div class="card">
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="kode_barang" class="form-label">Kode Barang</label>
                                            <input type="text" class="form-control" id="kode_barang" name="kode_barang"
                                                placeholder="Kosongkan untuk generate otomatis">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="nama_barang" class="form-label">Nama Barang*</label>
                                            <input type="text" class="form-control" id="nama_barang" name="nama_barang" required>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="id_kategori" class="form-label">Kategori*</label>
                                            <select class="form-select" id="id_kategori" name="id_kategori" required>
                                                <option value="">Pilih Kategori</option>
                                                <?php foreach ($kategori as $item): ?>
                                                    <option value="<?= $item['id_kategori'] ?>"><?= htmlspecialchars($item['nama_kategori']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="id_lokasi" class="form-label">Lokasi*</label>
                                            <select class="form-select" id="id_lokasi" name="id_lokasi" required>
                                                <option value="">Pilih Lokasi</option>
                                                <?php foreach ($lokasi as $item): ?>
                                                    <option value="<?= $item['id_lokasi'] ?>"><?= htmlspecialchars($item['nama_lokasi']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <label for="stok" class="form-label">Stok Awal</label>
                                            <input type="number" class="form-control" id="stok" name="stok" min="0" value="0">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="satuan" class="form-label">Satuan*</label>
                                            <input type="text" class="form-control" id="satuan" name="satuan" required value="pcs">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="kondisi" class="form-label">Kondisi*</label>
                                            <select class="form-select" id="kondisi" name="kondisi" required>
                                                <option value="baik">Baik</option>
                                                <option value="rusak_ringan">Rusak Ringan</option>
                                                <option value="rusak_berat">Rusak Berat</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="keterangan" class="form-label">Keterangan</label>
                                        <textarea class="form-control" id="keterangan" name="keterangan" rows="4"></textarea>
                                    </div>

                                    <!-- Upload Gambar -->
                                    <!-- <div class="mb-3">
                                        <label for="gambar" class="form-label">Gambar Barang</label>
                                        <input type="file" name="gambar" id="gambar" class="form-control" accept="image/*">
                                    </div> -->

                                    <button type="submit" class="btn btn-primary">
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