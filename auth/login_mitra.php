<?php
session_start();
require_once __DIR__ . '/../tools/functions.php';
require_once __DIR__ . '/../config/database.php';

// Jika sudah login sebagai mitra, langsung ke dashboard mitra
if (($_SESSION['auth_role'] ?? '') === 'mitra') {
    header('Location: ../dashboard/dashboard_mitra.php');
    exit;
}

$pesanError = $_SESSION['error'] ?? [];
$pesanSukses = $_SESSION['sukses'] ?? '';
unset($_SESSION['error'], $_SESSION['sukses']);

// Handle Form Submission POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['form_type'] ?? '';

    if ($formType === 'register_mitra') {
        $namaUsaha = trim($_POST['mitra_nama'] ?? '');
        $pic = trim($_POST['mitra_pic'] ?? '');
        $emailInput = $_POST['mitra_email'] ?? '';
        $email = is_string($emailInput) ? trim($emailInput) : '';
        $plat = normalisasiPlat(trim($_POST['mitra_plat'] ?? ''));
        $tipe = trim($_POST['mitra_tipe'] ?? 'Mobil');
        $paket = trim($_POST['mitra_paket'] ?? '1 Bulan');
        $password = $_POST['mitra_password'] ?? '';

        if ($namaUsaha === '' || $pic === '' || $email === '' || $plat === '' || $password === '') {
            $pesanError[] = 'Semua kolom bertanda bintang (*) wajib diisi.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
            $pesanError[] = 'Alamat email tidak valid.';
        } elseif (strlen($password) < 6) {
            $pesanError[] = 'Password minimal 6 karakter.';
        } elseif (!validasiPlat($plat)) {
            $pesanError[] = 'Format plat nomor tidak valid. Contoh: B 9012 UP.';
        } elseif (!validasiTipe($tipe) || !in_array($paket, ['1 Bulan', '6 Bulan'], true)) {
            $pesanError[] = 'Pilihan tipe kendaraan atau paket tidak valid.';
        } else {
            $pdo = getDB();
            $emailCheck = $pdo->prepare('SELECT id FROM users_mitra WHERE email = ? LIMIT 1');
            $emailCheck->execute([$email]);
            if ($emailCheck->fetch()) {
                $pesanError[] = 'Email tersebut sudah terdaftar. Silakan login atau gunakan email lain.';
            }

            if (!$pesanError) {
                do {
                    $idMitra = 'MTR-UP-' . random_int(100000, 999999);
                    $check = $pdo->prepare('SELECT id FROM users_mitra WHERE id_mitra = ? LIMIT 1');
                    $check->execute([$idMitra]);
                } while ($check->fetch());

                $stmt = $pdo->prepare('
                    INSERT INTO users_mitra (id_mitra, nama, pic, email, password, plat, tipe, berlaku)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ');
                $stmt->execute([
                    $idMitra,
                    $namaUsaha,
                    $pic,
                    $email,
                    password_hash($password, PASSWORD_DEFAULT),
                    $plat,
                    $tipe,
                    $paket . ' (Aktif)',
                ]);

                session_regenerate_id(true);
                $_SESSION['auth_role'] = 'mitra';
                $_SESSION['user_data'] = [
                    'nama' => $namaUsaha,
                    'email' => $email,
                    'foto_profil' => null,
                    'id_mitra' => $idMitra,
                    'pic' => $pic,
                    'peran' => 'Mitra Kampus',
                    'plat' => $plat,
                    'tipe' => $tipe,
                    'berlaku' => $paket . ' (Aktif)',
                ];
                $_SESSION['sukses'] = "Pendaftaran mitra {$namaUsaha} berhasil. ID Mitra Anda: {$idMitra}. Simpan ID dan password untuk login.";
                header('Location: ../dashboard/dashboard_mitra.php');
                exit;
            }
        }
    } elseif ($formType === 'login_mitra') {
        $idMitra = trim($_POST['mitra_login_id'] ?? '');
        $password = $_POST['mitra_login_pass'] ?? '';
        if ($idMitra === '' || $password === '') {
            $pesanError[] = 'ID Mitra dan password wajib diisi untuk login.';
        } else {
            $stmt = getDB()->prepare('SELECT * FROM users_mitra WHERE id_mitra = ? LIMIT 1');
            $stmt->execute([$idMitra]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                $pesanError[] = 'ID Mitra atau password salah.';
            } else {
                session_regenerate_id(true);
                $_SESSION['auth_role'] = 'mitra';
                $_SESSION['user_data'] = [
                    'nama' => $user['nama'],
                    'email' => $user['email'] ?? '',
                    'foto_profil' => $user['foto_profil'] ?? null,
                    'id_mitra' => $user['id_mitra'],
                    'pic' => $user['pic'],
                    'peran' => 'Mitra Kampus',
                    'plat' => $user['plat'],
                    'tipe' => $user['tipe'],
                    'berlaku' => $user['berlaku'],
                ];
                $_SESSION['sukses'] = 'Berhasil login ke Akun Member Mitra UP.';
                header('Location: ../dashboard/dashboard_mitra.php');
                exit;
            }
        }
    }
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$showRegister = $_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['form_type'] ?? '') === 'register_mitra';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Registrasi Mitra | Universitas Pancasila</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" type="image/png" href="../assets/images/Logo UP.png">
    <style>
        .auth-wrapper {
            min-height: calc(100vh - 140px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        .auth-card {
            background: #ffffff;
            border: 1px solid var(--line);
            border-radius: 14px;
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 520px;
            overflow: hidden;
        }
        .auth-header {
            background: var(--up-blue-subtle);
            border-bottom: 2px solid var(--up-blue-light);
            padding: 24px;
            text-align: center;
        }
        .auth-header img {
            height: 48px;
            margin-bottom: 10px;
        }
        .auth-header h2 {
            font-size: 20px;
            font-weight: 800;
            color: var(--up-blue);
        }
        .auth-header p {
            color: var(--muted);
            font-size: 13.5px;
            margin-top: 4px;
        }
        .auth-body {
            padding: 26px;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>

<header class="navbar-wrapper">
    <div class="navbar-container">
        <a href="../index.php" class="brand-link">
            <img src="../assets/images/Navbar.png" alt="Universitas Pancasila" class="brand-navbar-img">
            <div class="brand-divider"></div>
            <div class="brand-text-block">
                <span class="brand-text-top">PORTAL MITRA KAMPUS</span>
                <span class="brand-text-main">UNIVERSITAS PANCASILA</span>
            </div>
        </a>
        <a href="../index.php" class="btn btn-outline" style="font-size:13px;padding:6px 14px;">← Kembali ke Beranda</a>
    </div>
</header>

<main class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <img src="../assets/images/Logo UP.png" alt="Logo UP">
            <h2>Layanan Parkir Mitra & Tenant UP</h2>
            <p>Pendaftaran akses parkir vendor, rekanan resmi, dan tenant kantin</p>
        </div>

        <?php if (count($pesanError) > 0): ?>
            <div style="padding:16px 26px 0;">
                <div class="alert-box danger" role="alert" style="margin-bottom:0;">
                    <span>⚠</span>
                    <div>
                        <?php foreach ($pesanError as $err): ?>
                            <div>• <?= e($err) ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($pesanSukses !== ''): ?>
            <div style="padding:16px 26px 0;">
                <div class="alert-box success" role="status" style="margin-bottom:0;">
                    <span>✓</span>
                    <div><?= e($pesanSukses) ?></div>
                </div>
            </div>
        <?php endif; ?>

        <div class="auth-body">
            <!-- TAB 1: DAFTAR MEMBER MITRA -->
            <div class="tab-content<?= $showRegister ? ' active' : '' ?>" id="pane-daftar">
                <form action="login_mitra.php" method="post">
                    <input type="hidden" name="form_type" value="register_mitra">

                    <div class="form-group" style="margin-bottom:14px;">
                        <label for="mitra_nama">Nama Perusahaan / Vendor / Tenant *</label>
                        <input type="text" id="mitra_nama" name="mitra_nama" placeholder="Contoh: CV Rekanan Kuliner UP" required>
                    </div>

                    <div class="form-group" style="margin-bottom:14px;">
                        <label for="mitra_pic">Nama Penanggung Jawab (PIC) *</label>
                        <input type="text" id="mitra_pic" name="mitra_pic" placeholder="Contoh: Dheka Airlangga" required>
                    </div>

                    <div class="form-group" style="margin-bottom:14px;">
                        <label for="mitra_email">Email *</label>
                        <input type="email" id="mitra_email" name="mitra_email" maxlength="254" placeholder="nama@email.com" autocomplete="email" required>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                        <div class="form-group">
                            <label for="mitra_plat">Nomor Polisi (Plat) *</label>
                            <input type="text" id="mitra_plat" name="mitra_plat" placeholder="Contoh: B 9012 UP" required>
                        </div>
                        <div class="form-group">
                            <label for="mitra_tipe">Tipe Kendaraan</label>
                            <select id="mitra_tipe" name="mitra_tipe">
                                <option value="Mobil">Mobil</option>
                                <option value="Motor">Motor</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:20px;">
                        <label for="mitra_paket">Pilihan Paket Langganan</label>
                        <select id="mitra_paket" name="mitra_paket">
                            <option value="1 Bulan">1 Bulan (Rp 100.000)</option>
                            <option value="6 Bulan">6 Bulan (Rp 500.000)</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom:20px;">
                        <label for="mitra_password">Password *</label>
                        <div class="password-input-wrap">
                            <input type="password" id="mitra_password" name="mitra_password" placeholder="Buat password (min. 6 karakter)" minlength="6" required>
                            <button type="button" class="password-toggle" data-password-toggle aria-label="Tampilkan password" aria-pressed="false" aria-controls="mitra_password">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;padding:12px;font-size:14.5px;">
                        Daftar & Aktifkan Member Mitra UP
                    </button>
                </form>
                <button type="button" class="btn btn-outline auth-switch-btn" id="btn-ke-login" aria-controls="pane-login">
                    Sudah punya akun? Login
                </button>
            </div>

            <!-- TAB 2: LOGIN MITRA -->
            <div class="tab-content<?= $showRegister ? '' : ' active' ?>" id="pane-login">
                <form action="login_mitra.php" method="post">
                    <input type="hidden" name="form_type" value="login_mitra">

                    <div class="form-group" style="margin-bottom:14px;">
                        <label for="mitra_login_id">ID Mitra *</label>
                        <input type="text" id="mitra_login_id" name="mitra_login_id" placeholder="Contoh: MTR-UP-123456" required>
                    </div>

                    <div class="form-group" style="margin-bottom:20px;">
                        <label for="mitra_login_pass">Password</label>
                        <div class="password-input-wrap">
                            <input type="password" id="mitra_login_pass" name="mitra_login_pass" placeholder="Password akun mitra" autocomplete="current-password" required>
                            <button type="button" class="password-toggle" data-password-toggle aria-label="Tampilkan password" aria-pressed="false" aria-controls="mitra_login_pass">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;padding:12px;font-size:14.5px;">
                        Login
                    </button>
                </form>
                <button type="button" class="btn btn-outline auth-switch-btn" id="btn-ke-daftar" aria-controls="pane-daftar">
                    Buat akun baru
                </button>
            </div>
        </div>
    </div>
</main>

<footer class="site-footer" style="padding:25px;text-align:center;">
    <div style="font-size:12.5px;color:#94a3b8;">
        PARKIRKU &copy; <?= date('Y') ?> Universitas Pancasila &middot; PBW Kelompok 5
    </div>
</footer>

<script>
    const btnKeDaftar = document.getElementById('btn-ke-daftar');
    const btnKeLogin = document.getElementById('btn-ke-login');
    const paneDaftar = document.getElementById('pane-daftar');
    const paneLogin = document.getElementById('pane-login');

    function tampilkanPane(paneAktif) {
        const tampilkanDaftar = paneAktif === paneDaftar;
        paneDaftar.classList.toggle('active', tampilkanDaftar);
        paneLogin.classList.toggle('active', !tampilkanDaftar);
    }

    btnKeDaftar.addEventListener('click', function () {
        tampilkanPane(paneDaftar);
    });

    btnKeLogin.addEventListener('click', function () {
        tampilkanPane(paneLogin);
    });

    document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            const passwordInput = document.getElementById(button.getAttribute('aria-controls'));
            const shouldShow = passwordInput.type === 'password';
            passwordInput.type = shouldShow ? 'text' : 'password';
            button.setAttribute('aria-pressed', String(shouldShow));
            button.setAttribute('aria-label', shouldShow ? 'Sembunyikan password' : 'Tampilkan password');
            button.innerHTML = shouldShow
                ? '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a16 16 0 0 1-3.1 3.8M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7c1 0 2-.2 2.8-.5"/></svg>'
                : '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>';
        });
    });
</script>
</body>
</html>
