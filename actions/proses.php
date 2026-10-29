<?php
// ==========================================
// proses.php
// Menerima data form, merapikan, memvalidasi,
// menghitung biaya, lalu menyimpan ke session.
// Bagian A: variabel, operator, string, function.
// ==========================================
session_start();
require_once __DIR__ . '/../tools/functions.php';
require_once __DIR__ . '/../classes/Kendaraan.php';

// Hanya terima kiriman dari form (method POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../dashboard/dashboard_admin.php');
    exit;
}

// 1. Ambil data dari form ke variabel
$platMentah = trim($_POST['plat'] ?? '');
$tipe = trim($_POST['tipe'] ?? '');
$masukEpochMentah = trim($_POST['masuk_epoch'] ?? '');
$masukLokal = trim($_POST['masuk_lokal'] ?? '');

// 2. Rapikan plat nomor (string)
$plat = normalisasiPlat($platMentah);

// 3. Validasi, kumpulkan pesan error ke array
$error = [];

if (!validasiPlat($plat)) {
    $error[] = 'Format plat tidak valid. Contoh: B 1234 ABC.';
}
if (!validasiTipe($tipe)) {
    $error[] = 'Tipe kendaraan harus Motor atau Mobil.';
}
if (filter_var($masukEpochMentah, FILTER_VALIDATE_INT) === false
    || (int) $masukEpochMentah <= 0
    || preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $masukLokal) !== 1
) {
    $error[] = 'Waktu masuk dari perangkat tidak valid. Muat ulang halaman lalu coba lagi.';
}

$data = $_SESSION['kendaraan'] ?? dataContoh();

// 4. Cek plat yang sama masih berstatus Parkir
// WHILE: memeriksa apakah plat yang sama masih berada di area parkir.
$idx = 0;
$platKembar = false;
while ($idx < count($data)) {
    if (($data[$idx]['plat'] ?? '') === $plat && ($data[$idx]['status'] ?? '') === 'Parkir') {
        $platKembar = true;
        break;
    }
    $idx++;
}
if ($platKembar) {
    $error[] = 'Kendaraan ' . $plat . ' masih berstatus Parkir.';
}
if (validasiTipe($tipe)) {
    $ketersediaan = hitungKetersediaanParkir($data);
    if ($ketersediaan[$tipe]['tersedia'] === 0) {
        $error[] = 'Kapasitas parkir ' . strtolower($tipe) . ' sudah penuh.';
    }
}

// 5. Jika ada error, kembali ke form membawa pesan
if ($error) {
    $_SESSION['error'] = $error;
    header('Location: ../dashboard/dashboard_admin.php#input-kendaraan');
    exit;
}

try {
    // OOP: buat object Kendaraan setelah data lolos validasi.
    $peran = trim($_POST['peran'] ?? ($_SESSION['role'] ?? 'Dosen/Staf/Mahasiswa'));
    if (!in_array($peran, ['Dosen/Staf/Mahasiswa', 'Mitra'], true)) {
        $peran = 'Dosen/Staf/Mahasiswa';
    }
    $kendaraan = new Kendaraan($plat, $tipe, (int) $masukEpochMentah, $masukLokal, $peran);
    $data[] = $kendaraan->toArray();
    $_SESSION['kendaraan'] = $data;
    $_SESSION['sukses'] = 'Kendaraan ' . $kendaraan->getPlat() . ' (' . $kendaraan->getPeran() . ') berhasil ditambahkan. Durasi dan biaya dihitung saat kendaraan keluar.';
} catch (InvalidArgumentException $e) {
    $_SESSION['error'] = [$e->getMessage()];
}

header('Location: ../dashboard/dashboard_admin.php#data-parkir');
exit;
