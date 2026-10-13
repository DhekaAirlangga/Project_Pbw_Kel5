
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// SISTEM PARKIR KENDARAAN
// ==========================================

// TODO: Teman kelompok mengerjakan:
// 1. Function hitungBiayaParkir().
// 2. Class Kendaraan dan object.
// 3. Validasi serta normalisasi plat nomor.
// 4. Proses input dan penyimpanan kendaraan.
// 5. Perulangan WHILE dan DO-WHILE.
// 6. Perulangan FOR untuk laporan kendaraan.
// 7. Perhitungan tarif, durasi, dan total pembayaran.

// ==========================================
// 1. FUNCTION hitungBiayaParkir()
// ==========================================
/**
 * Menghitung total biaya parkir berdasarkan tipe kendaraan dan durasi (dalam jam).
 * Tarif standar:
 * - Mobil : Rp 5.000 / jam
 * - Motor : Rp 2.000 / jam
 *
 * @param string $tipe Tipe kendaraan ('Mobil' atau 'Motor')
 * @param int|float $durasi Durasi parkir dalam jam
 * @param int|null $tarifKustom Tarif per jam opsional jika diisi manual
 * @return int Total biaya parkir dalam Rupiah
 */
function hitungBiayaParkir($tipe, $durasi, $tarifKustom = null) {
    if ($tarifKustom !== null && is_numeric($tarifKustom) && $tarifKustom > 0) {
        $tarifPerJam = (int)$tarifKustom;
    } else {
        $tipeBersih = ucfirst(strtolower(trim((string)$tipe)));
        switch ($tipeBersih) {
            case 'Mobil':
                $tarifPerJam = 5000;
                break;
            case 'Motor':
                $tarifPerJam = 2000;
                break;
            default:
                $tarifPerJam = 3000; // Tarif default jika jenis kendaraan lain
                break;
        }
    }

    $durasiJam = max(1, (int)$durasi); // Minimal durasi parkir dihitung 1 jam
    return $durasiJam * $tarifPerJam;
}

// ==========================================
// 3. VALIDASI SERTA NORMALISASI PLAT NOMOR
// ==========================================
/**
 * Melakukan normalisasi plat nomor:
 * - Menghilangkan spasi ekstra di depan/belakang dan di tengah
 * - Mengubah semua huruf menjadi huruf kapital
 * - Menyeragamkan format standar spasi: [Kode Wilayah] [Nomor Polisi] [Seri Belakang]
 *   Contoh: "b 1234 abc" -> "B 1234 ABC" atau "b1234abc" -> "B 1234 ABC"
 *
 * @param string $plat Nomor plat kendaraan
 * @return string Plat nomor hasil normalisasi
 */
function normalisasiPlatNomor($plat) {
    if (empty($plat)) {
        return '';
    }

    // Ubah ke huruf besar dan hapus spasi ujung
    $platBersih = strtoupper(trim((string)$plat));

    // Pola umum plat nomor Indonesia: 1-2 huruf wilayah, 1-4 angka, 1-3 huruf seri
    if (preg_match('/^([A-Z]{1,2})\s*([0-9]{1,4})\s*([A-Z]{1,3})$/', $platBersih, $matches)) {
        return $matches[1] . ' ' . $matches[2] . ' ' . $matches[3];
    }

    // Jika variasi lain, bersihkan spasi ganda
    return preg_replace('/\s+/', ' ', $platBersih);
}

/**
 * Memvalidasi apakah nomor plat sesuai format standar Indonesia
 * (1-2 huruf wilayah, 1-4 digit angka, 1-3 huruf seri).
 *
 * @param string $plat Nomor plat kendaraan
 * @return bool True jika format valid, False jika tidak valid
 */
function validasiPlatNomor($plat) {
    $platNormal = normalisasiPlatNomor($plat);
    $pattern = '/^[A-Z]{1,2}\s[0-9]{1,4}\s[A-Z]{1,3}$/';
    return (bool) preg_match($pattern, $platNormal);
}

// ==========================================
// 2. CLASS KENDARAAN DAN OBJECT
// ==========================================
/**
 * Class Kendaraan merepresentasikan entitas objek kendaraan yang parkir.
 */
class Kendaraan {
    private $plat;
    private $tipe;
    private $masuk;
    private $durasi;
    private $biaya;
    private $status;

    /**
     * Constructor untuk inisialisasi object Kendaraan
     *
     * @param string $plat Nomor plat kendaraan
     * @param string $tipe Tipe kendaraan ('Mobil' atau 'Motor')
     * @param string $masuk Waktu masuk (misal '08:00')
     * @param int|string $durasi Durasi parkir dalam jam
     * @param string $status Status parkir ('Parkir' atau 'Keluar')
     */
    public function __construct($plat, $tipe, $masuk, $durasi = 1, $status = "Parkir") {
        // Normalisasi nomor plat secara otomatis saat object dibuat
        $this->plat = normalisasiPlatNomor($plat);
        $this->tipe = ucfirst(strtolower(trim((string)$tipe)));
        $this->masuk = $masuk;
        $this->durasi = (int)$durasi;
        $this->status = $status;
        // Hitung biaya parkir otomatis menggunakan function hitungBiayaParkir()
        $this->biaya = hitungBiayaParkir($this->tipe, $this->durasi);
    }

    // Getter methods
    public function getPlat() {
        return $this->plat;
    }

    public function getTipe() {
        return $this->tipe;
    }

    public function getMasuk() {
        return $this->masuk;
    }

    public function getDurasi() {
        return $this->durasi;
    }

    public function getBiaya() {
        return $this->biaya;
    }

    public function getStatus() {
        return $this->status;
    }

    // Setter methods
    public function setPlat($plat) {
        $this->plat = normalisasiPlatNomor($plat);
    }

    public function setTipe($tipe) {
        $this->tipe = ucfirst(strtolower(trim((string)$tipe)));
        $this->biaya = hitungBiayaParkir($this->tipe, $this->durasi);
    }

    public function setDurasi($durasi) {
        $this->durasi = (int)$durasi;
        $this->biaya = hitungBiayaParkir($this->tipe, $this->durasi);
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    // Validasi plat dari instance
    public function isValidPlat() {
        return validasiPlatNomor($this->plat);
    }

    // Mengonversi object menjadi array asosiatif (sesuai format $dataKendaraan)
    public function toArray() {
        return [
            "plat"   => $this->plat,
            "tipe"   => $this->tipe,
            "masuk"  => $this->masuk,
            "durasi" => $this->durasi . " jam",
            "biaya"  => $this->biaya,
            "status" => $this->status
        ];
    }
}

// ==========================================
// 2. CLASS KENDARAAN, OBJECT & SESI DATA
// ==========================================

// Inisialisasi data awal menggunakan objek Class Kendaraan jika session belum ada
if (!isset($_SESSION['dataKendaraan'])) {
    $kendaraan1 = new Kendaraan("B 1234 ABC", "Mobil", "08:00", 3, "Parkir");
    $kendaraan2 = new Kendaraan("F 5678 XYZ", "Motor", "09:15", 2, "Parkir");
    $kendaraan3 = new Kendaraan("B 9012 DEF", "Mobil", "07:30", 4, "Keluar");

    $_SESSION['dataKendaraan'] = [
        $kendaraan1->toArray(),
        $kendaraan2->toArray(),
        $kendaraan3->toArray()
    ];
}

// Opsi reset data ke kondisi default
if (isset($_GET['reset_data'])) {
    unset($_SESSION['dataKendaraan']);
    header("Location: index.php");
    exit;
}

// ==========================================
// 4. PROSES INPUT, VALIDASI, DAN PENYIMPANAN
// ==========================================
$pesanSukses = '';
$pesanError  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_kendaraan'])) {
    $rawPlat   = trim($_POST['plat'] ?? '');
    $tipe      = trim($_POST['tipe'] ?? '');
    $durasi    = (int)($_POST['durasi'] ?? 1);
    $jamMasuk  = date('H:i');

    // 3. Validasi Plat Nomor
    if (!validasiPlatNomor($rawPlat)) {
        $pesanError = "Plat nomor '" . htmlspecialchars($rawPlat) . "' TIDAK VALID! Format harus sesuai standar Indonesia (Contoh: B 1234 ABC, maksimal 4 digit angka).";
    } elseif (empty($tipe)) {
        $pesanError = "Silakan pilih tipe kendaraan terlebih dahulu.";
    } elseif ($durasi < 1) {
        $pesanError = "Durasi parkir minimal 1 jam.";
    } else {
        // 2 & 3. Buat Object baru dari Class Kendaraan
        // Konstruktor otomatis menormalisasi plat nomor dan menghitung biaya via hitungBiayaParkir()
        $kendaraanBaru = new Kendaraan($rawPlat, $tipe, $jamMasuk, $durasi, "Parkir");

        // Simpan kendaraan ke dalam session
        $_SESSION['dataKendaraan'][] = $kendaraanBaru->toArray();

        $pesanSukses = "Kendaraan berhasil ditambahkan! Plat dinormalisasi menjadi '" . $kendaraanBaru->getPlat() . "' dengan total biaya Rp " . number_format($kendaraanBaru->getBiaya(), 0, ',', '.') . " (" . $kendaraanBaru->getTipe() . ", " . $durasi . " jam).";
    }
}

// Data kendaraan yang akan ditampilkan di tabel
$dataKendaraan = $_SESSION['dataKendaraan'] ?? [];

// Hitung statistik dan total pendapatan secara dinamis
$totalKendaraan = count($dataKendaraan);
$totalSedangParkir = 0;
$totalPendapatan = 0;

foreach ($dataKendaraan as $k) {
    if (isset($k['status']) && $k['status'] === 'Parkir') {
        $totalSedangParkir++;
    }
    $totalPendapatan += (int)($k['biaya'] ?? 0);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Sistem Parkir Kendaraan</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f6fa;
            color: #1e293b;
        }

        .navbar {
            background: #172554;
            color: white;
            padding: 18px 6%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .brand {
            font-size: 20px;
            font-weight: bold;
        }

        .brand span {
            color: #60a5fa;
        }

        .navbar nav {
            display: flex;
            gap: 22px;
            flex-wrap: wrap;
        }

        .navbar nav a {
            color: #dbeafe;
            text-decoration: none;
            font-size: 14px;
        }

        .navbar nav a:hover {
            color: white;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 22px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }

        .header p {
            color: #64748b;
            font-size: 14px;
        }

        .badge {
            background: #dbeafe;
            color: #1d4ed8;
            padding: 9px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 25px;
        }

        .stat-card,
        .panel {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 22px;
        }

        .stat-card p {
            color: #64748b;
            font-size: 13px;
            margin-bottom: 12px;
        }

        .stat-card h2 {
            font-size: 26px;
            color: #1d4ed8;
        }

        .panel {
            margin-bottom: 25px;
        }

        .panel h2 {
            font-size: 19px;
            margin-bottom: 20px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background: white;
            font-size: 14px;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: 2px solid #bfdbfe;
            border-color: #2563eb;
        }

        .button {
            display: inline-block;
            padding: 12px 18px;
            border: none;
            border-radius: 6px;
            background: #2563eb;
            color: white;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
        }

        .button:hover {
            background: #1d4ed8;
        }

        .button-secondary {
            background: #475569;
        }

        .form-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            white-space: nowrap;
        }

        th,
        td {
            text-align: left;
            padding: 14px 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 13px;
        }

        th {
            background: #f8fafc;
            color: #475569;
        }

        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-parkir {
            background: #dcfce7;
            color: #166534;
        }

        .status-keluar {
            background: #e2e8f0;
            color: #475569;
        }

        .status-valid {
            background: #dcfce7;
            color: #166534;
        }

        .status-invalid {
            background: #fee2e2;
            color: #991b1b;
        }

        .demo-subheading {
            font-size: 15px;
            font-weight: bold;
            color: #1e293b;
            margin: 22px 0 10px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .code-tag {
            background: #f1f5f9;
            color: #0f172a;
            padding: 3px 8px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
            border: 1px solid #e2e8f0;
            display: inline-block;
        }

        .footer {
            text-align: center;
            color: #64748b;
            padding: 22px;
            font-size: 12px;
        }

        @media (max-width: 700px) {
            .navbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .header h1 {
                font-size: 23px;
            }
        }
    </style>
</head>

<body>

    <!-- NAVIGASI UTAMA -->
    <header class="navbar">
        <div class="brand">
            PARKIR<span>KU</span>
        </div>

        <nav>
            <a href="#dashboard">Dashboard</a>
            <a href="#input-kendaraan">Input Kendaraan</a>
            <a href="#data-parkir">Data Parkir</a>
            <a href="#laporan">Laporan</a>
        </nav>
    </header>

    <main class="container">

        <!-- DASHBOARD -->
        <section class="header" id="dashboard">
            <div>
                <h1>Sistem Parkir Kendaraan</h1>
                <p>Pengelolaan kendaraan masuk, durasi, dan biaya parkir.</p>
            </div>

            <span class="badge">PARKIR / KENDARAAN</span>
        </section>

        <!-- RINGKASAN -->
        <section class="stats">
            <div class="stat-card">
                <p>Total Kendaraan</p>
                <h2><?= $totalKendaraan ?></h2>
            </div>

            <div class="stat-card">
                <p>Kendaraan Sedang Parkir</p>
                <h2><?= $totalSedangParkir ?></h2>
            </div>

            <div class="stat-card">
                <p>Total Pendapatan</p>
                <h2>Rp <?= number_format($totalPendapatan, 0, ',', '.') ?></h2>
            </div>
        </section>

        <!-- FORM INPUT KENDARAAN -->
        <section class="panel" id="input-kendaraan">
            <h2>Input Kendaraan</h2>

            <?php if (!empty($pesanError)): ?>
                <div style="background: #fee2e2; color: #991b1b; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; border: 1px solid #fecaca; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 18px;">⚠️</span>
                    <div><strong>Gagal Validasi:</strong> <?= htmlspecialchars($pesanError) ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($pesanSukses)): ?>
                <div style="background: #dcfce7; color: #166534; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; border: 1px solid #bbf7d0; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 18px;">✅</span>
                    <div><strong>Berhasil:</strong> <?= htmlspecialchars($pesanSukses) ?></div>
                </div>
            <?php endif; ?>

            <form action="#input-kendaraan" method="post">

                <div class="form-grid">
                    <div class="form-group">
                        <label for="plat">Nomor Plat Kendaraan</label>
                        <input
                            type="text"
                            id="plat"
                            name="plat"
                            placeholder="Contoh: B 1234 ABC"
                            maxlength="15"
                            required
                            value="<?= isset($_POST['plat']) && !empty($pesanError) ? htmlspecialchars($_POST['plat']) : '' ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="tipe">Tipe Kendaraan</label>
                        <select id="tipe" name="tipe" required onchange="updateTarif()">
                            <option value="">Pilih kendaraan</option>
                            <option value="Motor" <?= isset($_POST['tipe']) && $_POST['tipe'] === 'Motor' ? 'selected' : '' ?>>Motor (Rp 2.000 / jam)</option>
                            <option value="Mobil" <?= isset($_POST['tipe']) && $_POST['tipe'] === 'Mobil' ? 'selected' : '' ?>>Mobil (Rp 5.000 / jam)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="durasi">Durasi Parkir (jam)</label>
                        <input
                            type="number"
                            id="durasi"
                            name="durasi"
                            min="1"
                            placeholder="Masukkan durasi"
                            required
                            value="<?= isset($_POST['durasi']) ? (int)$_POST['durasi'] : '1' ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="tarif">Tarif per Jam (Rp)</label>
                        <input
                            type="number"
                            id="tarif"
                            name="tarif"
                            placeholder="Terisi otomatis sesuai tipe kendaraan"
                            readonly
                        >
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="tambah_kendaraan" class="button">
                        + Tambah Kendaraan
                    </button>

                    <button type="reset" class="button button-secondary" onclick="setTimeout(updateTarif, 50)">
                        Reset Form
                    </button>

                    <a href="?reset_data=1" class="button button-secondary" style="margin-left: auto; background: #64748b;" onclick="return confirm('Kembalikan data ke daftar sampel awal?')">
                        ↺ Reset Data Parkir
                    </a>
                </div>
            </form>
        </section>

        <!-- TABEL DATA PARKIR -->
        <section class="panel" id="data-parkir">
            <h2>Data Kendaraan</h2>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nomor Plat</th>
                            <th>Tipe</th>
                            <th>Jam Masuk</th>
                            <th>Durasi</th>
                            <th>Biaya</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        // TODO: Gunakan FOR untuk menampilkan data
                        // kendaraan dari hasil proses yang sebenarnya.
                        ?>

                        <?php foreach ($dataKendaraan as $i => $kendaraan): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <?= htmlspecialchars($kendaraan["plat"]) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($kendaraan["tipe"]) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($kendaraan["masuk"]) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($kendaraan["durasi"]) ?>
                                </td>
                                <td>
                                    Rp <?= number_format(
                                        $kendaraan["biaya"], 0, ',', '.'
                                    ) ?>
                                </td>
                                <td>
                                    <span class="status <?= $kendaraan["status"] === "Parkir"
                                        ? "status-parkir"
                                        : "status-keluar" ?>">
                                        <?= htmlspecialchars($kendaraan["status"]) ?>
                                    </span>
                                </td>
                                <td>
                                    <!-- TODO: Tambahkan aksi keluar kendaraan
                                         dan hitung biaya akhir. -->
                                    <button type="button" class="button">
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- LAPORAN -->
        <section class="panel" id="laporan">
            <h2>Laporan Parkir</h2>

            <p style="color: #64748b; line-height: 1.8;">
                Bagian ini digunakan untuk melihat rekap kendaraan,
                durasi parkir, dan total pembayaran.
            </p>

            <!-- TODO: Teman mengerjakan rekap biaya dan laporan
                 menggunakan perulangan FOR. -->
            <div class="form-actions">
                <a href="#data-parkir" class="button">
                    Lihat Data Kendaraan
                </a>

                <button type="button" class="button button-secondary">
                    Cetak Laporan
                </button>
                <!-- TODO: Hubungkan tombol cetak dengan laporan. -->
            </div>
        </section>

    </main>

    <footer class="footer">
        Sistem Parkir Kendaraan · Pemrograman Berbasis Web · UTS
    </footer>

    <script>
        function updateTarif() {
            var tipeSelect = document.getElementById('tipe');
            var tarifInput = document.getElementById('tarif');
            if (tipeSelect.value === 'Mobil') {
                tarifInput.value = 5000;
            } else if (tipeSelect.value === 'Motor') {
                tarifInput.value = 2000;
            } else {
                tarifInput.value = '';
            }
        }
        document.addEventListener('DOMContentLoaded', updateTarif);
    </script>
</body>
</html>