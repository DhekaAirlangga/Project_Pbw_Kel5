<?php
require_once __DIR__ . '/../functions.php';

/** Representasi satu kendaraan dalam simulasi parkir. */
class Kendaraan
{
    private string $plat;
    private string $tipe;
    private int $durasi;
    private int $biaya;
    private string $masuk;
    private string $status;

    public function __construct(string $plat, string $tipe, int $durasi)
    {
        $plat = normalisasiPlat($plat);

        if (!validasiPlat($plat)) {
            throw new InvalidArgumentException('Format plat nomor tidak valid.');
        }
        if (!validasiTipe($tipe)) {
            throw new InvalidArgumentException('Tipe kendaraan tidak valid.');
        }
        if (!validasiDurasi($durasi)) {
            throw new InvalidArgumentException('Durasi harus 1 sampai ' . DURASI_MAKSIMAL . ' jam.');
        }

        $this->plat = $plat;
        $this->tipe = $tipe;
        $this->durasi = $durasi;
        $this->biaya = hitungBiayaParkir($tipe, $durasi);
        $this->masuk = date('H:i');
        $this->status = 'Parkir';
    }

    public function getPlat(): string { return $this->plat; }
    public function getTipe(): string { return $this->tipe; }
    public function getDurasi(): int { return $this->durasi; }
    public function getBiaya(): int { return $this->biaya; }

    // Array disimpan ke session agar dapat dipakai halaman-halaman PHP.
    public function toArray(): array
    {
        return [
            'plat' => $this->plat,
            'tipe' => $this->tipe,
            'masuk' => $this->masuk,
            'durasi' => $this->durasi,
            'biaya' => $this->biaya,
            'status' => $this->status,
        ];
    }
}
