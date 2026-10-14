<?php
session_start();
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$plat = normalisasiPlat(trim($_POST['plat'] ?? ''));
$keluarEpochMentah = trim($_POST['keluar_epoch'] ?? '');
$keluarLokal = trim($_POST['keluar_lokal'] ?? '');
if (!validasiPlat($plat)) {
    $_SESSION['error'] = ['Plat nomor untuk proses keluar tidak valid.'];
    header('Location: index.php#data-parkir');
    exit;
}
if (filter_var($keluarEpochMentah, FILTER_VALIDATE_INT) === false
    || (int) $keluarEpochMentah <= 0
    || preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $keluarLokal) !== 1
) {
    $_SESSION['error'] = ['Waktu keluar dari perangkat tidak valid. Muat ulang halaman lalu coba lagi.'];
    header('Location: index.php#data-parkir');
    exit;
}

$data = $_SESSION['kendaraan'] ?? dataContoh();
$found = false;

// Telusuri daftar kendaraan dan ubah status kendaraan yang masih parkir.
for ($i = 0; $i < count($data); $i++) {
    if ($data[$i]['plat'] === $plat && $data[$i]['status'] === 'Parkir') {
        if (isset($data[$i]['masukEpoch']) && (int) $keluarEpochMentah < (int) $data[$i]['masukEpoch']) {
            $_SESSION['error'] = ['Jam perangkat lebih awal dari waktu masuk. Periksa jam laptop lalu coba lagi.'];
            header('Location: index.php#data-parkir');
            exit;
        }
        $data[$i]['status'] = 'Keluar';
        $data[$i]['keluar'] = $keluarLokal;
        $data[$i]['keluarEpoch'] = (int) $keluarEpochMentah;
        if (!isset($data[$i]['masukEpoch'])) {
            $data[$i]['masukEpoch'] = (int) $keluarEpochMentah - ((int) $data[$i]['durasi'] * 3600);
        }
        $data[$i]['durasi'] = hitungDurasiParkir(
            (int) $data[$i]['masukEpoch'],
            (int) $keluarEpochMentah
        );
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
