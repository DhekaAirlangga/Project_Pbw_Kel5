<?php
session_start();
require_once __DIR__ . '/functions.php';

$dataKendaraan = $_SESSION['kendaraan'] ?? dataContoh();
$waktuSekarang = time();

// Tampilkan durasi dan estimasi biaya berjalan untuk kendaraan yang masih parkir.
for ($i = 0; $i < count($dataKendaraan); $i++) {
    if ($dataKendaraan[$i]['status'] === 'Parkir' && isset($dataKendaraan[$i]['masukEpoch'])) {
        $dataKendaraan[$i]['durasi'] = hitungDurasiParkir(
            (int) $dataKendaraan[$i]['masukEpoch'],
            $waktuSekarang
        );
    }
    $dataKendaraan[$i]['biaya'] = hitungBiayaParkir(
        $dataKendaraan[$i]['tipe'],
        (int) $dataKendaraan[$i]['durasi']
    );
}

$stat = hitungStatistik($dataKendaraan);
$ketersediaan = hitungKetersediaanParkir($dataKendaraan);
$pesanError = $_SESSION['error'] ?? [];
$pesanSukses = $_SESSION['sukses'] ?? '';
unset($_SESSION['error'], $_SESSION['sukses']);

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PARKIRKU | Sistem Parkir Kendaraan</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="navbar">
    <div class="brand">PARKIR<span>KU</span></div>
    <nav aria-label="Navigasi utama">
        <a href="#dashboard">Dashboard</a>
        <a href="#input-kendaraan">Input Kendaraan</a>
        <a href="#data-parkir">Data Parkir</a>
        <a href="laporan.php">Laporan</a>
    </nav>
</header>

<main class="container">
    <?php if ($pesanSukses !== ''): ?>
        <div class="alert alert-sukses" role="status"><?= e($pesanSukses) ?></div>
    <?php endif; ?>

    <?php if (count($pesanError) > 0): ?>
        <div class="alert alert-error" role="alert">
            <?php foreach ($pesanError as $pesan): ?>
                <div>• <?= e($pesan) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <section class="header" id="dashboard">
        <div>
            <h1>Sistem Parkir Kendaraan</h1>
            <p>Kelola kendaraan masuk, durasi parkir, dan biaya pembayaran.</p>
        </div>
        <span class="badge">PARKIR / KENDARAAN</span>
    </section>

    <section class="stats" aria-label="Ringkasan parkir">
        <div class="stat-card"><p>Total Kendaraan</p><h2><?= $stat['total'] ?></h2></div>
        <div class="stat-card"><p>Sedang Parkir</p><h2><?= $stat['sedangParkir'] ?></h2></div>
        <div class="stat-card"><p>Pendapatan Kendaraan Keluar</p><h2><?= formatRupiah($stat['pendapatan']) ?></h2></div>
    </section>

    <section class="stats" aria-label="Ketersediaan tempat parkir">
        <div class="stat-card">
            <p>Ketersediaan Parkir Mobil</p>
            <h2><?= $ketersediaan['Mobil']['tersedia'] ?> / <?= $ketersediaan['Mobil']['kapasitas'] ?></h2>
            <p class="muted"><?= $ketersediaan['Mobil']['terpakai'] ?> sedang digunakan</p>
        </div>
        <div class="stat-card">
            <p>Ketersediaan Parkir Motor</p>
            <h2><?= $ketersediaan['Motor']['tersedia'] ?> / <?= $ketersediaan['Motor']['kapasitas'] ?></h2>
            <p class="muted"><?= $ketersediaan['Motor']['terpakai'] ?> sedang digunakan</p>
        </div>
    </section>

    <section class="panel" id="input-kendaraan">
        <h2>Input Kendaraan Masuk</h2>
        <p class="muted" style="margin-top:-10px;margin-bottom:20px;font-size:13px;">Tarif Motor Rp2.000/jam dan Mobil Rp5.000/jam. Diskon 10% berlaku untuk durasi minimal 10 jam.</p>
        <form action="proses.php" method="post" id="form-kendaraan">
            <input type="hidden" name="masuk_epoch" id="masuk-epoch">
            <input type="hidden" name="masuk_lokal" id="masuk-lokal">
            <div class="form-grid">
                <div class="form-group">
                    <label for="plat">Nomor Plat Kendaraan</label>
                    <input type="text" id="plat" name="plat" placeholder="Contoh: B 1234 ABC" maxlength="15" autocomplete="off" required>
                </div>
                <div class="form-group">
                    <label for="tipe">Tipe Kendaraan</label>
                    <select id="tipe" name="tipe" required>
                        <option value="">Pilih tipe kendaraan</option>
                        <option value="Motor">Motor</option>
                        <option value="Mobil">Mobil</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tarif">Tarif per Jam (Rp)</label>
                    <input type="text" id="tarif" placeholder="Pilih tipe kendaraan" readonly aria-readonly="true">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="button">+ Tambah Kendaraan</button>
                <button type="reset" class="button button-secondary" id="reset-form">Reset</button>
            </div>
        </form>
    </section>

    <section class="panel" id="data-parkir">
        <div class="report-top">
            <div><h2 style="margin-bottom:5px;">Data Kendaraan</h2><p class="muted" style="font-size:13px;">Klik “Keluarkan” untuk menyelesaikan transaksi parkir.</p></div>
            <a href="laporan.php" class="button button-light">Buka Laporan</a>
        </div>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>No.</th><th>Nomor Plat</th><th>Tipe</th><th>Tanggal Masuk</th><th>Jam Masuk</th><th>Tanggal Keluar</th><th>Jam Keluar</th><th>Durasi</th><th>Biaya Estimasi</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php if (count($dataKendaraan) === 0): ?>
                    <tr><td colspan="11" style="text-align:center;padding:25px;">Belum ada data kendaraan.</td></tr>
                <?php else: ?>
                    <?php
                    // FOR: menampilkan daftar kendaraan dalam tabel (kompetensi P7).
                    for ($i = 0; $i < count($dataKendaraan); $i++):
                        $kendaraan = $dataKendaraan[$i];
                    ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= e($kendaraan['plat']) ?></td>
                        <td><?= e($kendaraan['tipe']) ?></td>
                        <td><?= e(isset($kendaraan['masuk']) ? substr($kendaraan['masuk'], 0, 10) : '-') ?></td>
                        <td><?= e(isset($kendaraan['masuk']) ? substr($kendaraan['masuk'], 11, 8) : '-') ?></td>
                        <td><?= e(isset($kendaraan['keluar']) ? substr($kendaraan['keluar'], 0, 10) : '-') ?></td>
                        <td><?= e(isset($kendaraan['keluar']) ? substr($kendaraan['keluar'], 11, 8) : '-') ?></td>
                        <td><?= formatDurasi((int) $kendaraan['durasi']) ?></td>
                        <td><?= formatRupiah((int) $kendaraan['biaya']) ?></td>
                        <td><span class="status <?= $kendaraan['status'] === 'Parkir' ? 'status-parkir' : 'status-keluar' ?>"><?= e($kendaraan['status']) ?></span></td>
                        <td>
                            <?php if ($kendaraan['status'] === 'Parkir'): ?>
                                <form action="keluar.php" method="post" onsubmit="return catatWaktuKeluar(this, '<?= e($kendaraan['plat']) ?>');" style="display:inline;">
                                    <input type="hidden" name="plat" value="<?= e($kendaraan['plat']) ?>">
                                    <input type="hidden" name="keluar_epoch">
                                    <input type="hidden" name="keluar_lokal">
                                    <button type="submit" class="button button-danger">Keluarkan</button>
                                </form>
                            <?php else: ?>
                                <span class="muted">Selesai<?= isset($kendaraan['keluar']) ? ' • ' . e($kendaraan['keluar']) : '' ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endfor; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <h2>Laporan Parkir</h2>
        <p class="muted" style="line-height:1.7;margin-bottom:16px;">Lihat rekap kendaraan, status parkir, serta pendapatan kendaraan yang sudah keluar.</p>
        <a href="laporan.php" class="button">Buka Halaman Laporan →</a>
    </section>
</main>

<footer class="footer">PARKIRKU · Pemrograman Berbasis Web · Kelompok 5</footer>
<script>
    const tarifPerJam = {
        Motor: <?= ambilTarif('Motor') ?>,
        Mobil: <?= ambilTarif('Mobil') ?>
    };
    const tipe = document.getElementById('tipe');
    const tarif = document.getElementById('tarif');
    function waktuLaptopSekarang() {
        const sekarang = new Date();
        const duaDigit = nilai => String(nilai).padStart(2, '0');
        const lokal = [
            sekarang.getFullYear(),
            duaDigit(sekarang.getMonth() + 1),
            duaDigit(sekarang.getDate())
        ].join('-') + ' ' + [
            duaDigit(sekarang.getHours()),
            duaDigit(sekarang.getMinutes()),
            duaDigit(sekarang.getSeconds())
        ].join(':');
        return { epoch: Math.floor(sekarang.getTime() / 1000), lokal: lokal };
    }
    document.getElementById('form-kendaraan').addEventListener('submit', function () {
        const waktu = waktuLaptopSekarang();
        document.getElementById('masuk-epoch').value = waktu.epoch;
        document.getElementById('masuk-lokal').value = waktu.lokal;
    });
    function catatWaktuKeluar(form, plat) {
        if (!confirm('Catat kendaraan ' + plat + ' keluar dari parkir?')) {
            return false;
        }
        const waktu = waktuLaptopSekarang();
        form.elements.keluar_epoch.value = waktu.epoch;
        form.elements.keluar_lokal.value = waktu.lokal;
        return true;
    }

    tipe.addEventListener('change', function () {
        tarif.value = Object.prototype.hasOwnProperty.call(tarifPerJam, this.value)
            ? new Intl.NumberFormat('id-ID').format(tarifPerJam[this.value])
            : '';
    });
    document.getElementById('reset-form').addEventListener('click', function () {
        tarif.value = '';
    });
</script>
</body>
</html>
