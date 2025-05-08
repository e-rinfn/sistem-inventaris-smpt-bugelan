<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

// Hanya admin dan staff yang bisa mengedit barang
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff') {
    header("Location: ../../dashboard.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php?error=ID+tidak+valid");
    exit();
}

$id = $_GET['id'];

// Ambil data barang
$stmt = $pdo->prepare("SELECT * FROM barang WHERE id_barang = ?");
$stmt->execute([$id]);
$barang = $stmt->fetch();

if (!$barang) {
    header("Location: index.php?error=Barang+tidak+ditemukan");
    exit();
}

// Ambil data untuk dropdown
$kategori = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();
$lokasi = $pdo->query("SELECT * FROM lokasi ORDER BY nama_lokasi")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_barang = $_POST['kode_barang'];
    $nama_barang = $_POST['nama_barang'];
    $id_kategori = $_POST['id_kategori'];
    $id_lokasi = $_POST['id_lokasi'];
    $stok = $_POST['stok'] ?? 0;
    $satuan = $_POST['satuan'];
    $kondisi = $_POST['kondisi'];
    $keterangan = $_POST['keterangan'] ?? null;

    // Proses upload gambar jika ada
    $gambar = $barang['gambar'];
    if (!empty($_FILES['gambar']['name'])) {
        $uploadDir = '../../uploads/';
        $fileName = uniqid() . '_' . basename($_FILES['gambar']['name']);
        $targetFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetFilePath)) {
            $gambar = $fileName;
        }
    }

    try {
        $stmt = $pdo->prepare("UPDATE barang SET
            kode_barang = ?, nama_barang = ?, id_kategori = ?, id_lokasi = ?,
            stok = ?, satuan = ?, kondisi = ?, keterangan = ?, gambar = ?
            WHERE id_barang = ?");

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
            $id
        ]);

        header("Location: index.php?success=Barang+berhasil+diupdate");
        exit();
    } catch (PDOException $e) {
        $error = "Gagal mengupdate barang: " . $e->getMessage();
    }
}
?>

<?php include '../../includes/header.php'; ?>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include '../../includes/sidebar.php'; ?>

            <div class="layout-page">
                <?php include '../../includes/navbar.php'; ?>

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <h2>Edit Barang</h2>
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
                                                value="<?= htmlspecialchars($barang['kode_barang']) ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="nama_barang" class="form-label">Nama Barang*</label>
                                            <input type="text" class="form-control" id="nama_barang" name="nama_barang"
                                                value="<?= htmlspecialchars($barang['nama_barang']) ?>" required>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="id_kategori" class="form-label">Kategori*</label>
                                            <select class="form-select" id="id_kategori" name="id_kategori" required>
                                                <option value="">Pilih Kategori</option>
                                                <?php foreach ($kategori as $item): ?>
                                                    <option value="<?= $item['id_kategori'] ?>" <?= $barang['id_kategori'] == $item['id_kategori'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($item['nama_kategori']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="id_lokasi" class="form-label">Lokasi*</label>
                                            <select class="form-select" id="id_lokasi" name="id_lokasi" required>
                                                <option value="">Pilih Lokasi</option>
                                                <?php foreach ($lokasi as $item): ?>
                                                    <option value="<?= $item['id_lokasi'] ?>" <?= $barang['id_lokasi'] == $item['id_lokasi'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($item['nama_lokasi']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <label for="stok" class="form-label">Stok</label>
                                            <input type="number" class="form-control" id="stok" name="stok"
                                                min="0" value="<?= htmlspecialchars($barang['stok']) ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="satuan" class="form-label">Satuan*</label>
                                            <input type="text" class="form-control" id="satuan" name="satuan"
                                                value="<?= htmlspecialchars($barang['satuan']) ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="kondisi" class="form-label">Kondisi*</label>
                                            <select class="form-select" id="kondisi" name="kondisi" required>
                                                <option value="baik" <?= $barang['kondisi'] == 'baik' ? 'selected' : '' ?>>Baik</option>
                                                <option value="rusak_ringan" <?= $barang['kondisi'] == 'rusak_ringan' ? 'selected' : '' ?>>Rusak Ringan</option>
                                                <option value="rusak_berat" <?= $barang['kondisi'] == 'rusak_berat' ? 'selected' : '' ?>>Rusak Berat</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="keterangan" class="form-label">Keterangan</label>
                                        <textarea class="form-control" id="keterangan" name="keterangan" rows="4"><?= htmlspecialchars($barang['keterangan']) ?></textarea>
                                    </div>

                                    <!-- Upload Gambar -->
                                    <!-- <div class="mb-3 d-flex">
                                        <div class="col-md-3 me-3">
                                            <label for="gambar" class="form-label">Gambar Barang</label>
                                            <input type="file" name="gambar" id="gambar" class="form-control" accept="image/*">
                                        </div>
                                        <?php if ($barang['gambar']): ?>
                                            <div class="mt-3 p-2 bg-light border text-center">
                                                <img src="../../uploads/<?= htmlspecialchars($barang['gambar']) ?>" width="300" alt="Preview Gambar">
                                            </div>
                                        <?php endif; ?>
                                    </div> -->

                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-save"></i> Update
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>

                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>