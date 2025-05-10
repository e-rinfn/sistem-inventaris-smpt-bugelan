<?php
session_start();

require_once(__DIR__ . '../../config/config.php');

// Cek apakah user belum login
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: " . $base_url . "/pages/auth/login.php");
    exit();
}

// Cek role jika diperlukan
$allowed_roles = ['admin', 'guru', 'staff'];
if (isset($_GET['role']) && !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: " . $base_url . "/pages/dashboard/index.php");
    exit();
}
