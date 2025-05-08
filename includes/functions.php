<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Fungsi untuk menampilkan toast notification
 */
function setToast($title, $message, $type = 'info')
{
    $_SESSION['toast'] = [
        'title' => $title,
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Fungsi untuk redirect dengan toast notification
 */
function redirectWithToast($url, $title, $message, $type = 'info')
{
    setToast($title, $message, $type);
    header("Location: $url");
    exit();
}

/**
 * Fungsi untuk mendapatkan opsi select dari tabel database
 */
function getSelectOptions($table, $value_field, $text_field, $where = '', $params = [])
{
    global $pdo;

    $query = "SELECT $value_field, $text_field FROM $table";
    if (!empty($where)) {
        $query .= " WHERE $where";
    }
    $query .= " ORDER BY $text_field";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fungsi untuk memformat tanggal Indonesia
 */
function formatTanggalIndonesia($date, $with_time = false)
{
    if (empty($date) || $date == '0000-00-00') {
        return '-';
    }

    $hari = array('Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu');
    $bulan = array(
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    );

    $timestamp = strtotime($date);
    $day = date('w', $timestamp);
    $tanggal = date('j', $timestamp);
    $bulan_num = date('n', $timestamp);
    $tahun = date('Y', $timestamp);

    $result = $hari[$day] . ', ' . $tanggal . ' ' . $bulan[$bulan_num] . ' ' . $tahun;

    if ($with_time) {
        $result .= ' ' . date('H:i:s', $timestamp);
    }

    return $result;
}

/**
 * Fungsi untuk memformat angka ke Rupiah
 */
function formatRupiah($angka, $with_currency = true)
{
    if (!is_numeric($angka)) {
        return $angka;
    }

    $result = number_format($angka, 0, ',', '.');
    return $with_currency ? 'Rp ' . $result : $result;
}

/**
 * Fungsi untuk sanitasi input
 */
function sanitizeInput($data)
{
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Mengecek role pengguna
 * @param string|array $roles Role atau array of roles yang diizinkan
 * @return bool True jika role pengguna sesuai
 */
function hasRole($roles)
{
    if (!isset($_SESSION['role'])) {
        return false;
    }

    if (!is_array($roles)) {
        $roles = [$roles];
    }

    return in_array($_SESSION['role'], $roles);
}

// $current_uri = $_SERVER['REQUEST_URI'];
// function isActive($path)
// {
//     global $current_uri;
//     return strpos($current_uri, $path) !== false ? 'active' : '';
// }
