<?php
require_once __DIR__ . '/../tools/functions.php';

/** Representasi satu kendaraan dalam simulasi parkir. */
class Kendaraan
{
    private string $plat;
    private string $tipe;
    private int $biaya;
    private string $masuk;
    private int $masukEpoch;
    private string $status;
    private string $peran;

    public function __construct(string $plat, string $tipe, int $masukEpoch, string $masuk, string $peran = 'Dosen/Staf/Mahasiswa')
    {
        $plat = normalisasiPlat($plat);

        if (!validasiPlat($plat)) {
            throw new InvalidArgumentException('Format plat nomor tidak valid.');
        }
        if (!validasiTipe($tipe)) {
            throw new InvalidArgumentException('Tipe kendaraan tidak valid.');
        }
        if ($masukEpoch <= 0 || preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $masuk) !== 1) {
            throw new InvalidArgumentException('Waktu masuk dari perangkat tidak valid.');
        }

        $this->plat = $plat;
        $this->tipe = $tipe;
        $this->biaya = 0;
        $this->masuk = $masuk;
        $this->masukEpoch = $masukEpoch;
        $this->status = 'Parkir';
        $this->peran = $peran;
    }

    public function getPlat(): string { return $this->plat; }
    public function getTipe(): string { return $this->tipe; }
    public function getBiaya(): int { return $this->biaya; }
    public function getPeran(): string { return $this->peran; }

    // Array disimpan ke session agar dapat dipakai halaman-halaman PHP.
    public function toArray(): array
    {
        return [
            'plat' => $this->plat,
            'tipe' => $this->tipe,
            'masuk' => $this->masuk,
            'masukEpoch' => $this->masukEpoch,
            'durasi' => 0,
            'biaya' => $this->biaya,
            'status' => $this->status,
            'peran' => $this->peran,
        ];
    }
}
