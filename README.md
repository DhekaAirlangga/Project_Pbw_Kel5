# Sistem Parkir Kendaraan (PARKIRKU)
> Tugas Mata Kuliah Pemrograman Berbasis Web (PBW) — Kelompok 5

Aplikasi web sederhana untuk pengelolaan kendaraan masuk, durasi, perhitungan biaya parkir, serta validasi data berbasis PHP Native.

---

## 👥 Anggota Kelompok (Kelompok 5)

1. **Bryan Ananda Saputra Hulu** (4524210020)
2. **Dheka Airlangga** (45242100)
3. **Farhan Ridwan** (4524210037)
4. **Muhammad Bachtiar** (4524210141)

---

## 🚀 Panduan Menjalankan Proyek

### 1. Prasyarat
* PHP versi 7.4 ke atas (Disarankan PHP 8.x).
* Web browser (Google Chrome, Microsoft Edge, Mozilla Firefox, dll.).

### 2. Cara Menjalankan via Terminal / CLI
Buka terminal atau PowerShell di dalam folder proyek ini, lalu jalankan:
```bash
php -S localhost:8000
```
Buka browser dan akses alamat:
```
http://localhost:8000
```

### 3. Cara Menjalankan via XAMPP
1. Pindahkan atau salin folder proyek ini ke dalam folder `htdocs` XAMPP:
   `C:\xampp\htdocs\Project_Pbw_Kel5`
2. Jalankan Apache di panel kontrol XAMPP.
3. Buka browser dan akses:
   `http://localhost/Project_Pbw_Kel5/index.php`

---

## 📂 Struktur Logika PHP Utama (`index.php`)

| Komponen | Nama Fungsi / Class | Penjelasan |
| :--- | :--- | :--- |
| **Kalkulasi Biaya** | `hitungBiayaParkir($tipe, $durasi)` | Menghitung total biaya: Mobil (Rp 5.000/jam), Motor (Rp 2.000/jam). Minimal durasi 1 jam. |
| **Sanitasi Plat** | `normalisasiPlatNomor($plat)` | Membersihkan spasi berlebih, mengubah ke huruf besar, dan menyeragamkan spasi pola plat nomor (contoh: `b 1234 abc` $\rightarrow$ `B 1234 ABC`). |
| **Validasi Format** | `validasiPlatNomor($plat)` | Menguji plat dengan Regex pola standar Indonesia (1-2 huruf wilayah, 1-4 digit angka, 1-3 huruf seri). Mengembalikan `true`/`false`. |
| **OOP Entitas** | `class Kendaraan` | Representasi data kendaraan. Mengotomatiskan normalisasi plat dan hitung biaya saat inisialisasi objek (`new Kendaraan()`). |
| **Penyimpanan Sesi** | `$_SESSION['dataKendaraan']` | Menyimpan daftar kendaraan aktif dan mempertahankan data baru saat halaman di-*refresh*. |

---
