<?php
// ==========================================
// proses.php
// Menerima data form, merapikan, memvalidasi,
// menghitung biaya, lalu menyimpan ke session.
// Bagian A: variabel, operator, string, function.
// ==========================================
session_start();
require_once "functions.php";

// Hanya terima kiriman dari form (method POST)
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

// 1. Ambil data dari form ke variabel
$platMentah   = $_POST["plat"] ?? "";
$tipe         = trim($_POST["tipe"] ?? "");
$durasiMentah = trim($_POST["durasi"] ?? "");

// 2. Rapikan plat nomor (string)
$plat = normalisasiPlat($platMentah);

// 3. Validasi, kumpulkan pesan error ke array
$error = [];

if (!validasiPlat($plat)) {
    $error[] = "Format plat tidak valid. Contoh: B 1234 ABC.";
}
if (!validasiTipe($tipe)) {
    $error[] = "Tipe kendaraan harus Motor atau Mobil.";
}
if (!validasiDurasi($durasiMentah)) {
    $error[] = "Durasi harus berupa angka 1 sampai " . DURASI_MAKSIMAL . " jam.";
}

// 4. Cek plat yang sama masih berstatus Parkir
$data = $_SESSION["kendaraan"] ?? dataContoh();
$platKembar = array_filter(
    $data,
    fn($k) => $k["plat"] === $plat && $k["status"] === "Parkir"
);
if (count($platKembar) > 0) {
    $error[] = "Kendaraan " . $plat . " masih berstatus Parkir.";
}

// 5. Jika ada error, kembali ke form membawa pesan
if (count($error) > 0) {
    $_SESSION["error"] = $error;
    header("Location: index.php#input-kendaraan");
    exit;
}

// 6. Data valid: konversi, hitung biaya, simpan
$durasi = (int) $durasiMentah;
$biaya  = hitungBiayaParkir($tipe, $durasi);

$data[] = [
    "plat"   => $plat,
    "tipe"   => $tipe,
    "masuk"  => date("H:i"),
    "durasi" => $durasi,
    "biaya"  => $biaya,
    "status" => "Parkir",
];

$_SESSION["kendaraan"] = $data;
$_SESSION["sukses"]    = "Kendaraan " . $plat . " berhasil ditambahkan. "
                       . "Biaya: " . formatRupiah($biaya) . ".";

header("Location: index.php#data-parkir");
exit;