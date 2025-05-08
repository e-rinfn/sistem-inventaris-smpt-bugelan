<?php
session_start();

if (!isset($_SESSION['id_pengguna'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Cek role jika diperlukan
$allowed_roles = ['admin', 'guru', 'staff'];
if (isset($_GET['role']) && !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: ../dashboard.php");
    exit();
}
