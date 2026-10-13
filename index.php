<?php
// ==========================================
// SISTEM PARKIR KENDARAAN
// ==========================================

session_start();
require_once "functions.php";

// TODO: Teman kelompok mengerjakan:
// 1. Function hitungBiayaParkir().
// 2. Class Kendaraan dan object.
// 3. Validasi serta normalisasi plat nomor.
// 4. Proses input dan penyimpanan kendaraan.
// 5. Perulangan WHILE dan DO-WHILE.
// 6. Perulangan FOR untuk laporan kendaraan.
// 7. Perhitungan tarif, durasi, dan total pembayaran.

// Data berikut hanya contoh tampilan.
// TODO: Ganti dengan data kendaraan hasil proses PHP.
$dataKendaraan = $_SESSION["kendaraan"] ?? dataContoh();

// Hitung biaya tiap kendaraan lewat function
// (supaya tetap benar walau data tidak membawa kolom biaya)
foreach ($dataKendaraan as &$k) {
    $k["biaya"] = hitungBiayaParkir($k["tipe"], $k["durasi"]);
}
unset($k);

// Statistik dashboard dihitung dari data (function Developer A)
$stat = hitungStatistik($dataKendaraan);

// Pesan dari proses.php (tampil sekali, lalu dihapus)
$pesanError  = $_SESSION["error"]  ?? [];
$pesanSukses = $_SESSION["sukses"] ?? "";
unset($_SESSION["error"], $_SESSION["sukses"]);
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

        .alert {
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.6;
        }
        .alert-error { background: #fee2e2; color: #991b1b; }
        .alert-sukses { background: #dcfce7; color: #166534; }
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

        <!-- PESAN HASIL PROSES -->
        <?php if ($pesanSukses !== ""): ?>
            <div class="alert alert-sukses">
                <?= htmlspecialchars($pesanSukses) ?>
            </div>
        <?php endif; ?>

        <?php if (count($pesanError) > 0): ?>
            <div class="alert alert-error">
                <?php foreach ($pesanError as $pesan): ?>
                    <div>• <?= htmlspecialchars($pesan) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

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
                <h2><?= $stat["total"] ?></h2>
                <!-- TODO: Tampilkan jumlah kendaraan dari data sebenarnya. -->
            </div>

            <div class="stat-card">
                <p>Kendaraan Sedang Parkir</p>
                <h2><?= $stat["sedangParkir"] ?></h2>
                <!-- TODO: Hitung kendaraan dengan status Parkir. -->
            </div>

            <div class="stat-card">
                <p>Total Pendapatan</p>
                <h2><?= formatRupiah($stat["pendapatan"]) ?></h2>
                <!-- TODO: Hitung pendapatan dari transaksi selesai. -->
            </div>
        </section>

        <!-- FORM INPUT KENDARAAN -->
        <section class="panel" id="input-kendaraan">
            <h2>Input Kendaraan</h2>

            <!-- TODO: Teman menghubungkan form dengan proses
                 input, validasi, dan penyimpanan data. -->
            <form action="proses.php" method="post">

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
                        >
                    </div>

                    <div class="form-group">
                        <label for="tipe">Tipe Kendaraan</label>
                        <select id="tipe" name="tipe" required>
                            <option value="">Pilih kendaraan</option>
                            <option value="Motor">Motor</option>
                            <option value="Mobil">Mobil</option>
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
                        >
                        <!-- TODO: Validasi durasi dan hitung biaya. -->
                    </div>

                    <div class="form-group">
                        <label for="tarif">Tarif per Jam (Rp)</label>
                        <input
                            type="number"
                            id="tarif"
                            name="tarif"
                            placeholder="Diisi berdasarkan tipe kendaraan"
                            readonly
                        >
                        <!-- TODO: Isi tarif sesuai tipe kendaraan. -->
                    </div>
                </div>

                <div class="form-actions">
                    <!-- Tombol belum memproses data.
                         TODO: Hubungkan ke logic teman. -->
                    <button type="submit" class="button">
                        + Tambah Kendaraan
                    </button>

                    <button type="reset"
                            class="button button-secondary">
                        Reset
                    </button>
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
                                    <?= formatDurasi($kendaraan["durasi"]) ?>
                                </td>
                                <td>
                                    <?= formatRupiah($kendaraan["biaya"]) ?>
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

    <!-- Isi kolom tarif otomatis (tarif diambil dari PHP: ambilTarif) -->
    <script>
        const tarifPerJam = {
            Motor: <?= ambilTarif("Motor") ?>,
            Mobil: <?= ambilTarif("Mobil") ?>
        };

        document.getElementById("tipe").addEventListener("change", function () {
            document.getElementById("tarif").value = tarifPerJam[this.value] ?? "";
        });
    </script>

</body>
</html>