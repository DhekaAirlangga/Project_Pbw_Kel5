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
const KAPASITAS_PARKIR = [
    'Mobil' => 75,
    'Motor' => 150,
];

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

// Setiap bagian jam yang sudah dimulai dihitung sebagai satu jam parkir.
function hitungDurasiParkir(int $masukEpoch, int $keluarEpoch): int
{
    return max(1, (int) ceil(max(0, $keluarEpoch - $masukEpoch) / 3600));
}

function hitungKetersediaanParkir(array $data): array
{
    $ketersediaan = [];
    foreach (KAPASITAS_PARKIR as $tipe => $kapasitas) {
        $terpakai = count(array_filter($data, fn($kendaraan) =>
            ($kendaraan['status'] ?? '') === 'Parkir' && ($kendaraan['tipe'] ?? '') === $tipe
        ));
        $ketersediaan[$tipe] = [
            'kapasitas' => $kapasitas,
            'terpakai' => $terpakai,
            'tersedia' => max(0, $kapasitas - $terpakai),
        ];
    }

    return $ketersediaan;
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
    $waktuSekarang = time();
    $mobilMasukEpoch = $waktuSekarang - (2 * 3600 + 30 * 60);
    $motorMasukEpoch = $waktuSekarang - (1 * 3600 + 30 * 60);
    $mobilKeluarMasukEpoch = $waktuSekarang - (3 * 3600 + 30 * 60);

    return [
        [
            'plat' => 'B 1234 ABC', 'tipe' => 'Mobil',
            'masuk' => date('Y-m-d H:i:s', $mobilMasukEpoch),
            'masukEpoch' => $mobilMasukEpoch,
            'durasi' => hitungDurasiParkir($mobilMasukEpoch, $waktuSekarang),
            'biaya' => hitungBiayaParkir('Mobil', hitungDurasiParkir($mobilMasukEpoch, $waktuSekarang)),
            'status' => 'Parkir',
            'peran' => 'Dosen/Staf/Mahasiswa'
        ],
        [
            'plat' => 'F 5678 XYZ', 'tipe' => 'Motor',
            'masuk' => date('Y-m-d H:i:s', $motorMasukEpoch),
            'masukEpoch' => $motorMasukEpoch,
            'durasi' => hitungDurasiParkir($motorMasukEpoch, $waktuSekarang),
            'biaya' => hitungBiayaParkir('Motor', hitungDurasiParkir($motorMasukEpoch, $waktuSekarang)),
            'status' => 'Parkir',
            'peran' => 'Dosen/Staf/Mahasiswa'
        ],
        [
            'plat' => 'B 9012 DEF', 'tipe' => 'Mobil',
            'masuk' => date('Y-m-d H:i:s', $mobilKeluarMasukEpoch),
            'masukEpoch' => $mobilKeluarMasukEpoch,
            'durasi' => hitungDurasiParkir($mobilKeluarMasukEpoch, $waktuSekarang),
            'biaya' => hitungBiayaParkir('Mobil', hitungDurasiParkir($mobilKeluarMasukEpoch, $waktuSekarang)),
            'status' => 'Keluar',
            'keluar' => date('Y-m-d H:i:s', $waktuSekarang),
            'keluarEpoch' => $waktuSekarang,
            'peran' => 'Mitra'
        ],
    ];
}
