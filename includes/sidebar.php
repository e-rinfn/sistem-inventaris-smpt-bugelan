<?php
require_once(__DIR__ . '/../config/database.php');
require_once 'auth_check.php';

// Untuk pengembangan
$base_url = '/sistem-inventaris-smpt-bugelan';

// Untuk localhost
// $base_url = 'https://192.168.0.115:8083';

// Untuk deployment ke server online
// $base_url = 'https://nwn.erinfn.my.id';

$current_uri = $_SERVER['REQUEST_URI'];

function isActive($path)
{
  global $current_uri;
  return strpos($current_uri, $path) !== false ? 'active' : '';
}

?>

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
  <div class="app-brand demo">
    <a href="<?= $base_url ?>/pages/dashboard/index.php" class="app-brand-link">
      <span class="app-brand-logo demo">
        <img src="<?= $base_url ?>/assets/img/Logo.png" alt="Logo" width="50" height="50">
      </span>
      <span class="menu-text fw-medium fs-6 ms-2">Inventaris Barang SMP Terpadu Bugelan</span>
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
      <i class="bx bx-chevron-left bx-sm align-middle"></i>
    </a>
  </div>
  <hr>

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">
    <!-- Dashboard -->
    <li class="menu-item <?= isActive('/dashboard') ?>">
      <a href="<?= $base_url ?>/pages/dashboard/index.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-home-circle"></i>
        <div data-i18n="Analytics">Dashboard</div>
      </a>
    </li>



    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Operasi</span>
    </li>

    <li class="menu-item <?= isActive('/barang') ?>">
      <a href="<?= $base_url ?>/pages/barang/index.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-package"></i>
        <div data-i18n="Analytics">Data Barang</div>
      </a>
    </li>

    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class='menu-icon tf-icons bx bx-selection'></i>
        <div data-i18n="Layouts">Entitas</div>
      </a>

      <ul class="menu-sub">
        <li class="menu-item <?= isActive('/kategori') ?>">
          <a href="<?= $base_url ?>/pages/kategori/index.php" class="menu-link">
            <div data-i18n="Without navbar">Kategori</div>
          </a>
        </li>
        <li class="menu-item <?= isActive('/lokasi') ?>">
          <a href="<?= $base_url ?>/pages/lokasi/index.php" class="menu-link">
            <div data-i18n="Without menu">Lokasi</div>
          </a>
        </li>
        <li class="menu-item <?= isActive('/supplier') ?>">
          <a href="<?= $base_url ?>/pages/supplier/index.php" class="menu-link">
            <div data-i18n="Without navbar">Supplier</div>
          </a>
        </li>
      </ul>
    </li>



    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Transaksi</span>
    </li>


    <!-- Dashboard -->
    <li class="menu-item <?= isActive('/transaksi/masuk') ?>">
      <a href="<?= $base_url ?>/pages/transaksi/masuk/index.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-import"></i>
        <div data-i18n="Analytics">Barang Masuk</div>
      </a>
    </li>

    <!-- Dashboard -->
    <li class="menu-item <?= isActive('/transaksi/keluar') ?>">
      <a href="<?= $base_url ?>/pages/transaksi/keluar/index.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-export"></i>
        <div data-i18n="Analytics">Barang Keluar</div>
      </a>
    </li>

    <li class="menu-item <?= isActive('/transaksi/hilang') ?>">
      <a href="<?= $base_url ?>/pages/transaksi/hilang/index.php" class="menu-link">
        <i class="menu-icon tf-icons bx bxs-error-circle"></i>
        <div data-i18n="Analytics">Barang Hilang</div>
      </a>
    </li>

    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Laporan</span>
    </li>

    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class='menu-icon tf-icons bx bx-package'></i>
        <div data-i18n="Layouts">Laporan</div>
      </a>

      <ul class="menu-sub">
        <li class="menu-item <?= isActive('/laporan/stok.php') ?>">
          <a href="<?= $base_url ?>/pages/laporan/stok.php" class="menu-link">
            <div data-i18n="Without navbar">Stok Barang</div>
          </a>
        </li>
        <li class="menu-item <?= isActive('/laporan/transaksi.php') ?>">
          <a href="<?= $base_url ?>/pages/laporan/transaksi.php" class="menu-link">
            <div data-i18n="Without menu">Transaksi</div>
          </a>
        </li>
      </ul>
    </li>

    <!-- Misc -->
    <li class="menu-header small text-uppercase"><span class="menu-header-text">Bantuan</span></li>
    <li class="menu-item <?= isActive('/pages/panduan') ?>">
      <a href="<?= $base_url ?>/pages/panduan/index.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-help-circle"></i>
        <div data-i18n="Panduan">Panduan Penggunaan</div>
      </a>
    </li>
  </ul>
</aside>