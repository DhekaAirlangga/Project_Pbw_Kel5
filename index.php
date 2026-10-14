<?php
// ==========================================
// SISTEM PARKIR KENDARAAN
// ==========================================

session_start();
require_once "functions.php";

$dataKendaraan = normalisasiDataKendaraan($_SESSION["kendaraan"] ?? dataContoh());
$slotParkir = hitungSlotParkir($dataKendaraan);

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

        .capacity-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
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

        .slot-card h2 {
            font-size: 22px;
            color: #1d4ed8;
        }

        .slot-card.is-full h2 {
            color: #b91c1c;
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

        .button-danger {
            background: #dc2626;
        }

        .button-danger:hover {
            background: #b91c1c;
        }

        .form-hint {
            color: #64748b;
            font-size: 13px;
            margin-top: 8px;
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

            .capacity-grid {
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
            <a href="laporan.php">Laporan</a>
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
            </div>

            <div class="stat-card">
                <p>Kendaraan Sedang Parkir</p>
                <h2><?= $stat["sedangParkir"] ?></h2>
            </div>

            <div class="stat-card">
                <p>Total Pendapatan</p>
                <h2><?= formatRupiah($stat["pendapatan"]) ?></h2>
            </div>
        </section>

        <section class="capacity-grid" aria-label="Kapasitas slot parkir">
            <?php foreach ($slotParkir as $tipeSlot => $rincianSlot): ?>
                <div class="stat-card slot-card <?= $rincianSlot["penuh"] ? "is-full" : "" ?>">
                    <p>Slot <?= htmlspecialchars($tipeSlot) ?></p>
                    <h2>
                        <?= $rincianSlot["penuh"]
                            ? "Penuh"
                            : "Tersisa: " . $rincianSlot["tersisa"] ?>
                    </h2>
                    <p>
                        Terisi <?= $rincianSlot["terisi"] ?> dari
                        <?= $rincianSlot["kapasitas"] ?> slot
                    </p>
                </div>
            <?php endforeach; ?>
        </section>

        <!-- FORM INPUT KENDARAAN -->
        <section class="panel" id="input-kendaraan">
            <h2>Input Kendaraan</h2>

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
                            <?php foreach ($slotParkir as $tipeSlot => $rincianSlot): ?>
                                <option
                                    value="<?= htmlspecialchars($tipeSlot) ?>"
                                    <?= $rincianSlot["penuh"] ? "disabled" : "" ?>
                                >
                                    <?= htmlspecialchars($tipeSlot) ?>
                                    <?= $rincianSlot["penuh"] ? "(Penuh)" : "" ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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
                        <p class="form-hint">
                            Waktu masuk dicatat otomatis saat kendaraan ditambahkan.
                            Durasi dan biaya final dihitung saat kendaraan keluar.
                        </p>
                        <p class="form-hint" id="slot-warning" aria-live="polite"></p>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="button" id="tambah-kendaraan">
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
                            <th>Jam Keluar</th>
                            <th>Durasi</th>
                            <th>Biaya</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $i = 0; ?>
                        <?php while ($i < count($dataKendaraan)): ?>
                            <?php $kendaraan = $dataKendaraan[$i]; ?>
                            <?php
                            $kendaraanKeluar = $kendaraan["status"] === "Keluar";
                            $menitBerjalan = $kendaraanKeluar
                                ? $kendaraan["durasiMenit"]
                                : hitungDurasiMenit($kendaraan["masukAt"]);
                            $biayaSaatIni = $kendaraanKeluar
                                ? $kendaraan["biaya"]
                                : hitungBiayaParkirMenit($kendaraan["tipe"], $menitBerjalan);
                            ?>
                            <tr
                                class="kendaraan-row"
                                data-masuk-at="<?= htmlspecialchars($kendaraan["masukAt"]) ?>"
                                data-tipe="<?= htmlspecialchars($kendaraan["tipe"]) ?>"
                                data-selesai="<?= $kendaraanKeluar ? "1" : "0" ?>"
                            >
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <?= htmlspecialchars($kendaraan["plat"]) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($kendaraan["tipe"]) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars(formatTimestamp($kendaraan["masukAt"])) ?>
                                </td>
                                <td>
                                    <?= $kendaraanKeluar && $kendaraan["keluarAt"] !== null
                                        ? htmlspecialchars(formatTimestamp($kendaraan["keluarAt"]))
                                        : "—" ?>
                                </td>
                                <td>
                                    <span class="durasi-value"><?= htmlspecialchars(formatDurasiMenit($menitBerjalan)) ?></span>
                                </td>
                                <td>
                                    <span class="biaya-value"><?= formatRupiah($biayaSaatIni) ?></span>
                                </td>
                                <td>
                                    <span class="status <?= $kendaraan["status"] === "Parkir"
                                        ? "status-parkir"
                                        : "status-keluar" ?>">
                                        <?= htmlspecialchars($kendaraan["status"]) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($kendaraan["status"] === "Parkir"): ?>
                                        <form action="proses.php" method="post">
                                            <input type="hidden" name="aksi" value="keluar">
                                            <input type="hidden" name="indeks" value="<?= $i ?>">
                                            <button type="submit" class="button button-danger">
                                                Keluar
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span>
                                            <?= isset($kendaraan["keluar"])
                                                ? "Keluar " . htmlspecialchars($kendaraan["keluar"])
                                                : "Selesai" ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php $i++; ?>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- LAPORAN -->
        <section class="panel" id="laporan">
            <h2>Laporan Parkir</h2>

            <p style="color: #64748b; line-height: 1.8;">
                Lihat rincian kendaraan, status parkir, durasi, dan total
                pendapatan pada halaman laporan.
            </p>
            <div class="form-actions">
                <a href="laporan.php" class="button">
                    Lihat Data Kendaraan
                </a>

                <a href="laporan.php" class="button button-secondary">
                    Buka Laporan
                </a>
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
        const slotTersisa = <?= json_encode(
            array_map(fn($rincian) => $rincian["tersisa"], $slotParkir),
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        ) ?>;
        const batasDiskonJam = <?= BATAS_DISKON_JAM ?>;
        const persenDiskon = <?= PERSEN_DISKON ?>;
        const pilihanTipe = document.getElementById("tipe");
        const inputTarif = document.getElementById("tarif");
        const tombolTambah = document.getElementById("tambah-kendaraan");
        const peringatanSlot = document.getElementById("slot-warning");

        function perbaruiPilihanSlot() {
            const tipeTerpilih = pilihanTipe.value;
            inputTarif.value = tarifPerJam[tipeTerpilih] ?? "";

            if (tipeTerpilih && slotTersisa[tipeTerpilih] === 0) {
                peringatanSlot.textContent = `Slot ${tipeTerpilih} penuh. Kendaraan tidak dapat ditambahkan.`;
                tombolTambah.disabled = true;
            } else {
                peringatanSlot.textContent = tipeTerpilih
                    ? `Slot ${tipeTerpilih} tersisa: ${slotTersisa[tipeTerpilih]}.`
                    : "Pilih tipe kendaraan untuk melihat slot tersedia.";
                tombolTambah.disabled = !tipeTerpilih;
            }
        }

        pilihanTipe.addEventListener("change", perbaruiPilihanSlot);
        perbaruiPilihanSlot();

        const formatRupiah = new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            maximumFractionDigits: 0
        });

        function perbaruiDurasiParkir() {
            document.querySelectorAll(".kendaraan-row[data-selesai='0']").forEach((baris) => {
                const masukMs = new Date(baris.dataset.masukAt).getTime();
                const durasiMenit = Math.max(0, Math.ceil((Date.now() - masukMs) / 60000));
                const jam = Math.floor(durasiMenit / 60);
                const menit = durasiMenit % 60;
                const durasiText = jam === 0
                    ? `${menit} menit`
                    : `${jam} jam${menit > 0 ? ` ${menit} menit` : ""}`;
                const jamTagihan = Math.max(1, Math.ceil(durasiMenit / 60));
                const total = jamTagihan * tarifPerJam[baris.dataset.tipe];
                const biaya = jamTagihan >= batasDiskonJam
                    ? Math.round(total * (100 - persenDiskon) / 100)
                    : total;

                baris.querySelector(".durasi-value").textContent = durasiText;
                baris.querySelector(".biaya-value").textContent = formatRupiah.format(biaya);
            });
        }

        perbaruiDurasiParkir();
        window.setInterval(perbaruiDurasiParkir, 30000);
    </script>

</body>
</html>