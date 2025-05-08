<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="../../../assets/" data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Panduan Sistem Inventaris</title>


    <!-- Include CSS -->
    <?php include '../../includes/header.php'; ?>

    <style>
        .guide-section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            background-color: #f8f9fa;
            border-radius: 0.5rem;
        }

        .guide-step {
            margin-bottom: 1rem;
            padding-left: 1.5rem;
            border-left: 3px solid #696cff;
        }

        .guide-img {
            max-width: 100%;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
            margin: 10px 0;
        }
    </style>
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Sidebar -->
            <?php include '../../includes/sidebar.php'; ?>
            <!-- /Sidebar -->

            <div class="layout-page">
                <!-- Navbar -->
                <?php include '../../includes/navbar.php'; ?>
                <!-- /Navbar -->

                <div class="content-wrapper">
                    <div class="text-center mb-4 mt-5">
                        <img src="../../assets/img/Logo.png" alt="Logo" style="height: 100px; margin-bottom: 10px;">
                        <h2>Panduan Penggunaan <br> Sistem Inventaris Barang</h2>
                    </div>


                    <div class="guide-section">
                        <h3>1. Pendahuluan</h3>
                        <p>
                            Sistem Inventaris Barang adalah aplikasi untuk mengelola stok barang, transaksi masuk/keluar, dan laporan inventaris sekolah.
                            Sistem ini dirancang khusus untuk mendukung kebutuhan pengelolaan inventaris barang di <strong>SMP Terpadu Bugelan, Tasikmalaya</strong>,
                            agar proses pencatatan dan pelaporan menjadi lebih efisien dan terorganisir.
                        </p>
                    </div>


                    <div class="guide-section">
                        <h3>2. Master Data</h3>

                        <div class="guide-step">
                            <h5>2.1 Manajemen Barang</h5>
                            <p>Untuk menambahkan barang baru:</p>
                            <ol>
                                <li>Pilih menu <strong>Data Barang</strong></li>
                                <li>Klik tombol <strong>Tambah Barang</strong></li>
                                <li>Isi form dengan data lengkap</li>
                                <li>Klik <strong>Simpan</strong></li>
                            </ol>
                            <p><em>Catatan: Kode barang bisa dikosongkan untuk generate otomatis</em></p>
                        </div>

                        <div class="guide-step">
                            <h5>2.2 Kategori, Lokasi, dan Supplier</h5>
                            <p>Sebelum menambah barang, pastikan kategori dan lokasi sudah dibuat:</p>
                            <ol>
                                <li>Pilih menu <strong>Entitas > Kategori Barang</strong></li>
                                <li>Klik <strong>Tambah Kategori</strong></li>
                                <li>Isi nama kategori dan deskripsi</li>
                                <li>Ulangi langkah serupa untuk Lokasi dan Supplier Barang</li>
                            </ol>
                        </div>
                    </div>

                    <div class="guide-section">
                        <h3>3. Transaksi Barang</h3>

                        <div class="guide-step">
                            <h5>3.1 Barang Masuk</h5>
                            <p>Untuk mencatat barang masuk:</p>
                            <ol>
                                <li>Pilih menu <strong>Transaksi > Barang Masuk</strong></li>
                                <li>Klik <strong>Tambah Barang Masuk</strong></li>
                                <li>Pilih barang dan isi jumlah</li>
                                <li>Tambah data supplier (jika ada)</li>
                                <li>Klik <strong>Simpan</strong></li>
                            </ol>
                            <p><em>Stok akan otomatis bertambah setelah transaksi disimpan</em></p>
                        </div>

                        <div class="guide-step">
                            <h5>3.2 Barang Keluar</h5>
                            <p>Prosedur pengeluaran barang:</p>
                            <ol>
                                <li>Pilih menu <strong>Transaksi > Barang Keluar</strong></li>
                                <li>Klik <strong>Tambah Barang Keluar</strong></li>
                                <li>Pilih barang dan isi jumlah</li>
                                <li>Tulis tujuan/keperluan</li>
                                <li>Klik <strong>Simpan</strong></li>
                            </ol>
                        </div>

                        <div class="guide-step">
                            <h5>3.3 Barang Hilang</h5>
                            <p>Untuk melaporkan barang hilang:</p>
                            <ol>
                                <li>Pilih menu <strong>Transaksi > Barang Hilang</strong></li>
                                <li>Klik <strong>Tambah Laporan</strong></li>
                                <li>Pilih barang dan isi jumlah</li>
                                <li>Tulis keterangan kejadian</li>
                                <li>Klik <strong>Simpan</strong></li>
                            </ol>
                        </div>
                    </div>

                    <div class="guide-section">
                        <h3>4. Laporan</h3>

                        <div class="guide-step">
                            <h5>4.1 Laporan Stok</h5>
                            <p>Untuk melihat stok barang:</p>
                            <ol>
                                <li>Pilih menu <strong>Laporan > Stok Barang</strong></li>
                                <li>Gunakan filter untuk melihat data tertentu</li>
                                <li>Klik <strong>Cetak</strong> untuk versi PDF</li>
                            </ol>
                        </div>

                        <div class="guide-step">
                            <h5>4.2 Laporan Transaksi</h5>
                            <p>Cara melihat riwayat transaksi:</p>
                            <ol>
                                <li>Pilih menu <strong>Laporan > Transaksi</strong></li>
                                <li>Atur periode tanggal</li>
                                <li>Pilih jenis transaksi (opsional)</li>
                                <li>Klik <strong>Filter</strong> untuk melihat data</li>
                            </ol>
                        </div>
                    </div>

                    <div class="guide-section">
                        <h3>5. Manajemen Pengguna</h3>
                        <p><em>Hanya untuk Administrator</em></p>
                        <div class="guide-step">
                            <ol>
                                <li>Pilih menu <strong>Pengguna</strong></li>
                                <li>Untuk tambah pengguna baru, klik <strong>Tambah Pengguna</strong></li>
                                <li>Isi data lengkap termasuk role/akses</li>
                                <li>Klik <strong>Simpan</strong></li>
                            </ol>
                        </div>
                    </div>

                    <div class="alert alert-info m-4">
                        <h5>Bantuan Tambahan</h5>
                        <p>Jika menemui kendala, silakan hubungi Administrator sistem.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include footer dan scripts -->
    <?php include '../../includes/footer.php'; ?>
    </div>
</body>

</html>