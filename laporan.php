<?php
session_start();
require_once __DIR__ . '/functions.php';

$dataKendaraan = $_SESSION['kendaraan'] ?? dataContoh();
$stat = hitungStatistik($dataKendaraan);
$filter = $_GET['status'] ?? 'Semua';
if (!in_array($filter, ['Semua', 'Parkir', 'Keluar'], true)) {
    $filter = 'Semua';
}
$dataLaporan = array_values(array_filter($dataKendaraan, function ($item) use ($filter) {
    return $filter === 'Semua' || ($item['status'] ?? '') === $filter;
}));
function e($value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Parkir | PARKIRKU</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="navbar">
    <div class="brand">PARKIR<span>KU</span></div>
    <nav aria-label="Navigasi utama"><a href="index.php#dashboard">Dashboard</a><a href="index.php#input-kendaraan">Input Kendaraan</a><a href="index.php#data-parkir">Data Parkir</a><a href="laporan.php">Laporan</a></nav>
</header>
<main class="container">
    <section class="header">
        <div><h1>Laporan Parkir</h1><p>Rekap kendaraan dan pendapatan berdasarkan status transaksi.</p></div>
        <span class="badge">LAPORAN</span>
    </section>
    <section class="stats">
        <div class="stat-card"><p>Total Kendaraan</p><h2><?= $stat['total'] ?></h2></div>
        <div class="stat-card"><p>Sedang Parkir</p><h2><?= $stat['sedangParkir'] ?></h2></div>
        <div class="stat-card"><p>Total Pendapatan</p><h2><?= formatRupiah($stat['pendapatan']) ?></h2></div>
    </section>
    <section class="panel">
        <div class="report-top">
            <div><h2 style="margin-bottom:5px;">Rincian Kendaraan</h2><p class="muted" style="font-size:13px;">Pendapatan menjumlahkan biaya kendaraan berstatus Keluar.</p></div>
            <div class="form-actions no-print" style="margin-top:0;">
                <a href="index.php" class="button button-light">← Dashboard</a>
                <button type="button" class="button" onclick="window.print()">Cetak Laporan</button>
            </div>
        </div>
        <form method="get" class="filter no-print" style="margin-bottom:20px;">
            <label for="status">Filter status:</label>
            <select id="status" name="status">
                <option value="Semua" <?= $filter === 'Semua' ? 'selected' : '' ?>>Semua</option>
                <option value="Parkir" <?= $filter === 'Parkir' ? 'selected' : '' ?>>Sedang Parkir</option>
                <option value="Keluar" <?= $filter === 'Keluar' ? 'selected' : '' ?>>Sudah Keluar</option>
            </select>
            <button class="button">Terapkan</button>
        </form>
        <div class="table-wrapper">
        <table>
            <thead><tr><th>No.</th><th>Plat Nomor</th><th>Tipe</th><th>Jam Masuk</th><th>Jam Keluar</th><th>Durasi</th><th>Biaya</th><th>Status</th></tr></thead>
            <tbody>
            <?php if (count($dataLaporan) === 0): ?>
                <tr><td colspan="8" style="text-align:center;padding:24px;">Tidak ada data untuk filter ini.</td></tr>
            <?php else: ?>
                <?php
                // DO-WHILE: menampilkan laporan minimal satu kali ketika data tersedia.
                // Dalam aplikasi web, pengulangan input dilakukan dengan kembali ke form dan submit ulang.
                $i = 0;
                do {
                    $kendaraan = $dataLaporan[$i];
                ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($kendaraan['plat']) ?></td>
                    <td><?= e($kendaraan['tipe']) ?></td>
                    <td><?= e($kendaraan['masuk']) ?></td>
                    <td><?= e($kendaraan['keluar'] ?? '-') ?></td>
                    <td><?= formatDurasi((int) $kendaraan['durasi']) ?></td>
                    <td><?= formatRupiah((int) $kendaraan['biaya']) ?></td>
                    <td><span class="status <?= $kendaraan['status'] === 'Parkir' ? 'status-parkir' : 'status-keluar' ?>"><?= e($kendaraan['status']) ?></span></td>
                </tr>
                <?php
                    $i++;
                } while ($i < count($dataLaporan));
                ?>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
        <p class="muted" style="font-size:12px;margin-top:15px;">Jumlah pada filter ini: <?= count($dataLaporan) ?> kendaraan.</p>
    </section>
    <section class="panel">
        <h2>Ringkasan Pendapatan</h2>
        <div class="row"><span>Total kendaraan selesai</span><strong><?= $stat['keluar'] ?> kendaraan</strong></div>
        <div class="row"><span>Total pendapatan kendaraan keluar</span><strong><?= formatRupiah($stat['pendapatan']) ?></strong></div>
        <p class="muted" style="font-size:12px;line-height:1.6;margin-top:15px;">Catatan: durasi pada project ini merupakan durasi simulasi yang dimasukkan saat kendaraan masuk, bukan selisih waktu masuk dan keluar aktual.</p>
    </section>
</main>
<footer class="footer">PARKIRKU · Laporan Parkir</footer>
</body>
</html>
