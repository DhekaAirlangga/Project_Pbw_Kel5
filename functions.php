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
const KAPASITAS_PARKIR = [
    "Motor" => 50,
    "Mobil" => 20,
];
const BATAS_DISKON_JAM = 10;   // durasi minimal untuk dapat diskon
const PERSEN_DISKON    = 10;   // diskon 10%
const DURASI_MAKSIMAL  = 24;   // batas wajar durasi parkir (jam)
const ZONA_WAKTU       = "Asia/Jakarta";

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

function waktuSekarang(): DateTimeImmutable
{
    return new DateTimeImmutable("now", new DateTimeZone(ZONA_WAKTU));
}

function formatTimestamp(string $timestamp): string
{
    return (new DateTimeImmutable($timestamp))
        ->setTimezone(new DateTimeZone(ZONA_WAKTU))
        ->format("d/m/Y H:i:s");
}

function hitungDurasiMenit(string $masukAt, ?string $keluarAt = null): int
{
    $mulai = new DateTimeImmutable($masukAt);
    $selesai = $keluarAt === null
        ? waktuSekarang()
        : new DateTimeImmutable($keluarAt);
    $detik = max(0, $selesai->getTimestamp() - $mulai->getTimestamp());

    return (int) ceil($detik / 60);
}

function hitungJamTagihan(int $durasiMenit): int
{
    return max(1, (int) ceil(max(0, $durasiMenit) / 60));
}

function hitungBiayaParkirMenit(string $tipe, int $durasiMenit): int
{
    return hitungBiayaParkir($tipe, hitungJamTagihan($durasiMenit));
}

function formatDurasiMenit(int $durasiMenit): string
{
    $jam = intdiv($durasiMenit, 60);
    $menit = $durasiMenit % 60;

    if ($jam === 0) {
        return $menit . " menit";
    }

    return $jam . " jam" . ($menit > 0 ? " " . $menit . " menit" : "");
}

function normalisasiDataKendaraan(array $data): array
{
    foreach ($data as &$kendaraan) {
        if (!isset($kendaraan["masukAt"])) {
            // Data sesi lama hanya menyimpan durasi manual; pertahankan sebagai perkiraan.
            $jamLama = max(1, (int) ($kendaraan["durasi"] ?? 1));
            $kendaraan["masukAt"] = waktuSekarang()
                ->modify("-" . $jamLama . " hours")
                ->format(DateTimeInterface::ATOM);
        }

        $kendaraan["status"] = $kendaraan["status"] ?? "Parkir";
        $kendaraan["keluarAt"] = $kendaraan["keluarAt"] ?? null;

        if ($kendaraan["status"] === "Keluar" && $kendaraan["keluarAt"] === null) {
            $durasiLama = max(1, (int) ($kendaraan["durasi"] ?? 1));
            $kendaraan["keluarAt"] = (new DateTimeImmutable($kendaraan["masukAt"]))
                ->modify("+" . $durasiLama . " hours")
                ->format(DateTimeInterface::ATOM);
        }

        $kendaraan["durasiMenit"] = hitungDurasiMenit(
            $kendaraan["masukAt"],
            $kendaraan["status"] === "Keluar" ? $kendaraan["keluarAt"] : null
        );
        $kendaraan["jamTagihan"] = hitungJamTagihan($kendaraan["durasiMenit"]);
        $kendaraan["biaya"] = $kendaraan["status"] === "Keluar"
            ? hitungBiayaParkirMenit($kendaraan["tipe"], $kendaraan["durasiMenit"])
            : 0;
    }
    unset($kendaraan);

    return array_values($data);
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

function hitungSlotParkir(array $data): array
{
    $slot = [];
    foreach (KAPASITAS_PARKIR as $tipe => $kapasitas) {
        $slot[$tipe] = [
            "kapasitas" => $kapasitas,
            "terisi" => 0,
            "tersisa" => $kapasitas,
            "penuh" => false,
        ];
    }

    foreach ($data as $kendaraan) {
        $tipe = $kendaraan["tipe"] ?? "";
        if (($kendaraan["status"] ?? "") === "Parkir" && isset($slot[$tipe])) {
            $slot[$tipe]["terisi"]++;
        }
    }

    foreach ($slot as &$rincian) {
        $rincian["tersisa"] = max(0, $rincian["kapasitas"] - $rincian["terisi"]);
        $rincian["penuh"] = $rincian["tersisa"] === 0;
    }
    unset($rincian);

    return $slot;
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

// ---------- CLASS KENDARAAN ----------
class Kendaraan
{
    public string $plat;
    public string $tipe;
    public string $masukAt;
    public ?string $keluarAt;
    public int $durasiMenit;
    public int $jamTagihan;
    public int $biaya;
    public string $status;

    public function __construct(
        string $plat,
        string $tipe,
        ?string $masukAt = null
    ) {
        $this->plat = normalisasiPlat($plat);
        $this->tipe = $tipe;
        $this->masukAt = $masukAt ?? waktuSekarang()->format(DateTimeInterface::ATOM);
        $this->keluarAt = null;
        $this->durasiMenit = 0;
        $this->jamTagihan = 0;
        $this->biaya = 0;
        $this->status = "Parkir";
    }

    public function selesaikan(?string $keluarAt = null): void
    {
        $this->keluarAt = $keluarAt ?? waktuSekarang()->format(DateTimeInterface::ATOM);
        $this->durasiMenit = hitungDurasiMenit($this->masukAt, $this->keluarAt);
        $this->jamTagihan = hitungJamTagihan($this->durasiMenit);
        $this->biaya = hitungBiayaParkirMenit($this->tipe, $this->durasiMenit);
        $this->status = "Keluar";
    }

    public function toArray(): array
    {
        return [
            "plat" => $this->plat,
            "tipe" => $this->tipe,
            "masukAt" => $this->masukAt,
            "keluarAt" => $this->keluarAt,
            "durasiMenit" => $this->durasiMenit,
            "jamTagihan" => $this->jamTagihan,
            "biaya" => $this->biaya,
            "status" => $this->status,
        ];
    }
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
    $sekarang = waktuSekarang();
    $mobilParkir = new Kendaraan(
        "B 1234 ABC",
        "Mobil",
        $sekarang->modify("-3 hours")->format(DateTimeInterface::ATOM)
    );
    $motorParkir = new Kendaraan(
        "F 5678 XYZ",
        "Motor",
        $sekarang->modify("-2 hours -15 minutes")->format(DateTimeInterface::ATOM)
    );
    $mobilKeluar = new Kendaraan(
        "B 9012 DEF",
        "Mobil",
        $sekarang->modify("-5 hours")->format(DateTimeInterface::ATOM)
    );
    $mobilKeluar->selesaikan($sekarang->modify("-45 minutes")->format(DateTimeInterface::ATOM));

    return [
        $mobilParkir->toArray(),
        $motorParkir->toArray(),
        $mobilKeluar->toArray(),
    ];
}