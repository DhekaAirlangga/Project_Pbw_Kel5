<?php
// ==========================================
// functions.php
// Logika procedural Sistem Parkir Kendaraan
// Penanggung jawab: PHP Developer A
// Materi: function, variabel, operator, string
// ==========================================

// --- (P3) ----
const TARIF_PER_JAM = [
    "Motor" => 2000,
    "Mobil" => 5000,
];
const BATAS_DISKON_JAM = 10;   // durasi minimal untuk dapat diskon
const PERSEN_DISKON    = 10;   // diskon 10%
const DURASI_MAKSIMAL  = 24;   // batas wajar durasi parkir (jam)

// ---------- FUNCTION 1: tarif per jam ----------
// Mengambil tarif berdasarkan tipe kendaraan.
// Operator null coalescing (??) -> 0 jika tipe tidak dikenal.
function ambilTarif(string $tipe): int
{
    return TARIF_PER_JAM[$tipe] ?? 0;
}

// ---------- FUNCTION 2: hitung biaya parkir ----------
// Operator: aritmatika (*, /, -), perbandingan (>=),
// dan ternary (?:).
function hitungBiayaParkir(string $tipe, int $durasi): int
{
    $tarif = ambilTarif($tipe);
    $total = $tarif * $durasi;

    // Diskon 10% jika parkir lama
    $diskon = ($durasi >= BATAS_DISKON_JAM)
        ? $total * PERSEN_DISKON / 100
        : 0;

    return (int) round($total - $diskon);
}

// ---------- FUNCTION 3: normalisasi plat nomor ----------
// Teknik string: trim, strtoupper, preg_replace, preg_match.
// "  b1234abc " -> "B 1234 ABC"
function normalisasiPlat(string $plat): string
{
    $bersih = strtoupper(trim($plat));                // huruf besar, buang spasi tepi
    $bersih = preg_replace('/[^A-Z0-9]/', '', $bersih); // buang spasi/simbol

    // Pecah menjadi: kode wilayah, angka, huruf akhir
    if (preg_match('/^([A-Z]{1,2})(\d{1,4})([A-Z]{1,3})$/', $bersih, $m)) {
        return $m[1] . " " . $m[2] . " " . $m[3];
    }

    return $bersih; // format tidak dikenal, kembalikan apa adanya
}

// ---------- FUNCTION 4: validasi plat ----------
// Operator logika (&&) dan fungsi string strlen().
function validasiPlat(string $plat): bool
{
    $panjangOk = strlen($plat) >= 4 && strlen($plat) <= 11;
    $formatOk  = preg_match('/^[A-Z]{1,2} \d{1,4} [A-Z]{1,3}$/', $plat) === 1;

    return $panjangOk && $formatOk;
}

// ---------- FUNCTION 5: validasi durasi ----------
// Input form selalu berupa string -> dikonversi ke int (P3: konversi).
function validasiDurasi($durasi): bool
{
    if (!is_numeric($durasi)) {
        return false;
    }

    $jam = (int) $durasi;
    return $jam >= 1 && $jam <= DURASI_MAKSIMAL;
}

// ---------- FUNCTION 6: validasi tipe ----------
function validasiTipe(string $tipe): bool
{
    return array_key_exists($tipe, TARIF_PER_JAM);
}

// ---------- FUNCTION 7: format tampilan ----------
function formatRupiah(int $angka): string
{
    return "Rp " . number_format($angka, 0, ",", ".");
}

function formatDurasi(int $jam): string
{
    return $jam . " jam";
}

// ---------- FUNCTION 8: statistik dashboard ----------
// $data = array kendaraan (key: plat, tipe, durasi, biaya, status)
// Tanpa loop manual: pakai array_filter, array_column, array_sum.
function hitungStatistik(array $data): array
{
    $sedangParkir = array_filter($data, fn($k) => $k["status"] === "Parkir");
    $selesai      = array_filter($data, fn($k) => $k["status"] === "Keluar");

    return [
        "total"        => count($data),
        "sedangParkir" => count($sedangParkir),
        "pendapatan"   => array_sum(array_column($selesai, "biaya")),
    ];
}

// ---------- FUNCTION 9: data contoh awal ----------
// Dipakai saat belum ada kendaraan yang diinput.
// Biaya dihitung oleh hitungBiayaParkir(), bukan ditulis manual.
function dataContoh(): array
{
    return [
        ["plat" => "B 1234 ABC", "tipe" => "Mobil", "masuk" => "08:00", "durasi" => 3,
         "biaya" => hitungBiayaParkir("Mobil", 3), "status" => "Parkir"],
        ["plat" => "F 5678 XYZ", "tipe" => "Motor", "masuk" => "09:15", "durasi" => 2,
         "biaya" => hitungBiayaParkir("Motor", 2), "status" => "Parkir"],
        ["plat" => "B 9012 DEF", "tipe" => "Mobil", "masuk" => "07:30", "durasi" => 4,
         "biaya" => hitungBiayaParkir("Mobil", 4), "status" => "Keluar"],
    ];
}