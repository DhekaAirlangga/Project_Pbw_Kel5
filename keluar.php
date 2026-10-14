<?php
session_start();
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$plat = normalisasiPlat(trim($_POST['plat'] ?? ''));
if (!validasiPlat($plat)) {
    $_SESSION['error'] = ['Plat nomor untuk proses keluar tidak valid.'];
    header('Location: index.php#data-parkir');
    exit;
}

$data = $_SESSION['kendaraan'] ?? dataContoh();
$found = false;

// Telusuri daftar kendaraan dan ubah status kendaraan yang masih parkir.
for ($i = 0; $i < count($data); $i++) {
    if ($data[$i]['plat'] === $plat && $data[$i]['status'] === 'Parkir') {
        $data[$i]['status'] = 'Keluar';
        $data[$i]['keluar'] = date('H:i');
        // Biaya menggunakan durasi simulasi yang dimasukkan saat kendaraan masuk.
        $data[$i]['biaya'] = hitungBiayaParkir($data[$i]['tipe'], (int) $data[$i]['durasi']);
        $found = true;
        break;
    }
}

if ($found) {
    $_SESSION['kendaraan'] = $data;
    $_SESSION['sukses'] = 'Kendaraan ' . $plat . ' berhasil dicatat keluar. Pendapatan ' . formatRupiah($data[$i]['biaya']) . ' masuk ke laporan.';
} else {
    $_SESSION['kendaraan'] = $data;
    $_SESSION['error'] = ['Kendaraan tidak ditemukan atau sudah berstatus Keluar.'];
}

header('Location: index.php#data-parkir');
exit;
