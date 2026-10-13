<?php
// ==========================================
// proses.php
// Menerima data form, merapikan, memvalidasi,
// menghitung biaya, lalu menyimpan ke session.
// Bagian A: variabel, operator, string, function.
// ==========================================
session_start();
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/classes/Kendaraan.php';

// Hanya terima kiriman dari form (method POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// 1. Ambil data dari form ke variabel
$platMentah = trim($_POST['plat'] ?? '');
$tipe = trim($_POST['tipe'] ?? '');
$durasiMentah = trim($_POST['durasi'] ?? '');

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
if (!validasiDurasi($durasiMentah)) {
    $error[] = 'Durasi harus berupa bilangan bulat 1 sampai ' . DURASI_MAKSIMAL . ' jam.';
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

// 5. Jika ada error, kembali ke form membawa pesan
if ($error) {
    $_SESSION['error'] = $error;
    header('Location: index.php#input-kendaraan');
    exit;
}

try {
    // OOP: buat object Kendaraan setelah data lolos validasi.
    $kendaraan = new Kendaraan($plat, $tipe, (int) $durasiMentah);
    $data[] = $kendaraan->toArray();
    $_SESSION['kendaraan'] = $data;
    $_SESSION['sukses'] = 'Kendaraan ' . $kendaraan->getPlat() . ' berhasil ditambahkan. Biaya estimasi: ' . formatRupiah($kendaraan->getBiaya()) . '.';
} catch (InvalidArgumentException $e) {
    $_SESSION['error'] = [$e->getMessage()];
}

header('Location: index.php#data-parkir');
exit;
