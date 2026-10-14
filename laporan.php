<?php
session_start();
require_once "functions.php";

$dataKendaraan = normalisasiDataKendaraan($_SESSION["kendaraan"] ?? dataContoh());
$dataKendaraan = array_values(array_filter(
    $dataKendaraan,
    fn($kendaraan) => $kendaraan["status"] === "Keluar"
));
$jumlahKendaraan = count($dataKendaraan);

$stat = hitungStatistik($dataKendaraan);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Parkir - PARKIRKU</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 24px;
            background: #f4f6fa;
            color: #1e293b;
            font-family: Arial, Helvetica, sans-serif;
        }
        main {
            max-width: 1100px;
            margin: 0 auto;
            padding: 24px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
        }
        h1 { margin-top: 0; }
        .summary {
            display: flex;
            flex-wrap: wrap;
            gap: 24px;
            margin: 20px 0;
            color: #475569;
        }
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; white-space: nowrap; }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
            font-size: 14px;
        }
        th { background: #f8fafc; color: #475569; }
        .actions { display: flex; gap: 10px; margin-top: 22px; }
        .button {
            display: inline-block;
            padding: 11px 16px;
            border: 0;
            border-radius: 6px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }
        .button-secondary { background: #475569; }
        @media print {
            body { padding: 0; background: white; }
            main { max-width: none; border: 0; }
            .actions { display: none; }
        }
    </style>
</head>
<body>
<main>
    <h1>Laporan Parkir Kendaraan</h1>
    <p>Rekap hanya menampilkan kendaraan yang sudah keluar.</p>

    <section class="summary" aria-label="Ringkasan laporan">
        <div>Total transaksi selesai: <strong><?= $jumlahKendaraan ?></strong></div>
        <div>Total pendapatan: <strong><?= formatRupiah($stat["pendapatan"]) ?></strong></div>
    </section>

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
                    <th>Jam Tagihan</th>
                    <th>Biaya</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($jumlahKendaraan === 0): ?>
                    <tr><td colspan="9">Belum ada kendaraan yang keluar.</td></tr>
                <?php else: ?>
                    <?php
                    $i = 0;
                    do {
                        $kendaraan = $dataKendaraan[$i];
                        ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($kendaraan["plat"]) ?></td>
                            <td><?= htmlspecialchars($kendaraan["tipe"]) ?></td>
                            <td><?= htmlspecialchars(formatTimestamp($kendaraan["masukAt"])) ?></td>
                            <td><?= $kendaraan["keluarAt"] !== null
                                ? htmlspecialchars(formatTimestamp($kendaraan["keluarAt"]))
                                : "—" ?></td>
                            <td><?= htmlspecialchars(formatDurasiMenit($kendaraan["durasiMenit"])) ?></td>
                            <td><?= $kendaraan["jamTagihan"] ?> jam</td>
                            <td><?= formatRupiah($kendaraan["biaya"]) ?></td>
                            <td><?= htmlspecialchars($kendaraan["status"]) ?></td>
                        </tr>
                        <?php
                        $i++;
                    } while ($i < $jumlahKendaraan);
                    ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="actions">
        <a class="button button-secondary" href="index.php">Kembali</a>
        <button class="button" type="button" onclick="window.print()">Cetak Laporan</button>
    </div>
</main>
</body>
</html>
