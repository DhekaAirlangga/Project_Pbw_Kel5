<?php
session_start();
require_once __DIR__ . '/../tools/functions.php';
require_once __DIR__ . '/../config/database.php';

$role = $dashboardRole ?? '';
$dashboardConfig = [
    'civitas' => [
        'title' => 'Dashboard Civitas',
        'login' => '../auth/login_civitas.php',
        'identifierLabel' => 'NIM / NIP',
        'identifier' => $_SESSION['user_data']['npm'] ?? '—',
    ],
    'mitra' => [
        'title' => 'Dashboard Mitra',
        'login' => '../auth/login_mitra.php',
        'identifierLabel' => 'ID Mitra',
        'identifier' => $_SESSION['user_data']['id_mitra'] ?? '—',
    ],
];

if (!isset($dashboardConfig[$role])) {
    http_response_code(404);
    exit('Dashboard tidak ditemukan.');
}

if (($_SESSION['auth_role'] ?? '') !== $role) {
    $_SESSION['error'] = [$role === 'mitra'
        ? 'Silakan login terlebih dahulu sebagai Mitra Resmi UP.'
        : 'Silakan login terlebih dahulu sebagai Civitas Akademika.'];
    header('Location: ' . $dashboardConfig[$role]['login']);
    exit;
}

$profileToken = $_SESSION['profile_token'] ??= bin2hex(random_bytes(32));
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['form_type'] ?? '') === 'update_profile') {
    $errors = [];
    $submittedToken = $_POST['profile_token'] ?? '';
    if (!is_string($submittedToken) || !hash_equals($profileToken, $submittedToken)) {
        $errors[] = 'Sesi formulir kedaluwarsa. Muat ulang halaman lalu coba lagi.';
    }

    $nameInput = $_POST['profile_name'] ?? '';
    $emailInput = $_POST['profile_email'] ?? '';
    $picInput = $_POST['profile_pic'] ?? '';
    $name = is_string($nameInput) ? trim($nameInput) : '';
    $email = is_string($emailInput) ? trim($emailInput) : '';
    $pic = $role === 'mitra' && is_string($picInput) ? trim($picInput) : '';
    if ($name === '' || strlen($name) > 150) {
        $errors[] = 'Nama wajib diisi dan maksimal 150 karakter.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
        $errors[] = 'Alamat email tidak valid.';
    }
    if ($role === 'mitra' && ($pic === '' || strlen($pic) > 150)) {
        $errors[] = 'Nama penanggung jawab wajib diisi dan maksimal 150 karakter.';
    }

    $photoUpload = $_FILES['profile_photo'] ?? null;
    $photoMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    $newPhotoPath = null;
    if ($photoUpload !== null && (!is_array($photoUpload)
        || !isset($photoUpload['error'], $photoUpload['size'], $photoUpload['tmp_name'])
        || !is_int($photoUpload['error'])
        || !is_int($photoUpload['size'])
        || !is_string($photoUpload['tmp_name']))) {
        $errors[] = 'Data foto profil tidak valid. Pilih ulang file foto.';
        $photoUpload = null;
    }
    if (is_array($photoUpload) && $photoUpload['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($photoUpload['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Foto gagal diunggah. Pilih ulang file foto.';
        } elseif ($photoUpload['size'] > 3 * 1024 * 1024) {
            $errors[] = 'Ukuran foto maksimal 3 MB.';
        } elseif (!is_uploaded_file($photoUpload['tmp_name'])) {
            $errors[] = 'Berkas foto tidak valid. Pilih ulang file foto.';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($photoUpload['tmp_name']);
            if (!isset($photoMimeTypes[$mimeType])) {
                $errors[] = 'Format foto harus JPG, PNG, atau WEBP.';
            }
        }
    }

    $user = $_SESSION['user_data'] ?? [];
    $memberId = $role === 'mitra' ? ($user['id_mitra'] ?? '') : ($user['npm'] ?? '');
    if (!$errors) {
        $table = $role === 'mitra' ? 'users_mitra' : 'users_civitas';
        $idColumn = $role === 'mitra' ? 'id_mitra' : 'npm';
        $emailCheck = getDB()->prepare("SELECT id FROM {$table} WHERE email = ? AND {$idColumn} <> ? LIMIT 1");
        $emailCheck->execute([$email, $memberId]);
        if ($emailCheck->fetch()) {
            $errors[] = 'Email tersebut sudah digunakan akun lain.';
        }
    }

    if (!$errors && is_array($photoUpload) && $photoUpload['error'] === UPLOAD_ERR_OK) {
        $profileDirectory = __DIR__ . '/../assets/images/profiles';
        if (!is_dir($profileDirectory) || !is_writable($profileDirectory)) {
            $errors[] = 'Folder foto profil tidak tersedia atau tidak memiliki izin tulis.';
        } else {
            $fileName = bin2hex(random_bytes(16)) . '.' . $photoMimeTypes[$mimeType];
            if (!move_uploaded_file($photoUpload['tmp_name'], $profileDirectory . '/' . $fileName)) {
                $errors[] = 'Foto profil tidak dapat disimpan. Silakan coba lagi.';
            } else {
                $newPhotoPath = 'assets/images/profiles/' . $fileName;
            }
        }
    }

    if ($errors) {
        $_SESSION['error'] = $errors;
        $_SESSION['open_profile'] = true;
    } else {
        $previousPhoto = $user['foto_profil'] ?? null;
        $photoPath = $newPhotoPath ?? $previousPhoto;
        if ($role === 'mitra') {
            $update = getDB()->prepare('UPDATE users_mitra SET nama = ?, pic = ?, email = ?, foto_profil = ? WHERE id_mitra = ?');
            $update->execute([$name, $pic, $email, $photoPath, $memberId]);
            $_SESSION['user_data']['pic'] = $pic;
        } else {
            $update = getDB()->prepare('UPDATE users_civitas SET nama = ?, email = ?, foto_profil = ? WHERE npm = ?');
            $update->execute([$name, $email, $photoPath, $memberId]);
        }

        $_SESSION['user_data']['nama'] = $name;
        $_SESSION['user_data']['email'] = $email;
        $_SESSION['user_data']['foto_profil'] = $photoPath;
        $_SESSION['sukses'] = 'Profil berhasil diperbarui.';

        if ($newPhotoPath !== null && is_string($previousPhoto)
            && str_starts_with($previousPhoto, 'assets/images/profiles/')
            && basename($previousPhoto) === substr($previousPhoto, strlen('assets/images/profiles/'))) {
            $oldPhoto = __DIR__ . '/../' . $previousPhoto;
            if (is_file($oldPhoto) && !unlink($oldPhoto)) {
                error_log('Profil lama tidak dapat dihapus: ' . $previousPhoto);
            }
        }
    }

    header('Location: dashboard_' . $role . '.php#profile');
    exit;
}

$user = $_SESSION['user_data'] ?? [];
$config = $dashboardConfig[$role];
$name = $user['nama'] ?? ($role === 'mitra' ? 'Mitra Kampus' : 'Member Civitas');
$plate = $user['plat'] ?? '—';
$vehicleType = $user['tipe'] ?? '—';
$memberPeriod = $user['berlaku'] ?? '—';
$memberId = $config['identifier'];
$barcodeValue = 'UP-PASS-' . strtoupper(substr(
    hash('sha256', $role . ':' . $memberId . ':' . $plate),
    0,
    8
));
$memberType = $role === 'civitas'
    ? 'Civitas · ' . ($user['peran'] ?? 'Mahasiswa')
    : 'Mitra Kampus';
$profileMeta = $role === 'civitas'
    ? ($user['fakultas'] ?? 'Universitas Pancasila')
    : 'PIC: ' . ($user['pic'] ?? '—');
$passDetails = $role === 'civitas'
    ? [
        ['Nama Pemilik:', $name],
        ['NPM / NIP:', $memberId],
        ['Fakultas / Unit:', $user['fakultas'] ?? 'Tidak berlaku'],
        ['Jenjang Pendidikan:', ($user['jenjang'] ?? '') ?: 'Tidak berlaku'],
        ['Program Studi:', ($user['program_studi'] ?? '') ?: 'Tidak berlaku'],
        ['Kategori:', $user['peran'] ?? 'Civitas'],
        ['Nomor Polisi (Plat):', $plate],
        ['Jenis Kendaraan:', $vehicleType],
    ]
    : [
        ['Nama Badan Usaha:', $name],
        ['ID Kemitraan:', $memberId],
        ['Penanggung Jawab (PIC):', $user['pic'] ?? '—'],
        ['Paket Langganan:', $memberPeriod],
        ['Nomor Polisi (Plat):', $plate],
        ['Kendaraan Operasional:', $vehicleType],
    ];
$initials = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 2));
$profilePhoto = $user['foto_profil'] ?? '';
$pesanSukses = $_SESSION['sukses'] ?? '';
unset($_SESSION['sukses']);
$hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
$bulan = [
    1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];
$tanggalHariIni = $hari[(int) date('N') - 1] . ', ' . date('d') . ' ' . $bulan[(int) date('n')] . ' ' . date('Y');

$history = array_values(array_filter(
    $_SESSION['kendaraan'] ?? [],
    static fn(array $vehicle): bool => ($vehicle['plat'] ?? '') === $plate
));
usort($history, static fn(array $left, array $right): int =>
    strcmp($right['masuk'] ?? '', $left['masuk'] ?? '')
);
$history = array_slice($history, 0, 5);

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function renderCode39(string $value): string
{
    $patterns = [
        '0' => 'nnnwwnwnn', '1' => 'wnnwnnnnw', '2' => 'nnwwnnnnw', '3' => 'wnwwnnnnn',
        '4' => 'nnnwwnnnw', '5' => 'wnnwwnnnn', '6' => 'nnwwwnnnn', '7' => 'nnnwnnwnw',
        '8' => 'wnnwnnwnn', '9' => 'nnwwnnwnn', 'A' => 'wnnnnwnnw', 'B' => 'nnwnnwnnw',
        'C' => 'wnwnnwnnn', 'D' => 'nnnnwwnnw', 'E' => 'wnnnwwnnn', 'F' => 'nnwnwwnnn',
        'G' => 'nnnnnwwnw', 'H' => 'wnnnnwwnn', 'I' => 'nnwnnwwnn', 'J' => 'nnnnwwwnn',
        'K' => 'wnnnnnnww', 'L' => 'nnwnnnnww', 'M' => 'wnwnnnnwn', 'N' => 'nnnnwnnww',
        'O' => 'wnnnwnnwn', 'P' => 'nnwnwnnwn', 'Q' => 'nnnnnnwww', 'R' => 'wnnnnnwwn',
        'S' => 'nnwnnnwwn', 'T' => 'nnnnwnwwn', 'U' => 'wwnnnnnnw', 'V' => 'nwwnnnnnw',
        'W' => 'wwwnnnnnn', 'X' => 'nwnnwnnnw', 'Y' => 'wwnnwnnnn', 'Z' => 'nwwnwnnnn',
        '-' => 'nwnnnnwnw', '*' => 'nwnnwnwnn',
    ];
    $encoded = '*' . strtoupper($value) . '*';
    $narrowWidth = 2;
    $wideWidth = 5;
    $characterGap = 2;
    $quietZone = 12;
    $x = $quietZone;
    $bars = [];

    foreach (str_split($encoded) as $characterIndex => $character) {
        if (!isset($patterns[$character])) {
            throw new InvalidArgumentException('Karakter barcode Code 39 tidak didukung.');
        }

        foreach (str_split($patterns[$character]) as $elementIndex => $element) {
            $width = $element === 'w' ? $wideWidth : $narrowWidth;
            if ($elementIndex % 2 === 0) {
                $bars[] = '<rect x="' . $x . '" y="0" width="' . $width . '" height="48"/>';
            }
            $x += $width;
        }

        if ($characterIndex < strlen($encoded) - 1) {
            $x += $characterGap;
        }
    }

    $width = $x + $quietZone;
    return '<svg viewBox="0 0 ' . $width . ' 48" role="img" aria-label="Barcode Code 39 ' . e($value) . '"'
        . ' preserveAspectRatio="none"><rect width="' . $width . '" height="48" fill="#fff"/>'
        . '<g fill="#172d4b">' . implode('', $bars) . '</g></svg>';
}

$pesanError = $_SESSION['error'] ?? [];
unset($_SESSION['error']);
$openProfile = $_SESSION['open_profile'] ?? false;
unset($_SESSION['open_profile']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($config['title']) ?> | PARKIRKU UP</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/member-dashboard.css">
    <link rel="icon" type="image/png" href="../assets/images/Logo UP.png">
</head>
<body class="member-dashboard">
<div class="dashboard-shell">
    <aside class="dashboard-sidebar" aria-label="Navigasi dashboard">
        <a class="sidebar-brand" href="dashboard_<?= e($role) ?>.php" aria-label="PARKIRKU UP, dashboard">
            <span class="sidebar-brand-mark">P</span>
            <span><strong>PARKIRKU</strong><small>UNIVERSITAS PANCASILA</small></span>
        </a>

        <div class="sidebar-label">MENU UTAMA</div>
        <nav class="sidebar-nav">
            <a class="sidebar-link is-active" href="#dashboard"><span>⌂</span>Dasbor</a>
            <a class="sidebar-link" href="#digital-card"><span>▣</span>Kartu Parkir Digital</a>
            <a class="sidebar-link" href="#parking-history"><span>◷</span>Riwayat Parkir</a>
            <a class="sidebar-link" href="#vehicles"><span>▱</span>Daftar Kendaraan</a>
            <a class="sidebar-link" href="#balance"><span>◈</span>Pembayaran &amp; Saldo</a>
            <a class="sidebar-link" href="#application"><span>＋</span>Pengajuan Member</a>
            <a class="sidebar-link" href="#help"><span>?</span>Bantuan</a>
        </nav>

        <div class="sidebar-bottom">
            <div class="sidebar-label">TAUTAN CEPAT</div>
            <a class="sidebar-link" href="../index.php?beranda=1"><span>↗</span>Website PARKIRKU</a>
            <a class="sidebar-link" href="mailto:parkir@univpancasila.ac.id"><span>✉</span>Hubungi Bantuan</a>
            <div class="sidebar-socials" aria-label="Media sosial">
                <a href="https://www.instagram.com/universitas_pancasila/" aria-label="Instagram Universitas Pancasila">ig</a>
                <a href="https://www.facebook.com/UniversitasPancasila/" aria-label="Facebook Universitas Pancasila">f</a>
                <a href="https://www.youtube.com/" aria-label="YouTube">▶</a>
            </div>
            <div class="sidebar-copyright">© <?= date('Y') ?> PARKIRKU UP</div>
        </div>
    </aside>

    <main class="dashboard-main" id="dashboard">
        <header class="dashboard-topbar">
            <div class="topbar-heading">
                <span class="topbar-eyebrow">PORTAL MEMBER PARKIR</span>
                <h1>Dasbor</h1>
            </div>
            <div class="topbar-actions">
                <span class="topbar-date"><?= e($tanggalHariIni) ?></span>
                <a class="topbar-profile" href="#profile" aria-label="Profil <?= e($name) ?>">
                    <?php if ($profilePhoto !== ''): ?>
                        <img class="avatar avatar-small avatar-photo" src="../<?= e($profilePhoto) ?>" alt="">
                    <?php else: ?>
                        <span class="avatar avatar-small"><?= e($initials ?: 'UP') ?></span>
                    <?php endif; ?>
                    <span class="topbar-profile-name"><?= e($name) ?></span>
                </a>
                <a class="logout-link" href="../auth/logout.php">Keluar</a>
            </div>
        </header>

        <?php if ($pesanSukses !== ''): ?>
            <div class="dashboard-alert success" role="status"><?= e($pesanSukses) ?></div>
        <?php endif; ?>
        <?php if ($pesanError): ?>
            <div class="dashboard-alert danger" role="alert">
                <?php foreach ($pesanError as $error): ?><div><?= e($error) ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <section class="member-banner" id="profile" aria-label="Profil member">
            <div class="member-banner-pattern" aria-hidden="true"></div>
            <div class="member-profile">
                <?php if ($profilePhoto !== ''): ?>
                    <img class="avatar avatar-large avatar-photo" src="../<?= e($profilePhoto) ?>" alt="Foto profil <?= e($name) ?>">
                <?php else: ?>
                    <div class="avatar avatar-large"><?= e($initials ?: 'UP') ?></div>
                <?php endif; ?>
                <div class="member-profile-info">
                    <div class="member-name-row">
                        <h2><?= e($name) ?></h2>
                        <span class="status-pill"><i></i>Member Aktif</span>
                    </div>
                    <p class="member-affiliation"><?= e($profileMeta) ?></p>
                    <div class="member-meta">
                        <span>✉ <?= e($user['email'] ?? 'Email belum terdaftar') ?></span>
                        <span><?= e($config['identifierLabel']) ?>: <?= e($memberId) ?></span>
                    </div>
                </div>
            </div>
            <div class="member-banner-actions">
                <button class="profile-edit-cta" type="button" data-open-profile>
                    <span aria-hidden="true">✎</span>Edit Profil
                </button>
                <button class="qr-cta" id="digital-card" type="button" data-open-qr>
                    <span class="qr-cta-icon">▦</span>
                    <span><strong>Kartu Parkir Digital</strong><small>Tampilkan QR Code</small></span>
                    <span class="qr-cta-arrow">→</span>
                </button>
            </div>
        </section>

        <section class="metric-grid" aria-label="Ringkasan member">
            <article class="metric-card metric-blue">
                <div class="metric-icon">♙</div>
                <div><span class="metric-label">Tipe Member</span><strong class="metric-value"><?= e($memberType) ?></strong></div>
            </article>
            <article class="metric-card metric-purple" id="vehicles">
                <div class="metric-icon">▱</div>
                <div><span class="metric-label">Plat Nomor Utama</span><strong class="metric-value"><?= e($plate) ?></strong><small><?= e($vehicleType) ?></small></div>
            </article>
            <article class="metric-card metric-green">
                <div class="metric-icon">◷</div>
                <div><span class="metric-label">Masa Aktif Member</span><strong class="metric-value"><?= e($memberPeriod) ?></strong><small>Status aktif</small></div>
            </article>
            <article class="metric-card metric-orange" id="balance">
                <div class="metric-icon">◈</div>
                <div><span class="metric-label">Saldo E-Parking</span><strong class="metric-value">Belum tersedia</strong><small>Fitur saldo belum terhubung</small></div>
            </article>
            <article class="metric-card metric-cyan">
                <div class="metric-icon">▣</div>
                <div><span class="metric-label">Kendaraan Terdaftar</span><strong class="metric-value">1 Kendaraan</strong><small><?= e($plate) ?></small></div>
            </article>
        </section>

        <div class="dashboard-content-grid">
            <section class="dashboard-panel parking-panel" aria-labelledby="parking-title">
                <div class="panel-heading">
                    <div>
                        <span class="panel-kicker">INFORMASI AREA</span>
                        <h2 id="parking-title">Ketersediaan Slot Parkir</h2>
                    </div>
                    <span class="live-indicator"><i></i>Belum terhubung</span>
                </div>
                <p class="panel-description">Pantau kapasitas area parkir kampus dari satu halaman.</p>
                <div class="location-list">
                    <?php foreach (['Gedung A', 'Gedung B', 'Lapangan Utama'] as $index => $location): ?>
                        <article class="location-card">
                            <div class="location-icon"><?= $index === 2 ? '▤' : '▥' ?></div>
                            <div class="location-details">
                                <strong><?= e($location) ?></strong>
                                <span>Mobil <b>—</b><em>slot</em><span class="location-divider"></span>Motor <b>—</b><em>slot</em></span>
                            </div>
                            <span class="location-arrow">›</span>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="data-note"><span>i</span> Data slot per gedung belum tersedia. Integrasi sensor akan menampilkan kapasitas di sini.</div>
            </section>

            <section class="dashboard-panel history-panel" id="parking-history" aria-labelledby="history-title">
                <div class="panel-heading">
                    <div>
                        <span class="panel-kicker">AKTIVITAS TERKINI</span>
                        <h2 id="history-title">Riwayat Parkir Terbaru</h2>
                    </div>
                    <a class="panel-action" href="#parking-history">Lihat semua <span>→</span></a>
                </div>
                <div class="table-wrap">
                    <table class="history-table">
                        <thead><tr><th>Tanggal / Waktu</th><th>Lokasi Parkir</th><th>Plat Nomor</th><th>Durasi</th><th>Tarif</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php if ($history): ?>
                            <?php foreach ($history as $entry): ?>
                                <tr>
                                    <td><?= e($entry['masuk'] ?? '—') ?></td>
                                    <td>Area kampus</td>
                                    <td><strong><?= e($entry['plat'] ?? '—') ?></strong></td>
                                    <td><?= e(($entry['durasi'] ?? '—') . (isset($entry['durasi']) ? ' jam' : '')) ?></td>
                                    <td><?= e(formatRupiah((int) ($entry['biaya'] ?? 0))) ?></td>
                                    <td><span class="history-status <?= ($entry['status'] ?? '') === 'Keluar' ? 'status-out' : 'status-in' ?>"><?= e($entry['status'] ?? '—') ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="empty-history"><span>◷</span><strong>Belum ada riwayat parkir</strong><small>Transaksi dengan kendaraan terdaftar akan muncul di sini.</small></td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <section class="dashboard-panel quick-panel" id="application">
            <div class="quick-panel-copy">
                <span class="panel-kicker">LAYANAN MEMBER</span>
                <h2>Butuh bantuan atau ingin mengajukan kendaraan?</h2>
                <p>Hubungi layanan PARKIRKU UP untuk informasi kartu, perubahan data, dan bantuan parkir.</p>
            </div>
            <div class="quick-panel-actions" id="help">
                <a class="quick-action secondary" href="mailto:parkir@univpancasila.ac.id">Hubungi Bantuan</a>
                <button class="quick-action primary" type="button" data-open-qr>Kartu Digital <span>→</span></button>
            </div>
        </section>

        <footer class="dashboard-footer">© <?= date('Y') ?> PARKIRKU · Universitas Pancasila</footer>
    </main>
</div>

<dialog class="profile-dialog" aria-labelledby="profile-dialog-title">
    <button class="qr-dialog-close" type="button" aria-label="Tutup" data-close-profile>×</button>
    <div class="profile-dialog-heading">
        <span class="qr-dialog-kicker">PARKIRKU · PENGATURAN AKUN</span>
        <h2 id="profile-dialog-title">Edit Profil</h2>
        <p>Perbarui informasi kontak dan foto profil Anda.</p>
    </div>
    <form class="profile-edit-form" action="dashboard_<?= e($role) ?>.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="form_type" value="update_profile">
        <input type="hidden" name="profile_token" value="<?= e($profileToken) ?>">
        <div class="profile-photo-field">
            <?php if ($profilePhoto !== ''): ?>
                <img class="avatar profile-photo-preview avatar-photo" src="../<?= e($profilePhoto) ?>" alt="">
            <?php else: ?>
                <span class="avatar profile-photo-preview"><?= e($initials ?: 'UP') ?></span>
            <?php endif; ?>
            <label>
                <span>Foto profil</span>
                <input type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp">
                <small>JPG, PNG, atau WEBP. Maksimal 3 MB.</small>
            </label>
        </div>
        <label class="profile-form-field">
            <span><?= $role === 'mitra' ? 'Nama usaha' : 'Nama lengkap' ?></span>
            <input type="text" name="profile_name" maxlength="150" value="<?= e($name) ?>" autocomplete="name" required>
        </label>
        <?php if ($role === 'mitra'): ?>
            <label class="profile-form-field">
                <span>Nama penanggung jawab (PIC)</span>
                <input type="text" name="profile_pic" maxlength="150" value="<?= e($user['pic'] ?? '') ?>" required>
            </label>
        <?php endif; ?>
        <label class="profile-form-field">
            <span>Email</span>
            <input type="email" name="profile_email" maxlength="254" value="<?= e($user['email'] ?? '') ?>" autocomplete="email" required>
        </label>
        <div class="profile-form-actions">
            <button class="profile-cancel-button" type="button" data-close-profile>Batal</button>
            <button class="profile-save-button" type="submit">Simpan Perubahan</button>
        </div>
    </form>
</dialog>

<dialog class="qr-dialog" aria-labelledby="qr-title">
    <button class="qr-dialog-close" type="button" aria-label="Tutup" data-close-qr>×</button>
    <div class="pass-dialog-heading">
        <span class="qr-dialog-kicker">PARKIRKU · MEMBER PASS</span>
        <h2 id="qr-title">Kartu Parkir Digital</h2>
        <p class="qr-dialog-description">Tunjukkan kartu member ini kepada petugas parkir.</p>
    </div>
    <article class="parking-pass-card">
        <header class="pass-card-header">
            <div class="pass-card-brand">
                <img src="../assets/images/Logo UP.png" alt="Logo Universitas Pancasila">
                <div>
                    <span><?= $role === 'civitas' ? 'E-PARKING PASS MAHASISWA & DOSEN' : 'E-PARKING PASS MITRA KAMPUS' ?></span>
                    <strong>UNIVERSITAS PANCASILA</strong>
                </div>
            </div>
            <span class="pass-card-status"><i>★</i> MEMBER AKTIF</span>
        </header>
        <div class="pass-card-content">
            <div class="pass-card-details">
                <?php foreach ($passDetails as [$label, $value]): ?>
                    <div class="pass-card-field">
                        <span><?= e($label) ?></span>
                        <strong class="<?= $label === 'Nomor Polisi (Plat):' ? 'pass-card-plate' : '' ?>"><?= e($value) ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="pass-card-barcode-panel">
                <span class="pass-barcode-caption">SENSOR BARCODE RFID GATE</span>
                <div class="pass-barcode"><?= renderCode39($barcodeValue) ?></div>
                <code><?= e($barcodeValue) ?></code>
            </div>
        </div>
        <footer class="pass-card-footer">
            <span>Masa Berlaku: <?= e($memberPeriod) ?> · Akses: Seluruh Gerbang &amp; Shelter Parkir UP</span>
            <strong><?= $role === 'civitas' ? 'BEBAS BIAYA PARKIR' : 'KONTRAK AKTIF' ?></strong>
        </footer>
    </article>
    <div class="pass-dialog-actions">
        <span>Barcode diperbarui otomatis dari ID member dan plat kendaraan.</span>
        <button class="pass-print-button" type="button" onclick="window.print()">▣ Cetak Kartu</button>
    </div>
</dialog>

<script>
    const qrDialog = document.querySelector('.qr-dialog');
    const profileDialog = document.querySelector('.profile-dialog');
    document.querySelectorAll('[data-open-profile]').forEach((button) => {
        button.addEventListener('click', () => profileDialog.showModal());
    });
    document.querySelectorAll('[data-close-profile]').forEach((button) => {
        button.addEventListener('click', () => profileDialog.close());
    });
    profileDialog.addEventListener('click', (event) => {
        if (event.target === profileDialog) profileDialog.close();
    });
    if (<?= $openProfile ? 'true' : 'false' ?>) profileDialog.showModal();
    document.querySelectorAll('[data-open-qr]').forEach((button) => {
        button.addEventListener('click', () => qrDialog.showModal());
    });
    document.querySelectorAll('[data-close-qr]').forEach((button) => {
        button.addEventListener('click', () => qrDialog.close());
    });
    qrDialog.addEventListener('click', (event) => {
        if (event.target === qrDialog) qrDialog.close();
    });
</script>
</body>
</html>
