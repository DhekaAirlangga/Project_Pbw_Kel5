<?php
session_start();
require_once __DIR__ . '/../tools/functions.php';

$dataKendaraan = $_SESSION['kendaraan'] ?? dataContoh();
$waktuSekarang = time();
for ($i = 0; $i < count($dataKendaraan); $i++) {
    if ($dataKendaraan[$i]['status'] === 'Parkir' && isset($dataKendaraan[$i]['masukEpoch'])) {
        $dataKendaraan[$i]['durasi'] = hitungDurasiParkir(
            (int) $dataKendaraan[$i]['masukEpoch'],
            $waktuSekarang
        );
        $dataKendaraan[$i]['biaya'] = hitungBiayaParkir(
            $dataKendaraan[$i]['tipe'],
            (int) $dataKendaraan[$i]['durasi']
        );
    }
}
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
    <title>Laporan Transaksi Parkir | Universitas Pancasila</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" type="image/png" href="../assets/images/Logo UP.png">
</head>
<body>
<header class="navbar">
    <div style="display:flex;align-items:center;gap:12px;">
        <img src="../assets/images/Navbar.png" alt="Universitas Pancasila" style="height:42px;">
        <div class="brand-divider" style="height:30px;"></div>
        <div class="brand-text-block">
            <span class="brand-text-top">REKAPITULASI</span>
            <span class="brand-text-main" style="font-size:15px;">UNIVERSITAS PANCASILA</span>
        </div>
    </div>
    <nav aria-label="Navigasi utama">
        <a href="../index.php">← Beranda</a>
        <a href="dashboard_admin.php">Dashboard Operasional</a>
        <a href="laporan.php" style="color:var(--up-blue);font-weight:700;">Laporan Parkir</a>
    </nav>
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
                <a href="dashboard_admin.php" class="button button-light">← Dashboard Operasional</a>
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
            <thead><tr><th>No.</th><th>Plat Nomor</th><th>Tipe</th><th>Waktu Keluar (Lokal)</th><th>Durasi</th><th>Biaya</th><th>Status</th></tr></thead>
            <tbody>
            <?php if (count($dataLaporan) === 0): ?>
                <tr><td colspan="7" style="text-align:center;padding:24px;">Tidak ada data untuk filter ini.</td></tr>
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
        <div class="row"><span>Total kendaraan selesai </span><strong><?= $stat['keluar'] ?> kendaraan</strong></div>
        <div class="row"><span>Total pendapatan kendaraan keluar </span><strong><?= formatRupiah($stat['pendapatan']) ?></strong></div>
        <p class="muted" style="font-size:12px;line-height:1.6;margin-top:15px;">Durasi dihitung otomatis dari waktu masuk hingga kendaraan keluar. Setiap bagian jam yang sudah dimulai dihitung sebagai satu jam.</p>
    </section>
</main>
<footer class="footer">PARKIRKU &middot; Universitas Pancasila &middot; PBW Kelompok 5</footer>
</body>
</html>
