<?php
// ==========================================
// functions.php
// Logika procedural Sistem Parkir Kendaraan
// Penanggung jawab: PHP Developer A
// Materi: function, variabel, operator, string
// ==========================================
const TARIF_PER_JAM = [
    "Motor" => 2000,
    "Mobil" => 5000,
];
const BATAS_DISKON_JAM = 10;
const PERSEN_DISKON = 10;
const DURASI_MAKSIMAL = 24;

// Mengambil tarif per jam sesuai tipe kendaraan.
function ambilTarif(string $tipe): int
{
    return TARIF_PER_JAM[$tipe] ?? 0;
}

// Menghitung biaya parkir; diskon 10% berlaku mulai 10 jam.
function hitungBiayaParkir(string $tipe, int $durasi): int
{
    $tarif = ambilTarif($tipe);
    $subtotal = $tarif * $durasi;
    $diskon = ($durasi >= BATAS_DISKON_JAM)
        ? $subtotal * PERSEN_DISKON / 100
        : 0;

    return (int) round($subtotal - $diskon);
}

// Normalisasi string plat: trim, uppercase, hapus tanda baca/spasi,
// kemudian bentuk ulang pola wilayah-angka-seri.
function normalisasiPlat(string $plat): string
{
    $bersih = strtoupper(trim($plat));
    $bersih = preg_replace('/[^A-Z0-9]/', '', $bersih);

    if (preg_match('/^([A-Z]{1,2})(\d{1,4})([A-Z]{1,3})$/', $bersih, $m)) {
        return $m[1] . ' ' . $m[2] . ' ' . $m[3];
    }

    return $bersih;
}

function validasiPlat(string $plat): bool
{
    $panjangOk = strlen($plat) >= 4 && strlen($plat) <= 11;
    $formatOk = preg_match('/^[A-Z]{1,2} \d{1,4} [A-Z]{1,3}$/', $plat) === 1;

    return $panjangOk && $formatOk;
}

// Durasi harus berupa bilangan bulat antara 1 dan 24 jam.
function validasiDurasi($durasi): bool
{
    if (filter_var($durasi, FILTER_VALIDATE_INT) === false) {
        return false;
    }

    $jam = (int) $durasi;
    return $jam >= 1 && $jam <= DURASI_MAKSIMAL;
}

function validasiTipe(string $tipe): bool
{
    return array_key_exists($tipe, TARIF_PER_JAM);
}

function formatRupiah(int $angka): string
{
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function formatDurasi(int $jam): string
{
    return $jam . ' jam';
}

// Statistik: pendapatan hanya menghitung kendaraan berstatus Keluar.
function hitungStatistik(array $data): array
{
    $sedangParkir = array_filter($data, fn($k) => ($k['status'] ?? '') === 'Parkir');
    $selesai = array_filter($data, fn($k) => ($k['status'] ?? '') === 'Keluar');

    return [
        'total' => count($data),
        'sedangParkir' => count($sedangParkir),
        'keluar' => count($selesai),
        'pendapatan' => array_sum(array_column($selesai, 'biaya')),
    ];
}

// Data contoh agar halaman tetap memiliki data saat pertama dibuka.
function dataContoh(): array
{
    return [
        [
            'plat' => 'B 1234 ABC', 'tipe' => 'Mobil', 'masuk' => '08:00',
            'durasi' => 3, 'biaya' => hitungBiayaParkir('Mobil', 3), 'status' => 'Parkir'
        ],
        [
            'plat' => 'F 5678 XYZ', 'tipe' => 'Motor', 'masuk' => '09:15',
            'durasi' => 2, 'biaya' => hitungBiayaParkir('Motor', 2), 'status' => 'Parkir'
        ],
        [
            'plat' => 'B 9012 DEF', 'tipe' => 'Mobil', 'masuk' => '07:30',
            'durasi' => 4, 'biaya' => hitungBiayaParkir('Mobil', 4), 'status' => 'Keluar', 'keluar' => '11:30'
        ],
    ];
}
