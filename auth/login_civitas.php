<?php
session_start();
require_once __DIR__ . '/../tools/functions.php';
require_once __DIR__ . '/../config/database.php';

// Jika sudah login sebagai civitas, langsung ke dashboard civitas
if (($_SESSION['auth_role'] ?? '') === 'civitas') {
    header('Location: ../dashboard/dashboard_civitas.php');
    exit;
}

$pesanError  = $_SESSION['error']  ?? [];
$pesanSukses = $_SESSION['sukses'] ?? '';
unset($_SESSION['error'], $_SESSION['sukses']);

// ─── Handle Form POST ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['form_type'] ?? '';

    // ── REGISTRASI CIVITAS ──────────────────────────────────────────────────
    if ($formType === 'register_civitas') {
        $nama     = trim($_POST['civitas_nama']     ?? '');
        $emailInput = $_POST['civitas_email'] ?? '';
        $email    = is_string($emailInput) ? trim($emailInput) : '';
        $npm      = trim($_POST['civitas_npm']      ?? '');
        $password = $_POST['civitas_password'] ?? '';
        $peran    = trim($_POST['civitas_peran']    ?? 'Mahasiswa');
        $fakultas = trim($_POST['civitas_fakultas'] ?? 'Fakultas Teknik');
        $jenjang  = trim($_POST['civitas_jenjang'] ?? '');
        $programStudi = trim($_POST['civitas_program_studi'] ?? '');
        $plat     = normalisasiPlat(trim($_POST['civitas_plat'] ?? ''));
        $tipe     = trim($_POST['civitas_tipe']     ?? 'Mobil');

        if ($nama === '' || $email === '' || $npm === '' || $password === '' || $plat === '') {
            $pesanError[] = 'Semua kolom bertanda bintang (*) wajib diisi.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
            $pesanError[] = 'Alamat email tidak valid.';
        } elseif ($peran === 'Mahasiswa' && ($programStudi === '' || !in_array($jenjang, ['S1', 'D3', 'D4'], true))) {
            $pesanError[] = 'Mahasiswa wajib mengisi program studi dan jenjang pendidikan.';
        } elseif (strlen($password) < 6) {
            $pesanError[] = 'Password minimal 6 karakter.';
        } elseif (!validasiPlat($plat)) {
            $pesanError[] = 'Format plat nomor tidak valid. Contoh: B 1234 UP.';
        } elseif (!in_array($peran, ['Mahasiswa', 'Dosen', 'Tenaga Kependidikan'], true)
            || !in_array($fakultas, [
                'Fakultas Teknik',
                'Fakultas Farmasi',
                'Fakultas Hukum',
                'Fakultas Ekonomi & Bisnis',
                'Fakultas Psikologi',
                'Fakultas Ilmu Komunikasi',
                'Fakultas Pariwisata',
                'Sekolah Pascasarjana',
            ], true)
            || ($peran !== 'Mahasiswa' && !in_array($peran, ['Dosen', 'Tenaga Kependidikan'], true))
            || !validasiTipe($tipe)) {
            $pesanError[] = 'Data peran, fakultas, atau tipe kendaraan tidak valid.';
        } else {
            $pdo = getDB();
            $stmt = $pdo->prepare('SELECT id FROM users_civitas WHERE npm = ? OR email = ? LIMIT 1');
            $stmt->execute([$npm, $email]);
            if ($stmt->fetch()) {
                $pesanError[] = 'NPM/NIP atau email sudah terdaftar. Silakan login.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $ins  = $pdo->prepare('
                    INSERT INTO users_civitas (nama, email, npm, password, peran, fakultas, jenjang, program_studi, plat, tipe)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ');
                $ins->execute([
                    $nama,
                    $email,
                    $npm,
                    $hash,
                    $peran,
                    $fakultas,
                    $peran === 'Mahasiswa' ? $jenjang : null,
                    $peran === 'Mahasiswa' ? $programStudi : null,
                    $plat,
                    $tipe,
                ]);

                session_regenerate_id(true);
                $_SESSION['auth_role'] = 'civitas';
                $_SESSION['user_data'] = [
                    'nama'     => $nama,
                    'email'    => $email,
                    'foto_profil' => null,
                    'npm'      => $npm,
                    'peran'    => $peran,
                    'fakultas' => $fakultas,
                    'jenjang'  => $peran === 'Mahasiswa' ? $jenjang : '',
                    'program_studi' => $peran === 'Mahasiswa' ? $programStudi : '',
                    'plat'     => $plat,
                    'tipe'     => $tipe,
                    'berlaku'  => '1 Semester (6 Bulan)',
                ];
                $_SESSION['sukses'] = "Pendaftaran member {$nama} berhasil! Kartu E-Parking Pass UP Anda telah aktif.";
                header('Location: ../dashboard/dashboard_civitas.php');
                exit;
            }
        }
    }

    // ── LOGIN CIVITAS ───────────────────────────────────────────────────────
    elseif ($formType === 'login_civitas') {
        $npm      = trim($_POST['civitas_login_npm']  ?? '');
        $password = $_POST['civitas_login_pass'] ?? '';

        if ($npm === '' || $password === '') {
            $pesanError[] = 'NPM/NIP dan password wajib diisi.';
        } else {
            $pdo  = getDB();
            $stmt = $pdo->prepare('SELECT * FROM users_civitas WHERE npm = ? LIMIT 1');
            $stmt->execute([$npm]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                $pesanError[] = 'NPM/NIP atau password salah.';
            } else {
                session_regenerate_id(true);
                $_SESSION['auth_role'] = 'civitas';
                $_SESSION['user_data'] = [
                    'nama'     => $user['nama'],
                    'email'    => $user['email'] ?? '',
                    'foto_profil' => $user['foto_profil'] ?? null,
                    'npm'      => $user['npm'],
                    'peran'    => $user['peran'],
                    'fakultas' => $user['fakultas'],
                    'jenjang'  => $user['jenjang'] ?? '',
                    'program_studi' => $user['program_studi'] ?? '',
                    'plat'     => $user['plat'],
                    'tipe'     => $user['tipe'],
                    'berlaku'  => $user['berlaku'],
                ];
                $_SESSION['sukses'] = 'Berhasil login ke Akun Member Civitas UP.';
                header('Location: ../dashboard/dashboard_civitas.php');
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
    && ($_POST['form_type'] ?? '') === 'register_civitas';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Registrasi Civitas | Universitas Pancasila</title>
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
                <span class="brand-text-top">PORTAL CIVITAS</span>
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
            <h2>Layanan Parkir Civitas Akademika</h2>
            <p>Pendaftaran akses member mahasiswa, dosen, dan staf UP</p>
        </div>

        <?php if (count($pesanError) > 0): ?>
            <div style="padding:16px 26px 0;">
                <div class="alert-box danger" role="alert" style="margin-bottom:0;">
                    <span>⚠</span>
                    <div>
                        <?php foreach ($pesanError as $err): ?>
                            <div>• <?= $err ?></div>
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
            <!-- TAB 1: FORM DAFTAR MEMBER BARU -->
            <div class="tab-content<?= $showRegister ? ' active' : '' ?>" id="pane-daftar">
                <form action="login_civitas.php" method="post">
                    <input type="hidden" name="form_type" value="register_civitas">

                    <div class="form-group" style="margin-bottom:14px;">
                        <label for="civitas_nama">Nama Lengkap *</label>
                        <input type="text" id="civitas_nama" name="civitas_nama" placeholder="Contoh: Bryan Ananda" required>
                    </div>

                    <div class="form-group" style="margin-bottom:14px;">
                        <label for="civitas_email">Email *</label>
                        <input type="email" id="civitas_email" name="civitas_email" maxlength="254" placeholder="nama@email.com" autocomplete="email" required>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                        <div class="form-group">
                            <label for="civitas_peran">Status Peran</label>
                            <select id="civitas_peran" name="civitas_peran">
                                <option value="Mahasiswa">Mahasiswa</option>
                                <option value="Dosen">Dosen</option>
                                <option value="Tenaga Kependidikan">Tenaga Kependidikan (Staf)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="civitas_npm">NPM / NIP *</label>
                            <input type="text" id="civitas_npm" name="civitas_npm" placeholder="Contoh: 4524210020" required>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:14px;">
                        <label for="civitas_fakultas">Fakultas / Unit Kerja</label>
                        <select id="civitas_fakultas" name="civitas_fakultas">
                            <option value="Fakultas Teknik">Fakultas Teknik</option>
                            <option value="Fakultas Farmasi">Fakultas Farmasi</option>
                            <option value="Fakultas Hukum">Fakultas Hukum</option>
                            <option value="Fakultas Ekonomi & Bisnis">Fakultas Ekonomi & Bisnis</option>
                            <option value="Fakultas Psikologi">Fakultas Psikologi</option>
                            <option value="Fakultas Ilmu Komunikasi">Fakultas Ilmu Komunikasi</option>
                            <option value="Fakultas Pariwisata">Fakultas Pariwisata</option>
                            <option value="Sekolah Pascasarjana">Sekolah Pascasarjana</option>
                        </select>
                    </div>

                    <div id="student-study-fields" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                        <div class="form-group">
                            <label for="civitas_jenjang">Jenjang Pendidikan *</label>
                            <select id="civitas_jenjang" name="civitas_jenjang">
                                <option value="">Pilih jenjang</option>
                                <option value="S1">S1</option>
                                <option value="D3">D3</option>
                                <option value="D4">D4</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="civitas_program_studi">Program Studi *</label>
                            <input type="text" id="civitas_program_studi" name="civitas_program_studi" maxlength="150" placeholder="Contoh: Teknik Informatika">
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                        <div class="form-group">
                            <label for="civitas_plat">Nomor Polisi (Plat) *</label>
                            <input type="text" id="civitas_plat" name="civitas_plat" placeholder="Contoh: B 1234 UP" required>
                        </div>
                        <div class="form-group">
                            <label for="civitas_tipe">Tipe Kendaraan</label>
                            <select id="civitas_tipe" name="civitas_tipe">
                                <option value="Mobil">Mobil</option>
                                <option value="Motor">Motor</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:20px;">
                        <label for="civitas_password">Password *</label>
                        <div class="password-input-wrap">
                            <input type="password" id="civitas_password" name="civitas_password" placeholder="Buat password (min. 6 karakter)" required>
                            <button type="button" class="password-toggle" data-password-toggle aria-label="Tampilkan password" aria-pressed="false" aria-controls="civitas_password">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;padding:12px;font-size:14.5px;">
                        Daftar & Terbitkan Kartu Parkir UP
                    </button>
                </form>
                <button type="button" class="btn btn-outline auth-switch-btn" id="btn-ke-login" aria-controls="pane-login">
                    Sudah punya akun? Login
                </button>
            </div>

            <!-- TAB 2: FORM LOGIN CIVITAS -->
            <div class="tab-content<?= $showRegister ? '' : ' active' ?>" id="pane-login">
                <form action="login_civitas.php" method="post">
                    <input type="hidden" name="form_type" value="login_civitas">

                    <div class="form-group" style="margin-bottom:14px;">
                        <label for="civitas_login_npm">NPM / NIP / NIDN *</label>
                        <input type="text" id="civitas_login_npm" name="civitas_login_npm" placeholder="Masukkan nomor identitas kampus" required>
                    </div>

                    <div class="form-group" style="margin-bottom:20px;">
                        <label for="civitas_login_pass">Password *</label>
                        <div class="password-input-wrap">
                            <input type="password" id="civitas_login_pass" name="civitas_login_pass" placeholder="Password akun" autocomplete="current-password" required>
                            <button type="button" class="password-toggle" data-password-toggle aria-label="Tampilkan password" aria-pressed="false" aria-controls="civitas_login_pass">
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
    const btnKeDaftar  = document.getElementById('btn-ke-daftar');
    const btnKeLogin   = document.getElementById('btn-ke-login');
    const paneDaftar   = document.getElementById('pane-daftar');
    const paneLogin    = document.getElementById('pane-login');
    const peranSelect  = document.getElementById('civitas_peran');
    const studentFields = document.getElementById('student-study-fields');
    const jenjangSelect = document.getElementById('civitas_jenjang');
    const programStudiInput = document.getElementById('civitas_program_studi');

    function updateStudentFields() {
        const isMahasiswa = peranSelect.value === 'Mahasiswa';
        studentFields.style.display = isMahasiswa ? 'grid' : 'none';
        jenjangSelect.required = isMahasiswa;
        programStudiInput.required = isMahasiswa;
    }

    peranSelect.addEventListener('change', updateStudentFields);
    updateStudentFields();

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
