<?php
session_start();
require_once __DIR__ . '/../tools/functions.php';

// Pastikan hanya admin yang dapat mengakses Dashboard Operasional
if (($_SESSION['auth_role'] ?? '') !== 'admin') {
    $_SESSION['error'] = ['Silakan login terlebih dahulu sebagai Administrator / Petugas Parkir.'];
    header('Location: ../auth/login_admin.php');
    exit;
}

$user = $_SESSION['user_data'] ?? ['nama' => 'Administrator Parkir UP', 'role' => 'Admin'];

$dataKendaraan = $_SESSION['kendaraan'] ?? dataContoh();
$waktuSekarang = time();

// Hitung durasi dan estimasi biaya berjalan untuk kendaraan yang masih parkir
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
    <title>Dashboard Operasional Petugas | Universitas Pancasila</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" type="image/png" href="../assets/images/Logo UP.png">
</head>
<body style="background:#f8fafc;">

<!-- NAVBAR ADMIN -->
<header class="navbar-wrapper">
    <div class="navbar-container">
        <a href="../index.php?beranda=1" class="brand-link">
            <img src="../assets/images/Navbar.png" alt="Universitas Pancasila" class="brand-navbar-img">
            <div class="brand-divider"></div>
            <div class="brand-text-block">
                <span class="brand-text-top">OPERASIONAL GERBANG</span>
                <span class="brand-text-main">UNIVERSITAS PANCASILA</span>
            </div>
        </a>

        <div style="display:flex;align-items:center;gap:12px;">
            <div class="role-badge-pill" style="background:#dbeafe;color:var(--up-blue);border-color:#bfdbfe;">
                <span>🛡️</span>
                <span><?= e($user['nama']) ?></span>
            </div>
            <a href="../index.php?beranda=1" class="btn btn-outline" style="padding:6px 12px;font-size:12.5px;">Beranda Utama</a>
            <a href="laporan.php" class="btn btn-outline" style="padding:6px 12px;font-size:12.5px;">Rekap Laporan</a>
            <a href="../auth/logout.php" class="btn btn-danger" style="padding:6px 12px;font-size:12.5px;">Logout Admin</a>
        </div>
    </div>
</header>

<main class="container-boxed" style="padding:40px 24px;">

    <!-- Flash Alerts -->
    <?php if ($pesanSukses !== ''): ?>
        <div class="alert-box success" role="status">
            <span style="font-size:20px;">✓</span>
            <div><?= e($pesanSukses) ?></div>
        </div>
    <?php endif; ?>

    <?php if (count($pesanError) > 0): ?>
        <div class="alert-box danger" role="alert">
            <span style="font-size:20px;">⚠</span>
            <div>
                <strong>Pemberitahuan:</strong>
                <?php foreach ($pesanError as $err): ?>
                    <div>• <?= e($err) ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Header Section -->
    <div class="portal-top-bar" style="border-bottom:none;margin-bottom:18px;">
        <div class="portal-top-title">
            <h1 style="font-size:26px;font-weight:800;color:var(--up-blue);margin-bottom:4px;">
                Dashboard Operasional Parkir UP
            </h1>
            <p>Pengelolaan barrier gate, pencatatan waktu masuk & keluar, dan pemantauan kapasitas slot parkir.</p>
        </div>
        <div style="display:flex;gap:10px;">
            <a href="laporan.php" class="btn btn-primary">
                <span>📑</span>
                <span>Cetak Rekap Laporan</span>
            </a>
        </div>
    </div>

    <!-- Ringkasan Statistik -->
    <div class="stats-grid">
        <div class="stat-item primary">
            <p>Total Kendaraan Tercatat</p>
            <h3><?= $stat['total'] ?></h3>
        </div>
        <div class="stat-item success">
            <p>Sedang Berada di Kampus</p>
            <h3><?= $stat['sedangParkir'] ?></h3>
        </div>
        <div class="stat-item danger">
            <p>Kendaraan Sudah Keluar</p>
            <h3><?= $stat['keluar'] ?></h3>
        </div>
        <div class="stat-item">
            <p>Total Pendapatan Kas</p>
            <h3 style="color:var(--up-blue);font-size:22px;"><?= formatRupiah($stat['pendapatan']) ?></h3>
        </div>
    </div>

    <!-- Ketersediaan Slot Parkir Mobil & Motor -->
    <div class="capacity-grid">
        <div class="capacity-card">
            <div class="capacity-header">
                <h4>Kapasitas Parkir Mobil Kampus UP</h4>
                <span class="badge-tag" style="background:#e8f1fc;color:var(--up-blue);font-size:13px;">
                    <?= $ketersediaan['Mobil']['tersedia'] ?> / <?= $ketersediaan['Mobil']['kapasitas'] ?> Slot Tersedia
                </span>
            </div>
            <?php
            $persenMobil = ($ketersediaan['Mobil']['kapasitas'] > 0)
                ? round(($ketersediaan['Mobil']['terpakai'] / $ketersediaan['Mobil']['kapasitas']) * 100)
                : 0;
            ?>
            <div class="capacity-bar">
                <div class="capacity-fill" style="width: <?= min(100, $persenMobil) ?>%;"></div>
            </div>
            <div class="capacity-details">
                <span>Terpakai: <?= $ketersediaan['Mobil']['terpakai'] ?> slot (<?= $persenMobil ?>%)</span>
                <span>Sisa: <?= $ketersediaan['Mobil']['tersedia'] ?> slot</span>
            </div>
        </div>

        <div class="capacity-card">
            <div class="capacity-header">
                <h4>Kapasitas Parkir Motor Kampus UP</h4>
                <span class="badge-tag" style="background:#fef3c7;color:#b45309;font-size:13px;">
                    <?= $ketersediaan['Motor']['tersedia'] ?> / <?= $ketersediaan['Motor']['kapasitas'] ?> Slot Tersedia
                </span>
            </div>
            <?php
            $persenMotor = ($ketersediaan['Motor']['kapasitas'] > 0)
                ? round(($ketersediaan['Motor']['terpakai'] / $ketersediaan['Motor']['kapasitas']) * 100)
                : 0;
            ?>
            <div class="capacity-bar">
                <div class="capacity-fill fill-motor" style="width: <?= min(100, $persenMotor) ?>%;"></div>
            </div>
            <div class="capacity-details">
                <span>Terpakai: <?= $ketersediaan['Motor']['terpakai'] ?> slot (<?= $persenMotor ?>%)</span>
                <span>Sisa: <?= $ketersediaan['Motor']['tersedia'] ?> slot</span>
            </div>
        </div>
    </div>

    <!-- Panel Input Kendaraan Masuk -->
    <div class="panel-box" id="input-kendaraan">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
            <div>
                <h3 class="panel-title">Catat Kendaraan Masuk (Barrier Gate)</h3>
                <p style="color:#64748b;font-size:13.5px;">
                    Tarif standar UP: Motor Rp2.000/jam, Mobil Rp5.000/jam. Diskon otomatis 10% jika durasi parkir minimal 10 jam.
                </p>
            </div>
            <span class="badge-tag badge-role">Operator Aktif: <?= e($user['nama']) ?></span>
        </div>

        <form action="../actions/proses.php" method="post" id="form-kendaraan">
            <input type="hidden" name="masuk_epoch" id="masuk-epoch">
            <input type="hidden" name="masuk_lokal" id="masuk-lokal">

            <div class="form-grid">
                <div class="form-group">
                    <label for="plat">Nomor Polisi Kendaraan</label>
                    <input type="text" id="plat" name="plat" placeholder="Contoh: B 1234 UP" maxlength="15" autocomplete="off" required>
                    <span id="preview-plat" style="font-size:12px;color:var(--up-blue);height:16px;"></span>
                </div>

                <div class="form-group">
                    <label for="tipe">Jenis Kendaraan</label>
                    <select id="tipe" name="tipe" required>
                        <option value="">-- Pilih Jenis --</option>
                        <option value="Motor">Motor</option>
                        <option value="Mobil">Mobil</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="peran">Kategori Pengendara</label>
                    <select id="peran" name="peran">
                        <option value="Dosen/Staf/Mahasiswa">Dosen / Staf / Mahasiswa (Civitas UP)</option>
                        <option value="Mitra">Mitra / Tenant Resmi UP</option>
                        <option value="Umum / Tamu">Pengunjung Umum / Tamu</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="tarif">Tarif per Jam</label>
                    <input type="text" id="tarif" placeholder="Otomatis terisi..." readonly aria-readonly="true">
                </div>

                <div class="form-group">
                    <label>Jam Masuk Gerbang (Lokal)</label>
                    <input type="text" id="jam-live" readonly placeholder="Sinkronisasi jam...">
                </div>

                <div class="form-group" style="justify-content:flex-end;">
                    <label style="visibility:hidden;">Aksi</label>
                    <div class="form-actions" style="margin-top:0;">
                        <button type="submit" class="btn btn-primary" style="flex:1;">+ Catat Kendaraan Masuk</button>
                        <button type="reset" class="btn btn-secondary" id="reset-form">Reset</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Panel Data Kendaraan & Transaksi -->
    <div class="panel-box" id="data-parkir">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:12px;">
            <div>
                <h3 class="panel-title">Data Kendaraan di Kawasan Kampus UP</h3>
                <p style="color:#64748b;font-size:13px;">Klik tombol "Keluarkan" untuk menyelesaikan parkir dan menghitung tagihan durasi aktual.</p>
            </div>
            <a href="laporan.php" class="btn btn-outline">Lihat Rekap Lengkap →</a>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nomor Polisi</th>
                        <th>Tipe</th>
                        <th>Kategori</th>
                        <th>Tanggal Masuk</th>
                        <th>Jam Masuk</th>
                        <th>Tanggal Keluar</th>
                        <th>Jam Keluar</th>
                        <th>Durasi</th>
                        <th>Biaya Parkir</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($dataKendaraan) === 0): ?>
                    <tr>
                        <td colspan="12" style="text-align:center;padding:30px;color:#94a3b8;">
                            Belum ada catatan kendaraan di area parkir.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php for ($i = 0; $i < count($dataKendaraan); $i++):
                        $k = $dataKendaraan[$i];
                        $kategoriPeran = $k['peran'] ?? 'Dosen/Staf/Mahasiswa';
                    ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= e($k['plat']) ?></strong></td>
                        <td><?= e($k['tipe']) ?></td>
                        <td>
                            <span class="badge-tag <?= ($kategoriPeran === 'Mitra') ? 'badge-role' : 'badge-tag' ?>" style="background:#f1f5f9;color:#334155;">
                                <?= e($kategoriPeran) ?>
                            </span>
                        </td>
                        <td><?= e(isset($k['masuk']) ? substr($k['masuk'], 0, 10) : '-') ?></td>
                        <td><?= e(isset($k['masuk']) ? substr($k['masuk'], 11, 8) : '-') ?></td>
                        <td><?= e(isset($k['keluar']) ? substr($k['keluar'], 0, 10) : '-') ?></td>
                        <td><?= e(isset($k['keluar']) ? substr($k['keluar'], 11, 8) : '-') ?></td>
                        <td><?= formatDurasi((int) $k['durasi']) ?></td>
                        <td><strong><?= formatRupiah((int) $k['biaya']) ?></strong></td>
                        <td>
                            <span class="badge-tag <?= $k['status'] === 'Parkir' ? 'badge-parkir' : 'badge-keluar' ?>">
                                <?= e($k['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($k['status'] === 'Parkir'): ?>
                                <form action="../actions/keluar.php" method="post" onsubmit="return catatWaktuKeluar(this, '<?= e($k['plat']) ?>');" style="display:inline;">
                                    <input type="hidden" name="plat" value="<?= e($k['plat']) ?>">
                                    <input type="hidden" name="keluar_epoch">
                                    <input type="hidden" name="keluar_lokal">
                                    <button type="submit" class="btn btn-danger">Keluarkan</button>
                                </form>
                            <?php else: ?>
                                <span style="color:#64748b;font-size:12.5px;">Selesai</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endfor; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<footer class="site-footer">
    <div class="footer-bottom" style="margin-top:0;">
        PARKIRKU &copy; <?= date('Y') ?> Universitas Pancasila &middot; PBW Kelompok 5
    </div>
</footer>

<script>
    // Konfigurasi Tarif
    const tarifPerJam = {
        Motor: <?= ambilTarif('Motor') ?>,
        Mobil: <?= ambilTarif('Mobil') ?>
    };

    const tipeSelect = document.getElementById('tipe');
    const tarifInput = document.getElementById('tarif');
    const platInput = document.getElementById('plat');
    const previewPlat = document.getElementById('preview-plat');
    const jamLive = document.getElementById('jam-live');

    // Helper waktu laptop/perangkat
    function waktuLaptopSekarang() {
        const sekarang = new Date();
        const duaDigit = val => String(val).padStart(2, '0');
        const lokal = [
            sekarang.getFullYear(),
            duaDigit(sekarang.getMonth() + 1),
            duaDigit(sekarang.getDate())
        ].join('-') + ' ' + [
            duaDigit(sekarang.getHours()),
            duaDigit(sekarang.getMinutes()),
            duaDigit(sekarang.getSeconds())
        ].join(':');

        return {
            epoch: Math.floor(sekarang.getTime() / 1000),
            lokal: lokal
        };
    }

    // Jam Live
    function updateLiveClock() {
        if (jamLive) {
            const w = waktuLaptopSekarang();
            jamLive.value = w.lokal;
        }
    }
    updateLiveClock();
    setInterval(updateLiveClock, 1000);

    // Form Kendaraan Submit
    const formKendaraan = document.getElementById('form-kendaraan');
    if (formKendaraan) {
        formKendaraan.addEventListener('submit', function () {
            const waktu = waktuLaptopSekarang();
            document.getElementById('masuk-epoch').value = waktu.epoch;
            document.getElementById('masuk-lokal').value = waktu.lokal;
        });
    }

    // Catat Keluar Kendaraan
    function catatWaktuKeluar(form, plat) {
        if (!confirm('Catat kendaraan ' + plat + ' keluar dari area parkir UP?')) {
            return false;
        }
        const waktu = waktuLaptopSekarang();
        form.elements.keluar_epoch.value = waktu.epoch;
        form.elements.keluar_lokal.value = waktu.lokal;
        return true;
    }

    // Otomatis Tarif
    if (tipeSelect && tarifInput) {
        tipeSelect.addEventListener('change', function () {
            if (Object.prototype.hasOwnProperty.call(tarifPerJam, this.value)) {
                tarifInput.value = 'Rp ' + new Intl.NumberFormat('id-ID').format(tarifPerJam[this.value]);
            } else {
                tarifInput.value = '';
            }
        });
    }

    // Normalisasi Plat Real-time
    if (platInput && previewPlat) {
        platInput.addEventListener('input', function () {
            let val = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            const match = val.match(/^([A-Z]{1,2})(\d{1,4})([A-Z]{1,3})$/);
            if (match) {
                previewPlat.textContent = 'Format terdeteksi: ' + match[1] + ' ' + match[2] + ' ' + match[3];
            } else if (val.length >= 3) {
                previewPlat.textContent = 'Mengetik: ' + val;
            } else {
                previewPlat.textContent = '';
            }
        });
    }

    const resetBtn = document.getElementById('reset-form');
    if (resetBtn && tarifInput && previewPlat) {
        resetBtn.addEventListener('click', function () {
            tarifInput.value = '';
            previewPlat.textContent = '';
        });
    }
</script>
</body>
</html>
