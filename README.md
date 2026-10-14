# Sistem Parkir Kendaraan (PARKIRKU)
> Tugas Mata Kuliah Pemrograman Berbasis Web (PBW) — Kelompok 5

Aplikasi web sederhana untuk pengelolaan kendaraan masuk, durasi, perhitungan biaya parkir, serta validasi data berbasis PHP Native.

---

## 👥 Anggota Kelompok (Kelompok 5)

1. **Bryan Ananda Saputra Hulu** (4524210020)
2. **Dheka Airlangga** (4524210027)
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

## 📖 Dokumentasi Aplikasi

### 1. Dashboard & input kendaraan

Dashboard menampilkan ringkasan kendaraan, pendapatan, kapasitas slot Motor dan Mobil, serta formulir untuk memasukkan nomor plat dan tipe kendaraan. Waktu masuk dicatat otomatis ketika kendaraan ditambahkan.

![Dashboard dan input kendaraan](Dokumentasi/Dashboard%20%26%20Input%20Kendaraan.png)

### 2. Data kendaraan

Tabel data kendaraan menampilkan waktu masuk, durasi parkir, estimasi biaya, status, dan tombol **Keluar** untuk menyelesaikan transaksi kendaraan yang masih parkir.

![Data kendaraan](Dokumentasi/Data%20Kendaraan.png)

### 3. Laporan

Halaman laporan berisi transaksi kendaraan yang sudah keluar, termasuk waktu masuk dan keluar, durasi, jam tagihan, biaya, serta ringkasan pendapatan.

![Laporan parkir](Dokumentasi/laporan%20(2).png)

### 4. Pengujian valid dan tidak valid

#### 4.1 Valid

Masukkan nomor plat yang sesuai format dan pilih tipe kendaraan dengan slot tersedia. Sistem menerima kendaraan dan menampilkan pesan keberhasilan serta waktu masuk.

![Pengujian input valid](Dokumentasi/Input%20valid.png)

#### 4.2 Tidak valid

Masukkan nomor plat dengan format yang tidak sesuai. Sistem menolak data dan menampilkan pesan kesalahan validasi.

![Pengujian input tidak valid](Dokumentasi/Input%20tidak%20valid.png)

---

## 🧪 Skenario Uji Web

Jalankan skenario berikut melalui browser setelah aplikasi aktif. Untuk menguji kapasitas penuh, gunakan sesi/data uji yang masih memiliki slot tersedia, lalu tambahkan kendaraan sampai kapasitas tipe tersebut tercapai.

| No. | Modul / Fitur | Skenario Uji (Langkah-langkah) | Hasil yang Diharapkan (Expected Result) | Status |
| :---: | :--- | :--- | :--- | :--- |
| 1 | **Input Kendaraan** | 1. Buka halaman utama web.<br>2. Masukkan nomor plat yang valid (contoh: `B 1234 ABC`).<br>3. Pilih tipe kendaraan yang slotnya masih tersedia, misalnya **Mobil**.<br>4. Klik **+ Tambah Kendaraan**. | Data kendaraan berhasil disimpan dan tampil di tabel data parkir dengan status **Parkir** serta waktu masuk tercatat otomatis. Slot untuk tipe tersebut berkurang satu. | [✔] Lulus<br>[ ] Gagal |
| 2 | **Validasi Form Kosong** | 1. Buka halaman utama web.<br>2. Biarkan kolom nomor plat kosong.<br>3. Klik **+ Tambah Kendaraan**. | Form menolak pengiriman data dan browser menampilkan validasi bahwa nomor plat wajib diisi. Tidak ada kendaraan baru yang disimpan. | [✔] Lulus<br>[ ] Gagal |
| 3 | **Kapasitas Slot Penuh** | 1. Tambahkan kendaraan tipe **Mobil** sampai kapasitas maksimal tercapai (20 slot).<br>2. Periksa informasi slot pada dashboard.<br>3. Coba tambahkan satu kendaraan Mobil lagi. | Dashboard menampilkan **Slot Mobil: Penuh**, pilihan Mobil dinonaktifkan, dan server menolak kendaraan tambahan dengan pesan bahwa slot Mobil sudah penuh. | [✔] Lulus<br>[ ] Gagal |
| 4 | **Proses Keluar & Tarif** | 1. Klik tombol **Keluar** pada kendaraan yang masih berstatus Parkir.<br>2. Periksa waktu keluar dan durasi pada tabel.<br>3. Buka halaman **Laporan** dan periksa transaksi kendaraan tersebut. | Sistem mencatat waktu keluar, menghitung durasi dari waktu masuk dan keluar, membulatkan jam tagihan ke atas (minimal satu jam), lalu menghitung tarif sesuai tipe kendaraan. Status berubah menjadi **Keluar**, kendaraan muncul pada laporan, dan slotnya tersedia kembali. | [✔] Lulus<br>[ ] Gagal |

Kapasitas awal yang digunakan untuk skenario uji adalah **Motor: 50 slot** dan **Mobil: 20 slot**. Data parkir disimpan dalam session browser, jadi gunakan browser/session yang sama saat menguji proses masuk, keluar, dan laporan.

---

## 📂 Struktur Logika PHP Utama

| Komponen | Nama Fungsi / Class | Penjelasan |
| :--- | :--- | :--- |
| **Kalkulasi Durasi** | `hitungDurasiMenit($masukAt, $keluarAt)` | Menghitung lama parkir dari selisih timestamp masuk dan keluar dalam menit. |
| **Kalkulasi Biaya** | `hitungBiayaParkirMenit($tipe, $durasiMenit)` | Tarif Mobil Rp 5.000/jam dan Motor Rp 2.000/jam. Jam parsial dibulatkan ke atas, minimal 1 jam; diskon 10% mulai 10 jam. |
| **Slot & Kapasitas** | `KAPASITAS_PARKIR`, `hitungSlotParkir($data)` | Menetapkan kapasitas Motor 50 dan Mobil 20 slot, lalu menghitung slot terisi/tersisa dari kendaraan berstatus `Parkir`. |
| **Sanitasi Plat** | `normalisasiPlat($plat)` | Membersihkan spasi/simbol, mengubah ke huruf besar, dan menyeragamkan plat (contoh: `b1234abc` menjadi `B 1234 ABC`). |
| **Validasi Format** | `validasiPlat($plat)` | Menguji plat dengan regex (1-2 huruf wilayah, 1-4 angka, 1-3 huruf seri). Mengembalikan `true`/`false`. |
| **OOP Entitas** | `class Kendaraan` | Menyimpan timestamp masuk, serta mencatat waktu keluar dan menghitung durasi/biaya melalui `selesaikan()`. Data disimpan sebagai array melalui `toArray()`. |
| **Penyimpanan Sesi** | `$_SESSION['kendaraan']` | Menyimpan data kendaraan parkir dan yang sudah keluar. |
| **Aksi keluar** | `proses.php` | Mengubah status kendaraan menjadi `Keluar`, mencatat timestamp, lalu menghitung durasi dan biaya final. |
| **Laporan** | `laporan.php` | Hanya menampilkan kendaraan yang sudah berstatus `Keluar`, beserta ringkasan transaksi selesai dan pendapatan. |
| **Perulangan** | `while` / `do-while` | `while` digunakan untuk tabel di `index.php`; `do-while` digunakan untuk tabel laporan. |

Waktu masuk/keluar dicatat oleh server dalam zona `Asia/Jakarta`. Durasi aktif dan estimasi biaya di tabel diperbarui oleh JavaScript di browser; perhitungan final dan waktu transaksi ditentukan kembali di server saat tombol **Keluar** ditekan.

Kapasitas slot ditampilkan di dashboard. Form menonaktifkan pilihan tipe yang penuh dan memberi peringatan, sementara `proses.php` memvalidasi kapasitas kembali di server sebelum menerima kendaraan baru.

---

## 📊 Laporan Singkat Pengujian & Status Proyek

- **Status Proyek:** Dalam tahap pengembangan.
- **Ringkasan Evaluasi:** Fitur utama—input kendaraan, pencatatan waktu, perhitungan tarif, kapasitas slot, dan laporan—telah diuji dan berjalan sesuai fungsinya.
- **Catatan Pengujian:** Pengujian mencakup input valid dan tidak valid, penolakan saat slot penuh, serta proses keluar dan perhitungan biaya. Hasilnya sesuai harapan; rincian langkah dan screenshot tersedia pada bagian dokumentasi di atas.
