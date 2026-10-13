<?php
// tes_functions.php
// Buka lewat browser: http://localhost/project_parkir/tes_functions.php
// Berguna untuk Tester & sebagai bukti screenshot.
require_once "functions.php";

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
</body>
</html>
