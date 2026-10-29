<?php
session_start();
require_once __DIR__ . '/tools/functions.php';

$authRole = $_SESSION['auth_role'] ?? null;
$dashboardRoutes = [
    'admin' => 'dashboard/dashboard_admin.php',
    'civitas' => 'dashboard/dashboard_civitas.php',
    'mitra' => 'dashboard/dashboard_mitra.php',
];
if (isset($dashboardRoutes[$authRole]) && ($_GET['beranda'] ?? '') !== '1') {
    header('Location: ' . $dashboardRoutes[$authRole]);
    exit;
}

$userData = $_SESSION['user_data'] ?? null;
$pesanSukses = $_SESSION['sukses'] ?? '';
$pesanError = $_SESSION['error'] ?? [];
unset($_SESSION['sukses'], $_SESSION['error']);

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Parkir Terintegrasi | Universitas Pancasila</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="assets/images/Logo UP.png">
</head>
<body id="beranda">

<!-- ===============================
     NAVBAR (UNIVERSITAS PANCASILA)
     =============================== -->
<header class="navbar-wrapper">
    <div class="navbar-container">
        <a href="index.php" class="brand-link" aria-label="Universitas Pancasila - Sistem Parkir">
            <img src="assets/images/Navbar.png" alt="Universitas Pancasila BAN-PT Unggul & Diktisaintek Berdampak" class="brand-navbar-img">
            <div class="brand-divider"></div>
            <div class="brand-text-block">
                <span class="brand-text-top">SISTEM PARKIR</span>
                <span class="brand-text-main">UNIVERSITAS PANCASILA</span>
            </div>
        </a>

        <button class="mobile-toggle" id="btn-mobile-nav" aria-label="Buka navigasi">☰</button>

        <nav class="nav-menu" id="nav-menu">
            <a href="#beranda" class="nav-link active">Beranda</a>
            <a href="#peraturan" class="nav-link">Peraturan</a>
            <a href="#pendaftaran" class="nav-link">Pendaftaran</a>
            <a href="#tarif" class="nav-link">Tarif</a>
            <a href="#faq" class="nav-link">FAQ</a>
            <?php if ($authRole === 'admin'): ?>
                <a href="dashboard/dashboard_admin.php" class="nav-link" style="color:var(--up-blue);font-weight:700;">Dashboard Operasional</a>
            <?php elseif ($authRole === 'civitas'): ?>
                <a href="dashboard/dashboard_civitas.php" class="nav-link" style="color:var(--up-blue);font-weight:700;">Dashboard Civitas</a>
            <?php elseif ($authRole === 'mitra'): ?>
                <a href="dashboard/dashboard_mitra.php" class="nav-link" style="color:var(--up-blue);font-weight:700;">Dashboard Mitra</a>
            <?php endif; ?>
        </nav>

        <div class="nav-actions">
            <?php if ($authRole === 'admin'): ?>
                <a href="dashboard/dashboard_admin.php" class="btn btn-primary" style="padding:6px 14px;font-size:12.5px;">
                    🛡️ Buka Dashboard Operasional
                </a>
                <a href="auth/logout.php" class="btn btn-outline" style="padding:6px 12px;font-size:12.5px;">Logout</a>
            <?php elseif ($authRole === 'civitas'): ?>
                <a href="dashboard/dashboard_civitas.php" class="btn btn-primary" style="padding:6px 14px;font-size:12.5px;">
                    🎓 E-Parking Pass Saya
                </a>
                <a href="auth/logout.php" class="btn btn-outline" style="padding:6px 12px;font-size:12.5px;">Logout</a>
            <?php elseif ($authRole === 'mitra'): ?>
                <a href="dashboard/dashboard_mitra.php" class="btn btn-primary" style="padding:6px 14px;font-size:12.5px;">
                    🏢 Dashboard Mitra
                </a>
                <a href="auth/logout.php" class="btn btn-outline" style="padding:6px 12px;font-size:12.5px;">Logout</a>
            <?php else: ?>
                <a href="auth/login_admin.php" class="btn-nav-admin" title="Khusus Petugas Barrier Gate Parkir">
                    <span>🛡️</span>
                    <span>Login Petugas / Admin</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main>
    <!-- Flash Notifications -->
    <div style="max-width:1240px;margin:16px auto 0;padding:0 24px;">
        <?php if ($pesanSukses !== ''): ?>
            <div class="alert-box success" role="status">
                <span style="font-size:18px;">✓</span>
                <div><?= e($pesanSukses) ?></div>
            </div>
        <?php endif; ?>

        <?php if (count($pesanError) > 0): ?>
            <div class="alert-box danger" role="alert">
                <span style="font-size:18px;">⚠</span>
                <div>
                    <strong>Pemberitahuan:</strong>
                    <?php foreach ($pesanError as $err): ?>
                        <div>• <?= e($err) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- ===============================
         HERO SECTION (UNIVERSITAS PANCASILA)
         =============================== -->
    <section class="hero-wrapper">
        <div class="hero-bg-organic-left" aria-hidden="true"></div>
        <div class="hero-bg-organic-right" aria-hidden="true"></div>

        <div class="hero-container">
            <div class="hero-content">
                <div class="hero-badge-tag">
                    <span>★</span>
                    <span>Sistem Parkir Universitas Pancasila</span>
                </div>

                <h1 class="hero-title">
                    Yuk, Berlangganan Parkir di <span>Universitas Pancasila</span>
                </h1>

                <p class="hero-description">
                    Bayar sekali nikmati layanan parkir gratis sampai dengan 6 (enam) bulan di lingkungan kampus Universitas Pancasila. Bebas antre karcis, aman dengan sensor RFID gate otomatis, dan terintegrasi kartu identitas kampus.
                </p>

                <!-- Tombol Masuk ke Sistem (Dropdown Role) -->
                <div class="masuk-sistem-box">
                    <button type="button" class="btn-masuk-sistem" id="btn-toggle-masuk" aria-haspopup="true" aria-expanded="true">
                        <span>Masuk ke Sistem</span>
                        <span class="caret-icon" id="caret-masuk">▼</span>
                    </button>

                    <!-- Pilihan Role Langsung Terhubung ke Halaman Masing-Masing -->
                    <div class="dropdown-role-menu" id="dropdown-role-menu">
                        <a href="<?= ($authRole === 'civitas') ? 'dashboard/dashboard_civitas.php' : 'auth/login_civitas.php' ?>" class="dropdown-role-item">
                            <span>🎓 Sebagai Dosen/Staf/Mahasiswa</span>
                            <span class="role-icon">→</span>
                        </a>
                        <a href="<?= ($authRole === 'mitra') ? 'dashboard/dashboard_mitra.php' : 'auth/login_mitra.php' ?>" class="dropdown-role-item">
                            <span>🏢 Sebagai Mitra</span>
                            <span class="role-icon">→</span>
                        </a>
                        <a href="<?= ($authRole === 'admin') ? 'dashboard/dashboard_admin.php' : 'auth/login_admin.php' ?>" class="dropdown-role-item item-admin">
                            <span>🛡️ Sebagai Admin / Petugas Parkir</span>
                            <span class="role-icon">🔐</span>
                        </a>
                    </div>

                    <?php if ($authRole !== null): ?>
                        <div class="active-role-card">
                            <div>
                                <span>Status Anda:</span>
                                <strong><?= e($userData['nama'] ?? ucfirst($authRole)) ?></strong>
                            </div>
                            <?php if ($authRole === 'admin'): ?>
                                <a href="dashboard/dashboard_admin.php" class="btn-link-action">Buka Dashboard Operasional →</a>
                            <?php elseif ($authRole === 'civitas'): ?>
                                <a href="dashboard/dashboard_civitas.php" class="btn-link-action">Buka Dashboard Civitas →</a>
                            <?php elseif ($authRole === 'mitra'): ?>
                                <a href="dashboard/dashboard_mitra.php" class="btn-link-action">Buka Dashboard Mitra →</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Visual 3D Isometric Parking Area -->
            <div class="hero-visual">
                <div class="hero-image-wrapper">
                    <img src="assets/images/parking_hero.jpg" alt="Ilustrasi Area Parkir Kampus Universitas Pancasila" loading="eager">
                </div>
            </div>
        </div>
    </section>

    <!-- ===============================
         SECTION: PERATURAN PARKIR KAMPUS UP
         =============================== -->
    <section class="section-wrapper bg-alt" id="peraturan">
        <div class="container-boxed">
            <div class="section-header">
                <span class="section-tag">Ketertiban Kampus</span>
                <h2 class="section-title">Peraturan Parkir Universitas Pancasila</h2>
                <p class="section-subtitle">Patuhi tata tertib berkendara dan parkir demi keselamatan dan keteraturan kawasan kampus UP.</p>
            </div>

            <div class="cards-grid-4">
                <div class="info-card">
                    <div class="info-card-icon">⚡</div>
                    <h3>Batas Kecepatan 20 km/jam</h3>
                    <p>Kendaraan bermotor wajib mematuhi batas kecepatan maksimal 20 km/jam di seluruh ruas jalan dalam kawasan kampus Universitas Pancasila.</p>
                </div>
                <div class="info-card">
                    <div class="info-card-icon">🪖</div>
                    <h3>Standar Keselamatan</h3>
                    <p>Wajib menggunakan helm berstandar SNI bagi pengendara roda dua dan sabuk pengaman bagi pengendara mobil saat memasuki gerbang.</p>
                </div>
                <div class="info-card">
                    <div class="info-card-icon">🅿️</div>
                    <h3>Parkir Sesuai Slot</h3>
                    <p>Parkir hanya pada kantong parkir fakultas yang disediakan. Dilarang parkir di bahu jalan lingkar kampus dan jalur evakuasi.</p>
                </div>
                <div class="info-card">
                    <div class="info-card-icon">💳</div>
                    <h3>Tap RFID / E-Pass Gate</h3>
                    <p>Gunakan kartu identitas civitas / stiker RFID resmi untuk membuka barrier gate otomatis di gerbang utama & srengseng sawah.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===============================
         SECTION: PENDAFTARAN LANGGANAN
         =============================== -->
    <section class="section-wrapper" id="pendaftaran">
        <div class="container-boxed">
            <div class="section-header">
                <span class="section-tag">Alur Pengajuan</span>
                <h2 class="section-title">Tahapan Pendaftaran Member Parkir</h2>
                <p class="section-subtitle">Ikuti langkah mudah berikut untuk mengaktifkan stiker RFID dan bebas biaya parkir semesteran.</p>
            </div>

            <div class="cards-grid-4">
                <div class="info-card">
                    <div class="info-card-icon">1</div>
                    <h3>Pilih Kategori Member</h3>
                    <p>Klik menu "Masuk ke Sistem" lalu pilih <a href="auth/login_civitas.php" style="color:var(--up-blue);font-weight:700;">Civitas UP</a> atau <a href="auth/login_mitra.php" style="color:var(--up-blue);font-weight:700;">Mitra Resmi</a>.</p>
                </div>
                <div class="info-card">
                    <div class="info-card-icon">2</div>
                    <h3>Lengkapi Data Diri</h3>
                    <p>Isikan NPM / NIP, Fakultas, Nomor Polisi Kendaraan (Plat), dan tipe kendaraan (Motor / Mobil).</p>
                </div>
                <div class="info-card">
                    <div class="info-card-icon">3</div>
                    <h3>Aktivasi Otomatis</h3>
                    <p>Sistem menerbitkan Kartu Member Parkir Digital yang langsung tersimpan di sistem gerbang otomatis UP.</p>
                </div>
                <div class="info-card">
                    <div class="info-card-icon">4</div>
                    <h3>Ambil Stiker Fisik</h3>
                    <p>Tunjukkan kartu digital ke pos pelayanan parkir Gedung Rektorat UP untuk penempelan stiker RFID fisik kendaraan.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===============================
         SECTION: TARIF & PAKET PARKIR
         =============================== -->
    <section class="section-wrapper bg-alt" id="tarif">
        <div class="container-boxed">
            <div class="section-header">
                <span class="section-tag">Skema Tarif</span>
                <h2 class="section-title">Pilihan Paket Langganan & Tarif</h2>
                <p class="section-subtitle">Tarif terjangkau bagi civitas akademika dan pengunjung kampus Universitas Pancasila.</p>
            </div>

            <div class="cards-grid-3">
                <div class="pricing-card featured">
                    <span class="pricing-badge">Civitas UP</span>
                    <h3>Mahasiswa, Dosen & Staf</h3>
                    <div class="pricing-price">GRATIS</div>
                    <div class="pricing-period">Terintegrasi 1 Semester (6 Bulan)</div>
                    <ul class="pricing-features">
                        <li><span class="check">✓</span> Akses barrier gate RFID bebas hambatan</li>
                        <li><span class="check">✓</span> Terintegrasi sistem KTM / Kartu Pegawai UP</li>
                        <li><span class="check">✓</span> Slot parkir resmi di gedung fakultas & rektorat</li>
                        <li><span class="check">✓</span> Keamanan & pengawasan kamera CCTV 24 jam</li>
                    </ul>
                    <a href="auth/login_civitas.php" class="btn btn-primary" style="width:100%;margin-top:14px;">
                        Login Civitas UP →
                    </a>
                </div>

                <div class="pricing-card">
                    <span class="pricing-badge" style="background:#0284c7;">Mitra Kampus</span>
                    <h3>Mitra & Tenant Resmi UP</h3>
                    <div class="pricing-price">Rp 100.000</div>
                    <div class="pricing-period">Per Bulan / Kontrak Kerjasama</div>
                    <ul class="pricing-features">
                        <li><span class="check">✓</span> Akses gate khusus vendor / tenant kantin UP</li>
                        <li><span class="check">✓</span> Slot khusus bongkar muat logistik kampus</li>
                        <li><span class="check">✓</span> Rekapitulasi transaksi bulanan transparan</li>
                        <li><span class="check">✓</span> Stiker resmi kendaraan operasional mitra</li>
                    </ul>
                    <a href="auth/login_mitra.php" class="btn btn-outline" style="width:100%;margin-top:14px;">
                        Login Mitra UP →
                    </a>
                </div>

                <div class="pricing-card">
                    <span class="pricing-badge" style="background:#64748b;">Pengunjung Umum</span>
                    <h3>Tamu & Pengunjung</h3>
                    <div class="pricing-price">Rp 2.000<span style="font-size:15px;color:#64748b;font-weight:normal;">/jam</span></div>
                    <div class="pricing-period">Motor: Rp 2.000/jam • Mobil: Rp 5.000/jam</div>
                    <ul class="pricing-features">
                        <li><span class="check">✓</span> Tiket karcis / barcode gerbang otomatis</li>
                        <li><span class="check">✓</span> Perhitungan durasi real-time per jam</li>
                        <li><span class="check">✓</span> <strong>Diskon 10%</strong> untuk durasi minimal 10 jam</li>
                        <li><span class="check">✓</span> Pembayaran tunai atau QRIS di gardu keluar</li>
                    </ul>
                    <a href="#peraturan" class="btn btn-secondary" style="width:100%;margin-top:14px;">
                        Lihat Tata Tertib Parkir
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ===============================
         SECTION: FAQ
         =============================== -->
    <section class="section-wrapper" id="faq">
        <div class="container-boxed">
            <div class="section-header">
                <span class="section-tag">Bantuan</span>
                <h2 class="section-title">Pertanyaan yang Sering Diajukan (FAQ)</h2>
                <p class="section-subtitle">Pertanyaan umum seputar layanan parkir di Universitas Pancasila.</p>
            </div>

            <div class="faq-list">
                <div class="faq-item open">
                    <button type="button" class="faq-question">
                        <span>Bagaimana cara mendaftar langganan parkir mahasiswa UP?</span>
                        <span class="faq-chevron">▲</span>
                    </button>
                    <div class="faq-answer">
                        Buka halaman <a href="auth/login_civitas.php" style="color:var(--up-blue);font-weight:700;">Login Civitas</a>. Jika belum memiliki akun, pilih "Buat akun baru" di bawah formulir login.
                    </div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-question">
                        <span>Apakah mahasiswa baru dikenakan biaya pendaftaran stiker?</span>
                        <span class="faq-chevron">▼</span>
                    </button>
                    <div class="faq-answer">
                        Tidak. Layanan parkir civitas akademika di lingkungan Universitas Pancasila sudah terintegrasi gratis selama masa studi aktif sesuai ketentuan kampus.
                    </div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-question">
                        <span>Bagaimana jika kartu atau stiker kendaraan rusak atau hilang?</span>
                        <span class="faq-chevron">▼</span>
                    </button>
                    <div class="faq-answer">
                        Laporkan segera ke Sentra Pelayanan Parkir Gedung Rektorat UP untuk penggantian stiker baru dengan menunjukkan bukti Kartu Tanda Mahasiswa (KTM).
                    </div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-question">
                        <span>Bagaimana cara petugas mengakses Dashboard Operasional?</span>
                        <span class="faq-chevron">▼</span>
                    </button>
                    <div class="faq-answer">
                        Petugas gerbang dapat mengakses <a href="auth/login_admin.php" style="color:var(--up-blue);font-weight:700;">Halaman Login Petugas / Admin</a> di pojok kanan atas untuk membuka sistem operasional barrier gate dan kas parkir.
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- ===============================
     FOOTER (UNIVERSITAS PANCASILA)
     =============================== -->
<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-col">
            <img src="assets/images/Navbar.png" alt="Universitas Pancasila" style="height:48px;margin-bottom:16px;filter:brightness(0) invert(1);">
            <p>
                Sistem Informasi & Manajemen Pengelolaan Parkir Berlangganan Universitas Pancasila. Mewujudkan kawasan kampus tertib, modern, aman, dan terintegrasi digital.
            </p>
            <p style="color:#94a3b8;font-size:12.5px;">
                Tugas Mata Kuliah Pemrograman Berbasis Web (PBW) &middot; Kelompok 5
            </p>
        </div>

        <div class="footer-col">
            <h4>Navigasi Layanan</h4>
            <ul class="footer-links">
                <li><a href="#beranda">Beranda</a></li>
                <li><a href="#peraturan">Peraturan & Ketertiban</a></li>
                <li><a href="#pendaftaran">Tahap Pendaftaran</a></li>
                <li><a href="#tarif">Skema Paket & Tarif</a></li>
                <li><a href="#faq">Pusat Bantuan (FAQ)</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Portal Masuk Pengguna UP</h4>
            <ul class="footer-links">
                <li><a href="auth/login_civitas.php">Portal Mahasiswa / Dosen / Staf</a></li>
                <li><a href="auth/login_mitra.php">Portal Mitra &amp; Tenant Kampus</a></li>
                <li><a href="auth/login_admin.php">Login Petugas &amp; Administrator</a></li>
                <li><a href="dashboard/laporan.php">Rekapitulasi Laporan Parkir</a></li>
                <li><a href="tools/tes_functions.php">Uji Verifikasi Fungsi</a></li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        PARKIRKU &copy; <?= date('Y') ?> Universitas Pancasila &middot; PBW Kelompok 5 (Bryan Hulu, Dheka Airlangga, Farhan Ridwan, Muhamad Bachtiar) &middot; Seluruh Hak Cipta Dilindungi.
    </div>
</footer>

<!-- ===============================
     JAVASCRIPT INTERAKTIF
     =============================== -->
<script>
    // Dropdown Masuk ke Sistem (Toggle)
    const btnToggleMasuk = document.getElementById('btn-toggle-masuk');
    const dropdownRoleMenu = document.getElementById('dropdown-role-menu');

    if (btnToggleMasuk && dropdownRoleMenu) {
        btnToggleMasuk.addEventListener('click', function (e) {
            e.stopPropagation();
            const isHidden = dropdownRoleMenu.style.display === 'none';
            dropdownRoleMenu.style.display = isHidden ? 'block' : 'none';
            btnToggleMasuk.classList.toggle('is-open', isHidden);
        });
    }

    // FAQ Accordion
    document.querySelectorAll('.faq-question').forEach(btn => {
        btn.addEventListener('click', function () {
            const item = this.parentElement;
            const isOpen = item.classList.contains('open');
            document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
            document.querySelectorAll('.faq-chevron').forEach(c => c.textContent = '▼');

            if (!isOpen) {
                item.classList.add('open');
                this.querySelector('.faq-chevron').textContent = '▲';
            }
        });
    });

    // Mobile Navbar
    const btnMobileNav = document.getElementById('btn-mobile-nav');
    const navMenu = document.getElementById('nav-menu');
    if (btnMobileNav && navMenu) {
        btnMobileNav.addEventListener('click', function () {
            navMenu.classList.toggle('is-active');
        });
    }
</script>
</body>
</html>
