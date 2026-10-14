<?php
// tes_functions.php
// Buka lewat browser: http://localhost/project_parkir/tes_functions.php
// Berguna untuk Tester & sebagai bukti screenshot.
require_once "functions.php";
require_once "classes/Kendaraan.php";

// Demo 5 tipe data (P3)
$demoString = "B 1234 ABC";   // string
$demoInt    = 3;             // integer
$demoFloat  = 2.5;           // float
$demoBool   = true;          // boolean
$demoArray  = ["Motor", "Mobil"]; // array

$kasusUji = [
    ["  b1234abc ", "Mobil", "3"],
    ["f 5678 xyz",  "Motor", "2"],
    ["B9012DEF",    "Mobil", "10"],  // kena diskon
    ["123",         "Truk",  "0"],   // semua tidak valid
];


// Demo DO-WHILE (P6): simulasi pengguna mengulang input durasi sampai valid.
// Pada aplikasi web asli, pengguna mengulang pengisian melalui form setelah pesan validasi.
$percobaanDurasi = [0, 25, 3];
$posisiDurasi = 0;
$durasiDiterima = null;
$hasilPercobaan = [];

do {
    $nilaiDurasi = $percobaanDurasi[$posisiDurasi];
    $valid = validasiDurasi((string) $nilaiDurasi);
    $hasilPercobaan[] = [
        'nilai' => $nilaiDurasi,
        'valid' => $valid,
    ];
    if ($valid) {
        $durasiDiterima = (int) $nilaiDurasi;
        break;
    }
    $posisiDurasi++;
} while ($posisiDurasi < count($percobaanDurasi));

// Demo class/object (P2).
$demoKendaraan = new Kendaraan('b1234abc', 'Motor', time(), date('Y-m-d H:i:s'));

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tes Functions</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px 12px; font-size: 13px; }
        th { background: #f1f5f9; }
        .ok { color: #166534; } .gagal { color: #b91c1c; }
    </style>
</head>
<body>
    <h2>Tes Function (PHP Developer A)</h2>

    <table>
        <tr>
            <th>Input Plat</th><th>Plat Normal</th><th>Plat Valid?</th>
            <th>Tipe</th><th>Tipe Valid?</th>
            <th>Durasi</th><th>Durasi Valid?</th><th>Biaya</th>
        </tr>
        <?php foreach ($kasusUji as [$platMentah, $tipe, $durasi]): ?>
            <?php
            $plat       = normalisasiPlat($platMentah);
            $platValid  = validasiPlat($plat);
            $tipeValid  = validasiTipe($tipe);
            $durasiOk   = validasiDurasi($durasi);
            $biaya      = ($tipeValid && $durasiOk)
                ? formatRupiah(hitungBiayaParkir($tipe, (int) $durasi))
                : "-";
            ?>
            <tr>
                <td>"<?= htmlspecialchars($platMentah) ?>"</td>
                <td><?= htmlspecialchars($plat) ?></td>
                <td class="<?= $platValid ? 'ok' : 'gagal' ?>"><?= $platValid ? "Ya" : "Tidak" ?></td>
                <td><?= htmlspecialchars($tipe) ?></td>
                <td class="<?= $tipeValid ? 'ok' : 'gagal' ?>"><?= $tipeValid ? "Ya" : "Tidak" ?></td>
                <td><?= htmlspecialchars($durasi) ?></td>
                <td class="<?= $durasiOk ? 'ok' : 'gagal' ?>"><?= $durasiOk ? "Ya" : "Tidak" ?></td>
                <td><?= $biaya ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h3>Demo Tipe Data</h3>
    <pre><?php
    var_dump($demoString, $demoInt, $demoFloat, $demoBool, $demoArray);
    ?></pre>

    <h3>Demo DO-WHILE: Input Ulang Durasi</h3>
    <p>Program mencoba durasi contoh secara berurutan sampai menemukan nilai valid.</p>
    <table>
        <tr><th>Percobaan</th><th>Durasi</th><th>Hasil Validasi</th></tr>
        <?php for ($i = 0; $i < count($hasilPercobaan); $i++): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars((string) $hasilPercobaan[$i]['nilai']) ?> jam</td>
                <td class="<?= $hasilPercobaan[$i]['valid'] ? 'ok' : 'gagal' ?>">
                    <?= $hasilPercobaan[$i]['valid'] ? 'Valid' : 'Tidak valid, coba ulang' ?>
                </td>
            </tr>
        <?php endfor; ?>
    </table>
    <p>Durasi valid yang diterima: <strong><?= $durasiDiterima === null ? 'Belum ada' : $durasiDiterima . ' jam' ?></strong></p>

    <h3>Demo Class dan Object Kendaraan</h3>
    <p>Object <?= htmlspecialchars($demoKendaraan->getPlat()) ?> (<?= htmlspecialchars($demoKendaraan->getTipe()) ?>), durasi dihitung saat kendaraan keluar.</p>
</body>
</html>
