<?php
// Load konfigurasi
require_once(__DIR__ . '/config/config.php');

// Pastikan variabel $base_url tersedia dan tidak kosong
if (!isset($base_url) || empty($base_url)) {
    die("Konfigurasi base_url tidak ditemukan.");
}

// Redirect ke halaman login
header("Location: {$base_url}/pages/auth/login.php");
exit;
