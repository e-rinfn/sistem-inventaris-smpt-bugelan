<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';


// Hitung total barang
$query_barang = "SELECT COUNT(*) as total FROM barang";
$total_barang = $pdo->query($query_barang)->fetchColumn();

// Hitung total kategori
$query_kategori = "SELECT COUNT(*) as total FROM kategori";
$total_kategori = $pdo->query($query_kategori)->fetchColumn();



// Hitung total transaksi bulan ini
$query_transaksi = "SELECT 
  (SELECT COUNT(*) FROM barang_masuk WHERE DATE(tanggal_masuk) = CURDATE()) as masuk,
  (SELECT COUNT(*) FROM barang_keluar WHERE DATE(tanggal_keluar) = CURDATE()) as keluar,
  (SELECT COUNT(*) FROM barang_hilang WHERE DATE(tanggal_hilang) = CURDATE()) as hilang";

$transaksi = $pdo->query($query_transaksi)->fetch(PDO::FETCH_ASSOC);

// Ambil data stok minimum (barang yang stoknya kurang dari 5)
$query_stok_minimum = "SELECT b.nama_barang, b.stok, b.satuan, k.nama_kategori 
                       FROM barang b
                       JOIN kategori k ON b.id_kategori = k.id_kategori
                       WHERE b.stok < 5
                       ORDER BY b.stok ASC
                       LIMIT 5";
$stok_minimum = $pdo->query($query_stok_minimum)->fetchAll(PDO::FETCH_ASSOC);

// Ambil riwayat transaksi terakhir
$query_riwayat = "SELECT 
                    'masuk' as jenis, bm.tanggal_masuk as tanggal, b.nama_barang, bm.jumlah, 
                    CONCAT('Dari: ', IFNULL(s.nama_supplier, '-')) as detail, u.nama_lengkap as operator
                  FROM barang_masuk bm
                  JOIN barang b ON bm.id_barang = b.id_barang
                  LEFT JOIN supplier s ON bm.id_supplier = s.id_supplier
                  JOIN pengguna u ON bm.id_pengguna = u.id_pengguna
                  
                  UNION ALL
                  
                  SELECT 
                    'keluar' as jenis, bk.tanggal_keluar as tanggal, b.nama_barang, bk.jumlah, 
                    CONCAT('Untuk: ', IFNULL(bk.penerima, '-')) as detail, u.nama_lengkap as operator
                  FROM barang_keluar bk
                  JOIN barang b ON bk.id_barang = b.id_barang
                  JOIN pengguna u ON bk.id_pengguna = u.id_pengguna
                  
                  UNION ALL
                  
                  SELECT 
                    'hilang' as jenis, bh.tanggal_hilang as tanggal, b.nama_barang, bh.jumlah, 
                    CONCAT('Keterangan: ', IFNULL(bh.keterangan, '-')) as detail, u.nama_lengkap as operator
                  FROM barang_hilang bh
                  JOIN barang b ON bh.id_barang = b.id_barang
                  JOIN pengguna u ON bh.id_pengguna = u.id_pengguna
                  
                  ORDER BY tanggal DESC
                  LIMIT 7";
$riwayat_transaksi = $pdo->query($query_riwayat)->fetchAll(PDO::FETCH_ASSOC);


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
            <div class="row">
              <div class="col-lg-5 mb-4 order-0">
                <div class="card">
                  <div class="d-flex align-items-end row">
                    <div class="col-sm-12">
                      <div class="card-body">
                        <h5 class="card-title text-warning">Selamat datang, <?= htmlspecialchars($_SESSION['nama_lengkap']) ?>!</h5>
                        <hr>
                        <div class="d-flex justify-content-end">
                          <img src="/inventaris_smpt_bugelan/assets/img/illustrations/InventoryManagement.jpg" width="250px" alt="">
                        </div>
                        <p class="mb-3 mt-3" style="text-align: justify; text-indent: 30px;">
                          Aplikasi inventaris barang adalah sebuah sistem berbasis web yang digunakan untuk mencatat, mengelola, dan memantau
                          data barang secara efisien, mulai dari pencatatan data barang, transaksi masuk dan keluar, hingga pelaporan stok.
                        </p>
                        <p class="mb-4" style="text-align: justify; text-indent: 30px;">
                          Sistem ini dikembangkan khusus untuk mendukung kebutuhan <strong>SMP Terpadu Bugelan Tasikmalaya</strong> dalam mengelola inventaris secara tertib dan terorganisir.
                        </p>
                      </div>

                    </div>
                  </div>
                </div>
              </div>

              <!-- Card -->
              <div class="col-lg-7 col-md-4 order-1">
                <div class="row">

                  <!-- Total Barang -->
                  <div class="col-6 mb-4">
                    <div class="card">
                      <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between">
                          <div class="avatar flex-shrink-0">
                            <i class="bx bx-package fs-2 text-primary"></i>
                          </div>
                        </div>
                        <span class="fw-semibold d-block mb-1">Total Jenis Barang</span>
                        <h3 class="card-title mb-2"><?= $total_barang ?></h3>
                      </div>
                      <div class="card-footer bg-transparent border-top-0">
                        <a href="../barang/" class="text-dark stretched-link">Lihat detail</a>
                      </div>
                    </div>
                  </div>

                  <!-- Total Kategori -->
                  <div class="col-lg-6 col-md-12 col-6 mb-4">
                    <div class="card">
                      <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between">
                          <div class="avatar flex-shrink-0">
                            <i class="bx bx-list-check fs-2 text-secondary"></i>
                          </div>
                        </div>
                        <span class="fw-semibold d-block mb-1">Total Kategori</span>
                        <h3 class="card-title mb-2"><?= $total_kategori ?></h3>
                      </div>
                      <div class="card-footer bg-transparent border-top-0">
                        <a href="../kategori/" class="text-dark stretched-link">Lihat detail</a>
                      </div>
                    </div>
                  </div>

                  <!-- Barang Masuk Hari Ini -->
                  <div class="col-6 mb-4">
                    <div class="card">
                      <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between">
                          <div class="avatar flex-shrink-0">
                            <i class="bx bx-import fs-2 text-success"></i>
                          </div>
                        </div>
                        <span class="fw-semibold d-block mb-1">Barang Masuk Hari Ini</span>
                        <h3 class="card-title mb-2"><?= $transaksi['masuk'] ?></h3>
                      </div>
                      <div class="card-footer bg-transparent border-top-0">
                        <a href="../transaksi/masuk" class="text-dark stretched-link">Lihat detail</a>
                      </div>
                    </div>
                  </div>

                  <!-- Barang Keluar Hari Ini -->
                  <div class="col-lg-6 col-md-12 col-6 mb-4">
                    <div class="card">
                      <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between">
                          <div class="avatar flex-shrink-0">
                            <i class="bx bx-export fs-2 text-warning"></i>
                          </div>
                        </div>
                        <span class="fw-semibold d-block mb-1">Barang Keluar Hari Ini</span>
                        <h3 class="card-title mb-2"><?= $transaksi['keluar'] ?></h3>
                      </div>
                      <div class="card-footer bg-transparent border-top-0">
                        <a href="../laporan/transaksi.php" class="text-dark stretched-link">Lihat detail</a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>




              <!-- Riwayat inventaris -->
              <div class="col-12 col-lg-6 order-2 order-md-3 order-lg-2 mb-4">
                <div class="card h-100">
                  <div class="card-header bg-danger text-white">
                    <div class="d-flex justify-content-between align-items-center">
                      <h5 class="mb-0 text-white"><i class="bi bi-exclamation-triangle"></i> Stok Minimum</h5>
                      <span class="badge bg-white text-danger"><?= count($stok_minimum) ?> item</span>
                    </div>
                  </div>
                  <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <?php if (count($stok_minimum) > 0): ?>
                      <div class="table-responsive">
                        <table class="table table-sm table-hover">
                          <thead>
                            <tr>
                              <th>No</th>
                              <th>Nama Barang</th>
                              <th>Kategori</th>
                              <th>Stok</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($stok_minimum as $key => $item): ?>
                              <tr>
                                <td><?= $key + 1 ?></td>
                                <td><?= htmlspecialchars($item['nama_barang']) ?></td>
                                <td><?= htmlspecialchars($item['nama_kategori']) ?></td>
                                <td>
                                  <span class="badge bg-danger"><?= $item['stok'] ?> <?= $item['satuan'] ?></span>
                                </td>
                              </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    <?php else: ?>
                      <div class="alert alert-success mt-3 mb-0">
                        <i class="bi bi-check-circle"></i> Tidak ada barang dengan stok minimum
                      </div>
                    <?php endif; ?>
                  </div>
                  <div class="card-footer">
                    <a href="/inventaris_smpt_bugelan/pages/barang/index.php?filter=stok_minimum" class="btn btn-sm btn-outline-danger">
                      Lihat Semua <i class="bi bi-arrow-right"></i>
                    </a>
                  </div>
                </div>
              </div>
              <!--/ Riwayat Inventaris -->


              <div class="col-12 col-lg-6 order-2 order-md-3 order-lg-2 mb-4">
                <div class="card h-100">
                  <div class="card-header bg-warning text-white">
                    <h5 class="mb-0 text-white"><i class="bi bi-clock-history"></i> Riwayat Transaksi Terakhir</h5>
                  </div>
                  <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <div class="timeline mt-3">
                      <?php foreach ($riwayat_transaksi as $transaksi): ?>
                        <div class="timeline-item">
                          <div class="timeline-icon 
                                <?= $transaksi['jenis'] == 'masuk' ? 'bg-success' : ($transaksi['jenis'] == 'keluar' ? 'bg-warning text-dark' : 'bg-danger') ?>">
                            <i class="bi 
                                    <?= $transaksi['jenis'] == 'masuk' ? 'bi-box-arrow-in-down' : ($transaksi['jenis'] == 'keluar' ? 'bi-box-arrow-up' : 'bi-exclamation-triangle') ?>">
                            </i>
                          </div>
                          <div class="timeline-content">
                            <div class="d-flex justify-content-between">
                              <h6 class="mb-1"><?= htmlspecialchars($transaksi['nama_barang']) ?></h6>
                              <small class="text-muted"><?= date('d M Y ', strtotime($transaksi['tanggal'])) ?></small>
                            </div>
                            <p class="mb-1">
                              <span class="badge 
                                        <?= $transaksi['jenis'] == 'masuk' ? 'bg-success' : ($transaksi['jenis'] == 'keluar' ? 'bg-warning text-dark' : 'bg-danger') ?>">
                                <?= $transaksi['jenis'] == 'masuk' ? 'Masuk' : ($transaksi['jenis'] == 'keluar' ? 'Keluar' : 'Hilang') ?>
                              </span>
                              <span class="ms-2"><?= $transaksi['jumlah'] ?> unit</span>
                            </p>
                            <small class="text-muted">
                              <?= htmlspecialchars($transaksi['detail']) ?> •
                              Oleh: <?= htmlspecialchars($transaksi['operator']) ?>
                            </small>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                  <div class="card-footer">
                    <a href="modules/laporan/transaksi.php" class="btn btn-sm btn-outline-primary">
                      Lihat Semua <i class="bi bi-arrow-right"></i>
                    </a>
                  </div>
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


  <style>
    /* Timeline Style */
    .timeline {
      position: relative;
      padding-left: 1rem;
    }

    .timeline-item {
      position: relative;
      padding-bottom: 1.5rem;
      padding-left: 2rem;
      border-left: 1px solid #dee2e6;
    }

    .timeline-item:last-child {
      padding-bottom: 0;
    }

    .timeline-icon {
      position: absolute;
      left: -1rem;
      width: 2.5rem;
      height: 2.5rem;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.1rem;
    }

    .timeline-content {
      padding: 0.5rem 0;
    }
  </style>

  <?php include '../../includes/footer.php'; ?>