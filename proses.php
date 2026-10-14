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

// Proses kendaraan keluar tanpa mengirim ulang form kendaraan masuk.
if (($_POST["aksi"] ?? "") === "keluar") {
    $indeks = filter_var($_POST["indeks"] ?? null, FILTER_VALIDATE_INT);
    $data = normalisasiDataKendaraan($_SESSION["kendaraan"] ?? dataContoh());

    if ($indeks === false || $indeks === null || !isset($data[$indeks])) {
        $_SESSION["error"] = ["Data kendaraan yang akan dikeluarkan tidak ditemukan."];
        header("Location: index.php#data-parkir");
        exit;
    }

    if ($data[$indeks]["status"] !== "Parkir") {
        $_SESSION["error"] = ["Kendaraan tersebut sudah keluar."];
        header("Location: index.php#data-parkir");
        exit;
    }

    $kendaraan = new Kendaraan(
        $data[$indeks]["plat"],
        $data[$indeks]["tipe"],
        $data[$indeks]["masukAt"]
    );
    $kendaraan->selesaikan();
    $data[$indeks] = $kendaraan->toArray();
    $_SESSION["kendaraan"] = $data;
    $_SESSION["sukses"] = "Kendaraan " . $kendaraan->plat
                        . " berhasil keluar setelah "
                        . formatDurasiMenit($kendaraan->durasiMenit)
                        . ". Total biaya: " . formatRupiah($kendaraan->biaya) . ".";

    header("Location: index.php#data-parkir");
    exit;
}

// 1. Ambil data dari form ke variabel
$platMentah   = $_POST["plat"] ?? "";
$tipe         = trim($_POST["tipe"] ?? "");

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
// 4. Cek plat yang sama masih berstatus Parkir
$data = normalisasiDataKendaraan($_SESSION["kendaraan"] ?? dataContoh());
$slotParkir = hitungSlotParkir($data);

if (validasiTipe($tipe) && $slotParkir[$tipe]["penuh"]) {
    $error[] = "Slot parkir untuk " . $tipe . " sudah penuh ("
             . $slotParkir[$tipe]["terisi"] . "/"
             . $slotParkir[$tipe]["kapasitas"] . "). Kendaraan tidak dapat ditambahkan.";
}

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

// Waktu masuk dicatat oleh server agar tidak dapat dimanipulasi dari browser.
$kendaraan = new Kendaraan($plat, $tipe);
$data[] = $kendaraan->toArray();

$_SESSION["kendaraan"] = $data;
$_SESSION["sukses"]    = "Kendaraan " . $plat . " berhasil ditambahkan. "
                       . "Waktu masuk: " . formatTimestamp($kendaraan->masukAt) . ".";

header("Location: index.php#data-parkir");
exit;